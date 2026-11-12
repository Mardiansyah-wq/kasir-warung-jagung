@extends('layouts.app')

@section('title', 'Kelola Stok - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Kelola Stok</h1>
            <p class="muted">
                Tambah, kurangi, atau koreksi stok per cabang. Semua perubahan tercatat di log aktivitas.
            </p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap">
            <a href="{{ route('owner.activity.index', ['action' => 'stock']) }}" class="btn btn-ghost">Log Stok</a>
            <span class="badge">Owner</span>
        </div>
    </div>

    @include('partials.alerts')

    @if ($branches->isEmpty())
        <div class="card empty">
            <h2>Belum ada cabang</h2>
            <p class="muted">Tambahkan cabang terlebih dahulu sebelum mengelola stok.</p>
            <a href="{{ route('owner.branches.create') }}" class="btn">+ Tambah Cabang</a>
        </div>
    @else
        <form method="GET" action="{{ route('owner.stock.index') }}" class="filter-bar">
            <div class="field">
                <label for="branch_id">Cabang</label>
                <select id="branch_id" name="branch_id" onchange="this.form.submit()">
                    @foreach ($branches as $option)
                        <option value="{{ $option->id }}" @selected($branch?->id === $option->id)>
                            {{ $option->name }}@unless ($option->is_active) (nonaktif)@endunless
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn">Tampilkan</button>
        </form>

        @if (! $branch)
            <div class="card empty">
                <p class="muted" style="margin: 0">Pilih cabang untuk mulai mengelola stok.</p>
            </div>
        @elseif ($menus->isEmpty())
            <div class="card empty">
                <h2>Belum ada menu aktif</h2>
                <p class="muted">Aktifkan menu dan varian terlebih dahulu agar stoknya bisa dikelola.</p>
            </div>
        @else
            <p class="hint" style="margin-bottom: 14px">
                <strong>Koreksi</strong> berarti stok dijadikan sejumlah angka yang diisi (bukan ditambah).
                Stok saat ini untuk {{ $branch->name }}.
            </p>

            @foreach ($menus as $menu)
                <h2 class="group-title">
                    {{ $menu->name }}
                    <span class="badge">{{ $menu->categoryLabel() }}</span>
                </h2>

                <div class="card">
                    @foreach ($menu->variants as $variant)
                        @php($stock = $variant->stockFor($branch->id))

                        <div class="stock-row" id="variant-{{ $variant->id }}">
                            <div>
                                <strong>{{ $variant->displayName() }}</strong>
                                <div class="muted">
                                    Rp {{ number_format((float) $variant->selling_price, 0, ',', '.') }}
                                    · <span class="stock-pill {{ $stock < 1 ? 'low' : '' }}">Stok {{ $stock }}</span>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('owner.stock.store') }}">
                                @csrf
                                <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                                <input type="hidden" name="variant_id" value="{{ $variant->id }}">

                                <select name="mode" aria-label="Jenis penyesuaian stok {{ $menu->name }} {{ $variant->displayName() }}">
                                    <option value="in" @selected(old('variant_id') == $variant->id && old('mode') === 'in')>Tambah (+)</option>
                                    <option value="out" @selected(old('variant_id') == $variant->id && old('mode') === 'out')>Kurangi (−)</option>
                                    <option value="set" @selected(old('variant_id') == $variant->id && old('mode') === 'set')>Koreksi (=)</option>
                                </select>

                                <input
                                    type="number"
                                    name="quantity"
                                    value="{{ old('variant_id') == $variant->id ? old('quantity', 1) : 1 }}"
                                    min="0"
                                    max="999999"
                                    aria-label="Jumlah stok {{ $menu->name }} {{ $variant->displayName() }}"
                                >

                                <button type="submit" class="btn btn-sm">Simpan</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif
    @endif
@endsection
