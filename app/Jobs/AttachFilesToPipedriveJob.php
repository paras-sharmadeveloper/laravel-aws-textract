<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class AttachFilesToPipedriveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 60;

    protected $dealId;
    protected $files;

    public function __construct($dealId, $files)
    {
        $this->dealId = $dealId;
        $this->files = $files;
    }

    /**
     * Fan out into one job per file, chained so they attach to the deal
     * strictly one at a time (regardless of worker count) instead of
     * looping through a potentially large batch inside a single job run.
     */
    public function handle()
    {
        $files = collect($this->files)
            ->filter(fn($file) => isset($file['s3_key']))
            ->values();

        if ($files->isEmpty()) {
            return;
        }

        Log::info("Queueing sequential Pipedrive attachments", [
            'deal_id' => $this->dealId,
            'file_count' => $files->count(),
        ]);

        $jobs = $files
            ->map(fn($file) => new AttachFileToPipedriveJob($this->dealId, $file))
            ->all();

        Bus::chain($jobs)
            ->onQueue('attachments')
            ->catch(function (\Throwable $e) {
                Log::critical("Attachment chain halted unexpectedly", [
                    'deal_id' => $this->dealId,
                    'error' => $e->getMessage(),
                ]);
            })
            ->dispatch();
    }

    public function failed(\Exception $exception)
    {
        Log::critical("AttachFiles Job Failed Completely", [
            'deal_id' => $this->dealId,
            'error' => $exception->getMessage()
        ]);
    }
}
