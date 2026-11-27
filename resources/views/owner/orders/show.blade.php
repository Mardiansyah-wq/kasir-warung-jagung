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

        body.page-owner-order-show .order-timeline {
            margin-top: 18px;
        }

        body.page-owner-order-show .order-timeline-heading {
            margin: 0 0 4px;
            font-size: 16px;
        }

        body.page-owner-order-show .order-timeline-sub {
            margin: 0 0 14px;
            font-size: 12.5px;
        }

        body.page-owner-order-show .order-timeline-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        body.page-owner-order-show .order-timeline-item {
            position: relative;
            padding: 0 0 18px 24px;
            border-left: 1px solid var(--border-warm);
        }

        body.page-owner-order-show .order-timeline-item:last-child {
            padding-bottom: 0;
            border-left-color: transparent;
        }

        body.page-owner-order-show .order-timeline-item::before {
            position: absolute;
            top: 3px;
            left: -5px;
            width: 9px;
            height: 9px;
            border: 2px solid var(--surface);
            border-radius: 50%;
            background: var(--brand-amber);
            box-shadow: 0 0 0 1px var(--border-warm);
            content: '';
        }

        body.page-owner-order-show .order-timeline-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        body.page-owner-order-show .order-timeline-time {
            color: var(--muted-warm);
            font-size: 11.5px;
        }

        body.page-owner-order-show .order-timeline-detail {
            margin-top: 6px;
            font-size: 13px;
            overflow-wrap: anywhere;
        }

        body.page-owner-order-show .order-timeline-actor {
            margin-top: 3px;
            color: var(--muted-warm);
            font-size: 11.5px;
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

    <section class="card order-timeline no-print">
        <h2 class="order-timeline-heading">Riwayat Perubahan</h2>
        <p class="muted order-timeline-sub">Siapa melakukan apa dan kapan.</p>

        @if ($order->editLogs->isNotEmpty())
            <ol class="order-timeline-list">
                @foreach ($order->editLogs as $log)
                    <li class="order-timeline-item">
                        <div class="order-timeline-row">
                            <span class="status {{ $log->actionTone() }}">{{ $log->actionLabel() }}</span>
                            <time class="order-timeline-time" datetime="{{ $log->created_at?->toIso8601String() }}">
                                {{ $log->created_at?->format('d/m/Y H:i') ?? '—' }}
                            </time>
                        </div>
                        <div class="order-timeline-detail">{{ $log->changeSummary($variantLabels->all()) }}</div>
                        <div class="order-timeline-actor">Oleh {{ $log->user?->name ?? 'Sistem' }}</div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="muted">Belum ada riwayat perubahan.</p>
        @endif
    </section>

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
