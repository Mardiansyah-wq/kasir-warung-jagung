@extends('layouts.app')

@section('title', 'Pesanan '.$order->queueLabel().' - Jagung Bakar WJT')

@section('content')
    @php
        $paid = $order->paidAmount();
        $due = $order->balanceDue();
    @endphp

    <div class="page-head no-print">
        <div>
            <h1>Pesanan {{ $order->queueLabel() }}</h1>
            <p class="muted">{{ $order->customer_name }} · {{ $order->order_code }}</p>
        </div>
        <div class="cart-controls" style="margin-top: 0">
            <a href="{{ route('staff.orders.index') }}" class="btn btn-sm btn-ghost">← Riwayat</a>
            @if ($order->isActive())
                <a href="{{ route('staff.pos.show', $order) }}" class="btn btn-sm">Buka Tagihan</a>
            @endif
            <button type="button" class="btn btn-sm" data-print>Cetak Struk</button>
        </div>
    </div>

    @include('partials.alerts')

    @include('partials.order-receipt', ['order' => $order])

    <div class="no-print" style="max-width: 460px; margin: 14px auto 0">
        @if ($order->isActive())
            <div class="card">
                <h2 style="font-size: 1rem">Aksi Pesanan</h2>

                <div class="cart-controls" style="margin-top: 10px">
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

                @if ($due > 0)
                    <form method="POST" action="{{ route('staff.orders.pay', $order) }}" style="margin-top: 16px">
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
                            <input type="number" id="amount" name="amount" min="1" max="{{ (int) ceil($due) }}" step="1" value="{{ (int) ceil($due) }}" required>
                        </div>

                        <div class="field">
                            <label for="amount_tendered">Uang diterima (Rp, tunai)</label>
                            <input type="number" id="amount_tendered" name="amount_tendered" min="0" step="1" value="{{ (int) ceil($due) }}">
                        </div>

                        <div class="field">
                            <label for="reference_code">Kode referensi (opsional)</label>
                            <input type="text" id="reference_code" name="reference_code" maxlength="100">
                        </div>

                        <button type="submit" class="btn btn-block">Terima Pembayaran Rp {{ number_format($due, 0, ',', '.') }}</button>
                    </form>
                @endif
            </div>
        @endif

        <p class="hint" style="text-align: center; margin-top: 14px">
            Gunakan tombol cetak untuk mencetak struk melalui browser.
        </p>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-print]').forEach(function (button) {
            button.addEventListener('click', function () {
                window.print();
            });
        });

        const amountInput = document.getElementById('amount');
        const tenderedInput = document.getElementById('amount_tendered');

        if (amountInput && tenderedInput) {
            amountInput.addEventListener('input', function () {
                if (Number(tenderedInput.value) < Number(amountInput.value)) {
                    tenderedInput.value = amountInput.value;
                }
            });
        }
    </script>
@endpush
