<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use App\Models\Lead;

/**
 * Entry point of the pipeline. Fans the OCR work out into one job per file so
 * a lead with 30-40 files is read in parallel instead of one file at a time,
 * then FinalizeOcrJob rebuilds the result and continues to ParseAndCreateLeadJob.
 */
class ProcessOcrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 120;

    protected $result;

    public function __construct($result)
    {
        $this->result = $result;
    }

    public function handle()
    {
        $leadId = $this->result['lead_id'] ?? null;

        Lead::track($leadId, ['status' => 'processing', 'stage' => 'ocr', 'error' => null]);

        $runId = (string) Str::uuid();
        $jobs = [];

        foreach ($this->result['documents'] as $docName => $doc) {

            // 🔥 Skip unnecessary docs (important)
            if (
                str_starts_with($docName, 'Pics') ||
                str_starts_with($docName, 'supportingdoc') ||
                str_starts_with($docName, 'Projections')
            ) {
                continue;
            }

            $jobs[] = new OcrDocumentJob($runId, $docName, $doc['s3_keys']);
        }

        // 👉 Statements (bank / ccp / pos) - individual files, never merged
        foreach ($this->result['statements'] ?? [] as $idx => $statement) {
            $jobs[] = new OcrStatementJob($runId, $idx, $statement);
        }

        if (empty($jobs)) {
            FinalizeOcrJob::dispatch($runId, $this->result);
            return;
        }

        $result = $this->result;

        Bus::batch($jobs)
            ->name('ocr-lead-' . ($leadId ?? 'legacy'))
            ->onQueue('ocr')
            ->then(function (Batch $batch) use ($runId, $result) {
                FinalizeOcrJob::dispatch($runId, $result);
            })
            ->catch(function (Batch $batch, \Throwable $e) use ($leadId) {
                Lead::track($leadId, [
                    'status' => 'failed',
                    'stage' => 'ocr',
                    'error' => 'OCR failed: ' . $e->getMessage(),
                ]);
            })
            ->dispatch();
    }

    public function failed(\Throwable $exception)
    {
        Lead::track($this->result['lead_id'] ?? null, [
            'status' => 'failed',
            'stage' => 'ocr',
            'error' => 'OCR failed: ' . $exception->getMessage(),
        ]);
    }
}
