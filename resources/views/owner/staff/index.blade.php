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
                <table class="data">
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
                                <td>
                                    {{ $user->name }}
                                    @if ($user->phone)
                                        <div class="muted">{{ $user->phone }}</div>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if ($user->branch)
                                        {{ $user->branch->name }}
                                    @else
                                        <span class="muted">Belum diassign</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->is_active)
                                        <span class="status status-ready">Aktif</span>
                                    @elseif ($user->branch_id === null)
                                        <span class="status status-processing">Menunggu</span>
                                    @else
                                        <span class="status status-completed">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
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
                                                aria-label="Cabang untuk {{ $user->name }}"
                                                style="width: auto; min-height: 36px; padding: 6px 10px; font-size: 13px"
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
                                        <div style="display: flex; gap: 6px; flex-wrap: wrap">
                                            <a href="{{ route('owner.staff.edit', $user) }}" class="btn btn-sm btn-ghost">Ubah</a>

                                            <form method="POST" action="{{ route('owner.staff.toggle', $user) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-ghost">
                                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>

                                            <form
                                                method="POST"
                                                action="{{ route('owner.staff.reset-password', $user) }}"
                                                style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap"
                                            >
                                                @csrf
                                                <input
                                                    type="password"
                                                    name="password"
                                                    minlength="8"
                                                    placeholder="Password baru"
                                                    aria-label="Password baru untuk {{ $user->name }}"
                                                    style="width: 150px; min-height: 36px; padding: 6px 10px; font-size: 13px"
                                                    required
                                                >
                                                <button type="submit" class="btn btn-sm btn-ghost">Reset</button>
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