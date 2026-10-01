@extends('admin.layout')

@section('title', 'Edit affiliate')

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow"><a href="{{ route('admin.affiliates.index') }}">AFFILIATES</a> · EDIT</div>
            <h1>{{ $affiliate->name }}</h1>
        </div>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('admin.affiliates.update', $affiliate) }}">
            @csrf
            @method('PUT')
            @include('admin.affiliates._form', ['affiliate' => $affiliate])
        </form>
    </div>
@endsection
