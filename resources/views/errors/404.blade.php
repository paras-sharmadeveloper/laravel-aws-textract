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
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #F4EEE0;
            color: #151A16;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        header {
            display: flex;
            align-items: center;
            gap: 22px;
            min-height: 84px;
            padding: 0 64px;
            border-bottom: 3px solid #D9D2C2;
        }

        .brand {
            display: flex;
            flex-direction: column;
        }

        .brand b {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 34px;
            line-height: 1;
            color: #1E2A44;
            letter-spacing: -0.5px;
        }

        .brand span {
            font-size: 8px;
            letter-spacing: 2px;
            color: #1E2A44;
            margin-top: 3px;
        }

        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 56px 24px 72px;
        }

        .wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            max-width: 560px;
            text-align: center;
            animation: rise .7s cubic-bezier(.2, .7, .2, 1) both;
        }

        /* Illustration: a document floating, a lens sweeping across it */
        .scene {
            position: relative;
            width: 180px;
            height: 170px;
            margin-bottom: 8px;
        }

        .doc {
            position: absolute;
            left: 34px;
            top: 6px;
            width: 112px;
            height: 140px;
            padding: 22px 18px;
            background: #FBF8EE;
            border: 1.5px dashed #CFC3A6;
            border-radius: 16px;
            animation: float 4s ease-in-out infinite;
        }

        .doc i {
            display: block;
            height: 7px;
            margin-bottom: 11px;
            border-radius: 4px;
            background: #E2D9C4;
        }

        .doc i:nth-child(1) {
            width: 60%;
            background: #BFA15A;
            opacity: .7;
        }

        .doc i:nth-child(3) {
            width: 80%;
        }

        .doc i:nth-child(5) {
            width: 45%;
        }

        .shadow {
            position: absolute;
            left: 50px;
            bottom: 0;
            width: 80px;
            height: 10px;
            border-radius: 50%;
            background: rgba(18, 30, 24, .12);
            animation: shadow 4s ease-in-out infinite;
        }

        .lens {
            position: absolute;
            top: 40px;
            left: 0;
            width: 64px;
            height: 64px;
            color: #121E18;
            animation: sweep 4s ease-in-out infinite;
        }

        .eyebrow {
            font-size: 13px;
            letter-spacing: 4px;
            color: #8F7431;
        }

        h1 {
            margin: 0;
            font-family: 'Cormorant Garamond', Garamond, Georgia, serif;
            font-weight: 400;
            font-size: 58px;
            line-height: 1.05;
        }

        h1 em {
            color: #A88A3F;
        }

        p {
            margin: 0;
            font-size: 17px;
            line-height: 1.6;
            color: #3F4640;
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            height: 50px;
            padding: 0 24px;
            border-radius: 26px;
            font-size: 15px;
            font-weight: 600;
            transition: background .15s, transform .15s;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-dark {
            background: #121E18;
            color: #FBF8EE;
        }

        .btn-dark:hover {
            background: #22332A;
        }

        .btn-line {
            border: 1.5px solid #151A16;
        }

        .btn-line:hover {
            background: #E8E0CC;
        }

        footer {
            padding: 24px 16px 32px;
            text-align: center;
            font-size: 14px;
            color: #3F4640;
            border-top: 1px solid #E2D9C4;
        }

        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(14px);
            }
        }

        @keyframes float {
            50% {
                transform: translateY(-8px) rotate(-1.5deg);
            }
        }

        @keyframes shadow {
            50% {
                transform: scaleX(.82);
                opacity: .6;
            }
        }

        @keyframes sweep {
            0%,
            100% {
                transform: translate(0, 0) rotate(-8deg);
            }

            50% {
                transform: translate(112px, 22px) rotate(8deg);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                animation: none !important;
            }
        }

        @media (max-width: 640px) {
            header {
                padding: 12px 16px;
            }

            h1 {
                font-size: 42px;
            }

            p {
                font-size: 16px;
            }
        }
    </style>
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
