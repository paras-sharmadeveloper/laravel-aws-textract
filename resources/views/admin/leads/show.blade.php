@extends('admin.layout')

@section('title', $lead->business_name ?: 'Lead #' . $lead->id)

@section('content')
    @php
        $stages = [
            'upload' => 'Receiving uploads',
            'ocr' => 'Reading documents (OCR)',
            'pipedrive' => 'Creating Pipedrive deal',
            'attachments' => 'Attaching documents to deal',
            'done' => 'Done',
        ];
    @endphp

    <div class="page-head">
        <div>
            <div class="eyebrow"><a href="{{ route('admin.leads.index') }}">LEADS</a> · #{{ $lead->id }}</div>
            <h1>{{ $lead->business_name ?: 'Untitled lead' }}</h1>
        </div>
        <span class="badge badge-{{ $lead->status }}" style="font-size: 14px; padding: 6px 14px">{{ ucfirst($lead->status) }}</span>
    </div>

    <div class="two-col">
        <div>
            <div class="card">
                <h2>Documents</h2>
                @if ($lead->documents->isEmpty())
                    <div class="empty">No documents were stored for this lead.</div>
                @else
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>File</th>
                                    <th>Pipedrive</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lead->documents as $document)
                                    <tr>
                                        <td class="strong">{{ $document->label() }}</td>
                                        <td style="max-width: 260px; word-break: break-all">
                                            {{ $document->file_name }}
                                            @if ($document->attach_error)
                                                <div class="small" style="color: var(--red); margin-top: 4px">{{ $document->attach_error }}</div>
                                            @endif
                                        </td>
                                        <td><span class="badge badge-{{ $document->attach_status }}">{{ ucfirst($document->attach_status) }}</span></td>
                                        <td>
                                            <a href="{{ route('admin.leads.document', [$lead, $document]) }}" target="_blank" rel="noopener" class="btn btn-sm">Open</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="muted small" style="margin: 14px 0 0">Files are stored in S3. "Open" creates a secure link valid for 15 minutes.</p>
                @endif
            </div>
        </div>

        <div>
            <div class="card">
                <h2>Pipedrive sync</h2>
                <dl class="dl">
                    <dt>Status</dt>
                    <dd><span class="badge badge-{{ $lead->status }}">{{ ucfirst($lead->status) }}</span></dd>
                    <dt>Stage</dt>
                    <dd>{{ $stages[$lead->stage] ?? $lead->stage }}</dd>
                    <dt>Deal</dt>
                    <dd>
                        @if ($lead->pipedrive_deal_id)
                            @if ($url = $lead->pipedriveDealUrl())
                                <a href="{{ $url }}" target="_blank" rel="noopener" style="text-decoration: underline">#{{ $lead->pipedrive_deal_id }}</a>
                            @else
                                #{{ $lead->pipedrive_deal_id }}
                            @endif
                        @else
                            <span class="muted">Not created yet</span>
                        @endif
                    </dd>
                    <dt>Attempts</dt>
                    <dd>{{ $lead->attempts }}</dd>
                    <dt>Last update</dt>
                    <dd>{{ $lead->updated_at->format('M j, Y g:i A') }}</dd>
                    @if ($lead->completed_at)
                        <dt>Completed</dt>
                        <dd>{{ $lead->completed_at->format('M j, Y g:i A') }}</dd>
                    @endif
                </dl>

                @if ($lead->error)
                    <div class="error-box" style="margin-top: 18px">{{ $lead->error }}</div>
                @endif

                @if ($lead->canRetry())
                    <form method="POST" action="{{ route('admin.leads.retry', $lead) }}" style="margin-top: 18px"
                        data-confirm="{{ $lead->pipedrive_deal_id ? 'Re-send the documents that did not attach to deal #' . $lead->pipedrive_deal_id . '?' : 'Run this lead through OCR and Pipedrive again? A new deal will be created.' }}">
                        @csrf
                        <button type="submit" class="btn btn-gold">
                            {{ $lead->pipedrive_deal_id ? 'Retry failed documents' : 'Restart processing' }}
                        </button>
                    </form>
                @elseif ($lead->status === 'failed' && empty($lead->payload))
                    <p class="muted small" style="margin: 18px 0 0">The upload itself failed, so there is nothing stored to retry. The merchant needs to resubmit.</p>
                @endif
            </div>

            <div class="card">
                <h2>Merchant</h2>
                <dl class="dl">
                    <dt>Owner</dt>
                    <dd>{{ $lead->owner_name ?: '—' }}</dd>
                    <dt>Business (DBA)</dt>
                    <dd>{{ $lead->business_name ?: '—' }}</dd>
                    <dt>Email</dt>
                    <dd><a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a></dd>
                    <dt>Phone</dt>
                    <dd><a href="tel:+1{{ $lead->phone }}">+1 {{ $lead->phone }}</a></dd>
                    <dt>Locations</dt>
                    <dd>{{ $lead->locations ?: '—' }}</dd>
                    <dt>New location</dt>
                    <dd>{{ $lead->new_location ? 'Yes' : 'No' }}</dd>
                    <dt>Affiliate</dt>
                    <dd>{{ $lead->affiliate?->name ?? 'Direct' }}</dd>
                    <dt>Submitted</dt>
                    <dd>{{ $lead->created_at->format('M j, Y g:i A') }}</dd>
                    <dt>S3 folder</dt>
                    <dd class="small" style="font-family: ui-monospace, Menlo, monospace">uploads/{{ $lead->deal_folder }}</dd>
                </dl>
            </div>
        </div>
    </div>
@endsection
