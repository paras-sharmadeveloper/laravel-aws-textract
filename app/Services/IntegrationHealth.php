<?php

namespace App\Services;

use Aws\Exception\AwsException;
use Aws\Textract\TextractClient;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;
use OpenAI;

/**
 * Live connection checks for the services the application depends on,
 * shown under Settings → Integrations in the admin portal.
 *
 * Each check returns:
 *   status   ok | warn | fail
 *   summary  one line for the card
 *   checks   [{label, ok: true|false|null, detail}]
 */
class IntegrationHealth
{
    public const INTEGRATIONS = [
        's3' => ['name' => 'AWS S3', 'role' => 'Stores every uploaded document'],
        'textract' => ['name' => 'AWS Textract', 'role' => 'Reads text from IDs, checks and statements'],
        'pipedrive' => ['name' => 'Pipedrive', 'role' => 'Receives leads, deals and attachments'],
        'openai' => ['name' => 'OpenAI', 'role' => 'Extracts merchant details from the documents'],
        'queue' => ['name' => 'Redis & Horizon', 'role' => 'Runs the background processing pipeline'],
    ];

    private const QUEUES = ['default', 'ocr', 'Parse-create-lead', 'attachments'];

    public function run(string $integration): array
    {
        $started = microtime(true);

        try {
            $result = match ($integration) {
                's3' => $this->s3(),
                'textract' => $this->textract(),
                'pipedrive' => $this->pipedrive(),
                'openai' => $this->openai(),
                'queue' => $this->queue(),
            };
        } catch (\Throwable $e) {
            $result = $this->result([['Connection', false, $this->message($e)]], 'Could not connect');
        }

        $result['latency_ms'] = (int) round((microtime(true) - $started) * 1000);
        $result['tested_at'] = now()->toIso8601String();

        try {
            Cache::put("integration-health:{$integration}", $result, now()->addDays(7));
        } catch (\Throwable) {
            // The cache lives in Redis; a Redis outage is reported by the queue check itself
        }

        return $result;
    }

    public function last(string $integration): ?array
    {
        try {
            return Cache::get("integration-health:{$integration}");
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Non-secret configuration shown on each card, with credentials masked.
     */
    public function config(string $integration): array
    {
        return match ($integration) {
            's3' => [
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Region' => config('filesystems.disks.s3.region'),
                'Access key' => $this->mask(config('filesystems.disks.s3.key')),
            ],
            'textract' => [
                'Region' => config('filesystems.disks.s3.region'),
                'Access key' => $this->mask(config('filesystems.disks.s3.key')),
            ],
            'pipedrive' => [
                'API URL' => config('services.pipedrive.base_url'),
                'API token' => $this->mask(config('services.pipedrive.api_key')),
                'Pipeline / stage' => config('services.pipedrive.pipeline_id') . ' / ' . config('services.pipedrive.stage_id'),
            ],
            'openai' => [
                'Model' => 'gpt-4o-mini',
                'API key' => $this->mask(config('services.openai.api_key')),
            ],
            'queue' => [
                'Queue driver' => config('queue.default'),
                'Redis host' => config('database.redis.default.host') . ':' . config('database.redis.default.port'),
            ],
        };
    }

    private function s3(): array
    {
        $bucket = config('filesystems.disks.s3.bucket');

        if (blank(config('filesystems.disks.s3.key')) || blank($bucket)) {
            return $this->result([['Credentials', false, 'AWS_ACCESS_KEY_ID or AWS_BUCKET is not set']], 'Not configured');
        }

        $client = Storage::disk('s3')->getClient();
        $key = '_healthcheck/' . Str::uuid() . '.txt';
        $checks = [];

        // Listing (rather than HEAD) returns a readable reason, e.g. InvalidAccessKeyId
        $checks[] = $this->step('Bucket reachable', fn() => tap("Found {$bucket}", fn() => $client->listObjectsV2(['Bucket' => $bucket, 'Prefix' => '_healthcheck/', 'MaxKeys' => 1])));

        if ($checks[0]['ok'] === false) {
            return $this->result($checks, 'Could not connect');
        }

        $checks[] = $this->step('Write a file', fn() => tap('Uploaded a test file', fn() => $client->putObject(['Bucket' => $bucket, 'Key' => $key, 'Body' => 'ok'])));
        $checks[] = $this->step('Read it back', fn() => (string) $client->getObject(['Bucket' => $bucket, 'Key' => $key])['Body'] === 'ok' ? 'Contents match' : throw new \RuntimeException('Contents did not match'));
        $checks[] = $this->step('Delete it', fn() => tap('Test file removed', fn() => $client->deleteObject(['Bucket' => $bucket, 'Key' => $key])));

        // Browser uploads go straight to the bucket, which only works with CORS allowing this site
        $origin = rtrim(config('app.url'), '/');
        try {
            $rules = $client->getBucketCors(['Bucket' => $bucket])['CORSRules'] ?? [];
            $allowed = collect($rules)->contains(fn($rule) => in_array('POST', $rule['AllowedMethods'] ?? [])
                && collect($rule['AllowedOrigins'] ?? [])->contains(fn($o) => $o === '*' || $o === $origin));
            $checks[] = ['label' => 'Browser uploads (CORS)', 'ok' => $allowed ? true : null, 'detail' => $allowed
                ? "POST allowed from {$origin}"
                : "No rule allows POST from {$origin} — files will fall back to the slower form upload"];
        } catch (AwsException $e) {
            $missing = $e->getAwsErrorCode() === 'NoSuchCORSConfiguration';
            $checks[] = ['label' => 'Browser uploads (CORS)', 'ok' => $missing ? null : false, 'detail' => $missing
                ? 'No CORS configured — files will fall back to the slower form upload'
                : $this->message($e)];
        }

        return $this->result($checks, 'Bucket is readable and writable');
    }

    private function textract(): array
    {
        if (blank(config('filesystems.disks.s3.key'))) {
            return $this->result([['Credentials', false, 'AWS_ACCESS_KEY_ID is not set']], 'Not configured');
        }

        $client = new TextractClient([
            'region' => config('filesystems.disks.s3.region'),
            'version' => 'latest',
            'http' => ['timeout' => 10, 'connect_timeout' => 5],
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);

        // Asking for a job that does not exist is free and still proves the
        // credentials are valid and allowed to call Textract
        try {
            $client->getDocumentTextDetection(['JobId' => str_repeat('0', 64)]);
            $check = ['Credentials & permission', true, 'Textract accepted the request'];
        } catch (AwsException $e) {
            $check = in_array($e->getAwsErrorCode(), ['InvalidJobIdException', 'InvalidParameterException'])
                ? ['Credentials & permission', true, 'Authenticated in ' . config('filesystems.disks.s3.region')]
                : ['Credentials & permission', false, $this->message($e)];
        }

        return $this->result([$check], 'Textract is reachable');
    }

    private function pipedrive(): array
    {
        $base = rtrim((string) config('services.pipedrive.base_url'), '/');
        $token = config('services.pipedrive.api_key');

        if (blank($base) || blank($token)) {
            return $this->result([['Credentials', false, 'PIPEDRIVE_BASE_URL or PIPEDRIVE_API_KEY is not set']], 'Not configured');
        }

        $client = Http::timeout(10)->acceptJson();
        $checks = [];

        $me = $client->get("{$base}/users/me", ['api_token' => $token]);
        if (!$me->successful()) {
            $checks[] = ['label' => 'API token', 'ok' => false, 'detail' => 'HTTP ' . $me->status() . ': ' . ($me->json('error') ?? 'request rejected')];
            return $this->result($checks, 'Could not connect');
        }

        $user = $me->json('data');
        $checks[] = ['label' => 'API token', 'ok' => true, 'detail' => trim(($user['name'] ?? '') . ' · ' . ($user['company_name'] ?? ''), ' ·')];

        $stageId = config('services.pipedrive.stage_id');
        $stage = $client->get("{$base}/stages/{$stageId}", ['api_token' => $token]);
        $checks[] = $stage->successful() && $stage->json('data')
            ? [
                'label' => 'Pipeline & stage',
                'ok' => (string) $stage->json('data.pipeline_id') === (string) config('services.pipedrive.pipeline_id') ? true : null,
                'detail' => $stage->json('data.pipeline_name') . ' → ' . $stage->json('data.name')
                    . ((string) $stage->json('data.pipeline_id') === (string) config('services.pipedrive.pipeline_id') ? '' : ' (stage is not in PIPEDRIVE_PIPELINE_ID)'),
            ]
            : ['label' => 'Pipeline & stage', 'ok' => false, 'detail' => "Stage {$stageId} was not found"];

        return $this->result($checks, 'Connected to ' . ($user['company_name'] ?? 'Pipedrive'));
    }

    private function openai(): array
    {
        $key = config('services.openai.api_key');

        if (blank($key)) {
            return $this->result([['API key', false, 'OPENAI_API_KEY is not set']], 'Not configured');
        }

        $client = OpenAI::factory()
            ->withApiKey($key)
            ->withHttpClient(new GuzzleClient(['timeout' => 10, 'connect_timeout' => 5]))
            ->make();

        $model = $client->models()->retrieve('gpt-4o-mini');

        return $this->result([['API key & model', true, "Model {$model->id} is available"]], 'Connected');
    }

    private function queue(): array
    {
        $checks = [];

        $checks[] = $this->step('Redis', fn() => Redis::connection()->ping() ? 'Responding' : throw new \RuntimeException('No reply'));

        $masters = app(MasterSupervisorRepository::class)->all();
        $running = collect($masters)->filter(fn($m) => ($m->status ?? '') === 'running');
        $checks[] = $running->isNotEmpty()
            ? ['label' => 'Horizon workers', 'ok' => true, 'detail' => 'Running on ' . $running->pluck('name')->implode(', ')]
            : ['label' => 'Horizon workers', 'ok' => false, 'detail' => collect($masters)->isEmpty()
                ? 'Horizon is not running — uploads will wait in the queue'
                : 'Horizon is ' . collect($masters)->pluck('status')->unique()->implode(', ')];

        $sizes = collect(self::QUEUES)->mapWithKeys(fn($q) => [$q => Queue::size($q)]);
        $backlog = $sizes->sum();
        $checks[] = [
            'label' => 'Waiting jobs',
            'ok' => $backlog > 100 ? null : true,
            'detail' => $sizes->map(fn($n, $q) => "{$q}: {$n}")->implode(' · '),
        ];

        return $this->result($checks, $backlog ? "{$backlog} job(s) waiting" : 'Queues are clear');
    }

    private function step(string $label, callable $fn): array
    {
        try {
            return ['label' => $label, 'ok' => true, 'detail' => (string) $fn()];
        } catch (\Throwable $e) {
            return ['label' => $label, 'ok' => false, 'detail' => $this->message($e)];
        }
    }

    private function result(array $checks, string $okSummary): array
    {
        $checks = array_map(fn($c) => isset($c['label']) ? $c : ['label' => $c[0], 'ok' => $c[1], 'detail' => $c[2]], $checks);
        $failed = collect($checks)->first(fn($c) => $c['ok'] === false);
        $warned = collect($checks)->contains(fn($c) => $c['ok'] === null);

        return [
            'status' => $failed ? 'fail' : ($warned ? 'warn' : 'ok'),
            'summary' => $failed ? $failed['label'] . ' failed' : ($warned ? $okSummary . ', with warnings' : $okSummary),
            'checks' => $checks,
        ];
    }

    private function message(\Throwable $e): string
    {
        if ($e instanceof AwsException) {
            return $e->getAwsErrorCode()
                ? trim($e->getAwsErrorCode() . ': ' . ($e->getAwsErrorMessage() ?: 'request failed'))
                : 'AWS returned HTTP ' . ($e->getStatusCode() ?: 'error') . ($e->getStatusCode() == 403 ? ' (access denied — check the keys and IAM permissions)' : '');
        }

        return Str::limit($e->getMessage(), 300);
    }

    private function mask(?string $value): string
    {
        return blank($value) ? 'Not set' : '••••' . substr($value, -4);
    }
}
