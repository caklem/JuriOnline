<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Ditolak — JURI.ON</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|montserrat:600,700,800&display=swap" rel="stylesheet" />

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/brand/favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/brand/apple-touch-icon.png') }}">
    <meta name="theme-color" content="#0B1F46">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/premium-global.css') }}?v={{ filemtime(public_path('css/premium-global.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/brand.css') }}?v={{ filemtime(public_path('css/brand.css')) }}">
</head>

<body class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-10 font-sans text-slate-900 antialiased">
    <main class="w-full max-w-lg">
        <a href="{{ route('home') }}" class="mb-6 flex justify-center">
            <x-brand.logo size="sm" />
        </a>

        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-rose-50 text-rose-600 ring-1 ring-inset ring-rose-100">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-7 w-7" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 2.8 17a1.7 1.7 0 0 0 1.5 2.5h15.4A1.7 1.7 0 0 0 21.2 17L13.7 3.9a1.7 1.7 0 0 0-3 0Z" />
                </svg>
            </span>

            <p class="mt-5 text-xs font-semibold uppercase tracking-[0.16em] text-rose-500">Error 403</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Akses ditolak</h1>

            <p class="mt-3 text-sm leading-6 text-slate-600">
                Akun Anda tidak memiliki izin untuk membuka halaman ini.
                @auth
                Anda masuk sebagai <strong class="text-slate-900">{{ auth()->user()->name }}</strong>
                dengan peran <strong class="text-slate-900">{{ auth()->user()->roleLabel() }}</strong>.
                @else
                Silakan masuk terlebih dahulu dengan akun yang sesuai.
                @endauth
            </p>

            <ul class="mx-auto mt-4 max-w-sm space-y-1 text-left text-xs leading-5 text-slate-500">
                <li>&bull; Admin event hanya bisa mengelola event miliknya sendiri.</li>
                <li>&bull; Halaman Admin Owner hanya untuk peran Admin Owner.</li>
                <li>&bull; Juri harus menerima undangan sebelum membuka data event.</li>
            </ul>

            <div class="mt-6 flex flex-wrap justify-center gap-2">
                @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Kembali ke Dashboard</a>
                @else
                <a href="{{ route('login') }}" class="btn btn-primary">Masuk</a>
                @endauth
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Halaman Sebelumnya</a>
            </div>

            @if(auth()->check())
            <p class="mt-5 break-all text-[11px] text-slate-400">
                {{ auth()->user()->email }} &middot; {{ request()->method() }} {{ request()->path() }}
            </p>
            @endif
        </div>
    </main>
</body>

</html>
