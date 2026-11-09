@extends('layouts.app')

@section('title', 'Detail Transaksi {{ $order->order_code }} - Jagung Bakar WJT')

@section('content')
    <div class="page-head no-print">
        <div>
            <h1>Detail Transaksi</h1>
            <p class="muted">{{ $order->order_code }} · {{ $order->branch?->name }}</p>
        </div>
        <div class="cart-controls" style="margin-top: 0">
            <a href="{{ route('owner.orders.index') }}" class="btn btn-sm btn-ghost">← Riwayat</a>
            @unless ($order->isDraft())
                <button type="button" class="btn btn-sm" data-print>Cetak</button>
            @endunless
        </div>
    </div>

    @include('partials.alerts')

    @include('partials.order-receipt', ['order' => $order])

    <p class="hint no-print" style="text-align: center; margin-top: 14px">
        Owner hanya dapat melihat transaksi.
    </p>
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
