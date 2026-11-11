@extends('layouts.app')

@section('title', 'Tambah Staff - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Tambah Staff</h1>
            <p class="muted">Buat akun staff langsung aktif dan terhubung ke cabang.</p>
        </div>
        <a href="{{ route('owner.staff.index') }}" class="btn btn-ghost">Kembali</a>
    </div>

    @include('partials.alerts')

    <form method="POST" action="{{ route('owner.staff.store') }}" class="card form-card">
        @csrf

        <div class="form-section" style="margin-top: 0; padding-top: 0; border-top: 0">
            <h2>Data Staff</h2>

            <div class="field">
                <label for="name">Nama Lengkap</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" maxlength="120" required>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="255" required>
            </div>

            <div class="field">
                <label for="phone">Nomor Telepon</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone') }}" maxlength="30" required>
            </div>

            <div class="field">
                <label for="address">Alamat</label>
                <textarea id="address" name="address" maxlength="500" required>{{ old('address') }}</textarea>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" minlength="8" required>
                <span class="hint">Minimal 8 karakter. Sampaikan ke staff agar bisa dipakai login.</span>
            </div>

            <div class="field">
                <label for="branch_id">Cabang</label>
                <select id="branch_id" name="branch_id" required>
                    <option value="">Pilih cabang...</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                @if ($branches->isEmpty())
                    <span class="hint">Belum ada cabang aktif. Tambahkan cabang dulu di menu Cabang.</span>
                @endif
            </div>
        </div>

        <button type="submit" class="btn btn-block">Simpan Staff</button>
    </form>
@endsection