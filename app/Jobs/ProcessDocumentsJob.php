<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Services\TextractService;
use App\Services\GPTService;
use App\Services\GeoService;
use App\Services\{PipedriveService, PdfService};

class ProcessDocumentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 600;

    protected $result;

    public function __construct($result)
    {
        $this->result = $result;
    }

    public function handle(
        TextractService $textract,
        GPTService $gpt,
        GeoService $geo,
        PipedriveService $pipedrive,
        PdfService $pdfService
    ) {

        try {

            Log::info("Job Started");

            /*
        |--------------------------------------------------------------------------
        | 1️⃣ OCR FROM S3
        |--------------------------------------------------------------------------
        */

            foreach ($this->result['documents'] as $docName => &$doc) {

                $rawText = '';

                foreach ($doc['s3_keys'] as $key) {

                    if (str_starts_with($docName, 'ID')) {

                        $rawText = $textract->analyzeID($key);
                        \Log::error("ID PAYLOAD ", ['content' => $rawText]);
                    } elseif (str_ends_with($key, '.pdf')) {

                        $rawText .= $textract->extractPdf($key);
                    } else {

                        $rawText .= $textract->extractImage($key);
                    }

                    // if (str_ends_with($key, '.pdf')) {
                    //     $rawText .= $textract->extractPdf($key);
                    // } else {
                    //     $rawText .= $textract->extractImage($key);
                    // }
                }

                $doc['raw_text'] = $rawText;

                \Log::error("raw_text", ['content' => $rawText]);
            }

            /*
        |--------------------------------------------------------------------------
        | 2️⃣ PREPARE GPT PAYLOAD
        |--------------------------------------------------------------------------
        */

            $gptPayload = [
                'email' => $this->result['email'],
                'phone' => $this->result['phone'],
                'documents' => [
                    'driver_license' => $this->getDocument('ID'),
                    'bank_document' => $this->getDocument('VC'),
                    'tax_document' => $this->getDocument('TAX_ID'),
                    'other_document' => $this->getDocument('Statement'),
                ]
                // 'documents' => [
                //     'driver_license' => $this->result['documents']['ID.pdf'] ?? [],
                //     'bank_document' => $this->result['documents']['VC.pdf'] ?? [],
                //     'tax_document' => $this->result['documents']['TaxID.pdf'] ?? [],
                //     'other_document' => $this->result['documents']['Statement.pdf'] ?? [],
                // ]
            ];

            \Log::error("gpt_payload", ['content' => $this->result]);

            /*
        |--------------------------------------------------------------------------
        | 3️⃣ GPT PARSE
        |--------------------------------------------------------------------------
        */

            $parsedData = $gpt->parse($gptPayload);

            /*
        |--------------------------------------------------------------------------
        | 4️⃣ GEO CODE
        |--------------------------------------------------------------------------
        */

            $homeGeo = $geo->geocode($parsedData['home_address'] ?? null, 'home');
            if ($homeGeo) {
                $parsedData = array_merge($parsedData, $homeGeo);
            }

            $businessGeo = $geo->geocode($parsedData['business_address'] ?? null, 'business');
            if ($businessGeo) {
                $parsedData = array_merge($parsedData, $businessGeo);
            }

            /*
        |--------------------------------------------------------------------------
        | 5️⃣ ALIGN DATA FOR PIPEDRIVE
        |--------------------------------------------------------------------------
        */

            $filesPayload = [];

            $unique = [];

            foreach ($this->result['documents'] as $docName => $doc) {

                foreach ($doc['s3_keys'] as $key) {

                    if (isset($unique[$key])) {
                        continue;
                    }

                    $unique[$key] = true;

                    $filesPayload[] = [
                        'file_name' => basename($key),
                        's3_key' => $key,
                        's3_url' => $this->generateS3Url($key),
                    ];
                }
            }
            \Log::info("FILES PAYLOAD", $filesPayload);
            $parsedData['files'] = $filesPayload;

            /*

            /*
        |--------------------------------------------------------------------------
        | 6️⃣ CREATE LEAD IN PIPEDRIVE
        |--------------------------------------------------------------------------
        */

            $ids = $pipedrive->processLead($parsedData);

            Log::info("Deal Created", ['deal_id' => $ids['deal_id']]);
        } catch (\Exception $e) {

            Log::error("Job Failed", [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    private function generateS3Url($key)
    {
        return "https://" . env('AWS_BUCKET') . ".s3.amazonaws.com/" . $key;
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

    public function failed(Exception $exception)
    {
        Log::critical("Job permanently failed", [
            'error' => $exception->getMessage()
        ]);
    }
}
