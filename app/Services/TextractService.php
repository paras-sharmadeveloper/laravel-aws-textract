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
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);
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
        do {
            sleep(2);
            $result = $this->client->getDocumentTextDetection([
                'JobId' => $jobId
            ]);
        } while ($result['JobStatus'] !== 'SUCCEEDED');

        $text = '';

        foreach ($result['Blocks'] as $block) {
            if ($block['BlockType'] === 'LINE') {
                $text .= $block['Text'] . "\n";
            }
        }

        return $text;
    }
}
