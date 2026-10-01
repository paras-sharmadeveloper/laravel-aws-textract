<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Aws\S3\PostObjectV4;
use App\Jobs\PrepareUploadsJob;
use App\Models\{Affiliate, Lead};
use App\Support\UploadFields;

class UploadController extends Controller
{
    public function index()
    {
        return view('upload', ['affiliate' => null]);
    }

    /**
     * Referral link (e.g. /jhonrocha). Only affiliates created and active in the
     * admin portal resolve — any other slug is a 404.
     */
    public function affiliate(string $slug)
    {
        $affiliate = Affiliate::findActiveBySlug($slug);

        abort_unless($affiliate, 404);

        return view('upload', ['affiliate' => $affiliate]);
    }

    /**
     * Signed S3 form so the browser uploads a file straight to the bucket.
     * S3 itself enforces the exact key, the content type and the 10MB limit.
     */
    public function presign(Request $request)
    {
        $data = $request->validate([
            'field' => ['required', Rule::in(array_keys(UploadFields::FIELDS))],
            'name' => 'required|string|max:255',
            'size' => 'required|integer|min:1|max:' . UploadFields::MAX_BYTES,
            'type' => 'nullable|string|max:150',
        ]);

        $ext = UploadFields::extension($data['name']);

        if (!in_array($ext, UploadFields::FIELDS[$data['field']]['ext'])) {
            return response()->json(['message' => 'This file type is not accepted here.'], 422);
        }

        $key = 'incoming/' . $this->uploadToken($request) . '/' . Str::uuid() . '.' . $ext;
        $type = $data['type'] ?: 'application/octet-stream';
        $bucket = config('filesystems.disks.s3.bucket');

        $post = new PostObjectV4(
            Storage::disk('s3')->getClient(),
            $bucket,
            ['key' => $key, 'Content-Type' => $type],
            [
                ['bucket' => $bucket],
                ['eq', '$key', $key],
                ['eq', '$Content-Type', $type],
                ['content-length-range', 1, UploadFields::MAX_BYTES],
            ],
            '+30 minutes'
        );

        return response()->json([
            'url' => $post->getFormAttributes()['action'],
            'fields' => $post->getFormInputs(),
            'key' => $key,
        ]);
    }

    public function upload(Request $request)
    {
        // Only fallback files (when a direct upload failed) are sent through this request
        set_time_limit(600);

        $lead = null;

        try {
            $request->validate(array_merge([
                'owner_name' => 'required|string|max:255',
                'business_name' => 'required|string|max:255',
                'locations' => 'nullable|integer|min:1|max:10000',
                'new_location' => 'nullable|boolean',
                'affiliate' => 'nullable|string|max:100',
                'email' => 'required|email',
                'phone' => 'required|digits:10',
                'uploads' => 'nullable|json',
            ], UploadFields::rules()));

            $token = $this->uploadToken($request);
            $files = $this->collectFiles($request, $token);

            $dealFolder = $request->phone . '_' . Str::random(4);

            // Only a real, active affiliate is credited — the hidden field alone is not trusted
            $affiliate = Affiliate::findActiveBySlug($request->affiliate);

            $lead = Lead::create([
                'affiliate_id' => $affiliate?->id,
                'deal_folder' => $dealFolder,
                'owner_name' => $request->owner_name,
                'business_name' => $request->business_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'locations' => $request->locations,
                'new_location' => $request->boolean('new_location'),
                'status' => 'processing',
                'stage' => 'upload',
            ]);

            $result = [
                'lead_id' => $lead->id,
                'email' => $request->email,
                'phone' => $request->phone,
                'owner_name' => $request->owner_name,
                'business_name' => $request->business_name,
                'locations' => $request->locations,
                'new_location' => $request->boolean('new_location'),
                'affiliate' => $affiliate?->name,
                'documents' => [],
                'statements' => []
            ];

            $lead->update(['payload' => ['intake' => ['files' => $files, 'result' => $result]]]);

            PrepareUploadsJob::dispatch($lead->id);

            // A fresh upload folder for the next application from this browser
            $request->session()->forget('upload_token');

            return back()->with('success', 'Your documents have been uploaded successfully. Processing has started and the lead will be created within approximately 2 minutes.');
        } catch (\Exception $e) {

            $lead?->update(['status' => 'failed', 'error' => $e->getMessage()]);

            return back()->withInput()->with('error',  $e->getMessage());
        }
    }

    /**
     * Combine files uploaded straight to S3 (listed in the "uploads" JSON) with any
     * fallback files sent in this request, as field => [{key, name}].
     */
    private function collectFiles(Request $request, string $token): array
    {
        $prefix = "incoming/{$token}/";
        $manifest = json_decode($request->input('uploads') ?: '{}', true) ?: [];
        $files = [];

        if ($manifest) {
            $present = array_flip(Storage::disk('s3')->files(rtrim($prefix, '/')));

            foreach ($manifest as $field => $entries) {
                $config = UploadFields::FIELDS[$field] ?? null;

                if (!$config || !is_array($entries)) {
                    throw new \Exception('Unexpected upload field.');
                }

                foreach ($entries as $entry) {
                    $key = $entry['key'] ?? '';
                    $name = basename((string) ($entry['name'] ?? ''));

                    // Keys are only accepted from this browser's own upload folder
                    if (!str_starts_with($key, $prefix) || !in_array(UploadFields::extension($name), $config['ext'])) {
                        throw new \Exception('One of your files could not be verified. Please remove it and add it again.');
                    }

                    if (!isset($present[$key])) {
                        throw new \Exception("\"{$name}\" did not finish uploading. Please remove it and add it again.");
                    }

                    $files[$field][] = ['key' => $key, 'name' => $name];
                }
            }
        }

        foreach (UploadFields::FIELDS as $field => $config) {
            if (!$request->hasFile($field)) {
                continue;
            }

            foreach (array_filter(Arr::wrap($request->file($field))) as $file) {
                $name = $file->getClientOriginalName();
                $key = Storage::disk('s3')->putFileAs(
                    rtrim($prefix, '/'),
                    $file,
                    Str::uuid() . '.' . strtolower($file->getClientOriginalExtension())
                );

                $files[$field][] = ['key' => $key, 'name' => $name];
            }
        }

        foreach ($files as $field => $entries) {
            $config = UploadFields::FIELDS[$field];

            if (!$config['multi']) {
                // Single-file fields keep the most recent file
                $files[$field] = [end($entries)];
            } elseif (count($entries) > $config['max']) {
                throw new \Exception("You can upload up to {$config['max']} files for this section.");
            }
        }

        return $files;
    }

    private function uploadToken(Request $request): string
    {
        if (!$request->session()->has('upload_token')) {
            $request->session()->put('upload_token', Str::random(32));
        }

        return $request->session()->get('upload_token');
    }
}
