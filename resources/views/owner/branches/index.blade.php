@extends('layouts.app')

@section('title', 'Manajemen Cabang')

@section('content')
<div class="container py-6">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <h1 class="h2 mb-0">Manajemen Cabang</h1>
        <a href="{{ route('owner.branches.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Cabang Baru
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
            <h5 class="card-title mb-0">Daftar Cabang</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
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
                    @forelse ($branches as $branch)
                        <tr>
                            <td>{{ $branch->name }}</td>
                            <td>{{ $branch->address ?: '-' }}</td>
                            <td>{{ $branch->phone ?: '-' }}</td>
                            <td>
                                @if ($branch->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('owner.branches.edit', $branch) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil me-1"></i> Ubah
                                    </a>
                                    <form action="{{ route('owner.branches.toggle', $branch) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $branch->is_active ? 'btn-danger' : 'btn-success' }}">
                                            <i class="bi bi-{{ $branch->is_active ? 'x-lg' : 'check-lg' }} me-1"></i>
                                            {{ $branch->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Belum ada cabang. Gunakan tombol "Tambah Cabang Baru" di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection