@extends('layouts.app')

@section('title', 'Log Aktivitas - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Log Aktivitas</h1>
            <p class="muted">Riwayat perubahan menu dan varian oleh owner.</p>
        </div>
        <span class="badge">Owner</span>
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
            <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Nama menu, varian, atau keterangan">
        </div>

        <button type="submit" class="btn">Filter</button>

        @if ($from || $to || $action || $userId || $search !== '')
            <a href="{{ route('owner.activity.index') }}" class="btn btn-ghost">Reset</a>
        @endif
    </form>

    @if ($logs->isEmpty())
        <div class="card empty">
            <h2>Belum ada aktivitas</h2>
            <p class="muted">Setiap tambah, ubah, atau aktif/nonaktif menu dan varian akan tercatat di sini.</p>
        </div>
    @else
        <div class="card" style="padding: 0">
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                            <th>Objek</th>
                            <th>Perubahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td style="white-space: nowrap">
                                    {{ $log->created_at?->format('d/m/Y') ?? '—' }}
                                    <div class="muted" style="font-size: 12px">
                                        {{ $log->created_at?->format('H:i') }}
                                    </div>
                                </td>
                                <td>
                                    {{ $log->user?->name ?? 'Sistem' }}
                                    <div class="muted" style="font-size: 12px">
                                        {{ $log->user?->role ?? '—' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="status {{ $log->actionTone() }}">{{ $log->actionLabel() }}</span>
                                </td>
                                <td>
                                    <strong>{{ $log->subject_label }}</strong>
                                    <div class="muted" style="font-size: 12px">{{ $log->description }}</div>
                                </td>
                                <td>
                                    @php($lines = $log->changeLines())
                                    @if ($lines === [])
                                        <span class="muted">—</span>
                                    @else
                                        @foreach ($lines as $line)
                                            <div class="muted" style="font-size: 12px">{{ $line }}</div>
                                        @endforeach
                                    @endif
                                    @if ($log->ip_address)
                                        <div class="muted" style="font-size: 11px; margin-top: 4px">IP {{ $log->ip_address }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($logs->hasPages())
            <div class="pager">
                <div>
                    @if (! $logs->onFirstPage())
                        <a href="{{ $logs->previousPageUrl() }}">← Sebelumnya</a>
                    @endif
                </div>
                <span class="muted">Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}</span>
                <div>
                    @if ($logs->hasMorePages())
                        <a href="{{ $logs->nextPageUrl() }}">Berikutnya →</a>
                    @endif
                </div>
            </div>
        @endif
    @endif
@endsection
