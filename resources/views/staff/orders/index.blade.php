@extends('layouts.app')

@section('title', 'Riwayat Pesanan - Jagung Bakar WJT')

@section('body_class', 'page-staff-orders')

@push('styles')
    <style>
        @media (max-width: 1099px) {
            body.page-staff-orders .order-card {
                border-left: 4px solid var(--border-warm);
            }

            body.page-staff-orders .order-open { border-left-color: var(--brand-amber); }
            body.page-staff-orders .order-processing { border-left-color: #925807; }
            body.page-staff-orders .order-ready { border-left-color: #3f6b3a; }
            body.page-staff-orders .order-completed { border-left-color: #72645d; }
            body.page-staff-orders .order-cancelled { border-left-color: #b43020; }
        }
    </style>
@endpush

@section('content')
    <div class="page-head">
        <div>
            <h1>Riwayat Pesanan</h1>
            <p class="muted">{{ auth()->user()->branch?->name ?? 'Cabang belum ditentukan' }} · semua pesanan cabang</p>
        </div>
        <a href="{{ route('staff.pos.index') }}" class="btn">Buka Kasir</a>
    </div>

    @include('partials.alerts')

    <form method="GET" action="{{ route('staff.orders.index') }}" class="filter-bar">
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">Semua</option>
                @foreach (\App\Models\Order::STATUSES as $item)
                    <option value="{{ $item }}" @selected($status === $item)>{{ ucfirst($item) }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="date">Tanggal</label>
            <input type="date" id="date" name="date" value="{{ $date }}">
        </div>

        <div class="field filter-search">
            <label for="search">Cari</label>
            <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Nama / nomor / kode">
        </div>

        <button type="submit" class="btn">Filter</button>
        @if ($status || $date || $search !== '')
            <a href="{{ route('staff.orders.index') }}" class="btn btn-ghost">Reset</a>
        @endif
    </form>

    @if ($orders->isEmpty())
        @php
            $hasFilters = (bool) $status || $search !== '' || $date !== '';
        @endphp
        <div class="card empty">
            <h2>{{ $hasFilters ? 'Tidak ada hasil untuk filter ini' : 'Belum ada pesanan' }}</h2>
            <p class="muted">
                {{ $hasFilters ? 'Coba ubah status, tanggal, atau kata kunci pencarian.' : 'Pesanan yang dibuat di kasir akan muncul di sini.' }}
            </p>
            @if ($hasFilters)
                <a href="{{ route('staff.orders.index') }}" class="btn btn-ghost">Reset filter</a>
            @endif
        </div>
    @else
        <div class="show-mobile orders-cards">
            @foreach ($orders as $order)
                @php
                    $paid = $order->paidAmount();
                    $due = $order->balanceDue();
                @endphp

                <article class="order-card order-{{ $order->status }}" style="margin-bottom: 10px">
                    <div class="order-card-head">
                        <div>
                            <div class="queue-number">{{ $order->queueLabel() }}</div>
                            <h2 title="{{ $order->customer_name }}">{{ $order->customer_name }}</h2>
                        </div>
                        <span class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
                    </div>

                    <div class="order-card-meta">
                        <span>{{ $order->orderTypeLabel() }}</span>
                        <span>{{ $order->created_at?->format('d/m/Y H:i') }}</span>
                        <span>{{ $order->items_count }} item</span>
                        @unless ($order->isCancelled())
                            <span class="status status-{{ $order->paymentStatus() }}">{{ $order->paymentStatusLabel() }}</span>
                        @endunless
                    </div>

                    <div class="order-card-money">
                        <span>Total <strong>Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</strong></span>
                        @if ($order->isCancelled())
                            <span class="muted">Dibatalkan, tidak ada tagihan</span>
                        @else
                            <span>Dibayar Rp {{ number_format($paid, 0, ',', '.') }}</span>
                            @if ($due > 0)
                                <span>Sisa Rp {{ number_format($due, 0, ',', '.') }}</span>
                            @endif
                        @endif
                    </div>

                    <div class="order-card-actions">
                        @if ($order->isActive())
                            <a href="{{ route('staff.pos.show', $order) }}" class="btn btn-sm">Buka</a>
                        @else
                            <a href="{{ route('staff.orders.show', $order) }}" class="btn btn-sm btn-ghost">Struk</a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="card hide-mobile orders-table-wrap" style="padding: 0">
            <div class="table-wrap">
                <table class="data orders-table">
                    <thead>
                        <tr>
                            <th>Antrian</th>
                            <th>Pelanggan</th>
                            <th>Tipe</th>
                            <th>Waktu</th>
                            <th class="col-secondary">Kasir</th>
                            <th class="amount col-secondary">Item</th>
                            <th class="amount">Total</th>
                            <th class="amount">Dibayar</th>
                            <th class="amount">Sisa</th>
                            <th>Status</th>
                            <th>Bayar</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            @php
                                $paid = $order->paidAmount();
                                $due = $order->balanceDue();
                            @endphp

                            <tr>
                                <td><strong class="queue-number" style="font-size: 1rem">{{ $order->queueLabel() }}</strong></td>
                                <td>
                                    <strong>{{ $order->customer_name }}</strong>
                                    <div class="muted" style="font-size: 12px">{{ $order->order_code }}</div>
                                </td>
                                <td>{{ $order->orderTypeLabel() }}</td>
                                <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="col-secondary">{{ $order->creator?->name ?? '—' }}</td>
                                <td class="amount col-secondary">{{ $order->items_count }}</td>
                                <td class="amount">Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</td>
                                <td class="amount">{{ $order->isCancelled() ? '—' : 'Rp '.number_format($paid, 0, ',', '.') }}</td>
                                <td class="amount">{{ $order->isCancelled() ? '—' : 'Rp '.number_format($due, 0, ',', '.') }}</td>
                                <td>
                                    <span class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
                                </td>
                                <td>
                                    @if ($order->isCancelled())
                                        <span class="muted">—</span>
                                    @else
                                        <span class="status status-{{ $order->paymentStatus() }}">{{ $order->paymentStatusLabel() }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($order->isActive())
                                        <a href="{{ route('staff.pos.show', $order) }}" class="btn btn-sm">Buka</a>
                                    @else
                                        <a href="{{ route('staff.orders.show', $order) }}" class="btn btn-sm btn-ghost">Struk</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($orders->hasPages())
            <div class="pager">
                <div>
                    @if (! $orders->onFirstPage())
                        <a href="{{ $orders->previousPageUrl() }}" class="btn btn-sm btn-ghost">← Sebelumnya</a>
                    @endif
                </div>
                <span class="muted">Halaman {{ $orders->currentPage() }} dari {{ $orders->lastPage() }}</span>
                <div>
                    @if ($orders->hasMorePages())
                        <a href="{{ $orders->nextPageUrl() }}" class="btn btn-sm btn-ghost">Berikutnya →</a>
                    @endif
                </div>
            </div>
        @endif
    @endif
@endsection
