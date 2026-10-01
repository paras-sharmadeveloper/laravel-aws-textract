<?php

namespace Tests\Feature;

use App\Jobs\ParseAndCreateLeadJob;
use App\Jobs\ProcessOcrJob;
use App\Models\Lead;
use App\Services\StatementDateService;
use App\Services\TextractService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class ParallelOcrTest extends TestCase
{
    use RefreshDatabase;

    public function test_files_are_ocrd_individually_and_reassembled_for_lead_creation(): void
    {
        Queue::fake([ParseAndCreateLeadJob::class]);

        $textract = Mockery::mock(TextractService::class);
        $textract->shouldReceive('extractPdf')->andReturnUsing(fn($key) => "text of {$key}");
        $textract->shouldReceive('extractImage')->andReturn('image text');
        $this->app->instance(TextractService::class, $textract);

        $dates = Mockery::mock(StatementDateService::class);
        $dates->shouldReceive('extractPeriod')->andReturnUsing(
            fn($text) => str_contains($text, 'unknown') ? null : ['month' => 'jan', 'year' => 2026]
        );
        $this->app->instance(StatementDateService::class, $dates);

        $lead = Lead::create(['email' => 'a@b.co', 'phone' => '1234567890']);

        $statements = [];
        for ($i = 0; $i < 35; $i++) {
            $statements[] = ['category' => 'bank', 's3_key' => "s/{$i}.pdf", 'original_name' => "orig{$i}.pdf", 'ext' => 'pdf'];
        }
        $statements[] = ['category' => 'ccp', 's3_key' => 's/unknown.pdf', 'original_name' => 'mystery.pdf', 'ext' => 'pdf'];

        ProcessOcrJob::dispatch([
            'lead_id' => $lead->id,
            'email' => 'a@b.co',
            'phone' => '1234567890',
            'documents' => [
                'VC.pdf' => ['s3_keys' => ['u/VC.pdf']],
                'Pics.pdf' => ['s3_keys' => ['u/Pics.pdf']],
            ],
            'statements' => $statements,
        ]);

        Queue::assertPushedOn('Parse-create-lead', ParseAndCreateLeadJob::class, function ($job) {
            $result = (fn() => $this->result)->call($job);

            $names = array_column($result['statements'], 'final_filename');

            return $result['documents']['VC.pdf']['raw_text'] === 'text of u/VC.pdf'
                && !isset($result['documents']['Pics.pdf']['raw_text'])
                && $names[0] === 'Bank_Statement_Jan_2026.pdf'
                && $names[1] === 'Bank_Statement_Jan_2026_2.pdf'
                && end($names) === 'mystery.pdf'
                && count(array_unique($names)) === 36;
        });
    }
}
