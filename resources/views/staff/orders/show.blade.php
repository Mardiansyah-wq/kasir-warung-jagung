@extends('layouts.app')

@section('title', 'Struk {{ $order->order_code }} - Jagung Bakar WJT')

@section('content')
    <div class="page-head no-print">
        <div>
            <h1>Detail Transaksi</h1>
            <p class="muted">{{ $order->order_code }}</p>
        </div>
        <div class="cart-controls" style="margin-top: 0">
            <a href="{{ route('staff.orders.index') }}" class="btn btn-sm btn-ghost">← Riwayat</a>
            @if ($order->isDraft())
                <a href="{{ route('staff.pos.index') }}" class="btn btn-sm">Lanjutkan di Kasir</a>
            @else
                <button type="button" class="btn btn-sm" data-print>Cetak Struk</button>
            @endif
        </div>
    </div>

    @include('partials.alerts')

    @include('partials.order-receipt', ['order' => $order])

    <div class="no-print" style="max-width: 460px; margin: 14px auto 0">
        @if ($order->isDraft())
            <p class="hint" style="text-align: center">
                Transaksi ini masih draft dan bisa dilanjutkan dari halaman kasir.
            </p>
        @else
            <p class="hint" style="text-align: center">
                Gunakan tombol cetak untuk mencetak struk melalui browser.
            </p>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-print]').forEach(function (button) {
            button.addEventListener('click', function () {
                window.print();
            });
        });
    </script>
@endpush
