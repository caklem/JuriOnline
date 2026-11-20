<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>JuriOnline — {{ $authTitle ?? 'Akun' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/premium-global.css') }}?v={{ filemtime(public_path('css/premium-global.css')) }}">

    @stack('styles')
</head>

<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
    <div class="grid min-h-screen lg:grid-cols-2">
        <div class="relative hidden overflow-hidden bg-slate-950 p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="pointer-events-none absolute inset-0" style="background-image: radial-gradient(ellipse at top right, rgba(99, 102, 241, 0.30), transparent 55%), radial-gradient(ellipse at bottom left, rgba(14, 165, 233, 0.16), transparent 50%);"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[0.07]" style="background-image: linear-gradient(to right, white 1px, transparent 1px), linear-gradient(to bottom, white 1px, transparent 1px); background-size: 64px 64px;"></div>

            <div class="relative">
                <a href="{{ route('home') }}" class="flex items-center gap-3 text-white">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-white/10 ring-1 ring-inset ring-white/10">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 21h8M5 7h14M5 7l-3 6h6L5 7Zm14 0-3 6h6l-3-6Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 13a3 3 0 0 0 6 0M16 13a3 3 0 0 0 6 0" />
                        </svg>
                    </span>
                    <span class="text-lg font-semibold tracking-tight">JuriOnline</span>
                </a>
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

            <p class="relative text-xs text-slate-500">&copy; {{ date('Y') }} JuriOnline. Dibuat untuk kompetisi yang lebih adil.</p>
        </div>

        <div class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <div class="mb-8 flex items-center justify-center gap-3 lg:hidden">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-950 text-white">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 21h8M5 7h14M5 7l-3 6h6L5 7Zm14 0-3 6h6l-3-6Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 13a3 3 0 0 0 6 0M16 13a3 3 0 0 0 6 0" />
                        </svg>
                    </span>
                    <span class="text-lg font-semibold tracking-tight text-slate-950">JuriOnline</span>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="mb-6">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-indigo-600">JuriOnline</p>
                        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">{{ $authTitle ?? 'Akun JuriOnline' }}</h1>
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

                <p class="mt-6 text-center text-xs text-slate-500">&copy; {{ date('Y') }} JuriOnline. Semua hak dilindungi.</p>
            </div>
        </div>
    </div>

    @include('partials.loading-overlay', ['autoShow' => false])

    @stack('scripts')
</body>

</html>
