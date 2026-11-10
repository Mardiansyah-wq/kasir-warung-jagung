@extends('layouts.app')

@section('title', 'Dashboard Staff - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Dashboard Staff</h1>
            <p class="muted">Cabang: {{ $branch?->name ?? 'Belum ditentukan' }}</p>
        </div>
        <span class="badge">Staff</span>
    </div>

    @include('partials.alerts')

    <div class="grid stats">
        <div class="card stat">
            <span class="stat-label">Pesanan Aktif</span>
            <strong class="stat-value">{{ $activeCount }}</strong>
            <span class="muted">{{ $openCount }} baru · {{ $processingCount }} diproses · {{ $readyCount }} siap</span>
        </div>

        <div class="card stat">
            <span class="stat-label">Uang Masuk Hari Ini</span>
            <strong class="stat-value">Rp {{ number_format($todaySales, 0, ',', '.') }}</strong>
            <span class="muted">{{ $todayCount }} pembayaran diterima</span>
        </div>

        <div class="card stat">
            <span class="stat-label">Menu Aktif</span>
            <strong class="stat-value">{{ $menuCount }}</strong>
            <span class="muted">Siap dijual</span>
        </div>

        <div class="card stat">
            <span class="stat-label">Stok Cabang</span>
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
        <div class="card">
            <h2>Papan Pesanan</h2>
            <p class="muted">Buat pesanan baru, tambah item kapan saja, dan terima pembayaran sebagian atau penuh.</p>
            <a href="{{ route('staff.pos.index') }}" class="btn btn-block">Buka Kasir</a>
        </div>

        @if ($recentOrders->isNotEmpty())
            <h2 class="group-title">Pesanan Aktif Terbaru</h2>

            <div class="order-grid">
                @foreach ($recentOrders as $order)
                    <article class="order-card order-{{ $order->status }}">
                        <div class="order-card-head">
                            <div>
                                <div class="queue-number">{{ $order->queueLabel() }}</div>
                                <h2>{{ $order->customer_name }}</h2>
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
        @endif
    @endif

    <div class="quick-grid" style="margin-top: 16px">
        <a href="{{ route('staff.orders.index') }}" class="quick quick-link">
            <strong>Riwayat Pesanan</strong>
            <span class="muted">Lihat dan cetak struk</span>
        </a>
        <a href="{{ route('staff.stock.index') }}" class="quick quick-link">
            <strong>Kelola Stok</strong>
            <span class="muted">Tambah stok saat habis</span>
        </a>
        <a href="{{ route('staff.menus.index') }}" class="quick quick-link">
            <strong>Daftar Menu</strong>
            <span class="muted">{{ $menuCount }} menu aktif</span>
        </a>
    </div>
@endsection
