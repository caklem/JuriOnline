@extends('layouts.app')

@section('title', 'Dashboard Juri')

@section('content')
<div class="page-header-premium">
    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Ruang kerja juri</p>
    <h1>Dashboard Juri</h1>
    <p>Selamat datang, <strong class="text-slate-700">{{ auth()->user()->name }}</strong>. Halaman ini menunjukkan langkah berikutnya yang perlu Anda lakukan.</p>
</div>

@php
    $firstPendingEvent = $pendingScores->first()?->event;
@endphp

{{-- Langkah berikutnya: satu arahan utama supaya juri tidak bingung --}}
@if($nextStep === 'accept_invitation')
<div class="premium-card !mb-6 border-l-4 !border-l-amber-400">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <span class="badge-premium badge-premium-warning">Langkah 1 dari 3</span>
            <h2 class="mt-3 text-lg font-semibold text-slate-900">Terima undangan juri dulu</h2>
            <p class="mt-1 text-sm text-slate-600">
                Ada <strong>{{ $pendingInvitations->count() }} undangan</strong> menunggu persetujuan Anda.
                Selama undangan belum diterima, daftar peserta event tersebut belum bisa dibuka.
            </p>
        </div>
        <a href="{{ route('jury.invitations.index') }}" class="btn btn-primary w-full sm:w-auto">Buka Undangan</a>
    </div>
</div>
@elseif($nextStep === 'score_participants')
<div class="premium-card !mb-6 border-l-4 !border-l-indigo-500">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <span class="badge-premium badge-premium-info">Langkah 2 dari 3</span>
            <h2 class="mt-3 text-lg font-semibold text-slate-900">Ada {{ $pendingScoreCount }} peserta yang belum Anda nilai</h2>
            <p class="mt-1 text-sm text-slate-600">
                Klik <strong>Nilai Sekarang</strong>, isi kriteria penilaian, lalu simpan.
                Setelah disimpan, sistem otomatis membawa Anda ke peserta berikutnya.
            </p>
        </div>
        @if($firstPendingEvent)
        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
            <a href="{{ route('jury.form', $firstPendingEvent) }}" class="btn btn-outline-secondary w-full sm:w-auto">Lihat Form</a>
            <a href="{{ route('jury.participants', $firstPendingEvent) }}" class="btn btn-primary w-full sm:w-auto">Nilai Sekarang</a>
        </div>
        @endif
    </div>
</div>
@elseif($nextStep === 'waiting_open')
<div class="premium-card !mb-6 border-l-4 !border-l-slate-300">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <span class="badge-premium badge-premium-warning">Menunggu hari H</span>
            <h2 class="mt-3 text-lg font-semibold text-slate-900">Penilaian belum dibuka</h2>
            <p class="mt-1 text-sm text-slate-600">
                @if($nextScoringDate)
                Penilaian {{ $waitingOpenEvents->count() }} event Anda terbuka otomatis pada
                <strong>{{ $nextScoringDate->format('d M Y') }}</strong> (hari H).
                @else
                Penilaian event Anda sedang ditutup oleh panitia.
                @endif
                Begitu dibuka, tombol <strong>Nilai Peserta</strong> akan muncul di sini.
            </p>
        </div>
        <a href="{{ route('jury.events') }}" class="btn btn-outline-secondary w-full sm:w-auto">Lihat Event</a>
    </div>
</div>
@elseif($nextStep === 'all_done')
<div class="premium-card !mb-6 border-l-4 !border-l-emerald-500">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <span class="badge-premium badge-premium-success">Selesai</span>
            <h2 class="mt-3 text-lg font-semibold text-slate-900">Semua peserta sudah Anda nilai</h2>
            <p class="mt-1 text-sm text-slate-600">Terima kasih. Anda masih bisa memperbaiki nilai kapan saja lewat riwayat penilaian di bawah.</p>
        </div>
        <a href="{{ route('jury.events') }}" class="btn btn-outline-secondary w-full sm:w-auto">Lihat Event</a>
    </div>
</div>
@else
<div class="premium-card !mb-6 border-l-4 !border-l-slate-300">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h2 class="text-lg font-semibold text-slate-900">Belum ada undangan yang Anda terima</h2>
            <p class="mt-1 text-sm text-slate-600">
                Anda baru bisa menilai peserta setelah panitia mengirim undangan dan Anda menekan <strong>Terima Undangan</strong>.
                Hubungi panitia jika undangan belum masuk.
            </p>
        </div>
        <a href="{{ route('jury.invitations.index') }}" class="btn btn-primary w-full sm:w-auto">Cek Undangan</a>
    </div>
</div>
@endif

{{-- Tugas menilai yang menunggu, tampil otomatis setelah undangan diterima --}}
@if($hasAssignments && $nextStep !== 'waiting_open')
<div class="premium-card !mb-6">
    <div class="premium-card-header">
        <div>
            <h2 class="premium-card-title">Perlu Anda Nilai ({{ $pendingScoreCount }})</h2>
            <p class="mt-1 text-xs text-slate-500">Peserta terverifikasi yang belum Anda beri nilai. Klik tombol Nilai pada peserta.</p>
        </div>
        <a href="{{ route('jury.events') }}" class="btn-premium btn-premium-outline btn-sm">Pilih Event</a>
    </div>

    @if($pendingScores->isEmpty())
    <div class="empty-state-premium">
        <h3>Tidak ada yang menunggu</h3>
        <p>Semua peserta di event yang Anda tangani sudah dinilai.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>Peserta</th>
                    <th>Event</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingScores as $registration)
                <tr>
                    <td><strong class="font-semibold text-slate-900">{{ $registration->user?->name }}</strong></td>
                    <td class="text-slate-600">{{ $registration->event?->name }}</td>
                    <td><span class="badge-premium badge-premium-warning">Belum dinilai</span></td>
                    <td class="text-right">
                        <a href="{{ route('jury.score', $registration) }}" class="btn btn-primary btn-sm">Nilai</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($pendingScoreCount > $pendingScores->count())
    <p class="mt-3 text-xs text-slate-500">
        Menampilkan {{ $pendingScores->count() }} dari {{ $pendingScoreCount }} peserta. Buka daftar peserta event untuk melihat semuanya.
    </p>
    @endif
    @endif
</div>
@endif

{{-- Panduan singkat 3 langkah --}}
@if($nextStep !== 'all_done')
<div class="premium-card !mb-6">
    <div class="premium-card-header">
        <div>
            <h2 class="premium-card-title">Alur Menilai dalam 3 Langkah</h2>
            <p class="mt-1 text-xs text-slate-500">Ikuti urutan ini, tidak perlu menghafal menu.</p>
        </div>
    </div>
    <div class="grid gap-3 sm:grid-cols-3">
        @foreach([
            ['1', 'Terima undangan', 'Buka Undangan Juri, lalu tekan Terima Undangan.'],
            ['2', 'Pilih peserta', 'Klik Berikan Penilaian pada peserta yang ingin dinilai.'],
            ['3', 'Isi nilai & simpan', 'Isi semua kriteria bertanda *, lalu Simpan. Sistem lanjut ke peserta berikutnya.'],
        ] as [$number, $title, $description])
        <div class="rounded-xl bg-slate-50 px-4 py-4 ring-1 ring-slate-100">
            <span class="grid h-8 w-8 place-items-center rounded-full bg-slate-900 text-sm font-semibold text-white">{{ $number }}</span>
            <p class="mt-2 text-sm font-semibold text-slate-900">{{ $title }}</p>
            <p class="mt-1 text-xs leading-5 text-slate-600">{{ $description }}</p>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($pendingInvitations->isNotEmpty())
<div class="premium-card !mb-6">
    <div class="premium-card-header">
        <div>
            <h2 class="premium-card-title">Undangan Menunggu Persetujuan ({{ $pendingInvitations->count() }})</h2>
            <p class="mt-1 text-xs text-slate-500">Setujui undangan agar Anda bisa menilai peserta event tersebut.</p>
        </div>
        <a href="{{ route('jury.invitations.index') }}" class="btn-premium btn-premium-outline btn-sm">Lihat semua</a>
    </div>

    <div class="grid gap-3">
        @foreach($pendingInvitations as $invitation)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-semibold text-slate-900">{{ $invitation->event?->name }}</p>
                    <p class="mt-1 text-xs text-slate-600">
                        Panitia: {{ $invitation->event?->organizer?->name ?? '-' }}
                        &middot; Form: {{ $invitation->juryFormTemplate?->name ?? '-' }}
                        @if($invitation->jury_role) &middot; Posisi: {{ $invitation->jury_role }} @endif
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('jury.invitations.accept', $invitation) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Terima</button>
                    </form>
                    <form method="POST" action="{{ route('jury.invitations.decline', $invitation) }}"
                          onsubmit="return confirm('Tolak undangan menjadi juri di event ini?');">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">Tolak</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(!$hasAssignments)
{{-- Pesan "belum ada assignment" sudah ditampilkan pada kartu langkah berikutnya di atas. --}}
@endif

<div class="stats-grid-premium">
    <div class="stat-card-premium">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3>Total Penilaian</h3>
                <div class="number">{{ $totalScored }}</div>
            </div>
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-100">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5m0 14h16M8 16v-5m4 5V8m4 8v-3" />
                </svg>
            </span>
        </div>
    </div>

    <div class="stat-card-premium">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3>Event Siap Dinilai</h3>
                <div class="number">{{ $eventsToScore }}</div>
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
                <h3>Belum Dinilai</h3>
                <div class="number">{{ $pendingScoreCount }}</div>
            </div>
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-inset ring-amber-100">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M10.3 3.9 2.8 17a1.7 1.7 0 0 0 1.5 2.5h15.4A1.7 1.7 0 0 0 21.2 17L13.7 3.9a1.7 1.7 0 0 0-3 0Z" />
                </svg>
            </span>
        </div>
    </div>
</div>

<div class="premium-card">
    <div class="premium-card-header">
        <div>
            <h2 class="premium-card-title">Penilaian Terbaru</h2>
            <p class="mt-1 text-xs text-slate-500">Riwayat penilaian terakhir yang Anda berikan.</p>
        </div>
        <a href="{{ route('jury.events') }}" class="btn-premium btn-premium-outline btn-sm">Mulai penilaian</a>
    </div>

    @if($recentScores->isEmpty())
    <div class="empty-state-premium">
        <div class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6a2 2 0 0 1 2 2v13l-5-3-5 3V7a2 2 0 0 1 2-2Z" />
            </svg>
        </div>
        <h3>Belum Ada Penilaian</h3>
        <p>
            @if($pendingScoreCount > 0)
            Ada {{ $pendingScoreCount }} peserta menunggu nilai Anda — lihat kartu "Perlu Anda Nilai" di atas.
            @else
            Anda belum memberikan penilaian. Mulai dengan membuka undangan atau memilih event.
            @endif
        </p>
        <div class="mt-6">
            @if($firstPendingEvent ?? null)
            <a href="{{ route('jury.participants', $firstPendingEvent) }}" class="btn-premium btn-premium-primary">
                Nilai Sekarang
            </a>
            @else
            <a href="{{ route('jury.events') }}" class="btn-premium btn-premium-primary">
                Mulai Penilaian
            </a>
            @endif
        </div>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>Peserta</th>
                    <th>Event</th>
                    <th>Nilai</th>
                    <th>Tanggal</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentScores as $score)
                <tr>
                    <td><strong class="font-semibold text-slate-900">{{ $score->registration->user->name }}</strong></td>
                    <td class="text-slate-600">{{ $score->registration->event->name }}</td>
                    <td>
                        <span class="text-lg font-semibold text-emerald-600">
                            {{ number_format($score->score, 2) }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap text-slate-600">{{ $score->created_at->format('d M Y H:i') }}</td>
                    <td class="text-right">
                        <a href="{{ route('jury.edit-score', $score) }}" class="btn-premium btn-premium-outline btn-sm">Edit</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
