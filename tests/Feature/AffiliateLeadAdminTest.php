<?php

namespace Tests\Feature;

use App\Jobs\AttachFilesToPipedriveJob;
use App\Jobs\ParseAndCreateLeadJob;
use App\Jobs\PrepareUploadsJob;
use App\Jobs\ProcessOcrJob;
use App\Models\Affiliate;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\PipedriveService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AffiliateLeadAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'desh@dpspayments.com', 'password' => 'Test-Pass-123', 'role_id' => Role::admin()->id]);
    }

    public function test_root_form_renders_without_referral(): void
    {
        $this->get('/')->assertOk()->assertSee('Your application.')->assertDontSee('Referred by')
            ->assertSee('Owner contact info')->assertSee('Tax ID <span class="opt">(optional)</span>', false)->assertSee('0/7');
    }

    public function test_active_affiliate_link_shows_name_and_hidden_field(): void
    {
        Affiliate::create(['name' => 'Jhon Rocha', 'slug' => 'jhonrocha', 'email' => 'jhon@example.com']);

        $this->get('/jhonrocha')
            ->assertOk()
            ->assertSee('Referred by', false)
            ->assertSee('Jhon Rocha')
            ->assertSee('Contact Jhon Rocha')
            ->assertSee('name="affiliate" value="jhonrocha"', false);
    }

    public function test_unknown_or_disabled_affiliate_links_404(): void
    {
        Affiliate::create(['name' => 'Off', 'slug' => 'off', 'is_active' => false]);

        $this->get('/fakeperson')->assertNotFound();
        $this->get('/off')->assertNotFound();
    }

    public function test_submission_creates_lead_with_documents_and_affiliate(): void
    {
        Storage::fake('s3');
        Queue::fake();
        $affiliate = Affiliate::create(['name' => 'Jhon Rocha', 'slug' => 'jhonrocha']);

        $this->from('/jhonrocha')->post('/upload', [
            'owner_name' => 'Jane Doe',
            'business_name' => 'Jane Bakery',
            'email' => 'jane@example.com',
            'phone' => '6465550100',
            'locations' => 2,
            'new_location' => 0,
            'affiliate' => 'jhonrocha',
            'driving_license' => UploadedFile::fake()->create('id.pdf', 100, 'application/pdf'),
            'bank_doc' => UploadedFile::fake()->create('vc.pdf', 100, 'application/pdf'),
            'statement_bank' => [
                UploadedFile::fake()->create('jan.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('feb.pdf', 100, 'application/pdf'),
            ],
        ])->assertRedirect('/jhonrocha')->assertSessionHas('success');

        $lead = Lead::sole();
        $this->assertSame($affiliate->id, $lead->affiliate_id);
        $this->assertSame('Jane Bakery', $lead->business_name);
        $this->assertSame('upload', $lead->stage);
        $this->assertSame($lead->id, $lead->payload['intake']['result']['lead_id']);

        // Files sent with the form are parked raw under incoming/ for the background job
        $files = collect($lead->payload['intake']['files']);
        $this->assertSame(['driving_license', 'bank_doc', 'statement_bank'], $files->keys()->all());
        $this->assertCount(2, $files['statement_bank']);
        $files->flatten(1)->each(fn($f) => Storage::disk('s3')->assertExists($f['key']));

        Queue::assertPushed(PrepareUploadsJob::class);
    }

    public function test_tampered_affiliate_field_is_not_credited(): void
    {
        Storage::fake('s3');
        Queue::fake();

        $this->post('/upload', [
            'owner_name' => 'Jane Doe',
            'business_name' => 'Jane Bakery',
            'email' => 'jane@example.com',
            'phone' => '6465550100',
            'affiliate' => 'someone-made-up',
        ])->assertSessionHas('success');

        $this->assertNull(Lead::sole()->affiliate_id);
    }

    public function test_attachment_results_close_out_the_lead(): void
    {
        $lead = Lead::create(['email' => 'a@b.co', 'phone' => '1234567890', 'stage' => 'attachments', 'payload' => ['x' => 1]]);
        $lead->documents()->createMany([
            ['category' => 'ID', 'file_name' => 'ID.pdf', 's3_key' => 'k1'],
            ['category' => 'VC', 'file_name' => 'VC.pdf', 's3_key' => 'k2'],
        ]);

        Lead::trackAttachment($lead->id, 'k1', 'ID.pdf', true);
        $this->assertSame('processing', $lead->fresh()->status);

        Lead::trackAttachment($lead->id, 'k2', 'VC.pdf', false, 'boom');
        $this->assertSame('failed', $lead->fresh()->status);
        $this->assertStringContainsString('1 of 2', $lead->fresh()->error);
    }

    public function test_admin_requires_login_and_accepts_seeded_credentials(): void
    {
        $this->admin();

        $this->get('/admin')->assertRedirect('/admin/login');
        $this->post('/admin/login', ['email' => 'desh@dpspayments.com', 'password' => 'wrong'])->assertSessionHas('error');
        $this->post('/admin/login', ['email' => 'desh@dpspayments.com', 'password' => 'Test-Pass-123'])->assertRedirect('/admin');
        $this->get('/admin')->assertOk()->assertSee('Leads');
    }

    public function test_horizon_is_only_for_signed_in_admins(): void
    {
        $this->get('/horizon')->assertRedirect('/admin/login');
        $this->actingAs($this->admin())->get('/horizon')->assertOk();
    }

    public function test_admin_creates_affiliate_with_generated_slug(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/affiliates', ['name' => 'Jhon Rocha', 'slug' => '', 'is_active' => 1])
            ->assertRedirect('/admin/affiliates');

        $this->assertDatabaseHas('affiliates', ['slug' => 'jhonrocha', 'is_active' => true]);
        $this->get('/jhonrocha')->assertOk();

        $this->post('/admin/affiliates', ['name' => 'Bad', 'slug' => 'admin'])->assertSessionHasErrors('slug');
        $this->post('/admin/affiliates', ['name' => 'Dupe', 'slug' => 'jhonrocha'])->assertSessionHasErrors('slug');
    }

    public function test_admin_pages_render(): void
    {
        $affiliate = Affiliate::create(['name' => 'Jhon Rocha', 'slug' => 'jhonrocha']);
        $lead = Lead::create(['email' => 'a@b.co', 'phone' => '1234567890', 'affiliate_id' => $affiliate->id, 'status' => 'failed', 'stage' => 'pipedrive', 'error' => 'Pipedrive API Error: 500', 'payload' => ['lead_id' => 1]]);
        $lead->documents()->create(['category' => 'ID', 'file_name' => 'ID.pdf', 's3_key' => 'uploads/x/ID.pdf']);

        $this->actingAs($this->admin());
        $this->get('/admin')->assertOk()->assertSee('a@b.co');
        $this->get('/admin?status=failed&affiliate=' . $affiliate->id)->assertOk()->assertSee('a@b.co');
        $this->get("/admin/leads/{$lead->id}")->assertOk()->assertSee('Pipedrive API Error: 500')->assertSee('Restart processing');
        $this->get('/admin/affiliates')->assertOk()->assertSee(url('jhonrocha'));
        $this->get("/admin/affiliates/{$affiliate->id}/edit")->assertOk();
        $this->get('/admin/account')->assertOk();
    }

    public function test_retry_restarts_pipeline_or_resends_failed_attachments(): void
    {
        Queue::fake();
        $this->actingAs($this->admin());

        $noDeal = Lead::create(['email' => 'a@b.co', 'phone' => '1234567890', 'status' => 'failed', 'stage' => 'ocr', 'payload' => ['lead_id' => 1]]);
        $this->post("/admin/leads/{$noDeal->id}/retry")->assertSessionHas('success');
        Queue::assertPushed(ProcessOcrJob::class);
        $this->assertSame(2, $noDeal->fresh()->attempts);

        $withDeal = Lead::create(['email' => 'a@b.co', 'phone' => '1234567890', 'status' => 'failed', 'stage' => 'attachments', 'pipedrive_deal_id' => 55, 'payload' => ['lead_id' => 2]]);
        $withDeal->documents()->createMany([
            ['category' => 'ID', 'file_name' => 'ID.pdf', 's3_key' => 'k1', 'attach_status' => 'attached'],
            ['category' => 'VC', 'file_name' => 'VC.pdf', 's3_key' => 'k2', 'attach_status' => 'failed'],
        ]);
        $this->post("/admin/leads/{$withDeal->id}/retry")->assertSessionHas('success');
        Queue::assertPushed(AttachFilesToPipedriveJob::class);
        $this->assertSame('pending', $withDeal->documents()->where('s3_key', 'k2')->value('attach_status'));
        $this->assertSame('attached', $withDeal->documents()->where('s3_key', 'k1')->value('attach_status'));
    }

    public function test_resync_sends_ocr_result_straight_to_pipedrive(): void
    {
        Queue::fake();
        $this->actingAs($this->admin());

        $lead = Lead::create(['email' => 'a@b.co', 'phone' => '1234567890', 'business_name' => 'Biz', 'status' => 'failed', 'stage' => 'pipedrive', 'payload' => ['lead_id' => 1], 'ocr_result' => ['lead_id' => 1, 'documents' => []]]);
        $lead->documents()->create(['category' => 'ID', 'file_name' => 'ID.pdf', 's3_key' => 'k1', 'attach_status' => 'failed']);

        $this->get('/admin')->assertSee('Resync to Pipedrive');
        $this->get("/admin/leads/{$lead->id}")->assertSee('Resync to Pipedrive');

        $this->post("/admin/leads/{$lead->id}/retry")->assertSessionHas('success');
        Queue::assertPushed(ParseAndCreateLeadJob::class);
        Queue::assertNotPushed(ProcessOcrJob::class);
        $this->assertSame('pipedrive', $lead->fresh()->stage);
        $this->assertSame('pending', $lead->documents()->value('attach_status'));
    }

    public function test_process_lead_reuses_person_and_org_from_a_failed_run(): void
    {
        config(['services.pipedrive.base_url' => 'https://pd.test/v1', 'services.pipedrive.api_key' => 'k']);
        Http::fake(['pd.test/v1/deals*' => Http::response(['data' => ['id' => 33]]), '*' => Http::response(['data' => ['id' => 1]])]);

        $ids = app(PipedriveService::class)->processLead(['business_name' => 'Biz'], null, ['person_id' => 7, 'org_id' => 8]);

        $this->assertSame(['person_id' => 7, 'org_id' => 8, 'deal_id' => 33], $ids);
        Http::assertSentCount(1);
    }

    public function test_stuck_leads_show_retry_buttons_in_list(): void
    {
        $this->actingAs($this->admin());

        $ocr = Lead::create(['email' => 'ocr@b.co', 'phone' => '1234567890', 'status' => 'processing', 'stage' => 'ocr', 'payload' => ['lead_id' => 1]]);
        $attach = Lead::create(['email' => 'att@b.co', 'phone' => '1234567890', 'status' => 'processing', 'stage' => 'attachments', 'pipedrive_deal_id' => 55, 'payload' => ['lead_id' => 2]]);
        Lead::whereKey([$ocr->id, $attach->id])->update(['updated_at' => now()->subHour()]);

        $this->get('/admin')->assertSee('Restart processing')->assertSee('Retry failed documents');
    }

    public function test_killed_attachment_job_fails_the_remaining_documents(): void
    {
        $lead = Lead::create(['email' => 'a@b.co', 'phone' => '1234567890', 'status' => 'processing', 'stage' => 'attachments', 'pipedrive_deal_id' => 55, 'payload' => ['lead_id' => 1]]);
        $lead->documents()->createMany([
            ['category' => 'ID', 'file_name' => 'ID.pdf', 's3_key' => 'k1', 'attach_status' => 'attached'],
            ['category' => 'VC', 'file_name' => 'VC.pdf', 's3_key' => 'k2'],
            ['category' => 'TAX_ID', 'file_name' => 'TAX.pdf', 's3_key' => 'k3'],
        ]);

        (new \App\Jobs\AttachFileToPipedriveJob(55, ['s3_key' => 'k2'], $lead->id))->failed(new \RuntimeException('timed out'));

        $lead->refresh();
        $this->assertSame('failed', $lead->status);
        $this->assertSame(2, $lead->documents()->where('attach_status', 'failed')->count());
        $this->actingAs($this->admin())->get("/admin/leads/{$lead->id}")->assertSee('Retry failed documents');
    }

    public function test_completed_lead_can_be_resynced_as_a_new_deal(): void
    {
        Queue::fake();
        $this->actingAs($this->admin());

        $lead = Lead::create(['email' => 'a@b.co', 'phone' => '1234567890', 'business_name' => 'Biz', 'status' => 'completed', 'stage' => 'done', 'pipedrive_person_id' => 5, 'pipedrive_org_id' => 6, 'pipedrive_deal_id' => 7, 'payload' => ['lead_id' => 1], 'ocr_result' => ['lead_id' => 1, 'documents' => []]]);
        $lead->documents()->create(['category' => 'ID', 'file_name' => 'ID.pdf', 's3_key' => 'k1', 'attach_status' => 'attached']);

        $this->get('/admin')->assertSee('Resync to Pipedrive');
        $this->get("/admin/leads/{$lead->id}")->assertSee('Resync to Pipedrive');

        $this->post("/admin/leads/{$lead->id}/resync")->assertSessionHas('success');
        Queue::assertPushed(ParseAndCreateLeadJob::class);

        $lead->refresh();
        $this->assertNull($lead->pipedrive_deal_id);
        $this->assertNull($lead->pipedrive_person_id);
        $this->assertSame('processing', $lead->status);
        $this->assertSame('pending', $lead->documents()->value('attach_status'));
    }

    public function test_resync_is_refused_while_lead_is_still_processing(): void
    {
        Queue::fake();
        $this->actingAs($this->admin());

        $lead = Lead::create(['email' => 'a@b.co', 'phone' => '1234567890', 'status' => 'processing', 'stage' => 'ocr', 'payload' => ['lead_id' => 1]]);

        $this->post("/admin/leads/{$lead->id}/resync")->assertSessionHas('error');
        Queue::assertNothingPushed();
    }

    public function test_leads_are_soft_deleted_and_can_be_restored(): void
    {
        $this->actingAs($this->admin());

        $lead = Lead::create(['email' => 'gone@b.co', 'phone' => '1234567890', 'business_name' => 'Gone Biz', 'status' => 'completed', 'stage' => 'done', 'payload' => ['lead_id' => 1]]);

        $this->get('/admin')->assertSee('Delete this lead?');

        $this->delete("/admin/leads/{$lead->id}")->assertRedirect('/admin');
        $this->assertSoftDeleted($lead);

        $this->get('/admin')->assertDontSee('gone@b.co');
        $this->get('/admin?status=deleted')->assertOk()->assertSee('gone@b.co')->assertSee('Restore');
        $this->get("/admin/leads/{$lead->id}")->assertOk()->assertSee('kept here for history');
        $this->post("/admin/leads/{$lead->id}/retry")->assertNotFound();

        $this->post("/admin/leads/{$lead->id}/restore")->assertRedirect("/admin/leads/{$lead->id}");
        $this->assertNotSoftDeleted($lead);
    }

    public function test_deleting_leads_needs_permission(): void
    {
        $role = Role::create(['name' => 'Viewer', 'permissions' => ['leads.view']]);
        $user = User::factory()->create(['role_id' => $role->id]);
        $lead = Lead::create(['email' => 'a@b.co', 'phone' => '1234567890', 'status' => 'completed', 'payload' => ['lead_id' => 1]]);

        $this->actingAs($user);
        $this->get('/admin')->assertDontSee('Delete this lead?');
        $this->delete("/admin/leads/{$lead->id}")->assertForbidden();
        $this->assertNotSoftDeleted($lead);
    }

    public function test_password_update(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/account/password', [
                'current_password' => 'Test-Pass-123',
                'password' => 'NewPass123',
                'password_confirmation' => 'NewPass123',
            ])->assertSessionHas('success');

        $this->post('/admin/logout');
        $this->post('/admin/login', ['email' => 'desh@dpspayments.com', 'password' => 'NewPass123'])->assertRedirect('/admin');
    }

    public function test_affiliate_page_shows_both_email_and_phone(): void
    {
        Affiliate::create(['name' => 'Jane Doe', 'slug' => 'janedoe', 'email' => 'jane@example.com', 'phone' => '(555) 555-0100']);

        $this->get('/janedoe')
            ->assertOk()
            ->assertSee('href="mailto:jane@example.com"', false)
            ->assertSee('href="tel:5555550100"', false)
            ->assertSee('(555) 555-0100');
    }
}
