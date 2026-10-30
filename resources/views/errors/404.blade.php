<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page not found · DPS Payments</title>
    <link href="{{ asset('logo.jpeg') }}" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    @include('partials.css', ['files' => ['global', 'error']])
</head>

<body>
    <header>
        <a href="{{ url('/') }}" class="brand" aria-label="DPS Payments Corp.">
            <img src="{{ asset('images/dps-logo.png') }}" alt="DPS Payments Corp." width="104">
        </a>
    </header>

    <main>
        <div class="wrap">
            <div class="scene" aria-hidden="true">
                <div class="doc"><i></i><i></i><i></i><i></i><i></i></div>
                <div class="shadow"></div>
                <svg class="lens" viewBox="0 0 64 64" fill="none">
                    <circle cx="26" cy="26" r="17" fill="rgba(251,248,238,.55)" stroke="currentColor" stroke-width="4" />
                    <path d="M39 39l15 15" stroke="currentColor" stroke-width="6" stroke-linecap="round" />
                    <path d="M18 20a10 10 0 0 1 8-5" stroke="#BFA15A" stroke-width="3" stroke-linecap="round" />
                </svg>
            </div>

            <div class="eyebrow">ERROR 404</div>
            <h1>This link <em>isn't active.</em></h1>
            <p>The page you're looking for doesn't exist or is no longer available. If someone shared an application
                link with you, please check it with them — or start a new application below.</p>

            <div class="actions">
                <a href="{{ url('/') }}" class="btn btn-dark">Start an application →</a>
                <a href="mailto:info@dpspayments.com" class="btn btn-line">Contact us</a>
            </div>
        </div>
    </main>

    <footer>(646) 825-4477 · info@dpspayments.com · © {{ date('Y') }} DPS Payments</footer>
</body>

</html>
