@extends('layouts.app')

@section('title', 'Manajemen Staff - Jagung Bakar WJT')

@section('body_class', 'page-owner-staff')

@push('styles')
    <style>
        body.page-owner-staff .staff-identity h2,
        body.page-owner-staff .staff-identity p {
            overflow-wrap: anywhere;
        }

        body.page-owner-staff .staff-actions {
            row-gap: 8px;
        }

        @media (max-width: 639px) {
            body.page-owner-staff .staff-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            body.page-owner-staff .staff-pending {
                flex-direction: column;
                align-items: stretch;
            }

            body.page-owner-staff .staff-pending select,
            body.page-owner-staff .staff-pending .btn {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="page-head">
        <div>
            <h1>Manajemen Staff</h1>
            <p class="muted">Setujui pendaftar baru, atur cabang, dan kelola akun staff.</p>
        </div>
        <a href="{{ route('owner.staff.create') }}" class="btn">+ Tambah Staff</a>
    </div>

    @include('partials.alerts')

    @if ($staff->isEmpty())
        <div class="card empty">
            <h2>Belum ada staff</h2>
            <p class="muted">Tambahkan staff pertama, atau tunggu ada yang mendaftar lewat halaman daftar.</p>
            <a href="{{ route('owner.staff.create') }}" class="btn">Tambah Staff</a>
        </div>
    @else
        <div class="staff-grid">
            @foreach ($staff as $user)
                @php
                    $initial = mb_strtoupper(mb_substr($user->name, 0, 2));
                    $pending = ! $user->is_active && $user->branch_id === null;
                @endphp

                <article class="staff-card {{ $user->is_active ? '' : 'is-inactive' }}">
                    <div class="staff-card-head">
                        <span class="staff-avatar" aria-hidden="true">{{ $initial }}</span>
                        <div class="staff-identity">
                            <h2>{{ $user->name }}</h2>
                            <p>{{ $user->email }}</p>
                        </div>

                        @if ($user->is_active)
                            <span class="status status-ready">Aktif</span>
                        @elseif ($pending)
                            <span class="status status-processing">Menunggu</span>
                        @else
                            <span class="status status-completed">Nonaktif</span>
                        @endif
                    </div>

                    <dl class="staff-meta">
                        <div>
                            <dt>Cabang</dt>
                            <dd>{{ $user->branch?->name ?? 'Belum diassign' }}</dd>
                        </div>
                        <div>
                            <dt>Telepon</dt>
                            <dd>{{ $user->phone ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt>Alamat</dt>
                            <dd>{{ $user->address ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt>Bergabung</dt>
                            <dd>{{ $user->created_at?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                    </dl>

                    @if ($pending)
                        <form
                            method="POST"
                            action="{{ route('owner.staff.toggle', $user) }}"
                            class="staff-pending"
                        >
                            @csrf
                            @method('PATCH')
                            <select
                                name="branch_id"
                                class="select-inline"
                                aria-label="Cabang untuk {{ $user->name }}"
                                required
                            >
                                <option value="">Pilih cabang...</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm">Setujui</button>
                        </form>
                    @else
                        <div class="staff-actions">
                            <a href="{{ route('owner.staff.edit', $user) }}" class="btn btn-sm btn-ghost">Ubah</a>

                            <form method="POST" action="{{ route('owner.staff.toggle', $user) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-ghost">
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
@endsection
