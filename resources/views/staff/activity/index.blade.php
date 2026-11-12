@extends('layouts.app')

@section('title', 'Log Aktivitas Stok - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Log Aktivitas Stok</h1>
            <p class="muted">
                Riwayat penambahan, pengurangan, dan koreksi stok di
                {{ $branch?->name ?? 'cabang kamu' }}.
            </p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap">
            <a href="{{ route('staff.stock.index') }}" class="btn btn-ghost">Kelola Stok</a>
            <span class="badge">Staff</span>
        </div>
    </div>

    @include('partials.alerts')

    @unless ($branch)
        <div class="card empty">
            <h2>Akun belum terhubung ke cabang</h2>
            <p class="muted">Hubungi owner untuk menghubungkan akun staff ke cabang.</p>
        </div>
    @else
        <form method="GET" action="{{ route('staff.activity.index') }}" class="filter-bar">
            <div class="field">
                <label for="from">Dari</label>
                <input type="date" id="from" name="from" value="{{ $from }}">
            </div>

            <div class="field">
                <label for="to">Sampai</label>
                <input type="date" id="to" name="to" value="{{ $to }}">
            </div>

            <div class="field">
                <label for="user_id">Pengguna</label>
                <select id="user_id" name="user_id">
                    <option value="">Semua</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((int) $userId === (int) $user->id)>
                            {{ $user->name }} ({{ $user->role }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field" style="flex: 1 1 200px">
                <label for="search">Cari</label>
                <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Nama menu, varian, atau keterangan">
            </div>

            <button type="submit" class="btn">Filter</button>

            @if ($from || $to || $userId || $search !== '')
                <a href="{{ route('staff.activity.index') }}" class="btn btn-ghost">Reset</a>
            @endif
        </form>

        @if ($logs->isEmpty())
            <div class="card empty">
                <h2>Belum ada aktivitas stok</h2>
                <p class="muted">Penambahan, pengurangan, dan koreksi stok akan tercatat di sini.</p>
            </div>
        @else
            @include('partials.activity-table', ['logs' => $logs])
        @endif
    @endunless
@endsection
