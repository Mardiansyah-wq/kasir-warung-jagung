@extends('layouts.app')

@section('title', 'Akun Saya - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Akun Saya</h1>
            <p class="muted">
                {{ $user->name }} · {{ $user->email }}
            </p>
        </div>
        @if ($user->isOwner())
            <span class="badge">Owner</span>
        @else
            <span class="badge">{{ $user->branch?->name ?? 'Staff' }}</span>
        @endif
    </div>

    @include('partials.alerts')

    <form method="POST" action="{{ route('account.update') }}" class="card form-card">
        @csrf
        @method('PATCH')

        <div class="form-section" style="margin-top: 0; padding-top: 0; border-top: 0">
            <h2>Profil</h2>
            <p class="muted" style="margin-top: -6px">
                Data ini dipakai untuk login dan ditampilkan di riwayat aktivitas.
            </p>

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
                <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="30">
            </div>

            <div class="field">
                <label for="address">Alamat</label>
                <textarea id="address" name="address" maxlength="500">{{ old('address', $user->address) }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Simpan Profil</button>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('account.password') }}" class="card form-card" style="margin-top: 16px">
        @csrf
        @method('PATCH')

        <div class="form-section" style="margin-top: 0; padding-top: 0; border-top: 0">
            <h2>Ganti Password</h2>
            <p class="muted" style="margin-top: -6px">
                Minimal 8 karakter. Gunakan password yang tidak dipakai di akun lain.
            </p>

            <div class="field">
                <label for="current_password">Password Saat Ini</label>
                <input id="current_password" type="password" name="current_password" autocomplete="current-password" required>
            </div>

            <div class="field">
                <label for="password">Password Baru</label>
                <input id="password" type="password" name="password" minlength="8" autocomplete="new-password" required>
            </div>

            <div class="field">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Simpan Password</button>
            </div>
        </div>
    </form>

    <div class="card form-card" style="margin-top: 16px">
        <h2>Sesi</h2>
        <p class="muted">
            Keluar akan mengakhiri sesi di perangkat ini. Kamu perlu login ulang untuk masuk.
        </p>

        <form method="POST" action="{{ route('logout') }}" class="form-actions" style="margin-top: 0">
            @csrf
            <button type="submit" class="btn btn-ghost">Keluar</button>
        </form>
    </div>
@endsection
