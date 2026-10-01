<?php

namespace App\Jobs;

use App\Jobs\Concerns\OcrRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use App\Models\Lead;

/**
 * Collects the per-file OCR results into the same $result shape the
 * pipeline has always passed on, then hands over to ParseAndCreateLeadJob.
 */
class FinalizeOcrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, OcrRun;

    public $tries = 1;
    public $timeout = 120;

    public function __construct(
        protected string $runId,
        protected array $result
    ) {}

    public function handle()
    {
        $keys = [];

        foreach ($this->result['documents'] as $docName => $doc) {
            $key = self::documentKey($this->runId, $docName);

            if (Cache::has($key)) {
                $this->result['documents'][$docName]['raw_text'] = Cache::get($key);
                $keys[] = $key;
            }
        }

        // 👉 Statement renaming (bank / ccp / pos) - individual files, never merged
        $usedNames = [];

        $categoryLabels = [
            'bank' => 'Bank',
            'ccp' => 'CCP',
            'pos' => 'POS',
        ];

        foreach ($this->result['statements'] ?? [] as $idx => $statement) {
            $key = self::statementKey($this->runId, $idx);
            $keys[] = $key;
            $period = Cache::get($key)['period'] ?? null;
            $ext = strtolower($statement['ext']);

            if ($period) {
                $categoryLabel = $categoryLabels[$statement['category']] ?? ucfirst($statement['category']);
                $monthLabel = ucfirst($period['month']);
                $finalName = "{$categoryLabel}_Statement_{$monthLabel}_{$period['year']}.{$ext}";
            } else {
                $finalName = $statement['original_name'];
            }

            $this->result['statements'][$idx]['final_filename'] = $this->uniqueFilename($finalName, $usedNames);
        }

        // 👉 Next job
        ParseAndCreateLeadJob::dispatch($this->result)->onQueue('Parse-create-lead');

        Cache::deleteMultiple($keys);
    }

    public function failed(\Throwable $exception)
    {
        Lead::track($this->result['lead_id'] ?? null, [
            'status' => 'failed',
            'stage' => 'ocr',
            'error' => 'OCR failed: ' . $exception->getMessage(),
        ]);
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
}
