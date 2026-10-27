@extends('admin.layout')

@section('title', 'Leads')

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow">APPLICATIONS</div>
            <h1>Leads</h1>
        </div>
    </div>

    @php
        $tabs = [
            '' => ['All leads', $counts->sum()],
            'completed' => ['Sent to Pipedrive', $counts['completed'] ?? 0],
            'processing' => ['Processing', $counts['processing'] ?? 0],
            'failed' => ['Needs attention', $counts['failed'] ?? 0],
            'deleted' => ['Deleted', $deletedCount],
        ];
        $showingDeleted = request('status') === 'deleted';
    @endphp

    <div class="stats">
        @foreach ($tabs as $status => [$label, $total])
            <a href="{{ route('admin.leads.index', array_filter(['status' => $status, 'affiliate' => request('affiliate'), 'q' => request('q')])) }}"
                class="stat {{ (string) request('status') === (string) $status ? 'active' : '' }}">
                <div class="stat-label">{{ $label }}</div>
                <div class="stat-value" @if ($status === 'failed' && $total) style="color: var(--red)" @endif>{{ $total }}</div>
            </a>
        @endforeach
    </div>

    <div class="card">
        <form method="GET" class="toolbar">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="search" name="q" class="input" value="{{ request('q') }}" placeholder="Search name, business, email or phone">
            <select name="affiliate" class="input" onchange="this.form.submit()">
                <option value="">All affiliates</option>
                @foreach ($affiliates as $affiliate)
                    <option value="{{ $affiliate->id }}" @selected((string) request('affiliate') === (string) $affiliate->id)>{{ $affiliate->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn">Search</button>
            @if (request()->hasAny(['q', 'affiliate', 'status']))
                <a href="{{ route('admin.leads.index') }}" class="btn">Clear</a>
            @endif
        </form>

        @if ($leads->isEmpty())
            <div class="empty">No leads match these filters.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Submitted</th>
                            <th>Merchant</th>
                            <th>Contact</th>
                            <th>Affiliate</th>
                            <th>Docs</th>
                            <th>Status</th>
                            @can('leads.delete')
                                <th></th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leads as $lead)
                            <tr class="link-row" data-href="{{ route('admin.leads.show', $lead) }}">
                                <td class="nowrap">
                                    <div>{{ $lead->created_at->format('M j, Y') }}</div>
                                    <div class="muted small">{{ $lead->created_at->format('g:i A') }}</div>
                                </td>
                                <td>
                                    <a href="{{ route('admin.leads.show', $lead) }}" class="strong">{{ $lead->business_name ?: '—' }}</a>
                                    <div class="muted small">{{ $lead->owner_name }}</div>
                                </td>
                                <td>
                                    <div>{{ $lead->email }}</div>
                                    <div class="muted small">+1 {{ $lead->phone }}</div>
                                </td>
                                <td>{{ $lead->affiliate?->name ?? '—' }}</td>
                                <td>{{ $lead->documents_count }}</td>
                                <td>
                                    @if ($showingDeleted)
                                        <span class="badge badge-inactive">Deleted</span>
                                        <div class="muted small" style="margin-top: 4px">{{ $lead->deleted_at->format('M j, Y') }}</div>
                                    @else
                                    <span class="badge badge-{{ $lead->status }}">{{ ucfirst($lead->status) }}</span>
                                    @if ($lead->status !== 'completed')
                                        <div class="muted small" style="margin-top: 4px">at {{ $lead->stage }}</div>
                                    @endif
                                    @if ($lead->canRetry() && auth()->user()->can('leads.retry'))
                                        <form method="POST" action="{{ route('admin.leads.retry', $lead) }}" style="margin-top: 6px"
                                            data-confirm="{{ $lead->retryLabel() }} for {{ $lead->business_name ?: $lead->email }}?">
                                            @csrf
                                            <button type="submit" class="btn btn-sm">{{ $lead->retryLabel() }}</button>
                                        </form>
                                    @elseif (!empty($lead->payload) && auth()->user()->can('leads.retry'))
                                        <form method="POST" action="{{ route('admin.leads.resync', $lead) }}" style="margin-top: 6px"
                                            data-confirm="Send {{ $lead->business_name ?: $lead->email }} to Pipedrive again as a new deal?">
                                            @csrf
                                            <button type="submit" class="btn btn-sm">Resync to Pipedrive</button>
                                        </form>
                                    @endif
                                    @endif
                                </td>
                                @can('leads.delete')
                                    <td class="nowrap">
                                        @if ($showingDeleted)
                                            <form method="POST" action="{{ route('admin.leads.restore', $lead) }}"
                                                data-confirm="Restore {{ $lead->business_name ?: $lead->email }} to the dashboard?"
                                                data-confirm-title="Restore lead" data-confirm-button="Restore">
                                                @csrf
                                                <button type="submit" class="btn btn-sm">Restore</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}"
                                                data-confirm="{{ $lead->business_name ?: $lead->email }} will be removed from the dashboard. It stays in the Deleted history and can be restored. Nothing is removed from Pipedrive."
                                                data-confirm-title="Delete this lead?" data-confirm-button="Delete lead" data-confirm-danger>
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pager">
                <span class="muted small">Showing {{ $leads->firstItem() }}–{{ $leads->lastItem() }} of {{ $leads->total() }}</span>
                <div style="display: flex; gap: 8px">
                    @if ($leads->previousPageUrl())
                        <a href="{{ $leads->previousPageUrl() }}" class="btn btn-sm">← Newer</a>
                    @endif
                    @if ($leads->nextPageUrl())
                        <a href="{{ $leads->nextPageUrl() }}" class="btn btn-sm">Older →</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
