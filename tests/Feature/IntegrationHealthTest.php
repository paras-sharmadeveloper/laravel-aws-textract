<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IntegrationHealthTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'Test-Pass-123']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.pipedrive.base_url' => 'https://api.pipedrive.test/v1',
            'services.pipedrive.api_key' => 'secret-token-abcd',
            'services.pipedrive.pipeline_id' => 2,
            'services.pipedrive.stage_id' => 36,
        ]);
    }

    public function test_page_lists_integrations_with_masked_credentials(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/integrations')
            ->assertOk()
            ->assertSee('AWS S3')
            ->assertSee('Pipedrive')
            ->assertSee('Redis &amp; Horizon', false)
            ->assertSee('••••abcd')
            ->assertDontSee('secret-token-abcd');
    }

    public function test_guests_cannot_run_tests_and_unknown_integrations_404(): void
    {
        $this->postJson('/admin/integrations/pipedrive/test')->assertUnauthorized();
        $this->actingAs($this->admin())->postJson('/admin/integrations/nope/test')->assertNotFound();
    }

    public function test_pipedrive_connected(): void
    {
        Http::fake([
            '*/users/me*' => Http::response(['data' => ['name' => 'Desh', 'company_name' => 'DPS Payments']]),
            '*/stages/36*' => Http::response(['data' => ['name' => 'New lead', 'pipeline_id' => 2, 'pipeline_name' => 'Merchants']]),
        ]);

        $this->actingAs($this->admin())->postJson('/admin/integrations/pipedrive/test')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('summary', 'Connected to DPS Payments')
            ->assertJsonPath('checks.0.detail', 'Desh · DPS Payments')
            ->assertJsonPath('checks.1.detail', 'Merchants → New lead');
    }

    public function test_pipedrive_bad_token_fails(): void
    {
        Http::fake(['*/users/me*' => Http::response(['error' => 'unauthorized access'], 401)]);

        $this->actingAs($this->admin())->postJson('/admin/integrations/pipedrive/test')
            ->assertJsonPath('status', 'fail')
            ->assertJsonPath('checks.0.detail', 'HTTP 401: unauthorized access');
    }

    public function test_pipedrive_stage_outside_configured_pipeline_warns(): void
    {
        $this->actingAs($this->admin());

        Http::fake([
            '*/users/me*' => Http::response(['data' => ['name' => 'Desh', 'company_name' => 'DPS']]),
            '*/stages/36*' => Http::response(['data' => ['name' => 'Won', 'pipeline_id' => 9, 'pipeline_name' => 'Other']]),
        ]);

        $this->postJson('/admin/integrations/pipedrive/test')
            ->assertJsonPath('status', 'warn')
            ->assertJsonPath('checks.1.ok', null);
    }
}
