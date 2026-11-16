@php
    $user = auth()->user();
    $storeOpen = $user->isOwner()
        ? \App\Models\Branch::query()->where('is_active', true)->exists()
        : (bool) $user->branch?->is_active;
@endphp

<header class="topbar">
    <button
        type="button"
        class="topbar-menu"
        data-drawer-open
        aria-controls="app-sidebar"
        aria-expanded="false"
        aria-label="Buka menu navigasi"
    >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
            <path d="M4 7h16M4 12h16M4 17h16"/>
        </svg>
    </button>

    <nav class="crumb" aria-label="Breadcrumb">
        <span class="crumb-root">{{ config('app.name', 'Jagung Bakar WJT') }}</span>
        <span class="crumb-sep" aria-hidden="true">/</span>
        <span class="crumb-current" aria-current="page">{{ \App\Support\Navigation::currentLabel($user) }}</span>
    </nav>

    <div class="topbar-right">
        <span class="store-pill {{ $storeOpen ? '' : 'closed' }}">
            {{ $storeOpen ? 'Kedai buka' : 'Kedai tutup' }}
        </span>
        <span class="topbar-date">{{ now()->locale('id')->translatedFormat('l, j F Y') }}</span>
    </div>
</header>
