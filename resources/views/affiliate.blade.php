@use('App\Support\LandingContent')
@use('App\Support\LandingVideo')
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Up to $1.5M+ Upfront Cash for Restaurants &amp; Bars | DPS Payments</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Growth capital for restaurants and bars. No interest, no monthly payments and no personal collateral. You pay it back in food and drinks.">
    <link href="{{ asset('logo.jpeg') }}" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --cream: #F4EEE0;
            --paper: #FBF8EE;
            --tile: #F7F1E3;
            --forest: #121E18;
            --forest-2: #1C2922;
            --forest-line: #34443A;
            --ink: #151A16;
            --ink-2: #3F4640;
            --muted: #535A52;
            --line: #E2D9C4;
            --gold: #BFA15A;
            --gold-2: #C9AD62;
            --gold-ink: #8F7431;
            --on-dark: #E9E4D6;
            --on-dark-2: #B5B5A6;
            --serif: 'Cormorant Garamond', Garamond, Georgia, serif;
            --sans: Inter, system-ui, -apple-system, "Segoe UI", sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 76px;
        }

        body {
            margin: 0;
            font-family: var(--sans);
            background: var(--paper);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        a:focus-visible,
        summary:focus-visible,
        video:focus-visible,
        iframe:focus-visible {
            outline: 2px solid var(--gold);
            outline-offset: 2px;
        }

        h1,
        h2,
        h3 {
            font-family: var(--serif);
            font-weight: 400;
            margin: 0;
        }

        p {
            margin: 0;
        }

        .wrap {
            width: 100%;
            max-width: 1080px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .eyebrow {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--gold-ink);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 15px;
            transition: background .15s ease, transform .15s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-gold {
            padding: 15px 40px;
            background: var(--gold-2);
            color: var(--ink);
        }

        .btn-gold:hover {
            background: #D4B971;
        }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 10;
            background: var(--cream);
            border-bottom: 1px solid var(--line);
        }

        .site-header .wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            min-height: 72px;
        }

        .brand-logo {
            display: block;
            width: 72px;
            height: auto;
        }

        .site-nav {
            display: flex;
            gap: 36px;
            font-size: 15px;
            font-weight: 500;
        }

        .site-nav a:hover {
            color: var(--gold-ink);
        }

        .btn-nav {
            padding: 10px 22px;
            background: var(--forest);
            color: #FBF8EE;
            font-size: 14px;
        }

        .btn-nav:hover {
            background: var(--forest-2);
        }

        .hero {
            background: var(--forest);
            color: #FBF8EE;
            padding: 72px 0 64px;
            text-align: center;
        }

        .hero .eyebrow {
            color: var(--gold-2);
        }

        .hero h1 {
            max-width: 520px;
            margin: 24px auto 0;
            font-size: 68px;
            line-height: 1.12;
        }

        .hero h1 em {
            color: var(--gold-2);
            font-weight: 500;
        }

        .hero-sub {
            max-width: 680px;
            margin: 28px auto 0;
            font-size: 18px;
            line-height: 1.6;
            color: var(--on-dark);
        }

        .hero-sub strong {
            color: var(--gold-2);
            font-weight: 500;
        }

        .video {
            position: relative;
            max-width: 900px;
            aspect-ratio: 16 / 9;
            margin: 44px auto 0;
            overflow: hidden;
            background: radial-gradient(ellipse at center, #26382E 0%, #16241D 70%);
        }

        .video iframe {
            display: block;
            width: 100%;
            height: 100%;
            border: 0;
        }

        .video video {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-brand {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .video-brand img {
            width: 32%;
            height: auto;
            opacity: .9;
        }

        .perks {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px 30px;
            margin-top: 28px;
            font-size: 14px;
            color: var(--on-dark);
        }

        .perks div {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .hero-cta {
            margin-top: 32px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            margin-top: 56px;
            padding-top: 44px;
            border-top: 1px solid var(--forest-line);
            text-align: left;
        }

        .stats.will-animate .stat {
            opacity: 0;
            transform: translateY(16px);
        }

        .stats.animated .stat {
            opacity: 1;
            transform: none;
            transition: opacity .7s ease, transform .7s ease;
        }

        .stats.animated .stat:nth-child(2) {
            transition-delay: .15s;
        }

        .stats.animated .stat:nth-child(3) {
            transition-delay: .3s;
        }

        .stat strong {
            display: block;
            font-family: var(--serif);
            font-size: 46px;
            font-weight: 500;
            line-height: 1.1;
            letter-spacing: 1px;
            color: var(--gold-2);
        }

        .stat span {
            display: block;
            margin-top: 12px;
            font-size: 14px;
            color: var(--on-dark-2);
        }

        .section {
            padding: 96px 0;
        }

        .section-cream {
            background: var(--cream);
        }

        .section-head {
            text-align: center;
            margin-bottom: 48px;
        }

        .section-head h2,
        .funding h2,
        .faq h2 {
            font-size: 52px;
            line-height: 1.15;
            margin-top: 14px;
        }

        .steps,
        .benefits,
        .qualify {
            display: grid;
            gap: 20px;
        }

        .steps {
            grid-template-columns: repeat(3, 1fr);
        }

        .step,
        .benefit {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 16px;
        }

        .step {
            padding: 32px;
        }

        .step-num {
            font-family: var(--serif);
            font-size: 22px;
            color: var(--gold-ink);
        }

        .step h3 {
            margin-top: 14px;
            font-size: 28px;
            font-weight: 500;
            line-height: 1.2;
        }

        .step p {
            margin-top: 16px;
            font-size: 15px;
            line-height: 1.65;
            color: var(--ink-2);
        }

        .benefits {
            grid-template-columns: repeat(4, 1fr);
            margin-top: 28px;
        }

        .benefit {
            padding: 22px 24px;
        }

        .benefit h3 {
            font-size: 23px;
            line-height: 1.2;
        }

        .benefit p {
            margin-top: 10px;
            font-size: 14px;
            line-height: 1.55;
            color: var(--ink-2);
        }

        .funding {
            max-width: 840px;
            margin: 0 auto;
        }

        .tiers {
            margin-top: 32px;
            padding: 12px 28px;
            background: var(--tile);
            border: 1px solid var(--line);
            border-radius: 16px;
        }

        .tier {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 20px 0;
        }

        .tier h3 {
            font-size: 26px;
            line-height: 1.2;
        }

        .tier p {
            margin-top: 4px;
            font-size: 14px;
            color: var(--muted);
        }

        .tier strong {
            flex: none;
            font-family: var(--serif);
            font-size: 30px;
            font-weight: 400;
            color: var(--gold-ink);
            text-align: right;
        }

        .qualify {
            grid-template-columns: 1.4fr 1fr 1fr;
            margin-top: 20px;
        }

        .qualify>div {
            padding: 22px 24px;
            background: var(--tile);
            border: 1px solid var(--line);
            border-radius: 14px;
        }

        .qualify h3 {
            font-size: 22px;
            line-height: 1.2;
        }

        .qualify p {
            margin-top: 6px;
            font-size: 14px;
            line-height: 1.5;
            color: var(--muted);
        }

        .funding-note {
            margin-top: 24px;
            font-size: 15px;
            font-style: italic;
            color: var(--muted);
        }

        .apply-card {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 48px;
            padding: 56px;
            background: var(--forest);
            color: #FBF8EE;
            border-radius: 20px;
            box-shadow: 0 24px 50px rgba(18, 30, 24, .22);
        }

        .apply-card .eyebrow {
            color: var(--gold-2);
        }

        .apply-card h2 {
            margin-top: 18px;
            font-size: 46px;
            line-height: 1.15;
        }

        .apply-card h2 em {
            color: var(--gold-2);
        }

        .apply-card>div>p {
            margin-top: 20px;
            font-size: 16px;
            line-height: 1.65;
            color: var(--on-dark);
        }

        .apply-card .btn-gold {
            margin-top: 28px;
        }

        .apply-secure {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 14px;
            font-size: 13px;
            color: var(--on-dark-2);
        }

        .ready {
            padding: 32px;
            background: var(--forest-2);
            border: 1px solid var(--forest-line);
            border-radius: 16px;
        }

        .ready h3 {
            font-family: var(--sans);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 3px;
            color: var(--gold-2);
        }

        .ready ul {
            list-style: none;
            margin: 20px 0 0;
            padding: 0;
        }

        .ready li {
            display: flex;
            gap: 10px;
            margin-top: 12px;
            font-size: 15px;
            line-height: 1.5;
            color: var(--on-dark);
        }

        .ready li svg {
            flex: none;
            margin-top: 4px;
        }

        .ready li span {
            color: var(--on-dark-2);
        }

        .ready-note {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid var(--forest-line);
            font-size: 14px;
            line-height: 1.55;
            color: var(--on-dark-2);
        }

        .faq {
            max-width: 760px;
            margin: 0 auto;
        }

        .faq-list {
            margin-top: 36px;
            border-bottom: 1px solid var(--line);
        }

        .faq-list details {
            border-top: 1px solid var(--line);
        }

        .faq-list summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 20px 8px;
            font-family: var(--serif);
            font-size: 24px;
            font-weight: 500;
            cursor: pointer;
            list-style: none;
        }

        .faq-list summary::-webkit-details-marker {
            display: none;
        }

        .faq-list summary::after {
            content: '+';
            flex: none;
            font-family: var(--sans);
            font-size: 22px;
            font-weight: 400;
        }

        .faq-list details[open] summary::after {
            content: '\2212';
        }

        .faq-list p {
            padding: 0 40px 22px 8px;
            font-size: 16px;
            line-height: 1.65;
            color: var(--ink-2);
        }

        .step,
        .benefit,
        .tiers,
        .qualify>div,
        .ready {
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }

        @media (hover: hover) {

            .step:hover,
            .benefit:hover,
            .tiers:hover,
            .qualify>div:hover {
                transform: translateY(-4px);
                border-color: var(--gold);
                box-shadow: 0 14px 30px rgba(18, 30, 24, .1);
            }

            .ready:hover {
                transform: translateY(-4px);
                border-color: var(--gold);
                box-shadow: 0 14px 30px rgba(0, 0, 0, .3);
            }
        }

        .site-footer {
            padding: 56px 0 40px;
            background: var(--cream);
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .footer-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px 24px;
            margin-bottom: 18px;
        }

        .footer-brand {
            display: flex;
            align-items: baseline;
            gap: 14px;
        }

        .footer-brand strong {
            font-family: var(--serif);
            font-size: 30px;
            font-weight: 500;
            color: var(--ink);
        }

        .footer-brand i {
            color: var(--gold);
            font-style: normal;
        }

        .footer-brand span {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
        }

        .footer-contact {
            font-size: 16px;
            color: var(--ink);
        }

        .footer-contact a:hover {
            color: var(--gold-ink);
        }

        .copy {
            margin-top: 18px;
            font-size: 13px;
        }

        @media (max-width: 960px) {
            .site-nav {
                display: none;
            }

            .steps,
            .apply-card {
                grid-template-columns: 1fr;
            }

            .benefits {
                grid-template-columns: repeat(2, 1fr);
            }

            .qualify {
                grid-template-columns: 1fr 1fr;
            }

            .qualify>div:first-child {
                grid-column: 1 / -1;
            }

            .apply-card {
                padding: 40px 32px;
            }
        }

        @media (max-width: 640px) {
            .wrap {
                padding: 0 16px;
            }

            .hero {
                padding: 48px 0;
            }

            .hero h1 {
                font-size: 44px;
            }

            .hero-sub {
                font-size: 16px;
            }

            .perks {
                flex-direction: column;
                align-items: flex-start;
                width: fit-content;
                margin-left: auto;
                margin-right: auto;
            }

            .stats,
            .benefits,
            .qualify {
                grid-template-columns: 1fr;
            }

            .stats {
                gap: 24px;
                margin-top: 40px;
                padding-top: 32px;
            }

            .section {
                padding: 64px 0;
            }

            .section-head h2,
            .funding h2,
            .faq h2,
            .apply-card h2 {
                font-size: 38px;
            }

            .tiers {
                padding: 8px 20px;
            }

            .tier {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }

            .tier strong {
                font-size: 26px;
                text-align: left;
            }

            .apply-card {
                gap: 32px;
                padding: 32px 20px;
            }

            .apply-card .btn-gold {
                width: 100%;
            }

            .ready {
                padding: 24px 20px;
            }

            .faq-list summary {
                font-size: 21px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            .btn,
            .step,
            .benefit,
            .tiers,
            .qualify>div,
            .ready {
                transition: none;
            }

            .btn:hover,
            .step:hover,
            .benefit:hover,
            .tiers:hover,
            .qualify>div:hover,
            .ready:hover {
                transform: none;
            }
        }
    </style>
</head>

<body>

    @php
        $c = $affiliate->landingContent();
        $video = LandingContent::video($c);
        $videoPoster = $c['video_poster'] ?: config('services.landing.video_poster');
        $check = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#C9AD62" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L20 7"></path></svg>';
    @endphp

    <header class="site-header">
        <div class="wrap">
            <a href="{{ $affiliate->landingLink() }}" aria-label="DPS Payments Corp.">
                <img src="{{ asset('images/dps-logo.png') }}" alt="DPS Payments Corp." class="brand-logo">
            </a>
            <nav class="site-nav" aria-label="Page sections">
                <a href="#watch">{{ $c['nav_watch'] }}</a>
                <a href="#how-it-works">{{ $c['nav_how'] }}</a>
                <a href="#funding">{{ $c['nav_funding'] }}</a>
                <a href="#faq">{{ $c['nav_faq'] }}</a>
            </nav>
            <a href="{{ LandingContent::buttonUrl($c, 'nav_button', $affiliate->link()) }}" class="btn btn-nav">{{ $c['nav_button'] }}</a>
        </div>
    </header>

    <section class="hero" id="watch">
        <div class="wrap">
            <div class="eyebrow">{{ $c['hero_eyebrow'] }}</div>
            <h1>{{ LandingContent::highlight($c['hero_title']) }}</h1>
            <p class="hero-sub">{{ LandingContent::highlight($c['hero_text'], 'strong') }}</p>

            <div class="video">
                @if ($video && $video['type'] === 'code')
                    {{-- No allow-same-origin: pasted scripts can't reach this page or its visitors' cookies --}}
                    <iframe srcdoc="{{ LandingVideo::document($video['html']) }}" title="DPS Payments video"
                        sandbox="allow-scripts allow-presentation allow-popups allow-popups-to-escape-sandbox"
                        allow="autoplay; encrypted-media; fullscreen; picture-in-picture"></iframe>
                @elseif ($video && $video['type'] === 'embed')
                    <iframe src="{{ $video['src'] }}" title="DPS Payments video" loading="lazy"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                @elseif ($video)
                    <video src="{{ $video['src'] }}" controls playsinline preload="metadata" @if ($videoPoster) poster="{{ $videoPoster }}" @endif></video>
                @else
                    <div class="video-brand">
                        <img src="{{ asset('images/dps-logo-light.png') }}" alt="">
                    </div>
                @endif
            </div>

            <div class="perks">
                @foreach (range(1, 5) as $i)
                    <div>{!! $check !!}{{ $c["perk_{$i}"] }}</div>
                @endforeach
            </div>

            <div class="hero-cta">
                <a href="{{ LandingContent::buttonUrl($c, 'hero_button', $affiliate->link()) }}" class="btn btn-gold">{{ $c['hero_button'] }}</a>
            </div>

            <div class="stats" id="stats">
                @foreach (range(1, 3) as $i)
                    <div class="stat"><strong>{{ $c["stat_{$i}_value"] }}</strong><span>{{ $c["stat_{$i}_label"] }}</span></div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section section-cream" id="how-it-works">
        <div class="wrap">
            <div class="section-head">
                <div class="eyebrow">{{ $c['steps_eyebrow'] }}</div>
                <h2>{{ $c['steps_title'] }}</h2>
            </div>

            <div class="steps">
                @foreach (range(1, 3) as $i)
                    <div class="step">
                        <div class="step-num">0{{ $i }}</div>
                        <h3>{{ $c["step_{$i}_title"] }}</h3>
                        <p>{{ $c["step_{$i}_text"] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="benefits">
                @foreach (range(1, 4) as $i)
                    <div class="benefit">
                        <h3>{{ $c["benefit_{$i}_title"] }}</h3>
                        <p>{{ $c["benefit_{$i}_text"] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section" id="funding">
        <div class="wrap">
            <div class="funding">
                <div class="eyebrow">{{ $c['funding_eyebrow'] }}</div>
                <h2>{{ $c['funding_title'] }}</h2>

                <div class="tiers">
                    @foreach (range(1, 3) as $i)
                        <div class="tier">
                            <div>
                                <h3>{{ $c["tier_{$i}_title"] }}</h3>
                                <p>{{ $c["tier_{$i}_text"] }}</p>
                            </div>
                            <strong>{{ $c["tier_{$i}_amount"] }}</strong>
                        </div>
                    @endforeach
                </div>

                <div class="qualify">
                    @foreach (range(1, 3) as $i)
                        <div>
                            <h3>{{ $c["qualify_{$i}_title"] }}</h3>
                            <p>{{ $c["qualify_{$i}_text"] }}</p>
                        </div>
                    @endforeach
                </div>

                <p class="funding-note">{{ $c['funding_note'] }}</p>
            </div>
        </div>
    </section>

    <section class="section section-cream" id="apply">
        <div class="wrap">
            <div class="apply-card">
                <div>
                    <div class="eyebrow">{{ $c['apply_eyebrow'] }}</div>
                    <h2>{{ LandingContent::highlight($c['apply_title']) }}</h2>
                    <p>{{ $c['apply_text'] }}</p>
                    <a href="{{ LandingContent::buttonUrl($c, 'apply_button', $affiliate->link()) }}" class="btn btn-gold">{{ $c['apply_button'] }}</a>
                    <div class="apply-secure">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="4" y="11" width="16" height="10" rx="2"></rect>
                            <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>
                        </svg>
                        {{ $c['apply_secure'] }}
                    </div>
                </div>
                <div class="ready">
                    <h3>{{ $c['ready_title'] }}</h3>
                    <ul>
                        @foreach (range(1, 4) as $i)
                            <li>{!! $check !!}<div>{{ LandingContent::highlight($c["ready_{$i}"], 'span') }}</div></li>
                        @endforeach
                    </ul>
                    <p class="ready-note">{{ $c['ready_note'] }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="faq">
        <div class="wrap">
            <div class="faq">
                <div class="eyebrow">{{ $c['faq_eyebrow'] }}</div>
                <h2>{{ $c['faq_title'] }}</h2>

                <div class="faq-list">
                    @foreach (range(1, 9) as $i)
                        <details>
                            <summary>{{ $c["faq_{$i}_question"] }}</summary>
                            <p>{{ $c["faq_{$i}_answer"] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <footer class="site-footer">
        <div class="wrap">
            <div class="footer-top">
                <div class="footer-brand">
                    <strong>DPS<i>.</i></strong>
                    <span>CAPITAL · PAYMENT PROCESSING</span>
                </div>
                <div class="footer-contact"><a href="tel:+16468254477">(646) 825-4477</a> · <a
                        href="mailto:info@dpspayments.com">info@dpspayments.com</a></div>
            </div>
            <p>DPS Payments is an all-in-one payment processing and growth capital company for restaurants and bars.
                Payment processing and funding are provided through our sponsor banks and partner network under
                separate agreements. Amounts, pricing, approval and timing are determined by our partners and are not
                guaranteed.</p>
            <div class="copy">© {{ date('Y') }} DPS Payments. All rights reserved.</div>
        </div>
    </footer>

    <script>
        // Stats count up the first time they scroll into view. The real values are in the
        // page, so without JavaScript or with reduced motion they simply show as they are.
        (function() {
            const stats = document.getElementById('stats');
            const DURATION = 1600;

            if (!('IntersectionObserver' in window) || matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            function countUp(el, delay) {
                const text = el.textContent;
                const match = text.match(/\d[\d,]*(?:\.\d+)?/);

                if (!match) {
                    return;
                }

                const target = parseFloat(match[0].replace(/,/g, ''));
                const decimals = (match[0].split('.')[1] || '').length;
                const format = new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals,
                    useGrouping: match[0].includes(','),
                });
                const render = function(value) {
                    el.textContent = text.slice(0, match.index) + format.format(value) + text.slice(match.index + match[0].length);
                };
                let start = null;

                function frame(now) {
                    start = start ?? now + delay;
                    const progress = Math.min(Math.max(now - start, 0) / DURATION, 1);

                    if (progress < 1) {
                        render(target * (1 - Math.pow(1 - progress, 3)));
                        requestAnimationFrame(frame);
                    } else {
                        el.textContent = text;
                    }
                }

                render(0);
                requestAnimationFrame(frame);
            }

            stats.classList.add('will-animate');

            new IntersectionObserver(function(entries, observer) {
                if (!entries[0].isIntersecting) {
                    return;
                }

                observer.disconnect();
                stats.classList.add('animated');
                stats.querySelectorAll('strong').forEach(function(el, i) {
                    countUp(el, i * 150);
                });
            }, {
                threshold: 0.3
            }).observe(stats);
        })();
    </script>

</body>

</html>
