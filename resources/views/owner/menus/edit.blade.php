@extends('layouts.app')

@section('title', 'Edit Menu - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Edit Menu</h1>
            <p class="muted">{{ $menu->name }} · {{ $menu->categoryLabel() }}</p>
        </div>
        <a href="{{ route('owner.menus.index') }}" class="btn btn-ghost">Kembali</a>
    </div>

    @include('partials.alerts')

    <form
        method="POST"
        action="{{ route('owner.menus.update', $menu) }}"
        enctype="multipart/form-data"
        class="card form-card"
        data-menu-form
    >
        @csrf
        @method('PUT')

        @include('owner.menus._form', ['menu' => $menu])

        <button type="submit" class="btn btn-block" data-menu-submit>Simpan Perubahan</button>
    </form>

    <div class="card form-card" style="margin-top: 16px">
        <div class="page-head" style="margin-bottom: 8px">
            <div>
                <h2>Varian</h2>
                <p class="muted">Harga tiap varian bisa berbeda.</p>
            </div>
        </div>

        @if ($menu->variants->isEmpty())
            <p class="muted">Belum ada varian.</p>
        @else
            <div class="variant-list">
                @foreach ($menu->variants as $variant)
                    <div class="variant-edit">
                        <form
                            method="POST"
                            action="{{ route('owner.menus.variants.update', [$menu, $variant]) }}"
                            class="variant-row"
                        >
                            @csrf
                            @method('PUT')

                            <input
                                type="text"
                                name="variant_name"
                                value="{{ $variant->variant_name }}"
                                aria-label="Nama varian"
                                maxlength="100"
                                required
                            >
                            <input
                                type="number"
                                name="selling_price"
                                value="{{ (float) $variant->selling_price }}"
                                aria-label="Harga varian"
                                min="0"
                                step="any"
                                inputmode="numeric"
                                required
                            >
                            <input type="hidden" name="is_active" value="0">
                            <label class="check">
                                <input type="checkbox" name="is_active" value="1" @checked($variant->is_active)>
                                Aktif
                            </label>
                            <button type="submit" class="btn btn-sm">Simpan</button>
                        </form>

                        <form
                            method="POST"
                            action="{{ route('owner.menus.variants.toggle', [$menu, $variant]) }}"
                            class="variant-toggle"
                        >
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-ghost">
                                {{ $variant->is_active ? 'Nonaktifkan varian' : 'Aktifkan varian' }}
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="form-section">
            <h2>Tambah Varian</h2>

            <form
                method="POST"
                action="{{ route('owner.menus.variants.store', $menu) }}"
                class="variant-row variant-row-add"
            >
                @csrf
                <input
                    type="text"
                    name="variant_name"
                    placeholder="Nama varian baru"
                    aria-label="Nama varian baru"
                    maxlength="100"
                    required
                >
                <input
                    type="number"
                    name="selling_price"
                    placeholder="Harga"
                    aria-label="Harga varian baru"
                    min="0"
                    step="any"
                    inputmode="numeric"
                    required
                >
                <input type="hidden" name="is_active" value="1">
                <button type="submit" class="btn btn-sm">Tambah Varian</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/menu-image.js') }}" defer></script>
@endpush
