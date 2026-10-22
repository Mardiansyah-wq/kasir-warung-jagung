@extends('layouts.app')

@section('title', 'Daftar Akun - Hotmad')

@section('content')
    <div class="auth-page">
        <div class="card auth-card">
            <div class="login-brand">
                <h1>Daftar Akun</h1>
                <p class="muted">Buat akun untuk mulai menggunakan Hotmad.</p>
            </div>

            @if ($errors->any())
                <div class="errors">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

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

                <button type="submit" class="btn btn-block">Daftar</button>
            </form>

            <p class="auth-footer">
                Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
            </p>
        </div>
    </div>
@endsection
