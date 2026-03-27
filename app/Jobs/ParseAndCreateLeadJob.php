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
                's3_url' => "https://" . env('AWS_BUCKET') . ".s3.amazonaws.com/" . $key,
            ])
            ->values()
            ->toArray();

        $parsedData['files'] = $filesPayload;

        $ids = $pipedrive->processLead($parsedData);

        // 👉 Attachments job (already correct)
        AttachFilesToPipedriveJob::dispatch(
            $ids['deal_id'],
            $parsedData['files']
        )->onQueue('attachments')->delay(now()->addSeconds(10));
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
