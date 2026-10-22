<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#b45309">
    <title>@yield('title', 'Hotmad')</title>
    <style>
        :root {
            --brand: #b45309;
            --brand-dark: #92400e;
            --bg: #f6f5f2;
            --surface: #fff;
            --border: #e5e0d8;
            --text: #1f2937;
            --muted: #6b7280;
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

        header {
            position: sticky;
            top: 0;
            z-index: 20;
            background: var(--brand);
            color: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .15);
        }

        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 10px 16px;
            padding-top: calc(10px + env(safe-area-inset-top));
        }

        .brand {
            font-size: 16px;
            font-weight: 700;
            white-space: nowrap;
        }

        .header-user {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 1px;
            font-size: 13px;
            line-height: 1.3;
            text-align: right;
        }

        .header-user .role {
            font-size: 11px;
            letter-spacing: .04em;
            text-transform: uppercase;
            opacity: .85;
        }

        .desktop-nav {
            display: none;
            align-items: center;
            gap: 8px;
        }

        .desktop-nav a,
        .desktop-nav button {
            display: inline-flex;
            align-items: center;
            min-height: 36px;
            padding: 6px 12px;
            border: 1px solid transparent;
            border-radius: 8px;
            background: transparent;
            color: #fff;
            font: inherit;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
        }

        .desktop-nav a.active {
            background: rgba(255, 255, 255, .18);
        }

        .desktop-nav button {
            border-color: rgba(255, 255, 255, .55);
        }

        .desktop-nav button:hover {
            background: rgba(255, 255, 255, .12);
        }

        main {
            display: flex;
            flex: 1;
            flex-direction: column;
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 16px;
            padding-bottom: calc(84px + env(safe-area-inset-bottom));
        }

        body.is-guest main {
            padding-bottom: 16px;
        }

        .mobile-nav {
            position: fixed;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 20;
            display: flex;
            gap: 4px;
            padding: 6px 8px;
            padding-bottom: calc(6px + env(safe-area-inset-bottom));
            border-top: 1px solid var(--border);
            background: var(--surface);
            box-shadow: 0 -1px 3px rgba(0, 0, 0, .06);
        }

        .mobile-nav a,
        .mobile-nav form {
            flex: 1;
            margin: 0;
        }

        .mobile-nav a,
        .mobile-nav button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 44px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: var(--muted);
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .mobile-nav a.active {
            background: #fdf2e3;
            color: var(--brand);
        }

        .mobile-nav a:hover,
        .mobile-nav button:hover {
            background: #f5f5f4;
        }

        .page-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
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

        .stats {
            margin-bottom: 16px;
        }

        .stat {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .stat-label {
            color: var(--muted);
            font-size: 13px;
            font-weight: 600;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
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

        .auth-page {
            display: flex;
            flex: 1;
            align-items: center;
            justify-content: center;
            padding: 16px 0;
        }

        .auth-card {
            width: 100%;
            max-width: 400px;
        }

        .login-brand {
            margin-bottom: 16px;
            text-align: center;
        }

        .login-brand h1 {
            font-size: 1.35rem;
        }

        .login-brand p {
            margin: 0;
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

        .auth-footer {
            margin: 16px 0 0;
            color: var(--muted);
            font-size: 14px;
            text-align: center;
        }

        .auth-footer a {
            color: var(--brand);
            font-weight: 600;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
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

            .mobile-nav {
                display: none;
            }

            .desktop-nav {
                display: flex;
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

        .chips {
            display: flex;
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
            min-height: 36px;
            padding: 6px 12px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: #fff;
            color: var(--muted);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
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
    </style>
</head>
<body class="@auth is-auth @else is-guest @endauth">
    @auth
        <header>
            <div class="header-inner">
                <div class="brand">{{ config('app.name', 'Hotmad') }}</div>

                <nav class="desktop-nav">
                    <a href="{{ route(auth()->user()->homeRouteName()) }}" class="{{ request()->routeIs('*.dashboard') ? 'active' : '' }}">Dashboard</a>
                    @if (auth()->user()->isOwner())
                        <a href="{{ route('owner.menus.index') }}" class="{{ request()->routeIs('owner.menus.*') ? 'active' : '' }}">Menu</a>
                    @else
                        <a href="{{ route('staff.menus.index') }}" class="{{ request()->routeIs('staff.menus.*') ? 'active' : '' }}">Menu</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Keluar</button>
                    </form>
                </nav>

                <div class="header-user">
                    <span>{{ auth()->user()->name }}</span>
                    <span class="role">{{ auth()->user()->role }}</span>
                </div>
            </div>
        </header>
    @endauth

    <main>
        @yield('content')
    </main>

    @auth
        <nav class="mobile-nav">
            <a href="{{ route(auth()->user()->homeRouteName()) }}" class="{{ request()->routeIs('*.dashboard') ? 'active' : '' }}">Dashboard</a>
            @if (auth()->user()->isOwner())
                <a href="{{ route('owner.menus.index') }}" class="{{ request()->routeIs('owner.menus.*') ? 'active' : '' }}">Menu</a>
            @else
                <a href="{{ route('staff.menus.index') }}" class="{{ request()->routeIs('staff.menus.*') ? 'active' : '' }}">Menu</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Keluar</button>
            </form>
        </nav>
    @endauth

    <script>
        document.addEventListener('click', function (event) {
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
        });
    </script>

    @stack('scripts')
</body>
</html>
