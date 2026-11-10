@extends('layouts.app')

@section('title', 'Pesanan '.$order->queueLabel().' - Jagung Bakar WJT')

@section('content')
    @php
        $items = $order->items;
        $paid = $order->paidAmount();
        $due = $order->balanceDue();
        $subtotal = (float) $order->subtotal;
        $discountAmount = (float) $order->discount_amount;
        $totalAmount = (float) $order->total_amount;
        $itemCount = (int) $items->sum('quantity');
        $editable = $order->isEditable();
        $payments = $order->payments->where('status', \App\Models\Payment::STATUS_PAID)->sortBy('paid_at');
    @endphp

    <div class="page-head no-print">
        <div>
            <h1>Pesanan {{ $order->queueLabel() }}</h1>
            <p class="muted">{{ $branch?->name ?? 'Cabang belum ditentukan' }} · {{ $order->order_code }}</p>
        </div>
        <div class="cart-controls" style="margin-top: 0">
            <a href="{{ route('staff.pos.index') }}" class="btn btn-sm btn-ghost">← Papan Pesanan</a>
            <a href="{{ route('staff.orders.show', $order) }}" class="btn btn-sm btn-ghost">Struk</a>
        </div>
    </div>

    @include('partials.alerts')

    @if (session('stock_shortage') || $errors->has('stock'))
        <p class="hint no-print" style="margin: -8px 0 14px">
            <a href="{{ route('staff.stock.index') }}" class="btn btn-sm">Tambah Stok Sekarang</a>
        </p>
    @endif

    <div class="order-hero no-print">
        <div>
            <div class="queue-number">{{ $order->queueLabel() }}</div>
            <h1>{{ $order->customer_name }}</h1>
            <div class="order-card-meta">
                <span>{{ $order->orderTypeLabel() }}</span>
                <span>Kasir {{ $order->creator?->name ?? '—' }}</span>
                <span>{{ $order->created_at?->format('d/m/Y H:i') }}</span>
            </div>
        </div>
        <div style="text-align: right">
            <span class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
            <span class="status status-{{ $order->paymentStatus() }}">{{ $order->paymentStatusLabel() }}</span>
            <div class="muted" style="margin-top: 8px">
                Total <strong class="money-big">Rp {{ number_format($totalAmount, 0, ',', '.') }}</strong>
            </div>
        </div>
    </div>

    @unless ($branch)
        <div class="card empty">
            <h2>Akun belum terhubung ke cabang</h2>
            <p class="muted">Hubungi owner untuk menghubungkan akun staff ke cabang sebelum bertransaksi.</p>
        </div>
    @else
        <div class="chips no-print">
            <a href="{{ route('staff.pos.show', $order) }}" class="chip {{ $category === null ? 'active' : '' }}">Semua</a>
            @foreach (\App\Models\Menu::CATEGORIES as $item)
                <a
                    href="{{ route('staff.pos.show', ['order' => $order, 'category' => $item]) }}"
                    class="chip {{ $category === $item ? 'active' : '' }}"
                >{{ \App\Models\Menu::categoryLabelFor($item) }}</a>
            @endforeach
        </div>

        <div class="pos-layout">
            <section class="no-print">
                @unless ($order->isActive())
                    <div class="card empty">
                        <h2>Pesanan sudah {{ $order->statusLabel() }}</h2>
                        <p class="muted">Pesanan ini tidak bisa ditambah item lagi. Buka struk untuk melihat rincian.</p>
                        <a href="{{ route('staff.orders.show', $order) }}" class="btn">Lihat Struk</a>
                    </div>
                @else
                    @if ($menus->isEmpty())
                        <div class="card empty">
                            <h2>Belum ada menu aktif</h2>
                            <p class="muted">Menu akan muncul setelah owner menambahkannya.</p>
                        </div>
                    @else
                        <p class="hint" style="margin: 0 0 10px">
                            Ketuk menu untuk menambah item. Stok langsung berkurang saat item ditambahkan.
                        </p>
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
                                    @disabled($menuStock < 1)
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
                @endunless
            </section>

            <aside class="cart pos-cart">
                <div class="cart-item-top" style="padding-bottom: 6px">
                    <div>
                        <h2>Tagihan Berjalan</h2>
                        <p class="muted" style="margin: 0">{{ $itemCount }} item</p>
                    </div>
                    <span class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
                </div>

                @if ($items->isEmpty())
                    <p class="muted" style="margin: 14px 0">
                        Belum ada item. Pilih menu di sebelah kiri untuk mulai pesanan.
                    </p>
                @else
                    @foreach ($items as $item)
                        @php
                            $hasTopping = $item->variant?->menu?->hasToppingOption() ?? false;
                            $withTopping = (float) $item->topping_price > 0;
                        @endphp

                        <div class="cart-item">
                            <div class="cart-item-top">
                                <div>
                                    <strong>
                                        {{ $item->product_name_snapshot }}
                                        @if ($item->is_addon)
                                            <span class="addon-tag">Tambahan</span>
                                        @endif
                                    </strong>
                                    <div class="muted">{{ $item->variant_name_snapshot }}</div>
                                    @if ($withTopping)
                                        <div class="muted">+ topping Rp {{ number_format((float) $item->topping_price, 0, ',', '.') }}</div>
                                    @endif
                                    <div class="muted">
                                        {{ $item->quantity }} × Rp {{ number_format((float) $item->unit_price + (float) $item->topping_price, 0, ',', '.') }}
                                    </div>
                                </div>
                                <span class="line-total">Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}</span>
                            </div>

                            @if ($editable)
                                <div class="cart-controls">
                                    <form method="POST" action="{{ route('staff.orders.items.update', [$order, $item]) }}" class="cart-controls" style="margin: 0">
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

                                    <form method="POST" action="{{ route('staff.orders.items.destroy', [$order, $item]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-ghost">Hapus</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    @unless ($editable)
                        <p class="hint" style="margin: 12px 0 0">
                            Item lama terkunci karena sudah ada pembayaran. Item baru otomatis ditandai <strong>Tambahan</strong>.
                        </p>
                    @endunless

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
                        <div class="summary-line">
                            <span>Dibayar</span>
                            <span>Rp {{ number_format($paid, 0, ',', '.') }}</span>
                        </div>
                        <div class="summary-line">
                            <span>Sisa tagihan</span>
                            <strong>Rp {{ number_format($due, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    @if ($editable)
                        <details class="form-section">
                            <summary class="muted" style="cursor: pointer">Diskon &amp; catatan</summary>

                            <form method="POST" action="{{ route('staff.orders.discount', $order) }}" style="margin-top: 12px">
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

                            <form method="POST" action="{{ route('staff.orders.notes', $order) }}" style="margin-top: 14px">
                                @csrf
                                @method('PATCH')
                                <div class="field">
                                    <label for="notes">Catatan pesanan</label>
                                    <textarea id="notes" name="notes" maxlength="500" placeholder="Contoh: tanpa saus, dibungkus">{{ $order->notes }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-sm btn-ghost btn-block">Simpan Catatan</button>
                            </form>
                        </details>
                    @elseif ($order->notes)
                        <div class="summary-line" style="margin-top: 10px">
                            <span class="muted">Catatan</span>
                            <span style="text-align: right">{{ $order->notes }}</span>
                        </div>
                    @endif

                    @if ($order->isActive() && $due > 0)
                        <button type="button" class="btn btn-block" style="margin-top: 14px" data-payment-open>
                            Bayar Rp {{ number_format($due, 0, ',', '.') }}
                        </button>
                    @endif

                    @if ($payments->isNotEmpty())
                        <div style="margin-top: 14px">
                            <h2 style="font-size: .9rem; margin-bottom: 4px">Riwayat Pembayaran</h2>
                            @foreach ($payments as $payment)
                                <div class="payment-row">
                                    <div>
                                        <strong>{{ $payment->methodLabel() }}</strong>
                                        <div class="muted">
                                            {{ $payment->paid_at?->format('d/m/Y H:i') }}
                                            @if ($payment->receiver)
                                                · {{ $payment->receiver->name }}
                                            @endif
                                            @if ($payment->reference_code)
                                                · {{ $payment->reference_code }}
                                            @endif
                                        </div>
                                    </div>
                                    <strong>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</strong>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($order->isActive())
                        <div class="cart-controls" style="margin-top: 14px">
                            @if ($order->isOpen())
                                <form method="POST" action="{{ route('staff.orders.status', $order) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="processing">
                                    <button type="submit" class="btn btn-sm">Mulai Masak</button>
                                </form>
                            @elseif ($order->isProcessing())
                                <form method="POST" action="{{ route('staff.orders.status', $order) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="ready">
                                    <button type="submit" class="btn btn-sm">Tandai Siap</button>
                                </form>
                            @endif

                            @if ($order->isReady())
                                @if ($order->canComplete())
                                    <form method="POST" action="{{ route('staff.orders.status', $order) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn btn-sm">Selesaikan Pesanan</button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-sm btn-ghost" disabled>Belum lunas</button>
                                @endif
                            @endif

                            @if ($order->canCancel())
                                <form method="POST" action="{{ route('staff.orders.cancel', $order) }}" onsubmit="return confirm('Batalkan pesanan {{ $order->queueLabel() }}?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-ghost">Batalkan</button>
                                </form>
                            @endif
                        </div>
                    @endif
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

        @if ($order->isActive() && $due > 0)
            <dialog id="payment-dialog" class="sheet" aria-labelledby="payment-title">
                <div class="sheet-handle"></div>

                <div class="sheet-head">
                    <div>
                        <h2 id="payment-title">Pembayaran</h2>
                        <p class="muted">Sisa tagihan Rp {{ number_format($due, 0, ',', '.') }}</p>
                    </div>
                    <button type="button" class="sheet-close" data-payment-close aria-label="Tutup">×</button>
                </div>

                <form
                    method="POST"
                    action="{{ route('staff.orders.pay', $order) }}"
                    id="payment-form"
                    data-due="{{ (int) ceil($due) }}"
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

                    <div class="field">
                        <label for="amount">Jumlah bayar (Rp)</label>
                        <input
                            type="number"
                            id="amount"
                            name="amount"
                            min="1"
                            max="{{ (int) ceil($due) }}"
                            step="1"
                            value="{{ (int) ceil($due) }}"
                        >
                        <span class="muted">Boleh dibayar sebagian.</span>
                    </div>

                    <div id="cash-fields">
                        <div class="field">
                            <label for="amount_tendered">Uang diterima (Rp)</label>
                            <input
                                type="number"
                                id="amount_tendered"
                                name="amount_tendered"
                                min="0"
                                step="1"
                                value="{{ (int) ceil($due) }}"
                            >
                        </div>

                        <div class="cart-controls" style="margin-bottom: 12px">
                            <button type="button" class="btn btn-sm btn-ghost" data-tender="exact">Uang pas</button>
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

                    <button type="submit" class="btn btn-block" style="margin-top: 14px">Terima Pembayaran</button>
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
            const storeUrl = @json(route('staff.orders.items.store', $order));
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
                const amountInput = document.getElementById('amount');
                const tendered = document.getElementById('amount_tendered');
                const changePreview = document.getElementById('change-preview');
                const submitButton = paymentForm.querySelector('button[type="submit"]');
                const due = Number(paymentForm.dataset.due) || 0;

                function currentAmount() {
                    return Math.max(0, Number(amountInput.value) || 0);
                }

                function updatePaymentFields() {
                    const isCash = methodSelect.value === 'cash';

                    cashFields.hidden = !isCash;
                    referenceFields.hidden = isCash;
                    tendered.required = isCash;
                    tendered.disabled = !isCash;

                    updateChange();
                }

                function updateChange() {
                    const amount = currentAmount();

                    tendered.min = String(amount);
                    submitButton.disabled = amount < 1 || amount > due;

                    if (methodSelect.value !== 'cash') {
                        changePreview.textContent = rupiah.format(0);

                        return;
                    }

                    const received = Number(tendered.value) || 0;
                    const change = received - amount;

                    changePreview.textContent = rupiah.format(Math.max(change, 0));
                    submitButton.disabled = submitButton.disabled || change < 0;
                }

                function syncTendered() {
                    const amount = currentAmount();

                    if (Number(tendered.value) < amount) {
                        tendered.value = amount;
                    }

                    updateChange();
                }

                methodSelect.addEventListener('change', updatePaymentFields);
                amountInput.addEventListener('input', syncTendered);
                tendered.addEventListener('input', updateChange);

                document.querySelectorAll('[data-tender]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        tendered.value = button.dataset.tender === 'exact'
                            ? currentAmount()
                            : button.dataset.tender;
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
