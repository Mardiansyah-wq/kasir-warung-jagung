<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Kasir Warung Jagung')</title>
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            background: #f6f5f2;
            color: #222;
        }

        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding: 12px 24px;
            background: #b45309;
            color: #fff;
        }

        header .brand {
            font-weight: 700;
        }

        header .user {
            font-size: 14px;
            opacity: .95;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav form {
            margin: 0;
        }

        .nav button {
            padding: 6px 10px;
            border: 1px solid rgba(255, 255, 255, .6);
            border-radius: 8px;
            background: transparent;
            color: #fff;
            font: inherit;
            font-size: 13px;
            cursor: pointer;
        }

        .nav button:hover {
            background: rgba(255, 255, 255, .15);
        }

        main {
            max-width: 960px;
            margin: 32px auto;
            padding: 0 16px;
        }

        .card {
            padding: 24px;
            border: 1px solid #e5e0d8;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .auth {
            max-width: 380px;
            margin: 8vh auto 0;
        }

        h1 {
            margin-top: 0;
        }

        label {
            display: block;
            margin-bottom: 14px;
            font-size: 14px;
            font-weight: 600;
        }

        input {
            width: 100%;
            margin-top: 6px;
            padding: 10px 12px;
            border: 1px solid #cfc7bb;
            border-radius: 8px;
            font: inherit;
        }

        button[type="submit"] {
            padding: 10px 16px;
            border: 0;
            border-radius: 8px;
            background: #b45309;
            color: #fff;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background: #92400e;
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

        .muted {
            color: #6b7280;
            font-size: 14px;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            background: #f3e8d3;
            color: #92400e;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    @auth
        <header>
            <div class="brand">Warung Jagung Bakar</div>
            <div class="nav">
                <span class="user">{{ auth()->user()->name }} &middot; {{ auth()->user()->role }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Keluar</button>
                </form>
            </div>
        </header>
    @endauth

    <main>
        @yield('content')
    </main>
</body>
</html>
