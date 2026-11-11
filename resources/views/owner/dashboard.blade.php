@extends('layouts.app')

@section('title', 'Dashboard Owner - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Dashboard Owner</h1>
            <p class="muted">Ringkasan operasional semua cabang.</p>
        </div>
        <span class="badge">Owner</span>
    </div>

    <div class="grid stats">
        <div class="card stat">
            <span class="stat-label">Uang Masuk Hari Ini</span>
            <strong class="stat-value">Rp {{ number_format($todaySales, 0, ',', '.') }}</strong>
            <span class="muted">{{ $todayCount }} pembayaran diterima</span>
        </div>

        <div class="card stat">
            <span class="stat-label">Pesanan Aktif</span>
            <strong class="stat-value">{{ $activeCount }}</strong>
            <span class="muted">Belum selesai / batal</span>
        </div>

        <div class="card stat">
            <span class="stat-label">Stok Menipis</span>
            <strong class="stat-value">{{ $lowStockCount }}</strong>
            <span class="muted">Perlu restock</span>
        </div>

        <div class="card stat">
            <span class="stat-label">Cabang Aktif</span>
            <strong class="stat-value">{{ $branchCount }}</strong>
            <span class="muted">Terdaftar</span>
        </div>
    </div>

    <div class="card">
        <h2>Menu Owner</h2>
        <div class="quick-grid">
            <a href="{{ route('owner.branches.index') }}" class="quick quick-link">
                <strong>Kelola Cabang</strong>
                <span class="muted">{{ $branchCount }} cabang aktif</span>
            </a>
            <a href="{{ route('owner.orders.index') }}" class="quick quick-link">
                <strong>Riwayat Pesanan</strong>
                <span class="muted">{{ $todayCount }} pembayaran hari ini</span>
            </a>
            <a href="{{ route('owner.menus.index') }}" class="quick quick-link">
                <strong>Kelola Menu</strong>
                <span class="muted">{{ $menuCount }} menu aktif</span>
            </a>
            <a href="{{ route('owner.staff.index') }}" class="quick quick-link">
                <strong>Kelola Staff</strong>
                <span class="muted">Setujui & atur akun staff</span>
            </a>
            <a href="{{ route('owner.reports.index') }}" class="quick quick-link">
                <strong>Laporan Harian</strong>
                <span class="muted">Rekap & unduh PDF/Excel</span>
            </a>
        </div>
    </div>
@endsection
