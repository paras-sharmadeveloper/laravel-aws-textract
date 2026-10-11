<div class="form-grid">
    <div class="field">
        <label for="name">Name</label>
        <input id="name" name="name" class="input" required value="{{ old('name', $user?->name) }}"
            placeholder="e.g. Jane Doe">
        @error('name')
            <span class="error-text">{{ $message }}</span>
        @enderror
    </div>
    <div class="field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" class="input" required value="{{ old('email', $user?->email) }}"
            placeholder="jane@example.com" autocomplete="off">
        <span class="help">Used to sign in to the admin area.</span>
        @error('email')
            <span class="error-text">{{ $message }}</span>
        @enderror
    </div>
    <div class="field">
        <label for="role_id">Role</label>
        <select id="role_id" name="role_id" class="input" required>
            <option value="">Choose a role…</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected((string) old('role_id', $user?->role_id) === (string) $role->id)>{{ $role->name }}</option>
            @endforeach
        </select>
        <span class="help">Controls which admin pages they can open. <a href="{{ route('admin.roles.index') }}" style="text-decoration: underline">Manage roles</a></span>
        @error('role_id')
            <span class="error-text">{{ $message }}</span>
        @enderror
    </div>
    <div class="field"></div>
    <div class="field">
        <label for="password">{{ $user ? 'New password' : 'Password' }} @if ($user)<span class="muted">(optional)</span>@endif</label>
        <input id="password" name="password" type="password" class="input" @required(!$user) autocomplete="new-password">
        <span class="help">{{ $user ? 'Leave blank to keep the current password. ' : '' }}At least 8 characters with letters and numbers.</span>
        @error('password')
            <span class="error-text">{{ $message }}</span>
        @enderror
    </div>
    <div class="field">
        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" class="input" @required(!$user)
            autocomplete="new-password">
    </div>
</div>
<div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px">
    @if ($user)
        <a href="{{ route('admin.users.index') }}" class="btn">Cancel</a>
    @endif
    <button type="submit" class="btn btn-primary">{{ $user ? 'Save changes' : 'Create user' }}</button>
</div>
