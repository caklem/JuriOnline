@extends('layouts.app')

@section('title', 'Leaderboard - ' . $event->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('jury.events') }}" class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-700">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12l7.5-7.5M3 12h18" />
        </svg>
        Kembali ke Daftar Event
    </a>
</div>

<div class="page-header-premium flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="flex items-center gap-2">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-7 w-7 text-amber-500" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8m-4-4v4m-5-9H4a2 2 0 0 1-2-2V8h4m14 4h-3m3 0a2 2 0 0 1-2 2h-1M8 8h8m-8 0a4 4 0 0 0 8 0M6 8V5h12v3" />
            </svg>
            Leaderboard
        </h1>
        <p>{{ $event->name }}</p>
    </div>
    <a href="{{ route('jury.participants', $event) }}" class="btn btn-primary">
        Kembali ke Daftar Peserta
    </a>
</div>

@if($registrations->count() > 0)
<div class="grid gap-4">
    @foreach($registrations as $index => $registration)
    @php
    $rankBorder = $index === 0 ? '!border-amber-300' : ($index === 1 ? '!border-slate-300' : ($index === 2 ? '!border-orange-300' : '!border-slate-200'));
    $rankBadge = $index === 0 ? 'bg-amber-100 text-amber-800' : ($index === 1 ? 'bg-slate-200 text-slate-700' : ($index === 2 ? 'bg-orange-100 text-orange-800' : 'bg-slate-100 text-slate-500'));
    @endphp
    <div class="premium-card !mb-0 border-2 {{ $rankBorder }}">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex min-w-0 flex-1 items-start gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-base font-semibold {{ $rankBadge }}">
                    {{ $index + 1 }}
                </span>
                <div class="min-w-0">
                    <h3 class="text-lg font-semibold text-slate-900">{{ $registration->user->name }}</h3>
                    <p class="text-sm text-slate-500">{{ $registration->user->email }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <div class="min-w-[120px] rounded-xl bg-slate-900 px-4 py-3 text-center text-white">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-300">Nilai Rata-rata</p>
                    <p class="mt-1 text-2xl font-semibold">{{ number_format($registration->average_score ?? 0, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="mt-4 border-t border-slate-100 pt-4">
            <strong class="text-sm text-slate-900">Penilaian dari {{ $registration->juryScores->count() }} Juri:</strong>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($registration->juryScores as $score)
                <div class="rounded-lg border-l-2 border-indigo-500 bg-slate-50 px-3 py-1.5 text-sm">
                    <strong class="text-slate-900">{{ $score->jury->name }}:</strong>
                    <span class="font-semibold text-indigo-700">{{ number_format($score->score, 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        @php
        $publicFeedbacks = $registration->juryScores->filter(fn($s) => !empty($s->public_feedback));
        @endphp

        @if($publicFeedbacks->count() > 0)
        <div class="mt-4 rounded-xl border-l-4 border-emerald-500 bg-emerald-50 px-4 py-3">
            <strong class="flex items-center gap-2 text-sm text-emerald-800">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8m-8 4h5m-9 6V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H8l-4 4Z" />
                </svg>
                Feedback Publik:
            </strong>
            <div class="mt-2 space-y-3">
                @foreach($publicFeedbacks as $score)
                <div>
                    <strong class="text-sm text-slate-900">{{ $score->jury->name }}:</strong>
                    <p class="mt-0.5 text-sm text-slate-600">{{ $score->public_feedback }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endforeach
</div>
@else
<div class="empty-state-premium">
    <h3>Belum ada peserta</h3>
    <p>Belum ada peserta terverifikasi untuk event ini.</p>
</div>
@endif
@endsection
