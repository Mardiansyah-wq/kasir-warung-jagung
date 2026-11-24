@extends('layouts.app')

@section('title', 'Manajemen Staff - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Manajemen Staff</h1>
            <p class="muted">Setujui pendaftar baru, atur cabang, dan reset password staff.</p>
        </div>
        <a href="{{ route('owner.staff.create') }}" class="btn">+ Tambah Staff</a>
    </div>

    @include('partials.alerts')

    @if ($staff->isEmpty())
        <div class="card empty">
            <h2>Belum ada staff</h2>
            <p class="muted">Tambahkan staff pertama, atau tunggu ada yang mendaftar lewat halaman daftar.</p>
            <a href="{{ route('owner.staff.create') }}" class="btn">Tambah Staff</a>
        </div>
    @else
        <div class="card" style="padding: 0">
            <div class="table-wrap">
                <table class="data responsive">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Cabang</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($staff as $user)
                            <tr>
                                <td data-label="Nama">
                                    {{ $user->name }}
                                    @if ($user->phone)
                                        <div class="muted">{{ $user->phone }}</div>
                                    @endif
                                </td>
                                <td data-label="Email">{{ $user->email }}</td>
                                <td data-label="Cabang">
                                    @if ($user->branch)
                                        {{ $user->branch->name }}
                                    @else
                                        <span class="muted">Belum diassign</span>
                                    @endif
                                </td>
                                <td data-label="Status">
                                    @if ($user->is_active)
                                        <span class="status status-ready">Aktif</span>
                                    @elseif ($user->branch_id === null)
                                        <span class="status status-processing">Menunggu</span>
                                    @else
                                        <span class="status status-completed">Nonaktif</span>
                                    @endif
                                </td>
                                <td data-label="Aksi">
                                    @if (! $user->is_active && $user->branch_id === null)
                                        <form
                                            method="POST"
                                            action="{{ route('owner.staff.toggle', $user) }}"
                                            style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <select
                                                name="branch_id"
                                                class="select-inline"
                                                aria-label="Cabang untuk {{ $user->name }}"
                                                required
                                            >
                                                <option value="">Pilih cabang...</option>
                                                @foreach ($branches as $branch)
                                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                                        {{ $branch->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-sm">Setujui</button>
                                        </form>
                                    @else
                                        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center">
                                            <a href="{{ route('owner.staff.edit', $user) }}" class="btn btn-sm btn-ghost">Ubah</a>

                                            <form method="POST" action="{{ route('owner.staff.toggle', $user) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-ghost">
                                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection