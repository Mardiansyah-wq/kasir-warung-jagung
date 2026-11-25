<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="@yield('theme_color', '#3c2820')">
    <title>@yield('title', 'Jagung Bakar WJT')</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">
    <style>
        :root {
            --brand: #b43020;
            --brand-dark: #96281a;
            /* Warm palette as default */
            --bg: #f6f0e5;
            --surface: #fffdf7;
            --border: #e4d6c2;
            --text: #36251e;
            --muted: #72645d;
            --brand-brown: #36251e;
            --brand-brown-light: #4a342a;
            --brand-amber: #e0b747;
            --brand-amber-dark: #c99c2d;
            --brand-red: #b43020;
            --brand-red-dark: #96281a;
            /* Explicit warm aliases for comprehensive compatibility */
            --bg-warm: #f6f0e5;
            --surface-warm: #fffdf7;
            --border-warm: #e4d6c2;
            --text-warm: #36251e;
            --muted-warm: #72645d;
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
            -webkit-tap-highlight-color: rgba(180, 48, 32, .12);
        }

        a {
            color: inherit;
        }

        ::selection {
            background: rgba(224, 183, 71, .35);
            color: var(--brand-brown);
        }

        :where(a, button, summary, [role="button"]):focus-visible {
            outline: 2px solid var(--brand);
            outline-offset: 2px;
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
            padding: 22px 10px 16px;
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

        .sidebar-brand-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            width: 100%;
        }

        /* Tombol tutup hanya dipakai saat sidebar berubah jadi drawer */
        .sidebar-close-btn {
            display: none;
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
            gap: 9px;
            width: 100%;
            min-height: 44px;
            padding: 9px 9px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #e7dcc8;
            font: inherit;
            font-size: 12.5px;
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

        .sidebar-avatar-img {
            flex: 0 0 auto;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 1.5px solid rgba(244, 239, 226, .35);
        }

        .account-avatar-wrap {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 6px;
        }

        .account-avatar-preview {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            overflow: hidden;
            background: #f2e6c4;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--border);
            flex: 0 0 68px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
        }

        .account-avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .account-avatar-initials {
            font-size: 22px;
            font-weight: 700;
            color: #5a4020;
        }

        .account-avatar-actions {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .account-avatar-remove-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: var(--brand-red, #b43020);
            cursor: pointer;
            margin-top: 4px;
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

        /* Shell mobile — mengikuti desain 06 · Mobile */
        .mobile-shell {
            display: none;
        }

        @media (max-width: 1099px) {
            .topbar {
                display: none;
            }

            body {
                background: var(--bg-warm);
            }

            .mobile-shell {
                display: block;
                flex: 0 0 auto;
            }

            .mobile-brand {
                padding: 16px 20px 14px;
                padding-top: calc(16px + env(safe-area-inset-top));
                background: var(--brand-brown);
                color: #f9f4e6;
            }

            .mobile-brand-row {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
            }

            .mobile-brand-left {
                display: flex;
                flex: 1 1 auto;
                align-items: center;
                gap: 10px;
                min-width: 0;
            }

            .mobile-menu-btn {
                display: grid;
                flex: 0 0 auto;
                width: 38px;
                height: 38px;
                padding: 0;
                border: 1px solid rgba(255, 249, 239, .18);
                border-radius: 10px;
                background: rgba(255, 249, 239, .08);
                color: #f9f4e6;
                cursor: pointer;
                place-items: center;
            }

            .mobile-menu-btn svg {
                width: 19px;
                height: 19px;
            }

            .mobile-menu-btn:hover,
            .mobile-menu-btn:focus-visible {
                background: rgba(255, 249, 239, .16);
                outline: none;
            }

            .mobile-brand-title-wrap {
                display: flex;
                flex-direction: column;
                min-width: 0;
            }

            .mobile-brand-name {
                min-width: 0;
                overflow-wrap: anywhere;
                font-family: var(--font-serif);
                font-size: 1.25rem;
                font-weight: 600;
                line-height: 1.15;
                letter-spacing: -.01em;
                color: #fff9ef;
            }

            .mobile-brand-role {
                overflow: hidden;
                margin-top: 2px;
                color: var(--brand-amber);
                font-size: 10px;
                font-weight: 700;
                letter-spacing: .14em;
                text-overflow: ellipsis;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .mobile-open {
                display: inline-flex;
                flex: 0 0 auto;
                align-items: center;
                gap: 6px;
                padding-top: 4px;
                color: var(--brand-amber);
                font-size: 12px;
                font-weight: 700;
                white-space: nowrap;
            }

            .mobile-open::before {
                content: "";
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: currentColor;
            }

            .mobile-open.closed {
                color: #b4a9a1;
            }

            .mobile-meta {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 8px 12px;
                margin-top: 6px;
            }

            .mobile-brand-sub {
                flex: 1 1 auto;
                min-width: 0;
                margin: 0;
                color: #c9bcae;
                font-size: 12.5px;
                line-height: 1.4;
            }

            .mobile-account {
                display: flex;
                flex: 0 0 auto;
                align-items: center;
                gap: 6px;
            }

            .mobile-account form {
                margin: 0;
            }

            .mobile-account-link {
                padding: 5px 11px;
                border-radius: 999px;
                background: rgba(244, 239, 226, .12);
                color: #f3e8d6;
                font-size: 12px;
                font-weight: 600;
                text-decoration: none;
            }

            .mobile-account-link.active {
                background: var(--brand-amber);
                color: var(--brand-brown);
            }

            .mobile-logout {
                display: grid;
                width: 30px;
                height: 30px;
                border: 0;
                border-radius: 999px;
                background: rgba(244, 239, 226, .12);
                color: #f3e8d6;
                cursor: pointer;
                place-items: center;
            }

            .mobile-logout:hover {
                background: rgba(180, 48, 32, .45);
                color: #fff;
            }

            .mobile-logout .nav-icon {
                width: 16px;
                height: 16px;
            }

            .mobile-account-link:focus-visible,
            .mobile-logout:focus-visible {
                outline: 2px solid var(--brand-amber);
                outline-offset: 2px;
            }

            .mobile-nav {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 6px;
                padding: 9px 12px;
                border-bottom: 1px solid var(--border-warm);
                background: var(--bg-warm);
            }

            .mobile-tab {
                display: inline-flex;
                min-width: 0;
                align-items: center;
                justify-content: center;
                min-height: 36px;
                padding: 6px 4px;
                border-radius: 999px;
                border: 1px solid var(--border-warm);
                background: var(--surface-warm);
                color: var(--muted-warm);
                font-size: 12px;
                font-weight: 600;
                line-height: 1;
                white-space: nowrap;
                text-decoration: none;
                transition: all .15s ease;
            }

            .mobile-tab.active {
                background: var(--brand-amber);
                border-color: var(--brand-amber-dark);
                color: var(--brand-brown);
                font-weight: 700;
                box-shadow: 0 2px 6px rgba(224, 183, 71, .25);
            }

            .mobile-tab:focus-visible {
                outline: 2px solid var(--brand-brown);
                outline-offset: 2px;
            }
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

            .sidebar-close-btn {
                position: absolute;
                top: 10px;
                right: 10px;
                display: grid;
                width: 32px;
                height: 32px;
                padding: 0;
                border: 0;
                border-radius: 8px;
                background: rgba(244, 239, 226, .1);
                color: #f4efe2;
                cursor: pointer;
                place-items: center;
            }

            .sidebar-close-btn:hover {
                background: rgba(180, 48, 32, .5);
            }

            .sidebar-close-btn svg {
                width: 18px;
                height: 18px;
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

        .head-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .role-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 12px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--surface);
            color: var(--brand-brown);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .02em;
            white-space: nowrap;
        }

        .role-chip-owner {
            border-color: #ead7a6;
            background: #fdf6e6;
        }

        .role-chip-staff {
            border-color: #c7e5cb;
            background: #edf7ed;
            color: #1e7039;
        }

        .role-chip-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--brand-amber);
            box-shadow: 0 0 0 3px rgba(224, 183, 71, .18);
        }

        .role-chip-staff .role-chip-dot {
            background: #3f6b3a;
            box-shadow: 0 0 0 3px rgba(63, 107, 58, .15);
        }

        .role-chip-hero {
            border-color: rgba(232, 205, 140, .35);
            background: rgba(255, 253, 247, .08);
            color: #f2dfb4;
        }

        .role-chip-hero .role-chip-dot {
            background: var(--brand-amber);
            box-shadow: 0 0 0 3px rgba(224, 183, 71, .18);
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
            border-color: var(--brand-amber);
            background: #fdf6e6;
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
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
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
            outline: 2px solid #e8cd8c;
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
            right: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
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
            min-height: 44px;
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
            background: var(--surface);
            color: var(--text);
        }

        .btn-ghost:hover {
            background: var(--bg);
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
            background: var(--surface);
            color: var(--muted);
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .chip.active {
            border-color: var(--brand-amber);
            background: var(--brand-amber);
            color: var(--brand-brown);
            font-weight: 700;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
        }

        @media (min-width: 720px) {
            .menu-grid {
                grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
                gap: 16px;
            }
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
            outline: 3px solid #e8cd8c;
            outline-offset: 2px;
        }

        .menu-thumb {
            position: relative;
            aspect-ratio: 4 / 3;
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
            color: var(--brand-brown);
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
            border: 1px dashed #e3b7a6;
            border-radius: 6px;
            background: #fdf3ee;
            color: #8a2f1c;
            font-size: 11px;
            line-height: 1.35;
        }

        .menu-actions {
            display: flex;
            gap: 8px;
            margin-top: auto;
            padding-top: 10px;
        }

        .menu-actions .btn {
            flex: 1 1 0;
            justify-content: center;
            padding-inline: 8px;
        }

        .menu-actions form {
            display: flex;
            flex: 1 1 0;
            margin: 0;
        }

        .menu-actions form .btn {
            width: 100%;
        }

        @media (max-width: 480px) {
            .menu-actions {
                gap: 6px;
            }

            .menu-actions .btn {
                padding-inline: 4px;
                font-size: 11.5px;
                letter-spacing: 0;
            }
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

        /* Foto Menu: Preview & Current */
        .image-preview,
        .current-image {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 10px;
            padding: 10px 14px;
            border: 1px solid var(--border-warm);
            border-radius: 12px;
            background: var(--surface-warm);
        }

        .image-preview img,
        .current-image img {
            width: 72px;
            height: 72px;
            border: 1px solid var(--border-warm);
            border-radius: 10px;
            object-fit: cover;
            box-shadow: 0 1px 3px rgba(54, 37, 30, .08);
            flex: 0 0 auto;
        }

        .image-preview-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .image-preview-tag {
            font-size: 11px;
            font-weight: 700;
            color: var(--brand-brown);
            text-transform: uppercase;
            letter-spacing: .02em;
        }

        /* Varian Menu di Halaman Edit */
        .variant-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .variant-edit {
            padding: 14px 16px;
            border: 1px solid var(--border-warm);
            border-radius: 12px;
            background: var(--surface-warm);
            box-shadow: 0 1px 3px rgba(54, 37, 30, .03);
            transition: border-color .15s ease;
        }

        .variant-edit:hover {
            border-color: #d6c4aa;
        }

        .variant-edit-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 6px 12px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px dashed var(--border-warm);
        }

        .variant-edit-title {
            margin: 0;
            font-size: 14.5px;
            font-weight: 700;
            color: var(--brand-brown);
        }

        .variant-edit-badges {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .variant-type {
            margin: 0;
            font-size: 11px;
            font-weight: 700;
            color: var(--brand-amber-dark);
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .variant-row {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr) auto auto;
            gap: 8px 12px;
            align-items: end;
        }

        .variant-field-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .variant-field-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--muted-warm);
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .variant-row input {
            width: 100%;
            margin: 0;
            min-height: 40px;
        }

        .variant-row input[type="checkbox"] {
            width: 18px;
            height: 18px;
            min-height: 18px;
            margin: 0;
        }

        .variant-row .check {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            margin: 0;
            cursor: pointer;
        }

        .variant-row .btn {
            min-height: 40px;
            white-space: nowrap;
        }

        .variant-row-add {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(0, 1.4fr) minmax(0, 1fr) auto;
            gap: 8px 12px;
            align-items: end;
        }

        .type-shortcut-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 6px;
        }

        .type-shortcut-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid var(--border-warm);
            background: var(--bg-warm);
            color: var(--brand-brown);
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s ease;
        }

        .type-shortcut-btn:hover {
            background: var(--brand-amber);
            border-color: var(--brand-amber-dark);
        }

        .type-shortcut-btn.is-selected {
            background: var(--brand-brown);
            border-color: var(--brand-brown);
            color: #fff;
        }

        .live-preview-box {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 8px;
            background: var(--bg-warm);
            border: 1px dashed var(--border-warm);
            font-size: 12.5px;
            color: var(--muted-warm);
            margin-top: 10px;
        }

        .live-preview-box strong {
            color: var(--brand-brown);
        }

        /* Badge Jenis / Tipe Penyajian (Utuh vs Serut) */
        .badge-type-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.3;
            white-space: nowrap;
        }

        .badge-type-pill.type-utuh {
            background: #fdf5df;
            color: #8a6308;
            border: 1px solid #f3dc98;
        }

        .badge-type-pill.type-serut {
            background: #f4ede6;
            color: #5d3824;
            border: 1px solid #ddccbe;
        }

        .badge-type-pill.type-custom {
            background: var(--bg-warm);
            color: var(--brand-brown);
            border: 1px solid var(--border-warm);
        }

        /* Sub-filter tab jenis penyajian di setiap menu (Kelola Stok) */
        .menu-type-tabs {
            display: flex;
            align-items: center;
            gap: 6px;
            overflow-x: auto;
            scrollbar-width: none;
            padding: 4px 0 8px;
            margin-bottom: 8px;
        }

        .menu-type-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 11px;
            border-radius: 999px;
            border: 1px solid var(--border-warm);
            background: var(--surface-warm);
            color: var(--muted-warm);
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all .15s ease;
        }

        .menu-type-tab-btn:hover {
            border-color: var(--brand-amber);
            color: var(--brand-brown);
        }

        .menu-type-tab-btn.active {
            background: var(--brand-brown);
            border-color: var(--brand-brown);
            color: #fff9ef;
            font-weight: 700;
        }

        .variant-actions-secondary {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-top: 10px;
        }

        @media (max-width: 639px) {
            .variant-edit {
                padding: 12px 14px;
            }

            .variant-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .variant-row .check {
                min-height: 36px;
            }

            .variant-row .btn {
                width: 100%;
            }

            .variant-row-add {
                grid-template-columns: 1fr;
            }

            .variant-row-add .btn {
                width: 100%;
            }

            .variant-actions-secondary {
                justify-content: stretch;
            }

            .variant-actions-secondary form,
            .variant-actions-secondary .btn {
                width: 100%;
            }
        }

        /* Log Aktivitas: Feed Cards di Mobile */
        .activity-cards {
            display: none;
        }

        .activity-card {
            padding: 14px 16px;
            border: 1px solid var(--border-warm);
            border-radius: 12px;
            background: var(--surface-warm);
            box-shadow: 0 1px 3px rgba(54, 37, 30, .03);
            margin-bottom: 10px;
        }

        .activity-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 8px;
        }

        .activity-card-head .status {
            max-width: 100%;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .activity-card-time {
            font-size: 12px;
            color: var(--muted-warm);
            font-weight: 600;
            white-space: nowrap;
        }

        .activity-card-user {
            font-size: 13px;
            color: var(--text-warm);
            margin-bottom: 6px;
        }

        .activity-card-user strong {
            color: var(--brand-brown);
        }

        .activity-card-subject {
            font-size: 14.5px;
            font-weight: 700;
            color: var(--brand-brown);
            margin-bottom: 4px;
            line-height: 1.3;
        }

        .activity-card-desc {
            font-size: 12.5px;
            color: var(--muted-warm);
            margin-bottom: 8px;
        }

        .activity-changes-box {
            padding: 8px 12px;
            border-radius: 8px;
            background: var(--bg-warm);
            border: 1px solid var(--border-warm);
            font-size: 12px;
            color: var(--text-warm);
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-top: 8px;
        }

        .activity-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-top: 8px;
            padding-top: 6px;
            font-size: 11px;
            color: var(--muted-warm);
        }

        @media (max-width: 767px) {
            .activity-cards {
                display: block;
            }

            .activity-table-wrap {
                display: none !important;
            }
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
            color: var(--brand-brown);
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
            border-color: var(--brand-amber);
            background: #fdf6e6;
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
            color: var(--brand-brown);
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
            color: var(--brand-brown);
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
                padding: 4px 0 4px 100px;
                border-bottom: 0;
            }

            table.data.responsive td::before {
                position: absolute;
                top: 4px;
                left: 0;
                width: 90px;
                content: attr(data-label);
                color: var(--muted);
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .03em;
                line-height: 1.5;
                text-transform: uppercase;
            }

            table.data.responsive td[data-label="Aksi"] {
                padding-left: 0;
                margin-top: 8px;
                padding-top: 8px;
                border-top: 1px dashed var(--border-warm);
            }

            table.data.responsive td[data-label="Aksi"]::before {
                position: static;
                display: block;
                margin-bottom: 6px;
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

        /* Tabel ringkas: kolom opsional disembunyikan di layar sempit */
        .table-narrow .row-sub,
        .table-narrow .row-code,
        .table-narrow .label-narrow,
        .table-narrow .foot-narrow {
            display: none;
        }

        .bar-line {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .bar-track {
            flex: 1;
            height: 6px;
            overflow: hidden;
            border-radius: 999px;
            background: #f3f4f6;
        }

        .bar-fill {
            height: 100%;
            background: var(--brand);
        }

        .bar-value {
            font-size: 13px;
            white-space: nowrap;
        }

        @media (max-width: 899px) {
            table.data.table-narrow {
                table-layout: fixed;
                font-size: 12.5px;
            }

            table.data.table-narrow th,
            table.data.table-narrow td {
                padding: 8px 6px;
                white-space: normal;
                overflow-wrap: anywhere;
            }

            table.data.table-narrow th {
                font-size: 10.5px;
                letter-spacing: .02em;
            }

            .table-narrow .col-optional {
                display: none;
            }

            .table-narrow .label-wide {
                display: none;
            }

            .table-narrow .label-narrow {
                display: inline;
            }

            .table-narrow .foot-wide {
                display: none;
            }

            .table-narrow .foot-narrow {
                display: table-row;
            }

            .table-narrow .row-sub {
                display: block;
                margin-top: 4px;
                font-size: 11px;
                font-weight: 400;
                line-height: 1.5;
            }

            table.data.table-narrow td.amount {
                white-space: normal;
            }

            .table-narrow .status {
                padding: 2px 6px;
                font-size: 9.5px;
                letter-spacing: 0;
            }

            .table-narrow .bar-line {
                flex-direction: column-reverse;
                align-items: stretch;
                gap: 4px;
            }

            .table-narrow .bar-value {
                font-size: 12px;
            }

            /* Laporan harian: antrian, pelanggan, item, total */
            .table-narrow th.col-queue { width: 22%; }
            .table-narrow th.col-customer { width: 34%; }
            .table-narrow th.col-items { width: 15%; }
            .table-narrow th.col-total { width: 29%; }

            /* Laporan mingguan: hari, pesanan, uang masuk */
            .table-narrow th.col-day { width: 32%; }
            .table-narrow th.col-orders { width: 30%; }
            .table-narrow th.col-revenue { width: 38%; }

            .report-pair {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 359px) {
            table.data.table-narrow {
                font-size: 11.5px;
            }

            table.data.table-narrow th,
            table.data.table-narrow td {
                padding: 7px 5px;
            }
        }

        .status {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .status-draft {
            background: #fdf5df;
            color: #8a6308;
            border: 1px solid #f3dc98;
        }

        .status-finalized {
            background: #edf7ed;
            color: #1e7039;
            border: 1px solid #c7e5cb;
        }

        .status-cancelled {
            background: #fdf3f2;
            color: #b43020;
            border: 1px solid #f6cfc9;
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

        .filter-bar .filter-search {
            min-width: 0;
        }

        @media (min-width: 600px) {
            .filter-bar .filter-search {
                flex: 1 1 200px;
            }
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
            background: var(--surface);
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
            color: var(--brand-brown, #3c2820);
            margin: 2px 0;
            letter-spacing: -0.02em;
        }

        .receipt-queue-type {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 999px;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--brand-brown);
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
            color: var(--brand, #b43020);
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
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 20px 0 8px;
            font-size: 1rem;
        }

        .group-title .badge {
            flex: 0 0 auto;
        }

        .stock-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 8px 12px;
            align-items: center;
            padding: 11px 0;
            border-bottom: 1px solid var(--border);
        }

        .stock-row:last-child {
            border-bottom: 0;
        }

        .stock-row-main {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 4px 8px;
            min-width: 0;
        }

        .stock-row-name {
            font-size: 13.5px;
            line-height: 1.3;
            overflow-wrap: anywhere;
        }

        .stock-row-price {
            color: var(--muted);
            font-size: 12px;
            white-space: nowrap;
        }

        .stock-row-main .stock-pill {
            flex: 0 0 auto;
            margin-left: auto;
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

        .stock-row input[type="text"][hidden] {
            display: none;
        }

        .stock-row .btn {
            min-height: 44px;
            padding: 8px 14px;
            font-size: 13px;
            white-space: nowrap;
        }

        @media (max-width: 720px) {
            .group-title {
                margin: 18px 0 8px;
                font-size: .95rem;
            }

            .stock-row {
                grid-template-columns: minmax(0, 1fr);
                gap: 6px;
                padding: 10px 0;
            }

            .stock-row-main {
                flex-wrap: nowrap;
                gap: 3px 8px;
            }

            .stock-row-name {
                flex: 1 1 auto;
                min-width: 0;
                font-size: 13.5px;
            }

            .stock-row-price {
                display: none;
            }

            .stock-row form {
                display: grid;
                grid-template-columns: 92px minmax(0, 1fr);
                gap: 6px;
            }

            .stock-row form select {
                grid-column: 1 / -1;
                width: 100%;
                min-width: 0;
                padding: 8px 10px;
                font-size: 13px;
            }

            .stock-row form input[type="number"] {
                width: 100%;
                padding: 8px;
                font-size: 14px;
            }

            .stock-row form input[type="text"] {
                grid-column: 1 / -1;
                order: 9;
                width: 100%;
                font-size: 13px;
            }

            .stock-row form .btn {
                width: 100%;
            }

            .stock-row select,
            .stock-row input[type="number"] {
                min-height: 40px;
            }

            .stock-row .btn {
                flex: 0 0 auto;
                min-height: 40px;
                padding: 8px 12px;
                font-size: 12.5px;
            }
        }

        @media (max-width: 359px) {
            .stock-row form {
                gap: 5px;
            }

            .stock-row form select {
                padding: 7px 4px;
                font-size: 12px;
            }

            .stock-row form input[type="number"] {
                flex: 0 0 52px;
                width: 52px;
                font-size: 12.5px;
            }

            .stock-row .btn {
                padding: 7px 9px;
                font-size: 12px;
            }
        }

        /* Riwayat stok: kartu di layar sempit, tabel di layar lebar */
        .stock-history-cards {
            display: none;
        }

        .stock-history-card {
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
        }

        .stock-history-card + .stock-history-card {
            margin-top: 8px;
        }

        .stock-history-head {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stock-history-time {
            color: var(--muted);
            font-size: 11.5px;
            white-space: nowrap;
        }

        .stock-history-qty {
            margin-left: auto;
            color: var(--brand-brown);
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
        }

        .stock-history-menu {
            margin: 6px 0 0;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .stock-history-meta {
            margin: 2px 0 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        @media (max-width: 899px) {
            .stock-history-cards {
                display: block;
            }

            .stock-history-table {
                display: none;
            }
        }

        @media (max-width: 720px) {
            .filter-bar .filter-submit {
                display: none;
            }
        }

        /* Order board & running tab styles */
        .status-open {
            background: #fdf5df;
            color: #8a6308;
            border: 1px solid #f3dc98;
        }

        .status-processing {
            background: #fef8e7;
            color: #966b0a;
            border: 1px solid #e0b747;
        }

        .status-ready {
            background: #edf7ed;
            color: #1e7039;
            border: 1px solid #c7e5cb;
        }

        .status-completed {
            background: #eee8df;
            color: #5a4f47;
            border: 1px solid #ded6ca;
        }

        .status-unpaid {
            background: #fdf3f2;
            color: #b43020;
            border: 1px solid #f6cfc9;
        }

        .status-partial {
            background: #fcf1d8;
            color: #8e580a;
            border: 1px solid #f2d79d;
        }

        .status-paid {
            background: #edf7ed;
            color: #1e7039;
            border: 1px solid #c7e5cb;
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
            border-left: 4px solid var(--brand-amber);
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
            color: var(--brand-brown);
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
            color: var(--brand-brown);
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

        /* Kasir mobile: satu halaman scroll (keranjang lalu pilih sajian) */
        .pos-mobile-bar,
        .pos-mobile-spacer {
            display: none;
        }

        .pos-mobile-switcher {
            display: none;
        }

        @media (max-width: 899px) {
            .pos-mobile-switcher {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 4px;
                margin: 0 0 14px;
                padding: 4px;
                border-radius: 12px;
                background: #efe4d1;
            }

            .pos-mobile-tab {
                min-height: 40px;
                padding: 6px 8px;
                border: 0;
                border-radius: 9px;
                background: transparent;
                color: #6b5a4d;
                font: inherit;
                font-size: 12.5px;
                font-weight: 700;
                line-height: 1.25;
                cursor: pointer;
            }

            .pos-mobile-tab.active {
                background: var(--surface);
                color: var(--brand-brown);
                box-shadow: 0 1px 3px rgba(54, 37, 30, .14);
            }

            .pos-mobile-tab:focus-visible {
                outline: 2px solid var(--brand-brown);
                outline-offset: 1px;
            }

            .pos-pane {
                display: none;
            }

            .pos-pane.is-active {
                display: block;
            }

            .pos-layout {
                display: flex;
                flex-direction: column;
                align-items: stretch;
                gap: 14px;
            }

            .pos-cart {
                order: 1;
            }

            .pos-pane[data-pos-pane="menu"] {
                order: 2;
            }

            .pos-menu-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .pos-menu-card {
                min-height: 96px;
            }
        }

        @media (min-width: 900px) {
            .pos-pane {
                display: block !important;
            }
            .pos-mobile-switcher {
                display: none !important;
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

        @media (min-width: 900px) and (max-width: 1099px) {
            .pos-cart {
                top: 12px;
            }
        }

        /* Penyesuaian mobile-first (ditempatkan terakhir agar menang cascade) */
        @media (max-width: 1099px) {
            .shell-main main {
                padding: 16px 20px calc(28px + env(safe-area-inset-bottom));
            }

            .card,
            .order-card,
            .order-hero,
            .cart,
            .new-order,
            .receipt,
            .stock-history-card {
                border-color: var(--border-warm);
                background: var(--surface-warm);
                box-shadow: none;
            }

            .order-card {
                padding: 16px;
                border-left-width: 1px;
                border-radius: 14px;
                gap: 8px;
            }

            .order-grid {
                grid-template-columns: 1fr;
            }

            .page-head {
                margin-bottom: 14px;
            }

            .page-head h1 {
                font-family: var(--font-serif);
                font-size: 1.55rem;
                letter-spacing: -.01em;
            }

            .chips {
                margin-bottom: 12px;
            }

            .chip {
                border-color: var(--border-warm);
                background: var(--surface-warm);
                color: #72645d;
            }

            .chip.active {
                border-color: #e0b747;
                background: #e0b747;
                color: var(--brand-brown);
                font-weight: 700;
            }

            .btn {
                border-radius: 10px;
            }

            .btn-ghost {
                border-color: var(--border-warm);
                background: var(--surface-warm);
            }

            .btn-ghost:hover {
                background: #f2e9d8;
            }

            .order-card-actions {
                gap: 8px;
            }

            .order-card-actions .btn {
                flex: 1 1 45%;
                min-height: 44px;
            }

            .order-card-actions form {
                display: contents;
            }

            .status {
                white-space: nowrap;
            }

            .new-order {
                padding: 16px;
            }

            .pos-cart {
                padding: 16px;
            }

            .receipt {
                padding: 18px;
                border-radius: 12px;
            }

            .stat-label {
                font-size: 12px;
            }
        }

        /* ---------- Polish: menu, stok, staff ---------- */

        /* Ringkasan kecil di atas daftar */
        .summary-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 0 0 16px;
        }

        .summary-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--surface);
            color: var(--muted);
            font-size: 12.5px;
            line-height: 1.2;
        }

        .summary-pill strong {
            color: var(--text);
            font-size: 15px;
            font-weight: 700;
        }

        .summary-pill.is-good {
            border-color: #cfe6cf;
            background: #f2f9f1;
        }

        .summary-pill.is-good strong {
            color: #15803d;
        }

        .summary-pill.is-warn {
            border-color: #f0dcae;
            background: #fdf7e7;
        }

        .summary-pill.is-warn strong {
            color: #a16207;
        }

        /* Kartu menu */
        .menu-card {
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .menu-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(54, 37, 30, .1);
        }

        .menu-card.is-inactive {
            opacity: .68;
        }

        .menu-card.is-inactive:hover {
            opacity: .95;
        }

        .menu-thumb .badge.badge-cat-makanan {
            background: #fbe7c2;
            color: #8a4b12;
        }

        .menu-thumb .badge.badge-cat-minuman {
            background: #d8ece6;
            color: #12604f;
        }

        .menu-thumb .badge.badge-cat-cemilan {
            background: #f1e0d6;
            color: #7a3b23;
        }

        .menu-thumb .menu-status {
            top: 8px;
            right: 8px;
            left: auto;
            background: rgba(255, 253, 247, .94);
        }

        .menu-thumb .menu-status.status-ready {
            color: #166534;
        }

        .menu-thumb .menu-status.status-completed {
            color: #6b7280;
        }

        /* Kartu staff */
        .staff-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 14px;
        }

        .staff-card {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--surface);
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .staff-card.is-inactive {
            border-style: dashed;
            background: #fdfaf2;
        }

        .staff-card-head {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .staff-avatar {
            display: grid;
            flex: 0 0 auto;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--brand-brown);
            color: #f9f4e6;
            font-family: var(--font-serif);
            font-size: 15px;
            font-weight: 600;
            letter-spacing: .02em;
            place-items: center;
        }

        .staff-identity {
            min-width: 0;
        }

        .staff-identity h2 {
            margin: 0;
            overflow: hidden;
            font-size: 1rem;
            line-height: 1.3;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .staff-identity p {
            margin: 2px 0 0;
            overflow: hidden;
            color: var(--muted);
            font-size: 12.5px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .staff-card-head .status {
            flex: 0 0 auto;
            margin-left: auto;
        }

        .staff-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 12px;
            margin: 0;
            padding-top: 12px;
            border-top: 1px dashed var(--border);
        }

        .staff-meta dt {
            color: var(--muted);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        .staff-meta dd {
            margin: 2px 0 0;
            overflow-wrap: anywhere;
            font-size: 13px;
        }

        .staff-actions {
            display: flex;
            gap: 6px;
            margin-top: auto;
        }

        .staff-actions .btn {
            flex: 1 1 0;
            justify-content: center;
        }

        .staff-actions form {
            display: flex;
            flex: 1 1 0;
            margin: 0;
        }

        .staff-actions form .btn {
            width: 100%;
        }

        .staff-pending {
            display: flex;
            gap: 6px;
            margin-top: auto;
        }

        .staff-pending select {
            flex: 1 1 auto;
            min-width: 0;
        }

        .staff-pending .btn {
            flex: 0 0 auto;
        }

        @media (max-width: 520px) {
            .staff-meta {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        /* ---------- Styling Modern Stok (Owner & Staff) ---------- */

        /* Grid ringkasan stok */
        .stock-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin: 0 0 16px;
        }

        .stock-stat-card {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 12px 14px;
            border: 1px solid var(--border-warm);
            border-radius: 12px;
            background: var(--surface-warm);
            box-shadow: 0 1px 3px rgba(54, 37, 30, .04);
            transition: transform .15s ease;
        }

        .stock-stat-num {
            font-size: 1.35rem;
            font-weight: 700;
            line-height: 1.1;
            color: var(--brand-brown);
        }

        .stock-stat-label {
            color: var(--muted-warm);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .stock-stat-card.stat-safe {
            border-color: #cfe6cf;
            background: #f5faf4;
        }
        .stock-stat-card.stat-safe .stock-stat-num {
            color: #1e7039;
        }

        .stock-stat-card.stat-warn {
            border-color: #f3dc98;
            background: #fdf9ee;
        }
        .stock-stat-card.stat-warn .stock-stat-num {
            color: #925807;
        }

        .stock-stat-card.stat-danger {
            border-color: #f6cfc9;
            background: #fdf4f2;
        }
        .stock-stat-card.stat-danger .stock-stat-num {
            color: #b43020;
        }

        @media (max-width: 639px) {
            .stock-stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
            }
            .stock-stat-card {
                padding: 10px 12px;
            }
            .stock-stat-num {
                font-size: 1.2rem;
            }
        }

        /* Toolbar filter & search */
        .stock-filter-toolbar {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: 0 0 18px;
        }

        @media (min-width: 640px) {
            .stock-filter-toolbar {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .stock-search-wrap {
            position: relative;
            display: flex;
            align-items: center;
            flex: 0 0 auto;
            min-width: 0;
            background: var(--surface-warm);
            border: 1px solid var(--border-warm);
            border-radius: 10px;
            padding: 0 12px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        @media (min-width: 640px) {
            .stock-search-wrap {
                flex: 1 1 260px;
            }
        }

        .stock-search-wrap:focus-within {
            border-color: var(--brand-amber);
            box-shadow: 0 0 0 3px rgba(224, 183, 71, .2);
        }

        .stock-search-wrap svg {
            width: 17px;
            height: 17px;
            color: var(--muted-warm);
            flex: 0 0 auto;
            margin-right: 8px;
        }

        .stock-search-wrap input {
            flex: 1 1 auto;
            min-width: 0;
            border: 0;
            background: transparent;
            color: var(--text-warm);
            font: inherit;
            font-size: 13.5px;
            padding: 10px 0;
            outline: none;
        }

        .stock-search-clear {
            border: 0;
            background: transparent;
            color: var(--muted-warm);
            font-size: 18px;
            line-height: 1;
            padding: 4px;
            cursor: pointer;
        }

        .stock-filter-pills {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            scrollbar-width: none;
            padding-bottom: 2px;
        }

        .stock-filter-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 13px;
            border-radius: 999px;
            border: 1px solid var(--border-warm);
            background: var(--surface-warm);
            color: var(--muted-warm);
            font: inherit;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all .15s ease;
        }

        .stock-filter-btn:hover {
            border-color: var(--brand-amber);
            color: var(--brand-brown);
        }

        .stock-filter-btn.active {
            background: var(--brand-brown);
            border-color: var(--brand-brown);
            color: #fff9ef;
        }

        .stock-filter-btn.btn-warn.active {
            background: #925807;
            border-color: #925807;
            color: #fff;
        }

        .stock-filter-btn.btn-danger.active {
            background: #b43020;
            border-color: #b43020;
            color: #fff;
        }

        .stock-empty-note {
            display: none;
            margin: 0 0 16px;
            padding: 20px 16px;
            border: 1px dashed var(--border-warm);
            border-radius: 12px;
            background: var(--surface-warm);
            color: var(--muted-warm);
            font-size: 13px;
            text-align: center;
        }

        .stock-empty-note.is-visible {
            display: block;
        }

        /* Kelola Stok Menu Group & Card */
        .stock-menu-group {
            margin-bottom: 22px;
        }

        .stock-menu-group.is-hidden {
            display: none !important;
        }

        .stock-menu-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 6px 4px 10px;
            border-bottom: 2px solid var(--border-warm);
            margin-bottom: 12px;
        }

        .stock-menu-title {
            margin: 0;
            font-family: var(--font-serif);
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--brand-brown);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stock-variants-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .stock-item-card {
            padding: 14px 16px;
            border: 1px solid var(--border-warm);
            border-radius: 12px;
            background: var(--surface-warm);
            box-shadow: 0 1px 3px rgba(54, 37, 30, .03);
            transition: border-color .15s ease, background-color .15s ease;
        }

        .stock-item-card:hover {
            border-color: #d6c4aa;
        }

        .stock-item-card.is-hidden {
            display: none !important;
        }

        .stock-item-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .stock-item-info {
            flex: 1 1 auto;
            min-width: 0;
        }

        .stock-item-title-row {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: 6px 10px;
        }

        .stock-item-name {
            margin: 0;
            font-size: 14.5px;
            font-weight: 700;
            color: var(--brand-brown);
            line-height: 1.25;
        }

        .stock-item-price {
            color: var(--muted-warm);
            font-size: 12.5px;
            font-weight: 500;
        }

        .stock-item-badge-wrap {
            flex: 0 0 auto;
        }

        .stock-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
        }

        .stock-badge.badge-safe {
            background: #edf7ed;
            color: #1e7039;
            border: 1px solid #c7e5cb;
        }

        .stock-badge.badge-low {
            background: #fdf8eb;
            color: #925807;
            border: 1px solid #f3dc98;
        }

        .stock-badge.badge-out {
            background: #fdf3f2;
            color: #b43020;
            border: 1px solid #f6cfc9;
        }

        /* Form penyesuaian stok */
        .stock-adjust-form {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding-top: 10px;
            border-top: 1px dashed var(--border-warm);
        }

        .stock-form-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 8px 12px;
        }

        /* Segmented control mode */
        .stock-mode-selector {
            display: inline-flex;
            background: var(--bg-warm);
            padding: 3px;
            border-radius: 8px;
            border: 1px solid var(--border-warm);
            gap: 2px;
        }

        .stock-mode-radio {
            position: relative;
            cursor: pointer;
            margin: 0;
        }

        .stock-mode-radio input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .stock-mode-radio span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 11px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--muted-warm);
            line-height: 1;
            user-select: none;
            transition: all .15s ease;
        }

        .stock-mode-radio input:checked + span {
            background: var(--surface-warm);
            color: var(--brand-brown);
            font-weight: 700;
            box-shadow: 0 1px 3px rgba(54, 37, 30, .1);
        }

        .stock-mode-radio.mode-out input:checked + span {
            color: #b43020;
        }

        .stock-mode-radio.mode-in input:checked + span {
            color: #1e7039;
        }

        /* Stepper actions */
        .stock-input-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stock-stepper {
            display: inline-flex;
            align-items: stretch;
            border: 1px solid var(--border-warm);
            border-radius: 8px;
            background: var(--surface-warm);
            overflow: hidden;
        }

        .stepper-btn {
            display: grid;
            width: 38px;
            height: 38px;
            padding: 0;
            border: 0;
            background: var(--bg-warm);
            color: var(--brand-brown);
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            place-items: center;
            user-select: none;
            transition: background-color .15s ease;
        }

        .stepper-btn:hover {
            background: #ebdfcd;
        }

        .stepper-btn:active {
            background: #decbb2;
        }

        .stepper-input {
            width: 58px;
            height: 38px;
            padding: 0 4px;
            border: 0;
            border-left: 1px solid var(--border-warm);
            border-right: 1px solid var(--border-warm);
            background: transparent;
            color: var(--text-warm);
            font: inherit;
            font-size: 14px;
            font-weight: 700;
            text-align: center;
            outline: none;
            -moz-appearance: textfield;
        }

        .stepper-input::-webkit-outer-spin-button,
        .stepper-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .btn-stock-submit {
            min-height: 38px;
            padding: 8px 16px;
            border: 0;
            border-radius: 8px;
            background: var(--brand-brown);
            color: #fff9ef;
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: background .15s ease;
        }

        .btn-stock-submit:hover {
            background: var(--brand-brown-light);
        }

        .stock-note-row {
            margin-top: 4px;
        }

        .stock-note-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border-warm);
            border-radius: 8px;
            background: var(--bg-warm);
            color: var(--text-warm);
            font: inherit;
            font-size: 12.5px;
            outline: none;
            transition: border-color .15s ease;
        }

        .stock-note-input:focus {
            border-color: var(--brand-amber);
            background: var(--surface-warm);
        }

        @media (max-width: 639px) {
            .stock-item-card {
                padding: 12px 14px;
            }

            .stock-form-controls {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }

            .stock-mode-selector {
                width: 100%;
                display: flex;
            }

            .stock-mode-radio {
                flex: 1 1 0;
            }

            .stock-mode-radio span {
                width: 100%;
                padding: 7px 4px;
                font-size: 12px;
            }

            .stock-input-actions {
                width: 100%;
                justify-content: space-between;
            }

            .stock-stepper {
                flex: 1 1 auto;
                justify-content: space-between;
                min-width: 0;
            }

            .stepper-btn {
                flex: 0 0 44px;
                width: 44px;
            }

            .stepper-input {
                flex: 1 1 auto;
                width: 100%;
            }

            .btn-stock-submit {
                flex: 0 0 88px;
            }
        }

        /* Kartu staff */
        .staff-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 14px;
        }

        .staff-card {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--surface);
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .staff-card.is-inactive {
            border-style: dashed;
            background: #fdfaf2;
        }

        .staff-card-head {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .staff-avatar {
            display: grid;
            flex: 0 0 auto;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--brand-brown);
            color: #f9f4e6;
            font-family: var(--font-serif);
            font-size: 15px;
            font-weight: 600;
            letter-spacing: .02em;
            place-items: center;
        }

        .staff-identity {
            min-width: 0;
        }

        .staff-identity h2 {
            margin: 0;
            overflow: hidden;
            font-size: 1rem;
            line-height: 1.3;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .staff-identity p {
            margin: 2px 0 0;
            overflow: hidden;
            color: var(--muted);
            font-size: 12.5px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .staff-card-head .status {
            flex: 0 0 auto;
            margin-left: auto;
        }

        .staff-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 12px;
            margin: 0;
            padding-top: 12px;
            border-top: 1px dashed var(--border);
        }

        .staff-meta dt {
            color: var(--muted);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        .staff-meta dd {
            margin: 2px 0 0;
            overflow-wrap: anywhere;
            font-size: 13px;
        }

        .staff-actions {
            display: flex;
            gap: 6px;
            margin-top: auto;
        }

        .staff-actions .btn {
            flex: 1 1 0;
            justify-content: center;
        }

        .staff-actions form {
            display: flex;
            flex: 1 1 0;
            margin: 0;
        }

        .staff-actions form .btn {
            width: 100%;
        }

        .staff-pending {
            display: flex;
            gap: 6px;
            margin-top: auto;
        }

        .staff-pending select {
            flex: 1 1 auto;
            min-width: 0;
        }

        .staff-pending .btn {
            flex: 0 0 auto;
        }

        @media (max-width: 520px) {
            .staff-meta {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        /* Mobile comfort: readable inputs, friendly tap targets, safe text wrapping. */
        @media (max-width: 767px) {
            input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
            select:not([hidden]),
            textarea {
                font-size: 16px;
            }

            .sidebar-close-btn,
            .sidebar-logout button,
            .mobile-menu-btn,
            .mobile-account-link,
            .mobile-logout,
            .mobile-tab,
            .dash-recent-link {
                display: inline-flex;
                min-width: 44px;
                min-height: 44px;
                align-items: center;
                justify-content: center;
            }

            .chip,
            .btn-sm,
            .pager a,
            .pager button,
            .stock-filter-btn,
            .menu-type-tab-btn,
            .type-shortcut-btn,
            .stock-mode-radio span,
            .stepper-btn,
            .stepper-input,
            .btn-stock-submit,
            .stock-search-clear,
            .check,
            details > summary {
                min-height: 44px;
            }

            .chip,
            .stock-filter-btn,
            .menu-type-tab-btn,
            .type-shortcut-btn {
                display: inline-flex;
                align-items: center;
            }

            .pager {
                flex-wrap: wrap;
                justify-content: center;
                row-gap: 8px;
            }

            .pager > div {
                flex: 1 1 120px;
                text-align: center;
            }

            .activity-card-subject,
            .activity-card-desc,
            .activity-changes-box,
            .activity-card-footer,
            .receipt-line > :last-child,
            .receipt-item-name,
            .receipt-item-variant,
            .receipt-meta,
            .receipt-notes-content {
                overflow-wrap: anywhere;
            }

            .receipt-line > :last-child,
            .receipt-meta {
                white-space: normal;
            }

            .chips,
            .stock-filter-pills,
            .menu-type-tabs {
                scrollbar-width: thin;
            }

            .stock-menu-title,
            .stock-item-title-row {
                flex-wrap: wrap;
                row-gap: 6px;
            }

            .stock-menu-title > span:first-child,
            .stock-item-name,
            .stock-item-price {
                min-width: 0;
                overflow-wrap: anywhere;
            }

            .stock-item-badge-wrap {
                margin-left: auto;
            }

            .stock-menu-header {
                flex-wrap: wrap;
            }

            .receipt-brand-title,
            .receipt-item-total {
                overflow-wrap: anywhere;
            }

            .receipt-item-total {
                text-align: right;
            }

            .receipt-item-row {
                gap: 8px;
            }

            .variant-row input,
            .variant-row .check,
            .variant-row .btn,
            .variant-row-add .btn {
                min-height: 44px;
            }
        }

        @media (max-width: 359px) {
            .staff-identity p {
                overflow-wrap: anywhere;
                text-overflow: clip;
                white-space: normal;
            }
        }

        @media print {
            .sidebar,
            .topbar,
            .mobile-shell,
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
                @include('partials.mobile-header')

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
