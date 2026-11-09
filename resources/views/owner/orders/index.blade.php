@extends('layouts.app')

@section('title', 'Riwayat Transaksi - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Riwayat Transaksi</h1>
            <p class="muted">Semua cabang. Owner hanya dapat melihat.</p>
        </div>
        <span class="badge">Owner</span>
    </div>

    @include('partials.alerts')

    <div class="grid stats">
        <div class="card stat">
            <span class="stat-label">Penjualan Hari Ini</span>
            <strong class="stat-value">Rp {{ number_format($todaySales, 0, ',', '.') }}</strong>
            <span class="muted">Transaksi lunas</span>
        </div>
        <div class="card stat">
            <span class="stat-label">Transaksi Hari Ini</span>
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

        <div class="field">
            <label for="search">Kode order</label>
            <input type="search" id="search" name="search" value="{{ $search }}" placeholder="WJT-...">
        </div>

        <button type="submit" class="btn">Filter</button>
        @if ($branchId || $status || $date || $search !== '')
            <a href="{{ route('owner.orders.index') }}" class="btn btn-ghost">Reset</a>
        @endif
    </form>

    @if ($orders->isEmpty())
        <div class="card empty">
            <h2>Belum ada transaksi</h2>
            <p class="muted">Transaksi dari seluruh cabang akan muncul di sini.</p>
        </div>
    @else
        <div class="card" style="padding: 0">
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Cabang</th>
                            <th>Waktu</th>
                            <th>Kasir</th>
                            <th class="amount">Item</th>
                            <th class="amount">Total</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td><strong>{{ $order->order_code }}</strong></td>
                                <td>{{ $order->branch?->name ?? '—' }}</td>
                                <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                <td>{{ $order->creator?->name ?? '—' }}</td>
                                <td class="amount">{{ $order->items_count }}</td>
                                <td class="amount">Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
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
