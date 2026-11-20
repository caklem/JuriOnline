@extends('layouts.app')

@section('title', 'Form Penilaian - ' . $event->name)

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
        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Form penilaian juri</p>
        <h1>{{ $formTemplate->name ?: 'Form Penilaian' }}</h1>
        <p>{{ $event->name }}</p>
    </div>
    <a href="{{ route('jury.participants', $event) }}" class="btn btn-primary">
        Mulai Menilai Peserta
    </a>
</div>

@php
    $criteriaFields = $formTemplate->criteria_fields ?? [];
    $totalMaxScore = 0;
    foreach ($criteriaFields as $field) {
        if (($field['type'] ?? '') === 'score') {
            $totalMaxScore += (int) ($field['max'] ?? 0);
        }
    }
@endphp

<div class="premium-card">
    <div class="premium-card-header">
        <div>
            <h2 class="premium-card-title">Kriteria yang Akan Anda Nilai</h2>
            <p class="mt-1 text-xs text-slate-500">
                Form ini yang muncul saat Anda menekan <strong>Berikan Penilaian</strong> pada peserta.
            </p>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-xl bg-slate-50 px-4 py-3 ring-1 ring-slate-100">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Jumlah Kriteria</p>
            <p class="mt-1 text-sm font-semibold text-slate-900">{{ count($criteriaFields) }} kriteria</p>
        </div>
        <div class="rounded-xl bg-slate-50 px-4 py-3 ring-1 ring-slate-100">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Maksimal Poin</p>
            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $totalMaxScore > 0 ? $totalMaxScore . ' poin' : '-' }}</p>
        </div>
        <div class="rounded-xl bg-slate-50 px-4 py-3 ring-1 ring-slate-100">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Nilai Akhir</p>
            <p class="mt-1 text-sm font-semibold text-slate-900">Dijumlahkan otomatis</p>
        </div>
    </div>

    @if(count($criteriaFields) === 0)
    <div class="empty-state-premium mt-4">
        <h3>Form masih kosong</h3>
        <p>Panitia belum mengisi kriteria penilaian untuk event ini. Hubungi panitia.</p>
    </div>
    @else
    <div class="mt-4 grid gap-3">
        @foreach($criteriaFields as $index => $field)
        @php
            $type = $field['type'] ?? 'text';
            $typeLabel = $type === 'score' ? 'Nilai' : ($type === 'textarea' ? 'Catatan' : 'Isian');
        @endphp
        <div class="rounded-xl border border-slate-200 px-4 py-3">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-900">
                        {{ $index + 1 }}. {{ $field['label'] ?? 'Kriteria ' . ($index + 1) }}
                        @if(!empty($field['required']))
                        <span class="text-rose-600">*</span>
                        @endif
                    </p>
                    @if(!empty($field['description']))
                    <p class="mt-1 text-xs leading-5 text-slate-600">{{ $field['description'] }}</p>
                    @endif
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <span class="badge-premium badge-premium-info">{{ $typeLabel }}</span>
                    @if($type === 'score')
                    <span class="badge-premium badge-premium-warning">Maks {{ $field['max'] ?? 100 }}</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <p class="mt-4 text-xs text-slate-500">
        Kolom bertanda <span class="text-rose-600">*</span> wajib diisi. Nilai akhir adalah jumlah seluruh kriteria dan dihitung otomatis oleh sistem.
    </p>
    @endif
</div>

<div class="premium-card">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-900">Sudah paham kriterianya?</p>
            <p class="mt-1 text-xs text-slate-600">Pilih peserta terverifikasi, isi kriteria di atas, lalu simpan. Sistem lanjut otomatis ke peserta berikutnya.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('jury.participants', $event) }}" class="btn btn-primary">Nilai Peserta</a>
            <a href="{{ route('jury.leaderboard', $event) }}" class="btn btn-outline-secondary">Leaderboard</a>
        </div>
    </div>
</div>
@endsection
