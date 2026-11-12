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
            Tambah stok di sini saat bahan atau menu habis. Setiap penambahan tercatat di riwayat stok dan log aktivitas.
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

                        <form method="POST" action="{{ route('staff.stock.store') }}">
                            @csrf
                            <input type="hidden" name="variant_id" value="{{ $variant->id }}">
                            <input
                                type="number"
                                name="quantity"
                                value="1"
                                min="1"
                                max="9999"
                                aria-label="Jumlah stok {{ $menu->name }} {{ $variant->displayName() }}"
                            >
                            <button type="submit" class="btn btn-sm">Tambah</button>
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
