@extends('layouts.app')

@section('title', 'Laporan Mingguan - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Laporan Mingguan</h1>
            <p class="muted">Rekap Senin–Minggu untuk pesanan, uang masuk, dan produk terjual.</p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap">
            <a
                href="{{ route('owner.reports.weekly.pdf', ['week' => $week, 'branch_id' => $branchId]) }}"
                class="btn btn-ghost"
            >Unduh PDF</a>
            <a
                href="{{ route('owner.reports.weekly.excel', ['week' => $week, 'branch_id' => $branchId]) }}"
                class="btn"
            >Unduh Excel</a>
        </div>
    </div>

    @include('partials.alerts')

    <div class="chips">
        <a
            href="{{ route('owner.reports.index', ['date' => $week, 'branch_id' => $branchId]) }}"
            class="chip"
        >Harian</a>
        <a
            href="{{ route('owner.reports.weekly', ['week' => $week, 'branch_id' => $branchId]) }}"
            class="chip active"
        >Mingguan</a>
    </div>

    <form method="GET" action="{{ route('owner.reports.weekly') }}" class="filter-bar">
        <div class="field">
            <label for="week">Tanggal dalam minggu</label>
            <input type="date" id="week" name="week" value="{{ $week }}" required>
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

        @if ($branchId || $week !== today()->toDateString())
            <a href="{{ route('owner.reports.weekly') }}" class="btn btn-ghost">Minggu Ini</a>
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
            <span class="stat-label">Rata-rata / Hari</span>
            <strong class="stat-value">Rp {{ number_format($report['summary']['average_per_day'], 0, ',', '.') }}</strong>
            <span class="muted">Dari uang masuk</span>
        </div>
        <div class="card stat">
            <span class="stat-label">Sisa Tagihan</span>
            <strong class="stat-value">Rp {{ number_format($report['summary']['outstanding'], 0, ',', '.') }}</strong>
            <span class="muted">Belum dibayar penuh</span>
        </div>
    </div>

    @php($comparison = $report['comparison'])
    <div class="card">
        <div style="display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap">
            <div>
                <h2>{{ $report['week_label'] }}</h2>
                <p class="muted">
                    Minggu ke-{{ $report['week_number'] }} · {{ $report['branch_label'] }} ·
                    {{ $report['summary']['orders_total'] }} pesanan
                </p>
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

        <div class="form-section">
            <p class="muted">
                Dibanding minggu sebelumnya:
                Rp {{ number_format($comparison['previous_revenue'], 0, ',', '.') }}
                @if ($comparison['revenue_delta_percent'] !== null)
                    @php($percent = $comparison['revenue_delta_percent'])
                    <strong style="color: {{ $percent >= 0 ? '#166534' : '#991b1b' }}">
                        ({{ $percent >= 0 ? '+' : '' }}{{ number_format($percent, 1, ',', '.') }}%)
                    </strong>
                @endif
                · {{ $comparison['orders_delta'] >= 0 ? '+' : '' }}{{ $comparison['orders_delta'] }} pesanan
            </p>
            <p class="muted">
                Hari terbaik: {{ $report['summary']['best_day_label'] }}
                (Rp {{ number_format($report['summary']['best_day_revenue'], 0, ',', '.') }})
            </p>
        </div>
    </div>

    <div class="card" style="padding: 0">
        <div style="padding: 16px 16px 0">
            <h2>Rekap Harian</h2>
            <p class="muted">Uang masuk dihitung dari pembayaran yang diterima pada hari tersebut.</p>
        </div>

        <div class="table-wrap" style="margin-top: 12px">
            <table class="data">
                <thead>
                    <tr>
                        <th>Hari</th>
                        <th>Tanggal</th>
                        <th class="amount">Pesanan</th>
                        <th class="amount">Dibayar</th>
                        <th>Uang Masuk</th>
                        <th class="amount">Nilai Pesanan</th>
                        <th class="amount">Batal</th>
                    </tr>
                </thead>
                <tbody>
                    @php($maxRevenue = collect($report['days'])->max('revenue') ?: 1)
                    @foreach ($report['days'] as $day)
                        <tr @if ($day['is_today']) style="background: #fff7ed" @endif>
                            <td>
                                <strong>{{ $day['day_label'] }}</strong>
                                @if ($day['is_today'])
                                    <span class="badge" style="margin-left: 4px">Hari ini</span>
                                @endif
                            </td>
                            <td>{{ $day['date_label'] }}</td>
                            <td class="amount">{{ $day['orders_total'] }}</td>
                            <td class="amount">{{ $day['paid_orders'] }}</td>
                            <td style="min-width: 170px">
                                <div style="display: flex; align-items: center; gap: 8px">
                                    <div style="flex: 1; height: 6px; border-radius: 999px; background: #f3f4f6; overflow: hidden">
                                        <div style="height: 100%; width: {{ $day['revenue'] > 0 ? max(4, ($day['revenue'] / $maxRevenue) * 100) : 0 }}%; background: var(--brand)"></div>
                                    </div>
                                    <span style="white-space: nowrap; font-size: 13px">
                                        Rp {{ number_format($day['revenue'], 0, ',', '.') }}
                                    </span>
                                </div>
                            </td>
                            <td class="amount">Rp {{ number_format($day['gross_sales'], 0, ',', '.') }}</td>
                            <td class="amount">{{ $day['orders_cancelled'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">Total</th>
                        <th class="amount">{{ $report['summary']['orders_total'] }}</th>
                        <th class="amount">{{ $report['summary']['paid_orders'] }}</th>
                        <th>Rp {{ number_format($report['summary']['revenue'], 0, ',', '.') }}</th>
                        <th class="amount">Rp {{ number_format($report['summary']['gross_sales'], 0, ',', '.') }}</th>
                        <th class="amount">{{ $report['summary']['orders_cancelled'] }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="grid" style="align-items: start">
        <div class="card" style="padding: 0">
            <div style="padding: 16px 16px 0">
                <h2>Metode Pembayaran</h2>
                <p class="muted">Uang yang benar-benar diterima selama seminggu.</p>
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
                    <p class="muted">Belum ada produk terjual pada minggu ini.</p>
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
