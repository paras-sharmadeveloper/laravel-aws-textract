<?php

namespace Tests\Feature;

use App\Jobs\AttachFilesToPipedriveJob;
use App\Jobs\PrepareUploadsJob;
use App\Jobs\ProcessOcrJob;
use App\Models\Affiliate;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AffiliateLeadAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'desh@dpspayments.com', 'password' => 'Test-Pass-123']);
    }

    public function test_root_form_renders_without_referral(): void
    {
        $this->get('/')->assertOk()->assertSee('Your application.')->assertDontSee('Referred by');
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
}
