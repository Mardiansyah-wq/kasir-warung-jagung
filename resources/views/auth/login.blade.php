@extends('layouts.app')

@section('title', 'Masuk - Jagung Bakar WJT')

@section('theme_color', '#2e2117')

@section('body_class', 'is-login')

@section('content')
    <div class="auth-split">
        @include('partials.auth-aside')

        <section class="auth-main">
            <div class="auth-form-box">
                <p class="auth-eyebrow auth-eyebrow-dark">Masuk ke akun</p>
                <h1>Selamat datang</h1>
                <p class="auth-sub">Masukkan email dan password untuk melanjutkan.</p>

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
                            placeholder="nama@gmail.com"
                            inputmode="email"
                            autocomplete="email"
                            required
                            autofocus
                        >
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                autocomplete="current-password"
                                required
                            >
                            @include('partials.password-toggle', ['target' => 'password'])
                        </div>
                    </div>

                    <button type="submit" class="btn auth-submit">Masuk</button>
                </form>

                <div class="auth-divider"><span>atau</span></div>

                <a href="{{ route('register') }}" class="btn auth-secondary">Daftar akun baru</a>

                <p class="auth-note">
                    Akun staff sebaiknya dibuat owner lewat menu Staff.
                </p>
            </div>
        </section>
    </div>
@endsection
