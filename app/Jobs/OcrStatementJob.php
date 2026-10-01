<?php

namespace App\Jobs;

use App\Jobs\Concerns\OcrRun;
use App\Services\StatementDateService;
use App\Services\TextractService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * OCR for one statement file, only to work out its month/year for renaming.
 * A failed read never fails the lead — the original filename is used instead.
 */
class OcrStatementJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, OcrRun;

    public $tries = 2;
    public $backoff = [15];
    public $timeout = 330;

    public function __construct(
        protected string $runId,
        protected int|string $idx,
        protected array $statement
    ) {}

    public function handle(TextractService $textract, StatementDateService $dateService)
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $ext = strtolower($this->statement['ext']);

        try {
            $rawText = $ext === 'pdf'
                ? $textract->extractPdf($this->statement['s3_key'])
                : $textract->extractImage($this->statement['s3_key']);
        } catch (\Exception $e) {
            Log::error("Statement OCR failed", [
                's3_key' => $this->statement['s3_key'],
                'error' => $e->getMessage()
            ]);
            $rawText = '';
        }

        $period = $dateService->extractPeriod($this->cleanRawText($rawText));

        if (!$period) {
            Log::warning("Unable to determine statement period for uploaded file. Using original filename.", [
                's3_key' => $this->statement['s3_key'],
                'original_name' => $this->statement['original_name'],
            ]);
        }

        Cache::put(self::statementKey($this->runId, $this->idx), ['period' => $period], now()->addDay());
    }
}
