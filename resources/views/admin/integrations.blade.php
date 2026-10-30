@extends('admin.layout')

@section('title', 'Integrations')

@push('styles')
    @include('partials.css', ['files' => ['integrations']])
@endpush

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow">SETTINGS</div>
            <h1>Integrations</h1>
        </div>
        <button type="button" class="btn btn-primary" id="testAll">Test all connections</button>
    </div>

    <p class="muted" style="margin: -12px 0 24px; max-width: 720px">Live checks against each service using the
        credentials in the server's <code>.env</code>. Tests are safe to run any time — the S3 test writes and then deletes a
        tiny file, and nothing is created in Pipedrive.</p>

    <div class="int-grid">
        @foreach ($integrations as $int)
            <section class="int-card" data-integration="{{ $int['key'] }}" data-status="{{ $int['last']['status'] ?? 'none' }}"
                data-url="{{ route('admin.integrations.test', $int['key']) }}">
                <div class="int-head">
                    <div>
                        <h2>{{ $int['name'] }}</h2>
                        <div class="muted small">{{ $int['role'] }}</div>
                    </div>
                    <span class="status" data-status="{{ $int['last']['status'] ?? 'none' }}" data-role="status">
                        {{ ['ok' => 'Connected', 'warn' => 'Needs attention', 'fail' => 'Failed'][$int['last']['status'] ?? ''] ?? 'Not tested' }}
                    </span>
                </div>

                <dl class="int-config">
                    @foreach ($int['config'] as $label => $value)
                        <dt>{{ $label }}</dt>
                        <dd>{{ $value ?: 'Not set' }}</dd>
                    @endforeach
                </dl>

                <ul class="int-checks" data-role="checks">
                    @foreach ($int['last']['checks'] ?? [] as $check)
                        <li>
                            <span class="icon {{ $check['ok'] === true ? 'ok' : ($check['ok'] === null ? 'warn' : 'fail') }}">{{ $check['ok'] === true ? '✓' : ($check['ok'] === null ? '!' : '×') }}</span>
                            <strong>{{ $check['label'] }}</strong>
                            <span class="detail">{{ $check['detail'] }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="int-foot">
                    <span class="int-meta" data-role="meta">
                        @if ($int['last'])
                            {{ $int['last']['summary'] }} ·
                            {{ \Illuminate\Support\Carbon::parse($int['last']['tested_at'])->diffForHumans() }} ·
                            {{ $int['last']['latency_ms'] }} ms
                        @else
                            Never tested
                        @endif
                    </span>
                    <button type="button" class="btn btn-sm" data-role="test">Test connection</button>
                </div>
            </section>
        @endforeach
    </div>

    <script>
        (function() {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const labels = { ok: 'Connected', warn: 'Needs attention', fail: 'Failed' };

            function el(tag, cls, text) {
                const node = document.createElement(tag);
                if (cls) node.className = cls;
                if (text !== undefined) node.textContent = text;
                return node;
            }

            function render(card, result) {
                card.dataset.status = result.status;
                const status = card.querySelector('[data-role="status"]');
                status.dataset.status = result.status;
                status.textContent = labels[result.status] || result.status;

                const list = card.querySelector('[data-role="checks"]');
                list.innerHTML = '';
                result.checks.forEach(function(check) {
                    const kind = check.ok === true ? 'ok' : (check.ok === null ? 'warn' : 'fail');
                    const li = el('li');
                    li.appendChild(el('span', 'icon ' + kind, kind === 'ok' ? '✓' : (kind === 'warn' ? '!' : '×')));
                    li.appendChild(el('strong', '', check.label));
                    li.appendChild(el('span', 'detail', check.detail));
                    list.appendChild(li);
                });

                card.querySelector('[data-role="meta"]').textContent = result.summary + ' · just now · ' + result.latency_ms + ' ms';
            }

            async function test(card) {
                const btn = card.querySelector('[data-role="test"]');
                const status = card.querySelector('[data-role="status"]');
                btn.disabled = true;
                btn.textContent = 'Testing…';
                status.dataset.status = 'testing';
                status.textContent = 'Testing…';

                try {
                    const res = await fetch(card.dataset.url, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                    });
                    if (res.status === 429) throw new Error('Too many tests — wait a minute and try again');
                    if (!res.ok) throw new Error('The server returned ' + res.status);
                    render(card, await res.json());
                } catch (err) {
                    render(card, {
                        status: 'fail',
                        summary: 'Test could not run',
                        latency_ms: 0,
                        checks: [{ label: 'Admin request', ok: false, detail: err.message }]
                    });
                } finally {
                    btn.disabled = false;
                    btn.textContent = 'Test connection';
                }
            }

            document.querySelectorAll('.int-card').forEach(function(card) {
                card.querySelector('[data-role="test"]').addEventListener('click', () => test(card));
            });

            document.getElementById('testAll').addEventListener('click', async function() {
                this.disabled = true;
                await Promise.all([...document.querySelectorAll('.int-card')].map(test));
                this.disabled = false;
            });
        })();
    </script>
@endsection
