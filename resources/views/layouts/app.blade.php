<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'JURI.ON'))</title>

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

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body class="font-sans antialiased bg-slate-50 text-slate-900">
    @if(session('login_loading'))
    @include('partials.loading-overlay', ['autoShow' => true])
    @endif

    @auth
    @php
        $currentEvent = null;
        if (request()->route('event')) {
            $currentEvent = request()->route('event');
            if (!$currentEvent->relationLoaded('eventForm')) {
                $currentEvent->load('eventForm');
            }
            if (!$currentEvent->relationLoaded('juryFormTemplate')) {
                $currentEvent->load('juryFormTemplate');
            }
            if (!$currentEvent->relationLoaded('registrations')) {
                $currentEvent->load('registrations');
            }
        } elseif (request()->routeIs('events.show') || request()->routeIs('events.edit') || request()->routeIs('events.form.*') || request()->routeIs('events.jury-form.*') || request()->routeIs('events.participants') || request()->routeIs('jury.leaderboard') || request()->routeIs('events.recap') || request()->routeIs('events.jury-assignments.*')) {
            try {
                $eventId = request()->route()->parameter('event');
                if ($eventId) {
                    $currentEvent = \App\Models\Event::with(['eventForm', 'juryFormTemplate', 'registrations'])->find($eventId);
                }
            } catch (\Exception $e) {
                $currentEvent = null;
            }
        }
    @endphp

    <div x-data="{ sidebarOpen: false, eventMenuOpen: true }" class="min-h-screen bg-slate-50">
        <div x-show="sidebarOpen" x-cloak x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/60 lg:hidden" @click="sidebarOpen = false"></div>

        <aside class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-white/10 bg-slate-950 text-slate-300 transition-transform duration-200 lg:translate-x-0" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <div class="flex h-20 items-center gap-3 border-b border-white/10 px-6">
                <x-brand.logo :href="route('home')" variant="dark" size="sm" tile />
                <button type="button" @click="sidebarOpen = false" class="ml-auto grid h-9 w-9 place-items-center rounded-lg text-slate-400 transition hover:bg-white/5 hover:text-white lg:hidden" aria-label="Tutup menu">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" /></svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-5" aria-label="Navigasi aplikasi">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Menu</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('dashboard') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('dashboard') ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                        Dashboard
                    </a>

                    @if(auth()->user()->canManageEvents())
                    <a href="{{ route('events.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('events.index') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('events.index') ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                        {{ auth()->user()->isEventAdmin() ? 'Event Saya' : 'Event' }}
                    </a>
                    @endif
                </div>

                @if($currentEvent && auth()->user()->canManageEvent($currentEvent))
                <div class="mt-6 border-t border-white/10 pt-4">
                    <button type="button" @click="eventMenuOpen = !eventMenuOpen" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500 transition hover:text-slate-300">
                        <span>Kelola Event</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 transition-transform" :class="eventMenuOpen ? '' : '-rotate-90'" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
                    </button>

                    <div x-show="eventMenuOpen" x-cloak x-collapse class="mt-1 space-y-1">
                        <a href="{{ route('events.show', $currentEvent) }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('events.show') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('events.show') ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                            Detail Event
                        </a>
                        <a href="{{ route('events.edit', $currentEvent) }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('events.edit') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('events.edit') ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                            Edit Event
                        </a>
                        <a href="{{ route('events.form.create', $currentEvent) }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('events.form.*') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('events.form.*') ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                            Form Pendaftaran
                            @if($currentEvent->eventForm)
                            <span class="ml-auto inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-400/15 text-emerald-300 ring-1 ring-inset ring-emerald-400/30">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3 w-3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg>
                            </span>
                            @endif
                        </a>

                        @if(auth()->user()->isAdmin() || auth()->user()->isAdminOwner())
                        <a href="{{ route('events.jury-form.create', $currentEvent) }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('events.jury-form.*') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('events.jury-form.*') ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                            Form Penilaian
                            @if($currentEvent->juryFormTemplate)
                            <span class="ml-auto inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-400/15 text-emerald-300 ring-1 ring-inset ring-emerald-400/30">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3 w-3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg>
                            </span>
                            @endif
                        </a>

                        @if($currentEvent->juryFormTemplate)
                        <a href="#" onclick="if(typeof showJuryFormPreview === 'function') { showJuryFormPreview(); return false; } else { alert('Fungsi preview belum tersedia di halaman ini'); return false; }" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-400 transition hover:bg-white/5 hover:text-white">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-600 group-hover:bg-slate-400"></span>
                            Preview Form Juri
                        </a>
                        @endif
                        @endif

                        <a href="{{ route('events.participants', $currentEvent) }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('events.participants') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('events.participants') ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                            Daftar Peserta
                            <span class="ml-auto rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-semibold text-slate-300">{{ $currentEvent->registrations->count() }}</span>
                        </a>

                        @if($currentEvent->registrations->isNotEmpty())
                        <a href="{{ route('jury.leaderboard', $currentEvent) }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('jury.leaderboard') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('jury.leaderboard') ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                            Leaderboard
                        </a>
                        @endif

                        <a href="{{ route('payments.pending') }}?event={{ $currentEvent->id }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('payments.*') && request()->get('event') == $currentEvent->id ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('payments.*') && request()->get('event') == $currentEvent->id ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                            Verifikasi Pembayaran
                        </a>

                        <a href="{{ route('events.recap', $currentEvent) }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('events.recap') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('events.recap') ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                            Rekapitulasi Nilai
                        </a>

                        @if($currentEvent->juryFormTemplates()->count() > 0)
                        @php $assignedCount = \App\Models\EventJuryFormAssignment::where('event_id', $currentEvent->id)->count(); @endphp
                        <a href="{{ route('events.jury-assignments.index', $currentEvent) }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('events.jury-assignments.*') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('events.jury-assignments.*') ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                            Assign Form ke Juri
                            @if($assignedCount > 0)
                            <span class="ml-auto rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-semibold text-slate-300">{{ $assignedCount }}</span>
                            @endif
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                @if(auth()->user()->role && auth()->user()->isParticipant())
                <div class="mt-6 border-t border-white/10 pt-4">
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Peserta</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('events.public') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('events.public') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('events.public') ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                            Semua Event
                        </a>
                        <a href="{{ route('registrations.my') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('registrations.*') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('registrations.*') ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                            Pendaftaran Saya
                        </a>
                    </div>
                </div>
                @endif

                @if(auth()->user()->role && (auth()->user()->isJury() || auth()->user()->isAdminOrOwner()))
                <div class="mt-6 border-t border-white/10 pt-4">
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Penilaian</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('jury.invitations.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('jury.invitations.*') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('jury.invitations.*') ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                            Undangan Juri
                            @php $pendingInviteCount = auth()->user()->pendingJuryAssignments()->count(); @endphp
                            @if($pendingInviteCount > 0)
                            <span class="ml-auto rounded-full bg-amber-400/20 px-2 py-0.5 text-[10px] font-semibold text-amber-300">{{ $pendingInviteCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('jury.events') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('jury.events', 'jury.participants', 'jury.leaderboard', 'jury.score', 'jury.score.store', 'jury.edit-score', 'jury.update-score') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('jury.events', 'jury.participants', 'jury.leaderboard', 'jury.score', 'jury.score.store', 'jury.edit-score', 'jury.update-score') ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                            Penilaian
                        </a>
                    </div>
                </div>
                @endif

                @if(auth()->user()->role && auth()->user()->canVerifyPayments())
                <div class="mt-6 border-t border-white/10 pt-4">
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Administrasi</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('payments.pending') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('payments.*') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('payments.*') ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                            Verifikasi Pembayaran
                        </a>
                        @if(auth()->user()->canManageAllEvents())
                        <a href="{{ route('jury.all-scores') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('jury.all-scores') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('jury.all-scores') ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                            Semua Penilaian
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                @if(auth()->user()->isAdminOwner())
                <div class="mt-6 border-t border-white/10 pt-4">
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-amber-400/80">Admin Owner</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('owner.users.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('owner.users.*') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('owner.users.*') ? 'bg-amber-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                            Manajemen Pengguna
                        </a>
                    </div>
                </div>
                @endif

                <div class="mt-6 border-t border-white/10 pt-4">
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Informasi</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('about') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('about') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('about') ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                            Tentang JURI.ON
                        </a>
                        <a href="{{ route('company-profile') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('company-profile') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('company-profile') ? 'bg-indigo-400' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                            Company Profile
                        </a>
                    </div>
                </div>
            </nav>

            <div class="border-t border-white/10 p-4">
                <a href="{{ route('home') }}" class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium text-slate-400 transition hover:bg-white/5 hover:text-white">
                    Lihat situs
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" /></svg>
                </a>
            </div>
        </aside>

        <div class="lg:pl-72">
            <header x-data="{ userMenuOpen: false }" class="sticky top-0 z-30 border-b border-slate-200 bg-white/85 backdrop-blur">
                <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                    <button type="button" @click="sidebarOpen = true" class="grid h-10 w-10 place-items-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50 lg:hidden" aria-label="Buka menu navigasi">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" /></svg>
                    </button>

                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Ruang kerja</p>
                        <p class="truncate text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                    </div>

                    <div class="ml-auto flex items-center gap-2">
                        <a href="{{ route('home') }}" class="hidden items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 sm:inline-flex">
                            Lihat situs
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" /></svg>
                        </a>

                        <div class="relative" x-data="{ notifOpen: false }" @click.outside="notifOpen = false">
                            @php
                                $unreadNotifications = auth()->user()->unreadNotifications()->latest()->take(8)->get();
                                $unreadCount = auth()->user()->unreadNotifications()->count();
                            @endphp
                            <button type="button" @click="notifOpen = !notifOpen" :aria-expanded="notifOpen.toString()"
                                    class="relative grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50"
                                    aria-label="Notifikasi">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-5 w-5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0" />
                                </svg>
                                @if($unreadCount > 0)
                                <span class="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                                @endif
                            </button>

                            <div x-show="notifOpen" x-cloak x-transition.origin.top.right
                                 @keydown.escape.window="notifOpen = false"
                                 class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg">
                                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-semibold text-slate-900">Notifikasi</p>
                                        @if($unreadCount > 0)
                                        <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-600 ring-1 ring-inset ring-rose-100">{{ $unreadCount }} baru</span>
                                        @endif
                                    </div>
                                    @if($unreadCount > 0)
                                    <form method="POST" action="{{ route('jury.notifications.read') }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg px-2 py-1 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-50 hover:underline">Tandai dibaca</button>
                                    </form>
                                    @endif
                                </div>

                                <div class="scrollbar-brand max-h-80 divide-y divide-slate-50 overflow-y-auto">
                                    @forelse($unreadNotifications as $notification)
                                    <a href="{{ $notification->data['url'] ?? route('dashboard') }}" class="block px-4 py-3 transition hover:bg-slate-50">
                                        <div class="flex items-start gap-2.5">
                                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-500"></span>
                                            <span class="min-w-0">
                                                <span class="block text-sm font-semibold text-slate-900">{{ $notification->data['title'] ?? 'Notifikasi' }}</span>
                                                <span class="mt-1 block text-xs leading-5 text-slate-600">{{ $notification->data['message'] ?? '' }}</span>
                                                <span class="mt-1 block text-[11px] text-slate-400">{{ $notification->created_at?->diffForHumans() }}</span>
                                            </span>
                                        </div>
                                    </a>
                                    @empty
                                    <p class="px-4 py-8 text-center text-sm text-slate-500">Belum ada notifikasi baru.</p>
                                    @endforelse
                                </div>

                                @if(auth()->user()->isJury() || auth()->user()->isAdminOrOwner())
                                <a href="{{ route('jury.invitations.index') }}" class="flex items-center justify-between border-t border-slate-100 px-4 py-3 text-xs font-semibold text-indigo-600 transition hover:bg-slate-50">
                                    Buka undangan juri
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" /></svg>
                                </a>
                                @endif
                            </div>
                        </div>

                        <div class="relative" @click.outside="userMenuOpen = false">
                            <button type="button" @click="userMenuOpen = !userMenuOpen" :aria-expanded="userMenuOpen.toString()" class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-left transition hover:bg-slate-50">
                                <span class="grid h-8 w-8 place-items-center rounded-lg bg-slate-950 text-xs font-semibold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                <span class="hidden sm:block">
                                    <span class="block text-xs font-semibold text-slate-900">{{ auth()->user()->name }}</span>
                                    <span class="block text-[11px] text-slate-500">{{ auth()->user()->roleLabel() }}</span>
                                </span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 text-slate-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
                            </button>

                            <div x-show="userMenuOpen" x-cloak x-transition class="absolute right-0 mt-2 w-60 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg">
                                <div class="border-b border-slate-100 px-4 py-3">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                                </div>
                                <div class="p-2">
                                    <a href="{{ route('profile.edit') }}" class="flex items-center rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Profil</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex w-full items-center rounded-lg px-3 py-2 text-left text-sm font-medium text-rose-600 transition hover:bg-rose-50">Keluar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @yield('content')
                {{ $slot ?? '' }}
            </main>
        </div>
    </div>
    @else
    <header x-data="{ open: false }" class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/85 backdrop-blur">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-6 lg:px-8">
            <x-brand.logo :href="route('home')" size="md" />

            <nav class="hidden items-center gap-8 md:flex" aria-label="Navigasi utama">
                <a href="{{ route('home') }}#fitur" class="text-sm font-medium text-slate-600 transition hover:text-slate-950">Fitur</a>
                <a href="{{ route('home') }}#cara-kerja" class="text-sm font-medium text-slate-600 transition hover:text-slate-950">Cara kerja</a>
                <a href="{{ route('home') }}#events" class="text-sm font-medium text-slate-600 transition hover:text-slate-950">Event</a>
                <a href="{{ route('about') }}" class="text-sm font-medium text-slate-600 transition hover:text-slate-950">Tentang</a>
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 hover:text-slate-950">Masuk</a>
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">Daftar gratis</a>
            </div>

            <button @click="open = !open" :aria-expanded="open.toString()" class="grid h-10 w-10 place-items-center rounded-lg border border-slate-200 text-slate-700 transition hover:bg-slate-50 md:hidden" aria-label="Buka menu navigasi">
                <svg x-show="!open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" /></svg>
                <svg x-show="open" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" /></svg>
            </button>
        </div>

        <div x-show="open" x-cloak x-transition.opacity class="border-t border-slate-200 bg-white md:hidden">
            <nav class="mx-auto flex max-w-7xl flex-col gap-1 px-6 py-4" aria-label="Navigasi mobile">
                <a @click="open = false" href="{{ route('home') }}#fitur" class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Fitur</a>
                <a @click="open = false" href="{{ route('home') }}#cara-kerja" class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Cara kerja</a>
                <a @click="open = false" href="{{ route('home') }}#events" class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Event</a>
                <a @click="open = false" href="{{ route('about') }}" class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Tentang</a>
                <div class="mt-3 flex flex-col gap-2 border-t border-slate-100 pt-4">
                    <a @click="open = false" href="{{ route('login') }}" class="rounded-lg border border-slate-200 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Masuk</a>
                    <a @click="open = false" href="{{ route('register') }}" class="rounded-lg bg-slate-950 px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-slate-800">Daftar gratis</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="min-h-[60vh] bg-slate-50">
        @if(request()->routeIs('home'))
            @yield('content')
            {{ $slot ?? '' }}
        @else
            <div class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
                @yield('content')
                {{ $slot ?? '' }}
            </div>
        @endif
    </main>

    <footer class="border-t border-white/10 bg-slate-950 text-slate-300">
        <div class="mx-auto max-w-7xl px-6 py-14 lg:px-8">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
                <div>
                    <x-brand.logo :href="route('home')" variant="dark" size="md" tile descriptor />
                    <p class="mt-4 max-w-sm text-sm leading-6 text-slate-400">Platform penilaian digital untuk event kompetisi yang adil, transparan, dan efisien.</p>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-white">Produk</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li><a href="{{ route('home') }}#fitur" class="transition hover:text-white">Fitur</a></li>
                        <li><a href="{{ route('home') }}#cara-kerja" class="transition hover:text-white">Cara kerja</a></li>
                        <li><a href="{{ route('home') }}#events" class="transition hover:text-white">Event</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-white">Perusahaan</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li><a href="{{ route('about') }}" class="transition hover:text-white">Tentang</a></li>
                        <li><a href="{{ route('company-profile') }}" class="transition hover:text-white">Company profile</a></li>
                        <li><a href="mailto:info@jurionline.com" class="transition hover:text-white">Kontak</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-white">Akun</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li><a href="{{ route('login') }}" class="transition hover:text-white">Masuk</a></li>
                        <li><a href="{{ route('register') }}" class="transition hover:text-white">Daftar gratis</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 flex flex-col gap-4 border-t border-white/10 pt-6 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} JURI.ON. All rights reserved.</p>
                <p>Dibuat untuk kompetisi yang lebih adil.</p>
            </div>
        </div>
    </footer>
    @endauth

    @stack('scripts')
</body>
</html>
