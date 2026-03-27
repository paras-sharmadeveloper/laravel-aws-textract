<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Services\TextractService;
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

    public function handle(TextractService $textract)
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

        // 👉 Next job
        ParseAndCreateLeadJob::dispatch($this->result)->onQueue('Parse-create-lead');
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
