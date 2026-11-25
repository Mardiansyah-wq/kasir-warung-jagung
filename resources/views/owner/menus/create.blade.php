@extends('layouts.app')

@section('title', 'Tambah Menu - Jagung Bakar WJT')

@section('body_class', 'page-owner-menu-form')

@push('styles')
    <style>
        body.page-owner-menu-form .form-card .hint {
            display: block;
            margin-top: 6px;
            overflow-wrap: anywhere;
        }

        body.page-owner-menu-form .form-section h2 {
            overflow-wrap: anywhere;
        }
    </style>
@endpush

@section('content')
    <div class="page-head">
        <div>
            <h1>Tambah Menu</h1>
            <p class="muted">Isi menu dasarnya, lalu tentukan satu varian pertama.</p>
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
            <p class="muted">Varian adalah pilihan yang dipilih pembeli di kasir. Bisa ditambah lagi setelah menu disimpan.</p>

            <div class="grid-2">
                <div class="field">
                    <label for="type">Jenis / Penyajian (opsional)</label>
                    <input
                        id="type"
                        type="text"
                        name="type"
                        value="{{ old('type') }}"
                        placeholder="Misal: Utuh, Serut, Pisang Raja, Es"
                        maxlength="50"
                        aria-describedby="type-hint"
                        autocomplete="off"
                    >
                    <span class="hint" id="type-hint">
                        Contoh: Jagung → Utuh atau Serut. Pisang → Pisang Raja atau Pisang Kepok.
                    </span>
                </div>

                <div class="field">
                    <label for="variant_name">Rasa / Varian (wajib)</label>
                    <input
                        id="variant_name"
                        type="text"
                        name="variant_name"
                        value="{{ old('variant_name') }}"
                        placeholder="Misal: Original, Pedas Manis, Keju"
                        maxlength="100"
                        aria-describedby="variant-hint"
                        required
                    >
                    <span class="hint" id="variant-hint">Rasa atau pilihan yang dipesan pembeli.</span>
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
                Nama di kasir: <strong>Jenis · Rasa</strong> kalau jenis diisi. Kalau kosong, cukup rasa saja.
            </p>
        </div>

        <button type="submit" class="btn btn-block" data-menu-submit>Simpan Menu</button>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('js/menu-image.js') }}" defer></script>
@endpush
