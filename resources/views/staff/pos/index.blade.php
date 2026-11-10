@extends('layouts.app')

@section('title', 'Papan Pesanan - Jagung Bakar WJT')

@section('content')
    <div class="page-head">
        <div>
            <h1>Papan Pesanan</h1>
            <p class="muted">{{ $branch?->name ?? 'Cabang belum ditentukan' }}</p>
        </div>
        <div class="cart-controls" style="margin-top: 0">
            <a href="{{ route('staff.orders.index') }}" class="btn btn-sm btn-ghost">Riwayat</a>
            <a href="{{ route('staff.stock.index') }}" class="btn btn-sm btn-ghost">Tambah Stok</a>
        </div>
    </div>

    @include('partials.alerts')

    @if (session('stock_shortage') || $errors->has('stock'))
        <p class="hint" style="margin: -8px 0 14px">
            <a href="{{ route('staff.stock.index') }}" class="btn btn-sm">Tambah Stok Sekarang</a>
        </p>
    @endif

    @unless ($branch)
        <div class="card empty">
            <h2>Akun belum terhubung ke cabang</h2>
            <p class="muted">Hubungi owner untuk menghubungkan akun staff ke cabang sebelum bertransaksi.</p>
        </div>
    @else
        <form method="POST" action="{{ route('staff.pos.store') }}" class="new-order">
            @csrf
            <div class="field">
                <label for="customer_name">Nama pelanggan</label>
                <input
                    type="text"
                    id="customer_name"
                    name="customer_name"
                    value="{{ old('customer_name') }}"
                    maxlength="120"
                    placeholder="Contoh: Budi"
                    required
                >
            </div>
            <div class="field">
                <label for="order_type">Tipe pesanan</label>
                <select id="order_type" name="order_type" required>
                    <option value="dine_in" @selected(old('order_type') === 'dine_in')>Dine-in</option>
                    <option value="takeaway" @selected(old('order_type') === 'takeaway')>Bawa Pulang</option>
                </select>
            </div>
            <button type="submit" class="btn">+ Pesanan Baru</button>
        </form>

        <div class="chips">
            <a href="{{ route('staff.pos.index', ['status' => 'active', 'search' => $search]) }}" class="chip {{ $status === 'active' ? 'active' : '' }}">
                Semua Aktif ({{ $counts['active'] }})
            </a>
            <a href="{{ route('staff.pos.index', ['status' => 'open', 'search' => $search]) }}" class="chip {{ $status === 'open' ? 'active' : '' }}">
                Baru ({{ $counts['open'] }})
            </a>
            <a href="{{ route('staff.pos.index', ['status' => 'processing', 'search' => $search]) }}" class="chip {{ $status === 'processing' ? 'active' : '' }}">
                Diproses ({{ $counts['processing'] }})
            </a>
            <a href="{{ route('staff.pos.index', ['status' => 'ready', 'search' => $search]) }}" class="chip {{ $status === 'ready' ? 'active' : '' }}">
                Siap ({{ $counts['ready'] }})
            </a>
            <a href="{{ route('staff.pos.index', ['status' => 'completed', 'search' => $search]) }}" class="chip {{ $status === 'completed' ? 'active' : '' }}">
                Selesai ({{ $counts['completed'] }})
            </a>
            <a href="{{ route('staff.pos.index', ['status' => 'cancelled', 'search' => $search]) }}" class="chip {{ $status === 'cancelled' ? 'active' : '' }}">
                Batal ({{ $counts['cancelled'] }})
            </a>
            <a href="{{ route('staff.pos.index', ['status' => 'all', 'search' => $search]) }}" class="chip {{ $status === 'all' ? 'active' : '' }}">
                Semua
            </a>
        </div>

        <form method="GET" action="{{ route('staff.pos.index') }}" class="filter-bar">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="field" style="flex: 1 1 220px">
                <label for="search">Cari nama / nomor pesanan</label>
                <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Budi, #023, atau kode order">
            </div>
            <button type="submit" class="btn">Cari</button>
            @if ($search !== '')
                <a href="{{ route('staff.pos.index', ['status' => $status]) }}" class="btn btn-ghost">Reset</a>
            @endif
        </form>

        @if ($orders->isEmpty())
            <div class="card empty">
                <h2>Belum ada pesanan</h2>
                <p class="muted">Isi nama pelanggan di atas untuk membuat pesanan pertama.</p>
            </div>
        @else
            <div class="order-grid">
                @foreach ($orders as $order)
                    @php
                        $paid = $order->paidAmount();
                        $due = $order->balanceDue();
                    @endphp

                    <article class="order-card order-{{ $order->status }}">
                        <div class="order-card-head">
                            <div>
                                <div class="queue-number">{{ $order->queueLabel() }}</div>
                                <h2>{{ $order->customer_name }}</h2>
                            </div>
                            <span class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
                        </div>

                        <div class="order-card-meta">
                            <span>{{ $order->orderTypeLabel() }}</span>
                            <span>{{ $order->created_at?->format('H:i') }}</span>
                            <span>{{ $order->items_count }} item</span>
                            <span class="status status-{{ $order->paymentStatus() }}">{{ $order->paymentStatusLabel() }}</span>
                        </div>

                        <div class="order-card-money">
                            <span>Total <strong>Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</strong></span>
                            <span>Dibayar Rp {{ number_format($paid, 0, ',', '.') }}</span>
                            @if ($due > 0)
                                <span>Sisa Rp {{ number_format($due, 0, ',', '.') }}</span>
                            @endif
                        </div>

                        <div class="order-card-actions">
                            @if ($order->isActive())
                                <a href="{{ route('staff.pos.show', $order) }}" class="btn btn-sm">Buka</a>
                            @else
                                <a href="{{ route('staff.orders.show', $order) }}" class="btn btn-sm btn-ghost">Struk</a>
                            @endif

                            @if ($order->isOpen() || $order->isProcessing() || $order->isReady())
                                <form method="POST" action="{{ route('staff.orders.status', $order) }}">
                                    @csrf
                                    @method('PATCH')
                                    @if ($order->isOpen())
                                        <input type="hidden" name="status" value="processing">
                                        <button type="submit" class="btn btn-sm btn-ghost">Mulai Masak</button>
                                    @elseif ($order->isProcessing())
                                        <input type="hidden" name="status" value="ready">
                                        <button type="submit" class="btn btn-sm btn-ghost">Siap</button>
                                    @elseif ($order->canComplete())
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn btn-sm">Selesai</button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-ghost" disabled>Belum lunas</button>
                                    @endif
                                </form>
                            @endif

                            @if ($order->canCancel())
                                <form method="POST" action="{{ route('staff.orders.cancel', $order) }}" onsubmit="return confirm('Batalkan pesanan {{ $order->queueLabel() }}?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-ghost">Batal</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    @endunless
@endsection
