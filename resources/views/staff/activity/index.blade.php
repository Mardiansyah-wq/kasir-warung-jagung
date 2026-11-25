@extends('layouts.app')

@section('title', 'Log Aktivitas Stok - Jagung Bakar WJT')

@section('body_class', 'page-staff-activity')

@push('styles')
    <style>
        body.page-staff-activity .activity-card-subject,
        body.page-staff-activity .activity-card-desc,
        body.page-staff-activity .activity-card-user,
        body.page-staff-activity .activity-changes-box,
        body.page-staff-activity .activity-card-footer {
            overflow-wrap: anywhere;
        }

        body.page-staff-activity .activity-card-user span:last-child {
            text-transform: capitalize;
        }
    </style>
@endpush

@section('content')
    <div class="page-head">
        <div>
            <h1>Log Aktivitas Stok</h1>
            <p class="muted">
                Riwayat penambahan, pengurangan, dan koreksi stok di
                {{ $branch?->name ?? 'cabang kamu' }}.
            </p>
        </div>
        <div class="head-actions">
            <a href="{{ route('staff.stock.index') }}" class="btn btn-ghost">Kelola Stok</a>
            @include('partials.role-chip', ['role' => 'staff'])
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

            <div class="field filter-search">
                <label for="search">Cari</label>
                <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Menu, varian, keterangan">
            </div>

            <button type="submit" class="btn">Filter</button>

            @if ($from || $to || $userId || $search !== '')
                <a href="{{ route('staff.activity.index') }}" class="btn btn-ghost">Reset</a>
            @endif
        </form>

        @if ($logs->isEmpty())
            @php
                $hasFilters = (bool) $from || (bool) $to || (bool) $userId || $search !== '';
            @endphp
            <div class="card empty">
                <h2>{{ $hasFilters ? 'Tidak ada aktivitas untuk filter ini' : 'Belum ada aktivitas stok' }}</h2>
                <p class="muted">
                    {{ $hasFilters ? 'Coba ubah rentang tanggal, staff, atau kata kunci.' : 'Penambahan, pengurangan, dan koreksi stok akan tercatat di sini.' }}
                </p>
                @if ($hasFilters)
                    <a href="{{ route('staff.activity.index') }}" class="btn btn-ghost">Reset filter</a>
                @endif
            </div>
        @else
            @include('partials.activity-table', ['logs' => $logs])
        @endif
    @endunless
@endsection
