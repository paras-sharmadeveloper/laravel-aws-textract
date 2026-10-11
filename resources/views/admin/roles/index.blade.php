@extends('admin.layout')

@section('title', 'Roles')

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow">ADMIN ACCESS</div>
            <h1>Roles &amp; permissions</h1>
        </div>
    </div>

    <div class="card">
        <h2>New role</h2>
        <form method="POST" action="{{ route('admin.roles.store') }}">
            @csrf
            @include('admin.roles._form', ['role' => null])
        </form>
    </div>

    <div class="card">
        <h2>All roles</h2>
        <p class="muted" style="margin: -8px 0 16px">Assign a role to each user on the <a
                href="{{ route('admin.users.index') }}" style="text-decoration: underline">Users</a> page.</p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Permissions</th>
                        <th>Users</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td class="strong">{{ $role->name }}</td>
                            <td class="small" style="max-width: 420px">
                                @if ($role->is_admin)
                                    Full access to everything
                                @else
                                    {{ collect($role->permissions)->map(fn($key) => \App\Models\Role::PERMISSIONS[$key] ?? $key)->implode(' · ') ?: 'No access — password page only' }}
                                @endif
                            </td>
                            <td>{{ $role->users_count }}</td>
                            <td>
                                @unless ($role->is_admin)
                                    <div class="actions">
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                                            data-confirm="Delete the {{ $role->name }} role?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
