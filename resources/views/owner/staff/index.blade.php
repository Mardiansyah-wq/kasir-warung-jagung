@extends('layouts.app')

@section('title', 'Manajemen Staff')

@section('content')
<div class="container py-6">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <h1 class="h2 mb-0">Manajemen Staff</h1>
        <a href="{{ route('owner.staff.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Staff Baru
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Daftar Staff</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
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
                    @forelse ($staff as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if ($user->branch)
                                    {{ $user->branch->name }}
                                @else
                                    <span class="text-muted">Belum diassign</span>
                                @endif
                            </td>
                            <td>
                                @if ($user->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @elseif ($user->is_active === false && $user->branch_id === null)
                                    <span class="badge bg-warning text-dark">Menunggu Persetujuan</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if ($user->is_active === false && $user->branch_id === null)
                                        <form action="{{ route('owner.staff.toggle', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="branch_id" value="{{ old('branch_id') }}">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-lg me-1"></i> Approve
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('owner.staff.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil me-1"></i> Ubah
                                        </a>
                                        <form action="{{ route('owner.staff.toggle', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-danger' : 'btn-success' }}">
                                                <i class="bi bi-{{ $user->is_active ? 'x-lg' : 'check-lg' }} me-1"></i>
                                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('owner.staff.reset-password', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-key me-1"></i> Reset Password
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Belum ada staff. Gunakan tombol "Tambah Staff Baru" di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection