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
        ];
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
                                    <span class="badge badge-{{ $lead->status }}">{{ ucfirst($lead->status) }}</span>
                                    @if ($lead->status !== 'completed')
                                        <div class="muted small" style="margin-top: 4px">at {{ $lead->stage }}</div>
                                    @endif
                                </td>
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
