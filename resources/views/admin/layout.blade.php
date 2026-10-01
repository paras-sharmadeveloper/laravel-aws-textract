<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') · DPS Admin</title>
    <link href="{{ asset('logo.jpeg') }}" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --cream: #F4EEE0;
            --paper: #FBF8EE;
            --field: #FFFDF7;
            --forest: #121E18;
            --forest-2: #1C2922;
            --forest-line: #34443A;
            --ink: #151A16;
            --ink-2: #3F4640;
            --muted: #6A7068;
            --line: #E2D9C4;
            --line-2: #D8CFBA;
            --sand: #E8E0CC;
            --gold: #BFA15A;
            --gold-2: #C9AD62;
            --gold-ink: #8F7431;
            --green: #3D6B47;
            --green-bg: #E5EEDD;
            --red: #9A3D12;
            --red-bg: #F6E3D6;
            --amber: #7A6128;
            --amber-bg: #F3EAD0;
            --serif: 'Cormorant Garamond', Garamond, Georgia, serif;
            --sans: Inter, system-ui, -apple-system, "Segoe UI", sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: var(--sans);
            font-size: 14px;
            background: var(--cream);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        input,
        button,
        select {
            font: inherit;
        }

        .app {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column;
            width: 248px;
            height: 100vh;
            flex-shrink: 0;
            padding: 28px 18px;
            background: var(--forest);
            color: #D8D6CB;
        }

        .side-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 0 6px 22px;
            border-bottom: 1px solid var(--forest-line);
        }

        .side-brand {
            display: flex;
            align-items: center;
            min-width: 0;
        }

        .logo-full {
            display: block;
            width: 132px;
            height: auto;
        }

        .logo-mark {
            display: none;
            width: 40px;
            height: auto;
        }

        .collapse-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 34px;
            height: 34px;
            padding: 0;
            border: 1px solid var(--forest-line);
            border-radius: 10px;
            background: transparent;
            color: #C4C4B8;
            cursor: pointer;
        }

        .collapse-btn:hover {
            background: var(--forest-2);
            color: var(--paper);
        }

        .collapse-btn .chev {
            transition: transform .2s;
            transform-origin: 14.5px 12px;
        }

        .nav {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-top: 18px;
        }

        .nav-label {
            padding: 14px 12px 6px;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #7F877C;
            white-space: nowrap;
        }

        .nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 500;
            color: #C4C4B8;
            white-space: nowrap;
        }

        .nav a:hover {
            background: var(--forest-2);
            color: var(--paper);
        }

        .nav a.active {
            background: var(--forest-2);
            color: var(--paper);
            box-shadow: inset 3px 0 0 var(--gold);
        }

        .nav svg {
            flex-shrink: 0;
            color: var(--gold-2);
        }

        .nav .label small {
            margin-left: 4px;
            font-size: 12px;
            color: #7F877C;
        }

        .nav .ext {
            margin-left: auto;
            color: #7F877C;
        }

        .side-foot {
            margin-top: auto;
            padding: 18px 10px 0;
            border-top: 1px solid var(--forest-line);
        }

        .side-user {
            display: block;
            font-size: 13px;
            color: #9BA197;
            margin-bottom: 10px;
            word-break: break-all;
        }

        .logout {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0;
            border: 0;
            background: none;
            color: #D8D6CB;
            font-weight: 500;
            cursor: pointer;
        }

        .logout:hover {
            color: var(--gold-2);
        }

        /* Collapsed sidebar (desktop) */
        .sidebar {
            transition: width .2s ease, padding .2s ease;
        }

        @media (min-width: 901px) {
            body.collapsed .sidebar {
                width: 76px;
                padding: 28px 12px;
            }

            body.collapsed .side-top {
                flex-direction: column;
                gap: 14px;
                padding: 0 0 18px;
            }

            body.collapsed .logo-full,
            body.collapsed .label,
            body.collapsed .nav .ext {
                display: none;
            }

            body.collapsed .logo-mark {
                display: block;
            }

            body.collapsed .collapse-btn .chev {
                transform: scaleX(-1);
            }

            body.collapsed .nav-label {
                padding: 12px 0 4px;
                font-size: 0;
                border-top: 1px solid var(--forest-line);
                margin-top: 8px;
            }

            body.collapsed .nav-label:first-child {
                border-top: 0;
                margin-top: 0;
            }

            body.collapsed .nav a {
                justify-content: center;
                padding: 12px 0;
            }

            body.collapsed .side-foot {
                display: flex;
                justify-content: center;
                padding: 18px 0 0;
            }
        }

        /* Main */
        .main {
            flex: 1;
            min-width: 0;
            padding: 40px 48px 64px;
        }

        .page-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 28px;
        }

        .eyebrow {
            font-size: 12px;
            letter-spacing: 3px;
            color: var(--gold-ink);
            margin-bottom: 6px;
        }

        h1 {
            margin: 0;
            font-family: var(--serif);
            font-weight: 400;
            font-size: 44px;
            line-height: 1.05;
        }

        h2 {
            margin: 0 0 16px;
            font-family: var(--serif);
            font-weight: 500;
            font-size: 26px;
        }

        .card {
            background: var(--paper);
            border: 1px solid #E6DECB;
            border-radius: 20px;
            padding: 24px 26px;
        }

        .card+.card {
            margin-top: 20px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat {
            display: block;
            padding: 20px 22px;
            background: var(--paper);
            border: 1px solid #E6DECB;
            border-radius: 18px;
        }

        .stat:hover,
        .stat.active {
            border-color: var(--gold);
        }

        .stat-label {
            font-size: 12px;
            letter-spacing: 2px;
            color: var(--muted);
            text-transform: uppercase;
        }

        .stat-value {
            margin-top: 6px;
            font-family: var(--serif);
            font-size: 40px;
            line-height: 1;
        }

        /* Forms */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px 20px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field label {
            font-size: 13px;
            font-weight: 600;
        }

        .field .help {
            font-size: 12px;
            color: var(--muted);
        }

        .input {
            width: 100%;
            height: 44px;
            padding: 0 14px;
            border: 1px solid var(--line-2);
            border-radius: 10px;
            background: var(--field);
            color: var(--ink);
        }

        .input:focus {
            outline: 2px solid var(--gold);
            outline-offset: 1px;
        }

        .prefix-input {
            display: flex;
            height: 44px;
            border: 1px solid var(--line-2);
            border-radius: 10px;
            background: var(--field);
            overflow: hidden;
        }

        .prefix-input:focus-within {
            outline: 2px solid var(--gold);
            outline-offset: 1px;
        }

        .prefix-input span {
            display: flex;
            align-items: center;
            padding: 0 12px;
            background: #F1EADA;
            border-right: 1px solid var(--line-2);
            color: var(--muted);
            white-space: nowrap;
            font-size: 13px;
        }

        .prefix-input input {
            flex: 1;
            min-width: 0;
            border: 0;
            padding: 0 12px;
            background: transparent;
        }

        .prefix-input input:focus {
            outline: none;
        }

        .error-text {
            font-size: 12px;
            color: var(--red);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 42px;
            padding: 0 18px;
            border: 1.5px solid var(--ink);
            border-radius: 22px;
            background: transparent;
            color: var(--ink);
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn:hover {
            background: var(--sand);
        }

        .btn-primary {
            border-color: var(--forest);
            background: var(--forest);
            color: var(--paper);
        }

        .btn-primary:hover {
            background: #22332A;
        }

        .btn-gold {
            border-color: var(--gold);
            background: var(--gold);
            color: var(--forest);
        }

        .btn-gold:hover {
            background: var(--gold-2);
        }

        .btn-sm {
            height: 32px;
            padding: 0 12px;
            font-size: 13px;
            border-width: 1px;
        }

        .btn-danger {
            border-color: #D9B49C;
            color: var(--red);
        }

        .btn-danger:hover {
            background: var(--red-bg);
        }

        .check {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            cursor: pointer;
        }

        .check input {
            width: 16px;
            height: 16px;
            accent-color: var(--forest);
        }

        /* Tables */
        .table-wrap {
            overflow-x: auto;
            margin: 0 -26px;
            padding: 0 26px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 10px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--muted);
            border-bottom: 1px solid var(--line);
            white-space: nowrap;
        }

        td {
            padding: 14px 12px;
            border-bottom: 1px solid #EDE6D4;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: 0;
        }

        tbody tr.link-row {
            cursor: pointer;
        }

        tbody tr.link-row:hover td {
            background: #F7F1E3;
        }

        .strong {
            font-weight: 600;
        }

        .muted {
            color: var(--muted);
        }

        .small {
            font-size: 12px;
        }

        .nowrap {
            white-space: nowrap;
        }

        .actions {
            display: flex;
            gap: 6px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 3px;
            background: currentColor;
        }

        .badge-completed,
        .badge-attached,
        .badge-active {
            background: var(--green-bg);
            color: var(--green);
        }

        .badge-failed {
            background: var(--red-bg);
            color: var(--red);
        }

        .badge-processing,
        .badge-pending {
            background: var(--amber-bg);
            color: var(--amber);
        }

        .badge-inactive {
            background: var(--sand);
            color: var(--muted);
        }

        .link-box {
            display: flex;
            align-items: center;
            gap: 6px;
            max-width: 100%;
        }

        .link-box code {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding: 5px 10px;
            border-radius: 8px;
            background: #F1EADA;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 12px;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 14px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: var(--green-bg);
            border: 1px solid #B9CDA9;
            color: #2F5B2C;
        }

        .alert-error {
            background: var(--red-bg);
            border: 1px solid #DDB89C;
            color: #7E3A12;
        }

        .toolbar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .toolbar .input {
            width: auto;
            flex: 1;
            min-width: 180px;
        }

        .toolbar select.input {
            flex: 0 1 220px;
        }

        .pager {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 18px;
        }

        .empty {
            padding: 40px 12px;
            text-align: center;
            color: var(--muted);
        }

        .dl {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 12px 16px;
            margin: 0;
        }

        .dl dt {
            color: var(--muted);
        }

        .dl dd {
            margin: 0;
            font-weight: 500;
            word-break: break-word;
        }

        .two-col {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
            gap: 20px;
            align-items: start;
        }

        .two-col .card+.card {
            margin-top: 20px;
        }

        .error-box {
            padding: 12px 14px;
            border-radius: 12px;
            background: var(--red-bg);
            color: #7E3A12;
            font-size: 13px;
            line-height: 1.5;
            word-break: break-word;
        }

        @media (max-width: 1100px) {
            .two-col {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 900px) {
            .app {
                flex-direction: column;
            }

            .sidebar {
                position: static;
                flex-direction: row;
                align-items: center;
                flex-wrap: wrap;
                width: auto;
                height: auto;
                gap: 8px 16px;
                padding: 14px 16px;
            }

            .side-top {
                padding: 0;
                border: 0;
            }

            .logo-full {
                width: 96px;
            }

            .collapse-btn,
            .nav-label,
            .nav .ext,
            .nav .label small {
                display: none;
            }

            .nav {
                order: 3;
                flex-direction: row;
                width: 100%;
                margin: 0;
                overflow-x: auto;
            }

            .nav a {
                padding: 8px 12px;
                white-space: nowrap;
            }

            .nav a.active {
                box-shadow: inset 0 -2px 0 var(--gold);
            }

            .side-foot {
                margin: 0 0 0 auto;
                padding: 0;
                border: 0;
            }

            .side-user {
                display: none;
            }

            .main {
                padding: 28px 16px 48px;
            }

            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            h1 {
                font-size: 36px;
            }
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .card {
                padding: 20px 16px;
            }

            .table-wrap {
                margin: 0 -16px;
                padding: 0 16px;
            }

            .dl {
                grid-template-columns: 1fr;
                gap: 2px;
            }

            .dl dd {
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
    <script>
        try {
            if (localStorage.getItem('dps-admin-sidebar') === 'collapsed') document.body.classList.add('collapsed');
        } catch (e) {}
    </script>
    <div class="app">
        <aside class="sidebar" id="sidebar">
            <div class="side-top">
                <a href="{{ route('admin.leads.index') }}" class="side-brand" aria-label="DPS Payments admin">
                    <img src="{{ asset('images/dps-logo-light.png') }}" alt="DPS Payments Corp." class="logo-full">
                    <img src="{{ asset('images/dps-mark-light.png') }}" alt="DPS" class="logo-mark">
                </a>
                <button type="button" class="collapse-btn" id="collapseBtn" aria-label="Collapse sidebar"
                    aria-controls="sidebar" aria-expanded="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="16" rx="3"></rect>
                        <path d="M9 4v16"></path>
                        <path d="M15.5 10l-2 2 2 2" class="chev"></path>
                    </svg>
                </button>
            </div>
            <nav class="nav">
                <div class="nav-label">Workspace</div>
                <a href="{{ route('admin.leads.index') }}" title="Leads"
                    class="{{ request()->routeIs('admin.leads.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"></path>
                        <path d="M14 3v5h5"></path>
                        <path d="M9 13h6M9 17h4"></path>
                    </svg><span class="label">Leads</span>
                </a>
                <a href="{{ route('admin.affiliates.index') }}" title="Affiliates"
                    class="{{ request()->routeIs('admin.affiliates.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="9" cy="8" r="3.5"></circle>
                        <path d="M2.5 20c.8-3.5 3.4-5.5 6.5-5.5s5.7 2 6.5 5.5"></path>
                        <path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14.8c1.9.8 3.1 2.6 3.5 5.2"></path>
                    </svg><span class="label">Affiliates</span>
                </a>

                <div class="nav-label">Settings</div>
                <a href="{{ route('admin.account') }}" title="Password"
                    class="{{ request()->routeIs('admin.account*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="4" y="11" width="16" height="10" rx="2"></rect>
                        <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>
                    </svg><span class="label">Password</span>
                </a>
                <a href="{{ url(config('horizon.path', 'horizon')) }}" target="_blank" rel="noopener"
                    title="Horizon queue monitor">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 12h4l3-8 4 16 3-8h4"></path>
                    </svg><span class="label">Horizon <small>Queues</small></span>
                    <svg class="ext" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"></path>
                    </svg>
                </a>
            </nav>
            <div class="side-foot">
                <div class="side-user label">{{ auth()->user()->email }}</div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="logout" title="Log out">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <path d="M16 17l5-5-5-5M21 12H9"></path>
                        </svg><span class="label">Log out</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="main">
            @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error" role="alert">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        (function() {
            const btn = document.getElementById('collapseBtn');
            const sync = () => {
                const collapsed = document.body.classList.contains('collapsed');
                btn.setAttribute('aria-expanded', String(!collapsed));
                btn.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
            };
            btn.addEventListener('click', function() {
                document.body.classList.toggle('collapsed');
                try {
                    localStorage.setItem('dps-admin-sidebar', document.body.classList.contains('collapsed') ? 'collapsed' : 'open');
                } catch (e) {}
                sync();
            });
            sync();
        })();

        document.addEventListener('click', function(e) {
            const copy = e.target.closest('[data-copy]');
            if (copy) {
                navigator.clipboard.writeText(copy.dataset.copy).then(function() {
                    const label = copy.textContent;
                    copy.textContent = 'Copied';
                    setTimeout(() => copy.textContent = label, 1500);
                });
                return;
            }
            const row = e.target.closest('tr[data-href]');
            if (row && !e.target.closest('a, button, form')) {
                window.location = row.dataset.href;
            }
        });
        document.querySelectorAll('form[data-confirm]').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                if (!confirm(form.dataset.confirm)) e.preventDefault();
            });
        });
    </script>
</body>

</html>
