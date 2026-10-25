@extends('layouts.app')

@section('title', 'Tambah Menu - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Tambah Menu</h1>
            <p class="muted">Menu baru langsung punya satu varian.</p>
        </div>
        <a href="{{ route('owner.menus.index') }}" class="btn btn-ghost">Kembali</a>
    </div>

    @include('partials.alerts')

    <form
        method="POST"
        action="{{ route('owner.menus.store') }}"
        enctype="multipart/form-data"
        class="card form-card"
        data-menu-form
    >
        @csrf

        @include('owner.menus._form', ['menu' => null])

        <div class="form-section">
            <h2>Varian Pertama</h2>
            <p class="muted">Varian bisa ditambah lagi setelah menu disimpan.</p>

            <div class="grid-2">
                <div class="field">
                    <label for="variant_name">Nama Varian</label>
                    <input
                        id="variant_name"
                        type="text"
                        name="variant_name"
                        value="{{ old('variant_name') }}"
                        placeholder="Contoh: Serut, Utuh, Pisang Raja"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="field">
                    <label for="selling_price">Harga</label>
                    <input
                        id="selling_price"
                        type="number"
                        name="selling_price"
                        value="{{ old('selling_price') }}"
                        min="0"
                        step="any"
                        inputmode="numeric"
                        required
                    >
                </div>
            </div>

            <p class="hint">
                Contoh: Jagung → Serut / Utuh. Pisang → Pisang Raja / Pisang Kepok.
            </p>
        </div>

        <button type="submit" class="btn btn-block" data-menu-submit>Simpan Menu</button>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('js/menu-image.js') }}" defer></script>
@endpush
