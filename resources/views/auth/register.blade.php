@extends('layouts.app')

@section('title', 'Daftar Akun - Jagung Bakar WJT')

@section('theme_color', '#3c2820')

@section('body_class', 'is-login')

@section('content')
    <div class="auth-split">
        @include('partials.auth-aside')

        <section class="auth-main auth-main-stack">
            <div class="auth-form-box">
                <p class="auth-eyebrow auth-eyebrow-red">Akun baru</p>
                <h1 class="auth-title">Daftar Akun</h1>
                <p class="auth-sub">Isi data di bawah. Akun aktif setelah disetujui owner.</p>

                @include('partials.alerts')

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="field">
                        <label for="name">Nama Lengkap</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Masukkan nama lengkap"
                            autocomplete="name"
                            maxlength="120"
                            required
                            autofocus
                        >
                    </div>

                    <div class="field">
                        <label for="address">Alamat</label>
                        <textarea
                            id="address"
                            name="address"
                            placeholder="Masukkan alamat"
                            autocomplete="street-address"
                            maxlength="500"
                            required
                        >{{ old('address') }}</textarea>
                    </div>

                    <div class="field">
                        <label for="phone">Nomor Telepon</label>
                        <input
                            id="phone"
                            type="tel"
                            name="phone"
                            value="{{ old('phone') }}"
                            placeholder="08xxxxxxxxxx"
                            inputmode="tel"
                            autocomplete="tel"
                            maxlength="30"
                            required
                        >
                    </div>

                    <div class="field">
                        <label for="email">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nama@gmail.com"
                            inputmode="email"
                            autocomplete="email"
                            maxlength="255"
                            required
                        >
                    </div>

                    <div class="field">
                        <label for="password">Kata sandi</label>
                        <div class="auth-field-row">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Minimal 8 karakter"
                                autocomplete="new-password"
                                minlength="8"
                                required
                            >
                            <button
                                type="button"
                                class="password-text-toggle"
                                data-password-toggle="password"
                                data-password-text
                                aria-label="Tampilkan kata sandi"
                                aria-pressed="false"
                            >Tampilkan</button>
                        </div>
                    </div>

                    <button type="submit" class="btn auth-submit">Daftar</button>
                </form>

                <p class="auth-after">
                    Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
                </p>

                <div class="auth-divider" aria-hidden="true"></div>

                <p class="auth-legal">
                    Khusus pegawai {{ config('app.name', 'Jagung Bakar WJT') }}.<br>
                    Jaga kerahasiaan akun dan kata sandi Anda.
                </p>
            </div>
        </section>
    </div>
@endsection
