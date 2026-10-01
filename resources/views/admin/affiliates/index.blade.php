@extends('admin.layout')

@section('title', 'Affiliates')

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow">REFERRAL LINKS</div>
            <h1>Affiliates</h1>
        </div>
    </div>

    <div class="card">
        <h2>New affiliate</h2>
        <form method="POST" action="{{ route('admin.affiliates.store') }}">
            @csrf
            @include('admin.affiliates._form', ['affiliate' => null])
        </form>
    </div>

    <div class="card">
        <h2>All affiliates</h2>
        <p class="muted" style="margin: -8px 0 16px">Only links listed here and marked active open the application form.
            Any other link returns "not found".</p>
        @if ($affiliates->isEmpty())
            <div class="empty">No affiliates yet. Create one above to generate their link.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Affiliate</th>
                            <th>Link</th>
                            <th>Leads</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($affiliates as $affiliate)
                            <tr>
                                <td>
                                    <div class="strong">{{ $affiliate->name }}</div>
                                    <div class="muted small">{{ collect([$affiliate->email, $affiliate->phone])->filter()->implode(' · ') ?: 'No contact details' }}</div>
                                </td>
                                <td style="max-width: 340px">
                                    <div class="link-box">
                                        <code>{{ $affiliate->link() }}</code>
                                        <button type="button" class="btn btn-sm" data-copy="{{ $affiliate->link() }}">Copy</button>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('admin.leads.index', ['affiliate' => $affiliate->id]) }}" class="strong" style="text-decoration: underline">{{ $affiliate->leads_count }}</a>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $affiliate->is_active ? 'active' : 'inactive' }}">{{ $affiliate->is_active ? 'Active' : 'Disabled' }}</span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="{{ route('admin.affiliates.edit', $affiliate) }}" class="btn btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.affiliates.toggle', $affiliate) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm">{{ $affiliate->is_active ? 'Disable' : 'Enable' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.affiliates.destroy', $affiliate) }}"
                                            data-confirm="Delete {{ $affiliate->name }}? Their link will stop working. Past leads are kept.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
