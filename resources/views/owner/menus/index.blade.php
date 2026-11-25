@extends('layouts.app')

@section('title', 'Kelola Menu - Jagung Bakar WJT')

@section('body_class', 'page-owner-menus')

@push('styles')
    <style>
        @media (max-width: 719px) {
            body.page-owner-menus .menu-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        body.page-owner-menus .menu-title-row h2,
        body.page-owner-menus .menu-desc,
        body.page-owner-menus .topping-note {
            overflow-wrap: anywhere;
        }

        body.page-owner-menus .summary-pill,
        body.page-owner-menus .menu-actions .btn {
            min-height: 44px;
        }

        @media (min-width: 481px) and (max-width: 719px) {
            body.page-owner-menus .menu-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
@endpush

@section('content')
    <div class="page-head">
        <div>
            <h1>Kelola Menu</h1>
            <p class="muted">Atur menu, harga, varian, dan foto.</p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap">
            <a href="{{ route('owner.activity.index') }}" class="btn btn-ghost">Aktivitas</a>
            <a href="{{ route('owner.menus.create') }}" class="btn">+ Tambah Menu</a>
        </div>
    </div>

    @include('partials.alerts')

    <div class="summary-row">
        <span class="summary-pill"><strong>{{ $menus->count() }}</strong> Menu</span>
        <span class="summary-pill is-good"><strong>{{ $menus->where('is_active', true)->count() }}</strong> Aktif</span>
        @if ($menus->contains('is_active', false))
            <span class="summary-pill is-warn">
                <strong>{{ $menus->where('is_active', false)->count() }}</strong> Nonaktif
            </span>
        @endif
    </div>

    <div class="chips">
        <a href="{{ route('owner.menus.index') }}" class="chip {{ $category === null ? 'active' : '' }}">Semua</a>
        @foreach (\App\Models\Menu::CATEGORIES as $item)
            <a
                href="{{ route('owner.menus.index', ['category' => $item]) }}"
                class="chip {{ $category === $item ? 'active' : '' }}"
            >{{ \App\Models\Menu::categoryLabelFor($item) }}</a>
        @endforeach
    </div>

    @if ($menus->isEmpty())
        <div class="card empty">
            <h2>{{ $category ? 'Tidak ada menu di kategori ini' : 'Belum ada menu' }}</h2>
            <p class="muted">
                {{ $category ? 'Coba pilih kategori lain.' : 'Tambahkan menu pertama, lalu atur varian dan fotonya.' }}
            </p>
            @unless ($category)
                <a href="{{ route('owner.menus.create') }}" class="btn">Tambah Menu</a>
            @endunless
        </div>
    @else
        <div class="menu-grid">
            @foreach ($menus as $menu)
                <article class="menu-card {{ $menu->is_active ? '' : 'is-inactive' }}">
                    <div class="menu-thumb">
                        @if ($menu->thumbUrl())
                            <img src="{{ $menu->thumbUrl() }}" alt="{{ $menu->name }}" loading="lazy">
                        @else
                            <div class="menu-placeholder">{{ $menu->categoryEmoji() }}</div>
                        @endif
                        <span class="badge badge-cat-{{ $menu->category }}">{{ $menu->categoryLabel() }}</span>
                        <span class="badge menu-status {{ $menu->is_active ? 'status-ready' : 'status-completed' }}">
                            {{ $menu->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <div class="menu-body">
                        <div class="menu-title-row">
                            <h2>{{ $menu->name }}</h2>
                        </div>

                        @if ($menu->description)
                            <p class="muted menu-desc">{{ $menu->description }}</p>
                        @endif

                        <p class="menu-price">{{ $menu->priceRange() }}</p>
                        <p class="menu-meta">{{ $menu->variants->where('is_active', true)->count() }} varian aktif</p>

                        @if ($menu->hasToppingOption())
                            <span class="topping-note">
                                Bisa tambah topping +Rp {{ number_format($menu->toppingPrice(), 0, ',', '.') }}
                            </span>
                        @endif

                        <div class="menu-actions">
                            <a href="{{ route('owner.menus.edit', $menu) }}" class="btn btn-sm">Edit</a>
                            <form method="POST" action="{{ route('owner.menus.toggle', $menu) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-ghost">
                                    {{ $menu->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
