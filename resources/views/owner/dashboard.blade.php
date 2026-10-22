@extends('layouts.app')

@section('title', 'Dashboard Owner - Hotmad')

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
            <span class="stat-label">Penjualan Hari Ini</span>
            <strong class="stat-value">Rp 0</strong>
            <span class="muted">Belum ada transaksi</span>
        </div>

        <div class="card stat">
            <span class="stat-label">Transaksi Hari Ini</span>
            <strong class="stat-value">0</strong>
            <span class="muted">Semua cabang</span>
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
            <div class="quick">
                <strong>Kelola Cabang</strong>
                <span class="muted">Segera hadir</span>
            </div>
            <a href="{{ route('owner.menus.index') }}" class="quick quick-link">
                <strong>Kelola Menu</strong>
                <span class="muted">{{ $menuCount }} menu aktif</span>
            </a>
            <div class="quick">
                <strong>Kelola Stok</strong>
                <span class="muted">Segera hadir</span>
            </div>
            <div class="quick">
                <strong>Laporan</strong>
                <span class="muted">Segera hadir</span>
            </div>
        </div>
    </div>
@endsection
