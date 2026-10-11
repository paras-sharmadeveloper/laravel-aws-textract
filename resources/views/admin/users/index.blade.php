@extends('admin.layout')

@section('title', 'Users')

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow">ADMIN ACCESS</div>
            <h1>Users</h1>
        </div>
    </div>

    <div class="card">
        <h2>New user</h2>
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            @include('admin.users._form', ['user' => null])
        </form>
    </div>

    <div class="card">
        <h2>All users</h2>
        <p class="muted" style="margin: -8px 0 16px">Everyone listed here can sign in to the admin area.</p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Added</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                <div class="strong">{{ $user->name }}@if ($user->is(auth()->user())) <span class="muted small">(you)</span>@endif</div>
                                <div class="muted small">{{ $user->email }}</div>
                            </td>
                            <td class="muted small">{{ $user->created_at?->format('M j, Y') }}</td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm">Edit</a>
                                    @unless ($user->is(auth()->user()))
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                            data-confirm="Delete {{ $user->name }}? They will no longer be able to sign in.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
