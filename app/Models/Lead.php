<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Lead extends Model
{
    protected $fillable = [
        'affiliate_id', 'deal_folder', 'owner_name', 'business_name', 'email', 'phone',
        'locations', 'new_location', 'status', 'stage', 'error', 'payload',
        'pipedrive_person_id', 'pipedrive_org_id', 'pipedrive_deal_id', 'attempts', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'new_location' => 'boolean',
            'payload' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function documents()
    {
        return $this->hasMany(LeadDocument::class);
    }

    /**
     * Update a lead's tracking columns from inside the queue pipeline.
     * Tracking must never break document processing, so failures are only logged.
     */
    public static function track(?int $leadId, array $attributes): void
    {
        if (!$leadId) {
            return;
        }

        try {
            static::whereKey($leadId)->update($attributes);
        } catch (\Throwable $e) {
            Log::warning('Lead tracking update failed', ['lead_id' => $leadId, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Record the result of one Pipedrive attachment and close out the lead
     * once every document has been attempted.
     */
    public static function trackAttachment(?int $leadId, string $s3Key, string $fileName, bool $ok, ?string $error = null): void
    {
        if (!$leadId) {
            return;
        }

        try {
            LeadDocument::where('lead_id', $leadId)->where('s3_key', $s3Key)->update([
                'file_name' => $fileName,
                'attach_status' => $ok ? 'attached' : 'failed',
                'attach_error' => $ok ? null : ($error ?: 'Pipedrive did not accept the file'),
                'attached_at' => $ok ? now() : null,
            ]);

            static::find($leadId)?->syncAttachmentStatus();
        } catch (\Throwable $e) {
            Log::warning('Lead attachment tracking failed', ['lead_id' => $leadId, 'error' => $e->getMessage()]);
        }
    }

    public function syncAttachmentStatus(): void
    {
        $documents = $this->documents()->get(['attach_status']);

        if ($documents->contains('attach_status', 'pending')) {
            return;
        }

        $failed = $documents->where('attach_status', 'failed')->count();

        if ($failed > 0) {
            $this->update([
                'status' => 'failed',
                'stage' => 'attachments',
                'error' => "{$failed} of {$documents->count()} documents failed to attach to the Pipedrive deal.",
            ]);

            return;
        }

        $this->update([
            'status' => 'completed',
            'stage' => 'done',
            'error' => null,
            'completed_at' => now(),
        ]);
    }

    public function canRetry(): bool
    {
        if ($this->status === 'completed' || empty($this->payload)) {
            return false;
        }

        // A lead still "processing" long after submission most likely lost its worker
        return $this->status === 'failed' || $this->updated_at->lt(now()->subMinutes(30));
    }

    public function pipedriveDealUrl(): ?string
    {
        $base = rtrim((string) config('services.pipedrive.app_url'), '/');

        return $this->pipedrive_deal_id && $base ? "{$base}/deal/{$this->pipedrive_deal_id}" : null;
    }
}
