<?php

namespace App\Jobs;

use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\GPTService;
use App\Services\GeoService;
use App\Services\{PipedriveService, PdfService};
use App\Models\Lead;
use App\Support\MicrLine;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class ParseAndCreateLeadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 300;

    protected $result;

    public function __construct($result)
    {
        $this->result = $result;
    }

    public function handle(
        GPTService $gpt,
        GeoService $geo,
        PipedriveService $pipedrive
    ) {
        $leadId = $this->result['lead_id'] ?? null;

        Lead::track($leadId, ['status' => 'processing', 'stage' => 'pipedrive', 'error' => null]);

        $gptPayload = [
            'email' => $this->result['email'],
            'phone' => $this->result['phone'],
            'documents' => [
                'driver_license' => $this->getDocument('ID'),
                'bank_document' => $this->getDocument('VC'),
                'tax_document' => $this->getDocument('TAX_ID'),
                'other_document' => $this->getDocument('Statement'),
            ]
        ];
        $gptPayload = $this->cleanUtf8($gptPayload);

        $parsedData = $gpt->parse($gptPayload);

        if (empty($parsedData)) {
            Log::warning('GPT parse returned no data, falling back to form fields', ['lead_id' => $leadId]);
        }

        $parsedData = $this->fillFromForm($parsedData);

        // Blank voided checks only carry the numbers in the MICR line, which GPT
        // often gives up on; read it directly and fill only what GPT left empty
        $micr = MicrLine::parse($gptPayload['documents']['bank_document']['raw_text'] ?? null);

        if (empty($parsedData['routing_number']) && $micr['routing_number']) {
            $parsedData['routing_number'] = $micr['routing_number'];

            if (empty($parsedData['account_number'])) {
                $parsedData['account_number'] = $micr['account_number'];
            }
        }
        file_put_contents(
            storage_path('app/gpt-parse.json'),
            json_encode($parsedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );


        // GEO
        if ($home = $geo->geocode($parsedData['home_address'] ?? null, 'home')) {
            $parsedData = array_merge($parsedData, $home);
        }

        if ($business = $geo->geocode($parsedData['business_address'] ?? null, 'business')) {
            $parsedData = array_merge($parsedData, $business);
        }

        // FILES
        $filesPayload = collect($this->result['documents'])
            ->flatMap(fn($doc) => $doc['s3_keys'])
            ->unique()
            ->map(fn($key) => [
                'file_name' => basename($key),
                's3_key' => $key,
                's3_url' => "https://" . config('filesystems.disks.s3.bucket') . ".s3.amazonaws.com/" . $key,
            ])
            ->values()
            ->toArray();

        // STATEMENTS (bank / ccp / pos) - attached under their generated filenames
        $statementFiles = collect($this->result['statements'] ?? [])
            ->map(fn($statement) => [
                'file_name' => $statement['final_filename'] ?? $statement['original_name'],
                's3_key' => $statement['s3_key'],
                's3_url' => "https://" . config('filesystems.disks.s3.bucket') . ".s3.amazonaws.com/" . $statement['s3_key'],
            ])
            ->values()
            ->toArray();

        $parsedData['files'] = array_merge($filesPayload, $statementFiles);

        $lead = $leadId ? Lead::find($leadId) : null;

        $ids = $pipedrive->processLead(
            $parsedData,
            $lead?->affiliate,
            ['person_id' => $lead?->pipedrive_person_id, 'org_id' => $lead?->pipedrive_org_id],
            fn(array $created) => Lead::track($leadId, array_combine(
                array_map(fn($key) => "pipedrive_{$key}", array_keys($created)),
                $created
            ))
        );

        Lead::track($leadId, [
            'stage' => 'attachments',
            'pipedrive_person_id' => $ids['person_id'],
            'pipedrive_org_id' => $ids['org_id'],
            'pipedrive_deal_id' => $ids['deal_id'],
        ]);

        // 👉 Attachments job (already correct)
        AttachFilesToPipedriveJob::dispatch(
            $ids['deal_id'],
            $parsedData['files'],
            $leadId
        )->onQueue('attachments')->delay(now()->addSeconds(10));
    }

    public function failed(\Throwable $exception)
    {
        Lead::track($this->result['lead_id'] ?? null, [
            'status' => 'failed',
            'stage' => 'pipedrive',
            'error' => 'Lead creation failed: ' . $exception->getMessage(),
        ]);
    }

    /**
     * The form fields are always known, so use them for anything GPT could not
     * extract — otherwise a failed parse creates an empty "New Lead" deal.
     */
    private function fillFromForm(array $parsedData): array
    {
        [$first, $last] = array_pad(preg_split('/\s+/', trim($this->result['owner_name'] ?? ''), 2), 2, null);

        $fallbacks = [
            'email' => $this->result['email'] ?? null,
            'phone' => $this->result['phone'] ?? null,
            'business_name' => $this->result['business_name'] ?? null,
            'first_name' => $first ?: null,
            'last_name' => $last,
        ];

        foreach ($fallbacks as $field => $value) {
            if (empty($parsedData[$field]) && filled($value)) {
                $parsedData[$field] = $value;
            }
        }

        return $parsedData;
    }

    private function getDocument($name)
    {
        foreach ($this->result['documents'] as $key => $doc) {
            if (str_starts_with($key, $name)) {
                return $doc;
            }
        }
        return [];
    }
    private function cleanUtf8($data)
    {
        if (is_array($data)) {
            $cleaned = [];

            foreach ($data as $key => $value) {

                // Clean key
                $cleanKey = is_string($key)
                    ? iconv('UTF-8', 'UTF-8//IGNORE', $key)
                    : $key;

                if (is_string($cleanKey)) {
                    $cleanKey = preg_replace('/[^\x20-\x7E]/u', '', $cleanKey);
                    $cleanKey = trim($cleanKey);
                }

                // 🔥 FIX: empty key ko handle karo
                if (empty($cleanKey)) {
                    $cleanKey = 'status'; // 👈 manually assign
                }

                $cleaned[$cleanKey] = $this->cleanUtf8($value);
            }

            return $cleaned;
        }

        if (is_string($data)) {
            $data = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $data);
            $data = iconv('UTF-8', 'UTF-8//IGNORE', $data);

            return $data !== false ? $data : '';
        }

        return $data;
    }
}
