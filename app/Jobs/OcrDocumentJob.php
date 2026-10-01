<?php

namespace App\Jobs;

use App\Jobs\Concerns\OcrRun;
use App\Services\TextractService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

/**
 * OCR for one identity / business document (ID, VC, TAX_ID).
 */
class OcrDocumentJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, OcrRun;

    // Textract hiccups (throttling, network) are retried; the read is idempotent
    public $tries = 3;
    public $backoff = [15, 45];
    public $timeout = 330;

    public function __construct(
        protected string $runId,
        protected string $docName,
        protected array $s3Keys
    ) {}

    public function handle(TextractService $textract)
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $rawText = '';

        foreach ($this->s3Keys as $key) {

            if (str_starts_with($this->docName, 'ID')) {
                $rawText = $textract->analyzeID($key);
            } elseif (str_ends_with(strtolower($key), '.pdf')) {
                $rawText .= $textract->extractPdf($key);
            } else {
                $rawText .= $textract->extractImage($key);
            }
        }

        $cleanText = $this->cleanRawText($rawText);
        file_put_contents(storage_path('app/raw_text_' . time() . '.txt'), $cleanText);

        Cache::put(self::documentKey($this->runId, $this->docName), $cleanText, now()->addDay());
    }
}
