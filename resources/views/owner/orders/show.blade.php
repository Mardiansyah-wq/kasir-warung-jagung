@extends('layouts.app')

@section('title', 'Pesanan '.$order->queueLabel().' - Jagung Bakar WJT')

@section('content')
    <div class="page-head no-print">
        <div>
            <h1>Pesanan {{ $order->queueLabel() }}</h1>
            <p class="muted">{{ $order->customer_name }} · {{ $order->branch?->name }} · {{ $order->order_code }}</p>
        </div>
        <div class="cart-controls" style="margin-top: 0">
            <a href="{{ route('owner.orders.index') }}" class="btn btn-sm btn-ghost">← Riwayat</a>
            <button type="button" class="btn btn-sm" data-print>Cetak</button>
        </div>
    </div>

    @include('partials.alerts')

    @include('partials.order-receipt', ['order' => $order])

    <p class="hint no-print" style="text-align: center; margin-top: 14px">
        Owner hanya dapat melihat pesanan dan pembayaran.
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
