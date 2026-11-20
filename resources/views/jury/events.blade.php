@extends('layouts.app')

@section('title', 'Event untuk Penilaian')

@section('content')
<div class="page-header-premium flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="flex items-center gap-2">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-7 w-7 text-indigo-600" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 21h8M5 7h14M5 7l-3 6h6L5 7Zm14 0-3 6h6l-3-6Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M2 13a3 3 0 0 0 6 0M16 13a3 3 0 0 0 6 0" />
            </svg>
            Event untuk Penilaian
        </h1>
        <p>Pilih event untuk memberikan penilaian kepada peserta.</p>
    </div>
</div>

@if($events->count() > 0)
<div class="grid gap-6">
    @foreach($events as $event)
    <div class="premium-card !mb-0">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div class="min-w-0 flex-1">
                <h2 class="text-xl font-semibold text-slate-900">{{ $event->name }}</h2>

                @php
                    $pendingCount = (int) ($pendingByEvent[$event->id] ?? 0);
                @endphp

                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="badge-premium badge-premium-info">{{ $event->category->name }}</span>
                    <span class="badge-premium badge-premium-success">{{ $event->status === 'active' ? 'Aktif' : ucfirst($event->status) }}</span>
                    @if(!$event->isScoringOpen())
                    <span class="badge-premium badge-premium-warning">
                        {{ $event->scoringMode() === 'closed' ? 'Penilaian ditutup panitia' : 'Penilaian dibuka pada hari H' }}
                    </span>
                    @elseif($pendingCount > 0)
                    <span class="badge-premium badge-premium-warning">{{ $pendingCount }} peserta menunggu nilai</span>
                    @else
                    <span class="badge-premium badge-premium-success">Semua peserta sudah dinilai</span>
                    @endif
                </div>

                <p class="mt-3 text-sm leading-relaxed text-slate-600">
                    {{ Str::limit($event->description, 200) }}
                </p>

                @php
                $assignedForm = auth()->user()->getAssignedFormTemplate($event);
                @endphp
                @if($assignedForm)
                <div class="mt-4 rounded-xl border-l-4 border-emerald-500 bg-emerald-50 px-4 py-3">
                    <p class="flex items-center gap-2 text-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4 text-emerald-600" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6m-7 4h8m-8 4h5m-5 4h8M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                        </svg>
                        <strong class="text-emerald-700">Form Penilaian Anda:</strong>
                        <span class="font-medium text-slate-900">{{ $assignedForm->name ?? 'Form Juri' }}</span>
                        <a href="{{ route('jury.form', $event) }}" class="ml-auto text-xs font-semibold text-emerald-700 underline hover:text-emerald-800">Lihat isi form</a>
                    </p>
                </div>
                @endif

                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-xl bg-slate-50 px-4 py-3 ring-1 ring-slate-100">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Penyelenggara</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $event->organizer->name }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-4 py-3 ring-1 ring-slate-100">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Hari Pelaksanaan</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            @if($event->event_date)
                            {{ $event->event_date->format('d M Y') }}
                            @elseif($event->event_start && $event->event_end && $event->event_start->equalTo($event->event_end))
                            {{ $event->event_start->format('d M Y') }}
                            @elseif($event->event_start)
                            {{ $event->event_start->format('d M Y') }}@if($event->event_end) &ndash; {{ $event->event_end->format('d M Y') }}@endif
                            @else
                            Belum ditentukan
                            @endif
                        </p>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-4 py-3 ring-1 ring-slate-100">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Peserta</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $event->registrations->count() }} peserta</p>
                        @php
                        $verifiedCount = $event->registrations->where('status', 'verified')->count();
                        @endphp
                        @if($verifiedCount > 0)
                        <p class="mt-1 text-xs font-medium text-emerald-700">{{ $verifiedCount }} terverifikasi</p>
                        @else
                        <p class="mt-1 text-xs text-slate-500">Belum ada yang terverifikasi</p>
                        @endif
                    </div>
                </div>
            </div>

            @if($event->banner)
            <div class="w-full shrink-0 sm:w-auto">
                <img src="{{ Storage::url($event->banner) }}"
                    alt="{{ $event->name }}"
                    class="h-40 w-full rounded-xl object-cover shadow-sm ring-1 ring-slate-200 sm:w-60">
            </div>
            @endif
        </div>

        <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
            @if($event->isScoringOpen())
            <a href="{{ route('jury.participants', $event) }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1m13-9a3 3 0 1 0 0-6m-3 6a3 3 0 1 0 0-6m5 14h4v-1a4 4 0 0 0-3-3.87" />
                </svg>
                {{ $pendingCount > 0 ? "Nilai {$pendingCount} Peserta" : 'Lihat Peserta' }}
            </a>
            @else
            <span class="btn btn-primary cursor-not-allowed opacity-60" aria-disabled="true" title="Penilaian belum dibuka">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M10.3 3.9 2.8 17a1.7 1.7 0 0 0 1.5 2.5h15.4A1.7 1.7 0 0 0 21.2 17L13.7 3.9a1.7 1.7 0 0 0-3 0Z" />
                </svg>
                Belum Bisa Menilai
            </span>
            @endif
            <a href="{{ route('jury.leaderboard', $event) }}" class="btn btn-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8m-4-4v4m-5-9H4a2 2 0 0 1-2-2V8h4m14 4h-3m3 0a2 2 0 0 1-2 2h-1M8 8h8m-8 0a4 4 0 0 0 8 0M6 8V5h12v3" />
                </svg>
                Lihat Leaderboard
            </a>
            <a href="{{ route('events.detail', $event) }}" class="btn btn-outline-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6m-7 4h8m-8 4h5m-5 4h8M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                </svg>
                Detail Event
            </a>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-6 flex justify-center">
    {{ $events->links() }}
</div>
@else
<div class="empty-state-premium">
    <h3>Tidak Ada Event Aktif</h3>
    <p>Event yang statusnya aktif akan muncul di sini untuk dinilai.</p>
</div>
@endif
@endsection
