<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Services\TextractService;
use App\Services\StatementDateService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessOcrJob implements ShouldQueue
{


    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 300;

    protected $result;

    public function __construct($result)
    {
        $this->result = $result;
    }

    public function handle(TextractService $textract, StatementDateService $dateService)
    {
        foreach ($this->result['documents'] as $docName => $doc) {

            // 🔥 Skip unnecessary docs (important)
            if (
                str_starts_with($docName, 'Pics') ||
                str_starts_with($docName, 'supportingdoc')
            ) {
                continue;
            }

            $rawText = '';

            foreach ($doc['s3_keys'] as $key) {

                if (str_starts_with($docName, 'ID')) {
                    $rawText = $textract->analyzeID($key);
                } elseif (str_ends_with(strtolower($key), '.pdf')) {
                    $rawText .= $textract->extractPdf($key);
                } else {
                    $rawText .= $textract->extractImage($key);
                }
            }
            $cleanText = $this->cleanRawText($rawText);
            $this->result['documents'][$docName]['raw_text'] = $cleanText;
            file_put_contents(storage_path('app/raw_text_' . time() . '.txt'), $cleanText);

            // Storage::put(
            //     'debug/raw_text_' . time() . '.txt',
            //     $rawText
            // );
        }

        // 👉 Statement renaming (bank / ccp / pos) - individual files, never merged
        $usedNames = [];

        $categoryLabels = [
            'bank' => 'Bank',
            'ccp' => 'CCP',
            'pos' => 'POS',
        ];

        foreach ($this->result['statements'] ?? [] as $idx => $statement) {

            $ext = strtolower($statement['ext']);

            try {
                $rawText = $ext === 'pdf'
                    ? $textract->extractPdf($statement['s3_key'])
                    : $textract->extractImage($statement['s3_key']);
            } catch (\Exception $e) {
                Log::error("Statement OCR failed", [
                    's3_key' => $statement['s3_key'],
                    'error' => $e->getMessage()
                ]);
                $rawText = '';
            }

            $cleanText = $this->cleanRawText($rawText);
            $period = $dateService->extractPeriod($cleanText);

            if ($period) {
                $categoryLabel = $categoryLabels[$statement['category']] ?? ucfirst($statement['category']);
                $monthLabel = ucfirst($period['month']);
                $finalName = "{$categoryLabel}_Statement_{$monthLabel}_{$period['year']}.{$ext}";
            } else {
                Log::warning("Unable to determine statement period for uploaded file. Using original filename.", [
                    's3_key' => $statement['s3_key'],
                    'original_name' => $statement['original_name'],
                ]);
                $finalName = $statement['original_name'];
            }

            $finalName = $this->uniqueFilename($finalName, $usedNames);

            $this->result['statements'][$idx]['final_filename'] = $finalName;
        }

        // 👉 Next job
        ParseAndCreateLeadJob::dispatch($this->result)->onQueue('Parse-create-lead');
    }

    private function uniqueFilename($name, array &$usedNames)
    {
        if (!isset($usedNames[$name])) {
            $usedNames[$name] = 1;
            return $name;
        }

        $usedNames[$name]++;
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $base = pathinfo($name, PATHINFO_FILENAME);

        return "{$base}_{$usedNames[$name]}." . $ext;
    }

    private function cleanRawText($text)
    {
        // Remove control characters (MOST IMPORTANT)
        $text = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $text);

        // Normalize spaces
        $text = preg_replace('/\s+/', ' ', $text);

        // Trim
        $text = trim($text);

        return $text;
    }
}
