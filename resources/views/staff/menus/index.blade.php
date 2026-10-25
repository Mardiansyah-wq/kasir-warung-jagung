@extends('layouts.app')

@section('title', 'Menu - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Menu</h1>
            <p class="muted">{{ $branch?->name ?? 'Cabang belum ditentukan' }}</p>
        </div>
        <span class="badge">Staff</span>
    </div>

    <div class="chips">
        <a href="{{ route('staff.menus.index') }}" class="chip {{ $category === null ? 'active' : '' }}">Semua</a>
        @foreach (\App\Models\Menu::CATEGORIES as $item)
            <a
                href="{{ route('staff.menus.index', ['category' => $item]) }}"
                class="chip {{ $category === $item ? 'active' : '' }}"
            >{{ \App\Models\Menu::categoryLabelFor($item) }}</a>
        @endforeach
    </div>

    <p class="hint" style="margin-bottom: 12px">Ketuk menu untuk melihat varian dan stok.</p>

    @if ($menus->isEmpty())
        <div class="card empty">
            <h2>{{ $category ? 'Tidak ada menu di kategori ini' : 'Belum ada menu aktif' }}</h2>
            <p class="muted">
                {{ $category ? 'Coba pilih kategori lain.' : 'Menu akan muncul setelah owner menambahkannya.' }}
            </p>
        </div>
    @else
        <div class="menu-grid">
            @foreach ($menus as $menu)
                @php
                    $variants = $menu->variants->map(fn ($variant) => [
                        'name' => $variant->variant_name,
                        'price' => (float) $variant->selling_price,
                        'stock' => $variant->stockFor($branch?->id),
                    ])->values();
                @endphp

                <article
                    class="menu-card menu-card-button"
                    data-menu-card
                    role="button"
                    tabindex="0"
                    aria-label="Lihat varian {{ $menu->name }}"
                    data-menu-name="{{ $menu->name }}"
                    data-menu-category="{{ $menu->categoryLabel() }}"
                    data-menu-topping="{{ $menu->hasToppingOption() ? $menu->toppingPrice() : 0 }}"
                    data-menu-variants="{{ json_encode($variants) }}"
                >
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
                        </div>

                        @if ($menu->description)
                            <p class="muted menu-desc">{{ $menu->description }}</p>
                        @endif

                        <p class="menu-price">{{ $menu->priceRange() }}</p>
                        <p class="menu-meta">{{ $menu->variants->count() }} varian</p>

                        @if ($menu->hasToppingOption())
                            <span class="topping-note">
                                Bisa tambah topping +Rp {{ number_format($menu->toppingPrice(), 0, ',', '.') }}
                            </span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    <dialog id="variant-dialog" class="sheet" aria-labelledby="sheet-title">
        <div class="sheet-handle"></div>

        <div class="sheet-head">
            <div>
                <h2 id="sheet-title"></h2>
                <p class="muted" id="sheet-category"></p>
            </div>
            <button type="button" class="sheet-close" data-sheet-close aria-label="Tutup">×</button>
        </div>

        <div id="sheet-variants"></div>
        <p class="topping-note" id="sheet-topping" hidden></p>

        <button type="button" class="btn btn-block btn-ghost" data-sheet-close style="margin-top: 12px">Tutup</button>
    </dialog>
@endsection

@push('scripts')
    <script>
        (function () {
            const dialog = document.getElementById('variant-dialog');
            const title = document.getElementById('sheet-title');
            const category = document.getElementById('sheet-category');
            const list = document.getElementById('sheet-variants');
            const topping = document.getElementById('sheet-topping');
            const rupiah = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0,
            });

            function openDialog() {
                if (typeof dialog.showModal === 'function') {
                    dialog.showModal();
                } else {
                    dialog.setAttribute('open', '');
                }
            }

            function closeDialog() {
                if (typeof dialog.close === 'function') {
                    dialog.close();
                } else {
                    dialog.removeAttribute('open');
                }
            }

            document.querySelectorAll('[data-menu-card]').forEach(function (card) {
                card.addEventListener('click', function () {
                    let variants = [];

                    try {
                        variants = JSON.parse(card.dataset.menuVariants || '[]');
                    } catch (error) {
                        variants = [];
                    }

                    title.textContent = card.dataset.menuName;
                    category.textContent = card.dataset.menuCategory;

                    list.innerHTML = '';

                    if (variants.length === 0) {
                        const empty = document.createElement('p');
                        empty.className = 'muted';
                        empty.textContent = 'Belum ada varian aktif.';
                        list.appendChild(empty);
                    }

                    variants.forEach(function (variant) {
                        const row = document.createElement('div');
                        row.className = 'variant-item';

                        const name = document.createElement('div');
                        name.className = 'variant-name';
                        name.textContent = variant.name;

                        const meta = document.createElement('div');
                        meta.className = 'variant-meta';

                        const price = document.createElement('strong');
                        price.textContent = rupiah.format(Number(variant.price) || 0);

                        const stock = document.createElement('span');
                        stock.className = 'muted';
                        stock.textContent = 'Stok: ' + (Number(variant.stock) || 0);

                        meta.append(price, stock);
                        row.append(name, meta);
                        list.appendChild(row);
                    });

                    const toppingPrice = Number(card.dataset.menuTopping) || 0;

                    topping.hidden = toppingPrice <= 0;
                    topping.textContent = toppingPrice > 0
                        ? 'Bisa tambah topping +' + rupiah.format(toppingPrice)
                        : '';

                    openDialog();
                });

                card.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        card.click();
                    }
                });
            });

            document.querySelectorAll('[data-sheet-close]').forEach(function (button) {
                button.addEventListener('click', function () {
                    closeDialog();
                });
            });

            dialog.addEventListener('click', function (event) {
                if (event.target === dialog) {
                    closeDialog();
                }
            });
        })();
    </script>
@endpush
