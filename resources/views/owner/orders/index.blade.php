@extends('layouts.app')

@section('title', 'Riwayat Pesanan - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Riwayat Pesanan</h1>
            <p class="muted">Semua cabang. Owner hanya dapat melihat.</p>
        </div>
        <span class="badge">Owner</span>
    </div>

    @include('partials.alerts')

    <div class="grid stats">
        <div class="card stat">
            <span class="stat-label">Pembayaran Hari Ini</span>
            <strong class="stat-value">Rp {{ number_format($todaySales, 0, ',', '.') }}</strong>
            <span class="muted">Uang masuk (bukan total pesanan)</span>
        </div>
        <div class="card stat">
            <span class="stat-label">Transaksi Pembayaran</span>
            <strong class="stat-value">{{ $todayCount }}</strong>
            <span class="muted">Semua cabang</span>
        </div>
    </div>

    <form method="GET" action="{{ route('owner.orders.index') }}" class="filter-bar">
        <div class="field">
            <label for="branch_id">Cabang</label>
            <select id="branch_id" name="branch_id">
                <option value="">Semua cabang</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((int) $branchId === (int) $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>

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

        <div class="field" style="flex: 1 1 200px">
            <label for="search">Cari</label>
            <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Nama, #nomor, atau kode order">
        </div>

        <button type="submit" class="btn">Filter</button>
        @if ($branchId || $status || $date || $search !== '')
            <a href="{{ route('owner.orders.index') }}" class="btn btn-ghost">Reset</a>
        @endif
    </form>

    @if ($orders->isEmpty())
        <div class="card empty">
            <h2>Belum ada pesanan</h2>
            <p class="muted">Pesanan dari seluruh cabang akan muncul di sini.</p>
        </div>
    @else
        <div class="show-mobile">
            @foreach ($orders as $order)
                @php
                    $paid = $order->paidAmount();
                    $due = $order->balanceDue();
                @endphp

                <article class="order-card order-{{ $order->status }}" style="margin-bottom: 10px">
                    <div class="order-card-head">
                        <div>
                            <div class="queue-number">{{ $order->queueLabel() }}</div>
                            <h2>{{ $order->customer_name }}</h2>
                        </div>
                        <span class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
                    </div>

                    <div class="order-card-meta">
                        <span>{{ $order->branch?->name ?? '—' }}</span>
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
                        <a href="{{ route('owner.orders.show', $order) }}" class="btn btn-sm btn-ghost">Lihat</a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="card hide-mobile" style="padding: 0">
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Antrian</th>
                            <th>Pelanggan</th>
                            <th>Cabang</th>
                            <th>Waktu</th>
                            <th>Kasir</th>
                            <th class="amount">Item</th>
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
                                <td>{{ $order->branch?->name ?? '—' }}</td>
                                <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                <td>{{ $order->creator?->name ?? '—' }}</td>
                                <td class="amount">{{ $order->items_count }}</td>
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
                                    <a href="{{ route('owner.orders.show', $order) }}" class="btn btn-sm btn-ghost">Lihat</a>
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
                        <a href="{{ $orders->previousPageUrl() }}">← Sebelumnya</a>
                    @endif
                </div>
                <span class="muted">Halaman {{ $orders->currentPage() }} dari {{ $orders->lastPage() }}</span>
                <div>
                    @if ($orders->hasMorePages())
                        <a href="{{ $orders->nextPageUrl() }}">Berikutnya →</a>
                    @endif
                </div>
            </div>
        @endif
    @endif
@endsection
