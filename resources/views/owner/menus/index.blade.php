@extends('layouts.app')

@section('title', 'Kelola Menu - Hotmad')

@section('content')
    <div class="page-head">
        <div>
            <h1>Kelola Menu</h1>
            <p class="muted">Atur menu, harga, varian, dan foto.</p>
        </div>
        <a href="{{ route('owner.menus.create') }}" class="btn">+ Tambah Menu</a>
    </div>

    @include('partials.alerts')

    <div class="chips">
        <a href="{{ route('owner.menus.index') }}" class="chip {{ $category === null ? 'active' : '' }}">Semua</a>
        @foreach (\App\Models\Menu::CATEGORIES as $item)
            <a
                href="{{ route('owner.menus.index', ['category' => $item]) }}"
                class="chip {{ $category === $item ? 'active' : '' }}"
            >{{ ucfirst($item) }}</a>
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
                <article class="menu-card">
                    <div class="menu-thumb">
                        @if ($menu->thumbUrl())
                            <img src="{{ $menu->thumbUrl() }}" alt="{{ $menu->name }}" loading="lazy">
                        @else
                            <div class="menu-placeholder">{{ $menu->categoryEmoji() }}</div>
                        @endif
                        <span class="badge">{{ $menu->categoryLabel() }}</span>
                    </div>

                    <div class="menu-body">
                        <div class="menu-title-row">
                            <h2>{{ $menu->name }}</h2>
                            @unless ($menu->is_active)
                                <span class="badge badge-muted">Nonaktif</span>
                            @endunless
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
