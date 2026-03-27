<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\S3Service;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PipedriveService
{
    protected $baseUrl;
    protected $token;
    protected $ownerId;
    protected $pipelineId;
    protected $stageId;
    protected $s3Service;

    public function __construct(S3Service $s3Service)
    {
        $this->baseUrl   = config('services.pipedrive.base_url');
        $this->token     = config('services.pipedrive.api_key');
        $this->ownerId   = config('services.pipedrive.owner_id');
        $this->pipelineId = config('services.pipedrive.pipeline_id');
        $this->stageId   = config('services.pipedrive.stage_id');
        $this->s3Service = $s3Service;
    }
    private function request($method, $endpoint, $data = [], $isMultipart = false)
    {
        $url = "{$this->baseUrl}{$endpoint}?api_token={$this->token}";

        // Clean UTF-8 issues
        $data = $this->cleanUtf8($data);

        file_put_contents(
            storage_path('app/cleanutf.txt'),
            print_r($data, true)
        );



        try {

            $client = Http::timeout(60)        // ⏱️ prevent hanging
                ->retry(3, 1000);              // 🔁 retry 3 times (1 sec gap)

            if ($isMultipart) {
                $response = $client->asMultipart()->post($url, $data);
            } else {
                $response = match (strtolower($method)) {
                    'post' => $client->post($url, $data),
                    'get' => $client->get($url, $data),
                    'put' => $client->put($url, $data),
                    'delete' => $client->delete($url, $data),
                    default => throw new \Exception("Invalid HTTP method: {$method}")
                };
            }

            if (!$response->successful()) {

                Log::error("Pipedrive API Error", [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'payload' => $data
                ]);

                throw new \Exception("Pipedrive API Error: " . $response->status());
            }

            return $response->json('data');
        } catch (\Exception $e) {

            Log::error("Pipedrive Request Failed", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    // private function request($method, $endpoint, $data = [], $isMultipart = false)
    // {
    //     $url = "{$this->baseUrl}{$endpoint}?api_token={$this->token}";

    //     // Clean UTF-8 issues from OCR / external data
    //     $data = $this->cleanUtf8($data);

    //     $response = $isMultipart
    //         ? Http::asMultipart()->post($url, $data)
    //         : Http::$method($url, $data);

    //     if (!$response->successful()) {
    //         Log::error("Pipedrive API Error", [
    //             'endpoint' => $endpoint,
    //             'response' => $response->body()
    //         ]);
    //         throw new \Exception("Pipedrive API Error");
    //     }

    //     return $response->json('data');
    // }

    // private function request($method, $endpoint, $data = [], $isMultipart = false)
    // {
    //     $url = "{$this->baseUrl}{$endpoint}?api_token={$this->token}";

    //     $response = $isMultipart
    //         ? Http::asMultipart()->post($url, $data)
    //         : Http::$method($url, $data);

    //     if (!$response->successful()) {
    //         Log::error("Pipedrive API Error", [
    //             'endpoint' => $endpoint,
    //             'response' => $response->body()
    //         ]);
    //         throw new \Exception("Pipedrive API Error");
    //     }

    //     return $response->json('data');
    // }

    /*
    |--------------------------------------------------------------------------
    | PERSON
    |--------------------------------------------------------------------------
    */
    public function createPerson(array $data)
    {
        $name = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));

        $payload = [
            'name' => Str::title($name) ?: ($data['email'] ?? 'Unknown Lead'),
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'owner_id' => $this->ownerId,
            'visible_to' => 3,
            // DOB custom field
            'c1370eac8a04feabd3a533ed981bf9a1a498b4a6' => $data['date_of_birth'] ?? null,
            // Full Address
            '1367ab6ef1d586538eea139bc7e4971e204068c4' => Str::title($data['home_address']) ?? null,
        ];

        return $this->request('post', '/persons', $payload)['id'];
    }

    /*
    |--------------------------------------------------------------------------
    | ORGANIZATION
    |--------------------------------------------------------------------------
    */
    public function createOrganization(array $data)
    {
        if (empty($data['business_name'])) {
            return null;
        }
        $identificationNumber = str_replace('-', '', $data['identification_number'] ?? '');
        $payload = [
            'name' => Str::title($data['business_name']),
            'address' => Str::title($data['business_address']) ?? null,
            'owner_id' => $this->ownerId,
            'visible_to' => 3,
            'a5caf4d8d131d8b6d965dc17a52d08de2d433bd9' => $identificationNumber ?? null,
            '87e4f8286776a95af868610d3c73af929b7da72f' => Str::title($data['bank_name']) ?? null,
            'd48c4347fce9119821fe599ca67daec5b2be614f' => $data['routing_number'] ?? null,
            '7a749d6ff1cf7de4ecaa2ad3ffc8b35e1f1442a7' => $data['account_number'] ?? null,
        ];

        return $this->request('post', '/organizations', $payload)['id'];
    }

    /*
    |--------------------------------------------------------------------------
    | DEAL
    |--------------------------------------------------------------------------
    */
    public function createDeal(array $data, $personId, $orgId)
    {
        $title = ($data['business_name'] ?? $data['email'] ?? 'New Lead');

        $payload = [
            'title' => Str::title($title),
            ~'status' => 'open',
            'stage_id' => $this->stageId,
            'pipeline_id' => $this->pipelineId,
            'user_id' => $this->ownerId,
            'org_id' => $orgId,
            'person_id' => $personId,
            'visible_to' => 3,
            'currency' => 'USD'
        ];

        return $this->request('post', '/deals', $payload)['id'];
    }

    /*
    |--------------------------------------------------------------------------
    | ATTACH FILE
    |--------------------------------------------------------------------------
    */
    public function attachFile($dealId, $filePath)
    {
        if (!file_exists($filePath)) {
            // \Log::error("File not found for attachment", ['path' => $filePath]);
            return;
        }

        $response = Http::attach(
            'file',
            fopen($filePath, 'r'),
            basename($filePath)
        )->post("{$this->baseUrl}/files", [
            'api_token' => $this->token,
            'deal_id' => $dealId
        ]);

        if (!$response->successful()) {
            // \Log::error("Pipedrive File Upload Failed", [
            //     'response' => $response->body()
            // ]);
        }
    }


    public function attachFileFromS3($dealId, $s3Key, $fileName)
    {

        Log::info("ATTACH FROM S3 START jj", [
            'fileName' => $fileName,
            's3Key' => $s3Key
        ]);
        // Download file from S3
        $fileContent = $this->s3Service->getFileContent($s3Key);

        if (!$fileContent) {
            Log::error("S3 file download failed", ['key' => $s3Key]);
            return;
        }

        // Create temp file
        $tempPath = sys_get_temp_dir() . '/' . uniqid('file_');
        file_put_contents($tempPath, $fileContent);

        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $uploadPath = $tempPath;

        // If image → convert to PDF
        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {

            $pdfPath = app(\App\Services\PdfService::class)->imageToPdf($tempPath);

            if ($pdfPath && file_exists($pdfPath)) {
                $uploadPath = $pdfPath;
                $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '.pdf';
            }
        } else {

            $uploadPath = $tempPath;
        }

        $url = "{$this->baseUrl}/files?api_token={$this->token}";

        $response = Http::attach(
            'file',
            fopen($uploadPath, 'r'),
            $fileName
        )->post($url, [
            'deal_id' => $dealId
        ]);

        if (!$response->successful()) {
            // Log::error("Pipedrive File Upload Failed", [
            //     'response' => $response->body()
            // ]);
        }

        // cleanup
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        if (isset($pdfPath) && file_exists($pdfPath)) {
            unlink($pdfPath);
        }
    }



    /*
    |--------------------------------------------------------------------------
    | FULL PIPELINE
    |--------------------------------------------------------------------------
    */
    public function processLead(array $data)
    {
        $personId = $this->createPerson($data);
        $orgId = $this->createOrganization($data);
        $dealId = $this->createDeal($data, $personId, $orgId);

        $uploaded = [];


        // foreach ($data['files'] ?? [] as $file) {
        //     Log::info("Processing file for attachment", ['file' => $file]);
        //     if (!isset($file['s3_key'])) {
        //         continue;
        //     }

        //     $this->attachFileFromS3(
        //         $dealId,
        //         $file['s3_key'],
        //         $file['file_name'] ?? 'document.pdf'
        //     );
        // }

        return [
            'person_id' => $personId,
            'org_id' => $orgId,
            'deal_id' => $dealId
        ];
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
