@extends('layouts.app')

@section('title', 'Kasir - Jagung Bakar WJT')

@section('content')
    @php
        $draftItems = $draft?->items ?? collect();
        $subtotal = (float) ($draft?->subtotal ?? 0);
        $discountAmount = (float) ($draft?->discount_amount ?? 0);
        $totalAmount = (float) ($draft?->total_amount ?? 0);
        $itemCount = (int) $draftItems->sum('quantity');
    @endphp

    <div class="page-head">
        <div>
            <h1>Kasir</h1>
            <p class="muted">{{ $branch?->name ?? 'Cabang belum ditentukan' }}</p>
        </div>
        <div class="cart-controls" style="margin-top: 0">
            <a href="{{ route('staff.stock.index') }}" class="btn btn-sm btn-ghost">Tambah Stok</a>
            <a href="{{ route('staff.orders.index') }}" class="btn btn-sm btn-ghost">Riwayat</a>
        </div>
    </div>

    @include('partials.alerts')

    @if (session('stock_shortage') || $errors->has('stock'))
        <p class="hint" style="margin: -8px 0 14px">
            <a href="{{ route('staff.stock.index') }}" class="btn btn-sm">Tambah Stok Sekarang</a>
        </p>
    @endif

    @unless ($branch)
        <div class="card empty">
            <h2>Akun belum terhubung ke cabang</h2>
            <p class="muted">Hubungi owner untuk menghubungkan akun staff ke cabang sebelum bertransaksi.</p>
        </div>
    @else
        <div class="chips">
            <a href="{{ route('staff.pos.index') }}" class="chip {{ $category === null ? 'active' : '' }}">Semua</a>
            @foreach (\App\Models\Menu::CATEGORIES as $item)
                <a
                    href="{{ route('staff.pos.index', ['category' => $item]) }}"
                    class="chip {{ $category === $item ? 'active' : '' }}"
                >{{ \App\Models\Menu::categoryLabelFor($item) }}</a>
            @endforeach
        </div>

        <div class="pos-layout">
            <section>
                @if ($menus->isEmpty())
                    <div class="card empty">
                        <h2>Belum ada menu aktif</h2>
                        <p class="muted">Menu akan muncul setelah owner menambahkannya.</p>
                    </div>
                @else
                    <div class="pos-menu-grid">
                        @foreach ($menus as $menu)
                            @php
                                $variants = $menu->variants->map(fn ($variant) => [
                                    'id' => $variant->id,
                                    'name' => $variant->displayName(),
                                    'price' => (float) $variant->selling_price,
                                    'stock' => $variant->stockFor($branch->id),
                                ])->values();
                                $menuStock = $variants->sum('stock');
                            @endphp

                            <button
                                type="button"
                                class="pos-menu-card"
                                data-pos-menu
                                data-menu-name="{{ $menu->name }}"
                                data-menu-category="{{ $menu->categoryLabel() }}"
                                data-menu-topping="{{ $menu->hasToppingOption() ? $menu->toppingPrice() : 0 }}"
                                data-menu-variants="{{ $variants->toJson() }}"
                            >
                                <strong>{{ $menu->name }}</strong>
                                <span class="price">{{ $menu->priceRange() }}</span>
                                <span class="stock-pill {{ $menuStock < 1 ? 'low' : '' }}">
                                    {{ $menuStock < 1 ? 'Stok habis' : 'Stok '.$menuStock }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </section>

            <aside class="cart pos-cart">
                <div class="cart-item-top" style="padding-bottom: 6px">
                    <div>
                        <h2>Keranjang</h2>
                        <p class="muted" style="margin: 0">{{ $itemCount }} item</p>
                    </div>
                    @if ($draft)
                        <span class="status status-draft">Draft</span>
                    @endif
                </div>

                @if ($draftItems->isEmpty())
                    <p class="muted" style="margin: 14px 0">
                        Keranjang masih kosong. Pilih menu di sebelah kiri untuk mulai transaksi.
                    </p>
                @else
                    @foreach ($draftItems as $item)
                        @php
                            $hasTopping = $item->variant?->menu?->hasToppingOption() ?? false;
                            $withTopping = (float) $item->topping_price > 0;
                        @endphp

                        <div class="cart-item">
                            <div class="cart-item-top">
                                <div>
                                    <strong>{{ $item->product_name_snapshot }}</strong>
                                    <div class="muted">{{ $item->variant_name_snapshot }}</div>
                                    @if ($withTopping)
                                        <div class="muted">+ topping Rp {{ number_format((float) $item->topping_price, 0, ',', '.') }}</div>
                                    @endif
                                </div>
                                <span class="line-total">Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}</span>
                            </div>

                            <div class="cart-controls">
                                <form method="POST" action="{{ route('staff.orders.items.update', $item) }}" class="cart-controls" style="margin: 0">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="99" aria-label="Jumlah {{ $item->product_name_snapshot }}">
                                    @if ($hasTopping)
                                        <label class="check">
                                            <input type="checkbox" name="with_topping" value="1" @checked($withTopping)>
                                            Topping
                                        </label>
                                    @endif
                                    <button type="submit" class="btn btn-sm btn-ghost">Ubah</button>
                                </form>

                                <form method="POST" action="{{ route('staff.orders.items.destroy', $item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-ghost">Hapus</button>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    <div class="cart-summary">
                        <div class="summary-line">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="summary-line">
                            <span>Diskon</span>
                            <span>− Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                        </div>
                        <div class="summary-line total">
                            <span>Total</span>
                            <span>Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <details class="form-section">
                        <summary class="muted" style="cursor: pointer">Diskon &amp; catatan</summary>

                        <form method="POST" action="{{ route('staff.orders.discount') }}" style="margin-top: 12px">
                            @csrf
                            @method('PATCH')
                            <div class="field">
                                <label for="discount_amount">Diskon (Rp)</label>
                                <input
                                    type="number"
                                    id="discount_amount"
                                    name="discount_amount"
                                    value="{{ (int) $discountAmount }}"
                                    min="0"
                                    step="1"
                                >
                            </div>
                            <div class="field">
                                <label for="discount_reason">Alasan diskon</label>
                                <input type="text" id="discount_reason" name="reason" maxlength="255" placeholder="Opsional">
                            </div>
                            <button type="submit" class="btn btn-sm btn-ghost btn-block">Simpan Diskon</button>
                        </form>

                        <form method="POST" action="{{ route('staff.orders.notes') }}" style="margin-top: 14px">
                            @csrf
                            @method('PATCH')
                            <div class="field">
                                <label for="notes">Catatan pesanan</label>
                                <textarea id="notes" name="notes" maxlength="500" placeholder="Contoh: tanpa saus, dibungkus">{{ $draft?->notes }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-ghost btn-block">Simpan Catatan</button>
                        </form>
                    </details>

                    <button type="button" class="btn btn-block" style="margin-top: 14px" data-payment-open>
                        Bayar Rp {{ number_format($totalAmount, 0, ',', '.') }}
                    </button>

                    <form method="POST" action="{{ route('staff.orders.cancel') }}" style="margin-top: 8px" onsubmit="return confirm('Batalkan keranjang ini?')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-ghost btn-block">Batalkan Keranjang</button>
                    </form>
                @endif
            </aside>
        </div>

        <dialog id="variant-dialog" class="sheet" aria-labelledby="variant-title">
            <div class="sheet-handle"></div>

            <div class="sheet-head">
                <div>
                    <h2 id="variant-title"></h2>
                    <p class="muted" id="variant-category"></p>
                </div>
                <button type="button" class="sheet-close" data-sheet-close aria-label="Tutup">×</button>
            </div>

            <div id="variant-list"></div>
        </dialog>

        @if ($draft && $draftItems->isNotEmpty())
            <dialog id="payment-dialog" class="sheet" aria-labelledby="payment-title">
                <div class="sheet-handle"></div>

                <div class="sheet-head">
                    <div>
                        <h2 id="payment-title">Pembayaran</h2>
                        <p class="muted">Total Rp {{ number_format($totalAmount, 0, ',', '.') }}</p>
                    </div>
                    <button type="button" class="sheet-close" data-payment-close aria-label="Tutup">×</button>
                </div>

                <form
                    method="POST"
                    action="{{ route('staff.orders.finalize') }}"
                    id="payment-form"
                    data-total="{{ $totalAmount }}"
                >
                    @csrf

                    <div class="field">
                        <label for="payment_method">Metode pembayaran</label>
                        <select id="payment_method" name="payment_method">
                            <option value="cash">Tunai</option>
                            <option value="qris">QRIS</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </div>

                    <div id="cash-fields">
                        <div class="field">
                            <label for="amount_tendered">Uang diterima (Rp)</label>
                            <input
                                type="number"
                                id="amount_tendered"
                                name="amount_tendered"
                                min="{{ (int) ceil($totalAmount) }}"
                                step="1"
                                value="{{ (int) ceil($totalAmount) }}"
                            >
                        </div>

                        <div class="cart-controls" style="margin-bottom: 12px">
                            <button type="button" class="btn btn-sm btn-ghost" data-tender="{{ (int) ceil($totalAmount) }}">Uang pas</button>
                            <button type="button" class="btn btn-sm btn-ghost" data-tender="20000">20.000</button>
                            <button type="button" class="btn btn-sm btn-ghost" data-tender="50000">50.000</button>
                            <button type="button" class="btn btn-sm btn-ghost" data-tender="100000">100.000</button>
                        </div>

                        <div class="summary-line">
                            <span>Kembalian</span>
                            <strong id="change-preview">Rp 0</strong>
                        </div>
                    </div>

                    <div id="reference-fields" hidden>
                        <div class="field">
                            <label for="reference_code">Kode referensi (opsional)</label>
                            <input type="text" id="reference_code" name="reference_code" maxlength="100">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-block" style="margin-top: 14px">Bayar Sekarang</button>
                </form>
            </dialog>
        @endif
    @endunless
@endsection

@push('scripts')
    <script>
        (function () {
            const rupiah = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0,
            });

            const variantDialog = document.getElementById('variant-dialog');
            const variantTitle = document.getElementById('variant-title');
            const variantCategory = document.getElementById('variant-category');
            const variantList = document.getElementById('variant-list');
            const storeUrl = @json(route('staff.orders.items.store'));
            const csrfToken = @json(csrf_token());

            function openDialog(dialog) {
                if (!dialog) {
                    return;
                }

                if (typeof dialog.showModal === 'function') {
                    dialog.showModal();
                } else {
                    dialog.setAttribute('open', '');
                }
            }

            function closeDialog(dialog) {
                if (!dialog) {
                    return;
                }

                if (typeof dialog.close === 'function') {
                    dialog.close();
                } else {
                    dialog.removeAttribute('open');
                }
            }

            if (!variantDialog) {
                return;
            }

            function buildVariantForm(variant, toppingPrice) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = storeUrl;
                form.className = 'pos-variant-row';

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = csrfToken;
                form.appendChild(csrf);

                const variantInput = document.createElement('input');
                variantInput.type = 'hidden';
                variantInput.name = 'variant_id';
                variantInput.value = variant.id;
                form.appendChild(variantInput);

                const info = document.createElement('div');

                const name = document.createElement('div');
                name.className = 'variant-name';
                name.textContent = variant.name;
                info.appendChild(name);

                const meta = document.createElement('div');
                meta.className = 'muted';
                meta.textContent = rupiah.format(Number(variant.price) || 0)
                    + ' · stok ' + (Number(variant.stock) || 0);
                info.appendChild(meta);

                if (toppingPrice > 0) {
                    const topping = document.createElement('label');
                    topping.className = 'check';
                    topping.style.marginTop = '6px';

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = 'with_topping';
                    checkbox.value = '1';

                    const text = document.createElement('span');
                    text.textContent = 'Topping +' + rupiah.format(toppingPrice);

                    topping.append(checkbox, text);
                    info.appendChild(topping);
                }

                const actions = document.createElement('div');
                actions.className = 'qty-add';

                const quantity = document.createElement('input');
                quantity.type = 'number';
                quantity.name = 'quantity';
                quantity.value = '1';
                quantity.min = '1';
                quantity.max = String(Math.min(Math.max(Number(variant.stock) || 0, 1), 99));
                quantity.ariaLabel = 'Jumlah ' + variant.name;
                actions.appendChild(quantity);

                const button = document.createElement('button');
                button.type = 'submit';
                button.className = 'btn btn-sm';
                button.textContent = Number(variant.stock) > 0 ? 'Tambah' : 'Stok habis';
                button.disabled = Number(variant.stock) < 1;
                actions.appendChild(button);

                form.append(info, actions);

                return form;
            }

            document.querySelectorAll('[data-pos-menu]').forEach(function (card) {
                card.addEventListener('click', function () {
                    let variants = [];

                    try {
                        variants = JSON.parse(card.dataset.menuVariants || '[]');
                    } catch (error) {
                        variants = [];
                    }

                    const toppingPrice = Number(card.dataset.menuTopping) || 0;

                    variantTitle.textContent = card.dataset.menuName;
                    variantCategory.textContent = card.dataset.menuCategory;
                    variantList.innerHTML = '';

                    if (variants.length === 0) {
                        const empty = document.createElement('p');
                        empty.className = 'muted';
                        empty.textContent = 'Belum ada varian aktif.';
                        variantList.appendChild(empty);
                    } else {
                        variants.forEach(function (variant) {
                            variantList.appendChild(buildVariantForm(variant, toppingPrice));
                        });
                    }

                    openDialog(variantDialog);
                });
            });

            document.querySelectorAll('[data-sheet-close]').forEach(function (button) {
                button.addEventListener('click', function () {
                    closeDialog(variantDialog);
                });
            });

            variantDialog.addEventListener('click', function (event) {
                if (event.target === variantDialog) {
                    closeDialog(variantDialog);
                }
            });

            const paymentDialog = document.getElementById('payment-dialog');

            if (paymentDialog) {
                const paymentForm = document.getElementById('payment-form');
                const methodSelect = document.getElementById('payment_method');
                const cashFields = document.getElementById('cash-fields');
                const referenceFields = document.getElementById('reference-fields');
                const tendered = document.getElementById('amount_tendered');
                const changePreview = document.getElementById('change-preview');
                const submitButton = paymentForm.querySelector('button[type="submit"]');
                const total = Number(paymentForm.dataset.total) || 0;

                function updatePaymentFields() {
                    const isCash = methodSelect.value === 'cash';

                    cashFields.hidden = !isCash;
                    referenceFields.hidden = isCash;
                    tendered.required = isCash;
                    tendered.disabled = !isCash;

                    updateChange();
                }

                function updateChange() {
                    if (methodSelect.value !== 'cash') {
                        changePreview.textContent = rupiah.format(0);
                        submitButton.disabled = false;

                        return;
                    }

                    const received = Number(tendered.value) || 0;
                    const change = received - total;

                    changePreview.textContent = rupiah.format(Math.max(change, 0));
                    submitButton.disabled = change < 0;
                }

                methodSelect.addEventListener('change', updatePaymentFields);
                tendered.addEventListener('input', updateChange);

                document.querySelectorAll('[data-tender]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        tendered.value = button.dataset.tender;
                        updateChange();
                    });
                });

                document.querySelectorAll('[data-payment-open]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        updatePaymentFields();
                        openDialog(paymentDialog);
                    });
                });

                document.querySelectorAll('[data-payment-close]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        closeDialog(paymentDialog);
                    });
                });

                paymentDialog.addEventListener('click', function (event) {
                    if (event.target === paymentDialog) {
                        closeDialog(paymentDialog);
                    }
                });

                updatePaymentFields();
            }
        })();
    </script>
@endpush
