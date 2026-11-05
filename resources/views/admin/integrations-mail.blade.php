@extends('admin.layout')

@section('title', 'Email settings')

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow"><a href="{{ route('admin.integrations') }}">INTEGRATIONS</a> · EMAIL</div>
            <h1>Email (SMTP)</h1>
        </div>
    </div>

    <p class="muted" style="margin: -12px 0 24px; max-width: 720px">Once a submission has been created in Pipedrive,
        two emails go out: one to the affiliate whose link it came through (at the contact email saved on the affiliate),
        and one to your team. Each lead is only emailed once, even if it is resynced.
        @if ($affiliatesWithoutEmail)
            <strong>{{ $affiliatesWithoutEmail }} active {{ Str::plural('affiliate', $affiliatesWithoutEmail) }}
                {{ $affiliatesWithoutEmail === 1 ? 'has' : 'have' }} no email</strong> and won't be notified.
        @endif
    </p>

    <form method="POST" action="{{ route('admin.integrations.mail.update') }}">
        @csrf
        @method('PUT')

        <div class="card">
            <h2>Notifications</h2>
            <label class="check">
                <input type="hidden" name="notify_affiliate" value="0">
                <input type="checkbox" name="notify_affiliate" value="1" @checked(old('notify_affiliate', $settings['notify_affiliate']))>
                Email the affiliate once their lead is created in Pipedrive
            </label>
            <label class="check" style="margin-top: 12px">
                <input type="hidden" name="notify_team" value="0">
                <input type="checkbox" name="notify_team" value="1" @checked(old('notify_team', $settings['notify_team']))>
                Email the team about every new submission (including direct ones with no affiliate)
            </label>

            <div class="form-grid" style="margin-top: 20px">
                <div class="field">
                    <label for="timezone">Time zone for "submitted" dates</label>
                    <select id="timezone" name="timezone" class="input">
                        @foreach ($timezones as $tz)
                            <option value="{{ $tz }}" @selected(old('timezone', $settings['timezone']) === $tz)>{{ str_replace('_', ' ', $tz) }}</option>
                        @endforeach
                    </select>
                    @error('timezone')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
                <div class="field"></div>
                <div class="field">
                    <label for="reply_to">Reply-to address <span class="muted">(optional)</span></label>
                    <input id="reply_to" name="reply_to" type="email" class="input"
                        value="{{ old('reply_to', $settings['reply_to']) }}" placeholder="support@yourcompany.com">
                    <span class="help">Where replies from affiliates go. Defaults to the "from" address. Replies to
                        the team email go straight to the affiliate.</span>
                    @error('reply_to')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
                <div class="field">
                    <label for="team_recipients">Team recipients</label>
                    <input id="team_recipients" name="team_recipients" class="input"
                        value="{{ old('team_recipients', $settings['team_recipients']) }}"
                        placeholder="owner@yourcompany.com, sales@yourcompany.com">
                    <span class="help">Who gets the team email. Separate several addresses with commas.</span>
                    @error('team_recipients')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        @foreach ([
            '' => ['Affiliate email', 'Sent to the affiliate whose link the submission came through.'],
            'team_' => ['Team email', 'Sent to the team recipients above.'],
        ] as $prefix => [$title, $intro])
            <div class="card">
                <h2>{{ $title }}</h2>
                <p class="muted" style="margin: -6px 0 16px">{{ $intro }}</p>
                <div class="form-grid">
                    <div class="field field-wide">
                        <label for="{{ $prefix }}subject">Subject</label>
                        <input id="{{ $prefix }}subject" name="{{ $prefix }}subject" class="input" maxlength="200" data-template
                            value="{{ old($prefix . 'subject', $settings[$prefix . 'subject']) }}">
                        @error($prefix . 'subject')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="field field-wide">
                        <label for="{{ $prefix }}body">Message</label>
                        <textarea id="{{ $prefix }}body" name="{{ $prefix }}body" class="input input-area" rows="16" maxlength="5000"
                            data-template>{{ old($prefix . 'body', $settings[$prefix . 'body']) }}</textarea>
                        <span class="help">Plain text. Leave a blank line between paragraphs.
                            <a href="#" data-reset="{{ $prefix }}body">Restore the default message</a></span>
                        @error($prefix . 'body')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="help" style="margin-top: 14px; font-size: 12px">
                    <strong>Placeholders</strong> — click to insert into the subject or message you last clicked in:
                    <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px">
                        @foreach ($placeholders as $tag => $label)
                            <button type="button" class="btn btn-sm" data-placeholder="{{ $tag }}" data-for="{{ $prefix }}body"
                                title="{{ $label }}" style="font-family: ui-monospace, Menlo, monospace; font-size: 12px">{{ $tag }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach

        <div class="card">
            <h2>Sender</h2>
            <div class="form-grid">
                <div class="field">
                    <label for="from_name">From name</label>
                    <input id="from_name" name="from_name" class="input"
                        value="{{ old('from_name', $settings['from_name']) }}" placeholder="DPS Payments">
                    @error('from_name')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
                <div class="field">
                    <label for="from_address">From address</label>
                    <input id="from_address" name="from_address" type="email" class="input"
                        value="{{ old('from_address', $settings['from_address']) }}" placeholder="no-reply@yourcompany.com">
                    <span class="help">Must be an address or domain your SMTP provider allows you to send from.</span>
                    @error('from_address')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card">
            <h2>SMTP server</h2>
            <div class="form-grid">
                <div class="field">
                    <label for="host">Host</label>
                    <input id="host" name="host" class="input" value="{{ old('host', $settings['host']) }}"
                        placeholder="smtp.gmail.com" autocomplete="off">
                    <span class="help">e.g. smtp.gmail.com, smtp.office365.com, email-smtp.us-east-1.amazonaws.com,
                        smtp.sendgrid.net</span>
                    @error('host')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
                <div class="field">
                    <label for="port">Port</label>
                    <input id="port" name="port" type="number" min="1" max="65535" class="input"
                        value="{{ old('port', $settings['port']) }}">
                    <span class="help">587 for STARTTLS, 465 for SSL, 25 or 2525 when unencrypted.</span>
                    @error('port')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
                <div class="field">
                    <label for="encryption">Encryption</label>
                    <select id="encryption" name="encryption" class="input">
                        @foreach ($encryptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('encryption', $settings['encryption']) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('encryption')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
                <div class="field">
                    <label for="timeout">Timeout (seconds)</label>
                    <input id="timeout" name="timeout" type="number" min="5" max="120" class="input"
                        value="{{ old('timeout', $settings['timeout']) }}">
                    @error('timeout')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
                <div class="field">
                    <label for="username">Username <span class="muted">(optional)</span></label>
                    <input id="username" name="username" class="input" value="{{ old('username', $settings['username']) }}"
                        autocomplete="off">
                    <span class="help">Usually your full email address, or the API key name your provider gives you.</span>
                    @error('username')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" class="input" autocomplete="new-password"
                        placeholder="{{ $hasPassword ? '•••••••• (saved, leave blank to keep)' : 'Not set' }}">
                    <span class="help">Stored encrypted. For Gmail or Microsoft 365, use an app password.</span>
                    @if ($hasPassword)
                        <label class="check" style="font-size: 13px">
                            <input type="checkbox" name="clear_password" value="1"> Remove the saved password
                        </label>
                    @endif
                    @error('password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <label class="check" style="margin-top: 16px">
                <input type="hidden" name="verify_peer" value="0">
                <input type="checkbox" name="verify_peer" value="1" @checked(old('verify_peer', $settings['verify_peer']))>
                Verify the server's TLS certificate <span class="muted">(turn off only for self-signed internal servers)</span>
            </label>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px">
                <a href="{{ route('admin.integrations') }}" class="btn">Back</a>
                <button type="submit" class="btn btn-primary">Save settings</button>
            </div>
        </div>
    </form>

    <div class="card">
        <h2>Send a test email</h2>
        <p class="muted" style="margin: -6px 0 16px">Sends either email with sample data using the saved settings. Save any
            changes first.</p>
        <form method="POST" action="{{ route('admin.integrations.mail.test') }}"
            style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-start">
            @csrf
            <select name="template" class="input" style="width: auto" aria-label="Which email to test">
                <option value="affiliate" @selected(old('template') !== 'team')>Affiliate email</option>
                <option value="team" @selected(old('template') === 'team')>Team email</option>
            </select>
            <div class="field" style="flex: 1; min-width: 240px">
                <input name="to" type="email" class="input" required value="{{ old('to', auth()->user()->email) }}"
                    aria-label="Send the test email to">
                @error('to')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Send test email</button>
        </form>
    </div>

    <script>
        (function() {
            const defaults = @json($defaults);
            let last = null;

            document.querySelectorAll('[data-template]').forEach(el => el.addEventListener('focus', () => last = el));

            document.querySelectorAll('[data-placeholder]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    // The field last clicked in this card, otherwise this card's message
                    const card = btn.closest('.card');
                    const target = last && card.contains(last) ? last : document.getElementById(btn.dataset.for);
                    const text = btn.dataset.placeholder;
                    const start = target.selectionStart ?? target.value.length;
                    const end = target.selectionEnd ?? target.value.length;
                    target.value = target.value.slice(0, start) + text + target.value.slice(end);
                    target.focus();
                    target.selectionStart = target.selectionEnd = start + text.length;
                });
            });

            document.querySelectorAll('[data-reset]').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm('Replace the message with the default text?')) {
                        document.getElementById(link.dataset.reset).value = defaults[link.dataset.reset];
                    }
                });
            });
        })();
    </script>
@endsection
