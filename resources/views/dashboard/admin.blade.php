@extends('layouts.app')

@section('title', 'Dashboard ' . auth()->user()->roleLabel())

@section('content')
<div class="page-header-premium">
    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Ringkasan platform</p>
    <h1>Dashboard {{ auth()->user()->roleLabel() }}</h1>
    <p>Selamat datang, <strong class="text-slate-700">{{ auth()->user()->name }}</strong>. {{ auth()->user()->isEventAdmin() ? 'Kelola event yang Anda selenggarakan.' : 'Kelola seluruh event dan sistem dari sini.' }}</p>
</div>

<div class="stats-grid-premium">
    <div class="stat-card-premium">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3>Total Event</h3>
                <div class="number">{{ $totalEvents }}</div>
            </div>
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-100">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 3v3m8-3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                </svg>
            </span>
        </div>
    </div>

    <div class="stat-card-premium">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3>Event Aktif</h3>
                <div class="number">{{ $activeEvents }}</div>
            </div>
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-100">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4m5.6 2A9.6 9.6 0 0 1 12 21a9.6 9.6 0 0 1-8.6-9A9.6 9.6 0 0 1 12 3a9.6 9.6 0 0 1 8.6 9Z" />
                </svg>
            </span>
        </div>
    </div>

    <div class="stat-card-premium">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3>Pembayaran Pending</h3>
                <div class="number">{{ $pendingPayments }}</div>
            </div>
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-inset ring-amber-100">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2.5 2.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </span>
        </div>
    </div>

    <div class="stat-card-premium">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3>Total Pendaftaran</h3>
                <div class="number">{{ $totalRegistrations }}</div>
            </div>
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-sky-50 text-sky-600 ring-1 ring-inset ring-sky-100">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1m17-1v-1a3 3 0 0 0-2.5-2.96M14 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm4 0a2.5 2.5 0 1 1-2.5-2.5" />
                </svg>
            </span>
        </div>
    </div>
</div>

<div class="premium-card">
    <div class="premium-card-header">
        <div>
            <h2 class="premium-card-title">Event Terbaru</h2>
            <p class="mt-1 text-xs text-slate-500">Pantau event terbaru beserta status dan jumlah pesertanya.</p>
        </div>
        <a href="{{ route('events.index') }}" class="btn-premium btn-premium-outline btn-sm">{{ auth()->user()->isEventAdmin() ? 'Event Saya' : 'Semua event' }}</a>
    </div>

    @if($recentEvents->isEmpty())
    <div class="empty-state-premium">
        <div class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 3v3m8-3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
            </svg>
        </div>
        <h3>Belum Ada Event</h3>
        <p>Belum ada event yang dibuat. Mulai dengan membuat event pertama Anda.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Penyelenggara</th>
                    <th>Status</th>
                    <th>Peserta</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentEvents as $event)
                <tr>
                    <td><strong class="font-semibold text-slate-900">{{ $event->name }}</strong></td>
                    <td class="text-slate-600">{{ $event->organizer->name }}</td>
                    <td>
                        @if($event->status === 'active')
                        <span class="badge-premium badge-premium-success">Aktif</span>
                        @elseif($event->status === 'draft')
                        <span class="badge-premium badge-premium-warning">Draft</span>
                        @else
                        <span class="badge-premium badge-premium-info">{{ $event->status }}</span>
                        @endif
                    </td>
                    <td class="text-slate-600">{{ $event->registered_count }} / {{ $event->quota }}</td>
                    <td>
                        <a href="{{ route('events.show', $event) }}" class="btn-premium btn-premium-outline btn-sm">
                            Detail
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <a href="{{ route('events.index') }}" class="btn-premium btn-premium-primary">
        {{ auth()->user()->isEventAdmin() ? 'Event Saya' : 'Lihat Semua Event' }}
    </a>
    <a href="{{ route('payments.pending') }}" class="btn-premium btn-premium-warning">
        Verifikasi Pembayaran
    </a>
    <a href="{{ route('jury.events') }}" class="btn-premium btn-premium-success">
        Kelola Penilaian
    </a>
    @if(auth()->user()->canManageAllEvents())
    <a href="{{ route('jury.all-scores') }}" class="btn-premium btn-premium-info">
        Lihat Semua Penilaian
    </a>
    @endif
</div>
@endsection
