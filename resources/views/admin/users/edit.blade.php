@extends('admin.layout')

@section('title', 'Edit user')

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow"><a href="{{ route('admin.users.index') }}">USERS</a> · EDIT</div>
            <h1>{{ $user->name }}</h1>
        </div>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')
            @include('admin.users._form', ['user' => $user])
        </form>
    </div>
@endsection
