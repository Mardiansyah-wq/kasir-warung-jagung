@extends('layouts.app')

@section('title', 'Pesanan '.$order->queueLabel().' - Jagung Bakar WJT')

@section('body_class', 'page-owner-order-show')

@push('styles')
    <style>
        body.page-owner-order-show .page-head .cart-controls {
            width: 100%;
        }

        body.page-owner-order-show .page-head .cart-controls .btn {
            flex: 1 1 auto;
        }

        body.page-owner-order-show .order-title-name {
            overflow-wrap: anywhere;
        }

        @media (min-width: 640px) {
            body.page-owner-order-show .page-head .cart-controls {
                width: auto;
            }
        }
    </style>
@endpush

@section('content')
    <div class="page-head no-print">
        <div>
            <h1>Pesanan {{ $order->queueLabel() }}</h1>
            <p class="muted order-title-name">{{ $order->customer_name }} · {{ $order->branch?->name }} · {{ $order->order_code }}</p>
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
