@extends('layouts.app')

@section('title', 'Kelola Stok - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Kelola Stok</h1>
            <p class="muted">
                Tambah, kurangi, atau koreksi stok per cabang. Semua perubahan tercatat di log aktivitas.
            </p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center">
            <a href="{{ route('owner.activity.index', ['action' => 'stock']) }}" class="btn btn-ghost">Log Stok</a>
            <span class="badge">Owner</span>
        </div>
    </div>

    @include('partials.alerts')

    @if ($branches->isEmpty())
        <div class="card empty">
            <h2>Belum ada cabang</h2>
            <p class="muted">Tambahkan cabang terlebih dahulu sebelum mengelola stok.</p>
            <a href="{{ route('owner.branches.create') }}" class="btn">+ Tambah Cabang</a>
        </div>
    @else
        @if ($branches->count() > 1)
            <form method="GET" action="{{ route('owner.stock.index') }}" class="filter-bar" style="margin-bottom: 16px">
                <div class="field">
                    <label for="branch_id">Cabang</label>
                    <select id="branch_id" name="branch_id" onchange="this.form.submit()">
                        @foreach ($branches as $option)
                            <option value="{{ $option->id }}" @selected($branch?->id === $option->id)>
                                {{ $option->name }}@unless ($option->is_active) (nonaktif)@endunless
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn filter-submit">Tampilkan</button>
            </form>
        @endif

        @if (! $branch)
            <div class="card empty">
                <p class="muted" style="margin: 0">Pilih cabang untuk mulai mengelola stok.</p>
            </div>
        @elseif ($menus->isEmpty())
            <div class="card empty">
                <h2>Belum ada menu aktif</h2>
                <p class="muted">Aktifkan menu dan varian terlebih dahulu agar stoknya bisa dikelola.</p>
            </div>
        @else
            @php
                $totalVariants = 0;
                $outOfStock = 0;
                $lowStock = 0;
                $safeStock = 0;

                foreach ($menus as $m) {
                    foreach ($m->variants as $v) {
                        $totalVariants++;
                        $st = $v->stockFor($branch->id);
                        if ($st <= 0) {
                            $outOfStock++;
                        } elseif ($st <= 5) {
                            $lowStock++;
                        } else {
                            $safeStock++;
                        }
                    }
                }
            @endphp

            <!-- Ringkasan Statistik Stok Cabang -->
            <div class="stock-stats-grid">
                <div class="stock-stat-card">
                    <span class="stock-stat-num">{{ $totalVariants }}</span>
                    <span class="stock-stat-label">Total Varian</span>
                </div>
                <div class="stock-stat-card stat-safe">
                    <span class="stock-stat-num">{{ $safeStock }}</span>
                    <span class="stock-stat-label">Stok Aman</span>
                </div>
                <div class="stock-stat-card stat-warn">
                    <span class="stock-stat-num">{{ $lowStock }}</span>
                    <span class="stock-stat-label">Menipis (≤5)</span>
                </div>
                <div class="stock-stat-card stat-danger">
                    <span class="stock-stat-num">{{ $outOfStock }}</span>
                    <span class="stock-stat-label">Habis</span>
                </div>
            </div>

            <!-- Toolbar Filter & Search Real-Time -->
            <div class="stock-filter-toolbar">
                <div class="stock-search-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="search" id="stockSearchInput" placeholder="Cari nama sajian, jenis (Utuh/Serut), atau rasa..." autocomplete="off">
                    <button type="button" class="stock-search-clear" id="stockSearchClear" hidden aria-label="Hapus pencarian">&times;</button>
                </div>
                <div class="stock-filter-pills" role="tablist">
                    <button type="button" class="stock-filter-btn active" data-filter="all">Semua ({{ $totalVariants }})</button>
                    <button type="button" class="stock-filter-btn btn-warn" data-filter="low">Menipis ({{ $lowStock }})</button>
                    <button type="button" class="stock-filter-btn btn-danger" data-filter="out">Habis ({{ $outOfStock }})</button>
                </div>
            </div>

            <div class="stock-empty-note" id="stockEmptyNote">
                Tidak ada menu atau varian yang sesuai dengan filter pencarian.
            </div>

            <!-- Daftar Menu & Varian Stok -->
            <div id="stockGroupsContainer">
                @foreach ($menus as $menu)
                    @php
                        $typeGroups = $menu->variants->groupBy(fn ($v) => $v->typeLabel() ?: 'Semua');
                        $hasMultipleTypes = $typeGroups->count() > 1;
                    @endphp

                    <section class="stock-menu-group" data-menu-group>
                        <div class="stock-menu-header">
                            <h2 class="stock-menu-title">
                                <span>{{ $menu->name }}</span>
                                <span class="badge">{{ $menu->categoryLabel() }}</span>
                            </h2>
                            <span class="muted" style="font-size: 12px">{{ $menu->variants->count() }} varian</span>
                        </div>

                        {{-- Sub-filter Jenis Penyajian jika menu memiliki beberapa jenis (misal Utuh & Serut) --}}
                        @if ($hasMultipleTypes)
                            <div class="menu-type-tabs" data-menu-type-tabs>
                                <button type="button" class="menu-type-tab-btn active" data-type-target="all">
                                    Semua Jenis ({{ $menu->variants->count() }})
                                </button>
                                @foreach ($typeGroups as $typeKey => $itemsInType)
                                    @php
                                        $isU = strcasecmp((string) $typeKey, 'utuh') === 0;
                                        $isS = strcasecmp((string) $typeKey, 'serut') === 0;
                                        $typeEmoji = $isU ? '🌽 ' : ($isS ? '🥣 ' : '');
                                    @endphp
                                    <button type="button" class="menu-type-tab-btn" data-type-target="{{ strtolower((string) $typeKey) }}">
                                        {{ $typeEmoji }}{{ $typeKey }} ({{ $itemsInType->count() }})
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <div class="stock-variants-list">
                            @foreach ($menu->variants as $variant)
                                @php
                                    $stock = $variant->stockFor($branch->id);
                                    $statusClass = $stock <= 0 ? 'out' : ($stock <= 5 ? 'low' : 'safe');
                                    $tLabel = $variant->typeLabel();
                                    $fLabel = $variant->flavourLabel();
                                    $isUtuh = strcasecmp((string) $tLabel, 'utuh') === 0;
                                    $isSerut = strcasecmp((string) $tLabel, 'serut') === 0;
                                    $typeSlug = strtolower((string) ($tLabel ?: 'all'));

                                    $searchKeyword = strtolower($menu->name . ' ' . $variant->displayName() . ' ' . ($tLabel ?? '') . ' ' . $fLabel . ' ' . $menu->categoryLabel());
                                    $isOldVariant = old('variant_id') == $variant->id;
                                    $selectedMode = $isOldVariant ? old('mode', 'in') : 'in';
                                @endphp

                                <article
                                    class="stock-item-card"
                                    id="variant-{{ $variant->id }}"
                                    data-stock-item
                                    data-status="{{ $statusClass }}"
                                    data-type="{{ $typeSlug }}"
                                    data-search="{{ $searchKeyword }}"
                                >
                                    <div class="stock-item-header">
                                        <div class="stock-item-info">
                                            <div class="stock-item-title-row">
                                                @if ($tLabel)
                                                    <span class="badge-type-pill {{ $isUtuh ? 'type-utuh' : ($isSerut ? 'type-serut' : 'type-custom') }}">
                                                        {{ $isUtuh ? '🌽 Utuh' : ($isSerut ? '🥣 Serut' : $tLabel) }}
                                                    </span>
                                                @endif
                                                <h3 class="stock-item-name">{{ $fLabel }}</h3>
                                                <span class="stock-item-price">Rp {{ number_format((float) $variant->selling_price, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                        <div class="stock-item-badge-wrap">
                                            @if ($stock <= 0)
                                                <span class="stock-badge badge-out">Habis (0)</span>
                                            @elseif ($stock <= 5)
                                                <span class="stock-badge badge-low">Menipis ({{ $stock }})</span>
                                            @else
                                                <span class="stock-badge badge-safe">Stok {{ $stock }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('owner.stock.store') }}#variant-{{ $variant->id }}" class="stock-adjust-form" data-adjust-form>
                                        @csrf
                                        <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                                        <input type="hidden" name="variant_id" value="{{ $variant->id }}">

                                        <div class="stock-form-controls">
                                            <!-- Pilihan Mode Segmented -->
                                            <div class="stock-mode-selector" role="radiogroup">
                                                <label class="stock-mode-radio mode-in" title="Tambah stok">
                                                    <input type="radio" name="mode" value="in" @checked($selectedMode === 'in') data-mode-radio>
                                                    <span>+ Tambah</span>
                                                </label>
                                                <label class="stock-mode-radio mode-out" title="Kurangi stok">
                                                    <input type="radio" name="mode" value="out" @checked($selectedMode === 'out') data-mode-radio>
                                                    <span>− Kurang</span>
                                                </label>
                                                <label class="stock-mode-radio" title="Koreksi stok langsung ke angka ini">
                                                    <input type="radio" name="mode" value="set" @checked($selectedMode === 'set') data-mode-radio>
                                                    <span>Koreksi (=)</span>
                                                </label>
                                            </div>

                                            <!-- Stepper & Tombol Simpan -->
                                            <div class="stock-input-actions">
                                                <div class="stock-stepper">
                                                    <button type="button" class="stepper-btn" data-step-dec aria-label="Kurangi jumlah">−</button>
                                                    <input
                                                        type="number"
                                                        name="quantity"
                                                        value="{{ $isOldVariant ? old('quantity', 1) : 1 }}"
                                                        min="{{ $selectedMode === 'set' ? 0 : 1 }}"
                                                        max="999999"
                                                        class="stepper-input"
                                                        data-qty-input
                                                        aria-label="Jumlah penyesuaian stok {{ $variant->displayName() }}"
                                                        required
                                                    >
                                                    <button type="button" class="stepper-btn" data-step-inc aria-label="Tambah jumlah">+</button>
                                                </div>
                                                <button type="submit" class="btn-stock-submit">Simpan</button>
                                            </div>
                                        </div>

                                        <div class="stock-note-row" data-note-row @if($selectedMode === 'in') hidden @endif>
                                            <input
                                                type="text"
                                                name="note"
                                                value="{{ $isOldVariant ? old('note') : '' }}"
                                                maxlength="255"
                                                placeholder="Catatan alasan perubahan (opsional)..."
                                                class="stock-note-input"
                                            >
                                        </div>
                                    </form>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    @endif
@endsection

@push('scripts')
    <script>
        (function () {
            // 1. Stepper and Form Mode Interactions
            document.querySelectorAll('[data-adjust-form]').forEach(function (form) {
                const qtyInput = form.querySelector('[data-qty-input]');
                const decBtn = form.querySelector('[data-step-dec]');
                const incBtn = form.querySelector('[data-step-inc]');
                const radios = form.querySelectorAll('[data-mode-radio]');
                const noteRow = form.querySelector('[data-note-row]');

                function updateMode(mode) {
                    if (mode === 'set') {
                        qtyInput.min = 0;
                    } else {
                        qtyInput.min = 1;
                        if (parseInt(qtyInput.value, 10) < 1) {
                            qtyInput.value = 1;
                        }
                    }

                    if (noteRow) {
                        noteRow.hidden = (mode === 'in');
                    }
                }

                radios.forEach(function (radio) {
                    radio.addEventListener('change', function () {
                        if (radio.checked) {
                            updateMode(radio.value);
                        }
                    });
                });

                if (decBtn && qtyInput) {
                    decBtn.addEventListener('click', function () {
                        const currentVal = parseInt(qtyInput.value, 10) || 0;
                        const min = parseInt(qtyInput.min, 10) || 0;
                        if (currentVal > min) {
                            qtyInput.value = currentVal - 1;
                        }
                    });
                }

                if (incBtn && qtyInput) {
                    incBtn.addEventListener('click', function () {
                        const currentVal = parseInt(qtyInput.value, 10) || 0;
                        qtyInput.value = currentVal + 1;
                    });
                }
            });

            // 2. Real-time Search and Filter Pills
            const searchInput = document.getElementById('stockSearchInput');
            const clearBtn = document.getElementById('stockSearchClear');
            const filterBtns = document.querySelectorAll('.stock-filter-btn');
            const groups = document.querySelectorAll('[data-menu-group]');
            const emptyNote = document.getElementById('stockEmptyNote');

            let currentFilter = 'all';
            let currentQuery = '';

            function applyFilter() {
                let totalVisible = 0;

                groups.forEach(function (group) {
                    const groupItems = group.querySelectorAll('[data-stock-item]');
                    const activeTypeTab = group.querySelector('.menu-type-tab-btn.active');
                    const selectedType = activeTypeTab ? activeTypeTab.dataset.typeTarget : 'all';
                    let groupVisible = 0;

                    groupItems.forEach(function (item) {
                        const itemStatus = item.dataset.status;
                        const itemSearch = item.dataset.search || '';
                        const itemType = item.dataset.type || '';

                        const matchesStatus = (currentFilter === 'all') ||
                            (currentFilter === 'low' && itemStatus === 'low') ||
                            (currentFilter === 'out' && itemStatus === 'out');

                        const matchesType = (selectedType === 'all') || (itemType === selectedType);
                        const matchesQuery = !currentQuery || itemSearch.includes(currentQuery);

                        if (matchesStatus && matchesType && matchesQuery) {
                            item.classList.remove('is-hidden');
                            groupVisible++;
                            totalVisible++;
                        } else {
                            item.classList.add('is-hidden');
                        }
                    });

                    if (groupVisible > 0) {
                        group.classList.remove('is-hidden');
                    } else {
                        group.classList.add('is-hidden');
                    }
                });

                if (emptyNote) {
                    emptyNote.classList.toggle('is-visible', totalVisible === 0);
                }
            }

            // Per-Menu Type Tabs Handling (Utuh vs Serut)
            document.querySelectorAll('[data-menu-type-tabs]').forEach(function (tabsWrap) {
                const tabBtns = tabsWrap.querySelectorAll('.menu-type-tab-btn');
                tabBtns.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        tabBtns.forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                        applyFilter();
                    });
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    currentQuery = searchInput.value.trim().toLowerCase();
                    if (clearBtn) {
                        clearBtn.hidden = !currentQuery;
                    }
                    applyFilter();
                });

                if (clearBtn) {
                    clearBtn.addEventListener('click', function () {
                        searchInput.value = '';
                        currentQuery = '';
                        clearBtn.hidden = true;
                        searchInput.focus();
                        applyFilter();
                    });
                }
            }

            filterBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    filterBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    currentFilter = btn.dataset.filter || 'all';
                    applyFilter();
                });
            });
        })();
    </script>
@endpush
