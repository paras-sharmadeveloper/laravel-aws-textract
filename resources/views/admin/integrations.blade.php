@extends('admin.layout')

@section('title', 'Integrations')

@section('content')
    <style>
        .int-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
        }

        .int-card {
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding: 24px 26px;
            background: var(--paper);
            border: 1px solid #E6DECB;
            border-radius: 20px;
            transition: border-color .2s;
        }

        .int-card[data-status="ok"] {
            border-color: #B9CDA9;
        }

        .int-card[data-status="warn"] {
            border-color: #E3CF9A;
        }

        .int-card[data-status="fail"] {
            border-color: #DDB89C;
        }

        .int-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .int-head h2 {
            margin: 0 0 2px;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 12px;
            border-radius: 14px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            background: var(--sand);
            color: var(--muted);
        }

        .status::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 4px;
            background: currentColor;
        }

        .status[data-status="ok"] {
            background: var(--green-bg);
            color: var(--green);
        }

        .status[data-status="warn"] {
            background: var(--amber-bg);
            color: var(--amber);
        }

        .status[data-status="fail"] {
            background: var(--red-bg);
            color: var(--red);
        }

        .status[data-status="testing"]::before {
            animation: blink .8s ease-in-out infinite;
        }

        @keyframes blink {
            50% {
                opacity: .25;
            }
        }

        .int-config {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 6px 14px;
            margin: 0;
            padding: 14px 16px;
            background: #F4EEE0;
            border-radius: 12px;
            font-size: 13px;
        }

        .int-config dt {
            color: var(--muted);
        }

        .int-config dd {
            margin: 0;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 12px;
            word-break: break-all;
        }

        .int-checks {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .int-checks li {
            display: grid;
            grid-template-columns: 18px 1fr;
            gap: 4px 10px;
            font-size: 13px;
        }

        .int-checks .icon {
            grid-row: span 2;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            margin-top: 1px;
            border-radius: 9px;
            font-size: 11px;
            font-weight: 700;
            color: #fff;
        }

        .int-checks .icon.ok {
            background: var(--green);
        }

        .int-checks .icon.warn {
            background: #B08A2E;
        }

        .int-checks .icon.fail {
            background: var(--red);
        }

        .int-checks strong {
            font-weight: 600;
        }

        .int-checks span.detail {
            color: var(--muted);
            word-break: break-word;
        }

        .int-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: auto;
            padding-top: 4px;
        }

        .int-meta {
            font-size: 12px;
            color: var(--muted);
        }
    </style>

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
