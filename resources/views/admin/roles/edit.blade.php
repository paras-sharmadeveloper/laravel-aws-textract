@extends('admin.layout')

@section('title', 'Edit role')

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow"><a href="{{ route('admin.roles.index') }}">ROLES</a> · EDIT</div>
            <h1>{{ $role->name }}</h1>
        </div>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('admin.roles.update', $role) }}">
            @csrf
            @method('PUT')
            @include('admin.roles._form', ['role' => $role])
        </form>
    </div>
@endsection
