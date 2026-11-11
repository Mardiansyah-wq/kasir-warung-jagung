@extends('layouts.app')

@section('title', 'Tambah Cabang - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Tambah Cabang</h1>
            <p class="muted">Cabang baru langsung aktif dan bisa dipilih saat menambah staff.</p>
        </div>
        <a href="{{ route('owner.branches.index') }}" class="btn btn-ghost">Kembali</a>
    </div>

    @include('partials.alerts')

    <form method="POST" action="{{ route('owner.branches.store') }}" class="card form-card">
        @csrf

        <div class="form-section" style="margin-top: 0; padding-top: 0; border-top: 0">
            <h2>Data Cabang</h2>

            <div class="field">
                <label for="name">Nama Cabang</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" maxlength="100" required>
            </div>

            <div class="field">
                <label for="address">Alamat</label>
                <textarea id="address" name="address" maxlength="500">{{ old('address') }}</textarea>
            </div>

            <div class="field">
                <label for="phone">Nomor Telepon</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone') }}" maxlength="30">
            </div>
        </div>

        <button type="submit" class="btn btn-block">Simpan Cabang</button>
    </form>
@endsection