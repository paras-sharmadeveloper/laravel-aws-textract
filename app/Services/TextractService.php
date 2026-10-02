<?php

namespace App\Services;

use Aws\Textract\TextractClient;

class TextractService
{
    protected $client;

    public function __construct()
    {
        $this->client = new TextractClient([
            'region' => config('filesystems.disks.s3.region'),
            'version' => 'latest',
            // Many files are read in parallel; back off and retry when Textract throttles
            'retries' => [
                'mode' => 'adaptive',
                'max_attempts' => 8,
            ],
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);
    }

    public function analyzeID($s3Key)
    {
        $result = $this->client->analyzeID([
            'DocumentPages' => [
                [
                    'S3Object' => [
                        'Bucket' => config('filesystems.disks.s3.bucket'),
                        'Name' => $s3Key
                    ]
                ]
            ]
        ]);

        return $result;
    }

    public function extractImage($s3Key)
    {
        $result = $this->client->detectDocumentText([
            'Document' => [
                'S3Object' => [
                    'Bucket' => config('filesystems.disks.s3.bucket'),
                    'Name' => $s3Key
                ]
            ]
        ]);

        $text = '';

        foreach ($result['Blocks'] as $block) {
            if ($block['BlockType'] === 'LINE') {
                $text .= $block['Text'] . "\n";
            }
        }

        return $text;
    }

    public function extractPdf($s3Key)
    {
        $start = $this->client->startDocumentTextDetection([
            'DocumentLocation' => [
                'S3Object' => [
                    'Bucket' => config('filesystems.disks.s3.bucket'),
                    'Name' => $s3Key
                ]
            ]
        ]);

        $jobId = $start['JobId'];

        // Polling
        $attempts = 0;
        $maxAttempts = 120; // ~4 minutes at 2s intervals

        do {
            sleep(2);
            $result = $this->client->getDocumentTextDetection([
                'JobId' => $jobId
            ]);
            $attempts++;

            if ($result['JobStatus'] === 'FAILED' || $result['JobStatus'] === 'PARTIAL_SUCCESS') {
                throw new \Exception("Textract job {$jobId} ended with status: {$result['JobStatus']}");
            }

            if ($attempts >= $maxAttempts) {
                throw new \Exception("Textract job {$jobId} timed out waiting for completion");
            }
        } while ($result['JobStatus'] !== 'SUCCEEDED');

        $text = '';

        // Results come back in pages of up to 1000 blocks — read them all
        while (true) {
            foreach ($result['Blocks'] as $block) {
                if ($block['BlockType'] === 'LINE') {
                    $text .= $block['Text'] . "\n";
                }
            }

            if (empty($result['NextToken'])) {
                break;
            }

            $result = $this->client->getDocumentTextDetection([
                'JobId' => $jobId,
                'NextToken' => $result['NextToken'],
            ]);
        }

        return $text;
    }
}
