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
 * Emails about a lead once its Pipedrive deal exists:
 *   affiliate — the affiliate whose link it came through
 *   team      — the internal "Team recipients" from the email settings
 * Each audience is emailed once per lead, so a resync never sends again.
 */
class SendLeadEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const AFFILIATE = 'affiliate';

    public const TEAM = 'team';

    public int $tries = 3;

    public int $timeout = 60;

    /** Seconds between attempts, e.g. while the SMTP server is briefly down. */
    public array $backoff = [60, 300];

    public function __construct(public int $leadId, public string $audience = self::AFFILIATE) {}

    public function handle(): void
    {
        $lead = Lead::with('affiliate')->find($this->leadId);
        $column = "{$this->audience}_notified_at";

        if (!$lead || $lead->{$column}) {
            return;
        }

        $settings = MailSettings::all();
        $team = $this->audience === self::TEAM;

        $recipients = $team
            ? MailSettings::emailList($settings['team_recipients'])
            : array_filter([$lead->affiliate?->email]);

        if (!$recipients || !$settings[$team ? 'notify_team' : 'notify_affiliate']) {
            return;
        }

        if (!MailSettings::configured($settings)) {
            Log::warning('Lead email skipped: SMTP is not configured', ['lead_id' => $lead->id, 'audience' => $this->audience]);
            return;
        }

        MailSettings::apply($settings);
        $values = MailSettings::values($lead, $settings['timezone']);
        $prefix = $team ? 'team_' : '';

        $message = new LeadSubmittedMail(
            MailSettings::render($settings["{$prefix}subject"], $values),
            MailSettings::render($settings["{$prefix}body"], $values),
            // The team replies straight to the affiliate; affiliates reply to the configured address
            $team ? ($lead->affiliate?->email ?: null) : ($settings['reply_to'] ?: null),
        );

        $mailer = Mail::mailer('smtp');
        $team
            ? $mailer->to($recipients)->send($message)
            : $mailer->to($recipients[0], $lead->affiliate->name)->send($message);

        Lead::track($lead->id, [$column => now()]);

        Log::info('Lead email sent', ['lead_id' => $lead->id, 'audience' => $this->audience]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Lead email failed', ['lead_id' => $this->leadId, 'audience' => $this->audience, 'error' => $e->getMessage()]);
    }
}
