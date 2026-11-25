@extends('layouts.app')

@section('title', 'Masuk - Jagung Bakar WJT')

@section('theme_color', '#3c2820')

@section('body_class', 'is-login')

@section('content')
    <div class="auth-split">
        @include('partials.auth-aside')

        <section class="auth-main">
            <div class="auth-form-box">
                <p class="auth-eyebrow auth-eyebrow-red">Masuk ke akun</p>
                <h1 class="auth-title">Selamat datang kembali</h1>
                <p class="auth-sub">Masuk untuk mulai mengelola {{ config('app.name', 'Jagung Bakar WJT') }}.</p>

                @include('partials.alerts')

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="field">
                        <label for="email">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Masukkan email"
                            inputmode="email"
                            autocomplete="email"
                            required
                            autofocus
                        >
                    </div>

                    <div class="field">
                        <label for="password">Kata sandi</label>
                        <div class="auth-field-row">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Masukkan kata sandi"
                                autocomplete="current-password"
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

                    <div class="auth-meta">
                        <button type="button" data-dialog-open="lupa-sandi">Lupa kata sandi?</button>
                    </div>

                    <button type="submit" class="btn auth-submit">Masuk</button>
                </form>

                <div class="auth-divider" aria-hidden="true"></div>

                <p class="auth-legal">
                    Khusus pengelola {{ config('app.name', 'Jagung Bakar WJT') }}.<br>
                    Jaga kerahasiaan akun dan kata sandi Anda.
                </p>
            </div>

            <p class="auth-footnote">Panel pengelola &middot; {{ config('app.name', 'Jagung Bakar WJT') }}</p>
        </section>
    </div>

    <dialog id="lupa-sandi" class="auth-dialog" aria-labelledby="lupa-sandi-title">
        <h2 id="lupa-sandi-title">Lupa kata sandi?</h2>
        <p>
            Hubungi owner warung untuk mengganti kata sandi. Owner bisa membuka menu <strong>Staff</strong>,
            memilih <strong>Ubah</strong> pada akun Anda, lalu mengisi kolom kata sandi baru.
        </p>

        <form method="dialog" class="form-actions">
            <button type="submit" class="btn">Mengerti</button>
        </form>
    </dialog>
@endsection
