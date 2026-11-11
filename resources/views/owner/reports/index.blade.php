@extends('layouts.app')

@section('title', 'Laporan Harian - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Laporan Harian</h1>
            <p class="muted">Rekap pesanan, uang masuk, dan produk terjual per tanggal.</p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap">
            <a
                href="{{ route('owner.reports.pdf', ['date' => $date, 'branch_id' => $branchId]) }}"
                class="btn btn-ghost"
            >Unduh PDF</a>
            <a
                href="{{ route('owner.reports.excel', ['date' => $date, 'branch_id' => $branchId]) }}"
                class="btn"
            >Unduh Excel</a>
        </div>
    </div>

    @include('partials.alerts')

    <form method="GET" action="{{ route('owner.reports.index') }}" class="filter-bar">
        <div class="field">
            <label for="date">Tanggal</label>
            <input type="date" id="date" name="date" value="{{ $date }}" required>
        </div>

        <div class="field">
            <label for="branch_id">Cabang</label>
            <select id="branch_id" name="branch_id">
                <option value="">Semua cabang</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((int) $branchId === (int) $branch->id)>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn">Tampilkan</button>

        @if ($branchId || $date !== today()->toDateString())
            <a href="{{ route('owner.reports.index') }}" class="btn btn-ghost">Hari Ini</a>
        @endif
    </form>

    <div class="grid stats">
        <div class="card stat">
            <span class="stat-label">Uang Masuk</span>
            <strong class="stat-value">Rp {{ number_format($report['summary']['revenue'], 0, ',', '.') }}</strong>
            <span class="muted">{{ $report['summary']['paid_orders'] }} transaksi dibayar</span>
        </div>
        <div class="card stat">
            <span class="stat-label">Nilai Pesanan</span>
            <strong class="stat-value">Rp {{ number_format($report['summary']['gross_sales'], 0, ',', '.') }}</strong>
            <span class="muted">Tanpa pesanan batal</span>
        </div>
        <div class="card stat">
            <span class="stat-label">Rata-rata / Transaksi</span>
            <strong class="stat-value">Rp {{ number_format($report['summary']['average'], 0, ',', '.') }}</strong>
            <span class="muted">Dari uang masuk</span>
        </div>
        <div class="card stat">
            <span class="stat-label">Sisa Tagihan</span>
            <strong class="stat-value">Rp {{ number_format($report['summary']['outstanding'], 0, ',', '.') }}</strong>
            <span class="muted">Belum dibayar penuh</span>
        </div>
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap">
            <div>
                <h2>{{ $report['date_label'] }}</h2>
                <p class="muted">{{ $report['branch_label'] }} · {{ $report['summary']['orders_total'] }} pesanan</p>
            </div>
            <div style="text-align: right">
                <p class="muted">
                    Selesai {{ $report['summary']['orders_completed'] }} ·
                    Berjalan {{ $report['summary']['orders_active'] }} ·
                    Batal {{ $report['summary']['orders_cancelled'] }}
                </p>
                <p class="muted">Total diskon Rp {{ number_format($report['summary']['discounts'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="card" style="padding: 0">
        <div style="padding: 16px 16px 0">
            <h2>Rincian Pesanan</h2>
            <p class="muted">Pesanan dengan tanggal usaha {{ $report['date'] }}.</p>
        </div>

        @if ($report['orders']->isEmpty())
            <div class="empty">
                <p class="muted">Belum ada pesanan pada tanggal ini.</p>
            </div>
        @else
            <div class="table-wrap" style="margin-top: 12px">
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
                            <th>Status</th>
                            <th>Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['orders'] as $order)
                            <tr>
                                <td><strong>{{ $order->queueLabel() }}</strong></td>
                                <td>
                                    {{ $order->customer_name }}
                                    <div class="muted" style="font-size: 12px">{{ $order->order_code }}</div>
                                </td>
                                <td>{{ $order->branch?->name ?? '—' }}</td>
                                <td>{{ $order->created_at?->format('H:i') ?? '—' }}</td>
                                <td>{{ $order->creator?->name ?? '—' }}</td>
                                <td class="amount">{{ $order->items_count }}</td>
                                <td class="amount">Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</td>
                                <td class="amount">
                                    {{ $order->isCancelled() ? '—' : 'Rp '.number_format($order->paidAmount(), 0, ',', '.') }}
                                </td>
                                <td>
                                    <span class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
                                </td>
                                <td>
                                    @if ($order->isCancelled())
                                        <span class="muted">—</span>
                                    @else
                                        <span class="status status-{{ $order->paymentStatus() }}">
                                            {{ $order->paymentStatusLabel() }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="grid" style="align-items: start">
        <div class="card" style="padding: 0">
            <div style="padding: 16px 16px 0">
                <h2>Metode Pembayaran</h2>
                <p class="muted">Uang yang benar-benar diterima pada tanggal ini.</p>
            </div>

            <div class="table-wrap" style="margin-top: 12px">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Metode</th>
                            <th class="amount">Transaksi</th>
                            <th class="amount">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['methods'] as $method)
                            <tr>
                                <td>{{ $method['label'] }}</td>
                                <td class="amount">{{ $method['count'] }}</td>
                                <td class="amount">Rp {{ number_format($method['amount'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th class="amount">{{ $report['summary']['paid_orders'] }}</th>
                            <th class="amount">Rp {{ number_format($report['summary']['revenue'], 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card" style="padding: 0">
            <div style="padding: 16px 16px 0">
                <h2>Produk Terjual</h2>
                <p class="muted">Diurutkan dari pendapatan terbesar.</p>
            </div>

            @if ($report['products']->isEmpty())
                <div class="empty">
                    <p class="muted">Belum ada produk terjual pada tanggal ini.</p>
                </div>
            @else
                <div class="table-wrap" style="margin-top: 12px">
                    <table class="data">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="amount">Qty</th>
                                <th class="amount">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($report['products'] as $product)
                                <tr>
                                    <td>
                                        {{ $product['name'] }}
                                        @if ($product['variant'] !== '')
                                            <div class="muted" style="font-size: 12px">{{ $product['variant'] }}</div>
                                        @endif
                                    </td>
                                    <td class="amount">{{ $product['quantity'] }}</td>
                                    <td class="amount">Rp {{ number_format($product['revenue'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
