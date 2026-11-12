@extends('layouts.app')

@section('title', 'Log Aktivitas - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Log Aktivitas</h1>
            <p class="muted">Riwayat perubahan menu, varian, dan stok oleh owner maupun staff.</p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap">
            <a href="{{ route('owner.stock.index') }}" class="btn btn-ghost">Kelola Stok</a>
            <span class="badge">Owner</span>
        </div>
    </div>

    @include('partials.alerts')

    <form method="GET" action="{{ route('owner.activity.index') }}" class="filter-bar">
        <div class="field">
            <label for="from">Dari</label>
            <input type="date" id="from" name="from" value="{{ $from }}">
        </div>

        <div class="field">
            <label for="to">Sampai</label>
            <input type="date" id="to" name="to" value="{{ $to }}">
        </div>

        <div class="field">
            <label for="action">Jenis</label>
            <select id="action" name="action">
                <option value="">Semua</option>
                <option value="menu" @selected($action === 'menu')>Menu</option>
                <option value="variant" @selected($action === 'variant')>Varian</option>
                <option value="stock" @selected($action === 'stock')>Stok</option>
            </select>
        </div>

        <div class="field">
            <label for="branch_id">Cabang</label>
            <select id="branch_id" name="branch_id">
                <option value="">Semua</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((int) $branchId === (int) $branch->id)>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
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
            <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Nama menu, varian, cabang, atau keterangan">
        </div>

        <button type="submit" class="btn">Filter</button>

        @if ($from || $to || $action || $branchId || $userId || $search !== '')
            <a href="{{ route('owner.activity.index') }}" class="btn btn-ghost">Reset</a>
        @endif
    </form>

    @if ($logs->isEmpty())
        <div class="card empty">
            <h2>Belum ada aktivitas</h2>
            <p class="muted">Setiap tambah, ubah, atau aktif/nonaktif menu dan varian, serta perubahan stok, akan tercatat di sini.</p>
        </div>
    @else
        @include('partials.activity-table', ['logs' => $logs, 'showBranch' => true])
    @endif
@endsection
