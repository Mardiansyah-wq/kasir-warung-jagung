@php
    $paidAmount = $order->paidAmount();
    $balanceDue = $order->balanceDue();
    $payments = $order->payments
        ->where('status', \App\Models\Payment::STATUS_PAID)
        ->sortBy('paid_at');
    $hasTopping = static fn ($item) => (float) $item->topping_price > 0;
@endphp

<div class="receipt">
    <div class="receipt-head">
        <h1>{{ config('app.name', 'Jagung Bakar WJT') }}</h1>
        <div class="receipt-meta">
            <strong>{{ $order->branch?->name ?? 'Cabang' }}</strong>
            @if ($order->branch?->address)
                <span>{{ $order->branch->address }}</span>
            @endif
            @if ($order->branch?->phone)
                <span>{{ $order->branch->phone }}</span>
            @endif
        </div>
    </div>

    <div class="receipt-meta" style="padding: 10px 0; border-bottom: 1px dashed var(--border)">
        <div class="receipt-line">
            <span>Nomor Antrian</span>
            <strong class="money-big">{{ $order->queueLabel() }}</strong>
        </div>
        <div class="receipt-line">
            <span>Nama Pelanggan</span>
            <strong>{{ $order->customer_name }}</strong>
        </div>
        <div class="receipt-line">
            <span>Tipe Pesanan</span>
            <span>{{ $order->orderTypeLabel() }}</span>
        </div>
        <div class="receipt-line">
            <span>Kode</span>
            <span>{{ $order->order_code }}</span>
        </div>
        <div class="receipt-line">
            <span>Waktu</span>
            <span>{{ $order->created_at?->format('d/m/Y H:i') }}</span>
        </div>
        <div class="receipt-line">
            <span>Kasir</span>
            <span>{{ $order->creator?->name ?? '—' }}</span>
        </div>
        <div class="receipt-line">
            <span>Status Pesanan</span>
            <span class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
        </div>
        <div class="receipt-line">
            <span>Status Bayar</span>
            <span class="status status-{{ $order->paymentStatus() }}">{{ $order->paymentStatusLabel() }}</span>
        </div>
    </div>

    <div class="receipt-items">
        @forelse ($order->items as $item)
            <div class="receipt-line">
                <div>
                    <strong>
                        {{ $item->quantity }}× {{ $item->product_name_snapshot }}
                        @if ($item->is_addon)
                            <span class="addon-tag">Tambahan</span>
                        @endif
                    </strong>
                    <div class="muted">{{ $item->variant_name_snapshot }}</div>
                    @if ($hasTopping($item))
                        <div class="muted">+ topping Rp {{ number_format((float) $item->topping_price, 0, ',', '.') }}</div>
                    @endif
                    <div class="muted">
                        Rp {{ number_format((float) $item->unit_price + (float) $item->topping_price, 0, ',', '.') }} / item
                    </div>
                </div>
                <strong>Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}</strong>
            </div>
        @empty
            <p class="muted">Belum ada item.</p>
        @endforelse
    </div>

    <div style="padding-top: 10px">
        <div class="receipt-line">
            <span>Subtotal</span>
            <span>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</span>
        </div>
        @if ((float) $order->discount_amount > 0)
            <div class="receipt-line">
                <span>Diskon</span>
                <span>− Rp {{ number_format((float) $order->discount_amount, 0, ',', '.') }}</span>
            </div>
        @endif
        <div class="receipt-line total">
            <span>Total</span>
            <span>Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</span>
        </div>
        <div class="receipt-line">
            <span>Dibayar</span>
            <span>Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
        </div>
        <div class="receipt-line">
            <span>Sisa Tagihan</span>
            <strong>Rp {{ number_format($balanceDue, 0, ',', '.') }}</strong>
        </div>
    </div>

    @if ($payments->isNotEmpty())
        <div style="padding-top: 10px; margin-top: 10px; border-top: 1px dashed var(--border)">
            <div class="muted" style="margin-bottom: 4px">Riwayat Pembayaran</div>

            @foreach ($payments as $payment)
                <div style="padding: 6px 0; border-bottom: 1px dotted var(--border)">
                    <div class="receipt-line">
                        <span>{{ $payment->methodLabel() }}</span>
                        <strong>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="receipt-line muted" style="font-size: 12px">
                        <span>{{ $payment->paid_at?->format('d/m/Y H:i') }}</span>
                        <span>{{ $payment->receiver?->name ?? '—' }}</span>
                    </div>
                    @if ($payment->payment_method === 'cash')
                        <div class="receipt-line" style="font-size: 12px">
                            <span>Diterima</span>
                            <span>Rp {{ number_format((float) $payment->amount_tendered, 0, ',', '.') }}</span>
                        </div>
                        <div class="receipt-line" style="font-size: 12px">
                            <span>Kembalian</span>
                            <span>Rp {{ number_format((float) $payment->change_amount, 0, ',', '.') }}</span>
                        </div>
                    @elseif ($payment->reference_code)
                        <div class="receipt-line" style="font-size: 12px">
                            <span>Referensi</span>
                            <span>{{ $payment->reference_code }}</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if ($order->notes)
        <div style="padding-top: 10px; margin-top: 10px; border-top: 1px dashed var(--border)">
            <div class="muted">Catatan</div>
            <p style="margin: 2px 0 0">{{ $order->notes }}</p>
        </div>
    @endif

    <p class="receipt-foot">
        Terima kasih sudah berbelanja.<br>
        {{ $order->statusLabel() }} · {{ $order->queueLabel() }} · {{ $order->order_code }}
    </p>
</div>
