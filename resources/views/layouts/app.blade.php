<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="@yield('theme_color', '#b45309')">
    <title>@yield('title', 'Jagung Bakar WJT')</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">
    <style>
        :root {
            --brand: #b45309;
            --brand-dark: #92400e;
            --bg: #f6f5f2;
            --surface: #fff;
            --border: #e5e0d8;
            --text: #1f2937;
            --muted: #6b7280;
            --brand-brown: #3c2820;
            --brand-amber: #e0bc48;
            --brand-red: #b43020;
            --brand-red-dark: #96281a;
            --bg-warm: #f4f0e4;
            --surface-warm: #fcfcf4;
            --border-warm: #dcd4bc;
            --text-warm: #3e2b21;
            --muted-warm: #7a6a5c;
            --font-serif: "Lora", Georgia, "Times New Roman", serif;
        }

        * {
            box-sizing: border-box;
        }

        [hidden] {
            display: none !important;
        }

        html {
            -webkit-text-size-adjust: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-height: 100dvh;
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            line-height: 1.5;
        }

        a {
            color: inherit;
        }

        /* Shell: sidebar + topbar */
        .shell {
            display: grid;
            flex: 1;
            grid-template-columns: 232px minmax(0, 1fr);
            min-height: 100vh;
            min-height: 100dvh;
        }

        .shell-main {
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 100vh;
            min-height: 100dvh;
        }

        .sidebar {
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column;
            gap: 16px;
            height: 100vh;
            height: 100dvh;
            padding: 22px 14px 16px;
            overflow-y: auto;
            background: var(--brand-brown);
            color: #f4efe2;
            scrollbar-width: thin;
        }

        .sidebar-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 4px 0 18px;
            border-bottom: 1px solid rgba(244, 239, 226, .16);
            text-align: center;
        }

        .sidebar-logo {
            width: 52px;
            height: 52px;
            object-fit: contain;
        }

        .sidebar-name {
            font-family: var(--font-serif);
            font-size: 18px;
            font-weight: 600;
            line-height: 1.3;
            letter-spacing: -.01em;
            color: #f9f4e6;
        }

        .sidebar-tagline {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: var(--brand-amber);
        }

        .sidebar-nav,
        .sidebar-secondary {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .sidebar-group {
            display: block;
            padding: 0 10px 8px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: rgba(244, 239, 226, .55);
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 11px;
            width: 100%;
            min-height: 42px;
            padding: 9px 12px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #e7dcc8;
            font: inherit;
            font-size: 13.5px;
            font-weight: 600;
            line-height: 1.3;
            text-align: left;
            text-decoration: none;
            cursor: pointer;
        }

        .sidebar-link:hover {
            background: rgba(244, 239, 226, .1);
            color: #f9f4e6;
        }

        .sidebar-link.active {
            background: var(--brand-amber);
            color: var(--brand-brown);
        }

        .sidebar-link.active .nav-icon {
            stroke-width: 1.9;
        }

        .sidebar-link:focus-visible,
        .topbar-menu:focus-visible,
        .sidebar-logout button:focus-visible {
            outline: 2px solid var(--brand-amber);
            outline-offset: 2px;
        }

        .nav-icon {
            flex: 0 0 auto;
            width: 19px;
            height: 19px;
        }

        .sidebar-bottom {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-top: auto;
            padding-top: 12px;
        }

        .sidebar-divider {
            margin: 8px 0;
            border: 0;
            border-top: 1px solid rgba(244, 239, 226, .16);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 10px;
            border-radius: 10px;
            background: rgba(244, 239, 226, .07);
        }

        .sidebar-avatar {
            display: grid;
            flex: 0 0 auto;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #f2e6c4;
            color: #5a4020;
            font-size: 12px;
            font-weight: 700;
            place-items: center;
        }

        .sidebar-user-text {
            display: flex;
            flex-direction: column;
            min-width: 0;
            line-height: 1.25;
        }

        .sidebar-user-name {
            overflow: hidden;
            font-size: 13px;
            font-weight: 600;
            color: #f9f4e6;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sidebar-user-role {
            font-size: 11px;
            color: rgba(244, 239, 226, .62);
        }

        .sidebar-logout {
            margin: 0 0 0 auto;
        }

        .sidebar-logout button {
            display: grid;
            width: 32px;
            height: 32px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: rgba(244, 239, 226, .7);
            cursor: pointer;
            place-items: center;
        }

        .sidebar-logout button:hover {
            background: rgba(180, 48, 32, .32);
            color: #fff;
        }

        .sidebar-logout svg {
            width: 17px;
            height: 17px;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 15;
            display: flex;
            align-items: center;
            gap: 14px;
            min-height: 72px;
            padding: 0 clamp(16px, 2.4vw, 30px);
            padding-top: env(safe-area-inset-top);
            border-bottom: 1px solid var(--border-warm);
            background: var(--bg-warm);
        }

        .topbar-menu {
            display: none;
            flex: 0 0 auto;
            width: 40px;
            height: 40px;
            border: 1px solid var(--border-warm);
            border-radius: 8px;
            background: var(--surface-warm);
            color: var(--brand-brown);
            cursor: pointer;
            place-items: center;
        }

        .topbar-menu svg {
            width: 20px;
            height: 20px;
        }

        .crumb {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            font-size: 13px;
            color: #8a7a6a;
        }

        .crumb-root {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .crumb-sep {
            color: #b9ab97;
        }

        .crumb-current {
            overflow: hidden;
            font-weight: 700;
            color: var(--text-warm);
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-left: auto;
        }

        .store-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 12px;
            border-radius: 6px;
            background: #e4ecdc;
            color: #506c44;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .store-pill::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .store-pill.closed {
            background: #ece3d0;
            color: #8a7a6a;
        }

        .topbar-date {
            font-size: 13px;
            color: #6b5b4d;
            white-space: nowrap;
        }

        .drawer-backdrop {
            position: fixed;
            inset: 0;
            z-index: 35;
            background: rgba(28, 18, 12, .5);
        }

        body.drawer-open {
            overflow: hidden;
        }

        main {
            display: flex;
            flex: 1;
            flex-direction: column;
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 16px;
        }

        .shell-main main {
            max-width: 1320px;
            padding: 24px clamp(16px, 2.4vw, 32px) 48px;
        }

        body.is-guest main {
            padding-bottom: 16px;
        }

        @media (max-width: 1099px) {
            .shell {
                grid-template-columns: minmax(0, 1fr);
            }

            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 40;
                width: 264px;
                max-width: 84vw;
                height: 100vh;
                height: 100dvh;
                transform: translateX(-100%);
                transition: transform .2s ease;
            }

            .sidebar.is-open {
                transform: none;
            }

            .topbar-menu {
                display: grid;
            }
        }

        @media (max-width: 639px) {
            .topbar {
                gap: 10px;
                min-height: 64px;
            }

            .topbar-date {
                display: none;
            }

            .crumb-root,
            .crumb-sep {
                display: none;
            }

            .crumb {
                font-size: 12.5px;
            }

            .topbar-right {
                gap: 10px;
            }

            .store-pill {
                padding: 6px 10px;
                font-size: 11.5px;
            }

            .shell-main main {
                padding: 18px 16px 40px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .sidebar {
                transition: none;
            }
        }

        .page-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .page-head > * {
            min-width: 0;
        }

        .page-head h1,
        .page-head p {
            overflow-wrap: anywhere;
        }

        .order-title-name {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        h1 {
            margin: 0 0 4px;
            font-size: 1.5rem;
            line-height: 1.25;
        }

        h2 {
            margin: 0 0 12px;
            font-size: 1.1rem;
        }

        .card {
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
        }

        .grid > * {
            min-width: 0;
        }

        .stats {
            margin-bottom: 16px;
        }

        .stat {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .stat-label {
            color: var(--muted);
            font-size: 13px;
            font-weight: 600;
        }

        .stat-value {
            font-size: clamp(1.25rem, 4.5vw, 1.5rem);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            line-height: 1.2;
        }

        .muted {
            color: var(--muted);
            font-size: 14px;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            background: #f3e8d3;
            color: var(--brand-dark);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .quick-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px;
        }

        .quick {
            padding: 12px;
            border: 1px dashed var(--border);
            border-radius: 10px;
            background: #faf9f6;
        }

        .quick strong {
            display: block;
            font-size: 14px;
        }

        .quick span {
            font-size: 12px;
        }

        .quick-link {
            display: block;
            text-decoration: none;
        }

        .quick-link:hover {
            border-color: var(--brand);
            background: #fff7ed;
        }

        .quick-group-title {
            margin: 16px 0 8px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .quick-group-title:first-of-type {
            margin-top: 0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 10px 16px;
            border: 0;
            border-radius: 8px;
            background: var(--brand);
            color: #fff;
            font: inherit;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .btn:hover {
            background: var(--brand-dark);
        }

        .btn:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .btn-block {
            width: 100%;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 600;
        }

        .field {
            margin-bottom: 14px;
        }

        input,
        select,
        textarea {
            width: 100%;
            margin-top: 0;
            padding: 12px;
            border: 1px solid #cfc7bb;
            border-radius: 8px;
            background: #fff;
            color: var(--text);
            font: inherit;
            font-size: 16px;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--brand);
            outline: 2px solid #f0c38a;
            outline-offset: 1px;
        }

        .input-wrap {
            position: relative;
            margin-top: 0;
        }

        .input-wrap input {
            margin-top: 0;
            padding-right: 48px;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            padding: 0;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            transform: translateY(-50%);
        }

        .toggle-password:hover {
            background: #f5f5f4;
            color: var(--text);
        }

        .toggle-password svg {
            width: 20px;
            height: 20px;
        }

        .toggle-password .eye-off {
            display: none;
        }

        .toggle-password[aria-pressed="true"] .eye {
            display: none;
        }

        .toggle-password[aria-pressed="true"] .eye-off {
            display: block;
        }

        /* Halaman login — mengikuti desain Figma */
        @font-face {
            font-family: "Lora";
            font-style: normal;
            font-weight: 400 700;
            font-display: swap;
            src: url("{{ asset('fonts/lora-var.woff2') }}") format("woff2");
        }

        body.is-login {
            background: var(--bg-warm);
        }

        body.is-login main {
            max-width: none;
            padding: 0;
        }

        .auth-split {
            display: grid;
            grid-template-columns: minmax(0, 44.4fr) minmax(0, 55.6fr);
            flex: 1;
            min-height: 100vh;
            min-height: 100dvh;
        }

        .auth-aside {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 28px;
            padding: clamp(24px, 3vw, 40px) clamp(24px, 4.5vw, 66px);
            background: var(--brand-brown);
            color: #f4efe2;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .auth-logo {
            display: block;
            flex: 0 0 auto;
            width: 64px;
            height: 64px;
            object-fit: contain;
        }

        .auth-wordmark {
            display: block;
            font-family: var(--font-serif);
            font-size: 23px;
            font-weight: 600;
            line-height: 1.2;
            letter-spacing: -.01em;
            color: #f9f4e6;
        }

        .auth-tagline {
            display: block;
            margin-top: 5px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: var(--brand-amber);
        }

        .auth-eyebrow {
            margin: 0;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: var(--brand-amber);
        }

        .auth-eyebrow-red {
            color: var(--brand-red);
        }

        .auth-headline {
            margin: 16px 0 0;
            font-family: var(--font-serif);
            font-size: clamp(30px, 3.8vw, 54px);
            font-weight: 600;
            line-height: 1.37;
            letter-spacing: -.012em;
            color: #f9f4e6;
        }

        .auth-rule {
            display: block;
            width: 76px;
            height: 2px;
            margin: 32px 0 22px;
            background: var(--brand-amber);
        }

        .auth-amber {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            line-height: 1.65;
            color: var(--brand-amber);
        }

        .auth-aside-note {
            margin: 26px 0 0;
            max-width: 34ch;
            font-size: 14px;
            line-height: 1.7;
            color: rgba(244, 239, 226, .74);
        }

        .auth-copy {
            margin: 0;
            font-size: 12px;
            color: rgba(244, 239, 226, .52);
        }

        .auth-main {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: clamp(22px, 3vw, 40px) clamp(20px, 4vw, 48px) clamp(20px, 2vw, 28px);
            background: var(--bg-warm);
        }

        .auth-help {
            align-self: stretch;
            margin: 0;
            font-size: 13px;
            color: #7a6a5c;
        }

        .auth-help a,
        .auth-help button {
            padding: 0;
            border: 0;
            background: transparent;
            color: var(--brand-red);
            font: inherit;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .auth-help a:hover,
        .auth-help button:hover {
            text-decoration: underline;
        }

        .auth-form-box {
            width: 100%;
            max-width: 472px;
            margin: auto 0;
        }

        .auth-title {
            margin: 12px 0 10px;
            font-family: var(--font-serif);
            font-size: clamp(26px, 2.6vw, 38px);
            font-weight: 600;
            line-height: 1.2;
            letter-spacing: -.012em;
            color: var(--text-warm);
        }

        .auth-sub {
            margin: 0 0 26px;
            font-size: 14px;
            line-height: 1.6;
            color: #7a6a5c;
        }

        .auth-form-box label {
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #3e2b21;
        }

        .auth-form-box input,
        .auth-form-box textarea {
            padding: 16px;
            border: 1px solid var(--border-warm);
            border-radius: 8px;
            background: var(--surface-warm);
            color: var(--text-warm);
        }

        .auth-form-box input::placeholder,
        .auth-form-box textarea::placeholder {
            color: #9a8b7c;
        }

        .auth-form-box input:focus,
        .auth-form-box textarea:focus {
            border-color: var(--brand-amber);
            outline: 2px solid rgba(224, 188, 72, .3);
        }

        .auth-field-row {
            position: relative;
        }

        .auth-field-row input {
            padding-right: 112px;
        }

        .password-text-toggle {
            position: absolute;
            top: 50%;
            right: 6px;
            min-height: 40px;
            padding: 0 10px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: var(--brand-red);
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transform: translateY(-50%);
        }

        .password-text-toggle:hover {
            background: rgba(180, 48, 32, .08);
        }

        .auth-meta {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            margin: 2px 0 24px;
            font-size: 13px;
        }

        .auth-meta a,
        .auth-meta button {
            padding: 0;
            border: 0;
            background: transparent;
            color: var(--brand-red);
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .auth-meta a:hover,
        .auth-meta button:hover {
            text-decoration: underline;
        }

        .auth-submit {
            width: 100%;
            min-height: 56px;
            border-radius: 8px;
            background: var(--brand-red);
            font-size: 15px;
            font-weight: 700;
        }

        .auth-submit:hover {
            background: var(--brand-red-dark);
        }

        .auth-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 30px 0 18px;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: "";
            flex: 1;
            border-top: 1px solid var(--border-warm);
        }

        .auth-legal {
            margin: 0;
            font-size: 12px;
            line-height: 1.7;
            color: #8a7a6a;
            text-align: center;
        }

        .auth-footnote {
            margin: 24px 0 0;
            font-size: 11px;
            letter-spacing: .02em;
            color: #9a8b7c;
            text-align: center;
        }

        .auth-dialog {
            width: min(440px, calc(100vw - 32px));
            padding: 24px;
            border: 1px solid var(--border-warm);
            border-radius: 14px;
            background: var(--surface-warm);
            color: var(--text-warm);
            box-shadow: 0 24px 60px rgba(30, 20, 12, .28);
        }

        .auth-dialog::backdrop {
            background: rgba(28, 18, 12, .55);
        }

        .auth-dialog h2 {
            margin: 0 0 8px;
            font-family: var(--font-serif);
            font-size: 22px;
        }

        .auth-dialog p {
            margin: 0;
            font-size: 14px;
            line-height: 1.65;
            color: #6b5b4d;
        }

        .auth-dialog .form-actions {
            margin-top: 18px;
        }

        .auth-dialog .btn {
            width: 100%;
            background: var(--brand-red);
        }

        @media (max-width: 767px) {
            .auth-split {
                grid-template-columns: 1fr;
                grid-template-rows: auto 1fr;
                min-height: 0;
            }

            .auth-aside {
                gap: 0;
                padding: 14px 18px;
            }

            .auth-aside-mid,
            .auth-copy {
                display: none;
            }

            .auth-logo {
                width: 48px;
                height: 48px;
            }

            .auth-brand {
                gap: 11px;
            }

            .auth-wordmark {
                font-size: 18px;
            }

            .auth-tagline {
                margin-top: 3px;
                font-size: 9px;
                letter-spacing: .17em;
            }

            .auth-main {
                padding: 20px 18px calc(28px + env(safe-area-inset-bottom));
            }

            .auth-form-box {
                max-width: 100%;
                margin: 22px 0 0;
            }

            .auth-title {
                margin-top: 10px;
                font-size: 28px;
            }

            .auth-sub {
                margin-bottom: 22px;
            }

            .auth-footnote {
                margin-top: 26px;
            }
        }

        .hint {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
        }

        .errors {
            margin-bottom: 16px;
            padding: 10px 12px;
            border: 1px solid #fecaca;
            border-radius: 8px;
            background: #fef2f2;
            color: #991b1b;
            font-size: 14px;
        }

        .errors div + div {
            margin-top: 4px;
        }

        @media (min-width: 768px) {
            main {
                padding: 28px 24px 40px;
            }

            h1 {
                font-size: 1.8rem;
            }

            .card {
                padding: 20px;
            }
        }

        .flash {
            margin-bottom: 16px;
            padding: 10px 12px;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            background: #f0fdf4;
            color: #166534;
            font-size: 14px;
        }

        .flash-error {
            border-color: #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }

        .btn-ghost {
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text);
        }

        .btn-ghost:hover {
            background: #f5f5f4;
        }

        .btn-sm {
            min-height: 36px;
            padding: 6px 10px;
            font-size: 13px;
        }

        @media (max-width: 767px) {
            .btn-sm {
                min-height: 44px;
                padding: 8px 12px;
            }
        }

        .chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
            padding-bottom: 4px;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .chips::-webkit-scrollbar {
            display: none;
        }

        .chip {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            min-height: 40px;
            padding: 8px 14px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: #fff;
            color: var(--muted);
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .chip.active {
            border-color: var(--brand);
            background: var(--brand);
            color: #fff;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 12px;
        }

        .menu-card {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding: 0;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .menu-card-button {
            width: 100%;
            color: inherit;
            font: inherit;
            text-align: left;
            cursor: pointer;
        }

        .menu-card-button:focus-visible {
            outline: 3px solid #f0c38a;
            outline-offset: 2px;
        }

        .menu-thumb {
            position: relative;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            background: #f5f5f4;
        }

        .menu-thumb img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .menu-placeholder {
            display: grid;
            width: 100%;
            height: 100%;
            place-items: center;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            font-size: 2.6rem;
        }

        .menu-thumb .badge {
            position: absolute;
            top: 8px;
            left: 8px;
            padding: 2px 8px;
            background: rgba(255, 255, 255, .92);
            color: var(--brand-dark);
            font-size: 10px;
        }

        .menu-body {
            display: flex;
            flex: 1;
            flex-direction: column;
            padding: 10px 12px 12px;
        }

        .menu-title-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 6px;
        }

        .menu-title-row h2 {
            margin: 0;
            font-size: 1rem;
            line-height: 1.3;
        }

        .menu-desc {
            display: -webkit-box;
            overflow: hidden;
            margin: 4px 0 0;
            font-size: 12px;
            line-height: 1.4;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        .menu-price {
            margin: 6px 0 0;
            color: var(--brand-dark);
            font-size: 14px;
            font-weight: 700;
        }

        .menu-meta {
            margin: 2px 0 0;
            color: var(--muted);
            font-size: 12px;
        }

        .topping-note {
            display: inline-block;
            margin: 6px 0 0;
            padding: 4px 8px;
            border: 1px dashed #fdba74;
            border-radius: 6px;
            background: #fff7ed;
            color: #9a3412;
            font-size: 11px;
            line-height: 1.35;
        }

        .menu-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: auto;
            padding-top: 10px;
        }

        .menu-actions form {
            margin: 0;
        }

        .badge-muted {
            background: #f3f4f6;
            color: #6b7280;
        }

        .empty {
            padding: 32px 16px;
            text-align: center;
        }

        .form-card {
            max-width: 760px;
        }

        .form-section {
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }

        .form-section h2 {
            margin-bottom: 4px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            font-size: 14px;
            font-weight: 600;
        }

        .check input {
            width: auto;
            margin: 0;
        }

        .image-preview {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 8px;
        }

        .image-preview img,
        .current-image img {
            width: 96px;
            height: 96px;
            border: 1px solid var(--border);
            border-radius: 10px;
            object-fit: cover;
        }

        .current-image {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 8px;
        }

        .variant-list {
            display: grid;
            gap: 10px;
        }

        .variant-edit {
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #fafaf9;
        }

        .variant-type {
            margin: 0 0 8px;
            font-size: .8rem;
            font-weight: 600;
            color: var(--muted);
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .variant-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            align-items: center;
        }

        .variant-row input {
            margin: 0;
        }

        .variant-row .check {
            min-height: 44px;
        }

        .variant-row .btn {
            width: 100%;
        }

        .variant-row-add {
            grid-template-columns: 1fr 1fr;
        }

        .variant-row-add .btn {
            grid-column: 1 / -1;
        }

        .variant-toggle {
            margin-top: 6px;
        }

        .sheet {
            position: fixed;
            inset: auto 0 0;
            width: 100%;
            max-width: 520px;
            max-height: 85vh;
            max-height: 85dvh;
            margin: 0 auto;
            padding: 0 16px calc(16px + env(safe-area-inset-bottom));
            overflow-y: auto;
            border: 0;
            border-radius: 18px 18px 0 0;
            background: var(--surface);
            color: var(--text);
            box-shadow: 0 -10px 30px rgba(0, 0, 0, .18);
        }

        .sheet::backdrop {
            background: rgba(15, 23, 42, .45);
        }

        .sheet[open] {
            animation: sheet-up .18s ease-out;
        }

        @keyframes sheet-up {
            from {
                opacity: .7;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .sheet[open] {
                animation: none;
            }
        }

        .sheet-handle {
            width: 44px;
            height: 4px;
            margin: 10px auto 8px;
            border-radius: 999px;
            background: #d6d3d1;
        }

        .sheet-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .sheet-head h2 {
            margin: 0;
            font-size: 1.2rem;
        }

        .sheet-head p {
            margin: 2px 0 0;
        }

        .sheet-close {
            width: 40px;
            height: 40px;
            border: 0;
            border-radius: 50%;
            background: #f5f5f4;
            color: var(--text);
            font-size: 24px;
            line-height: 1;
            cursor: pointer;
        }

        .variant-item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }

        .variant-item:last-child {
            border-bottom: 0;
        }

        .variant-name {
            font-weight: 600;
        }

        .variant-meta {
            display: flex;
            flex-direction: column;
            gap: 2px;
            text-align: right;
        }

        .variant-meta strong {
            color: var(--brand-dark);
        }

        @media (min-width: 560px) {
            .grid-2 {
                grid-template-columns: 1fr 1fr;
            }

            .variant-row {
                grid-template-columns: 1.5fr 1fr auto auto;
            }

            .variant-row .btn {
                width: auto;
            }

            .variant-row-add {
                grid-template-columns: 1.5fr 1fr auto;
            }

            .variant-row-add .btn {
                grid-column: auto;
            }
        }

        @media (min-width: 768px) {
            .sheet {
                inset: 0;
                margin: auto;
                border-radius: 16px;
            }
        }

        /* POS & transaction styles */
        .pos-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
            align-items: start;
        }

        .pos-menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }

        .pos-menu-card {
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
            color: inherit;
            font: inherit;
            text-align: left;
            cursor: pointer;
        }

        .pos-menu-card:hover {
            border-color: var(--brand);
            background: #fffbf5;
        }

        .pos-menu-card:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .pos-menu-card strong {
            font-size: 14px;
            line-height: 1.25;
        }

        .pos-menu-card .price {
            color: var(--brand-dark);
            font-size: 13px;
            font-weight: 700;
        }

        .stock-pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            background: #eef2f7;
            color: #475569;
            font-size: 12px;
            font-weight: 600;
        }

        .stock-pill.low {
            background: #fef2f2;
            color: #b91c1c;
        }

        .cart {
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
        }

        .cart h2 {
            margin-bottom: 4px;
        }

        .cart-item {
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }

        .cart-item-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }

        .cart-item-top > div {
            min-width: 0;
        }

        .cart-item-top strong {
            font-size: 14px;
        }

        .cart-item-top .line-total {
            color: var(--brand-dark);
            font-weight: 700;
            white-space: nowrap;
        }

        .cart-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
        }

        .cart-controls input[type="number"] {
            width: 72px;
            min-height: 44px;
            padding: 8px;
            font-size: 14px;
        }

        .cart-controls .check {
            min-height: 44px;
            font-size: 12px;
        }

        .cart-item .cart-controls form {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            margin: 0;
        }

        @media (max-width: 599px) {
            .cart-item .cart-controls {
                gap: 8px;
            }

            .cart-item .cart-controls > form {
                flex: 1 1 auto;
            }

            .cart-item .cart-controls > form:last-child {
                flex: 0 0 auto;
            }
        }

        .cart-summary {
            margin-top: 14px;
        }

        .summary-line {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 3px 0;
            font-size: 14px;
            font-variant-numeric: tabular-nums;
        }

        .summary-line > :first-child {
            min-width: 0;
        }

        .summary-line > :last-child {
            flex: 0 0 auto;
            text-align: right;
        }

        .summary-line.total {
            margin-top: 8px;
            padding-top: 10px;
            border-top: 1px dashed var(--border);
            font-size: 1.15rem;
            font-weight: 700;
        }

        .inline-form {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 8px;
        }

        .form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .form-actions .btn {
            flex: 1 1 auto;
        }

        @media (max-width: 479px) {
            .form-actions .btn {
                flex: 1 1 100%;
            }
        }

        .inline-form .field {
            flex: 1 1 120px;
            margin: 0;
        }

        .inline-form input,
        .inline-form select {
            min-height: 40px;
            padding: 8px 10px;
            font-size: 14px;
        }

        .inline-form .btn {
            min-height: 40px;
            padding: 8px 12px;
            font-size: 14px;
        }

        .pos-variant-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }

        .pos-variant-row:last-child {
            border-bottom: 0;
        }

        .pos-variant-row .qty-add {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 6px;
            min-width: 96px;
        }

        .pos-variant-row .qty-add input {
            min-height: 36px;
            padding: 6px 8px;
            font-size: 14px;
            text-align: center;
        }

        .table-wrap {
            overflow-x: auto;
            overscroll-behavior-x: contain;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 767px) {
            .table-wrap {
                background:
                    linear-gradient(to right, var(--surface) 30%, rgba(255, 255, 255, 0)) left center,
                    linear-gradient(to left, var(--surface) 30%, rgba(255, 255, 255, 0)) right center,
                    radial-gradient(farthest-side at 0 50%, rgba(0, 0, 0, .12), transparent) left center,
                    radial-gradient(farthest-side at 100% 50%, rgba(0, 0, 0, .12), transparent) right center;
                background-repeat: no-repeat;
                background-size: 40px 100%, 40px 100%, 14px 100%, 14px 100%;
                background-attachment: local, local, scroll, scroll;
            }

            table.data.responsive,
            table.data.responsive tbody,
            table.data.responsive tr,
            table.data.responsive td {
                display: block;
                width: 100%;
            }

            table.data.responsive thead {
                display: none;
            }

            table.data.responsive tr {
                padding: 12px 14px;
                border-bottom: 1px solid var(--border);
            }

            table.data.responsive tr:last-child {
                border-bottom: 0;
            }

            table.data.responsive td {
                position: relative;
                min-height: 22px;
                padding: 3px 0 3px 96px;
                border-bottom: 0;
            }

            table.data.responsive td::before {
                position: absolute;
                top: 3px;
                left: 0;
                width: 88px;
                content: attr(data-label);
                color: var(--muted);
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .03em;
                line-height: 1.7;
                text-transform: uppercase;
            }
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        table.data th,
        table.data td {
            padding: 10px 8px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            vertical-align: top;
        }

        table.data td {
            overflow-wrap: anywhere;
        }

        table.data th {
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .03em;
            white-space: nowrap;
        }

        table.data td.amount,
        table.data th.amount {
            text-align: right;
            white-space: nowrap;
        }

        .status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .status-draft {
            background: #fef3c7;
            color: #92400e;
        }

        .status-finalized {
            background: #dcfce7;
            color: #166534;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 8px;
            margin-bottom: 14px;
        }

        .filter-bar .field {
            margin: 0;
        }

        .filter-bar input,
        .filter-bar select {
            width: auto;
            min-width: 150px;
            min-height: 44px;
            padding: 8px 10px;
            font-size: 14px;
        }

        .filter-bar .field input,
        .filter-bar .field select {
            width: 100%;
        }

        .filter-bar .btn {
            min-height: 44px;
            padding: 8px 14px;
            font-size: 14px;
        }

        .input-inline,
        .select-inline {
            min-height: 40px;
            padding: 8px 10px;
            font-size: 13px;
        }

        .input-inline {
            width: 150px;
            max-width: 100%;
        }

        .select-inline {
            width: auto;
            max-width: 100%;
        }

        @media (max-width: 767px) {
            .input-inline,
            .select-inline {
                min-height: 44px;
            }
        }

        @media (max-width: 599px) {
            .filter-bar {
                gap: 10px;
            }

            .filter-bar .field {
                flex: 1 1 100%;
            }

            .filter-bar .btn {
                flex: 1 1 auto;
            }
        }

        .receipt {
            max-width: 440px;
            margin: 0 auto;
            padding: 24px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--surface);
            box-shadow: 0 4px 14px rgba(0, 0, 0, .05);
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }

        .receipt-head {
            text-align: center;
            padding-bottom: 12px;
            border-bottom: 1px dashed var(--border);
        }

        .receipt-logo-wrap {
            margin-bottom: 8px;
            display: flex;
            justify-content: center;
        }

        .receipt-logo {
            width: 56px;
            height: 56px;
            object-fit: contain;
            border-radius: 50%;
            background: #fff;
            padding: 2px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
        }

        .receipt-brand-title {
            margin: 0 0 4px;
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .receipt-meta {
            display: grid;
            gap: 2px;
            margin-top: 4px;
            color: var(--muted);
            font-size: 13px;
        }

        .receipt-queue-box {
            margin: 14px 0 12px;
            padding: 10px 12px;
            border: 2px dashed var(--border-warm, #dcd4bc);
            border-radius: 10px;
            background: var(--bg-warm, #fdfaf3);
            text-align: center;
        }

        .receipt-queue-caption {
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: var(--muted-warm, #7a6a5c);
            text-transform: uppercase;
        }

        .receipt-queue-number {
            display: block;
            font-size: 2.2rem;
            font-weight: 900;
            line-height: 1.1;
            color: var(--brand, #b45309);
            margin: 2px 0;
            letter-spacing: -0.02em;
        }

        .receipt-queue-type {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 999px;
            background: #fff;
            border: 1px solid var(--border-warm, #dcd4bc);
            color: var(--brand-brown, #3c2820);
        }

        .receipt-info-section {
            padding: 10px 0;
            border-bottom: 1px dashed var(--border);
        }

        .receipt-items {
            padding: 10px 0;
            border-bottom: 1px dashed var(--border);
        }

        .receipt-item-row {
            padding: 6px 0;
        }

        .receipt-item-name {
            display: block;
            font-size: 14px;
            line-height: 1.3;
        }

        .receipt-item-variant {
            font-size: 12px;
            margin-top: 2px;
        }

        .receipt-item-topping {
            font-size: 12px;
            color: var(--brand, #b45309);
            font-weight: 600;
        }

        .receipt-item-price {
            font-size: 12px;
        }

        .receipt-line {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 4px 0;
            font-size: 14px;
        }

        .receipt-line > :first-child {
            min-width: 0;
        }

        .receipt-line > :last-child {
            flex: 0 0 auto;
            text-align: right;
            white-space: nowrap;
        }

        .receipt-line > .receipt-name {
            flex: 0 1 auto;
            min-width: 0;
            text-align: right;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .receipt-line-name > :first-child {
            flex: 0 0 auto;
        }

        .receipt-line-name > .receipt-name {
            flex: 1 1 auto;
        }

        .receipt-summary {
            padding-top: 10px;
        }

        .receipt-line.total {
            margin-top: 8px;
            padding-top: 10px;
            border-top: 1px dashed var(--border);
            font-size: 1.2rem;
            font-weight: 800;
        }

        .receipt-payments, .receipt-notes {
            padding-top: 10px;
            margin-top: 10px;
            border-top: 1px dashed var(--border);
        }

        .receipt-section-heading {
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .receipt-payment-item {
            padding: 6px 0;
            border-bottom: 1px dotted var(--border);
        }

        .receipt-payment-item:last-child {
            border-bottom: none;
        }

        .receipt-notes-content {
            margin: 2px 0 0;
            padding: 6px 8px;
            background: var(--bg);
            border-radius: 6px;
            font-size: 13px;
        }

        .receipt-foot {
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px dashed var(--border);
            color: var(--muted);
            font-size: 13px;
            text-align: center;
        }

        .receipt-thankyou {
            margin: 0;
            font-weight: 700;
            color: var(--text);
            font-size: 14px;
        }

        .receipt-tagline {
            margin: 4px 0 8px;
            font-size: 12px;
        }

        .receipt-barcode-code {
            font-size: 11px;
            letter-spacing: 0.08em;
            opacity: 0.8;
        }

        .pager {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 14px;
            font-size: 14px;
        }

        .pager a {
            color: var(--brand);
            font-weight: 600;
            text-decoration: none;
        }

        .group-title {
            margin: 22px 0 10px;
            font-size: 1rem;
        }

        .stock-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }

        .stock-row:last-child {
            border-bottom: 0;
        }

        .stock-row form {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .stock-row input[type="number"] {
            width: 76px;
            min-height: 44px;
            padding: 8px;
            font-size: 14px;
            text-align: center;
        }

        .stock-row select {
            min-height: 44px;
            padding: 8px;
            font-size: 13px;
        }

        .stock-row input[type="text"] {
            width: 140px;
            min-height: 44px;
            padding: 8px;
            font-size: 13px;
        }

        @media (max-width: 720px) {
            .stock-row {
                grid-template-columns: 1fr;
            }

            .stock-row form {
                flex-wrap: wrap;
            }

            .stock-row form select,
            .stock-row form input[type="number"] {
                flex: 1 1 120px;
                width: auto;
            }

            .stock-row form input[type="text"] {
                flex: 1 1 100%;
                width: auto;
            }
        }

        .stock-row .btn {
            min-height: 44px;
            padding: 8px 14px;
            font-size: 13px;
        }

        /* Order board & running tab styles */
        .status-open {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-processing {
            background: #fef3c7;
            color: #92400e;
        }

        .status-ready {
            background: #dcfce7;
            color: #166534;
        }

        .status-completed {
            background: #e5e7eb;
            color: #374151;
        }

        .status-unpaid {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-partial {
            background: #fef3c7;
            color: #92400e;
        }

        .status-paid {
            background: #dcfce7;
            color: #166534;
        }

        .new-order {
            display: grid;
            gap: 10px;
            margin-bottom: 16px;
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
        }

        .new-order .field {
            margin: 0;
        }

        .order-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 12px;
        }

        .order-card {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 14px;
            border: 1px solid var(--border);
            border-left: 4px solid var(--brand);
            border-radius: 12px;
            background: var(--surface);
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .order-card.order-processing {
            border-left-color: #f59e0b;
        }

        .order-card.order-ready {
            border-left-color: #16a34a;
        }

        .order-card.order-completed {
            border-left-color: #6b7280;
            opacity: .82;
        }

        .order-card.order-cancelled {
            border-left-color: #dc2626;
            opacity: .72;
        }

        .order-card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }

        .order-card-head > div {
            min-width: 0;
        }

        .queue-number {
            font-size: 1.45rem;
            font-weight: 800;
            letter-spacing: -.02em;
            line-height: 1.1;
        }

        .order-card h2 {
            display: -webkit-box;
            margin: 2px 0 0;
            overflow: hidden;
            overflow-wrap: anywhere;
            font-size: 1rem;
            line-height: 1.25;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        .order-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 10px;
            color: var(--muted);
            font-size: 13px;
        }

        .order-card-money {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 12px;
            font-size: 13px;
        }

        .order-card-money strong {
            color: var(--brand-dark);
        }

        .order-card-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: auto;
            padding-top: 4px;
        }

        .order-card-actions form {
            margin: 0;
        }

        .order-card-actions .btn {
            min-height: 36px;
            padding: 6px 10px;
            font-size: 13px;
        }

        /* Order history table */
        .orders-table {
            min-width: 900px;
        }

        .orders-table th,
        .orders-table td {
            white-space: nowrap;
        }

        .orders-table td:nth-child(2) {
            min-width: 170px;
            white-space: normal;
        }

        @media (max-width: 1439px) {
            .orders-table .col-secondary {
                display: none;
            }
        }

        table.data .btn {
            white-space: nowrap;
        }

        .order-hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 14px;
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
        }

        .order-hero > div {
            min-width: 0;
        }

        .order-hero .queue-number {
            font-size: 2rem;
        }

        .order-hero-side {
            text-align: right;
        }

        @media (max-width: 599px) {
            .order-hero-side {
                text-align: left;
            }
        }

        .order-hero h1 {
            margin: 2px 0 4px;
            overflow-wrap: anywhere;
            font-size: 1.35rem;
        }

        .addon-tag {
            display: inline-block;
            margin-left: 6px;
            padding: 1px 6px;
            border-radius: 999px;
            background: #ede9fe;
            color: #6d28d9;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .payment-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            padding: 7px 0;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }

        .payment-row > :first-child {
            min-width: 0;
        }

        .payment-row > :last-child {
            flex: 0 0 auto;
            text-align: right;
            white-space: nowrap;
        }

        .payment-row:last-child {
            border-bottom: 0;
        }

        .payment-row .muted {
            font-size: 12px;
        }

        .money-big {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--brand-dark);
        }

        /* Responsive helpers */
        .show-mobile {
            display: none;
        }

        @media (max-width: 767px) {
            .show-mobile {
                display: block;
            }

            .hide-mobile {
                display: none !important;
            }
        }

        /* Order history: keep cards until the wide table fits */
        @media (max-width: 1279px) {
            .orders-cards {
                display: block;
            }

            .orders-table-wrap {
                display: none;
            }
        }

        /* Mobile running-tab switcher */
        .pos-mobile-bar,
        .pos-mobile-spacer {
            display: none;
        }

        @media (max-width: 899px) {
            .pos-pane {
                display: none;
            }

            .pos-pane.is-active {
                display: block;
            }

            .pos-mobile-bar {
                position: fixed;
                right: 0;
                bottom: 0;
                left: 0;
                z-index: 19;
                display: flex;
                gap: 8px;
                padding: 8px 12px;
                padding-bottom: calc(8px + env(safe-area-inset-bottom));
                border-top: 1px solid var(--border);
                background: var(--surface);
                box-shadow: 0 -2px 10px rgba(0, 0, 0, .08);
            }

            .pos-mobile-tab {
                display: flex;
                flex: 1;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 1px;
                min-height: 46px;
                padding: 6px 10px;
                border: 1px solid var(--border);
                border-radius: 10px;
                background: #fff;
                color: var(--text);
                font: inherit;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
            }

            .pos-mobile-tab strong {
                color: var(--brand-dark);
                font-size: 12px;
                max-width: 100%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .pos-mobile-tab.active {
                border-color: var(--brand);
                background: var(--brand);
                color: #fff;
            }

            .pos-mobile-tab.active strong {
                color: #fff;
            }

            .pos-mobile-spacer {
                display: block;
                height: 74px;
            }
        }

        @media (min-width: 900px) {
            .pos-pane {
                display: block;
            }
        }

        @media (min-width: 600px) {
            .new-order {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto;
                align-items: end;
            }
        }

        @media (min-width: 900px) {
            .pos-layout {
                grid-template-columns: minmax(0, 1fr) 370px;
            }

            .pos-cart {
                position: sticky;
                top: 96px;
            }
        }

        @media print {
            .sidebar,
            .topbar,
            .drawer-backdrop,
            .no-print,
            .flash,
            .errors {
                display: none !important;
            }

            .shell {
                display: block;
                min-height: 0;
            }

            .shell-main {
                min-height: 0;
            }

            @page {
                size: auto;
                margin: 4mm 2mm;
            }

            body {
                background: #fff !important;
                color: #000 !important;
            }

            main {
                max-width: none;
                padding: 0;
            }

            .receipt {
                max-width: 310px !important;
                margin: 0 auto !important;
                padding: 4px !important;
                border: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                font-family: 'Courier New', Courier, monospace !important;
                color: #000 !important;
            }

            .receipt-logo {
                filter: grayscale(100%) contrast(180%) !important;
                box-shadow: none !important;
                width: 48px !important;
                height: 48px !important;
            }

            .receipt-queue-box {
                background: transparent !important;
                border: 1.5px dashed #000 !important;
            }

            .receipt-queue-caption {
                color: #000 !important;
            }

            .receipt-queue-number {
                color: #000 !important;
            }

            .receipt-queue-type {
                border: 1px solid #000 !important;
                color: #000 !important;
            }

            .receipt-item-topping {
                color: #000 !important;
            }

            .receipt-notes-content {
                background: transparent !important;
                border: 1px solid #000 !important;
            }

            .receipt-line, .receipt-meta, .receipt-foot, .muted {
                color: #000 !important;
            }

            .receipt-head, .receipt-items, .receipt-info-section, .receipt-line.total, .receipt-payments, .receipt-notes, .receipt-foot {
                border-color: #000 !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="@auth is-auth @else is-guest @endauth @yield('body_class')">
    @auth
        <div class="shell">
            @include('partials.sidebar')

            <div class="shell-main">
                @include('partials.topbar')

                <main>
                    @yield('content')
                </main>
            </div>
        </div>

        <div class="drawer-backdrop" data-drawer-close hidden></div>
    @else
        <main>
            @yield('content')
        </main>
    @endauth

    <script>
        document.addEventListener('click', function (event) {
            const dialogOpener = event.target.closest('[data-dialog-open]');

            if (dialogOpener) {
                const dialog = document.getElementById(dialogOpener.dataset.dialogOpen);

                if (dialog && typeof dialog.showModal === 'function') {
                    dialog.showModal();
                }

                return;
            }

            const toggle = event.target.closest('[data-password-toggle]');

            if (!toggle) {
                return;
            }

            const input = document.getElementById(toggle.dataset.passwordToggle);

            if (!input) {
                return;
            }

            const show = input.type === 'password';

            input.type = show ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
            toggle.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');

            if (toggle.hasAttribute('data-password-text')) {
                toggle.textContent = show ? 'Sembunyikan' : 'Tampilkan';
            }
        });

        document.addEventListener('click', function (event) {
            const dialog = event.target.closest('dialog[open]');

            if (dialog && event.target === dialog) {
                dialog.close();
            }
        });

        (function () {
            const sidebar = document.getElementById('app-sidebar');
            const backdrop = document.querySelector('[data-drawer-close]');
            const openButton = document.querySelector('[data-drawer-open]');

            if (!sidebar) {
                return;
            }

            function setDrawer(open) {
                sidebar.classList.toggle('is-open', open);
                document.body.classList.toggle('drawer-open', open);

                if (openButton) {
                    openButton.setAttribute('aria-expanded', open ? 'true' : 'false');
                }

                if (backdrop) {
                    backdrop.hidden = !open;
                }
            }

            document.addEventListener('click', function (event) {
                if (event.target.closest('[data-drawer-open]')) {
                    setDrawer(true);

                    return;
                }

                if (event.target.closest('[data-drawer-close]')) {
                    setDrawer(false);

                    return;
                }

                if (event.target.closest('#app-sidebar a')) {
                    setDrawer(false);
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    setDrawer(false);
                }
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1100) {
                    setDrawer(false);
                }
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
