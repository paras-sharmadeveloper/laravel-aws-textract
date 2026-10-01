<div class="form-grid">
    <div class="field">
        <label for="name">Affiliate name</label>
        <input id="name" name="name" class="input" required value="{{ old('name', $affiliate?->name) }}"
            placeholder="e.g. Jhon Rocha">
        @error('name')
            <span class="error-text">{{ $message }}</span>
        @enderror
    </div>
    <div class="field">
        <label for="slug">Link</label>
        <div class="prefix-input">
            <span>{{ preg_replace('#^https?://#', '', url('/')) }}/</span>
            <input id="slug" name="slug" value="{{ old('slug', $affiliate?->slug) }}" placeholder="jhonrocha">
        </div>
        <span class="help">Leave blank to generate from the name. Lowercase letters, numbers and dashes.</span>
        @error('slug')
            <span class="error-text">{{ $message }}</span>
        @enderror
    </div>
    <div class="field">
        <label for="email">Contact email <span class="muted">(optional)</span></label>
        <input id="email" name="email" type="email" class="input" value="{{ old('email', $affiliate?->email) }}"
            placeholder="name@example.com">
        <span class="help">Used for the "Contact" button on their application page.</span>
        @error('email')
            <span class="error-text">{{ $message }}</span>
        @enderror
    </div>
    <div class="field">
        <label for="phone">Contact phone <span class="muted">(optional)</span></label>
        <input id="phone" name="phone" class="input" value="{{ old('phone', $affiliate?->phone) }}"
            placeholder="(646) 555-0100">
        @error('phone')
            <span class="error-text">{{ $message }}</span>
        @enderror
    </div>
</div>
<div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-top: 20px">
    <label class="check">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $affiliate?->is_active ?? true))>
        Link is active
    </label>
    <div style="display: flex; gap: 10px">
        @if ($affiliate)
            <a href="{{ route('admin.affiliates.index') }}" class="btn">Cancel</a>
        @endif
        <button type="submit" class="btn btn-primary">{{ $affiliate ? 'Save changes' : 'Create affiliate link' }}</button>
    </div>
</div>
