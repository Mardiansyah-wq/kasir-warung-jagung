@php
    $user = auth()->user();
    $navigation = \App\Support\Navigation::for($user);

    $shortLabels = [
        'Total Penjualan' => 'Penjualan',
        'Dashboard' => 'Ringkasan',
        'Kasir' => 'Kasir',
        'Kelola Akun Pegawai' => 'Pegawai',
        'Kelola Menu' => 'Menu',
        'Kelola Stok' => 'Stok',
        'Riwayat Pesanan' => 'Riwayat',
        'Log Aktivitas' => 'Aktivitas',
        'Laporan' => 'Laporan',
        'Cabang' => 'Cabang',
        'Pengaturan' => 'Pengaturan',
    ];

    // Urutan tab mobile. Sisanya tetap bisa dibuka lewat tombol menu (drawer).
    $order = [
        'owner.dashboard' => 1,
        'staff.dashboard' => 1,
        'owner.reports.index' => 2,
        'staff.pos.index' => 2,
        'owner.orders.index' => 3,
        'staff.orders.index' => 3,
        'owner.menus.index' => 4,
        'staff.menus.index' => 4,
        'owner.stock.index' => 5,
        'staff.stock.index' => 5,
        'owner.staff.index' => 6,
        'staff.activity.index' => 6,
        'owner.activity.index' => 7,
    ];

    $tabs = collect($navigation['main'])
        ->sortBy(fn ($item) => $order[$item['route']] ?? 99)
        ->take(6)
        ->values();

    $storeOpen = $user->isOwner()
        ? \App\Models\Branch::query()->where('is_active', true)->exists()
        : (bool) $user->branch?->is_active;
@endphp

<div class="mobile-shell">
    <div class="mobile-brand">
        <div class="mobile-brand-row">
            <div class="mobile-brand-left">
                <button
                    type="button"
                    class="mobile-menu-btn"
                    data-drawer-open
                    aria-controls="app-sidebar"
                    aria-expanded="false"
                    aria-label="Buka menu navigasi"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                </button>
                <div class="mobile-brand-title-wrap">
                    <strong class="mobile-brand-name">{{ config('app.name', 'Jagung Bakar WJT') }}</strong>
                    <span class="mobile-brand-role">{{ $user->isOwner() ? 'Owner' : ($user->branch?->name ?? 'Staff') }}</span>
                </div>
            </div>
            <span class="mobile-open {{ $storeOpen ? '' : 'closed' }}">{{ $storeOpen ? 'Buka' : 'Tutup' }}</span>
        </div>
        <div class="mobile-meta">
            <p class="mobile-brand-sub">
                {{ now()->locale('id')->translatedFormat('l, j F Y') }} · {{ $user->name }}
            </p>
            <div class="mobile-account">
                <a
                    href="{{ route('account.index') }}"
                    class="mobile-account-link {{ request()->routeIs('account.*') ? 'active' : '' }}"
                >
                    <span class="mobile-avatar" aria-hidden="true">
                        @if ($user->avatarUrl())
                            <img src="{{ $user->avatarUrl() }}" alt="">
                        @else
                            @include('partials.nav-icon', ['name' => 'user'])
                        @endif
                    </span>
                    <span>Profil</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mobile-logout" aria-label="Keluar dari akun" title="Keluar">
                        @include('partials.nav-icon', ['name' => 'logout'])
                    </button>
                </form>
            </div>
        </div>
    </div>

    <nav class="mobile-nav" aria-label="Navigasi mobile">
        @foreach ($tabs as $item)
            <a
                href="{{ route($item['route']) }}"
                class="mobile-tab {{ request()->routeIs($item['active']) ? 'active' : '' }}"
                @if (request()->routeIs($item['active'])) aria-current="page" @endif
            >{{ $shortLabels[$item['label']] ?? $item['label'] }}</a>
        @endforeach
    </nav>
</div>
