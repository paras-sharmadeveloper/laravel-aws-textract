<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in · DPS Admin</title>
    <link href="{{ asset('logo.jpeg') }}" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    @include('partials.css', ['files' => ['global', 'login']])
</head>

<body>
    <form class="card" method="POST" action="{{ route('admin.login') }}">
        @csrf
        <div class="brand">
            <img src="{{ asset('images/dps-logo.png') }}" alt="DPS Payments Corp." width="124">
            <span>ADMIN</span>
        </div>
        <h1>Welcome <em>back.</em></h1>
        <p class="lead">Sign in to manage leads and affiliates.</p>

        @if (session('error') || $errors->any())
            <div class="alert" role="alert">{{ session('error') ?? $errors->first() }}</div>
        @endif

        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" class="input" value="{{ old('email') }}" required autofocus
                autocomplete="username">
        </div>
        <div class="field">
            <label for="password">Password</label>
            <div class="pw">
                <input id="password" name="password" type="password" class="input" required
                    autocomplete="current-password">
                <button type="button" class="pw-toggle" id="pwToggle" aria-label="Show password"
                    aria-controls="password" aria-pressed="false">
                    <svg class="eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <svg class="eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <path d="M10.6 5.1A10.4 10.4 0 0 1 12 5c6.4 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.2"></path>
                        <path d="M6.6 6.6C3.7 8.4 2 12 2 12s3.6 7 10 7a9.7 9.7 0 0 0 5.4-1.6"></path>
                        <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"></path>
                        <path d="M3 3l18 18"></path>
                    </svg>
                </button>
            </div>
        </div>
        <label class="remember"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
        <button type="submit">Sign in</button>
    </form>
    <script>
        (function() {
            const btn = document.getElementById('pwToggle');
            const input = document.getElementById('password');
            btn.addEventListener('click', function() {
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.setAttribute('aria-pressed', String(show));
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                input.focus();
            });
        })();
    </script>
</body>

</html>
