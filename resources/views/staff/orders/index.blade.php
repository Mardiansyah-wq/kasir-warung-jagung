@extends('layouts.app')

@section('title', 'Riwayat Transaksi - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Riwayat Transaksi</h1>
            <p class="muted">{{ auth()->user()->branch?->name ?? 'Cabang belum ditentukan' }}</p>
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
            <label for="search">Kode order</label>
            <input type="search" id="search" name="search" value="{{ $search }}" placeholder="WJT-...">
        </div>

        <button type="submit" class="btn">Filter</button>
        @if ($status || $search !== '')
            <a href="{{ route('staff.orders.index') }}" class="btn btn-ghost">Reset</a>
        @endif
    </form>

    @if ($orders->isEmpty())
        <div class="card empty">
            <h2>Belum ada transaksi</h2>
            <p class="muted">Transaksi yang dibuat di kasir akan muncul di sini.</p>
        </div>
    @else
        <div class="card" style="padding: 0">
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Kode</th>
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
                                <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                <td>{{ $order->creator?->name ?? '—' }}</td>
                                <td class="amount">{{ $order->items_count }}</td>
                                <td class="amount">Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('staff.orders.show', $order) }}" class="btn btn-sm btn-ghost">Lihat</a>
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
