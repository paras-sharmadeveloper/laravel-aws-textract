<?php

namespace Tests\Feature;

use App\Jobs\ParseAndCreateLeadJob;
use App\Jobs\SendAffiliateLeadEmailJob;
use App\Jobs\SendLeadEmailJob;
use App\Mail\LeadSubmittedMail;
use App\Models\Affiliate;
use App\Models\Lead;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\GeoService;
use App\Services\GPTService;
use App\Services\PipedriveService;
use App\Support\MailSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AffiliateLeadEmailTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'Test-Pass-123', 'role_id' => Role::admin()->id]);
    }

    private function configureMail(array $overrides = []): void
    {
        MailSettings::save(array_merge([
            'notify_affiliate' => true,
            'host' => 'smtp.example.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'mailer@example.com',
            'password' => 'smtp-secret-pass',
            'verify_peer' => true,
            'timeout' => 15,
            'from_address' => 'no-reply@example.com',
            'from_name' => 'DPS Payments',
            'reply_to' => 'support@example.com',
            'notify_team' => true,
            'team_recipients' => 'owner@example.com, sales@example.com',
            'subject' => 'New lead: {{business_name}} for {{ affiliate_name }}',
            'body' => '',
            'timezone' => 'America/New_York',
        ], $overrides));
    }

    private function submit(string $affiliate): void
    {
        $this->post('/upload', [
            'owner_name' => 'Jane Doe',
            'business_name' => 'Jane Bakery',
            'email' => 'jane@example.com',
            'phone' => '6465550100',
            'affiliate' => $affiliate,
            'driving_license' => UploadedFile::fake()->create('id.pdf', 100, 'application/pdf'),
            'bank_doc' => UploadedFile::fake()->create('vc.pdf', 100, 'application/pdf'),
            'statement_bank' => [UploadedFile::fake()->create('jan.pdf', 100, 'application/pdf')],
        ])->assertSessionHas('success');
    }

    public function test_submission_does_not_email_before_the_deal_exists(): void
    {
        Storage::fake('s3');
        Queue::fake();
        Affiliate::create(['name' => 'Jhon Rocha', 'slug' => 'jhonrocha', 'email' => 'jhon@example.com']);

        $this->submit('jhonrocha');

        Queue::assertNotPushed(SendLeadEmailJob::class);
    }

    public function test_job_sends_the_template_to_the_affiliate_once(): void
    {
        Mail::fake();
        $this->configureMail();
        $affiliate = Affiliate::create(['name' => 'Jhon Rocha', 'slug' => 'jhonrocha', 'email' => 'jhon@example.com']);
        $lead = Lead::create(['affiliate_id' => $affiliate->id, 'owner_name' => 'Jane Doe', 'business_name' => 'Jane Bakery',
            'email' => 'jane@example.com', 'phone' => '6465550100', 'locations' => 3, 'status' => 'processing']);
        $lead->forceFill(['created_at' => '2026-11-05 19:30:00'])->save();

        (new SendAffiliateLeadEmailJob($lead->id))->handle();
        (new SendAffiliateLeadEmailJob($lead->id))->handle();

        Mail::assertSent(LeadSubmittedMail::class, 1);
        Mail::assertSent(LeadSubmittedMail::class, function (LeadSubmittedMail $mail) {
            return $mail->hasTo('jhon@example.com')
                && $mail->hasReplyTo('support@example.com')
                && $mail->subjectLine === 'New lead: Jane Bakery for Jhon Rocha'
                && str_starts_with($mail->bodyText, "Hi Jhon,\n\nGood news")
                && str_contains($mail->bodyText, "Merchant: Jane Doe\nBusiness: Jane Bakery\nLocations: 3\nSubmitted: Nov 5, 2026 at 2:30 PM EST")
                && str_ends_with($mail->bodyText, "Regards,\n\nDPS Payments");
        });

        $this->assertNotNull($lead->fresh()->affiliate_notified_at);
        $this->assertSame('smtp.example.com', config('mail.mailers.smtp.host'));
        $this->assertSame('smtp-secret-pass', config('mail.mailers.smtp.password'));
        $this->assertSame('no-reply@example.com', config('mail.from.address'));
    }

    public function test_template_renders_paragraphs_and_escapes_html(): void
    {
        $mail = new LeadSubmittedMail('Subject', "Hi <b>Jhon</b>,\n\nMerchant: A\nBusiness: B");

        $mail->assertSeeInHtml('Hi &lt;b&gt;Jhon&lt;/b&gt;,', false)
            ->assertSeeInHtml('Merchant: A<br />', false)
            ->assertSeeInText("Merchant: A\nBusiness: B");
    }

    public function test_placeholders_fill_and_unknown_ones_stay(): void
    {
        $this->assertSame('Hi Jo — {{nope}}', MailSettings::render('Hi {{ affiliate_first_name }} — {{nope}}', ['affiliate_first_name' => 'Jo']));
    }

    public function test_job_sends_nothing_when_turned_off_or_not_configured(): void
    {
        Mail::fake();
        config(['mail.mailers.smtp.host' => null, 'mail.from.address' => null]);
        $affiliate = Affiliate::create(['name' => 'Jhon', 'slug' => 'jhon', 'email' => 'jhon@example.com']);
        $lead = Lead::create(['affiliate_id' => $affiliate->id, 'owner_name' => 'A', 'business_name' => 'B',
            'email' => 'a@example.com', 'phone' => '6465550100', 'status' => 'processing']);

        (new SendAffiliateLeadEmailJob($lead->id))->handle();

        $this->configureMail(['notify_affiliate' => false]);
        (new SendAffiliateLeadEmailJob($lead->id))->handle();

        Mail::assertNothingSent();
    }

    public function test_admin_saves_settings_with_encrypted_password_kept_when_blank(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/integrations/email')->assertOk()->assertSee('SMTP server');

        $form = [
            'notify_affiliate' => 1, 'host' => 'smtp.gmail.com', 'port' => 587, 'timezone' => 'America/Chicago',
            'encryption' => 'tls', 'username' => 'me@gmail.com', 'password' => 'app-password-123',
            'verify_peer' => 1, 'timeout' => 15, 'from_address' => 'me@gmail.com', 'from_name' => 'DPS',
            'notify_team' => 1, 'team_recipients' => 'owner@example.com', 'subject' => '',
        ];

        $this->actingAs($admin)->put('/admin/integrations/email', $form)->assertRedirect('/admin/integrations/email');

        $stored = Setting::where('key', 'mail.password')->value('value');
        $this->assertNotSame('app-password-123', $stored);
        $this->assertSame('app-password-123', MailSettings::all()['password']);
        $this->assertSame(MailSettings::DEFAULT_SUBJECT, MailSettings::all()['subject']);
        $this->assertSame(MailSettings::DEFAULT_BODY, MailSettings::all()['body']);
        $this->assertSame('America/Chicago', MailSettings::all()['timezone']);

        // Saving again with a blank password keeps it, and the page never prints it
        $this->actingAs($admin)->put('/admin/integrations/email', ['password' => ''] + $form);
        $this->assertSame('app-password-123', MailSettings::all()['password']);
        $this->actingAs($admin)->get('/admin/integrations/email')->assertDontSee('app-password-123');
        $this->actingAs($admin)->get('/admin/integrations')->assertSee('Email (SMTP)')->assertSee('••••-123')->assertDontSee('app-password-123');

        $this->actingAs($admin)->put('/admin/integrations/email', ['clear_password' => 1] + $form);
        $this->assertNull(MailSettings::all()['password']);
    }

    public function test_settings_are_validated(): void
    {
        $this->actingAs($this->admin())->put('/admin/integrations/email', [
            'notify_affiliate' => 1, 'host' => '', 'port' => 99999, 'encryption' => 'bogus',
            'timeout' => 15, 'from_address' => 'not-an-email', 'notify_team' => 1, 'team_recipients' => 'ok@example.com, nope', 'timezone' => 'Mars/Base',
        ])->assertSessionHasErrors(['host', 'port', 'encryption', 'from_address', 'team_recipients', 'timezone']);
    }

    public function test_view_only_role_cannot_change_email_settings(): void
    {
        $role = Role::create(['name' => 'Viewer', 'permissions' => ['integrations.view']]);
        $user = User::create(['name' => 'V', 'email' => 'v@example.com', 'password' => 'Test-Pass-123', 'role_id' => $role->id]);

        $this->actingAs($user)->get('/admin/integrations')->assertOk()->assertDontSee(route('admin.integrations.mail'));
        $this->actingAs($user)->get('/admin/integrations/email')->assertForbidden();
        $this->actingAs($user)->put('/admin/integrations/email', [])->assertForbidden();
    }

    public function test_admin_can_send_a_test_email(): void
    {
        Mail::fake();
        $this->configureMail();

        $this->actingAs($this->admin())->post('/admin/integrations/email/send-test', ['to' => 'me@example.com'])
            ->assertSessionHas('success');

        Mail::assertSent(LeadSubmittedMail::class, fn($mail) => $mail->hasTo('me@example.com') && str_starts_with($mail->subjectLine, '[Test]'));
    }

    public function test_health_check_reports_missing_settings(): void
    {
        config(['mail.mailers.smtp.host' => null, 'mail.from.address' => null]);

        $this->actingAs($this->admin())->postJson('/admin/integrations/mail/test')
            ->assertOk()
            ->assertJsonPath('status', 'fail')
            ->assertJsonPath('summary', 'Settings failed');
    }

    public function test_pipeline_queues_the_email_after_the_deal_is_created(): void
    {
        Queue::fake();
        config(['services.pipedrive.base_url' => 'https://pd.test/v1', 'services.pipedrive.api_key' => 'k']);
        Http::fake(['pd.test/v1/deals*' => Http::response(['data' => ['id' => 33]]), '*' => Http::response(['data' => ['id' => 1, 'items' => []]])]);
        $this->mock(GPTService::class)->shouldReceive('parse')->andReturn([]);
        $this->mock(GeoService::class)->shouldReceive('geocode')->andReturn(null);

        $affiliate = Affiliate::create(['name' => 'Jhon', 'slug' => 'jhon', 'email' => 'jhon@example.com']);
        $notified = Lead::create(['affiliate_id' => $affiliate->id, 'owner_name' => 'A', 'business_name' => 'B', 'email' => 'a@example.com',
            'phone' => '6465550100', 'status' => 'processing', 'affiliate_notified_at' => now()]);
        $fresh = Lead::create(['affiliate_id' => $affiliate->id, 'owner_name' => 'A', 'business_name' => 'B', 'email' => 'a@example.com',
            'phone' => '6465550100', 'status' => 'processing']);

        foreach ([$fresh, $notified] as $lead) {
            app()->call([new ParseAndCreateLeadJob(['lead_id' => $lead->id, 'email' => 'a@example.com', 'phone' => '6465550100',
                'owner_name' => 'A', 'business_name' => 'B', 'documents' => [], 'statements' => []]), 'handle']);
        }

        // Fresh lead: affiliate + team. Lead already emailed to its affiliate: team only
        Queue::assertPushed(SendLeadEmailJob::class, 3);
        Queue::assertPushed(SendLeadEmailJob::class, fn($job) => $job->leadId === $fresh->id && $job->audience === 'affiliate');
        Queue::assertPushed(SendLeadEmailJob::class, fn($job) => $job->leadId === $fresh->id && $job->audience === 'team');
        Queue::assertNotPushed(SendLeadEmailJob::class, fn($job) => $job->leadId === $notified->id && $job->audience === 'affiliate');
    }

    public function test_deal_gets_source_channel_option_and_affiliate_link(): void
    {
        config(['services.pipedrive.base_url' => 'https://pd.test/v1', 'services.pipedrive.api_key' => 'k']);
        Http::fake([
            'pd.test/v1/dealFields*' => Http::response(['data' => [
                ['key' => 'title', 'options' => null],
                ['key' => 'channel', 'options' => [['id' => 41, 'label' => 'Indeed'], ['id' => 47, 'label' => 'Affiliate - MCA Broker']]],
            ]]),
            'pd.test/v1/deals*' => Http::response(['data' => ['id' => 33]]),
            '*' => Http::response(['data' => ['id' => 1, 'items' => []]]),
        ]);

        $affiliate = Affiliate::create(['name' => 'Desh', 'slug' => 'desh', 'source_channel' => 'Affiliate - MCA Broker']);
        app(PipedriveService::class)->processLead(['business_name' => 'Biz'], $affiliate, ['person_id' => 7, 'org_id' => 8]);

        Http::assertSent(fn($request) => str_contains($request->url(), '/deals?')
            && $request['channel'] === 47
            && $request['channel_id'] === url('desh'));

        // No channel chosen: the default (Affiliate - MCA Broker) is used
        $affiliate->source_channel = null;
        $this->assertSame(['channel_id' => url('desh'), 'channel' => 47], app(PipedriveService::class)->sourceFields($affiliate));
    }

    public function test_deal_without_matching_channel_still_gets_the_link(): void
    {
        config(['services.pipedrive.base_url' => 'https://pd.test/v1', 'services.pipedrive.api_key' => 'k']);
        Http::fake([
            'pd.test/v1/dealFields*' => Http::response(['data' => [['key' => 'channel', 'options' => [['id' => 41, 'label' => 'Indeed']]]]]),
            '*' => Http::response(['data' => ['id' => 1, 'items' => []]]),
        ]);

        $affiliate = Affiliate::create(['name' => 'Desh', 'slug' => 'desh', 'source_channel' => 'Affiliate - Accountant']);

        $this->assertSame(['channel_id' => url('desh')], app(PipedriveService::class)->sourceFields($affiliate));
        $this->assertSame([], app(PipedriveService::class)->sourceFields(null));
    }

    public function test_admin_sets_affiliate_source_channel(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/affiliates', ['name' => 'Desh', 'slug' => 'desh', 'source_channel' => 'Affiliate - MCA Broker'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Affiliate - MCA Broker', Affiliate::sole()->source_channel);

        $this->actingAs($admin)->post('/admin/affiliates', ['name' => 'X', 'slug' => 'x', 'source_channel' => 'Made Up'])
            ->assertSessionHasErrors('source_channel');

        $this->actingAs($admin)->post('/admin/affiliates', ['name' => 'No Pick', 'slug' => 'nopick'])->assertSessionHasNoErrors();
        $this->assertSame('Affiliate - MCA Broker', Affiliate::where('slug', 'nopick')->value('source_channel'));
        $this->actingAs($admin)->get('/admin/affiliates/' . Affiliate::where('slug', 'nopick')->value('id') . '/edit')
            ->assertSee('<option value="Affiliate - MCA Broker" selected', false);
    }

    public function test_team_email_uses_its_template_and_recipients(): void
    {
        Mail::fake();
        $this->configureMail();
        $affiliate = Affiliate::create(['name' => 'Jhon Rocha', 'slug' => 'jhonrocha', 'email' => 'jhon@example.com']);
        $lead = Lead::create(['affiliate_id' => $affiliate->id, 'owner_name' => 'Jane Doe', 'business_name' => 'Jane Bakery',
            'email' => 'jane@example.com', 'phone' => '6465550100', 'locations' => 3, 'new_location' => true, 'status' => 'processing']);

        (new SendLeadEmailJob($lead->id, SendLeadEmailJob::TEAM))->handle();
        (new SendLeadEmailJob($lead->id, SendLeadEmailJob::TEAM))->handle();

        Mail::assertSent(LeadSubmittedMail::class, 1);
        Mail::assertSent(LeadSubmittedMail::class, function (LeadSubmittedMail $mail) use ($lead) {
            return $mail->hasTo('owner@example.com') && $mail->hasTo('sales@example.com') && !$mail->hasTo('jhon@example.com')
                && $mail->hasReplyTo('jhon@example.com')
                && $mail->subjectLine === 'New Submission: Jane Bakery (3 locations) via Jhon Rocha'
                && str_contains($mail->bodyText, "AFFILIATE\nName: Jhon Rocha\nEmail: jhon@example.com")
                && str_contains($mail->bodyText, "Cell: (646) 555-0100\nEmail: jane@example.com\nLocations: 3\nNew location: Yes")
                && str_contains($mail->bodyText, 'View submission and files: ' . route('admin.leads.show', $lead));
        });
        $this->assertNotNull($lead->fresh()->team_notified_at);
        $this->assertNull($lead->fresh()->affiliate_notified_at);
    }

    public function test_team_email_for_direct_submission_and_when_off(): void
    {
        Mail::fake();
        $this->configureMail();
        $lead = Lead::create(['owner_name' => 'Jane Doe', 'business_name' => 'Jane Bakery', 'email' => 'jane@example.com',
            'phone' => '6465550100', 'status' => 'processing']);

        (new SendLeadEmailJob($lead->id, SendLeadEmailJob::TEAM))->handle();

        Mail::assertSent(LeadSubmittedMail::class, fn($mail) => $mail->subjectLine === 'New Submission: Jane Bakery (1 locations) via Direct'
            && str_contains($mail->bodyText, "Name: Direct\nEmail: None (direct submission)")
            && str_contains($mail->bodyText, 'New location: No'));

        $other = Lead::create(['owner_name' => 'A', 'business_name' => 'B', 'email' => 'a@example.com', 'phone' => '6465550100', 'status' => 'processing']);
        $this->configureMail(['notify_team' => false]);
        (new SendLeadEmailJob($other->id, SendLeadEmailJob::TEAM))->handle();

        Mail::assertSent(LeadSubmittedMail::class, 1);
    }

    public function test_old_affiliate_job_class_still_runs(): void
    {
        Mail::fake();
        $this->configureMail();
        $affiliate = Affiliate::create(['name' => 'Jhon', 'slug' => 'jhon', 'email' => 'jhon@example.com']);
        $lead = Lead::create(['affiliate_id' => $affiliate->id, 'owner_name' => 'A', 'business_name' => 'B',
            'email' => 'a@example.com', 'phone' => '6465550100', 'status' => 'processing']);

        (new SendAffiliateLeadEmailJob($lead->id))->handle();

        Mail::assertSent(LeadSubmittedMail::class, fn($mail) => $mail->hasTo('jhon@example.com'));
    }

    public function test_old_bcc_setting_becomes_team_recipients(): void
    {
        Setting::create(['key' => 'mail.bcc', 'value' => 'owner@example.com']);

        $this->assertSame('owner@example.com', MailSettings::all()['team_recipients']);
    }

    public function test_admin_can_send_a_test_team_email(): void
    {
        Mail::fake();
        $this->configureMail();

        $this->actingAs($this->admin())->post('/admin/integrations/email/send-test', ['to' => 'me@example.com', 'template' => 'team'])
            ->assertSessionHas('success');

        Mail::assertSent(LeadSubmittedMail::class, fn($mail) => str_starts_with($mail->subjectLine, '[Test] New Submission: Sample Coffee Co. (2 locations) via Sample Affiliate'));
    }
}
