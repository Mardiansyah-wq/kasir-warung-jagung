@extends('layouts.app')

@section('title', 'Total Penjualan - Jagung Bakar WJT')

@section('body_class', 'page-dashboard')

@push('styles')
    <style>
        body.page-dashboard {
            background: var(--bg-warm);
            color: var(--text-warm);
        }

        .dash-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 20px;
        }

        .dash-title {
            margin: 0 0 6px;
            font-family: var(--font-serif);
            font-size: clamp(1.75rem, 3.1vw, 2.5rem);
            font-weight: 700;
            line-height: 1.15;
            letter-spacing: -.01em;
            color: var(--brand-brown);
        }

        .dash-sub {
            margin: 0;
            font-size: 14.5px;
            color: var(--muted-warm);
        }

        .dash-download {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 11px 18px;
            border-radius: 8px;
            background: var(--brand-red);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(180, 48, 32, .2);
        }

        .dash-download:hover {
            background: var(--brand-red-dark);
        }

        .dash-download .nav-icon {
            width: 17px;
            height: 17px;
        }

        .dash-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }

        .dash-ranges {
            display: inline-flex;
            gap: 2px;
            padding: 4px;
            border-radius: 10px;
            background: #ebe2ca;
        }

        .dash-range {
            padding: 9px 16px;
            border-radius: 7px;
            color: #6b5b4d;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
        }

        .dash-range:hover {
            color: var(--brand-brown);
        }

        .dash-range.is-active {
            background: var(--surface-warm);
            color: var(--brand-brown);
            box-shadow: 0 1px 2px rgba(60, 40, 32, .12);
        }

        .dash-tools {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dash-sr {
            position: absolute;
            width: 1px;
            height: 1px;
            margin: -1px;
            padding: 0;
            overflow: hidden;
            clip: rect(0 0 0 0);
            white-space: nowrap;
            border: 0;
        }

        .dash-box {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 10px 14px;
            border: 1px solid var(--border-warm);
            border-radius: 8px;
            background: var(--surface-warm);
            color: var(--text-warm);
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 600;
            white-space: nowrap;
        }

        .dash-box .nav-icon {
            width: 16px;
            height: 16px;
            color: #8a7a6a;
        }

        select.dash-box {
            padding-right: 34px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%238a7a6a' stroke-width='1.8' stroke-linecap='round'%3E%3Cpath d='M6 9.5 12 15.5l6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 15px;
            appearance: none;
            cursor: pointer;
        }

        .dash-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .dash-stat {
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 148px;
            padding: 20px;
            border: 1px solid var(--border-warm);
            border-radius: 12px;
            background: var(--surface-warm);
        }

        .dash-stat.is-gold {
            border-color: #cba740;
            background: var(--brand-amber);
            color: var(--brand-brown);
        }

        .dash-stat-icon {
            position: absolute;
            top: 18px;
            right: 18px;
            color: #a08e6d;
        }

        .dash-stat.is-gold .dash-stat-icon {
            color: rgba(60, 40, 32, .5);
        }

        .dash-stat-icon .nav-icon {
            width: 20px;
            height: 20px;
        }

        .dash-stat-label {
            padding-right: 28px;
            font-size: 13.5px;
            font-weight: 600;
            color: #5c4a3d;
        }

        .dash-stat.is-gold .dash-stat-label {
            color: #4a3423;
        }

        .dash-stat-value {
            margin-top: 8px;
            font-family: var(--font-serif);
            font-size: clamp(1.5rem, 2.4vw, 2.1rem);
            font-weight: 700;
            line-height: 1.15;
            letter-spacing: -.01em;
            color: var(--brand-brown);
            font-variant-numeric: tabular-nums;
        }

        .dash-stat-note {
            display: block;
            margin-top: auto;
            padding-top: 12px;
            font-size: 12.5px;
            line-height: 1.45;
            color: var(--muted-warm);
        }

        .dash-stat.is-gold .dash-stat-note {
            color: #523a25;
        }

        .dash-up {
            color: #3f6b3a;
            font-weight: 700;
        }

        .dash-down {
            color: var(--brand-red);
            font-weight: 700;
        }

        .dash-card {
            padding: 22px;
            border: 1px solid var(--border-warm);
            border-radius: 12px;
            background: var(--surface-warm);
        }

        .dash-card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
        }

        .dash-card-title {
            margin: 0 0 4px;
            font-family: var(--font-serif);
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--brand-brown);
        }

        .dash-card-sub {
            margin: 0;
            font-size: 13px;
            color: var(--muted-warm);
        }

        .dash-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 7px;
            background: #dde7d6;
            color: #3f6b3a;
            font-size: 12.5px;
            font-weight: 700;
            white-space: nowrap;
        }

        .dash-badge.is-down {
            background: #f2ddd6;
            color: var(--brand-red);
        }

        .dash-empty {
            margin: 0 0 16px;
            font-size: 13px;
            color: var(--muted-warm);
        }

        .dash-chart-body {
            display: flex;
            gap: 12px;
        }

        .dash-axis {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex: 0 0 auto;
            width: 54px;
            height: 224px;
            padding-bottom: 24px;
            font-size: 11px;
            color: #8a7a6a;
            text-align: right;
            white-space: nowrap;
        }

        .dash-plot-shell {
            flex: 1;
            min-width: 0;
            overflow-x: auto;
            padding-bottom: 2px;
        }

        .dash-bars {
            position: relative;
            display: flex;
            align-items: flex-end;
            gap: 8px;
            height: 200px;
            min-width: calc(var(--bars) * 36px);
        }

        .dash-grid {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            pointer-events: none;
        }

        .dash-grid span {
            border-top: 1px solid #e8dfc8;
        }

        .dash-bar-col {
            position: relative;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            flex: 1 1 0;
            min-width: 14px;
            height: 100%;
        }

        .dash-bar {
            width: min(70%, 44px);
            min-height: 2px;
            border-radius: 4px 4px 0 0;
            background: var(--brand-amber);
            transition: filter .15s ease;
        }

        .dash-bar-col:hover .dash-bar {
            filter: brightness(.94);
        }

        .dash-bar.is-today {
            background: var(--brand-red);
        }

        .dash-labels {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            min-width: calc(var(--bars) * 36px);
            padding-bottom: 2px;
        }

        .dash-x {
            flex: 1 1 0;
            min-width: 14px;
            font-size: 11px;
            color: #8a7a6a;
            text-align: center;
            white-space: nowrap;
        }

        @media (max-width: 1199px) {
            .dash-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 639px) {
            .dash-toolbar {
                align-items: stretch;
            }

            .dash-ranges {
                justify-content: space-between;
                width: 100%;
            }

            .dash-range {
                flex: 1;
                padding: 9px 6px;
                text-align: center;
                font-size: 12.5px;
            }

            .dash-tools {
                width: 100%;
            }

            .dash-box {
                flex: 1;
                justify-content: center;
            }

            .dash-stat {
                min-height: 124px;
                padding: 16px;
            }

            .dash-card {
                padding: 18px 16px;
            }

            .dash-axis {
                width: 44px;
                height: 180px;
                font-size: 10px;
            }

            .dash-bars {
                height: 156px;
            }

            .dash-x {
                font-size: 10px;
                line-height: 1.3;
                white-space: normal;
            }
        }

        @media (max-width: 419px) {
            .dash-stats {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media print {
            .dash-download,
            .dash-ranges,
            .dash-tools {
                display: none !important;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $main = $summary['summary'];
        $cmp = $summary['comparison'];
        $range = $summary['range'];
        $days = $summary['days'];

        $rupiah = fn (float $value): string => 'Rp'.number_format($value, 0, ',', '.');

        $percent = fn (?float $value, int $decimals = 1): ?string => $value === null
            ? null
            : ($value < 0 ? '−' : '+').number_format(abs($value), $decimals, ',', '.').'%';

        $arrow = fn (?float $value): string => match (true) {
            $value === null, abs($value) < 0.05 => '→',
            $value > 0 => '↗',
            default => '↘',
        };

        $toneClass = fn (?float $value): string => $value !== null && $value < 0 ? 'dash-down' : 'dash-up';

        $rangeLink = fn (string $preset): string => route('owner.dashboard', array_filter([
            'range' => $preset,
            'branch' => $branchId,
        ]));

        $peak = (float) collect($days)->max('revenue');
        $magnitude = $peak > 0 ? 10 ** (int) floor(log10($peak)) : 1;
        $axisMax = $peak > 0 ? ceil($peak / $magnitude) * $magnitude : 1;

        $compact = function (float $value): string {
            if ($value >= 1000000) {
                return rtrim(rtrim(number_format($value / 1000000, 1, ',', '.'), '0'), ',').' juta';
            }

            if ($value >= 1000) {
                return rtrim(rtrim(number_format($value / 1000, 1, ',', '.'), '0'), ',').' rb';
            }

            return number_format($value, 0, ',', '.');
        };

        $ticks = [];
        foreach ([100, 75, 50, 25, 0] as $tickPercent) {
            $ticks[] = $peak > 0 ? $compact($axisMax * $tickPercent / 100) : '';
        }

        if ($peak <= 0) {
            $ticks[4] = '0';
        }

        $revenueDelta = $percent($cmp['revenue_delta_percent']);
        $ordersDelta = $cmp['orders_delta'];

        $chartSub = match ($range['preset']) {
            'today' => 'Pendapatan hari ini',
            'month' => 'Pendapatan harian bulan ini',
            default => 'Pendapatan harian selama 7 hari terakhir',
        };
    @endphp

    <div class="dash-head">
        <div>
            <h1 class="dash-title">Total Penjualan</h1>
            <p class="dash-sub">Setiap cangkir punya cerita. Lihat penjualan kedai Anda.</p>
        </div>

        <a
            href="{{ route('owner.reports.index', array_filter(['date' => today()->toDateString(), 'branch_id' => $branchId])) }}"
            class="dash-download"
            title="Buka laporan untuk mengunduh PDF atau Excel"
        >
            @include('partials.nav-icon', ['name' => 'download'])
            <span>Unduh laporan</span>
        </a>
    </div>

    <div class="dash-toolbar">
        <nav class="dash-ranges" aria-label="Rentang penjualan">
            @foreach (['today' => 'Hari ini', '7d' => '7 hari terakhir', 'month' => 'Bulan ini'] as $preset => $label)
                <a
                    href="{{ $rangeLink($preset) }}"
                    class="dash-range {{ $range['preset'] === $preset ? 'is-active' : '' }}"
                    @if ($range['preset'] === $preset) aria-current="true" @endif
                >{{ $label }}</a>
            @endforeach
        </nav>

        <div class="dash-tools">
            @if ($branches->count() > 1)
                <form method="GET" action="{{ route('owner.dashboard') }}">
                    <input type="hidden" name="range" value="{{ $range['preset'] }}">
                    <label for="dash-branch" class="dash-sr">Pilih cabang</label>
                    <select
                        id="dash-branch"
                        name="branch"
                        class="dash-box"
                        onchange="this.form.submit()"
                    >
                        <option value="">Semua cabang</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($branchId === $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif

            <span class="dash-box" title="Rentang tanggal yang sedang ditampilkan">
                @include('partials.nav-icon', ['name' => 'calendar'])
                <span>{{ $range['label'] }}</span>
                @include('partials.nav-icon', ['name' => 'chevron-down'])
            </span>
        </div>
    </div>

    <div class="dash-stats">
        <article class="dash-stat is-gold">
            <span class="dash-stat-icon">@include('partials.nav-icon', ['name' => 'wallet'])</span>
            <span class="dash-stat-label">Total pendapatan</span>
            <strong class="dash-stat-value">{{ $rupiah($main['revenue']) }}</strong>
            <span class="dash-stat-note">
                @if ($revenueDelta === null)
                    Belum ada pembanding
                @else
                    <span class="{{ $toneClass($cmp['revenue_delta_percent']) }}">{{ $arrow($cmp['revenue_delta_percent']) }} {{ $revenueDelta }}</span>
                    <span>{{ $cmp['label'] }}</span>
                @endif
            </span>
        </article>

        <article class="dash-stat">
            <span class="dash-stat-icon">@include('partials.nav-icon', ['name' => 'receipt'])</span>
            <span class="dash-stat-label">Total transaksi</span>
            <strong class="dash-stat-value">{{ number_format($main['orders'], 0, ',', '.') }}</strong>
            <span class="dash-stat-note">
                <span class="{{ $ordersDelta < 0 ? 'dash-down' : 'dash-up' }}">{{ $arrow((float) $ordersDelta) }} {{ $ordersDelta > 0 ? '+' : ($ordersDelta < 0 ? '−' : '±') }}{{ abs($ordersDelta) }} transaksi</span>
                <span>{{ $cmp['label'] }}</span>
            </span>
        </article>

        <article class="dash-stat">
            <span class="dash-stat-icon">@include('partials.nav-icon', ['name' => 'coffee'])</span>
            <span class="dash-stat-label">Menu terjual</span>
            <strong class="dash-stat-value">{{ number_format($main['items_sold'], 0, ',', '.') }}</strong>
            <span class="dash-stat-note">Porsi &amp; cangkir terjual</span>
        </article>

        <article class="dash-stat">
            <span class="dash-stat-icon">@include('partials.nav-icon', ['name' => 'trend'])</span>
            <span class="dash-stat-label">Rata-rata transaksi</span>
            <strong class="dash-stat-value">{{ $rupiah($main['average']) }}</strong>
            <span class="dash-stat-note">
                @if ($cmp['average_delta_percent'] === null)
                    Belum ada pembanding
                @else
                    <span class="{{ $toneClass($cmp['average_delta_percent']) }}">{{ $arrow($cmp['average_delta_percent']) }} {{ $percent($cmp['average_delta_percent']) }}</span>
                    <span>{{ $cmp['label'] }}</span>
                @endif
            </span>
        </article>
    </div>

    <section class="dash-card">
        <header class="dash-card-head">
            <div>
                <h2 class="dash-card-title">Tren penjualan</h2>
                <p class="dash-card-sub">{{ $chartSub }}</p>
            </div>

            @if ($revenueDelta !== null)
                <span class="dash-badge {{ $cmp['revenue_delta_percent'] < 0 ? 'is-down' : '' }}">
                    {{ $revenueDelta }}
                </span>
            @endif
        </header>

        @if ($main['revenue'] <= 0)
            <p class="dash-empty">Belum ada pembayaran pada rentang ini.</p>
        @endif

        <div class="dash-chart-body">
            <div class="dash-axis" aria-hidden="true">
                @foreach ($ticks as $tick)
                    <span>{{ $tick }}</span>
                @endforeach
            </div>

            <div class="dash-plot-shell">
                <div class="dash-bars" style="--bars: {{ count($days) }}" role="img" aria-label="Grafik pendapatan harian">
                    <div class="dash-grid" aria-hidden="true">
                        @foreach ($ticks as $tick)
                            <span></span>
                        @endforeach
                    </div>

                    @foreach ($days as $day)
                        @php
                            $height = $axisMax > 0 ? min(100, ($day['revenue'] / $axisMax) * 100) : 0;
                        @endphp
                        <div class="dash-bar-col">
                            <div
                                class="dash-bar {{ $day['is_today'] ? 'is-today' : '' }}"
                                style="height: {{ number_format($height, 2, '.', '') }}%"
                                title="{{ $day['label'] }} · {{ $rupiah($day['revenue']) }}"
                            ></div>
                        </div>
                    @endforeach
                </div>

                <div class="dash-labels" style="--bars: {{ count($days) }}" aria-hidden="true">
                    @foreach ($days as $day)
                        <span class="dash-x">{{ $day['label'] }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
