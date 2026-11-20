@extends('layouts.app')

@section('title', 'Detail Event')

@section('content')
<div class="space-y-6">
    @if($event->banner)
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="relative cursor-pointer" onclick="openBannerModal()">
            <img id="eventBanner" src="{{ Storage::url($event->banner) }}" alt="{{ $event->name }}" class="h-56 w-full object-cover object-top transition duration-300 sm:h-72 lg:h-80">
            <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-3 bg-slate-950/60 px-4 py-3 text-white backdrop-blur sm:px-5">
                <span class="inline-flex items-center gap-2 text-sm font-medium">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    Lihat banner
                </span>
                <span class="hidden text-xs text-slate-200 sm:block">Klik untuk memperbesar</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Page Header -->
    <div class="page-header-premium">
        <a href="{{ route('events.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-indigo-600">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 6l-6 6 6 6" />
            </svg>
            Kembali ke daftar event
        </a>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <h1 class="break-words">{{ $event->name }}</h1>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    @if($event->category)
                    <span class="badge-premium badge-premium-info">{{ $event->category->name }}</span>
                    @endif
                    <span class="badge-premium {{ $event->status === 'active' ? 'badge-premium-success' : ($event->status === 'draft' ? 'badge-premium-warning' : 'badge-premium-info') }}">
                        {{ ['draft' => 'Draft', 'active' => 'Aktif', 'closed' => 'Ditutup', 'completed' => 'Selesai'][$event->status] ?? ucfirst($event->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->canManageEvent($event))
    <!-- Kontrol Penilaian Peserta -->
    <div class="premium-card">
        <div class="premium-card-header">
            <div>
                <h2 class="premium-card-title">Penilaian Peserta</h2>
                <p class="mt-1 text-xs text-slate-500">
                    Buka penilaian lebih awal, tutup sementara, atau biarkan otomatis terbuka sejak hari H.
                </p>
            </div>
            <span class="badge-premium {{ $event->isScoringOpen() ? 'badge-premium-success' : 'badge-premium-warning' }}">
                {{ $event->scoringStatusLabel() }}
            </span>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <form method="POST" action="{{ route('events.scoring', $event) }}">
                @csrf
                <input type="hidden" name="mode" value="open">
                <button type="submit" class="btn btn-success w-full" @disabled($event->scoringMode() === 'open')>
                    {{ $event->scoringMode() === 'open' ? 'Penilaian Sudah Dibuka' : 'Buka Penilaian Sekarang' }}
                </button>
            </form>
            <form method="POST" action="{{ route('events.scoring', $event) }}">
                @csrf
                <input type="hidden" name="mode" value="closed">
                <button type="submit" class="btn btn-outline-danger w-full" @disabled($event->scoringMode() === 'closed')>
                    {{ $event->scoringMode() === 'closed' ? 'Penilaian Sedang Ditutup' : 'Tutup Penilaian' }}
                </button>
            </form>
            <form method="POST" action="{{ route('events.scoring', $event) }}">
                @csrf
                <input type="hidden" name="mode" value="auto">
                <button type="submit" class="btn btn-outline-secondary w-full" @disabled($event->scoringMode() === 'auto')>
                    {{ $event->scoringMode() === 'auto' ? 'Mode Otomatis Aktif' : 'Otomatis (Mulai Hari H)' }}
                </button>
            </form>
        </div>

        <p class="mt-3 text-xs text-slate-500">
            Hari H: <strong>{{ $event->scoringOpensAt()?->format('d M Y') ?? 'belum diisi' }}</strong>
            @if($event->scoringMode() === 'auto')
            &middot; penilaian terbuka otomatis sejak tanggal tersebut.
            @elseif($event->scoringMode() === 'open')
            &middot; dibuka manual oleh panitia, juri sudah bisa menilai.
            @else
            &middot; ditutup manual, juri tidak bisa menilai sampai dibuka.
            @endif
        </p>
    </div>

    <!-- Action Buttons Section -->
    <div class="space-y-4">
        <!-- Primary Actions Row -->
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <!-- Edit Event -->
            <a href="{{ route('events.edit', $event) }}" class="group relative flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50/50 hover:text-indigo-700">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition group-hover:bg-indigo-100 group-hover:text-indigo-600">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.5 5.5 3 3M4 20l4.5-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20Z" />
                    </svg>
                </span>
                <span>Edit Event</span>
            </a>

            <!-- Kelola Form Pendaftaran -->
            <a href="{{ route('events.form.create', $event) }}" class="group relative flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50/50 hover:text-indigo-700">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition group-hover:bg-indigo-100 group-hover:text-indigo-600">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6m-7 4h8m-8 4h5m-5 4h8M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                    </svg>
                </span>
                <span>Kelola Form Pendaftaran</span>
                @if($event->eventForm)
                <span class="absolute right-2 top-2 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 text-white" title="Form sudah dibuat">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="h-3 w-3" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                    </svg>
                </span>
                @endif
            </a>

            @if(auth()->user()->isAdmin() || auth()->user()->isAdminOwner())
            <!-- Kelola Form Penilaian Juri -->
            <a href="{{ route('events.jury-form.create', $event) }}" class="group relative flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50/50 hover:text-indigo-700">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition group-hover:bg-indigo-100 group-hover:text-indigo-600">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 21h8M5 7h14M5 7l-3 6h6L5 7Zm14 0-3 6h6l-3-6Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2 13a3 3 0 0 0 6 0M16 13a3 3 0 0 0 6 0" />
                    </svg>
                </span>
                <span>Kelola Form Penilaian Juri</span>
                @if($event->juryFormTemplate)
                <span class="absolute right-2 top-2 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 text-white" title="Form penilaian sudah dibuat">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="h-3 w-3" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                    </svg>
                </span>
                @endif
            </a>

            @if($event->juryFormTemplate || $event->juryFormTemplates()->count() > 0)
            <!-- Preview Form Juri -->
            <button type="button" onclick="showJuryFormPreview()" class="group flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 text-left text-sm font-semibold text-slate-700 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50/50 hover:text-indigo-700">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition group-hover:bg-indigo-100 group-hover:text-indigo-600">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </span>
                <span>Preview Form Juri</span>
            </button>
            @endif

            @if($event->juryFormTemplates()->count() > 0)
            <!-- Kelola Assignment Form Juri -->
            <a href="{{ route('events.jury-assignments.index', $event) }}" class="group flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50/50 hover:text-indigo-700">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition group-hover:bg-indigo-100 group-hover:text-indigo-600">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1m13-9a3 3 0 1 0 0-6m-3 6a3 3 0 1 0 0-6m5 14h4v-1a4 4 0 0 0-3-3.87" />
                    </svg>
                </span>
                <span>Assign Form ke Juri</span>
            </a>
            @endif
            @endif
        </div>

        <!-- Event Status Actions -->
        <div class="flex flex-wrap justify-start gap-3">
            @if($event->status === 'draft')
            <form method="POST" action="{{ route('events.update-status', $event) }}" class="inline">
                @csrf
                <input type="hidden" name="status" value="active">
                <button type="submit" class="btn-premium btn-premium-success" onclick="return confirm('Aktifkan event ini? Event akan terlihat oleh peserta.')">
                    Aktifkan Event
                </button>
            </form>
            @elseif($event->status === 'active')
            <form method="POST" action="{{ route('events.update-status', $event) }}" class="inline">
                @csrf
                <input type="hidden" name="status" value="closed">
                <button type="submit" class="btn-premium btn-premium-warning" onclick="return confirm('Tutup event ini? Peserta tidak bisa mendaftar lagi.')">
                    Tutup Event
                </button>
            </form>
            @elseif($event->status === 'closed')
            <form method="POST" action="{{ route('events.update-status', $event) }}" class="inline">
                @csrf
                <input type="hidden" name="status" value="completed">
                <button type="submit" class="btn btn-secondary" onclick="return confirm('Tandai event ini sebagai selesai?')">
                    Selesaikan Event
                </button>
            </form>
            @endif
        </div>
    </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Deskripsi Section -->
        <div class="premium-card lg:col-span-2">
            <div class="premium-card-header">
                <h2 class="premium-card-title flex items-center gap-2">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5 text-slate-400" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6m-7 4h8m-8 4h5m-5 4h8M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                    </svg>
                    Deskripsi
                </h2>
            </div>
            <p class="whitespace-pre-line text-sm leading-7 text-slate-600">{{ $event->description }}</p>
        </div>

        <!-- Informasi Event Section -->
        <div class="premium-card">
            <div class="premium-card-header">
                <h2 class="premium-card-title flex items-center gap-2">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5 text-slate-400" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9h.01M11 12h1v4h1M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Z" />
                    </svg>
                    Informasi Event
                </h2>
            </div>

            <dl class="space-y-3">
                <!-- Penyelenggara -->
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                    <dt class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4 text-slate-400" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1M14 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 12h3v-1a4 4 0 0 0-3-3.87" />
                        </svg>
                        Penyelenggara
                    </dt>
                    <dd class="mt-1.5 pl-6 text-sm font-medium text-slate-800">{{ $event->organizer->name }}</dd>
                </div>

                <!-- Lokasi -->
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                    <dt class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4 text-slate-400" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.5 10a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z" />
                        </svg>
                        Lokasi
                    </dt>
                    <dd class="mt-1.5 pl-6 text-sm font-medium text-slate-800">{{ $event->location ?? 'Online' }}</dd>
                </div>

                <!-- Harga -->
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 p-3">
                    <dt class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-indigo-600">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5v-9Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h3" />
                        </svg>
                        Harga
                    </dt>
                    <dd class="mt-1.5 pl-6 text-xl font-semibold tracking-tight text-indigo-700">Rp {{ number_format($event->price, 0, ',', '.') }}</dd>
                </div>

                <!-- Kuota -->
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                    <dt class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4 text-slate-400" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1M14 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 12h3v-1a4 4 0 0 0-3-3.87" />
                        </svg>
                        Kuota
                    </dt>
                    <dd class="mt-1.5 pl-6">
                        <div class="text-sm font-semibold text-slate-800">{{ $event->registered_count }} / {{ $event->quota }} peserta</div>
                        <div class="mt-2">
                            @if($event->getAvailableSlots() > 0)
                            <span class="badge-premium badge-premium-success">{{ $event->getAvailableSlots() }} slot tersisa</span>
                            @else
                            <span class="badge-premium badge-premium-danger">Kuota penuh</span>
                            @endif
                        </div>
                    </dd>
                </div>

                <!-- Periode Pendaftaran -->
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                    <dt class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4 text-slate-400" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 3v3m8-3v3M4 8h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                        </svg>
                        Periode Pendaftaran
                    </dt>
                    <dd class="mt-1.5 pl-6 text-sm text-slate-700">
                        {{ $event->registration_start->format('d M Y') }}<br>
                        s/d {{ $event->registration_end->format('d M Y') }}
                    </dd>
                </div>

                <!-- Hari H -->
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 p-3">
                    <dt class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-indigo-600">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4 text-indigo-400" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 3v3m8-3v3M4 8h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 14 2 2 4-4" />
                        </svg>
                        Tanggal Hari H
                    </dt>
                    <dd class="mt-1.5 pl-6 text-sm font-semibold text-indigo-900">
                        {{ $event->event_date?->format('d M Y') ?? $event->event_start?->format('d M Y') ?? '-' }}
                    </dd>
                </div>

                <!-- Periode Event -->
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                    <dt class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4 text-slate-400" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M3 12h2m14 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Periode Event
                    </dt>
                    <dd class="mt-1.5 pl-6 text-sm text-slate-700">
                        {{ $event->event_start->format('d M Y') }}<br>
                        s/d {{ $event->event_end->format('d M Y') }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Quick Links Section -->
    <div class="grid gap-4 sm:grid-cols-2">
        <!-- Daftar Peserta -->
        <a href="{{ route('events.participants', $event) }}" class="group flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-100">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1M14 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 12h3v-1a4 4 0 0 0-3-3.87" />
                </svg>
            </span>
            <span class="min-w-0 flex-1">
                <span class="block text-sm font-semibold text-slate-900">Daftar Peserta</span>
                <span class="mt-0.5 block text-xs text-slate-500">{{ $event->registrations->count() }} Peserta Terdaftar</span>
            </span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0 text-slate-300 transition group-hover:text-indigo-500" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" />
            </svg>
        </a>

        <!-- Leaderboard -->
        @if($event->registrations->isNotEmpty())
        <a href="{{ route('jury.leaderboard', $event) }}" class="group flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-md">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 transition group-hover:bg-amber-100">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8m-4-4v4m-5-9H4a2 2 0 0 1-2-2V8h4m14 4h-3m3 0a2 2 0 0 1-2 2h-1M8 8h8m-8 0a4 4 0 0 0 8 0M6 8V5h12v3" />
                </svg>
            </span>
            <span class="min-w-0 flex-1">
                <span class="block text-sm font-semibold text-slate-900">Leaderboard</span>
                <span class="mt-0.5 block text-xs text-slate-500">Lihat Peringkat Peserta</span>
            </span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0 text-slate-300 transition group-hover:text-amber-500" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" />
            </svg>
        </a>
        @endif
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('events.index') }}" class="btn-premium btn-premium-outline">Kembali</a>
    </div>
</div>

<!-- Banner Modal -->
@if($event->banner)
<div id="bannerModal" style="display: none;" class="fixed inset-0 z-[9999] items-center justify-center bg-slate-950/90 p-4 sm:p-8" onclick="closeBannerModal()">
    <div class="relative flex max-h-[90vh] max-w-[90%] flex-col items-center">
        <button type="button" onclick="closeBannerModal()" class="absolute -top-12 right-0 flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-700 shadow-lg transition hover:bg-slate-100" aria-label="Tutup banner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
            </svg>
        </button>
        <img src="{{ Storage::url($event->banner) }}" alt="{{ $event->name }}" class="max-h-[80vh] max-w-full rounded-lg object-contain shadow-2xl">
        <div class="mt-4 text-center text-base font-medium text-white">
            {{ $event->name }}
        </div>
    </div>
</div>

<script>
    function openBannerModal() {
        document.getElementById('bannerModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeBannerModal() {
        document.getElementById('bannerModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBannerModal();
        }
    });

    // Hover effect on banner
    document.getElementById('eventBanner')?.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.02)';
        this.style.boxShadow = '0 8px 24px rgba(0,0,0,0.2)';
    });

    document.getElementById('eventBanner')?.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
        this.style.boxShadow = 'none';
    });
</script>
@endif

@if($event->juryFormTemplate)
@push('scripts')
<script>
    function showJuryFormPreview() {
        const fields = @json($event->juryFormTemplate->criteria_fields);
        const useDefaultScore = {{ $event->juryFormTemplate->use_default_score ? 'true' : 'false' }};

        let totalMax = 0;
        let previewHTML = `
        <div class="jury-preview-overlay fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/70 p-4" onclick="this.remove()">
            <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl sm:p-8" onclick="event.stopPropagation()">
                <div class="mb-6 flex items-start justify-between gap-4 border-b border-slate-200 pb-4">
                    <h2 class="text-lg font-semibold tracking-tight text-slate-900">Preview Form Penilaian Juri</h2>
                    <button type="button" onclick="this.closest('.jury-preview-overlay').remove()" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" /></svg>
                        Tutup
                    </button>
                </div>

                <div class="mb-6 rounded-xl border border-emerald-100 bg-emerald-50/50 p-5">
                    <h3 class="mb-1 text-sm font-semibold text-emerald-800">Kriteria Penilaian</h3>
                    <p class="mb-4 text-sm text-slate-500">Ini adalah form yang akan dilihat oleh juri saat memberikan penilaian</p>
    `;

        fields.forEach((field, index) => {
            if (field.type === 'score') {
                totalMax += field.max;
                previewHTML += `
                <div class="mb-4 border-b border-slate-100 pb-4">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-800">
                        ${field.label}
                        ${field.required ? '<span class="text-rose-600">*</span>' : ''}
                        <span class="font-semibold text-emerald-700">(Max: ${field.max} poin)</span>
                    </label>
                    ${field.description ? `<p class="mb-2 mt-1 text-xs text-slate-500">${field.description}</p>` : ''}
                    <input
                        type="number"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 disabled:bg-slate-50"
                        placeholder="0 - ${field.max}"
                        min="0"
                        max="${field.max}"
                        disabled
                    >
                </div>
            `;
            } else if (field.type === 'textarea') {
                previewHTML += `
                <div class="mb-4 border-b border-slate-100 pb-4">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-800">
                        ${field.label}
                        ${field.required ? '<span class="text-rose-600">*</span>' : ''}
                    </label>
                    ${field.description ? `<p class="mb-2 mt-1 text-xs text-slate-500">${field.description}</p>` : ''}
                    <textarea
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 disabled:bg-slate-50"
                        rows="3"
                        disabled
                    ></textarea>
                </div>
            `;
            } else {
                previewHTML += `
                <div class="mb-4 border-b border-slate-100 pb-4">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-800">
                        ${field.label}
                        ${field.required ? '<span class="text-rose-600">*</span>' : ''}
                    </label>
                    ${field.description ? `<p class="mb-2 mt-1 text-xs text-slate-500">${field.description}</p>` : ''}
                    <input
                        type="text"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 disabled:bg-slate-50"
                        disabled
                    >
                </div>
            `;
            }
        });

        if (totalMax > 0) {
            previewHTML += `
            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                <strong class="text-base font-semibold text-emerald-800">Total Maksimal Poin: ${totalMax}</strong>
                <p class="mt-1.5 text-xs text-slate-500">
                    ${useDefaultScore ? 'Nilai akhir akan dihitung otomatis dari total kriteria penilaian' : 'Juri akan memasukkan nilai total manual'}
                </p>
            </div>
        `;
        }

        previewHTML += `
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-800">Feedback Publik</label>
                    <textarea
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 disabled:bg-slate-50"
                        rows="4"
                        placeholder="Feedback yang dapat dilihat oleh peserta..."
                        disabled
                    ></textarea>
                    <small class="mt-1 block text-xs text-slate-500">Feedback ini akan terlihat oleh peserta dan publik</small>
                </div>

                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-800">Evaluasi Private</label>
                    <textarea
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 disabled:bg-slate-50"
                        rows="4"
                        placeholder="Evaluasi internal untuk admin..."
                        disabled
                    ></textarea>
                    <small class="mt-1 block text-xs text-slate-500">Evaluasi ini hanya terlihat oleh admin dan juri</small>
                </div>

                <div class="border-t border-slate-200 pt-4 text-center">
                    <button type="button" onclick="this.closest('.jury-preview-overlay').remove()" class="btn btn-primary">
                        Tutup Preview
                    </button>
                </div>
            </div>
        </div>
    `;

        document.body.insertAdjacentHTML('beforeend', previewHTML);
    }
</script>
@endpush
@endif

@endsection
