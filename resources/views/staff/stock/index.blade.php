@extends('layouts.app')

@section('title', 'Kelola Stok - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Kelola Stok</h1>
            <p class="muted">{{ $branch?->name ?? 'Cabang belum ditentukan' }}</p>
        </div>
        <div class="head-actions">
            <a href="{{ route('staff.activity.index') }}" class="btn btn-ghost">Log Aktivitas</a>
            @include('partials.role-chip', ['role' => 'staff'])
        </div>
    </div>

    @include('partials.alerts')

    @unless ($branch)
        <div class="card empty">
            <h2>Akun belum terhubung ke cabang</h2>
            <p class="muted">Hubungi owner untuk menghubungkan akun staff ke cabang operasional.</p>
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

        <!-- Daftar Menu & Varian Stok Staff -->
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

                                <form method="POST" action="{{ route('staff.stock.store') }}#variant-{{ $variant->id }}" class="stock-adjust-form" data-adjust-form>
                                    @csrf
                                    <input type="hidden" name="variant_id" value="{{ $variant->id }}">

                                    <div class="stock-form-controls">
                                        <!-- Pilihan Mode Tambah / Kurang -->
                                        <div class="stock-mode-selector" role="radiogroup">
                                            <label class="stock-mode-radio mode-in" title="Tambah stok masuk">
                                                <input type="radio" name="mode" value="in" @checked($selectedMode !== 'out') data-mode-radio>
                                                <span>+ Tambah</span>
                                            </label>
                                            <label class="stock-mode-radio mode-out" title="Kurangi stok keluar">
                                                <input type="radio" name="mode" value="out" @checked($selectedMode === 'out') data-mode-radio>
                                                <span>− Kurang</span>
                                            </label>
                                        </div>

                                        <!-- Stepper & Tombol Simpan -->
                                        <div class="stock-input-actions">
                                            <div class="stock-stepper">
                                                <button type="button" class="stepper-btn" data-step-dec aria-label="Kurangi 1">−</button>
                                                <input
                                                    type="number"
                                                    name="quantity"
                                                    value="{{ $isOldVariant ? old('quantity', 1) : 1 }}"
                                                    min="1"
                                                    max="9999"
                                                    class="stepper-input"
                                                    data-qty-input
                                                    aria-label="Jumlah stok {{ $variant->displayName() }}"
                                                    required
                                                >
                                                <button type="button" class="stepper-btn" data-step-inc aria-label="Tambah 1">+</button>
                                            </div>
                                            <button type="submit" class="btn-stock-submit">Simpan</button>
                                        </div>
                                    </div>

                                    <div class="stock-note-row" data-note-row @if($selectedMode !== 'out') hidden @endif>
                                        <input
                                            type="text"
                                            name="note"
                                            value="{{ $isOldVariant ? old('note') : '' }}"
                                            maxlength="255"
                                            placeholder="Catatan wajib: rusak / tumpah / kadaluarsa..."
                                            class="stock-note-input"
                                            data-note-input
                                            @if($selectedMode === 'out') required @endif
                                        >
                                    </div>
                                </form>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <!-- Bagian Riwayat Stok Terbaru -->
        <h2 class="group-title" style="margin-top: 32px">Riwayat Stok Terbaru</h2>

        @if ($movements->isEmpty())
            <div class="card empty">
                <p class="muted" style="margin: 0">Belum ada pergerakan stok di cabang ini.</p>
            </div>
        @else
            <div class="stock-history-cards">
                @foreach ($movements as $movement)
                    <article class="stock-history-card">
                        <div class="stock-history-head">
                            @if ($movement->movement_type === 'in')
                                <span class="status status-finalized">Masuk</span>
                            @elseif ($movement->movement_type === 'out')
                                <span class="status status-cancelled">Keluar</span>
                            @else
                                <span class="status status-draft">Koreksi</span>
                            @endif
                            <span class="stock-history-time">{{ $movement->occurred_at?->format('d/m/Y H:i') }}</span>
                            <span class="stock-history-qty" style="color: {{ $movement->movement_type === 'in' ? '#1e7039' : ($movement->movement_type === 'out' ? '#b43020' : '#8a6308') }}">
                                {{ $movement->movement_type === 'in' ? '+' : ($movement->movement_type === 'out' ? '−' : '=') }}{{ $movement->quantity }}
                            </span>
                        </div>
                        <p class="stock-history-menu">
                            {{ $movement->variant?->menu?->name ?? '—' }}
                            <span class="muted">· {{ $movement->variant?->displayName() }}</span>
                        </p>
                        <p class="stock-history-meta">
                            Oleh: <strong>{{ $movement->user?->name ?? '—' }}</strong>@if ($movement->note) · <span style="font-style: italic">"{{ $movement->note }}"</span>@endif
                        </p>
                    </article>
                @endforeach
            </div>

            <div class="card stock-history-table" style="padding: 0">
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Menu</th>
                                <th>Jenis</th>
                                <th class="amount">Jumlah</th>
                                <th>Oleh</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($movements as $movement)
                                <tr>
                                    <td>{{ $movement->occurred_at?->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <strong>{{ $movement->variant?->menu?->name ?? '—' }}</strong>
                                        <div class="muted">{{ $movement->variant?->displayName() }}</div>
                                    </td>
                                    <td>
                                        @if ($movement->movement_type === 'in')
                                            <span class="status status-finalized">Masuk</span>
                                        @elseif ($movement->movement_type === 'out')
                                            <span class="status status-cancelled">Keluar</span>
                                        @else
                                            <span class="status status-draft">Koreksi</span>
                                        @endif
                                    </td>
                                    <td class="amount" style="font-weight: 700; color: {{ $movement->movement_type === 'in' ? '#1e7039' : ($movement->movement_type === 'out' ? '#b43020' : '#8a6308') }}">
                                        {{ $movement->movement_type === 'in' ? '+' : ($movement->movement_type === 'out' ? '−' : '=') }}{{ $movement->quantity }}
                                    </td>
                                    <td>{{ $movement->user?->name ?? '—' }}</td>
                                    <td>{{ $movement->note ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endunless
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
                const noteInput = form.querySelector('[data-note-input]');

                function updateMode(mode) {
                    const isOut = (mode === 'out');
                    if (noteRow) {
                        noteRow.hidden = !isOut;
                    }
                    if (noteInput) {
                        noteInput.required = isOut;
                        if (isOut) {
                            noteInput.focus();
                        }
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
                        if (currentVal > 1) {
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

            // 2. Real-time Search, Status Filter, and Per-Menu Type Filter
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
