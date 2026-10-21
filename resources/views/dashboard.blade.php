@extends('layouts.app')

@section('title', 'Dashboard - Kasir Warung Jagung')

@section('content')
    <div class="card">
        <h1>Dashboard</h1>
        <p>Halo, <strong>{{ auth()->user()->name }}</strong>.</p>
        <p class="muted">
            Role: <span class="badge">{{ auth()->user()->role }}</span><br>
            Cabang: {{ auth()->user()->branch?->name ?? 'Semua cabang' }}
        </p>
        <p class="muted">Menu transaksi akan tersedia pada tahap berikutnya.</p>
    </div>
@endsection
