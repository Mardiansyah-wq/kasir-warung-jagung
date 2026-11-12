@extends('layouts.app')

@section('title', 'Kelola Stok - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Kelola Stok</h1>
            <p class="muted">{{ $branch?->name ?? 'Cabang belum ditentukan' }}</p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap">
            <a href="{{ route('staff.activity.index') }}" class="btn btn-ghost">Log Aktivitas</a>
            <span class="badge">Staff</span>
        </div>
    </div>

    @include('partials.alerts')

    @unless ($branch)
        <div class="card empty">
            <h2>Akun belum terhubung ke cabang</h2>
            <p class="muted">Hubungi owner untuk menghubungkan akun staff ke cabang.</p>
        </div>
    @else
        <p class="hint" style="margin-bottom: 14px">
            Setiap perubahan stok tercatat di riwayat stok dan log aktivitas.
            Pilih <strong>Kurangi</strong> saat barang rusak atau tumpah, dan wajib isi catatan alasannya.
        </p>

        @foreach ($menus as $menu)
            <h2 class="group-title">
                {{ $menu->name }}
                <span class="badge">{{ $menu->categoryLabel() }}</span>
            </h2>

            <div class="card">
                @foreach ($menu->variants as $variant)
                    @php
                        $stock = $variant->stockFor($branch->id);
                    @endphp

                    <div class="stock-row" id="variant-{{ $variant->id }}">
                        <div>
                            <strong>{{ $variant->displayName() }}</strong>
                            <div class="muted">
                                Rp {{ number_format((float) $variant->selling_price, 0, ',', '.') }}
                                · <span class="stock-pill {{ $stock < 1 ? 'low' : '' }}">Stok {{ $stock }}</span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('staff.stock.store') }}" data-stock-form>
                            @csrf
                            <input type="hidden" name="variant_id" value="{{ $variant->id }}">

                            <select name="mode" data-stock-mode aria-label="Jenis perubahan stok {{ $menu->name }} {{ $variant->displayName() }}">
                                <option value="in" @selected(old('variant_id') == $variant->id && old('mode') !== 'out')>Tambah (+)</option>
                                <option value="out" @selected(old('variant_id') == $variant->id && old('mode') === 'out')>Kurangi (−)</option>
                            </select>

                            <input
                                type="number"
                                name="quantity"
                                value="{{ old('variant_id') == $variant->id ? old('quantity', 1) : 1 }}"
                                min="1"
                                max="9999"
                                aria-label="Jumlah stok {{ $menu->name }} {{ $variant->displayName() }}"
                            >

                            <input
                                type="text"
                                name="note"
                                value="{{ old('variant_id') == $variant->id ? old('note') : '' }}"
                                maxlength="255"
                                placeholder="Catatan (wajib jika kurangi)"
                                data-stock-note
                                aria-label="Catatan stok {{ $menu->name }} {{ $variant->displayName() }}"
                            >

                            <button type="submit" class="btn btn-sm">Simpan</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endforeach

        <h2 class="group-title">Riwayat Stok Terbaru</h2>

        @if ($movements->isEmpty())
            <div class="card empty">
                <p class="muted" style="margin: 0">Belum ada pergerakan stok.</p>
            </div>
        @else
            <div class="card" style="padding: 0">
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Menu</th>
                                <th>Jenis</th>
                                <th class="amount">Jumlah</th>
                                <th>Oleh</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($movements as $movement)
                                <tr>
                                    <td>{{ $movement->occurred_at?->format('d/m/Y H:i') }}</td>
                                    <td>
                                        {{ $movement->variant?->menu?->name ?? '—' }}
                                        <div class="muted">{{ $movement->variant?->displayName() }}</div>
                                    </td>
                                    <td>
                                        @if ($movement->movement_type === 'in')
                                            <span class="status status-finalized">Masuk</span>
                                        @elseif ($movement->movement_type === 'out')
                                            <span class="status status-cancelled">Keluar</span>
                                        @else
                                            <span class="status status-draft">Koreksi</span>
                                        @endif
                                    </td>
                                    <td class="amount">{{ $movement->quantity }}</td>
                                    <td>{{ $movement->user?->name ?? '—' }}</td>
                                    <td>{{ $movement->note ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endunless
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-stock-form]').forEach(function (form) {
            const mode = form.querySelector('[data-stock-mode]');
            const note = form.querySelector('[data-stock-note]');

            if (!mode || !note) {
                return;
            }

            const sync = function () {
                const reduce = mode.value === 'out';
                note.required = reduce;
                note.placeholder = reduce ? 'Catatan wajib: rusak / tumpah' : 'Catatan (opsional)';
            };

            mode.addEventListener('change', sync);
            sync();
        });
    </script>
@endpush
