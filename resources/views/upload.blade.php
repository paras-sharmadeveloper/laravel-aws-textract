<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>DPS Merchant Application</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            --field: #FFFDF7;
            --tile: #F7F1E3;
            --forest: #121E18;
            --forest-2: #1C2922;
            --forest-line: #34443A;
            --ink: #151A16;
            --ink-2: #3F4640;
            --muted: #535A52;
            --line: #E2D9C4;
            --line-2: #D8CFBA;
            --dash: #CFC3A6;
            --sand: #E8E0CC;
            --gold: #BFA15A;
            --gold-2: #C9AD62;
            --gold-ink: #8F7431;
            --gold-num: #A88A3F;
            --req: #A2621E;
            --navy: #1E2A44;
            --serif: 'Cormorant Garamond', Garamond, Georgia, serif;
            --sans: Inter, system-ui, -apple-system, "Segoe UI", sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: var(--sans);
            background: var(--cream);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: var(--ink);
            text-decoration: none;
        }

        a:hover {
            color: var(--gold-ink);
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }

        input,
        button {
            font: inherit;
        }

        input:focus-visible,
        button:focus-visible {
            outline: 2px solid var(--gold);
            outline-offset: 1px;
        }

        input::placeholder {
            color: #8A8A7E;
        }

        .serif {
            font-family: var(--serif);
        }

        /* ---------- Header ---------- */
        .site-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            min-height: 84px;
            padding: 0 64px;
            border-bottom: 3px solid #D9D2C2;
        }

        .brand-wrap {
            display: flex;
            align-items: center;
            gap: 22px;
        }

        .brand {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .brand-logo {
            display: block;
            width: 104px;
            height: auto;
        }

        .brand-mark {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 34px;
            font-weight: 700;
            line-height: 1;
            color: var(--navy);
            letter-spacing: -0.5px;
        }

        .brand-sub {
            font-size: 8px;
            letter-spacing: 2px;
            color: var(--navy);
            margin-top: 3px;
        }

        .v-rule {
            width: 1px;
            height: 34px;
            background: #CFC6B2;
        }

        .secure {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            font-weight: 500;
            color: var(--ink-2);
        }

        .header-contact {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 15px;
            color: var(--ink-2);
        }

        .pill-btn {
            display: flex;
            align-items: center;
            height: 46px;
            padding: 0 20px;
            border: 1.5px solid var(--ink);
            border-radius: 24px;
            font-size: 15px;
            font-weight: 600;
            white-space: nowrap;
        }

        /* ---------- Hero ---------- */
        .hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 22px;
            padding: 72px 24px 64px;
            background: var(--forest);
            color: var(--cream);
            text-align: center;
        }

        .referral {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 18px 8px 8px;
            background: var(--forest-2);
            border: 1px solid var(--forest-line);
            border-radius: 30px;
        }

        .referral-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 17px;
            background: var(--gold);
            color: var(--forest);
            font-weight: 600;
            font-size: 13px;
            flex-shrink: 0;
        }

        .referral span {
            font-size: 15px;
            color: #D8D6CB;
        }

        .referral strong {
            color: var(--paper);
            font-weight: 600;
        }

        .eyebrow {
            font-size: 14px;
            letter-spacing: 4px;
            color: var(--gold);
        }

        .hero h1 {
            margin: 0;
            max-width: 1000px;
            font-family: var(--serif);
            font-weight: 400;
            font-size: 68px;
            line-height: 1.05;
            color: var(--paper);
        }

        .hero h1 em {
            color: var(--gold-2);
        }

        .hero p {
            margin: 0;
            max-width: 680px;
            font-size: 19px;
            line-height: 1.6;
            color: #D8D6CB;
        }

        .perks {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 14px 36px;
            margin-top: 8px;
            font-size: 15px;
            color: #E4E2D8;
        }

        .perks div {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ---------- Form section ---------- */
        .form-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 40px;
            padding: 96px 24px 112px;
        }

        .section-head {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
            text-align: center;
        }

        .section-head .eyebrow {
            color: var(--gold-ink);
        }

        .section-head h2 {
            margin: 0;
            font-family: var(--serif);
            font-weight: 400;
            font-size: 58px;
        }

        .shell {
            width: 100%;
            max-width: 1000px;
        }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 18px 22px;
            border-radius: 16px;
            font-size: 15px;
            line-height: 1.5;
        }

        .alert svg {
            flex: none;
            margin-top: 2px;
        }

        .alert-success {
            background: #E5EEDD;
            border: 1px solid #B9CDA9;
            color: #2F5B2C;
        }

        .alert-error {
            background: #F6E3D6;
            border: 1px solid #DDB89C;
            color: #7E3A12;
        }

        .app-card {
            display: flex;
            flex-direction: column;
            gap: 48px;
            padding: 56px 64px;
            background: var(--paper);
            border: 1px solid #E6DECB;
            border-radius: 28px;
        }

        .step {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .step-title {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .step-title>div {
            display: flex;
            align-items: baseline;
            gap: 18px;
        }

        .step-num {
            font-family: var(--serif);
            font-size: 30px;
            color: var(--gold-num);
        }

        .step-title h3 {
            margin: 0;
            font-family: var(--serif);
            font-weight: 500;
            font-size: 34px;
        }

        .tag {
            padding: 6px 14px;
            border: 1px solid var(--gold);
            border-radius: 20px;
            color: #7A6128;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1.5px;
        }

        .rule {
            height: 1px;
            background: var(--line);
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px 24px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .field label,
        .field-label {
            font-size: 15px;
            font-weight: 600;
        }

        .req {
            color: var(--req);
        }

        .input {
            width: 100%;
            height: 52px;
            padding: 0 16px;
            border: 1px solid var(--line-2);
            border-radius: 12px;
            background: var(--field);
            font-size: 16px;
            color: var(--ink);
        }

        .input:focus {
            outline: 2px solid var(--gold);
            outline-offset: 1px;
        }

        .phone {
            display: flex;
            height: 52px;
            border: 1px solid var(--line-2);
            border-radius: 12px;
            background: var(--field);
            overflow: hidden;
        }

        .phone:focus-within {
            outline: 2px solid var(--gold);
            outline-offset: 1px;
        }

        .phone-prefix {
            display: flex;
            align-items: center;
            padding: 0 16px;
            background: #F1EADA;
            border-right: 1px solid var(--line-2);
            font-size: 16px;
            font-weight: 600;
            color: var(--ink-2);
        }

        .phone input {
            flex-grow: 1;
            min-width: 0;
            border: 0;
            padding: 0 16px;
            background: transparent;
            font-size: 16px;
            color: var(--ink);
        }

        .phone input:focus {
            outline: none;
        }

        /* ---------- Upload tiles ---------- */
        .upload {
            position: relative;
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 20px 22px;
            border: 1.5px dashed var(--dash);
            border-radius: 16px;
            background: var(--tile);
            cursor: pointer;
            transition: border-color .15s, background .15s;
        }

        .upload:hover {
            border-color: var(--gold);
        }

        .upload:focus-within {
            outline: 2px solid var(--gold);
            outline-offset: 1px;
        }

        .upload.light {
            background: var(--field);
        }

        .upload.stack {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
            padding: 22px;
            height: 100%;
        }

        .upload.is-filled {
            border-style: solid;
            border-color: var(--gold);
            background: #FBF5E4;
        }

        .upload-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 44px;
            height: 44px;
            border-radius: 22px;
            background: var(--forest);
            color: var(--gold-2);
        }

        .upload-icon.soft {
            background: var(--sand);
            color: var(--forest);
        }

        .upload.is-filled .upload-icon {
            background: #3D6B47;
            color: var(--paper);
        }

        .upload-copy {
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-width: 0;
        }

        .upload-title {
            font-size: 16px;
            font-weight: 600;
        }

        .upload.stack .upload-title {
            font-family: var(--serif);
            font-size: 22px;
            font-weight: 400;
            line-height: 1.2;
        }

        .upload-hint {
            font-size: 13px;
            color: var(--muted);
        }

        .upload-file {
            font-size: 13px;
            font-weight: 600;
            color: #3D6B47;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }

        .file-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .file-list:empty {
            display: none;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            max-width: 100%;
            padding: 6px 6px 6px 12px;
            background: var(--field);
            border: 1px solid var(--line-2);
            border-radius: 18px;
            font-size: 13px;
            color: var(--ink-2);
        }

        .chip span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 220px;
        }

        .chip {
            position: relative;
            overflow: hidden;
        }

        .chip::before {
            content: "";
            position: absolute;
            inset: auto auto 0 0;
            width: var(--p, 0);
            height: 3px;
            background: var(--gold);
            transition: width .2s;
        }

        .chip em {
            font-style: normal;
            font-size: 12px;
            color: var(--muted);
            white-space: nowrap;
        }

        .chip-done {
            border-color: #B9CDA9;
        }

        .chip-done em {
            color: #3D6B47;
            font-weight: 600;
        }

        .chip-failed {
            border-color: #DDB89C;
        }

        .chip-failed em {
            color: #9A5A22;
        }

        .chip button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border: 0;
            border-radius: 11px;
            background: var(--sand);
            color: var(--ink-2);
            cursor: pointer;
            font-size: 14px;
            line-height: 1;
        }

        .chip button:hover {
            background: var(--dash);
        }

        .stack-col {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .row-between {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 12px;
        }

        .sub {
            font-size: 14px;
            color: var(--muted);
        }

        /* Bank statements block */
        .bank-box {
            display: flex;
            flex-direction: column;
            gap: 18px;
            padding: 28px 30px;
            border: 1px solid var(--line);
            border-radius: 20px;
            background: var(--cream);
        }

        .bank-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .bank-title {
            font-family: var(--serif);
            font-size: 26px;
        }

        .toggle-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            color: var(--ink-2);
        }

        .toggle {
            display: flex;
            padding: 4px;
            gap: 4px;
            background: var(--sand);
            border-radius: 26px;
        }

        .toggle button {
            min-width: 64px;
            height: 44px;
            padding: 0 20px;
            border: 0;
            border-radius: 22px;
            background: transparent;
            color: var(--ink-2);
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
        }

        .toggle button[aria-pressed="true"] {
            background: var(--forest);
            color: var(--cream);
            font-weight: 600;
        }

        .months {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 5px;
        }

        .months div {
            height: 6px;
            border-radius: 3px;
            background: #DCD2BC;
            transition: background .2s;
        }

        .months div.on {
            background: var(--gold);
        }

        [hidden] {
            display: none !important;
        }

        /* ---------- Submit panel ---------- */
        .submit-panel {
            display: flex;
            gap: 56px;
            margin-top: 40px;
            padding: 52px 60px;
            background: var(--forest);
            border-radius: 28px;
            color: var(--cream);
        }

        .submit-left {
            display: flex;
            flex-direction: column;
            gap: 18px;
            flex-grow: 1;
        }

        .submit-left .eyebrow {
            font-size: 13px;
            letter-spacing: 3px;
        }

        .submit-heading {
            font-family: var(--serif);
            font-size: 40px;
            line-height: 1.1;
            color: var(--paper);
        }

        .submit-heading em {
            color: var(--gold-2);
        }

        .checklist {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px 28px;
            margin: 6px 0 0;
            padding: 0;
            list-style: none;
        }

        .checklist li {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 15px;
            color: #D8D6CB;
        }

        .dot {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            border-radius: 8px;
            border: 1.5px solid #8C927F;
            flex-shrink: 0;
        }

        .dot svg {
            display: none;
        }

        .checklist li.done {
            color: var(--paper);
        }

        .checklist li.done .dot {
            background: var(--gold);
            border-color: var(--gold);
        }

        .checklist li.done .dot svg {
            display: block;
        }

        .submit-right {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 16px;
            width: 300px;
            flex-shrink: 0;
            padding-left: 48px;
            border-left: 1px solid var(--forest-line);
        }

        .score {
            display: flex;
            align-items: baseline;
            gap: 10px;
        }

        .score strong {
            font-family: var(--serif);
            font-weight: 400;
            font-size: 56px;
            line-height: 1;
            color: var(--gold-2);
        }

        .score span {
            font-size: 14px;
            color: #C4C4B8;
        }

        .bar {
            height: 6px;
            border-radius: 3px;
            background: var(--forest-line);
            overflow: hidden;
        }

        .bar div {
            width: 0;
            height: 100%;
            background: var(--gold);
            transition: width .25s;
        }

        .submit-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            height: 56px;
            margin-top: 8px;
            border: 0;
            border-radius: 28px;
            background: var(--gold);
            color: var(--forest);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s;
        }

        .submit-btn:hover {
            background: var(--gold-2);
        }

        .submit-btn:disabled {
            background: #3A463F;
            color: #B9BFB5;
            cursor: not-allowed;
        }

        .spinner {
            display: none;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid rgba(185, 191, 181, .4);
            border-top-color: #B9BFB5;
            animation: spin .7s linear infinite;
        }

        .submit-btn.loading .spinner {
            display: inline-block;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .submit-note {
            margin: 0;
            font-size: 13px;
            line-height: 1.5;
            color: #B9BFB5;
        }

        /* ---------- Floating checklist bar ---------- */
        .float-bar {
            position: fixed;
            top: 14px;
            left: 50%;
            z-index: 50;
            display: flex;
            align-items: center;
            gap: 22px;
            width: min(1180px, calc(100% - 32px));
            padding: 12px 12px 12px 22px;
            background: rgba(18, 30, 24, .97);
            border: 1px solid var(--forest-line);
            border-radius: 22px;
            box-shadow: 0 18px 40px -18px rgba(18, 30, 24, .55);
            color: var(--cream);
            transform: translate(-50%, calc(-100% - 24px));
            opacity: 0;
            visibility: hidden;
            transition: transform .35s cubic-bezier(.2, .7, .2, 1), opacity .25s, visibility 0s .35s;
        }

        .float-bar.show {
            transform: translate(-50%, 0);
            opacity: 1;
            visibility: visible;
            transition: transform .35s cubic-bezier(.2, .7, .2, 1), opacity .25s;
        }

        .fb-progress {
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 92px;
            flex-shrink: 0;
        }

        .fb-progress div:first-child {
            display: flex;
            align-items: baseline;
            gap: 6px;
        }

        .fb-progress strong {
            font-family: var(--serif);
            font-weight: 400;
            font-size: 30px;
            line-height: 1;
            color: var(--gold-2);
        }

        .fb-progress span {
            font-size: 12px;
            color: #C4C4B8;
        }

        .fb-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            flex: 1;
            min-width: 0;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .fb-chip {
            display: flex;
            align-items: center;
            gap: 7px;
            height: 32px;
            padding: 0 12px 0 8px;
            border: 1px solid var(--forest-line);
            border-radius: 16px;
            background: transparent;
            color: #D8D6CB;
            font-size: 13px;
            cursor: pointer;
            white-space: nowrap;
            transition: background .15s, border-color .15s, color .15s;
        }

        .fb-chip:hover {
            border-color: var(--gold);
            color: var(--paper);
        }

        .fb-chip .dot {
            width: 14px;
            height: 14px;
        }

        .fb-chip.done {
            border-color: transparent;
            background: var(--forest-2);
            color: var(--paper);
        }

        .fb-chip.done .dot {
            background: var(--gold);
            border-color: var(--gold);
        }

        .fb-chip.done .dot svg {
            display: block;
        }

        .fb-next {
            display: none;
            flex: 1;
            min-width: 0;
            font-size: 13px;
            color: #D8D6CB;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .fb-next b {
            color: var(--paper);
            font-weight: 600;
        }

        .float-bar .submit-btn {
            flex-shrink: 0;
            height: 48px;
            margin: 0;
            padding: 0 24px;
        }

        /* Ready to submit: gold glow + a short shake every few seconds */
        .submit-btn.ready {
            animation: nudge 2.8s ease-in-out infinite, glow 2.8s ease-out infinite;
        }

        @keyframes nudge {

            0%,
            20%,
            100% {
                transform: none;
            }

            3% {
                transform: translateX(-6px) rotate(-1.5deg);
            }

            6% {
                transform: translateX(6px) rotate(1.5deg);
            }

            9% {
                transform: translateX(-5px) rotate(-1deg);
            }

            12% {
                transform: translateX(4px) rotate(.5deg);
            }

            15% {
                transform: translateX(-2px);
            }
        }

        @keyframes glow {
            0% {
                box-shadow: 0 0 0 0 rgba(201, 173, 98, .65);
            }

            35%,
            100% {
                box-shadow: 0 0 0 14px rgba(201, 173, 98, 0);
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .submit-btn.ready,
            .float-bar,
            .float-bar.show {
                animation: none;
                transition: none;
            }
        }

        @media (max-width: 1100px) {
            .fb-list {
                display: none;
            }

            .fb-next {
                display: block;
            }
        }

        /* ---------- Footer ---------- */
        .site-footer {
            display: flex;
            flex-direction: column;
            gap: 22px;
            padding: 44px 188px 48px;
            border-top: 1px solid var(--line);
        }

        .footer-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .footer-brand {
            display: flex;
            align-items: baseline;
            gap: 12px;
            flex-wrap: wrap;
        }

        .footer-brand strong {
            font-family: var(--serif);
            font-weight: 400;
            font-size: 34px;
            letter-spacing: 2px;
        }

        .footer-brand strong i {
            font-style: normal;
            color: var(--gold);
        }

        .footer-brand span {
            font-size: 14px;
            letter-spacing: 3px;
            color: var(--ink-2);
        }

        .footer-contact {
            font-size: 17px;
            color: var(--ink-2);
        }

        .site-footer p,
        .site-footer .copy {
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
            color: var(--ink-2);
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 1100px) {
            .site-footer {
                padding: 40px 48px;
            }
        }

        @media (max-width: 900px) {
            .site-header {
                padding: 14px 24px;
            }

            .hero h1 {
                font-size: 52px;
            }

            .section-head h2 {
                font-size: 46px;
            }

            .app-card {
                padding: 40px 32px;
            }

            .grid-3 {
                grid-template-columns: 1fr;
            }

            .submit-panel {
                flex-direction: column;
                gap: 32px;
                padding: 40px 32px;
            }

            .submit-right {
                width: auto;
                padding-left: 0;
                padding-top: 28px;
                border-left: 0;
                border-top: 1px solid var(--forest-line);
            }
        }

        @media (max-width: 640px) {
            .float-bar {
                top: 8px;
                gap: 12px;
                width: calc(100% - 16px);
                padding: 8px 8px 8px 14px;
                border-radius: 18px;
            }

            .fb-progress {
                width: auto;
            }

            .fb-progress .bar,
            .fb-progress span {
                display: none;
            }

            .fb-progress strong {
                font-size: 26px;
            }

            .float-bar .submit-btn {
                height: 42px;
                padding: 0 16px;
                font-size: 14px;
            }

            .site-header {
                padding: 12px 16px;
            }

            .brand-wrap {
                gap: 14px;
            }

            .secure span,
            .header-contact>span {
                display: none;
            }

            .brand-logo {
                width: 84px;
            }

            .pill-btn {
                height: 40px;
                padding: 0 14px;
                font-size: 14px;
            }

            .hero {
                padding: 48px 16px 44px;
                gap: 18px;
            }

            .eyebrow {
                font-size: 12px;
                letter-spacing: 3px;
            }

            .hero h1 {
                font-size: 40px;
            }

            .hero p {
                font-size: 17px;
            }

            .form-section {
                padding: 56px 16px 72px;
                gap: 28px;
            }

            .section-head h2 {
                font-size: 40px;
            }

            .app-card {
                padding: 28px 18px;
                gap: 36px;
                border-radius: 22px;
            }

            .step-title h3 {
                font-size: 28px;
            }

            .grid-2,
            .checklist {
                grid-template-columns: 1fr;
            }

            .bank-box {
                padding: 20px 16px;
            }

            .submit-panel {
                padding: 32px 22px;
                border-radius: 22px;
            }

            .submit-heading {
                font-size: 32px;
            }

            .site-footer {
                padding: 36px 16px;
            }
        }
    </style>
</head>

<body>

    @php
        $contactHref = $affiliate?->email
            ? 'mailto:' . $affiliate->email
            : ($affiliate?->phone ? 'tel:' . preg_replace('/[^0-9+]/', '', $affiliate->phone) : 'mailto:info@dpspayments.com');
    @endphp

    <header class="site-header">
        <div class="brand-wrap">
            <a href="{{ $affiliate ? url($affiliate->slug) : url('/') }}" class="brand" aria-label="DPS Payments Corp.">
                <img src="{{ asset('images/dps-logo.png') }}" alt="DPS Payments Corp." class="brand-logo">
            </a>
            <div class="v-rule"></div>
            <div class="secure">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3D8B4F" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="4" y="11" width="16" height="10" rx="2"></rect>
                    <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>
                </svg>
                <span>Secure application</span>
            </div>
        </div>
        <div class="header-contact">
            <span>Questions?</span>
            <a href="{{ $contactHref }}" class="pill-btn">Contact {{ $affiliate?->name ?? 'us' }}</a>
        </div>
    </header>

    <section class="hero">
        @if ($affiliate)
            <div class="referral">
                <div class="referral-avatar">{{ $affiliate->initials() }}</div>
                <span>Referred by <strong>{{ $affiliate->name }}</strong></span>
            </div>
        @endif
        <div class="eyebrow">APPLICATION · ABOUT 5 MINUTES</div>
        <h1>See your number in <em>1–3 business days.</em></h1>
        <p>Upload your documents below. No cost, no obligation — and you'll hear from us the same day.</p>
        <div class="perks">
            @foreach (['No interest, ever', 'No monthly payments', 'Not a loan — not debt'] as $perk)
                <div>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#C9AD62" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12l5 5L20 7"></path>
                    </svg>{{ $perk }}
                </div>
            @endforeach
        </div>
    </section>

    <section class="form-section" id="apply">
        <div class="section-head">
            <div class="eyebrow">FOUR SECTIONS</div>
            <h2>Your application.</h2>
        </div>

        @if (session('success') || session('error'))
            <div class="shell" id="flash">
                @if (session('success'))
                    <div class="alert alert-success" role="status">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <path d="M20 6L9 17l-5-5" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error" role="alert">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 8v4M12 16h.01" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
            </div>
        @endif

        <form method="POST" action="/upload" enctype="multipart/form-data" id="uploadForm" class="shell" novalidate>
            @csrf
            <input type="hidden" name="affiliate" value="{{ $affiliate?->slug }}">
            <input type="hidden" name="uploads" id="uploadsManifest" value="">
            <input type="hidden" name="new_location" id="newLocation" value="{{ old('new_location', 0) ? 1 : 0 }}">

            <div class="app-card">

                {{-- 01 --}}
                <div class="step">
                    <div class="step-title">
                        <div><span class="step-num">01</span>
                            <h3>Your contact info</h3>
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="field">
                            <label for="ownerName">Your full name <span class="req">*</span></label>
                            <input id="ownerName" name="owner_name" type="text" class="input" required
                                autocomplete="name" placeholder="First and last name" value="{{ old('owner_name') }}"
                                data-check="name">
                        </div>
                        <div class="field">
                            <label for="bizName">Business name (DBA) <span class="req">*</span></label>
                            <input id="bizName" name="business_name" type="text" class="input" required
                                autocomplete="organization" placeholder="Name on your storefront"
                                value="{{ old('business_name') }}" data-check="business">
                        </div>
                        <div class="field">
                            <label for="ownerPhone">Cell phone <span class="req">*</span></label>
                            <div class="phone">
                                <div class="phone-prefix">+1</div>
                                <input id="ownerPhone" name="phone" type="tel" inputmode="numeric" required
                                    maxlength="10" pattern="[0-9]{10}" autocomplete="tel-national"
                                    placeholder="10-digit cell" value="{{ old('phone') }}"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" data-check="phone">
                            </div>
                        </div>
                        <div class="field">
                            <label for="ownerEmail">Email <span class="req">*</span></label>
                            <input id="ownerEmail" name="email" type="email" class="input" required
                                autocomplete="email" placeholder="you@business.com" value="{{ old('email') }}"
                                data-check="email">
                        </div>
                        <div class="field">
                            <label for="locCount">Number of locations</label>
                            <input id="locCount" name="locations" type="number" min="1" class="input"
                                placeholder="e.g. 3" value="{{ old('locations') }}">
                        </div>
                    </div>
                </div>

                <div class="rule"></div>

                {{-- 02 --}}
                <div class="step">
                    <div class="step-title">
                        <div><span class="step-num">02</span>
                            <h3>Required documents</h3>
                        </div>
                    </div>

                    <div class="grid-3">
                        <div class="stack-col">
                            <div class="field-label">Owner ID <span class="req">*</span></div>
                            <label class="upload stack" data-upload>
                                <input type="file" name="driving_license" class="sr-only"
                                    accept=".pdf,.jpg,.jpeg,.png,.heic" data-check="id">
                                <div class="upload-icon"><svg width="20" height="20" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                        stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                        <circle cx="9" cy="11" r="2"></circle>
                                        <path d="M6 16c.6-1.5 1.8-2 3-2s2.4.5 3 2"></path>
                                        <path d="M14 10h4M14 13h3"></path>
                                    </svg></div>
                                <span class="upload-title">Driver's license or passport</span>
                                <span class="upload-hint" data-hint>PDF, JPG, PNG, HEIC · 10MB</span>
                                <span class="upload-file" data-filename hidden></span>
                            </label>
                        </div>
                        <div class="stack-col">
                            <div class="field-label">Voided check <span class="req">*</span></div>
                            <label class="upload stack" data-upload>
                                <input type="file" name="bank_doc" class="sr-only"
                                    accept=".pdf,.jpg,.jpeg,.png,.heic" data-check="vc">
                                <div class="upload-icon"><svg width="20" height="20" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                        stroke-linejoin="round" aria-hidden="true">
                                        <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                                        <path d="M2 10h20"></path>
                                        <path d="M6 15h4"></path>
                                    </svg></div>
                                <span class="upload-title">Voided check or bank letter</span>
                                <span class="upload-hint" data-hint>PDF, JPG, PNG, HEIC · 10MB</span>
                                <span class="upload-file" data-filename hidden></span>
                            </label>
                        </div>
                        <div class="stack-col">
                            <div class="field-label">Tax ID <span class="req">*</span></div>
                            <label class="upload stack" data-upload>
                                <input type="file" name="tax_doc" class="sr-only"
                                    accept=".pdf,.jpg,.jpeg,.png,.heic" data-check="tax">
                                <div class="upload-icon"><svg width="20" height="20" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                        stroke-linejoin="round" aria-hidden="true">
                                        <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"></path>
                                        <path d="M14 3v5h5"></path>
                                        <path d="M9 13h6M9 17h4"></path>
                                    </svg></div>
                                <span class="upload-title">Business certificate or EIN letter</span>
                                <span class="upload-hint" data-hint>PDF, JPG, PNG, HEIC · 10MB</span>
                                <span class="upload-file" data-filename hidden></span>
                            </label>
                        </div>
                    </div>

                    <div class="bank-box">
                        <div class="bank-head">
                            <div>
                                <div class="bank-title">Bank statements <span class="req"
                                        style="font-family: var(--sans); font-size: 16px">*</span></div>
                                <div class="sub">What we need depends on whether the location is new.</div>
                            </div>
                            <div class="toggle-wrap">
                                <span>New location?</span>
                                <div class="toggle" role="group" aria-label="New location?">
                                    <button type="button" data-new="0" aria-pressed="true">No</button>
                                    <button type="button" data-new="1" aria-pressed="false">Yes</button>
                                </div>
                            </div>
                        </div>

                        <div class="stack-col" id="bankPanel">
                            <div class="row-between" style="font-size: 14px; color: var(--ink-2)">
                                <span>Last 12 months · one file per month</span>
                                <span style="font-weight: 600; color: var(--ink)"><span id="bankCount">0</span> of 12
                                    uploaded</span>
                            </div>
                            <div class="months" id="bankMonths" aria-hidden="true">
                                @for ($i = 0; $i < 12; $i++)
                                    <div></div>
                                @endfor
                            </div>
                            <label class="upload light" data-upload data-multi data-max="20">
                                <input type="file" name="statement_bank[]" multiple class="sr-only"
                                    accept=".pdf,.jpg,.jpeg,.png,.heic" data-check="bank">
                                <div class="upload-icon"><svg width="20" height="20" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                        stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 10l9-6 9 6"></path>
                                        <path d="M5 10v8M9.5 10v8M14.5 10v8M19 10v8"></path>
                                        <path d="M3 20h18"></path>
                                    </svg></div>
                                <div class="upload-copy"><span class="upload-title">Upload 12 months of bank
                                        statements</span><span class="upload-hint">PDF, JPG, PNG, HEIC · 10MB per file
                                        · up to 20 files</span></div>
                            </label>
                            <div class="file-list" data-list-for="statement_bank[]"></div>
                        </div>

                        <div class="stack-col" id="projPanel" hidden>
                            <div style="font-size: 14px; color: var(--ink-2)">New location: upload your 1st Year
                                Projections in place of bank statements.</div>
                            <label class="upload light" data-upload>
                                <input type="file" name="projections" class="sr-only"
                                    accept=".pdf,.xlsx,.xls,.csv,.jpg,.jpeg,.png,.heic" data-check="projections"
                                    disabled>
                                <div class="upload-icon"><svg width="20" height="20" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                        stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 3v18h18"></path>
                                        <path d="M7 15l4-4 3 3 5-6"></path>
                                    </svg></div>
                                <div class="upload-copy"><span class="upload-title">Upload 1st Year
                                        Projections</span><span class="upload-hint" data-hint>PDF, XLSX, JPG, PNG ·
                                        10MB</span><span class="upload-file" data-filename hidden></span></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="rule"></div>

                {{-- 03 --}}
                <div class="step">
                    <div class="step-title">
                        <div><span class="step-num">03</span>
                            <h3>Recommended</h3>
                        </div>
                        <span class="tag">SPEEDS UP APPROVAL</span>
                    </div>
                    <div class="stack-col">
                        <div class="row-between">
                            <div class="field-label">Merchant processing statements</div>
                            <span style="font-size: 14px; font-weight: 600"><span id="ccpCount">0</span> of 3
                                uploaded</span>
                        </div>
                        <div class="sub">Last 3 months from your current processor · one file per month</div>
                        <label class="upload" data-upload data-multi data-max="20">
                            <input type="file" name="statement_ccp[]" multiple class="sr-only"
                                accept=".pdf,.jpg,.jpeg,.png,.heic">
                            <div class="upload-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true">
                                    <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"></path>
                                    <path d="M14 3v5h5"></path>
                                    <path d="M9 12h6M9 15h6M9 18h3"></path>
                                </svg></div>
                            <div class="upload-copy"><span class="upload-title">Upload processing
                                    statements</span><span class="upload-hint">PDF, JPG, PNG, HEIC · 10MB per
                                    file</span></div>
                        </label>
                        <div class="file-list" data-list-for="statement_ccp[]"></div>
                    </div>
                    <div class="stack-col">
                        <div class="field-label">POS statements</div>
                        <div class="sub">From your point-of-sale system · one file per month</div>
                        <label class="upload" data-upload data-multi data-max="20">
                            <input type="file" name="statement_pos[]" multiple class="sr-only"
                                accept=".pdf,.jpg,.jpeg,.png,.heic">
                            <div class="upload-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true">
                                    <rect x="4" y="3" width="16" height="18" rx="2"></rect>
                                    <path d="M8 7h8M8 11h2M12 11h2M8 15h2M12 15h2M16 11v4"></path>
                                </svg></div>
                            <div class="upload-copy"><span class="upload-title">Upload POS statements</span><span
                                    class="upload-hint">PDF, JPG, PNG, HEIC · 10MB per file</span></div>
                        </label>
                        <div class="file-list" data-list-for="statement_pos[]"></div>
                    </div>
                </div>

                <div class="rule"></div>

                {{-- 04 --}}
                <div class="step">
                    <div class="step-title">
                        <div><span class="step-num">04</span>
                            <h3>Optional</h3>
                        </div>
                    </div>
                    <div class="grid-2" style="gap: 18px">
                        <div class="stack-col">
                            <label class="upload" data-upload data-multi>
                                <input type="file" name="pictures[]" multiple class="sr-only"
                                    accept=".pdf,.jpg,.jpeg,.png,.heic">
                                <div class="upload-icon soft"><svg width="20" height="20" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                        stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                        <circle cx="9" cy="10" r="1.5"></circle>
                                        <path d="M21 16l-5-5-8 8"></path>
                                    </svg></div>
                                <div class="upload-copy"><span class="upload-title">Pictures of your current
                                        POS</span><span class="upload-hint">Multiple files allowed</span></div>
                            </label>
                            <div class="file-list" data-list-for="pictures[]"></div>
                        </div>
                        <div class="stack-col">
                            <label class="upload" data-upload data-multi>
                                <input type="file" name="other_doc[]" multiple class="sr-only"
                                    accept=".pdf,.jpg,.jpeg,.png,.heic">
                                <div class="upload-icon soft"><svg width="20" height="20" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                        stroke-linejoin="round" aria-hidden="true">
                                        <path
                                            d="M21 12.5l-8.5 8.5a5 5 0 0 1-7-7L14 5.5a3.3 3.3 0 0 1 4.7 4.7l-8.5 8.5a1.7 1.7 0 0 1-2.4-2.4l7.8-7.8">
                                        </path>
                                    </svg></div>
                                <div class="upload-copy"><span class="upload-title">Other supporting
                                        documents</span><span class="upload-hint">Multiple files allowed</span></div>
                            </label>
                            <div class="file-list" data-list-for="other_doc[]"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit panel --}}
            <div class="submit-panel">
                <div class="submit-left">
                    <div class="eyebrow">READY TO APPLY?</div>
                    <div class="submit-heading">Complete the checklist, <em>then send it.</em></div>
                    <ul class="checklist" id="checklist">
                        @foreach ([
                            'name' => 'Full name',
                            'business' => 'Business name',
                            'phone' => 'Cell phone',
                            'email' => 'Email',
                            'id' => 'Owner ID',
                            'vc' => 'Voided check or bank letter',
                            'tax' => 'Tax ID',
                            'statements' => 'Bank statements (12 mo.)',
                        ] as $key => $label)
                            <li data-item="{{ $key }}">
                                <span class="dot"><svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                                        stroke="#121E18" stroke-width="4" stroke-linecap="round"
                                        stroke-linejoin="round" aria-hidden="true">
                                        <path d="M5 12l5 5L20 7"></path>
                                    </svg></span>
                                <span data-label>{{ $label }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="submit-right">
                    <div class="score"><strong id="score">0/8</strong><span>required complete</span></div>
                    <div class="bar">
                        <div id="scoreBar"></div>
                    </div>
                    <button type="submit" class="submit-btn" id="submitBtn" data-submit disabled>
                        <span class="spinner"></span>
                        <span class="btn-text">Submit application →</span>
                    </button>
                    <p class="submit-note">No cost, no obligation. You'll hear from us the same day.</p>
                </div>
            </div>

            {{-- Floating checklist: follows the customer while they fill the form --}}
            <div class="float-bar" id="floatBar" aria-label="Application progress">
                <div class="fb-progress">
                    <div><strong id="fbScore">0/8</strong><span>required</span></div>
                    <div class="bar">
                        <div id="fbBar"></div>
                    </div>
                </div>
                <ul class="fb-list">
                    @foreach ([
                        'name' => 'Name',
                        'business' => 'Business',
                        'phone' => 'Phone',
                        'email' => 'Email',
                        'id' => 'Owner ID',
                        'vc' => 'Voided check',
                        'tax' => 'Tax ID',
                        'statements' => 'Bank statements',
                    ] as $key => $label)
                        <li>
                            <button type="button" class="fb-chip" data-jump="{{ $key }}">
                                <span class="dot"><svg width="9" height="9" viewBox="0 0 24 24" fill="none"
                                        stroke="#121E18" stroke-width="4" stroke-linecap="round"
                                        stroke-linejoin="round" aria-hidden="true">
                                        <path d="M5 12l5 5L20 7"></path>
                                    </svg></span>
                                <span data-label>{{ $label }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
                <div class="fb-next" id="fbNext"></div>
                <button type="submit" class="submit-btn" data-submit disabled>
                    <span class="spinner"></span>
                    <span class="btn-text">Submit →</span>
                </button>
            </div>
        </form>
    </section>

    <footer class="site-footer">
        <div class="footer-top">
            <div class="footer-brand">
                <strong>DPS<i>.</i></strong>
                <span>PAYMENTS · CAPITAL ADVISORY</span>
            </div>
            <div class="footer-contact"><a href="tel:+16468254477">(646) 825-4477</a> · <a
                    href="mailto:info@dpspayments.com">info@dpspayments.com</a></div>
        </div>
        <p>DPS Payments is a capital advisory and funding facilitation firm. Amounts, pricing, approval, and timing are
            determined by the provider in its sole discretion and are not guaranteed. Your documents are encrypted and
            used only to review your application.</p>
        <div class="copy">© {{ date('Y') }} DPS Payments. All rights reserved.</div>
    </footer>

    <script>
        (function() {
            const form = document.getElementById('uploadForm');
            const MAX_BYTES = 10 * 1024 * 1024;
            const phoneRegex = /^[0-9]{10}$/;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const canEditFileList = typeof DataTransfer !== 'undefined';
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            // Files go straight from the browser to S3 as soon as they are picked.
            // Anything that cannot be uploaded directly is sent with the form instead.
            const direct = canEditFileList && 'fetch' in window && 'FormData' in window;
            const PARALLEL = 4;

            // input -> [{ file, status: queued|uploading|done|failed, loaded, key, xhr }]
            const store = new Map();
            const queue = [];
            let active = 0;

            const fieldOf = input => input.name.replace('[]', '');
            const entriesOf = input => store.get(input) || [];

            function oversized(files) {
                const big = files.filter(f => f.size > MAX_BYTES);
                if (big.length) {
                    alert('These files are larger than 10MB and were skipped:\n' + big.map(f => f.name).join('\n'));
                }
                return files.filter(f => f.size <= MAX_BYTES);
            }

            function syncInput(input, onlyFallback) {
                if (!canEditFileList) return;
                const dt = new DataTransfer();
                entriesOf(input)
                    .filter(e => !onlyFallback || e.status !== 'done')
                    .forEach(e => dt.items.add(e.file));
                input.files = dt.files;
            }

            function statusText(entry) {
                if (entry.status === 'queued') return 'Waiting…';
                if (entry.status === 'uploading') return Math.round(entry.loaded / entry.file.size * 100) + '%';
                if (entry.status === 'done') return 'Uploaded';
                if (entry.status === 'failed') return 'Sends with form';
                return '';
            }

            function render(input) {
                const zone = input.closest('[data-upload]');
                const entries = entriesOf(input);
                zone.classList.toggle('is-filled', entries.length > 0);

                if (!zone.hasAttribute('data-multi')) {
                    const nameEl = zone.querySelector('[data-filename]');
                    const hintEl = zone.querySelector('[data-hint]');
                    const entry = entries[0];
                    nameEl.hidden = !entry;
                    if (hintEl) hintEl.hidden = !!entry;
                    nameEl.textContent = entry ? entry.file.name + (direct ? ' · ' + statusText(entry) : '') : '';
                    return;
                }

                const list = document.querySelector('[data-list-for="' + input.name + '"]');
                list.innerHTML = '';
                entries.forEach(function(entry) {
                    const chip = document.createElement('span');
                    chip.className = 'chip chip-' + entry.status;
                    if (entry.status === 'uploading') {
                        chip.style.setProperty('--p', Math.round(entry.loaded / entry.file.size * 100) + '%');
                    }
                    const name = document.createElement('span');
                    name.textContent = entry.file.name;
                    chip.appendChild(name);
                    if (direct) {
                        const st = document.createElement('em');
                        st.textContent = statusText(entry);
                        chip.appendChild(st);
                    }
                    if (canEditFileList) {
                        const rm = document.createElement('button');
                        rm.type = 'button';
                        rm.setAttribute('aria-label', 'Remove ' + entry.file.name);
                        rm.textContent = '×';
                        rm.addEventListener('click', () => removeEntry(input, entry));
                        chip.appendChild(rm);
                    }
                    list.appendChild(chip);
                });
            }

            function removeEntry(input, entry) {
                if (entry.xhr) entry.xhr.abort();
                entry.status = 'removed';
                store.set(input, entriesOf(input).filter(e => e !== entry));
                syncInput(input);
                render(input);
                update();
            }

            function enqueue(input, entry) {
                if (!direct) return;
                queue.push({ input, entry });
                pump();
            }

            function pump() {
                while (active < PARALLEL && queue.length) {
                    const job = queue.shift();
                    if (job.entry.status !== 'queued') continue;
                    active++;
                    uploadEntry(job.input, job.entry).finally(function() {
                        active--;
                        pump();
                    });
                }
            }

            async function uploadEntry(input, entry) {
                entry.status = 'uploading';
                render(input);
                update();

                try {
                    const res = await fetch('{{ url('/upload/presign') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({
                            field: fieldOf(input),
                            name: entry.file.name,
                            size: entry.file.size,
                            type: entry.file.type || ''
                        })
                    });

                    if (entry.status === 'removed') return;

                    if (res.status === 422) {
                        const body = await res.json().catch(() => ({}));
                        alert(entry.file.name + ': ' + (body.message || 'This file cannot be uploaded here.'));
                        removeEntry(input, entry);
                        return;
                    }
                    if (!res.ok) throw new Error('presign ' + res.status);

                    const target = await res.json();
                    const data = new FormData();
                    Object.entries(target.fields).forEach(([k, v]) => data.append(k, v));
                    data.append('file', entry.file);

                    await new Promise(function(resolve, reject) {
                        const xhr = new XMLHttpRequest();
                        entry.xhr = xhr;
                        xhr.open('POST', target.url);
                        xhr.upload.onprogress = function(e) {
                            entry.loaded = e.loaded;
                            render(input);
                            update();
                        };
                        xhr.onload = () => xhr.status >= 200 && xhr.status < 300 ? resolve() : reject(new Error('s3 ' + xhr.status));
                        xhr.onerror = () => reject(new Error('network'));
                        xhr.onabort = () => reject(new Error('aborted'));
                        xhr.send(data);
                    });

                    entry.key = target.key;
                    entry.status = 'done';
                } catch (err) {
                    if (entry.status === 'removed') return;
                    // Keep the file in the form; it will be sent the classic way on submit
                    entry.status = 'failed';
                } finally {
                    entry.xhr = null;
                }

                render(input);
                update();
            }

            document.querySelectorAll('[data-upload]').forEach(function(zone) {
                const input = zone.querySelector('input[type="file"]');
                const isMulti = zone.hasAttribute('data-multi');
                const max = parseInt(zone.dataset.max || '0', 10);

                input.addEventListener('change', function() {
                    const picked = oversized(Array.from(input.files));
                    let entries = isMulti && canEditFileList ? entriesOf(input) : [];

                    if (!isMulti) {
                        entriesOf(input).forEach(e => e.xhr && e.xhr.abort());
                        entriesOf(input).forEach(e => e.status = 'removed');
                    }

                    const added = [];
                    (isMulti ? picked : picked.slice(0, 1)).forEach(function(f) {
                        const dupe = entries.some(x => x.file.name === f.name && x.file.size === f.size && x.file.lastModified === f.lastModified);
                        if (!dupe) {
                            const entry = { file: f, status: direct ? 'queued' : 'local', loaded: 0, key: null, xhr: null };
                            entries.push(entry);
                            added.push(entry);
                        }
                    });

                    if (max && entries.length > max) {
                        alert('You can upload up to ' + max + ' files here. Extra files were skipped.');
                        entries.slice(max).forEach(e => e.status = 'removed');
                        entries = entries.slice(0, max);
                    }

                    store.set(input, entries);
                    syncInput(input);
                    render(input);
                    added.filter(e => e.status === 'queued').forEach(e => enqueue(input, e));
                    update();
                });
            });

            function uploadState() {
                let total = 0, loaded = 0, pending = 0;
                store.forEach(function(entries, input) {
                    if (input.disabled) return;
                    entries.forEach(function(e) {
                        if (e.status === 'queued' || e.status === 'uploading') {
                            pending++;
                            total += e.file.size;
                            loaded += e.loaded;
                        }
                    });
                });
                return { pending, percent: total ? Math.round(loaded / total * 100) : 100 };
            }

            // New location toggle: swap bank statements for 1st Year Projections
            const newLoc = document.getElementById('newLocation');
            const bankPanel = document.getElementById('bankPanel');
            const projPanel = document.getElementById('projPanel');
            const bankInput = form.querySelector('[name="statement_bank[]"]');
            const projInput = form.querySelector('[name="projections"]');

            function setNewLocation(isNew) {
                newLoc.value = isNew ? 1 : 0;
                document.querySelectorAll('[data-new]').forEach(b => b.setAttribute('aria-pressed', String((b.dataset.new === '1') === isNew)));
                bankPanel.hidden = isNew;
                projPanel.hidden = !isNew;
                // Disabled inputs are not submitted, so only the visible option is sent
                bankInput.disabled = isNew;
                projInput.disabled = !isNew;
                document.querySelector('[data-item="statements"] [data-label]').textContent = isNew ? '1st Year Projections' : 'Bank statements (12 mo.)';
                document.querySelector('[data-jump="statements"] [data-label]').textContent = isNew ? 'Projections' : 'Bank statements';
                update();
            }

            document.querySelectorAll('[data-new]').forEach(function(btn) {
                btn.addEventListener('click', () => setNewLocation(btn.dataset.new === '1'));
            });

            function val(name) {
                return (form.querySelector('[name="' + name + '"]').value || '').trim();
            }

            function hasFile(name) {
                const input = form.querySelector('[name="' + name + '"]');
                return !input.disabled && entriesOf(input).length > 0;
            }

            const submitButtons = document.querySelectorAll('[data-submit]');
            submitButtons.forEach(btn => btn.dataset.label = btn.querySelector('.btn-text').textContent);

            // Where each checklist item lives, so a chip can take the customer straight to it
            const targets = {
                name: () => document.getElementById('ownerName'),
                business: () => document.getElementById('bizName'),
                phone: () => document.getElementById('ownerPhone'),
                email: () => document.getElementById('ownerEmail'),
                id: () => form.querySelector('[name="driving_license"]'),
                vc: () => form.querySelector('[name="bank_doc"]'),
                tax: () => form.querySelector('[name="tax_doc"]'),
                statements: () => newLoc.value === '1' ? projInput : bankInput,
            };

            document.querySelectorAll('[data-jump]').forEach(function(chip) {
                chip.addEventListener('click', function() {
                    const el = targets[chip.dataset.jump]();
                    const box = el.closest('[data-upload]') || el;
                    box.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    setTimeout(() => el.focus({
                        preventScroll: true
                    }), 400);
                });
            });

            function update() {
                const isNew = newLoc.value === '1';
                const bankFiles = entriesOf(bankInput).length;
                const ccpInput = form.querySelector('[name="statement_ccp[]"]');

                const state = {
                    name: val('owner_name').length > 0,
                    business: val('business_name').length > 0,
                    phone: phoneRegex.test(val('phone')),
                    email: emailRegex.test(val('email')),
                    id: hasFile('driving_license'),
                    vc: hasFile('bank_doc'),
                    tax: hasFile('tax_doc'),
                    statements: isNew ? hasFile('projections') : bankFiles > 0,
                };

                let done = 0;
                let next = null;
                Object.keys(state).forEach(function(key) {
                    document.querySelector('[data-item="' + key + '"]').classList.toggle('done', state[key]);
                    document.querySelector('[data-jump="' + key + '"]').classList.toggle('done', state[key]);
                    if (state[key]) done++;
                    else if (!next) next = key;
                });

                const uploads = uploadState();
                const complete = done === 8 && uploads.pending === 0;
                document.getElementById('score').textContent = done + '/8';
                document.getElementById('fbScore').textContent = done + '/8';
                document.getElementById('scoreBar').style.width = (done / 8 * 100) + '%';
                document.getElementById('fbBar').style.width = (done / 8 * 100) + '%';
                submitButtons.forEach(function(btn) {
                    if (btn.classList.contains('loading')) return;
                    btn.disabled = !complete;
                    btn.classList.toggle('ready', complete);
                    btn.querySelector('.btn-text').textContent = uploads.pending ?
                        'Uploading… ' + uploads.percent + '%' :
                        btn.dataset.label;
                });

                const nextEl = document.getElementById('fbNext');
                if (done === 8 && uploads.pending) {
                    nextEl.innerHTML = 'Uploading <b></b> file(s)…';
                    nextEl.querySelector('b').textContent = uploads.pending;
                } else if (complete) {
                    nextEl.innerHTML = '<b>All set!</b> Submit your application.';
                } else {
                    const label = document.querySelector('[data-item="' + next + '"] [data-label]').textContent;
                    nextEl.innerHTML = 'Next: <b></b>';
                    nextEl.querySelector('b').textContent = label;
                }

                document.getElementById('bankCount').textContent = Math.min(bankFiles, 12);
                document.querySelectorAll('#bankMonths div').forEach((bar, i) => bar.classList.toggle('on', i < bankFiles));
                document.getElementById('ccpCount').textContent = Math.min(entriesOf(ccpInput).length, 3);
            }

            form.addEventListener('input', update);
            setNewLocation(newLoc.value === '1');

            // Validate first, then show the loading state — only if validation passes
            form.addEventListener('submit', function(e) {
                const phone = val('phone');
                const email = val('email');

                if (!phoneRegex.test(phone)) {
                    alert("Phone must be exactly 10 digits");
                    e.preventDefault();
                    return;
                }

                if (!emailRegex.test(email)) {
                    alert("Enter a valid email address");
                    e.preventDefault();
                    return;
                }

                if (uploadState().pending) {
                    alert("Please wait — your files are still uploading.");
                    e.preventDefault();
                    return;
                }

                // Files already in S3 are referenced by key; only failed ones are re-sent with the form
                const manifest = {};
                store.forEach(function(entries, input) {
                    if (input.disabled) return;
                    const done = entries.filter(en => en.status === 'done');
                    if (done.length) manifest[fieldOf(input)] = done.map(en => ({ key: en.key, name: en.file.name }));
                    syncInput(input, true);
                });
                document.getElementById('uploadsManifest').value = JSON.stringify(manifest);

                submitButtons.forEach(function(btn) {
                    btn.classList.remove('ready');
                    btn.classList.add('loading');
                    btn.querySelector('.btn-text').textContent = 'Processing...';
                    btn.disabled = true;
                });
            });

            // Show the floating bar while the customer is inside the form,
            // but not when the full submit panel is already on screen
            const floatBar = document.getElementById('floatBar');
            const appCard = form.querySelector('.app-card');
            const panel = form.querySelector('.submit-panel');
            function placeBar() {
                const inForm = appCard.getBoundingClientRect().top < 40;
                const panelVisible = panel.getBoundingClientRect().top < window.innerHeight - 60;
                const show = inForm && !panelVisible;
                floatBar.classList.toggle('show', show);
                floatBar.toggleAttribute('inert', !show);
            }

            window.addEventListener('scroll', placeBar, {
                passive: true
            });
            window.addEventListener('resize', placeBar);
            placeBar();

            const flash = document.getElementById('flash');
            if (flash) flash.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        })();
    </script>

</body>

</html>
