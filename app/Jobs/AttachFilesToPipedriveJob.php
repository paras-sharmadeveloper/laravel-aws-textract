<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

use App\Services\PipedriveService;

class AttachFilesToPipedriveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 300;

    protected $dealId;
    protected $files;

    public function __construct($dealId, $files)
    {
        $this->dealId = $dealId;
        $this->files = $files;
    }

    public function handle(PipedriveService $pipedrive)
    {
        foreach ($this->files as $file) {

            if (!isset($file['s3_key'])) {
                continue;
            }

            try {

                Log::info("Attaching file to Pipedrive", [
                    'deal_id' => $this->dealId,
                    'file' => $file['file_name'] ?? null
                ]);

                $pipedrive->attachFileFromS3(
                    $this->dealId,
                    $file['s3_key'],
                    $file['file_name'] ?? 'document.pdf'
                );
            } catch (\Exception $e) {

                Log::error("File attachment failed", [
                    'deal_id' => $this->dealId,
                    'file' => $file,
                    'error' => $e->getMessage()
                ]);

                // Continue instead of failing whole job
                continue;
            }
        }
    }

    public function failed(\Exception $exception)
    {
        Log::critical("AttachFiles Job Failed Completely", [
            'deal_id' => $this->dealId,
            'error' => $exception->getMessage()
        ]);
    }
}
