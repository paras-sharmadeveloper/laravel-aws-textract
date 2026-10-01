@extends('admin.layout')

@section('title', 'Password')

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow">SETTINGS</div>
            <h1>Password</h1>
        </div>
    </div>

    <div class="card" style="max-width: 560px">
        <h2>Change your password</h2>
        <form method="POST" action="{{ route('admin.account.password') }}">
            @csrf
            @method('PUT')
            <div style="display: flex; flex-direction: column; gap: 16px">
                <div class="field">
                    <label for="current_password">Current password</label>
                    <input id="current_password" name="current_password" type="password" class="input" required
                        autocomplete="current-password">
                    @error('current_password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
                <div class="field">
                    <label for="password">New password</label>
                    <input id="password" name="password" type="password" class="input" required
                        autocomplete="new-password">
                    <span class="help">At least 8 characters, with letters and numbers.</span>
                    @error('password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="input"
                        required autocomplete="new-password">
                </div>
                <div><button type="submit" class="btn btn-primary">Update password</button></div>
            </div>
        </form>
    </div>
@endsection
