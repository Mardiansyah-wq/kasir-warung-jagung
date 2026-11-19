@php
    $user = auth()->user();
    $navigation = \App\Support\Navigation::for($user);

    $shortLabels = [
        'Total Penjualan' => 'Penjualan',
        'Dashboard' => 'Penjualan',
        'Kelola Akun Pegawai' => 'Pegawai',
        'Kelola Menu' => 'Menu',
        'Kelola Stok' => 'Stok',
        'Riwayat Pesanan' => 'Riwayat',
        'Log Aktivitas' => 'Aktivitas',
    ];

    $order = [
        'owner.dashboard' => 1,
        'staff.dashboard' => 1,
        'owner.staff.index' => 2,
        'owner.menus.index' => 3,
        'staff.menus.index' => 3,
        'owner.stock.index' => 4,
        'staff.stock.index' => 4,
        'owner.reports.index' => 5,
        'staff.pos.index' => 5,
        'owner.orders.index' => 6,
        'staff.orders.index' => 6,
        'staff.activity.index' => 7,
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
            <strong class="mobile-brand-name">{{ config('app.name', 'Jagung Bakar WJT') }}</strong>
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
                >Akun</a>
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
