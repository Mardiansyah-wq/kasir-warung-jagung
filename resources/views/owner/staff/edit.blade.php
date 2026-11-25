@extends('layouts.app')

@section('title', 'Ubah Staff - Jagung Bakar WJT')

@section('body_class', 'page-owner-staff-form')

@push('styles')
    <style>
        body.page-owner-staff-form .form-card .hint,
        body.page-owner-staff-form .form-card .muted {
            display: block;
            margin-top: 6px;
            overflow-wrap: anywhere;
        }

        body.page-owner-staff-form .staff-password-wrap {
            position: relative;
        }

        body.page-owner-staff-form .staff-password-wrap input {
            padding-right: 48px;
        }

        body.page-owner-staff-form .staff-password-wrap .toggle-password {
            right: 2px;
        }
    </style>
@endpush

@section('content')
    <div class="page-head">
        <div>
            <h1>Ubah Staff</h1>
            <p class="muted">{{ $user->name }} · {{ $user->email }}</p>
        </div>
        <a href="{{ route('owner.staff.index') }}" class="btn btn-ghost">Kembali</a>
    </div>

    @include('partials.alerts')

    <form method="POST" action="{{ route('owner.staff.update', $user) }}" class="card form-card">
        @csrf
        @method('PUT')

        <div class="form-section" style="margin-top: 0; padding-top: 0; border-top: 0">
            <h2>Data Staff</h2>

            <div class="field">
                <label for="name">Nama Lengkap</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" maxlength="120" required>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" maxlength="255" required>
            </div>

            <div class="field">
                <label for="phone">Nomor Telepon</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="30" required>
            </div>

            <div class="field">
                <label for="address">Alamat</label>
                <textarea id="address" name="address" maxlength="500" required>{{ old('address', $user->address) }}</textarea>
            </div>

            <div class="field">
                <label for="branch_id">Cabang</label>
                <select id="branch_id" name="branch_id" required>
                    <option value="">Pilih cabang...</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-section">
            <h2>Ganti Password Staff</h2>
            <p class="muted">Isi hanya jika ingin mengubah atau me-reset password akun staff ini.</p>

            <div class="field">
                <label for="password">Password Baru (opsional)</label>
                <div class="staff-password-wrap">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        minlength="8"
                        maxlength="255"
                        placeholder="Minimal 8 karakter (kosongkan jika tidak diubah)"
                        autocomplete="new-password"
                    >
                    @include('partials.password-toggle', ['target' => 'password'])
                </div>
                <span class="hint">Kosongkan jika tidak ingin mengganti password lama staff.</span>
            </div>

            <p class="hint">
                Status akun saat ini: <strong>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</strong>.
                Untuk mengubah status akun, gunakan tombol aktifkan/nonaktifkan di halaman Manajemen Staff.
            </p>
        </div>

        <button type="submit" class="btn btn-block">Simpan Perubahan</button>
    </form>
@endsection