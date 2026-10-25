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

    <div class="grid stats">
        <div class="card stat">
            <span class="stat-label">Order Draft</span>
            <strong class="stat-value">0</strong>
            <span class="muted">Belum ada order</span>
        </div>

        <div class="card stat">
            <span class="stat-label">Menu Aktif</span>
            <strong class="stat-value">{{ $menuCount }}</strong>
            <span class="muted">Siap dijual</span>
        </div>

        <div class="card stat">
            <span class="stat-label">Transaksi Hari Ini</span>
            <strong class="stat-value">0</strong>
            <span class="muted">Cabang ini</span>
        </div>

        <div class="card stat">
            <span class="stat-label">Stok Cabang</span>
            <strong class="stat-value">{{ $stockCount }}</strong>
            <span class="muted">Total item</span>
        </div>
    </div>

    <div class="card">
        <h2>Menu Jagung Bakar WJT</h2>
        <p class="muted">Lihat daftar menu, varian, harga, dan stok cabang.</p>
        <a href="{{ route('staff.menus.index') }}" class="btn btn-block">Lihat Menu</a>
    </div>

    <div class="card" style="margin-top: 16px">
        <h2>Mulai Transaksi</h2>
        <p class="muted">Fitur kasir akan tersedia pada tahap berikutnya.</p>
        <button type="button" class="btn btn-block" disabled>Mulai Transaksi (segera)</button>
    </div>
@endsection
