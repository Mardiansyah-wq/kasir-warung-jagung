{{-- Shared activity log table. Expects $logs and optional $showBranch. --}}
@php($showBranch = $showBranch ?? false)

<div class="card" style="padding: 0">
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pengguna</th>
                    <th>Aksi</th>
                    <th>Objek</th>
                    @if ($showBranch)
                        <th>Cabang</th>
                    @endif
                    <th>Perubahan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $log)
                    <tr>
                        <td style="white-space: nowrap">
                            {{ $log->created_at?->format('d/m/Y') ?? '—' }}
                            <div class="muted" style="font-size: 12px">
                                {{ $log->created_at?->format('H:i') }}
                            </div>
                        </td>
                        <td>
                            {{ $log->user?->name ?? 'Sistem' }}
                            <div class="muted" style="font-size: 12px">
                                {{ $log->user?->role ?? '—' }}
                            </div>
                        </td>
                        <td>
                            <span class="status {{ $log->actionTone() }}">{{ $log->actionLabel() }}</span>
                        </td>
                        <td>
                            <strong>{{ $log->subject_label }}</strong>
                            <div class="muted" style="font-size: 12px">{{ $log->description }}</div>
                        </td>
                        @if ($showBranch)
                            <td>{{ $log->branch?->name ?? '—' }}</td>
                        @endif
                        <td>
                            @php($lines = $log->changeLines())
                            @if ($lines === [])
                                <span class="muted">—</span>
                            @else
                                @foreach ($lines as $line)
                                    <div class="muted" style="font-size: 12px">{{ $line }}</div>
                                @endforeach
                            @endif
                            @if ($log->ip_address)
                                <div class="muted" style="font-size: 11px; margin-top: 4px">IP {{ $log->ip_address }}</div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if ($logs->hasPages())
    <div class="pager">
        <div>
            @if (! $logs->onFirstPage())
                <a href="{{ $logs->previousPageUrl() }}">← Sebelumnya</a>
            @endif
        </div>
        <span class="muted">Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}</span>
        <div>
            @if ($logs->hasMorePages())
                <a href="{{ $logs->nextPageUrl() }}">Berikutnya →</a>
            @endif
        </div>
    </div>
@endif
