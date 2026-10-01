<?php

namespace Tests\Feature;

use App\Jobs\PrepareUploadsJob;
use App\Jobs\ProcessOcrJob;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DirectUploadTest extends TestCase
{
    use RefreshDatabase;

    private const PDF = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj 3 0 obj<</Type/Page/MediaBox[0 0 10 10]/Parent 2 0 R>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF";

    private function fields(array $extra = []): array
    {
        return array_merge([
            'owner_name' => 'Jane Doe',
            'business_name' => 'Jane Bakery',
            'email' => 'jane@example.com',
            'phone' => '6465550100',
        ], $extra);
    }

    public function test_presign_returns_a_signed_s3_form_scoped_to_the_session(): void
    {
        $response = $this->withSession(['upload_token' => 'tok123'])->postJson('/upload/presign', [
            'field' => 'statement_bank',
            'name' => 'March.PDF',
            'size' => 2048,
            'type' => 'application/pdf',
        ])->assertOk();

        $this->assertStringStartsWith('incoming/tok123/', $response['key']);
        $this->assertStringEndsWith('.pdf', $response['key']);
        $this->assertSame($response['key'], $response['fields']['key']);
        $this->assertArrayHasKey('Policy', $response['fields']);
        $this->assertArrayHasKey('X-Amz-Signature', $response['fields']);
    }

    public function test_presign_rejects_wrong_type_size_and_field(): void
    {
        $this->postJson('/upload/presign', ['field' => 'driving_license', 'name' => 'x.exe', 'size' => 10])->assertStatus(422);
        $this->postJson('/upload/presign', ['field' => 'driving_license', 'name' => 'x.pdf', 'size' => 11 * 1024 * 1024])->assertStatus(422);
        $this->postJson('/upload/presign', ['field' => 'secret', 'name' => 'x.pdf', 'size' => 10])->assertStatus(422);
    }

    public function test_directly_uploaded_files_are_accepted_by_key(): void
    {
        Storage::fake('s3');
        Queue::fake();
        Storage::disk('s3')->put('incoming/tok123/a.pdf', self::PDF);
        Storage::disk('s3')->put('incoming/tok123/b.pdf', self::PDF);

        $this->withSession(['upload_token' => 'tok123'])->post('/upload', $this->fields([
            'uploads' => json_encode([
                'driving_license' => [['key' => 'incoming/tok123/a.pdf', 'name' => 'license.pdf']],
                'statement_bank' => [['key' => 'incoming/tok123/b.pdf', 'name' => 'march.pdf']],
            ]),
        ]))->assertSessionHas('success')->assertSessionMissing('upload_token');

        $files = Lead::sole()->payload['intake']['files'];
        $this->assertSame('license.pdf', $files['driving_license'][0]['name']);
        Queue::assertPushed(PrepareUploadsJob::class);
    }

    public function test_keys_from_another_session_or_missing_uploads_are_rejected(): void
    {
        Storage::fake('s3');
        Queue::fake();
        Storage::disk('s3')->put('incoming/someone-else/a.pdf', self::PDF);

        $this->withSession(['upload_token' => 'tok123'])->post('/upload', $this->fields([
            'uploads' => json_encode(['driving_license' => [['key' => 'incoming/someone-else/a.pdf', 'name' => 'a.pdf']]]),
        ]))->assertSessionHas('error');

        $this->withSession(['upload_token' => 'tok123'])->post('/upload', $this->fields([
            'uploads' => json_encode(['driving_license' => [['key' => 'incoming/tok123/never.pdf', 'name' => 'a.pdf']]]),
        ]))->assertSessionHas('error');

        Queue::assertNotPushed(PrepareUploadsJob::class);
    }

    public function test_prepare_job_stores_files_like_the_old_upload_and_starts_ocr(): void
    {
        Storage::fake('s3');
        Queue::fake([ProcessOcrJob::class]);

        $this->withSession(['upload_token' => 'tok123'])->post('/upload', $this->fields([
            'driving_license' => UploadedFile::fake()->createWithContent('id.pdf', self::PDF),
            'bank_doc' => UploadedFile::fake()->createWithContent('vc.pdf', self::PDF),
            'statement_bank' => [
                UploadedFile::fake()->createWithContent('jan.pdf', self::PDF),
                UploadedFile::fake()->createWithContent('feb.pdf', self::PDF),
            ],
        ]))->assertSessionHas('success');

        $lead = Lead::with('documents')->sole();
        $this->assertSame('ocr', $lead->stage);
        $this->assertSame(['ID.pdf', 'VC.pdf'], array_keys($lead->payload['documents']));
        $this->assertCount(2, $lead->payload['statements']);
        $this->assertSame('jan.pdf', $lead->payload['statements'][0]['original_name']);
        $this->assertCount(4, $lead->documents);

        $disk = Storage::disk('s3');
        $disk->assertExists("uploads/{$lead->deal_folder}/ID.pdf");
        $this->assertSame([], $disk->files('incoming/tok123'), 'raw uploads are cleaned up');

        Queue::assertPushed(ProcessOcrJob::class, fn($job) => (fn() => $this->result['lead_id'])->call($job) === $lead->id);
    }

    public function test_prepare_job_rejects_disguised_files(): void
    {
        Storage::fake('s3');
        Queue::fake([ProcessOcrJob::class]);
        Storage::disk('s3')->put('incoming/tok123/a.pdf', 'MZ this is really an executable');

        $this->withSession(['upload_token' => 'tok123'])->post('/upload', $this->fields([
            'uploads' => json_encode(['driving_license' => [['key' => 'incoming/tok123/a.pdf', 'name' => 'a.pdf']]]),
        ]));

        $lead = Lead::sole();
        $this->assertSame('failed', $lead->status);
        $this->assertStringContainsString('Invalid upload', $lead->error);
        $this->assertTrue($lead->canRetry());
        Queue::assertNotPushed(ProcessOcrJob::class);
    }
}
