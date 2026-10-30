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
    @include('partials.css', ['files' => ['global', 'admin']])
    @stack('styles')
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
                @can('leads.view')
                    <a href="{{ route('admin.leads.index') }}" title="Leads"
                        class="{{ request()->routeIs('admin.leads.*') ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"></path>
                            <path d="M14 3v5h5"></path>
                            <path d="M9 13h6M9 17h4"></path>
                        </svg><span class="label">Leads</span>
                    </a>
                @endcan
                @can('affiliates.manage')
                    <a href="{{ route('admin.affiliates.index') }}" title="Affiliates"
                        class="{{ request()->routeIs('admin.affiliates.*') ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="9" cy="8" r="3.5"></circle>
                            <path d="M2.5 20c.8-3.5 3.4-5.5 6.5-5.5s5.7 2 6.5 5.5"></path>
                            <path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14.8c1.9.8 3.1 2.6 3.5 5.2"></path>
                        </svg><span class="label">Affiliates</span>
                    </a>
                @endcan
                
                <div class="nav-label">Settings</div>
                @can('integrations.view')
                    <a href="{{ route('admin.integrations') }}" title="Integrations"
                        class="{{ request()->routeIs('admin.integrations*') ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9 7V3M15 7V3"></path>
                            <path d="M6 7h12v4a6 6 0 0 1-12 0z"></path>
                            <path d="M12 17v4"></path>
                        </svg><span class="label">Integrations</span>
                    </a>
                @endcan
                @can('users.manage')
                    <a href="{{ route('admin.users.index') }}" title="Users"
                        class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="8" r="4"></circle>
                            <path d="M4 21c.9-4 4-6.5 8-6.5s7.1 2.5 8 6.5"></path>
                        </svg><span class="label">Users</span>
                    </a>
                    <a href="{{ route('admin.roles.index') }}" title="Roles"
                        class="{{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 3l8 3v6c0 4.5-3.4 8.3-8 9-4.6-.7-8-4.5-8-9V6z"></path>
                            <path d="M9 12l2 2 4-4"></path>
                        </svg><span class="label">Roles</span>
                    </a>
                @endcan
                <a href="{{ route('admin.account') }}" title="Password"
                    class="{{ request()->routeIs('admin.account*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="4" y="11" width="16" height="10" rx="2"></rect>
                        <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>
                    </svg><span class="label">Password</span>
                </a>
                @can('horizon.view')
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
                @endcan
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

    <dialog id="confirmBox" class="confirm-box" aria-labelledby="confirmTitle">
        <form method="dialog">
            <h3 id="confirmTitle">Are you sure?</h3>
            <p id="confirmText"></p>
            <div class="actions">
                <button type="submit" value="cancel" class="btn" autofocus>Cancel</button>
                <button type="submit" value="ok" id="confirmOk" class="btn btn-primary">Confirm</button>
            </div>
        </form>
    </dialog>

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
        (function() {
            const box = document.getElementById('confirmBox');
            const ok = document.getElementById('confirmOk');
            let pending = null;

            document.querySelectorAll('form[data-confirm]').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    if (form.dataset.confirmed) return;

                    // Older browsers without <dialog> fall back to the plain prompt
                    if (typeof box.showModal !== 'function') {
                        if (!confirm(form.dataset.confirm)) e.preventDefault();
                        return;
                    }

                    e.preventDefault();
                    pending = form;
                    document.getElementById('confirmTitle').textContent = form.dataset.confirmTitle || 'Are you sure?';
                    document.getElementById('confirmText').textContent = form.dataset.confirm;
                    box.returnValue = '';
                    ok.textContent = form.dataset.confirmButton || 'Confirm';
                    ok.className = 'btn ' + ('confirmDanger' in form.dataset ? 'btn-danger' : 'btn-primary');
                    box.showModal();
                });
            });

            box.addEventListener('close', function() {
                if (box.returnValue === 'ok' && pending) {
                    pending.dataset.confirmed = '1';
                    pending.requestSubmit ? pending.requestSubmit() : pending.submit();
                }
                pending = null;
            });
        })();
    </script>
</body>

</html>
