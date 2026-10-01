<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

use App\Services\PipedriveService;
use App\Models\Lead;

class AttachFileToPipedriveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 120;

    protected $dealId;
    protected $file;
    protected $leadId;

    public function __construct($dealId, array $file, $leadId = null)
    {
        $this->dealId = $dealId;
        $this->file = $file;
        $this->leadId = $leadId;
    }

    public function handle(PipedriveService $pipedrive)
    {
        if (!isset($this->file['s3_key'])) {
            return;
        }

        try {
            Log::info("Attaching file to Pipedrive", [
                'deal_id' => $this->dealId,
                'file' => $this->file['file_name'] ?? null
            ]);

            $attached = $pipedrive->attachFileFromS3(
                $this->dealId,
                $this->file['s3_key'],
                $this->file['file_name'] ?? 'document.pdf'
            );

            Lead::trackAttachment($this->leadId, $this->file['s3_key'], $this->file['file_name'] ?? 'document.pdf', $attached);
        } catch (\Exception $e) {

            Lead::trackAttachment($this->leadId, $this->file['s3_key'], $this->file['file_name'] ?? 'document.pdf', false, $e->getMessage());

            Log::error("File attachment failed", [
                'deal_id' => $this->dealId,
                'file' => $this->file,
                'error' => $e->getMessage()
            ]);

            // Swallow so the rest of the chain still runs for the remaining files
        }
    }

    public function failed(\Exception $exception)
    {
        Log::critical("AttachFile Job Failed Completely", [
            'deal_id' => $this->dealId,
            'file' => $this->file,
            'error' => $exception->getMessage()
        ]);
    }
}
