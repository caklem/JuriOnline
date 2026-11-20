@extends('layouts.app')

@section('title', 'Company Profile - JuriOnline')

@section('content')
@php
$values = [
    [
        'title' => 'Profesional',
        'description' => 'Kami berkomitmen memberikan layanan profesional dengan standar tinggi dalam setiap aspek platform.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4m5.6 2A9.6 9.6 0 0 1 12 21a9.6 9.6 0 0 1-8.6-9A9.6 9.6 0 0 1 12 3a9.6 9.6 0 0 1 8.6 9Z"/></svg>',
        'iconClass' => 'bg-indigo-50 text-indigo-600 ring-indigo-100',
    ],
    [
        'title' => 'Transparan',
        'description' => 'Transparansi dalam proses penilaian dan manajemen event adalah prioritas utama kami.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>',
        'iconClass' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
    ],
    [
        'title' => 'Inovatif',
        'description' => 'Kami terus berinovasi untuk memberikan solusi terbaik dan fitur-fitur terkini.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 2 4.5 13.5H11L9.5 22 19 9.5h-6L13 2Z"/></svg>',
        'iconClass' => 'bg-amber-50 text-amber-600 ring-amber-100',
    ],
    [
        'title' => 'Terpercaya',
        'description' => 'Membangun kepercayaan melalui layanan yang konsisten dan dapat diandalkan.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4.5 6v5.2c0 4.4 3.1 8.4 7.5 9.8 4.4-1.4 7.5-5.4 7.5-9.8V6L12 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>',
        'iconClass' => 'bg-rose-50 text-rose-600 ring-rose-100',
    ],
];

$services = [
    [
        'title' => 'Manajemen Event',
        'description' => 'Kelola event dari awal hingga akhir dengan fitur lengkap untuk penyelenggara.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6m-7 4h8m-8 4h5m-5 4h8M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/></svg>',
    ],
    [
        'title' => 'Sistem Penilaian',
        'description' => 'Form penilaian custom yang dapat disesuaikan dengan kebutuhan event Anda.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 21h8M5 7h14M5 7l-3 6h6L5 7Zm14 0-3 6h6l-3-6Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 13a3 3 0 0 0 6 0M16 13a3 3 0 0 0 6 0"/></svg>',
    ],
    [
        'title' => 'Manajemen Peserta',
        'description' => 'Tracking pendaftaran, verifikasi, dan status peserta secara real-time.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1m13-9a3 3 0 1 0 0-6m-3 6a3 3 0 1 0 0-6m5 14h4v-1a4 4 0 0 0-3-3.87"/></svg>',
    ],
    [
        'title' => 'Sistem Pembayaran',
        'description' => 'Verifikasi pembayaran yang terintegrasi dan mudah dikelola.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5v-9Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h3"/></svg>',
    ],
    [
        'title' => 'Rekapitulasi Nilai',
        'description' => 'Laporan penilaian lengkap dengan peringkat dan statistik detail.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5m0 14h16M8 16v-5m4 5V8m4 8v-3"/></svg>',
    ],
    [
        'title' => 'Leaderboard',
        'description' => 'Peringkat peserta yang update secara real-time berdasarkan penilaian juri.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8m-4-4v4m-5-9H4a2 2 0 0 1-2-2V8h4m14 4h-3m3 0a2 2 0 0 1-2 2h-1M8 8h8m-8 0a4 4 0 0 0 8 0M6 8V5h12v3"/></svg>',
    ],
];
@endphp

<div class="page-header-premium">
    <h1>Company Profile</h1>
    <p>Tentang JuriOnline - Platform Penilaian Digital Terpercaya</p>
</div>

<div class="premium-card">
    <div class="premium-card-header">
        <h2 class="premium-card-title">Tentang JuriOnline</h2>
    </div>

    <p class="text-base leading-7 text-slate-600">
        <strong class="font-semibold text-slate-950">JuriOnline</strong> adalah platform digital inovatif yang dirancang khusus untuk memudahkan proses penilaian dan manajemen event kompetisi, lomba, dan acara penjurian lainnya. Kami menyediakan solusi terintegrasi untuk mengelola seluruh siklus event, mulai dari pendaftaran peserta hingga penilaian oleh juri profesional.
    </p>

    <div class="mt-8 grid gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
            <h3 class="text-sm font-semibold uppercase tracking-[0.14em] text-indigo-600">Visi</h3>
            <p class="mt-3 text-sm leading-6 text-slate-600">
                Menjadi platform terdepan di Indonesia untuk sistem penilaian digital yang profesional, transparan, dan terpercaya, mendukung pengembangan talenta dan kompetisi berkualitas di berbagai bidang.
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
            <h3 class="text-sm font-semibold uppercase tracking-[0.14em] text-indigo-600">Misi</h3>
            <ul class="mt-3 space-y-2.5">
                @foreach([
                    'Menyediakan sistem penilaian yang akurat, transparan, dan mudah digunakan',
                    'Mendukung penyelenggara event dalam mengelola kompetisi secara profesional',
                    'Memfasilitasi juri dalam memberikan penilaian yang objektif dan terstruktur',
                    'Memberikan pengalaman terbaik bagi peserta dalam proses pendaftaran dan tracking',
                    'Mengembangkan teknologi yang terus berinovasi sesuai kebutuhan pengguna',
                ] as $mission)
                    <li class="flex items-start gap-2.5 text-sm leading-6 text-slate-600">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-500"></span>
                        <span>{{ $mission }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <h3 class="mt-10 text-xl font-semibold tracking-tight text-slate-950">Nilai-Nilai Kami</h3>

    <div class="mt-5 grid gap-4 sm:grid-cols-2">
        @foreach($values as $value)
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex items-center gap-3">
                    <span class="grid h-9 w-9 place-items-center rounded-lg ring-1 ring-inset {{ $value['iconClass'] }}">
                        {!! $value['icon'] !!}
                    </span>
                    <h4 class="text-sm font-semibold text-slate-950">{{ $value['title'] }}</h4>
                </div>
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $value['description'] }}</p>
            </div>
        @endforeach
    </div>

    <h3 class="mt-10 text-xl font-semibold tracking-tight text-slate-950">Layanan Kami</h3>

    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($services as $service)
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 transition hover:border-indigo-200 hover:bg-white">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-indigo-600 ring-1 ring-inset ring-slate-200">
                    {!! $service['icon'] !!}
                </span>
                <h4 class="mt-4 text-sm font-semibold text-slate-950">{{ $service['title'] }}</h4>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $service['description'] }}</p>
            </div>
        @endforeach
    </div>

    <h3 class="mt-10 text-xl font-semibold tracking-tight text-slate-950">Kontak Kami</h3>

    <div class="mt-5 grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-5 sm:grid-cols-3">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Email</p>
            <a href="mailto:info@jurionline.com" class="mt-1.5 block text-sm font-medium text-slate-700 transition hover:text-indigo-600">info@jurionline.com</a>
        </div>
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Website</p>
            <p class="mt-1.5 text-sm font-medium text-slate-700">www.jurionline.com</p>
        </div>
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Support</p>
            <a href="mailto:support@jurionline.com" class="mt-1.5 block text-sm font-medium text-slate-700 transition hover:text-indigo-600">support@jurionline.com</a>
        </div>
    </div>
</div>

<div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-6 sm:p-8">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        <div class="max-w-2xl">
            <h3 class="text-lg font-semibold tracking-tight text-slate-950">Siap menggelar kompetisi yang lebih rapi?</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Bergabung bersama penyelenggara, juri, dan peserta yang menggunakan JuriOnline untuk pengalaman kompetisi yang adil dan transparan.
            </p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                Daftar Sekarang
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" />
                </svg>
            </a>
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-slate-950">
                Masuk
            </a>
        </div>
    </div>
</div>
@endsection
