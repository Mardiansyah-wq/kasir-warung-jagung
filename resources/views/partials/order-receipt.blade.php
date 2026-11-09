@php
    $paid = $order->payments->firstWhere('status', \App\Models\Payment::STATUS_PAID);
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
            <span>Kode</span>
            <strong>{{ $order->order_code }}</strong>
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
            <span>Status</span>
            <span class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
        </div>
    </div>

    <div class="receipt-items">
        @forelse ($order->items as $item)
            <div class="receipt-line">
                <div>
                    <strong>{{ $item->quantity }}× {{ $item->product_name_snapshot }}</strong>
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
    </div>

    @if ($paid)
        <div style="padding-top: 10px; margin-top: 10px; border-top: 1px dashed var(--border)">
            <div class="receipt-line">
                <span>Metode</span>
                <strong>{{ $paid->methodLabel() }}</strong>
            </div>
            @if ($paid->payment_method === 'cash')
                <div class="receipt-line">
                    <span>Diterima</span>
                    <span>Rp {{ number_format((float) $paid->amount_tendered, 0, ',', '.') }}</span>
                </div>
                <div class="receipt-line">
                    <span>Kembalian</span>
                    <span>Rp {{ number_format((float) $paid->change_amount, 0, ',', '.') }}</span>
                </div>
            @endif
            @if ($paid->reference_code)
                <div class="receipt-line">
                    <span>Referensi</span>
                    <span>{{ $paid->reference_code }}</span>
                </div>
            @endif
            <div class="receipt-line">
                <span>Dibayar</span>
                <span>{{ $paid->paid_at?->format('d/m/Y H:i') }}</span>
            </div>
        </div>
    @elseif ($order->isFinalized())
        <div class="receipt-line">
            <span>Metode</span>
            <span>—</span>
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
        {{ $order->statusLabel() }} · {{ $order->order_code }}
    </p>
</div>
