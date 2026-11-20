@extends('layouts.app')

@section('title', 'Undangan Juri')

@section('content')
<div class="page-header-premium">
    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Ruang kerja juri</p>
    <h1>Undangan Juri</h1>
    <p>Panitia mengundang Anda untuk menilai event. Setujui undangan agar Anda bisa mengakses peserta dan memberi nilai.</p>
</div>

@if($pending->isEmpty())
<div class="premium-card">
    <div class="empty-state-premium">
        <div class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16v12H4zM4 7l8 6 8-6" />
            </svg>
        </div>
        <h3>Tidak Ada Undangan Baru</h3>
        <p>Semua undangan sudah Anda jawab. Undangan baru akan muncul di sini.</p>
    </div>
</div>
@else
<div class="grid gap-4">
    @foreach($pending as $invitation)
    <div class="premium-card !mb-0">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge-premium badge-premium-warning">Menunggu persetujuan</span>
                    @if($invitation->jury_role)
                    <span class="badge-premium badge-premium-info">{{ $invitation->jury_role }}</span>
                    @endif
                </div>

                <h2 class="mt-3 text-lg font-semibold text-slate-900">{{ $invitation->event?->name }}</h2>

                <dl class="mt-3 grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Panitia</dt>
                        <dd class="font-medium text-slate-800">{{ $invitation->event?->organizer?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Form penilaian</dt>
                        <dd class="font-medium text-slate-800">{{ $invitation->juryFormTemplate?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Dikirim</dt>
                        <dd class="font-medium text-slate-800">{{ $invitation->created_at?->translatedFormat('d F Y, H:i') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Posisi juri</dt>
                        <dd class="font-medium text-slate-800">{{ $invitation->jury_role ?: 'Juri' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                <form method="POST" action="{{ route('jury.invitations.accept', $invitation) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary w-full sm:w-auto">Terima Undangan</button>
                </form>
                <form method="POST" action="{{ route('jury.invitations.decline', $invitation) }}"
                      onsubmit="return confirm('Tolak undangan menjadi juri di event ini?');">
                    @csrf
                    <button type="submit" class="btn btn-secondary w-full sm:w-auto">Tolak</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@if($responded->isNotEmpty())
<div class="premium-card mt-6">
    <div class="premium-card-header">
        <div>
            <h2 class="premium-card-title">Riwayat Undangan</h2>
            <p class="mt-1 text-xs text-slate-500">Undangan yang sudah Anda jawab.</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Posisi</th>
                    <th>Form</th>
                    <th>Jawaban</th>
                    <th>Dijawab</th>
                </tr>
            </thead>
            <tbody>
                @foreach($responded as $invitation)
                <tr>
                    <td><strong class="font-semibold text-slate-900">{{ $invitation->event?->name }}</strong></td>
                    <td class="text-slate-600">{{ $invitation->jury_role ?: 'Juri' }}</td>
                    <td class="text-slate-600">{{ $invitation->juryFormTemplate?->name ?? '-' }}</td>
                    <td>
                        @if($invitation->isAccepted())
                        <span class="badge-premium badge-premium-success">Diterima</span>
                        @else
                        <span class="badge-premium badge-premium-danger">Ditolak</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap text-slate-600">{{ $invitation->responded_at?->format('d M Y H:i') ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
