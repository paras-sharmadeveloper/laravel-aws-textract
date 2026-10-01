<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Services\IntakeService;
use App\Support\UploadFields;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Takes the raw files the browser uploaded straight to S3 (incoming/...),
 * validates and stores them exactly as the old upload request did, then
 * starts the OCR pipeline. Keeps the web request itself instant.
 */
class PrepareUploadsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $tries = 2;
    public $backoff = [30];
    public $timeout = 900;

    public function __construct(protected int $leadId) {}

    public function handle(IntakeService $intake)
    {
        $lead = Lead::findOrFail($this->leadId);
        $intakePayload = $lead->payload['intake'] ?? null;

        if (!$intakePayload) {
            throw new \Exception('Lead has no pending uploads to prepare.');
        }

        Lead::track($lead->id, ['status' => 'processing', 'stage' => 'upload', 'error' => null]);

        $workDir = storage_path('app/tmp/intake/' . $lead->id . '_' . uniqid());
        File::ensureDirectoryExists($workDir);

        try {
            // Pull each raw upload down so the existing conversions/merges can run on it
            $files = [];

            foreach ($intakePayload['files'] as $field => $entries) {
                $config = UploadFields::FIELDS[$field];

                foreach ($entries as $i => $entry) {
                    $local = $workDir . '/' . $field . '_' . $i . '.' . UploadFields::extension($entry['name']);
                    $stream = Storage::disk('s3')->readStream($entry['key']);

                    if (!$stream) {
                        throw new \Exception("Uploaded file is missing from storage: {$entry['name']}");
                    }

                    file_put_contents($local, $stream);

                    $file = new UploadedFile($local, $entry['name'], null, null, true);

                    if ($config['multi']) {
                        $files[$field][] = $file;
                    } else {
                        $files[$field] = $file;
                    }
                }
            }

            $validator = Validator::make($files, UploadFields::rules());

            if ($validator->fails()) {
                throw new \Exception('Invalid upload: ' . $validator->errors()->first());
            }

            $stored = $intake->store($files, $lead->deal_folder);

            $result = $intakePayload['result'];
            $result['documents'] = $stored['documents'];
            $result['statements'] = $stored['statements'];

            Log::info("uploadme files ", ['file' => $result['documents'], 'statements' => $result['statements']]);

            $lead->documents()->delete();
            $lead->documents()->createMany($stored['lead_documents']);
            $lead->update(['payload' => $result, 'stage' => 'ocr']);

            ProcessOcrJob::dispatch($result);

            // Raw copies are no longer needed once stored under uploads/
            Storage::disk('s3')->delete(collect($intakePayload['files'])->flatten(1)->pluck('key')->all());
        } finally {
            File::deleteDirectory($workDir);
        }
    }

    public function failed(\Throwable $exception)
    {
        Lead::track($this->leadId, [
            'status' => 'failed',
            'stage' => 'upload',
            'error' => 'Preparing uploads failed: ' . $exception->getMessage(),
        ]);
    }
}
