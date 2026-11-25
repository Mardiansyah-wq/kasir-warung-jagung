@extends('layouts.app')

@section('title', 'Pengaturan - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Pengaturan</h1>
            <p class="muted">Atur fitur operasional yang dipakai di kasir.</p>
        </div>
        @include('partials.role-chip', ['role' => 'owner'])
    </div>

    @include('partials.alerts')

    <div class="card form-card">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap">
            <div style="min-width: 0">
                <h2>Diskon Manual di Kasir</h2>
                <p class="muted" style="margin: 0">
                    Potongan harga per pesanan yang diisi langsung oleh kasir.
                </p>
            </div>
            @if ($discountsEnabled)
                <span class="status status-ready">Aktif</span>
            @else
                <span class="status status-completed">Nonaktif</span>
            @endif
        </div>

        <div class="form-section">
            @if ($discountsEnabled)
                <p class="muted">
                    Panel diskon tampil di halaman kasir. Setiap diskon yang diberikan tercatat
                    di log aktivitas owner beserta nominal, alasan, dan nama staff.
                </p>
            @else
                <p class="muted">
                    Panel diskon disembunyikan dari halaman kasir, sehingga staff tidak bisa
                    memberi potongan harga. Catatan pesanan tetap tersedia.
                </p>
            @endif

            <p class="hint">
                Diskon yang sudah tercatat di pesanan lama tetap tersimpan dan tetap tampil
                di struk, laporan, dan log aktivitas.
            </p>

            <form
                method="POST"
                action="{{ route('owner.settings.discount') }}"
                class="form-actions"
                onsubmit="return confirm('{{ $discountsEnabled ? 'Nonaktifkan diskon manual di kasir?' : 'Aktifkan diskon manual di kasir?' }}')"
            >
                @csrf
                @method('PATCH')
                <input type="hidden" name="enabled" value="{{ $discountsEnabled ? 0 : 1 }}">

                <button type="submit" class="btn {{ $discountsEnabled ? 'btn-ghost' : '' }}">
                    {{ $discountsEnabled ? 'Nonaktifkan diskon' : 'Aktifkan diskon' }}
                </button>
            </form>
        </div>
    </div>
@endsection
