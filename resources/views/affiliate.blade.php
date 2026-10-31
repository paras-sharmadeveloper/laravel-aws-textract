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
    @include('partials.css', ['files' => ['global', 'landing']])
</head>

<body>

    @php
        $c = $affiliate->landingContent();
        $video = LandingContent::video($c);
        $videoPoster = $c['video_poster'] ?: config('services.landing.video_poster');
        $check =
            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#C9AD62" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L20 7"></path></svg>';
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
            <a href="{{ LandingContent::buttonUrl($c, 'nav_button', $affiliate->link()) }}"
                class="btn btn-nav">{{ $c['nav_button'] }}</a>
        </div>
    </header>

    <section class="hero" id="watch">
        <div class="wrap">
            <div class="eyebrow">{{ $c['hero_eyebrow'] }}</div>
            <h1>{{ LandingContent::highlight($c['hero_title']) }}</h1>


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
                    <video src="{{ $video['src'] }}" controls playsinline preload="metadata"
                        @if ($videoPoster) poster="{{ $videoPoster }}" @endif></video>
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
                <a href="{{ LandingContent::buttonUrl($c, 'hero_button', $affiliate->link()) }}"
                    class="btn btn-gold">{{ $c['hero_button'] }}</a>
                @if ($affiliate->phone || $affiliate->email)
                    <div class="hero-contact">
                        @if ($affiliate->phone)
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $affiliate->phone) }}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path
                                        d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2">
                                    </path>
                                </svg>{{ $affiliate->phone }}
                            </a>
                        @endif
                        @if ($affiliate->email)
                            <a href="mailto:{{ $affiliate->email }}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                    <path d="M3 7l9 6 9-6"></path>
                                </svg>{{ $affiliate->email }}
                            </a>
                        @endif
                    </div>
                @endif
                <p class="hero-sub">{{ LandingContent::highlight($c['hero_text'], 'strong') }}</p>
            </div>

            <div class="stats" id="stats">
                @foreach (range(1, 3) as $i)
                    <div class="stat">
                        <strong>{{ $c["stat_{$i}_value"] }}</strong><span>{{ $c["stat_{$i}_label"] }}</span>
                    </div>
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
                    <a href="{{ LandingContent::buttonUrl($c, 'apply_button', $affiliate->link()) }}"
                        class="btn btn-gold">{{ $c['apply_button'] }}</a>
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
                            <li>{!! $check !!}<div>{{ LandingContent::highlight($c["ready_{$i}"], 'span') }}
                                </div>
                            </li>
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
                    el.textContent = text.slice(0, match.index) + format.format(value) + text.slice(match.index +
                        match[0].length);
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
