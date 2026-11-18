@php
    $user = auth()->user();
    $navigation = \App\Support\Navigation::for($user);
    $words = preg_split('/\s+/', trim($user->name)) ?: [];
    $initials = collect($words)->filter()->take(2)->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))->implode('');
@endphp

<aside class="sidebar" id="app-sidebar" aria-label="Navigasi utama">
    <div class="sidebar-brand">
        <img
            src="{{ asset('img/logo-mark.png') }}"
            alt=""
            class="sidebar-logo"
            width="52"
            height="52"
            decoding="async"
        >
        <span class="sidebar-name">{{ config('app.name', 'Jagung Bakar WJT') }}</span>
        <span class="sidebar-tagline">Ruang administrasi</span>
    </div>

    <nav class="sidebar-nav" aria-label="Menu utama">
        <span class="sidebar-group">Ruang admin</span>

        @foreach ($navigation['main'] as $item)
            <a
                href="{{ route($item['route']) }}"
                class="sidebar-link {{ request()->routeIs($item['active']) ? 'active' : '' }}"
                @if (request()->routeIs($item['active'])) aria-current="page" @endif
            >
                @include('partials.nav-icon', ['name' => $item['icon']])
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="sidebar-bottom">
        <nav class="sidebar-secondary" aria-label="Menu lainnya">
            @foreach ($navigation['secondary'] as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="sidebar-link {{ request()->routeIs($item['active']) ? 'active' : '' }}"
                    @if (request()->routeIs($item['active'])) aria-current="page" @endif
                >
                    @include('partials.nav-icon', ['name' => $item['icon']])
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        @unless ($user->isOwner())
            <button type="button" class="sidebar-link" data-dialog-open="pusat-bantuan">
                @include('partials.nav-icon', ['name' => 'help'])
                <span>Pusat bantuan</span>
            </button>
        @endunless

        <hr class="sidebar-divider">

        <div class="sidebar-user">
            @if ($user->avatarUrl())
                <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="sidebar-avatar-img">
            @else
                <span class="sidebar-avatar" aria-hidden="true">{{ $user->initials() }}</span>
            @endif
            <span class="sidebar-user-text">
                <span class="sidebar-user-name">{{ $user->name }}</span>
                <span class="sidebar-user-role">{{ $user->isOwner() ? 'Owner' : 'Staff' }}</span>
            </span>

            <form method="POST" action="{{ route('logout') }}" class="sidebar-logout">
                @csrf
                <button type="submit" aria-label="Keluar dari akun" title="Keluar">
                    @include('partials.nav-icon', ['name' => 'logout'])
                </button>
            </form>
        </div>
    </div>
</aside>

@unless ($user->isOwner())
    <dialog id="pusat-bantuan" class="auth-dialog" aria-labelledby="pusat-bantuan-title">
        <h2 id="pusat-bantuan-title">Pusat bantuan</h2>
        <p>
            Butuh bantuan akun, stok, atau pesanan? Hubungi owner warung. Owner bisa mengatur akun
            staff, mengoreksi stok, dan membuka akses lewat menu <strong>Pengaturan</strong>.
        </p>

        <form method="dialog" class="form-actions">
            <button type="submit" class="btn">Mengerti</button>
        </form>
    </dialog>
@endunless
