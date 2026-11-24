{{-- Shared activity log table & mobile cards. Expects $logs and optional $showBranch. --}}
@php($showBranch = $showBranch ?? false)

<!-- Tampilan Feed Kartu untuk Layar Mobile -->
<div class="activity-cards">
    @foreach ($logs as $log)
        @php($lines = $log->changeLines())
        <article class="activity-card">
            <div class="activity-card-head">
                <span class="activity-card-time">{{ $log->created_at?->format('d/m/Y H:i') ?? '—' }}</span>
                <span class="status {{ $log->actionTone() }}">{{ $log->actionLabel() }}</span>
            </div>

            <div class="activity-card-user">
                Oleh: <strong>{{ $log->user?->name ?? 'Sistem' }}</strong>
                <span class="muted">({{ $log->user?->role ?? 'Sistem' }})</span>
                @if ($showBranch && $log->branch)
                    <span class="muted">· {{ $log->branch->name }}</span>
                @endif
            </div>

            <div class="activity-card-subject">{{ $log->subject_label }}</div>

            @if ($log->description)
                <div class="activity-card-desc">{{ $log->description }}</div>
            @endif

            @if ($lines !== [])
                <div class="activity-changes-box">
                    @foreach ($lines as $line)
                        <div>• {{ $line }}</div>
                    @endforeach
                </div>
            @endif

            @if ($log->ip_address)
                <div class="activity-card-footer">
                    <span>IP: {{ $log->ip_address }}</span>
                </div>
            @endif
        </article>
    @endforeach
</div>

<!-- Tampilan Tabel untuk Layar Desktop -->
<div class="card activity-table-wrap" style="padding: 0">
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th style="min-width: 100px">Waktu</th>
                    <th>Pengguna</th>
                    <th>Aksi</th>
                    <th>Objek & Keterangan</th>
                    @if ($showBranch)
                        <th>Cabang</th>
                    @endif
                    <th style="min-width: 180px">Perubahan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $log)
                    @php($lines = $log->changeLines())
                    <tr>
                        <td style="white-space: nowrap">
                            <strong>{{ $log->created_at?->format('d/m/Y') ?? '—' }}</strong>
                            <div class="muted" style="font-size: 11.5px">
                                {{ $log->created_at?->format('H:i') }}
                            </div>
                        </td>
                        <td>
                            <strong>{{ $log->user?->name ?? 'Sistem' }}</strong>
                            <div class="muted" style="font-size: 12px">
                                {{ $log->user?->role ?? '—' }}
                            </div>
                        </td>
                        <td>
                            <span class="status {{ $log->actionTone() }}">{{ $log->actionLabel() }}</span>
                        </td>
                        <td>
                            <strong>{{ $log->subject_label }}</strong>
                            @if ($log->description)
                                <div class="muted" style="font-size: 12px; margin-top: 2px">{{ $log->description }}</div>
                            @endif
                        </td>
                        @if ($showBranch)
                            <td>{{ $log->branch?->name ?? '—' }}</td>
                        @endif
                        <td>
                            @if ($lines === [])
                                <span class="muted">—</span>
                            @else
                                <div style="display: flex; flex-direction: column; gap: 2px">
                                    @foreach ($lines as $line)
                                        <div class="muted" style="font-size: 12px">• {{ $line }}</div>
                                    @endforeach
                                </div>
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
    <div class="pager" style="margin-top: 16px">
        <div>
            @if (! $logs->onFirstPage())
                <a href="{{ $logs->previousPageUrl() }}" class="btn btn-sm btn-ghost">← Sebelumnya</a>
            @endif
        </div>
        <span class="muted" style="font-size: 13px; font-weight: 600">
            Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}
        </span>
        <div>
            @if ($logs->hasMorePages())
                <a href="{{ $logs->nextPageUrl() }}" class="btn btn-sm btn-ghost">Berikutnya →</a>
            @endif
        </div>
    </div>
@endif
