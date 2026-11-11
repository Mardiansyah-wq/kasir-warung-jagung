@extends('layouts.app')

@section('title', 'Manajemen Cabang - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Manajemen Cabang</h1>
            <p class="muted">Tambah cabang baru dan atur status aktifnya.</p>
        </div>
        <a href="{{ route('owner.branches.create') }}" class="btn">+ Tambah Cabang</a>
    </div>

    @include('partials.alerts')

    @if ($branches->isEmpty())
        <div class="card empty">
            <h2>Belum ada cabang</h2>
            <p class="muted">Tambahkan cabang pertama agar staff bisa dihubungkan.</p>
            <a href="{{ route('owner.branches.create') }}" class="btn">Tambah Cabang</a>
        </div>
    @else
        <div class="card" style="padding: 0">
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Nama Cabang</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($branches as $branch)
                            <tr>
                                <td>
                                    {{ $branch->name }}
                                    <div class="muted">{{ $branch->users_count ?? $branch->users()->count() }} staff</div>
                                </td>
                                <td>{{ $branch->address ?: '—' }}</td>
                                <td>{{ $branch->phone ?: '—' }}</td>
                                <td>
                                    @if ($branch->is_active)
                                        <span class="status status-ready">Aktif</span>
                                    @else
                                        <span class="status status-completed">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px; flex-wrap: wrap">
                                        <a href="{{ route('owner.branches.edit', $branch) }}" class="btn btn-sm btn-ghost">Ubah</a>

                                        <form method="POST" action="{{ route('owner.branches.toggle', $branch) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-ghost">
                                                {{ $branch->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection