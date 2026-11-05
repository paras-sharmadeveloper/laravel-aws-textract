<?php

namespace App\Support;

use App\Models\Lead;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

/**
 * SMTP and notification settings managed under Settings → Integrations → Email.
 * Values saved in the admin portal win; anything never saved falls back to .env.
 */
class MailSettings
{
    public const ENCRYPTION = [
        'tls' => 'STARTTLS (usually port 587)',
        'ssl' => 'SSL / TLS (usually port 465)',
        'auto' => 'Automatic (STARTTLS when the server offers it)',
        'none' => 'None (not recommended)',
    ];

    public const DEFAULT_SUBJECT = 'New submission received: {{business_name}}';

    public const DEFAULT_BODY = <<<'TXT'
Hi {{affiliate_first_name}},

Good news, a new application just came in through your link.

Merchant: {{owner_full_name}}
Business: {{business_name}}
Locations: {{number_of_locations}}
Submitted: {{submission_date_time}}

Our team is reviewing it now and will reach out to the merchant today. They'll typically see their number within 1–2 business days. We'll keep you posted as the deal moves forward.

Regards,

DPS Payments
TXT;

    public const DEFAULT_TEAM_SUBJECT = 'New Submission: {{business_name}} ({{number_of_locations}} locations) via {{affiliate_full_name}}';

    public const DEFAULT_TEAM_BODY = <<<'TXT'
New submission received. Same-day response required.

AFFILIATE
Name: {{affiliate_full_name}}
Email: {{affiliate_email}}

MERCHANT
Owner: {{owner_full_name}}
Business (DBA): {{business_name}}
Cell: {{owner_cell_phone}}
Email: {{owner_email}}
Locations: {{number_of_locations}}
New location: {{new_location_yes_no}}
Submitted: {{submission_date_time}}

View submission and files: {{submission_link}}
TXT;

    /** Placeholders usable in every subject and message, with what each one becomes. */
    public const PLACEHOLDERS = [
        'affiliate_first_name' => "Affiliate's first name",
        'affiliate_full_name' => "Affiliate's full name (\"Direct\" when there is none)",
        'affiliate_email' => "Affiliate's email",
        'owner_full_name' => "Merchant's full name",
        'owner_first_name' => "Merchant's first name",
        'business_name' => 'Business name (DBA)',
        'owner_cell_phone' => "Merchant's cell, e.g. (646) 555-0100",
        'owner_email' => "Merchant's email",
        'number_of_locations' => 'Number of locations',
        'new_location_yes_no' => 'Yes or No',
        'submission_date_time' => 'Date and time submitted',
        'submission_link' => 'Admin portal page with the files',
        'pipedrive_deal_link' => 'The Pipedrive deal',
    ];

    /**
     * All settings, with the password decrypted. Never send this to a view as-is.
     */
    public static function all(): array
    {
        $saved = Setting::group('mail');

        $password = $saved['password'] ?? null;
        if ($password !== null && $password !== '') {
            try {
                $password = Crypt::decryptString($password);
            } catch (\Throwable) {
                $password = null;
            }
        }

        // .env fallbacks are read through config(): env() returns null once config is cached
        $env = config('mail.mailers.smtp', []);

        return [
            'notify_affiliate' => (bool) ($saved['notify_affiliate'] ?? true),
            'host' => $saved['host'] ?? ($env['host'] ?? null),
            'port' => (int) ($saved['port'] ?? ($env['port'] ?? 587)),
            'encryption' => $saved['encryption'] ?? (($env['scheme'] ?? null) === 'smtps' ? 'ssl' : 'tls'),
            'username' => $saved['username'] ?? ($env['username'] ?? null),
            'password' => (array_key_exists('password', $saved) ? $password : ($env['password'] ?? null)) ?: null,
            'verify_peer' => (bool) ($saved['verify_peer'] ?? true),
            'timeout' => (int) ($saved['timeout'] ?? 15),
            'from_address' => $saved['from_address'] ?? config('mail.from.address'),
            'from_name' => $saved['from_name'] ?? (config('mail.from.name') ?: config('app.name')),
            'reply_to' => $saved['reply_to'] ?? null,
            'subject' => ($saved['subject'] ?? null) ?: self::DEFAULT_SUBJECT,
            'body' => ($saved['body'] ?? null) ?: self::DEFAULT_BODY,
            'notify_team' => (bool) ($saved['notify_team'] ?? true),
            // "bcc" held these addresses before the team email existed
            'team_recipients' => $saved['team_recipients'] ?? ($saved['bcc'] ?? null),
            'team_subject' => ($saved['team_subject'] ?? null) ?: self::DEFAULT_TEAM_SUBJECT,
            'team_body' => ($saved['team_body'] ?? null) ?: self::DEFAULT_TEAM_BODY,
            'timezone' => ($saved['timezone'] ?? null) ?: 'America/New_York',
        ];
    }

    /**
     * Save validated form values. A blank password keeps the stored one.
     */
    public static function save(array $values): void
    {
        if (!empty($values['clear_password'])) {
            $values['password'] = '';
        } elseif (blank($values['password'] ?? null)) {
            unset($values['password']);
        } else {
            $values['password'] = Crypt::encryptString($values['password']);
        }
        unset($values['clear_password']);

        foreach (['notify_affiliate', 'notify_team', 'verify_peer'] as $flag) {
            $values[$flag] = !empty($values[$flag]) ? '1' : '0';
        }

        Setting::putGroup('mail', $values);
    }

    public static function configured(?array $settings = null): bool
    {
        $settings ??= self::all();

        return filled($settings['host']) && filled($settings['port']) && filled($settings['from_address']);
    }

    /** @return string[] */
    public static function emailList(?string $value): array
    {
        return collect(preg_split('/[\s,;]+/', (string) $value))
            ->map(fn($email) => trim($email))
            ->filter(fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Point Laravel's "smtp" mailer at these settings. Queue workers live for hours,
     * so the cached mailer is dropped to pick up changes saved since it was built.
     */
    public static function apply(?array $settings = null): array
    {
        $settings ??= self::all();
        $encryption = $settings['encryption'];

        config([
            'mail.mailers.smtp' => array_merge(config('mail.mailers.smtp', []), [
                'transport' => 'smtp',
                'url' => null,
                'scheme' => $encryption === 'ssl' ? 'smtps' : 'smtp',
                'host' => $settings['host'],
                'port' => $settings['port'],
                'username' => $settings['username'] ?: null,
                'password' => $settings['password'] ?: null,
                'timeout' => $settings['timeout'],
                'auto_tls' => $encryption === 'none' ? 'false' : 'true',
                'require_tls' => $encryption === 'tls',
                'verify_peer' => $settings['verify_peer'] ? 'true' : 'false',
            ]),
            'mail.from.address' => $settings['from_address'],
            'mail.from.name' => $settings['from_name'],
        ]);

        Mail::purge('smtp');

        return $settings;
    }

    /**
     * Placeholder values for a lead, e.g. ['business_name' => 'Jane Bakery', ...].
     */
    public static function values(Lead $lead, string $timezone): array
    {
        $first = fn(?string $name) => strtok(trim((string) $name), ' ') ?: '';
        $affiliate = $lead->affiliate?->name;
        $phone = preg_replace('/\D/', '', (string) $lead->phone);

        return [
            'affiliate_first_name' => $first($affiliate) ?: 'there',
            'affiliate_full_name' => $affiliate ?: 'Direct',
            'affiliate_name' => $affiliate ?: 'Direct',
            'affiliate_email' => $lead->affiliate?->email ?: ($affiliate ? 'Not provided' : 'None (direct submission)'),
            'owner_full_name' => trim((string) $lead->owner_name),
            'owner_first_name' => $first($lead->owner_name),
            'business_name' => trim((string) $lead->business_name),
            'owner_cell_phone' => strlen($phone) === 10
                ? sprintf('(%s) %s-%s', substr($phone, 0, 3), substr($phone, 3, 3), substr($phone, 6))
                : (string) $lead->phone,
            'owner_phone' => (string) $lead->phone,
            'owner_email' => (string) $lead->email,
            'number_of_locations' => $lead->locations ? (string) $lead->locations : '1',
            'new_location_yes_no' => $lead->new_location ? 'Yes' : 'No',
            'submission_date_time' => ($lead->created_at ?? now())->copy()->timezone($timezone)->format('M j, Y \a\t g:i A T'),
            'submission_link' => $lead->exists ? route('admin.leads.show', $lead) : url('/admin'),
            'pipedrive_deal_link' => $lead->pipedriveDealUrl() ?: 'Not available',
        ];
    }

    /**
     * Fill {{placeholders}}; spaces inside the braces are allowed and unknown names are left as typed.
     */
    public static function render(string $template, array $values): string
    {
        return preg_replace_callback(
            '/\{\{\s*([a-z_]+)\s*\}\}/i',
            fn($m) => array_key_exists(strtolower($m[1]), $values) ? $values[strtolower($m[1])] : $m[0],
            $template
        );
    }
}
