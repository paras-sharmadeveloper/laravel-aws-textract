<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\LeadSubmittedMail;
use App\Models\Affiliate;
use App\Models\Lead;
use App\Support\MailSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MailSettingsController extends Controller
{
    public function edit()
    {
        $settings = MailSettings::all();
        $hasPassword = filled($settings['password']);
        unset($settings['password']);

        return view('admin.integrations-mail', [
            'settings' => $settings,
            'hasPassword' => $hasPassword,
            'encryptions' => MailSettings::ENCRYPTION,
            // Keyed by the tag to type, e.g. "{{business_name}}" => "Business name"
            'placeholders' => collect(MailSettings::PLACEHOLDERS)->mapWithKeys(fn($label, $key) => ['{{' . $key . '}}' => $label])->all(),
            'defaults' => ['body' => MailSettings::DEFAULT_BODY, 'team_body' => MailSettings::DEFAULT_TEAM_BODY],
            'timezones' => \DateTimeZone::listIdentifiers(\DateTimeZone::AMERICA | \DateTimeZone::UTC),
            'affiliatesWithoutEmail' => Affiliate::active()->where(fn($q) => $q->whereNull('email')->orWhere('email', ''))->count(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'notify_affiliate' => 'nullable|boolean',
            'host' => 'required_if:notify_affiliate,1|nullable|string|max:255',
            'port' => 'required|integer|between:1,65535',
            'encryption' => ['required', Rule::in(array_keys(MailSettings::ENCRYPTION))],
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'clear_password' => 'nullable|boolean',
            'verify_peer' => 'nullable|boolean',
            'timeout' => 'required|integer|between:5,120',
            'from_address' => 'required_if:notify_affiliate,1|nullable|email|max:255',
            'from_name' => 'nullable|string|max:255',
            'reply_to' => 'nullable|email|max:255',
            'notify_team' => 'nullable|boolean',
            'team_recipients' => ['required_if:notify_team,1', 'nullable', 'string', 'max:1000', function ($attribute, $value, $fail) {
                $entries = array_filter(preg_split('/[\s,;]+/', (string) $value));
                $invalid = array_filter($entries, fn($e) => !filter_var($e, FILTER_VALIDATE_EMAIL));
                if ($invalid) {
                    $fail('These are not valid email addresses: ' . implode(', ', $invalid));
                }
            }],
            'subject' => 'nullable|string|max:200',
            'body' => 'nullable|string|max:5000',
            'team_subject' => 'nullable|string|max:200',
            'team_body' => 'nullable|string|max:5000',
            'timezone' => ['required', 'timezone:all'],
        ], [
            'host.required_if' => 'Enter the SMTP host to turn on affiliate emails.',
            'from_address.required_if' => 'Enter a "from" address to turn on affiliate emails.',
            'team_recipients.required_if' => 'Add at least one team address, or turn off the team email.',
        ]);

        MailSettings::save($data);

        return redirect()->route('admin.integrations.mail')->with('success', 'Email settings saved.');
    }

    /**
     * Send a sample "new lead" email with the saved settings.
     */
    public function test(Request $request)
    {
        $data = $request->validate(['to' => 'required|email', 'template' => 'nullable|in:affiliate,team']);
        $prefix = ($data['template'] ?? 'affiliate') === 'team' ? 'team_' : '';
        $settings = MailSettings::all();

        if (!MailSettings::configured($settings)) {
            return back()->with('error', 'Save the SMTP host, port and "from" address first.');
        }

        MailSettings::apply($settings);

        $lead = new Lead([
            'business_name' => 'Sample Coffee Co.',
            'owner_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '5555550100',
            'locations' => 2,
            'new_location' => true,
        ]);
        $lead->created_at = now();
        $lead->setRelation('affiliate', new Affiliate(['name' => 'Sample Affiliate', 'email' => 'affiliate@example.com']));
        $values = MailSettings::values($lead, $settings['timezone']);

        try {
            Mail::mailer('smtp')->to($data['to'])->send(new LeadSubmittedMail(
                '[Test] ' . MailSettings::render($settings["{$prefix}subject"], $values),
                MailSettings::render($settings["{$prefix}body"], $values),
                $settings['reply_to'] ?: null,
            ));
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'The test email could not be sent: ' . Str::limit($e->getMessage(), 300));
        }

        $which = $prefix ? 'team' : 'affiliate';

        return back()->with('success', "Test {$which} email sent to {$data['to']}. Check the inbox (and spam folder).");
    }
}
