@extends('layouts.app')

@section('title', 'Masuk - Jagung Bakar WJT')

@section('content')
    <div class="auth-page">
        <div class="card auth-card">
            <div class="login-brand">
                <h1>Jagung Bakar WJT</h1>
                <p class="muted">Warung Jagung Bakar & Pisang</p>
            </div>

            @if ($errors->any())
                <div class="errors">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

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

                <button type="submit" class="btn btn-block">Masuk</button>
            </form>

            <p class="auth-footer">
                Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a>
            </p>
        </div>
    </div>
@endsection
