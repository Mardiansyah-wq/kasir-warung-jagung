@extends('layouts.app')

@section('title', 'Masuk - Kasir Warung Jagung')

@section('content')
    <div class="card auth">
        <h1>Warung Jagung Bakar</h1>
        <p class="muted">Masuk untuk mulai mengelola transaksi.</p>

        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <label>
                Username
                <input type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username">
            </label>

            <label>
                Password
                <input type="password" name="password" required autocomplete="current-password">
            </label>

            <button type="submit">Masuk</button>
        </form>
    </div>
@endsection
