<div class="form-grid">
    <div class="field">
        <label for="name">Role name</label>
        <input id="name" name="name" class="input" required value="{{ old('name', $role?->name) }}"
            placeholder="e.g. Sales team">
        @error('name')
            <span class="error-text">{{ $message }}</span>
        @enderror
    </div>
</div>
<div class="field" style="margin-top: 18px">
    <label>Permissions</label>
    <div class="perm-grid">
        @php($granted = old('permissions', $role?->permissions ?? []))
        @foreach (\App\Models\Role::PERMISSIONS as $key => $label)
            <label class="check">
                <input type="checkbox" name="permissions[]" value="{{ $key }}" @checked(in_array($key, $granted, true))>
                {{ $label }}
            </label>
        @endforeach
    </div>
    <span class="help">Every user can change their own password, whatever their role.</span>
    @error('permissions.*')
        <span class="error-text">{{ $message }}</span>
    @enderror
</div>
<div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px">
    @if ($role)
        <a href="{{ route('admin.roles.index') }}" class="btn">Cancel</a>
    @endif
    <button type="submit" class="btn btn-primary">{{ $role ? 'Save changes' : 'Create role' }}</button>
</div>
