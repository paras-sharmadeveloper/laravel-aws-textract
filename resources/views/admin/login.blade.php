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
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            background: #121E18;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif;
            color: #151A16;
            -webkit-font-smoothing: antialiased;
        }

        input,
        button {
            font: inherit;
        }

        .card {
            width: 100%;
            max-width: 420px;
            padding: 44px 40px;
            background: #FBF8EE;
            border: 1px solid #E6DECB;
            border-radius: 28px;
        }

        .brand {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 22px;
            border-bottom: 1px solid #E2D9C4;
        }

        .brand img {
            display: block;
            height: auto;
        }

        .brand span {
            font-size: 12px;
            letter-spacing: 3px;
            color: #8F7431;
        }

        .pw {
            position: relative;
            display: flex;
        }

        .pw .input {
            flex: 1;
            min-width: 0;
            padding-right: 52px;
        }

        .pw-toggle {
            position: absolute;
            top: 5px;
            right: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            padding: 0;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #535A52;
            cursor: pointer;
        }

        .pw-toggle:hover {
            background: #F1EADA;
            color: #151A16;
        }

        .pw-toggle:focus-visible {
            outline: 2px solid #BFA15A;
        }

        .pw-toggle .eye-off,
        .pw-toggle[aria-pressed="true"] .eye {
            display: none;
        }

        .pw-toggle[aria-pressed="true"] .eye-off {
            display: block;
        }

        h1 {
            margin: 0 0 6px;
            font-family: 'Cormorant Garamond', Garamond, Georgia, serif;
            font-weight: 400;
            font-size: 40px;
            line-height: 1.05;
        }

        h1 em {
            color: #A88A3F;
        }

        p.lead {
            margin: 0 0 28px;
            color: #535A52;
            font-size: 15px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 18px;
        }

        label {
            font-size: 14px;
            font-weight: 600;
        }

        .input {
            height: 50px;
            padding: 0 16px;
            border: 1px solid #D8CFBA;
            border-radius: 12px;
            background: #FFFDF7;
            font-size: 16px;
        }

        .input:focus {
            outline: 2px solid #BFA15A;
            outline-offset: 1px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #3F4640;
        }

        .remember input {
            accent-color: #121E18;
        }

        button[type="submit"] {
            width: 100%;
            height: 52px;
            border: 0;
            border-radius: 26px;
            background: #121E18;
            color: #FBF8EE;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background: #22332A;
        }

        .alert {
            padding: 12px 14px;
            margin-bottom: 20px;
            border-radius: 12px;
            background: #F6E3D6;
            border: 1px solid #DDB89C;
            color: #7E3A12;
            font-size: 14px;
        }

        @media (max-width: 480px) {
            .card {
                padding: 32px 22px;
            }
        }
    </style>
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
