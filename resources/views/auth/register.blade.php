@extends('layouts.app')

@section('title', 'Daftar Akun - Jagung Bakar WJT')

@section('theme_color', '#2e2117')

@section('body_class', 'is-login')

@section('content')
    <div class="auth-split">
        @include('partials.auth-aside')

        <section class="auth-main auth-main-top">
            <div class="auth-form-box">
                <p class="auth-eyebrow auth-eyebrow-dark">Akun baru</p>
                <h1>Daftar Akun</h1>
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
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                autocomplete="new-password"
                                minlength="8"
                                required
                            >
                            @include('partials.password-toggle', ['target' => 'password'])
                        </div>
                        <span class="hint">Minimal 8 karakter.</span>
                    </div>

                    <button type="submit" class="btn auth-submit">Daftar</button>
                </form>

                <div class="auth-divider"><span>atau</span></div>

                <a href="{{ route('login') }}" class="btn auth-secondary">Masuk ke akun</a>
            </div>
        </section>
    </div>
@endsection
