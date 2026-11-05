<?php

namespace App\Jobs;

use App\Mail\LeadSubmittedMail;
use App\Models\Lead;
use App\Support\MailSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Emails the affiliate once their lead's Pipedrive deal exists. Runs on its own
 * job so a mail outage never holds up document processing.
 */
class SendAffiliateLeadEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    /** Seconds between attempts, e.g. while the SMTP server is briefly down. */
    public array $backoff = [60, 300];

    public function __construct(public int $leadId) {}

    public function handle(): void
    {
        $lead = Lead::with('affiliate')->find($this->leadId);
        $email = $lead?->affiliate?->email;

        // Already notified (e.g. the lead was resynced as a new deal), or nobody to tell
        if (!$lead || blank($email) || $lead->affiliate_notified_at) {
            return;
        }

        $settings = MailSettings::all();

        if (!$settings['notify_affiliate']) {
            return;
        }

        if (!MailSettings::configured($settings)) {
            Log::warning('Affiliate lead email skipped: SMTP is not configured', ['lead_id' => $lead->id]);
            return;
        }

        MailSettings::apply($settings);
        $values = MailSettings::values($lead, $settings['timezone']);

        Mail::mailer('smtp')
            ->to($email, $lead->affiliate->name)
            ->bcc(MailSettings::bccList($settings['bcc']))
            ->send(new LeadSubmittedMail(
                MailSettings::render($settings['subject'], $values),
                MailSettings::render($settings['body'], $values),
                $settings['reply_to'] ?: null,
            ));

        Lead::track($lead->id, ['affiliate_notified_at' => now()]);

        Log::info('Affiliate lead email sent', ['lead_id' => $lead->id, 'affiliate_id' => $lead->affiliate_id]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Affiliate lead email failed', ['lead_id' => $this->leadId, 'error' => $e->getMessage()]);
    }
}
