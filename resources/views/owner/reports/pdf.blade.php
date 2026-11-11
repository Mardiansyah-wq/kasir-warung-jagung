<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Harian {{ $report['date'] }}</title>
    <style>
        @page {
            margin: 28px 30px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1f2937;
        }

        h1 {
            margin: 0;
            font-size: 18px;
            color: #7c2d12;
        }

        h2 {
            margin: 0 0 6px;
            font-size: 12px;
            color: #7c2d12;
        }

        p {
            margin: 0;
        }

        .muted {
            color: #6b7280;
            font-size: 9px;
        }

        .head {
            padding-bottom: 10px;
            border-bottom: 2px solid #7c2d12;
        }

        .head-table {
            width: 100%;
            border-collapse: collapse;
        }

        .head-table td {
            vertical-align: top;
            padding: 0;
        }

        .head-right {
            text-align: right;
            font-size: 9px;
            color: #6b7280;
        }

        .meta {
            margin: 10px 0 14px;
            padding: 8px 10px;
            background: #fff7ed;
            border: 1px solid #fed7aa;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 2px 0;
            font-size: 9.5px;
        }

        .meta-table td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .section {
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        table.grid {
            width: 100%;
            border-collapse: collapse;
        }

        table.grid th,
        table.grid td {
            padding: 5px 6px;
            border: 1px solid #e5e7eb;
            text-align: left;
            vertical-align: top;
        }

        table.grid thead th {
            background: #f3f4f6;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #374151;
        }

        table.grid tbody tr:nth-child(even) td {
            background: #fafafa;
        }

        table.grid tfoot th {
            background: #f3f4f6;
            font-size: 9px;
        }

        .right {
            text-align: right;
            white-space: nowrap;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
        }

        .summary td {
            width: 25%;
            padding: 7px 8px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .summary .label {
            display: block;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6b7280;
        }

        .summary .value {
            display: block;
            margin-top: 2px;
            font-size: 12px;
            font-weight: bold;
        }

        .two-col {
            width: 100%;
            border-collapse: collapse;
        }

        .two-col > tbody > tr > td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }

        .two-col > tbody > tr > td:first-child {
            padding-right: 8px;
        }

        .footer {
            margin-top: 16px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            font-size: 8.5px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="head">
        <table class="head-table">
            <tr>
                <td>
                    <h1>Laporan Harian</h1>
                    <p class="muted">Jagung Bakar WJT</p>
                </td>
                <td class="head-right">
                    Dicetak {{ now()->format('d/m/Y H:i') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="meta">
        <table class="meta-table">
            <tr>
                <td><strong>Tanggal</strong> {{ $report['date_label'] }}</td>
                <td><strong>Cabang</strong> {{ $report['branch_label'] }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table class="summary">
            <tr>
                <td>
                    <span class="label">Uang Masuk</span>
                    <span class="value">Rp {{ number_format($report['summary']['revenue'], 0, ',', '.') }}</span>
                    <span class="muted">{{ $report['summary']['paid_orders'] }} transaksi dibayar</span>
                </td>
                <td>
                    <span class="label">Nilai Pesanan</span>
                    <span class="value">Rp {{ number_format($report['summary']['gross_sales'], 0, ',', '.') }}</span>
                    <span class="muted">Tanpa pesanan batal</span>
                </td>
                <td>
                    <span class="label">Total Diskon</span>
                    <span class="value">Rp {{ number_format($report['summary']['discounts'], 0, ',', '.') }}</span>
                    <span class="muted">Potongan harga</span>
                </td>
                <td>
                    <span class="label">Sisa Tagihan</span>
                    <span class="value">Rp {{ number_format($report['summary']['outstanding'], 0, ',', '.') }}</span>
                    <span class="muted">Belum dibayar penuh</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Total Pesanan</span>
                    <span class="value">{{ $report['summary']['orders_total'] }}</span>
                    <span class="muted">Semua status</span>
                </td>
                <td>
                    <span class="label">Selesai</span>
                    <span class="value">{{ $report['summary']['orders_completed'] }}</span>
                    <span class="muted">Pesanan selesai</span>
                </td>
                <td>
                    <span class="label">Berjalan</span>
                    <span class="value">{{ $report['summary']['orders_active'] }}</span>
                    <span class="muted">Aktif / diproses / siap</span>
                </td>
                <td>
                    <span class="label">Batal</span>
                    <span class="value">{{ $report['summary']['orders_cancelled'] }}</span>
                    <span class="muted">Tidak dihitung penjualan</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Rincian Pesanan</h2>

        @if ($report['orders']->isEmpty())
            <p class="muted">Belum ada pesanan pada tanggal ini.</p>
        @else
            <table class="grid">
                <thead>
                    <tr>
                        <th>Antrian</th>
                        <th>Pelanggan</th>
                        <th>Cabang</th>
                        <th>Waktu</th>
                        <th>Kasir</th>
                        <th class="right">Item</th>
                        <th class="right">Total</th>
                        <th class="right">Dibayar</th>
                        <th>Status</th>
                        <th>Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['orders'] as $order)
                        <tr>
                            <td>{{ $order->queueLabel() }}</td>
                            <td>{{ $order->customer_name }}</td>
                            <td>{{ $order->branch?->name ?? '-' }}</td>
                            <td>{{ $order->created_at?->format('H:i') ?? '-' }}</td>
                            <td>{{ $order->creator?->name ?? '-' }}</td>
                            <td class="right">{{ $order->items_count }}</td>
                            <td class="right">{{ number_format((float) $order->total_amount, 0, ',', '.') }}</td>
                            <td class="right">
                                {{ $order->isCancelled() ? '-' : number_format($order->paidAmount(), 0, ',', '.') }}
                            </td>
                            <td>{{ $order->statusLabel() }}</td>
                            <td>{{ $order->isCancelled() ? '-' : $order->paymentStatusLabel() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="section">
        <table class="two-col">
            <tr>
                <td>
                    <h2>Metode Pembayaran</h2>
                    <table class="grid">
                        <thead>
                            <tr>
                                <th>Metode</th>
                                <th class="right">Transaksi</th>
                                <th class="right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($report['methods'] as $method)
                                <tr>
                                    <td>{{ $method['label'] }}</td>
                                    <td class="right">{{ $method['count'] }}</td>
                                    <td class="right">{{ number_format($method['amount'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total</th>
                                <th class="right">{{ $report['summary']['paid_orders'] }}</th>
                                <th class="right">{{ number_format($report['summary']['revenue'], 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </td>
                <td>
                    <h2>Produk Terjual</h2>

                    @if ($report['products']->isEmpty())
                        <p class="muted">Belum ada produk terjual pada tanggal ini.</p>
                    @else
                        <table class="grid">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="right">Qty</th>
                                    <th class="right">Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($report['products'] as $product)
                                    <tr>
                                        <td>
                                            {{ $product['name'] }}
                                            @if ($product['variant'] !== '')
                                                <br><span class="muted">{{ $product['variant'] }}</span>
                                            @endif
                                        </td>
                                        <td class="right">{{ $product['quantity'] }}</td>
                                        <td class="right">{{ number_format($product['revenue'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Uang masuk dihitung dari pembayaran yang diterima pada tanggal tersebut. Nilai pesanan dihitung
        dari pesanan dengan tanggal usaha yang sama, tanpa pesanan batal.
    </div>
</body>
</html>
