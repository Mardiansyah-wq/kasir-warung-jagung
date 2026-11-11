@extends('layouts.app')

@section('title', 'Ubah Cabang - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Ubah Cabang</h1>
            <p class="muted">{{ $branch->name }}</p>
        </div>
        <a href="{{ route('owner.branches.index') }}" class="btn btn-ghost">Kembali</a>
    </div>

    @include('partials.alerts')

    <form method="POST" action="{{ route('owner.branches.update', $branch) }}" class="card form-card">
        @csrf
        @method('PUT')

        <div class="form-section" style="margin-top: 0; padding-top: 0; border-top: 0">
            <h2>Data Cabang</h2>

            <div class="field">
                <label for="name">Nama Cabang</label>
                <input id="name" type="text" name="name" value="{{ old('name', $branch->name) }}" maxlength="100" required>
            </div>

            <div class="field">
                <label for="address">Alamat</label>
                <textarea id="address" name="address" maxlength="500">{{ old('address', $branch->address) }}</textarea>
            </div>

            <div class="field">
                <label for="phone">Nomor Telepon</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $branch->phone) }}" maxlength="30">
            </div>

            <p class="hint">
                Status cabang: {{ $branch->is_active ? 'Aktif' : 'Nonaktif' }}.
                Untuk mengubah status, gunakan tombol di halaman Manajemen Cabang.
            </p>
        </div>

        <button type="submit" class="btn btn-block">Simpan Perubahan</button>
    </form>
@endsection