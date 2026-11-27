<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>JURI.ON — {{ $authTitle ?? 'Akun' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|montserrat:600,700,800&display=swap" rel="stylesheet" />

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/brand/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/brand/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/brand/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#0B1F46">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/premium-global.css') }}?v={{ filemtime(public_path('css/premium-global.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/brand.css') }}?v={{ filemtime(public_path('css/brand.css')) }}">

    @stack('styles')
</head>

<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
    <div class="grid min-h-screen lg:grid-cols-2">
        <div class="relative hidden overflow-hidden bg-slate-950 p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="pointer-events-none absolute inset-0" style="background-image: radial-gradient(ellipse at top right, rgba(47, 128, 255, 0.28), transparent 55%), radial-gradient(ellipse at bottom left, rgba(11, 31, 70, 0.55), transparent 55%);"></div>
            <div class="brand-pattern pointer-events-none absolute inset-0" aria-hidden="true"></div>

            <div class="relative">
                <x-brand.logo :href="route('home')" variant="dark" size="md" tile descriptor />
            </div>

            <div class="relative max-w-md">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-medium text-slate-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    Platform penilaian digital
                </span>
                <h2 class="mt-6 text-3xl font-semibold tracking-tight text-white xl:text-4xl">Penilaian kompetisi yang adil, transparan, dan efisien.</h2>
                <p class="mt-4 text-sm leading-7 text-slate-300">Satu alur kerja untuk pendaftaran peserta, penilaian juri, verifikasi pembayaran, dan rekapitulasi hasil.</p>
                <ul class="mt-8 space-y-3 text-sm text-slate-300">
                    <li class="flex items-center gap-3">
                        <span class="grid h-6 w-6 place-items-center rounded-full bg-emerald-400/15 text-emerald-300 ring-1 ring-inset ring-emerald-400/30">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg>
                        </span>
                        Form builder yang fleksibel
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="grid h-6 w-6 place-items-center rounded-full bg-emerald-400/15 text-emerald-300 ring-1 ring-inset ring-emerald-400/30">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg>
                        </span>
                        Penilaian multi-juri
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="grid h-6 w-6 place-items-center rounded-full bg-emerald-400/15 text-emerald-300 ring-1 ring-inset ring-emerald-400/30">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg>
                        </span>
                        Rekapitulasi otomatis
                    </li>
                </ul>
            </div>

            <p class="relative text-xs text-slate-500">&copy; {{ date('Y') }} JURI.ON. Dibuat untuk kompetisi yang lebih adil.</p>
        </div>

        <div class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <div class="mb-8 flex items-center justify-center gap-3 lg:hidden">
                    <x-brand.logo :href="route('home')" size="md" />
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="mb-6">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-indigo-600">JURI.ON</p>
                        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">{{ $authTitle ?? 'Akun JURI.ON' }}</h1>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $authSubtitle ?? 'Kelola akses akun Anda dengan aman.' }}</p>
                    </div>

                    @if(session('status') && session('status') !== 'verification-link-sent')
                    <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-error">
                        <strong>Terjadi kesalahan:</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{ $slot }}
                </div>

                <p class="mt-6 text-center text-xs text-slate-500">&copy; {{ date('Y') }} JURI.ON. Semua hak dilindungi.</p>
            </div>
        </div>
    </div>

    @include('partials.loading-overlay', ['autoShow' => false])

    @stack('scripts')
</body>

</html>
