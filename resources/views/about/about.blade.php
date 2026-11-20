@extends('layouts.app')

@section('title', 'Tentang JuriOnline')

@section('content')
@php
$features = [
    [
        'title' => 'Form Pendaftaran Custom',
        'description' => 'Buat form pendaftaran sesuai kebutuhan event Anda dengan drag & drop builder yang mudah digunakan. Kumpulkan informasi peserta yang relevan dan terstruktur.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6m-7 4h8m-8 4h5m-5 4h8M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/></svg>',
    ],
    [
        'title' => 'Form Penilaian Juri',
        'description' => 'Sistem penilaian yang fleksibel dengan kriteria yang dapat disesuaikan. Satu event dapat memiliki multiple form penilaian untuk juri yang berbeda (contoh: Form Vokal, Form Adab, Form Terbang).',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 21h8M5 7h14M5 7l-3 6h6L5 7Zm14 0-3 6h6l-3-6Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 13a3 3 0 0 0 6 0M16 13a3 3 0 0 0 6 0"/></svg>',
    ],
    [
        'title' => 'Manajemen Peserta',
        'description' => 'Kelola pendaftaran peserta, verifikasi data, dan tracking status dengan mudah. Sistem otomatis mengelola kuota dan periode pendaftaran.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1m13-9a3 3 0 1 0 0-6m-3 6a3 3 0 1 0 0-6m5 14h4v-1a4 4 0 0 0-3-3.87"/></svg>',
    ],
    [
        'title' => 'Verifikasi Pembayaran',
        'description' => 'Upload bukti pembayaran dan verifikasi oleh admin. Status pembayaran terintegrasi dengan status pendaftaran peserta.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5v-9Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h3"/></svg>',
    ],
    [
        'title' => 'Rekapitulasi Nilai',
        'description' => 'Sistem rekapitulasi otomatis yang menggabungkan penilaian dari multiple juri dan form. Menghitung rata-rata nilai dengan akurat dan transparan.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5m0 14h16M8 16v-5m4 5V8m4 8v-3"/></svg>',
    ],
    [
        'title' => 'Leaderboard Real-time',
        'description' => 'Peringkat peserta yang update secara real-time berdasarkan penilaian juri. Tampilkan hasil kompetisi dengan jelas dan profesional.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8m-4-4v4m-5-9H4a2 2 0 0 1-2-2V8h4m14 4h-3m3 0a2 2 0 0 1-2 2h-1M8 8h8m-8 0a4 4 0 0 0 8 0M6 8V5h12v3"/></svg>',
    ],
];

$audiences = [
    [
        'title' => 'Admin / Penyelenggara',
        'description' => 'Admin yang menyelenggarakan event dan ingin mengelola kompetisi secara profesional dengan sistem yang terintegrasi.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0a9 9 0 0 0 0-18m0 18a5.6 5.6 0 0 0 0-18m0 18a5.6 5.6 0 0 1 0-18M3.6 9h16.8M3.6 15h16.8"/></svg>',
    ],
    [
        'title' => 'Juri',
        'description' => 'Juri profesional yang membutuhkan platform untuk memberikan penilaian yang terstruktur dan objektif.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 21h8M5 7h14M5 7l-3 6h6L5 7Zm14 0-3 6h6l-3-6Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 13a3 3 0 0 0 6 0M16 13a3 3 0 0 0 6 0"/></svg>',
    ],
    [
        'title' => 'Peserta',
        'description' => 'Peserta kompetisi yang ingin mendaftar dan tracking status pendaftaran serta hasil penilaian.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 21a8 8 0 0 0-16 0m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>',
    ],
    [
        'title' => 'Admin',
        'description' => 'Administrator yang mengelola seluruh sistem, verifikasi pembayaran, dan monitoring event.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4.5 6v5.2c0 4.4 3.1 8.4 7.5 9.8 4.4-1.4 7.5-5.4 7.5-9.8V6L12 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>',
    ],
];

$advantages = [
    'Sistem terintegrasi untuk seluruh proses event',
    'Form builder yang mudah digunakan tanpa coding',
    'Support multiple form penilaian per event',
    'Rekapitulasi nilai otomatis dan akurat',
    'Interface yang user-friendly dan profesional',
    'Real-time updates untuk semua pengguna',
    'Sistem keamanan data yang terjamin',
];
@endphp

<div class="page-header-premium">
    <h1>Tentang JuriOnline</h1>
    <p>Platform Penilaian Digital untuk Event Kompetisi</p>
</div>

<div class="premium-card">
    <div class="premium-card-header">
        <h2 class="premium-card-title">Apa itu JuriOnline?</h2>
    </div>

    <p class="text-base leading-7 text-slate-600">
        <strong class="font-semibold text-slate-950">JuriOnline</strong> adalah sistem manajemen event dan penilaian digital yang dirancang untuk memudahkan penyelenggaraan kompetisi, lomba, dan acara penjurian lainnya. Platform ini menyediakan solusi lengkap mulai dari pendaftaran peserta hingga penilaian oleh juri profesional.
    </p>

    <h3 class="mt-8 text-xl font-semibold tracking-tight text-slate-950">Fitur Utama</h3>

    <div class="mt-5 grid gap-4 sm:grid-cols-2">
        @foreach($features as $feature)
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                <div class="flex items-start gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-indigo-600 ring-1 ring-inset ring-slate-200">
                        {!! $feature['icon'] !!}
                    </span>
                    <div>
                        <h4 class="text-sm font-semibold text-slate-950">{{ $feature['title'] }}</h4>
                        <p class="mt-1.5 text-sm leading-6 text-slate-600">{{ $feature['description'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <h3 class="mt-10 text-xl font-semibold tracking-tight text-slate-950">Siapa yang Dapat Menggunakan JuriOnline?</h3>

    <div class="mt-5 grid gap-4 sm:grid-cols-2">
        @foreach($audiences as $audience)
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex items-center gap-3">
                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-100">
                        {!! $audience['icon'] !!}
                    </span>
                    <h4 class="text-sm font-semibold text-slate-950">{{ $audience['title'] }}</h4>
                </div>
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $audience['description'] }}</p>
            </div>
        @endforeach
    </div>

    <h3 class="mt-10 text-xl font-semibold tracking-tight text-slate-950">Keunggulan JuriOnline</h3>

    <ul class="mt-5 grid gap-3 sm:grid-cols-2">
        @foreach($advantages as $advantage)
            <li class="flex items-start gap-3 text-sm leading-6 text-slate-600">
                <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-100">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3 w-3" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                    </svg>
                </span>
                <span>{{ $advantage }}</span>
            </li>
        @endforeach
    </ul>
</div>

<div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-6 sm:p-8">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        <div class="max-w-2xl">
            <h3 class="text-lg font-semibold tracking-tight text-slate-950">Mulai Menggunakan JuriOnline</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Daftarkan event Anda sekarang dan rasakan kemudahan mengelola kompetisi dengan sistem digital yang profesional.
            </p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                Daftar Sekarang
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" />
                </svg>
            </a>
            <a href="{{ route('events.public') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-slate-950">
                Lihat Event Tersedia
            </a>
        </div>
    </div>
</div>
@endsection
