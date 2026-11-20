@extends('layouts.app')

@section('title', 'Peserta - ' . $event->name)

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
        <h1>{{ $event->name }}</h1>
        <p>Berikan penilaian untuk peserta yang telah terverifikasi.</p>
    </div>
    <a href="{{ route('jury.leaderboard', $event) }}" class="btn btn-success">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8m-4-4v4m-5-9H4a2 2 0 0 1-2-2V8h4m14 4h-3m3 0a2 2 0 0 1-2 2h-1M8 8h8m-8 0a4 4 0 0 0 8 0M6 8V5h12v3" />
        </svg>
        Lihat Leaderboard
    </a>
</div>

@php
    $progressPercent = $verifiedTotal > 0 ? (int) round(($myScoredTotal / $verifiedTotal) * 100) : 0;
@endphp

@if(!$scoringOpen)
<div class="mb-4 rounded-xl border-l-4 border-amber-400 bg-amber-50 px-4 py-3">
    <p class="text-sm font-semibold text-amber-900">Penilaian belum dibuka</p>
    <p class="mt-1 text-xs leading-5 text-amber-800">
        @if($event->scoringMode() === 'closed')
        Panitia menutup penilaian untuk event ini sementara.
        @else
        Penilaian terbuka otomatis pada <strong>{{ $event->scoringOpensAt()?->format('d M Y') ?? 'hari H' }}</strong>,
        atau lebih cepat bila panitia mengaktifkannya.
        @endif
        Tombol <strong>Nilai Peserta</strong> akan muncul begitu penilaian dibuka.
    </p>
</div>
@endif

<div class="premium-card !mb-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-900">Progres penilaian Anda</p>
            <p class="mt-1 text-xs leading-5 text-slate-600">
                Sudah dinilai <strong>{{ $myScoredTotal }}</strong> dari <strong>{{ $verifiedTotal }}</strong> peserta terverifikasi.
                @if($scoringOpen)
                Klik <strong>Berikan Penilaian</strong> pada peserta, isi semua kriteria, lalu simpan.
                @else
                Tombol penilaian akan muncul begitu penilaian dibuka.
                @endif
            </p>
        </div>
        <span class="badge-premium badge-premium-info">{{ $progressPercent }}%</span>
    </div>
    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100">
        <div class="h-full rounded-full bg-indigo-500 transition-all" style="width: {{ $progressPercent }}%"></div>
    </div>
</div>

@if(isset($assignedFormTemplate) && $assignedFormTemplate)
<div class="mb-4 rounded-xl border-l-4 border-emerald-500 bg-emerald-50 px-4 py-3">
    <p class="flex items-center gap-2 text-sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4 text-emerald-600" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6m-7 4h8m-8 4h5m-5 4h8M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
        </svg>
        <strong class="text-emerald-700">Form Penilaian Anda:</strong>
        <span class="font-medium text-slate-900">{{ $assignedFormTemplate->name ?? 'Form Juri' }}</span>
        <a href="{{ route('jury.form', $event) }}" class="ml-auto text-xs font-semibold text-emerald-700 underline hover:text-emerald-800">Lihat isi form</a>
    </p>
</div>
@endif

@if($registrations->count() > 0)
<div class="grid gap-4">
    @foreach($registrations as $registration)
    <div class="premium-card !mb-0">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div class="min-w-0 flex-1">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">
                        {{ strtoupper(substr($registration->user->name, 0, 1)) }}
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">{{ $registration->user->name }}</h3>
                        <p class="text-sm text-slate-500">{{ $registration->user->email }}</p>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    @php
                        $allScores = $registration->juryScores;
                        $myScore = $allScores->firstWhere('jury_id', auth()->id());
                        $averageScore = $allScores->isNotEmpty() ? (float) $allScores->avg('score') : null;
                    @endphp
                    <span class="badge badge-success">Terverifikasi</span>
                    <span class="badge badge-info">Rata-rata: {{ $averageScore !== null ? number_format($averageScore, 2) : 'Belum ada' }}</span>
                    <span class="badge badge-warning">{{ $allScores->count() }} juri menilai</span>
                </div>

                @if($registration->notes)
                <div class="mt-3 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-700 ring-1 ring-slate-100">
                    <strong class="text-slate-900">Catatan Peserta:</strong>
                    <span class="mt-1 block whitespace-pre-line">{{ $registration->notes }}</span>
                </div>
                @endif
            </div>

            <div class="w-full shrink-0 sm:w-48">
                @if($myScore)
                <div class="rounded-xl bg-emerald-50 px-4 py-3 text-center ring-1 ring-emerald-100">
                    <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Nilai Anda</p>
                    <p class="mt-1 text-2xl font-semibold text-emerald-700">{{ number_format($myScore->score, 2) }}</p>
                </div>
                @if($scoringOpen)
                <a href="{{ route('jury.edit-score', $myScore) }}" class="btn btn-warning mt-3 w-full">
                    Edit Penilaian
                </a>
                @else
                <span class="btn btn-warning mt-3 w-full cursor-not-allowed opacity-60" aria-disabled="true">Penilaian Ditutup</span>
                @endif
                @else
                @if($scoringOpen)
                <a href="{{ route('jury.score', $registration) }}" class="btn btn-primary w-full">
                    Berikan Penilaian
                </a>
                @else
                <span class="btn btn-primary w-full cursor-not-allowed opacity-60" aria-disabled="true">Belum Bisa Menilai</span>
                @endif
                @endif
            </div>
        </div>

        @if($registration->juryScores->count() > 0)
        <div class="mt-4 border-t border-slate-100 pt-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Penilaian dari Juri Lain:</p>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($registration->juryScores as $score)
                @if($score->jury_id !== auth()->id())
                <div class="rounded-lg bg-slate-50 px-3 py-1.5 text-sm ring-1 ring-slate-100">
                    <strong class="text-slate-900">{{ $score->jury->name }}:</strong>
                    <span class="text-slate-700">{{ $score->score }}</span>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endforeach
</div>

<div class="mt-5">
    {{ $registrations->links() }}
</div>
@else
<div class="empty-state-premium">
    <h3>Belum ada peserta</h3>
    <p>Belum ada peserta terverifikasi untuk event ini.</p>
</div>
@endif
@endsection
