@extends('layouts.app')

@section('title', 'JuriOnline — Platform Penilaian Digital untuk Kompetisi')

@php
use Illuminate\Support\Facades\Storage;

$events = \App\Models\Event::where('status', 'active')
    ->with(['organizer', 'category'])
    ->latest()
    ->take(3)
    ->get();

$features = [
    [
        'title' => 'Form builder fleksibel',
        'description' => 'Susun formulir pendaftaran dan penilaian sesuai kebutuhan event tanpa menulis kode.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6m-7 4h8m-8 4h5m-5 4h8M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/></svg>',
    ],
    [
        'title' => 'Penilaian multi-juri',
        'description' => 'Setiap juri dapat memiliki formulir dan komponen penilaiannya sendiri dalam satu event.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 21h8M5 7h14M5 7l-3 6h6L5 7Zm14 0-3 6h6l-3-6Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 13a3 3 0 0 0 6 0M16 13a3 3 0 0 0 6 0"/></svg>',
    ],
    [
        'title' => 'Rekapitulasi otomatis',
        'description' => 'Nilai akhir dihitung konsisten dari seluruh juri, tanpa rekap manual dan tanpa selisih.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5m0 14h16M8 16v-5m4 5V8m4 8v-3"/></svg>',
    ],
    [
        'title' => 'Manajemen peserta',
        'description' => 'Pantau pendaftaran, verifikasi, dan status peserta dalam satu dasbor yang terpusat.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1m13-9a3 3 0 1 0 0-6m-3 6a3 3 0 1 0 0-6m5 14h4v-1a4 4 0 0 0-3-3.87"/></svg>',
    ],
    [
        'title' => 'Verifikasi pembayaran',
        'description' => 'Unggah bukti bayar dan kelola verifikasi pembayaran tanpa berpindah aplikasi.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5v-9Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h3"/></svg>',
    ],
    [
        'title' => 'Leaderboard real-time',
        'description' => 'Peringkat peserta diperbarui saat penilaian masuk dan siap dilihat kapan saja.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8m-4-4v4m-5-9H4a2 2 0 0 1-2-2V8h4m14 4h-3m3 0a2 2 0 0 1-2 2h-1M8 8h8m-8 0a4 4 0 0 0 8 0M6 8V5h12v3"/></svg>',
    ],
];

$values = [
    [
        'title' => 'Profesional',
        'description' => 'Alur kerja yang jelas untuk tim penyelenggara, juri, dan peserta.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4m5.6 2A9.6 9.6 0 0 1 12 21a9.6 9.6 0 0 1-8.6-9A9.6 9.6 0 0 1 12 3a9.6 9.6 0 0 1 8.6 9Z"/></svg>',
    ],
    [
        'title' => 'Transparan',
        'description' => 'Riwayat penilaian dan status peserta dapat ditelusuri dengan mudah.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>',
    ],
    [
        'title' => 'Terpercaya',
        'description' => 'Akses berbasis peran menjaga data event dan penilaian tetap aman.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4.5 6v5.2c0 4.4 3.1 8.4 7.5 9.8 4.4-1.4 7.5-5.4 7.5-9.8V6L12 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>',
    ],
];

$previewRows = [
    ['initials' => 'AI', 'name' => 'Atlas Idaman', 'score' => '98.5', 'avatar' => 'bg-indigo-50 text-indigo-700', 'bar' => 'bg-indigo-500', 'width' => 'w-[98%]'],
    ['initials' => 'II', 'name' => 'IIQSI', 'score' => '97.0', 'avatar' => 'bg-sky-50 text-sky-700', 'bar' => 'bg-sky-500', 'width' => 'w-[92%]'],
    ['initials' => 'JM', 'name' => 'Jadid Muazam', 'score' => '96.5', 'avatar' => 'bg-emerald-50 text-emerald-700', 'bar' => 'bg-emerald-500', 'width' => 'w-[86%]'],
];

$eventStatus = function ($event) {
    $now = now();

    if ($now->gt($event->event_end)) {
        return ['label' => 'Selesai', 'class' => 'bg-slate-100 text-slate-600 ring-slate-500/20'];
    }

    if ($event->isRegistrationOpen()) {
        return ['label' => 'Pendaftaran dibuka', 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'];
    }

    if ($now->lt($event->registration_start)) {
        return ['label' => 'Segera dibuka', 'class' => 'bg-sky-50 text-sky-700 ring-sky-600/20'];
    }

    if ($event->getAvailableSlots() <= 0) {
        return ['label' => 'Kuota penuh', 'class' => 'bg-amber-50 text-amber-700 ring-amber-600/20'];
    }

    return ['label' => 'Pendaftaran ditutup', 'class' => 'bg-amber-50 text-amber-700 ring-amber-600/20'];
};
@endphp

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-slate-950 text-white">
        <div class="pointer-events-none absolute inset-0" style="background-image: radial-gradient(ellipse at top right, rgba(99, 102, 241, 0.28), transparent 55%), radial-gradient(ellipse at bottom left, rgba(14, 165, 233, 0.16), transparent 50%);"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.07]" style="background-image: linear-gradient(to right, white 1px, transparent 1px), linear-gradient(to bottom, white 1px, transparent 1px); background-size: 64px 64px;"></div>

        <div class="relative mx-auto max-w-7xl px-6 pb-20 pt-16 sm:pt-20 lg:px-8 lg:pb-28 lg:pt-24">
            <div class="grid items-center gap-16 lg:grid-cols-2">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-medium text-slate-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Platform penilaian digital
                    </span>

                    <h1 class="mt-6 max-w-2xl text-4xl font-semibold tracking-tight text-white sm:text-5xl lg:text-6xl">
                        Penilaian kompetisi yang <span class="text-indigo-300">adil</span>, transparan, dan efisien.
                    </h1>

                    <p class="mt-6 max-w-xl text-base leading-7 text-slate-300 sm:text-lg">
                        JuriOnline menyatukan pendaftaran peserta, penilaian juri, verifikasi pembayaran, dan rekapitulasi hasil dalam satu alur kerja yang rapi.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-slate-950 shadow-sm transition hover:bg-slate-100">
                            Mulai sekarang
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" />
                            </svg>
                        </a>
                        <a href="#events" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/5 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                            Jelajahi event
                        </a>
                    </div>

                    <div class="mt-10 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-slate-400">
                        <span class="inline-flex items-center gap-2">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 text-emerald-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg>
                            Form builder
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 text-emerald-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg>
                            Multi-juri
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 text-emerald-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg>
                            Rekap otomatis
                        </span>
                    </div>
                </div>

                {{-- Decorative dashboard preview --}}
                <div class="relative mx-auto w-full max-w-lg lg:max-w-none" aria-hidden="true">
                    <div class="absolute -inset-8 rounded-full bg-indigo-500/20 blur-3xl"></div>
                    <div class="relative rounded-3xl border border-white/10 bg-white/5 p-2 shadow-2xl ring-1 ring-white/10 backdrop-blur">
                        <div class="rounded-2xl bg-white p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Pratinjau dasbor</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-950">Festival Banjari</p>
                                </div>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Live
                                </span>
                            </div>

                            <div class="mt-5 space-y-3">
                                @foreach($previewRows as $row)
                                    <div class="flex items-center gap-3">
                                        <span class="w-4 text-xs font-semibold text-slate-400">{{ $loop->iteration }}</span>
                                        <span class="grid h-8 w-8 place-items-center rounded-full text-[11px] font-semibold {{ $row['avatar'] }}">{{ $row['initials'] }}</span>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-3">
                                                <p class="truncate text-xs font-semibold text-slate-700">{{ $row['name'] }}</p>
                                                <p class="text-xs font-semibold text-slate-950">{{ $row['score'] }}</p>
                                            </div>
                                            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-full rounded-full {{ $row['bar'] }} {{ $row['width'] }}"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-5 grid grid-cols-3 gap-2 rounded-xl bg-slate-50 p-3 text-center ring-1 ring-inset ring-slate-100">
                                <div>
                                    <p class="text-sm font-semibold text-slate-950">24</p>
                                    <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-slate-400">Grup</p>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-950">5</p>
                                    <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-slate-400">Juri</p>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-950">3</p>
                                    <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-slate-400">Kategori</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -bottom-20 -left-6 hidden w-56 rounded-2xl border border-slate-200 bg-white p-4 shadow-xl sm:block">
                        <div class="flex items-start gap-3">
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-100">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 10V8a6 6 0 1 1 12 0v2m-13 0h14v10H5V10Z" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs font-semibold text-slate-950">Penilaian terverifikasi</p>
                                <p class="mt-1 text-[11px] leading-5 text-slate-500">Setiap nilai tercatat dan dapat ditelusuri.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Capability strip --}}
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-x-6 gap-y-8 py-8 sm:grid-cols-4">
                @foreach([
                    ['label' => 'Pendaftaran', 'value' => 'Form dinamis'],
                    ['label' => 'Penilaian', 'value' => 'Multi-juri'],
                    ['label' => 'Rekap', 'value' => 'Otomatis'],
                    ['label' => 'Akses', 'value' => 'Berbasis peran'],
                ] as $item)
                    <div class="border-l border-slate-200 pl-5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">{{ $item['label'] }}</p>
                        <p class="mt-2 text-sm font-semibold text-slate-950">{{ $item['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="fitur" class="scroll-mt-24 bg-slate-50 py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-600">Fitur</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Semua yang dibutuhkan untuk kompetisi yang rapi.</h2>
                <p class="mt-4 text-base leading-7 text-slate-600">Dari formulir pendaftaran sampai pengumuman hasil, setiap langkah punya tempatnya.</p>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($features as $feature)
                    <div class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
                        <div class="grid h-11 w-11 place-items-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-100">
                            {!! $feature['icon'] !!}
                        </div>
                        <h3 class="mt-5 text-base font-semibold text-slate-950">{{ $feature['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $feature['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section id="cara-kerja" class="scroll-mt-24 bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-2 lg:items-start">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-600">Cara kerja</p>
                    <h2 class="mt-3 max-w-xl text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Dari pendaftaran hingga pengumuman, dalam satu tempat.</h2>
                    <p class="mt-4 max-w-xl text-base leading-7 text-slate-600">Tidak perlu menggabungkan spreadsheet, pesan grup, dan rekap manual. JuriOnline menjaga alur kerja tetap terlihat oleh semua pihak yang berwenang.</p>
                    <a href="{{ route('register') }}" class="mt-8 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 transition hover:text-indigo-500">
                        Mulai sebagai penyelenggara
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" /></svg>
                    </a>
                </div>

                <ol class="space-y-8">
                    <li class="flex gap-5">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-slate-950 text-xs font-semibold text-white ring-8 ring-white">01</span>
                        <div>
                            <h3 class="text-base font-semibold text-slate-950">Siapkan event</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Tentukan kategori, jadwal, kuota, dan formulir pendaftaran yang sesuai dengan kebutuhan kompetisi.</p>
                        </div>
                    </li>
                    <li class="flex gap-5">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-slate-950 text-xs font-semibold text-white ring-8 ring-white">02</span>
                        <div>
                            <h3 class="text-base font-semibold text-slate-950">Kelola peserta dan juri</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Verifikasi pembayaran, tugaskan juri ke formulir penilaian, dan pantau progres dari dasbor.</p>
                        </div>
                    </li>
                    <li class="flex gap-5">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-slate-950 text-xs font-semibold text-white ring-8 ring-white">03</span>
                        <div>
                            <h3 class="text-base font-semibold text-slate-950">Umumkan hasil</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Rekap nilai otomatis, tinjau leaderboard, dan bagikan hasil kompetisi dengan lebih tenang.</p>
                        </div>
                    </li>
                </ol>
            </div>
        </div>
    </section>

    {{-- Events --}}
    <section id="events" class="scroll-mt-24 bg-slate-50 py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-600">Event</p>
                    <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Event terbaru</h2>
                    <p class="mt-4 max-w-xl text-base leading-7 text-slate-600">Lihat event yang sedang berjalan atau sudah dipublikasikan oleh penyelenggara.</p>
                </div>
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 transition hover:text-indigo-500">
                    Ingin menyelenggarakan event?
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" /></svg>
                </a>
            </div>

            @if($events->isEmpty())
                <div class="mt-12 rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                    <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-slate-500">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 3v3m10-3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-slate-950">Belum ada event yang dipublikasikan</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">Event baru akan tampil di sini setelah penyelenggara mempublikasikannya. Sementara itu, Anda tetap bisa membuat akun dan menyiapkan event pertama.</p>
                    <a href="{{ route('register') }}" class="mt-6 inline-flex items-center justify-center rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Daftar sekarang</a>
                </div>
            @elseif($events->count() === 1)
                @php
                    $event = $events->first();
                    $status = $eventStatus($event);
                @endphp

                <div class="mt-12 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm lg:grid lg:grid-cols-2">
                    <div class="relative min-h-[280px] overflow-hidden bg-slate-900 lg:min-h-[420px]">
                        @if($event->banner)
                            <img src="{{ Storage::url($event->banner) }}" alt="{{ $event->name }}" class="absolute inset-0 h-full w-full object-cover object-top">
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900"></div>
                            <div class="absolute inset-0 grid place-items-center text-white/70">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" class="h-16 w-16" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 21h8M5 7h14M5 7l-3 6h6L5 7Zm14 0-3 6h6l-3-6Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2 13a3 3 0 0 0 6 0M16 13a3 3 0 0 0 6 0" />
                                </svg>
                            </div>
                        @endif

                        <div class="absolute inset-x-6 top-6 flex flex-wrap items-center gap-2">
                            @if($event->category)
                                <span class="rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm backdrop-blur">{{ $event->category->name }}</span>
                            @endif
                            <span class="rounded-full px-3 py-1 text-xs font-semibold shadow-sm ring-1 ring-inset {{ $status['class'] }}">{{ $status['label'] }}</span>
                        </div>
                        <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-slate-950/50 to-transparent"></div>
                    </div>

                    <div class="flex flex-col justify-center p-7 sm:p-10">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-600">Event unggulan</p>
                        <h3 class="mt-3 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">{{ $event->name }}</h3>
                        <p class="mt-4 max-w-xl text-base leading-7 text-slate-600">{{ \Illuminate\Support\Str::limit($event->description, 220) }}</p>

                        <dl class="mt-8 grid grid-cols-2 gap-5 border-t border-slate-100 pt-6 text-sm">
                            <div>
                                <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Penyelenggara</dt>
                                <dd class="mt-1 font-medium text-slate-700">{{ $event->organizer->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Harga</dt>
                                <dd class="mt-1 font-medium text-slate-700">{{ $event->price > 0 ? 'Rp ' . number_format($event->price, 0, ',', '.') : 'Gratis' }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Jadwal</dt>
                                <dd class="mt-1 font-medium text-slate-700">{{ $event->event_start->format('d M Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Kuota</dt>
                                <dd class="mt-1 font-medium text-slate-700">{{ $event->getAvailableSlots() }} / {{ $event->quota }} tersisa</dd>
                            </div>
                        </dl>

                        <a href="{{ route('events.detail', $event) }}" class="mt-8 inline-flex w-fit items-center gap-2 rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Lihat detail event
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" /></svg>
                        </a>
                    </div>
                </div>
            @else
                <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($events as $event)
                        @php $status = $eventStatus($event); @endphp

                        <a href="{{ route('events.detail', $event) }}" class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg">
                            <div class="relative aspect-[16/10] overflow-hidden bg-slate-900">
                                @if($event->banner)
                                    <img src="{{ Storage::url($event->banner) }}" alt="{{ $event->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @else
                                    <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900"></div>
                                    <div class="absolute inset-0 grid place-items-center text-white/70">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" class="h-12 w-12" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 21h8M5 7h14M5 7l-3 6h6L5 7Zm14 0-3 6h6l-3-6Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 13a3 3 0 0 0 6 0M16 13a3 3 0 0 0 6 0" />
                                        </svg>
                                    </div>
                                @endif

                                <div class="absolute inset-x-4 top-4 flex flex-wrap items-center gap-2">
                                    @if($event->category)
                                        <span class="rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-semibold text-slate-700 shadow-sm backdrop-blur">{{ $event->category->name }}</span>
                                    @endif
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold shadow-sm ring-1 ring-inset {{ $status['class'] }}">{{ $status['label'] }}</span>
                                </div>
                            </div>

                            <div class="flex flex-1 flex-col p-6">
                                <h3 class="text-lg font-semibold tracking-tight text-slate-950 transition group-hover:text-indigo-700">{{ $event->name }}</h3>
                                <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ \Illuminate\Support\Str::limit($event->description, 110) }}</p>

                                <dl class="mt-5 grid grid-cols-2 gap-3 border-t border-slate-100 pt-5 text-sm">
                                    <div>
                                        <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Penyelenggara</dt>
                                        <dd class="mt-1 truncate font-medium text-slate-700">{{ $event->organizer->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Harga</dt>
                                        <dd class="mt-1 font-medium text-slate-700">{{ $event->price > 0 ? 'Rp ' . number_format($event->price, 0, ',', '.') : 'Gratis' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Jadwal</dt>
                                        <dd class="mt-1 font-medium text-slate-700">{{ $event->event_start->format('d M Y') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Kuota</dt>
                                        <dd class="mt-1 font-medium text-slate-700">{{ $event->getAvailableSlots() }} / {{ $event->quota }} tersisa</dd>
                                    </div>
                                </dl>

                                <div class="mt-6 flex items-center gap-2 text-sm font-semibold text-indigo-600">
                                    Lihat detail
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 transition group-hover:translate-x-0.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" /></svg>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- About / values --}}
    <section id="tentang" class="scroll-mt-24 bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-600">Tentang JuriOnline</p>
                    <h2 class="mt-3 max-w-xl text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Dibangun untuk penyelenggara, juri, dan peserta.</h2>
                    <p class="mt-4 max-w-xl text-base leading-7 text-slate-600">Kami percaya penilaian yang baik dimulai dari proses yang jelas. JuriOnline membantu tim menyimpan data di satu tempat, mengurangi rekap manual, dan membuat hasil kompetisi lebih mudah dipertanggungjawabkan.</p>
                    <a href="{{ route('about') }}" class="mt-8 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 transition hover:text-indigo-500">
                        Pelajari lebih lanjut
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" /></svg>
                    </a>
                </div>

                <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-1">
                    @foreach($values as $value)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <div class="grid h-10 w-10 place-items-center rounded-lg bg-white text-indigo-600 shadow-sm ring-1 ring-inset ring-slate-200">
                                {!! $value['icon'] !!}
                            </div>
                            <h3 class="mt-4 text-sm font-semibold text-slate-950">{{ $value['title'] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $value['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="bg-slate-950 py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950 px-6 py-12 sm:px-12 sm:py-16">
                <div class="pointer-events-none absolute -right-24 -top-24 h-64 w-64 rounded-full bg-indigo-500/20 blur-3xl"></div>
                <div class="relative max-w-2xl">
                    <h2 class="text-3xl font-semibold tracking-tight text-white sm:text-4xl">Siap menjalankan event yang lebih rapi?</h2>
                    <p class="mt-4 text-base leading-7 text-slate-300">Buat akun, siapkan event, dan mulai penilaian digital bersama JuriOnline.</p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-xl bg-white px-5 py-3 text-sm font-semibold text-slate-950 shadow-sm transition hover:bg-slate-100">Mulai sekarang</a>
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/5 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">Masuk ke akun</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
