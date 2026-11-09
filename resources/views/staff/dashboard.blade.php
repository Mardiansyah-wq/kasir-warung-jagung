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
            <span class="stat-label">Order Draft</span>
            <strong class="stat-value">{{ $draftCount }}</strong>
            <span class="muted">{{ $draftCount > 0 ? 'Keranjang aktif' : 'Belum ada keranjang' }}</span>
        </div>

        <div class="card stat">
            <span class="stat-label">Penjualan Hari Ini</span>
            <strong class="stat-value">Rp {{ number_format($todaySales, 0, ',', '.') }}</strong>
            <span class="muted">{{ $todayCount }} transaksi lunas</span>
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
            <h2>Mulai Transaksi</h2>
            <p class="muted">Buat keranjang, pilih menu, lalu bayar. Stok berkurang otomatis setelah pembayaran.</p>
            <a href="{{ route('staff.pos.index') }}" class="btn btn-block">Buka Kasir</a>
        </div>
    @endif

    <div class="quick-grid" style="margin-top: 16px">
        <a href="{{ route('staff.orders.index') }}" class="quick quick-link">
            <strong>Riwayat Transaksi</strong>
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
