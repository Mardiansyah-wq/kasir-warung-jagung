@extends('layouts.app')

@section('title', 'Dashboard Staff - Jagung Bakar WJT')

@section('body_class', 'page-staff-dash')

@push('styles')
    <style>
        body.page-staff-dash {
            background: var(--bg-warm);
            color: var(--text-warm);
        }

        .sd-hero {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 16px;
            overflow: hidden;
            margin-bottom: 16px;
            padding: 22px;
            border-radius: 16px;
            background: linear-gradient(135deg, #36251e 0%, #4a342a 58%, #6b4426 100%);
            color: #f9f2e4;
            box-shadow: 0 6px 20px rgba(54, 37, 30, .16);
        }

        .sd-hero::after {
            position: absolute;
            top: -70px;
            right: -50px;
            width: 190px;
            height: 190px;
            border-radius: 50%;
            background: rgba(224, 183, 71, .16);
            content: "";
            pointer-events: none;
        }

        .sd-hero > * {
            position: relative;
            z-index: 1;
        }

        .sd-hero-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .sd-hero-brand {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #e8cd8c;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .sd-hero-brand .nav-icon {
            width: 16px;
            height: 16px;
        }

        .sd-hero h1 {
            margin: 8px 0 0;
            font-family: var(--font-serif);
            font-size: clamp(1.6rem, 4vw, 2.1rem);
            line-height: 1.15;
            letter-spacing: -.01em;
        }

        .sd-hero p {
            margin: 6px 0 0;
            color: #e2d5c1;
            font-size: 13.5px;
        }

        body.page-staff-dash .grid.stats {
            margin-bottom: 16px;
        }

        body.page-staff-dash .stat {
            position: relative;
            display: flex;
            min-height: 132px;
            flex-direction: column;
            gap: 0;
            padding: 16px;
            border: 1px solid var(--border-warm);
            border-radius: 14px;
            background: var(--surface-warm);
            box-shadow: 0 1px 2px rgba(54, 37, 30, .04);
        }

        body.page-staff-dash .stat::before {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 3px;
            border-radius: 14px 0 0 14px;
            background: var(--brand-amber);
            content: "";
        }

        body.page-staff-dash .stat.is-sales::before {
            background: #3f6b3a;
        }

        body.page-staff-dash .stat.is-menu::before {
            background: #925807;
        }

        .sd-stat-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .sd-stat-icon {
            display: grid;
            flex: 0 0 auto;
            width: 30px;
            height: 30px;
            border-radius: 9px;
            background: #f7ecd4;
            color: var(--brand-brown);
            place-items: center;
        }

        .sd-stat-icon .nav-icon {
            width: 16px;
            height: 16px;
        }

        body.page-staff-dash .stat-value {
            margin-top: 10px;
            font-family: var(--font-serif);
            font-size: clamp(1.35rem, 4.5vw, 1.7rem);
        }

        body.page-staff-dash .stat .muted {
            margin-top: 6px;
            font-size: 12px;
            line-height: 1.35;
        }

        .sd-cta {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 18px;
            padding: 18px;
            border: 1px solid #ead7a6;
            border-left: 4px solid var(--brand-amber);
            border-radius: 14px;
            background: linear-gradient(135deg, var(--surface-warm), #fdf5e2);
        }

        .sd-cta h2 {
            margin: 0;
            font-family: var(--font-serif);
            font-size: 1.1rem;
        }

        .sd-cta p {
            margin: 4px 0 0;
            color: var(--muted-warm);
            font-size: 13px;
        }

        .sd-cta .btn {
            align-self: flex-start;
            gap: 8px;
        }

        .sd-cta .btn .nav-icon {
            width: 16px;
            height: 16px;
        }

        .sd-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin: 18px 0 10px;
        }

        .sd-section-head > div {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sd-section-head h2 {
            margin: 0;
            font-family: var(--font-serif);
            font-size: 1.1rem;
        }

        .sd-section-head a {
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            color: var(--brand);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .sd-count {
            display: inline-flex;
            min-width: 24px;
            height: 24px;
            align-items: center;
            justify-content: center;
            padding: 0 7px;
            border-radius: 999px;
            background: #f3e8d3;
            color: var(--brand-brown);
            font-size: 12px;
            font-weight: 700;
        }

        body.page-staff-dash .quick-grid {
            margin-top: 18px;
        }

        body.page-staff-dash .quick {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 13px;
            border: 1px solid var(--border-warm);
            border-radius: 12px;
            background: var(--surface-warm);
            box-shadow: 0 1px 2px rgba(54, 37, 30, .04);
            transition: transform .15s ease, box-shadow .15s ease;
        }

        body.page-staff-dash .quick:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(54, 37, 30, .1);
        }

        .sd-quick-icon {
            display: grid;
            flex: 0 0 auto;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #f7ecd4;
            color: var(--brand-brown);
            place-items: center;
        }

        .sd-quick-icon .nav-icon {
            width: 17px;
            height: 17px;
        }

        .sd-quick-copy {
            min-width: 0;
        }

        body.page-staff-dash .quick strong {
            font-size: 13.5px;
        }

        body.page-staff-dash .quick span {
            display: block;
            margin-top: 2px;
            overflow: hidden;
            font-size: 12px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        body.page-staff-dash .stat-value {
            white-space: nowrap;
        }

        body.page-staff-dash .quick-grid {
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }

        @media (max-width: 1099px) {
            body.page-staff-dash .order-card {
                border-left: 4px solid var(--brand-amber);
            }
        }

        @media (min-width: 900px) {
            .sd-hero {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                padding: 26px 30px;
            }

            body.page-staff-dash .grid.stats {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            body.page-staff-dash .stat {
                min-height: 148px;
                padding: 20px;
            }

            .sd-cta {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }
    </style>
@endpush

@section('content')
    <div class="sd-hero">
        <div class="sd-hero-top">
            <div>
                <div class="sd-hero-brand">
                    @include('partials.nav-icon', ['name' => 'store'])
                    <span>Jagung Bakar WJT</span>
                </div>
                <h1>Dashboard Staff</h1>
                <p>Cabang: {{ $branch?->name ?? 'Belum ditentukan' }}</p>
            </div>
            @include('partials.role-chip', ['role' => 'staff', 'hero' => true])
        </div>
    </div>

    @include('partials.alerts')

    <div class="grid stats">
        <div class="card stat">
            <div class="sd-stat-head">
                <span class="stat-label">Pesanan Aktif</span>
                <span class="sd-stat-icon">@include('partials.nav-icon', ['name' => 'receipt'])</span>
            </div>
            <strong class="stat-value">{{ $activeCount }}</strong>
            <span class="muted">{{ $openCount }} baru · {{ $processingCount }} diproses · {{ $readyCount }} siap</span>
        </div>

        <div class="card stat is-sales">
            <div class="sd-stat-head">
                <span class="stat-label">Uang Masuk Hari Ini</span>
                <span class="sd-stat-icon">@include('partials.nav-icon', ['name' => 'wallet'])</span>
            </div>
            <strong class="stat-value">Rp {{ number_format($todaySales, 0, ',', '.') }}</strong>
            <span class="muted">{{ $todayCount }} pembayaran diterima</span>
        </div>

        <div class="card stat is-menu">
            <div class="sd-stat-head">
                <span class="stat-label">Menu Aktif</span>
                <span class="sd-stat-icon">@include('partials.nav-icon', ['name' => 'coffee'])</span>
            </div>
            <strong class="stat-value">{{ $menuCount }}</strong>
            <span class="muted">Siap dijual</span>
        </div>

        <div class="card stat is-stock">
            <div class="sd-stat-head">
                <span class="stat-label">Stok Cabang</span>
                <span class="sd-stat-icon">@include('partials.nav-icon', ['name' => 'box'])</span>
            </div>
            <strong class="stat-value">{{ $stockCount }}</strong>
            <span class="muted">Total item</span>
        </div>
    </div>

    @if (! $branch)
        <div class="card empty">
            <h2>Akun belum terhubung ke cabang</h2>
            <p class="muted">Hubungi owner untuk menghubungkan akun staff ke cabang sebelum bertransaksi.</p>
        </div>
    @else
        <div class="sd-cta">
            <div>
                <h2>Papan Pesanan</h2>
                <p>Buat pesanan baru, tambah item kapan saja, dan terima pembayaran sebagian atau penuh.</p>
            </div>
            <a href="{{ route('staff.pos.index') }}" class="btn">
                @include('partials.nav-icon', ['name' => 'cart'])
                <span>Buka Kasir</span>
            </a>
        </div>

        <div class="sd-section-head">
            <div>
                <h2>Pesanan Aktif Terbaru</h2>
                <span class="sd-count">{{ $activeCount }}</span>
            </div>
            <a href="{{ route('staff.orders.index') }}">Lihat semua →</a>
        </div>

        @if ($recentOrders->isNotEmpty())
            <div class="order-grid">
                @foreach ($recentOrders as $order)
                    <article class="order-card order-{{ $order->status }}">
                        <div class="order-card-head">
                            <div>
                                <div class="queue-number">{{ $order->queueLabel() }}</div>
                                <h2 title="{{ $order->customer_name }}">{{ $order->customer_name }}</h2>
                            </div>
                            <span class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
                        </div>

                        <div class="order-card-meta">
                            <span>{{ $order->orderTypeLabel() }}</span>
                            <span>{{ $order->updated_at?->format('H:i') }}</span>
                            <span class="status status-{{ $order->paymentStatus() }}">{{ $order->paymentStatusLabel() }}</span>
                        </div>

                        <div class="order-card-money">
                            <span>Total <strong>Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</strong></span>
                            <span>Sisa Rp {{ number_format($order->balanceDue(), 0, ',', '.') }}</span>
                        </div>

                        <div class="order-card-actions">
                            <a href="{{ route('staff.pos.show', $order) }}" class="btn btn-sm">Buka</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="card empty">
                <h2>Belum ada pesanan aktif</h2>
                <p class="muted">Pesanan baru akan muncul di sini setelah pelanggan dibuatkan antrean.</p>
            </div>
        @endif

        <div class="quick-grid">
            <a href="{{ route('staff.orders.index') }}" class="quick quick-link">
                <span class="sd-quick-icon">@include('partials.nav-icon', ['name' => 'receipt'])</span>
                <span class="sd-quick-copy">
                    <strong>Riwayat Pesanan</strong>
                    <span class="muted">Lihat dan cetak struk</span>
                </span>
            </a>
            <a href="{{ route('staff.stock.index') }}" class="quick quick-link">
                <span class="sd-quick-icon">@include('partials.nav-icon', ['name' => 'box'])</span>
                <span class="sd-quick-copy">
                    <strong>Kelola Stok</strong>
                    <span class="muted">Tambah stok saat habis</span>
                </span>
            </a>
            <a href="{{ route('staff.menus.index') }}" class="quick quick-link">
                <span class="sd-quick-icon">@include('partials.nav-icon', ['name' => 'coffee'])</span>
                <span class="sd-quick-copy">
                    <strong>Daftar Menu</strong>
                    <span class="muted">{{ $menuCount }} menu aktif</span>
                </span>
            </a>
            <a href="{{ route('account.index') }}" class="quick quick-link">
                <span class="sd-quick-icon">@include('partials.nav-icon', ['name' => 'user'])</span>
                <span class="sd-quick-copy">
                    <strong>Akun Saya</strong>
                    <span class="muted">Profil &amp; password</span>
                </span>
            </a>
        </div>
    @endif
@endsection
