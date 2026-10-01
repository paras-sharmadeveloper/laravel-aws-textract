<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\{AttachFilesToPipedriveJob, PrepareUploadsJob, ProcessOcrJob};
use App\Models\{Affiliate, Lead, LeadDocument};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $leads = Lead::with('affiliate')
            ->withCount('documents')
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->affiliate, fn($q, $id) => $q->where('affiliate_id', $id))
            ->when($request->q, function ($q, $term) {
                $q->where(fn($q) => $q
                    ->where('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('owner_name', 'like', "%{$term}%")
                    ->orWhere('business_name', 'like', "%{$term}%"));
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $counts = Lead::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.leads.index', [
            'leads' => $leads,
            'counts' => $counts,
            'affiliates' => Affiliate::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Lead $lead)
    {
        $lead->load('affiliate', 'documents');

        return view('admin.leads.show', compact('lead'));
    }

    /**
     * Opens a stored document through a short-lived signed S3 URL, so links
     * in the portal keep working no matter how old the lead is.
     */
    public function document(Lead $lead, LeadDocument $document)
    {
        abort_unless($document->lead_id === $lead->id, 404);

        return redirect()->away(Storage::disk('s3')->temporaryUrl($document->s3_key, now()->addMinutes(15)));
    }

    public function retry(Lead $lead)
    {
        if (!$lead->canRetry()) {
            return back()->with('error', 'This lead cannot be retried right now.');
        }

        // Deal already exists in Pipedrive: only re-send documents that did not make it
        if ($lead->pipedrive_deal_id) {
            $documents = $lead->documents()->where('attach_status', '!=', 'attached')->get();

            LeadDocument::whereIn('id', $documents->pluck('id'))->update(['attach_status' => 'pending', 'attach_error' => null]);

            $lead->update(['status' => 'processing', 'stage' => 'attachments', 'error' => null, 'attempts' => $lead->attempts + 1]);

            AttachFilesToPipedriveJob::dispatch(
                $lead->pipedrive_deal_id,
                $documents->map(fn($doc) => ['file_name' => $doc->file_name, 's3_key' => $doc->s3_key])->all(),
                $lead->id
            )->onQueue('attachments');

            return back()->with('success', "Re-sending {$documents->count()} document(s) to Pipedrive deal #{$lead->pipedrive_deal_id}.");
        }

        // Failed while preparing the browser's uploads: prepare them again
        if (isset($lead->payload['intake'])) {
            $lead->update(['status' => 'processing', 'stage' => 'upload', 'error' => null, 'attempts' => $lead->attempts + 1]);

            PrepareUploadsJob::dispatch($lead->id);

            return back()->with('success', 'Lead re-queued. The uploaded files will be prepared again.');
        }

        // No deal yet: run the whole pipeline again from the documents already in S3
        $lead->documents()->update(['attach_status' => 'pending', 'attach_error' => null, 'attached_at' => null]);
        $lead->update(['status' => 'processing', 'stage' => 'ocr', 'error' => null, 'attempts' => $lead->attempts + 1]);

        ProcessOcrJob::dispatch($lead->payload);

        return back()->with('success', 'Lead re-queued. It will be processed again from the stored documents.');
    }
}
