@extends('layouts.app')

@section('title', 'Edit Menu - Jagung Bakar WJT')

@section('body_class', 'page-owner-menu-form page-owner-menu-edit')

@push('styles')
    <style>
        body.page-owner-menu-form .form-card .hint {
            display: block;
            margin-top: 6px;
            overflow-wrap: anywhere;
        }

        body.page-owner-menu-form .form-section h2 {
            overflow-wrap: anywhere;
        }

        body.page-owner-menu-edit .page-head p {
            overflow-wrap: anywhere;
        }

        body.page-owner-menu-edit .variant-edit-title,
        body.page-owner-menu-edit .live-preview-box {
            overflow-wrap: anywhere;
        }

        body.page-owner-menu-edit .variant-edit-badges {
            flex-wrap: wrap;
        }
    </style>
@endpush

@section('content')
    <div class="page-head">
        <div>
            <h1>Edit Menu</h1>
            <p class="muted">{{ $menu->name }} · {{ $menu->categoryLabel() }}</p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center">
            <a href="{{ route('owner.menus.index') }}" class="btn btn-ghost">Kembali</a>
        </div>
    </div>

    @include('partials.alerts')

    <!-- Kartu Informasi Dasar Menu -->
    <form
        method="POST"
        action="{{ route('owner.menus.update', $menu) }}"
        enctype="multipart/form-data"
        class="card form-card"
        data-menu-form
    >
        @csrf
        @method('PUT')

        @include('owner.menus._form', ['menu' => $menu])

        <button type="submit" class="btn btn-block" data-menu-submit style="margin-top: 10px">Simpan Informasi Menu</button>
    </form>

    <!-- Kartu Kelola Varian Menu -->
    <div class="card form-card" style="margin-top: 20px">
        <div class="page-head" style="margin-bottom: 14px">
            <div>
                <h2>Varian Menu</h2>
                <p class="muted">
                    Atur jenis penyajian (misal: <strong>Utuh</strong> atau <strong>Serut</strong>) dan variasi rasa (misal: <strong>Pedas Manis</strong>, <strong>Keju</strong>).
                </p>
            </div>
            <span class="badge">{{ $menu->variants->count() }} Varian</span>
        </div>

        @if ($menu->variants->isEmpty())
            <div class="empty" style="padding: 16px; border: 1px dashed var(--border-warm); border-radius: 10px; margin-bottom: 16px">
                <p class="muted" style="margin: 0">Belum ada varian untuk menu ini. Tambahkan varian pertama di bawah.</p>
            </div>
        @else
            <div class="variant-list">
                @foreach ($menu->variants as $variant)
                    @php
                        $tLabel = $variant->typeLabel();
                        $fLabel = $variant->flavourLabel();
                        $isUtuh = strcasecmp((string) $tLabel, 'utuh') === 0;
                        $isSerut = strcasecmp((string) $tLabel, 'serut') === 0;
                    @endphp

                    <div class="variant-edit" id="variant-edit-{{ $variant->id }}">
                        <div class="variant-edit-head">
                            <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 6px 10px">
                                @if ($tLabel)
                                    <span class="badge-type-pill {{ $isUtuh ? 'type-utuh' : ($isSerut ? 'type-serut' : 'type-custom') }}">
                                        {{ $isUtuh ? '🌽 Utuh' : ($isSerut ? '🥣 Serut' : $tLabel) }}
                                    </span>
                                @endif
                                <h3 class="variant-edit-title">{{ $fLabel }}</h3>
                            </div>
                            <div class="variant-edit-badges">
                                <span class="stock-badge {{ $variant->is_active ? 'badge-safe' : 'badge-out' }}">
                                    {{ $variant->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                <span style="font-weight: 700; font-size: 13.5px; color: var(--brand-brown)">
                                    Rp {{ number_format((float) $variant->selling_price, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('owner.menus.variants.update', [$menu, $variant]) }}"
                            class="variant-row"
                        >
                            @csrf
                            @method('PUT')

                            <div class="variant-field-group">
                                <label class="variant-field-label" for="vtype-{{ $variant->id }}">Jenis / Tipe</label>
                                <input
                                    id="vtype-{{ $variant->id }}"
                                    type="text"
                                    name="type"
                                    value="{{ $variant->type }}"
                                    placeholder="Contoh: Utuh, Serut"
                                    aria-label="Jenis varian"
                                    maxlength="50"
                                >
                            </div>

                            <div class="variant-field-group">
                                <label class="variant-field-label" for="vname-{{ $variant->id }}">Nama Varian / Rasa</label>
                                <input
                                    id="vname-{{ $variant->id }}"
                                    type="text"
                                    name="variant_name"
                                    value="{{ $variant->variant_name }}"
                                    placeholder="Contoh: Pedas Manis, Keju"
                                    aria-label="Nama varian"
                                    maxlength="100"
                                    required
                                >
                            </div>

                            <div class="variant-field-group">
                                <label class="variant-field-label" for="vprice-{{ $variant->id }}">Harga (Rp)</label>
                                <input
                                    id="vprice-{{ $variant->id }}"
                                    type="number"
                                    name="selling_price"
                                    value="{{ (float) $variant->selling_price }}"
                                    aria-label="Harga varian"
                                    min="0"
                                    step="any"
                                    inputmode="numeric"
                                    required
                                >
                            </div>

                            <div style="display: flex; align-items: center; gap: 8px">
                                <input type="hidden" name="is_active" value="0">
                                <label class="check" title="Centang untuk mengaktifkan varian">
                                    <input type="checkbox" name="is_active" value="1" @checked($variant->is_active)>
                                    <span>Aktif</span>
                                </label>

                                <button type="submit" class="btn btn-sm">Simpan</button>
                            </div>
                        </form>

                        <div class="variant-actions-secondary">
                            <form
                                method="POST"
                                action="{{ route('owner.menus.variants.toggle', [$menu, $variant]) }}"
                            >
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-ghost" style="font-size: 12px">
                                    {{ $variant->is_active ? 'Nonaktifkan Varian' : 'Aktifkan Varian' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Form Tambah Varian Baru -->
        <div class="form-section" style="margin-top: 24px">
            <h2>Tambah Varian Baru</h2>
            <p class="muted">
                Untuk Jagung Bakar, Anda bisa memilih jenis <strong>Utuh</strong> atau <strong>Serut</strong>, lalu isi rasa yang diinginkan.
            </p>

            @php
                $menuLower = strtolower($menu->name);
                $isCorn = str_contains($menuLower, 'jagung');
                $isDrink = $menu->category === \App\Models\Menu::CATEGORY_MINUMAN;
                $isBanana = str_contains($menuLower, 'pisang');
            @endphp

            <form
                method="POST"
                action="{{ route('owner.menus.variants.store', $menu) }}"
                class="variant-row-add"
                id="addVariantForm"
            >
                @csrf
                <div class="variant-field-group">
                    <label class="variant-field-label" for="new_variant_type">Jenis Penyajian (Opsional)</label>
                    <input
                        id="new_variant_type"
                        type="text"
                        name="type"
                        placeholder="{{ $isCorn ? 'Misal: Utuh atau Serut' : ($isDrink ? 'Misal: Es atau Panas' : 'Misal: Reguler') }}"
                        aria-label="Jenis penyajian baru"
                        maxlength="50"
                        autocomplete="off"
                    >
                    @if ($isCorn)
                        <div class="type-shortcut-pills">
                            <button type="button" class="type-shortcut-btn" data-fill-type="Utuh">🌽 Utuh</button>
                            <button type="button" class="type-shortcut-btn" data-fill-type="Serut">🥣 Serut</button>
                        </div>
                    @elseif ($isDrink)
                        <div class="type-shortcut-pills">
                            <button type="button" class="type-shortcut-btn" data-fill-type="Es">❄️ Es</button>
                            <button type="button" class="type-shortcut-btn" data-fill-type="Panas">☕ Panas</button>
                        </div>
                    @elseif ($isBanana)
                        <div class="type-shortcut-pills">
                            <button type="button" class="type-shortcut-btn" data-fill-type="Pisang Raja">🍌 Pisang Raja</button>
                            <button type="button" class="type-shortcut-btn" data-fill-type="Pisang Kepok">🍌 Pisang Kepok</button>
                        </div>
                    @endif
                </div>

                <div class="variant-field-group">
                    <label class="variant-field-label" for="new_variant_name">Nama Varian / Rasa (Wajib)</label>
                    <input
                        id="new_variant_name"
                        type="text"
                        name="variant_name"
                        placeholder="Contoh: Pedas Manis, Keju, Original"
                        aria-label="Nama varian baru"
                        maxlength="100"
                        required
                        autocomplete="off"
                    >
                </div>

                <div class="variant-field-group">
                    <label class="variant-field-label" for="new_selling_price">Harga Jual (Rp)</label>
                    <input
                        id="new_selling_price"
                        type="number"
                        name="selling_price"
                        placeholder="Contoh: 8000"
                        aria-label="Harga varian baru"
                        min="0"
                        step="any"
                        inputmode="numeric"
                        required
                    >
                </div>

                <input type="hidden" name="is_active" value="1">
                <button type="submit" class="btn btn-sm">+ Tambah Varian</button>
            </form>

            <div class="live-preview-box" id="newVariantPreview">
                <span>Pratinjau nama di Kasir & Struk:</span>
                <strong id="previewVariantName">{{ $isCorn ? 'Utuh · Pedas Manis' : 'Nama Varian' }}</strong>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/menu-image.js') }}" defer></script>
    <script>
        (function () {
            const typeInput = document.getElementById('new_variant_type');
            const nameInput = document.getElementById('new_variant_name');
            const preview = document.getElementById('previewVariantName');
            const shortcutBtns = document.querySelectorAll('[data-fill-type]');

            function updatePreview() {
                const t = typeInput ? typeInput.value.trim() : '';
                const n = nameInput ? nameInput.value.trim() : '';

                if (t && n) {
                    preview.textContent = t + ' · ' + n;
                } else if (n) {
                    preview.textContent = n;
                } else if (t) {
                    preview.textContent = t;
                } else {
                    preview.textContent = '—';
                }
            }

            if (typeInput) {
                typeInput.addEventListener('input', updatePreview);
            }
            if (nameInput) {
                nameInput.addEventListener('input', updatePreview);
            }

            shortcutBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (typeInput) {
                        typeInput.value = btn.dataset.fillType || '';
                        shortcutBtns.forEach(b => b.classList.remove('is-selected'));
                        btn.classList.add('is-selected');
                        updatePreview();
                        if (nameInput && !nameInput.value) {
                            nameInput.focus();
                        }
                    }
                });
            });

            updatePreview();
        })();
    </script>
@endpush
