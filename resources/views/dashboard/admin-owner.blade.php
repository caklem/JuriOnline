@extends('layouts.app')

@section('title', 'Dashboard Admin Owner')

@section('content')
<div class="page-header-premium">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Ringkasan platform</p>
            <h1>Dashboard Admin Owner</h1>
            <p>Selamat datang, <strong class="text-slate-700">{{ auth()->user()->name }}</strong>. Pantau seluruh event, organizer, pengguna, dan transaksi dari satu tempat.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="badge-premium badge-premium-info">{{ $activeUsers }} pengguna aktif</span>
            <a href="{{ route('owner.users.index') }}" class="btn-premium btn-premium-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1m17-1v-1a3 3 0 0 0-2.5-2.96M14 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm4 0a2.5 2.5 0 1 1-2.5-2.5" />
                </svg>
                Kelola Pengguna
            </a>
        </div>
    </div>
</div>

<div class="stats-grid-premium">
    <div class="stat-card-premium">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3>Total Event</h3>
                <div class="number">{{ $totalEvents }}</div>
                <p class="mt-1 text-xs text-slate-500">{{ $draftEvents }} masih draft</p>
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
                <h3>Event Aktif</h3>
                <div class="number">{{ $activeEvents }}</div>
                <p class="mt-1 text-xs text-slate-500">Sedang menerima peserta</p>
            </div>
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-100">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4m5.6 2A9.6 9.6 0 0 1 12 21a9.6 9.6 0 0 1-8.6-9A9.6 9.6 0 0 1 12 3a9.6 9.6 0 0 1 8.6 9Z" />
                </svg>
            </span>
        </div>
    </div>

    <div class="stat-card-premium">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3>Total Pendaftaran</h3>
                <div class="number">{{ $totalRegistrations }}</div>
                <p class="mt-1 text-xs text-slate-500">{{ $verifiedRegistrations }} terverifikasi</p>
            </div>
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-sky-50 text-sky-600 ring-1 ring-inset ring-sky-100">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1m17-1v-1a3 3 0 0 0-2.5-2.96M14 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm4 0a2.5 2.5 0 1 1-2.5-2.5" />
                </svg>
            </span>
        </div>
    </div>

    <div class="stat-card-premium">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3>Total Pendapatan</h3>
                <div class="number" style="font-size: 1.5rem;">Rp {{ number_format((float) $totalRevenue, 0, ',', '.') }}</div>
                <p class="mt-1 text-xs text-slate-500">Pembayaran terverifikasi</p>
            </div>
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-100">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5v-9Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h3" />
                </svg>
            </span>
        </div>
    </div>

    <div class="stat-card-premium">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3>Pembayaran Pending</h3>
                <div class="number">{{ $pendingPayments }}</div>
                <p class="mt-1 text-xs text-slate-500">Rp {{ number_format((float) $pendingPaymentAmount, 0, ',', '.') }} menunggu</p>
            </div>
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-inset ring-amber-100">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2.5 2.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </span>
        </div>
    </div>

    <div class="stat-card-premium">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3>Total Pengguna</h3>
                <div class="number">{{ $totalUsers }}</div>
                <p class="mt-1 text-xs text-slate-500">{{ $totalUsers - $activeUsers }} nonaktif</p>
            </div>
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1m17-1v-1a3 3 0 0 0-2.5-2.96M14 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm4 0a2.5 2.5 0 1 1-2.5-2.5" />
                </svg>
            </span>
        </div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="premium-card lg:col-span-2" style="margin-bottom: 0;">
        <div class="premium-card-header">
            <div>
                <h2 class="premium-card-title">Performa Organizer</h2>
                <p class="mt-1 text-xs text-slate-500">Peringkat penyelenggara berdasarkan pendapatan terverifikasi.</p>
            </div>
            <a href="{{ route('events.index') }}" class="btn-premium btn-premium-outline btn-sm">Semua event</a>
        </div>

        @if($organizerStats->isEmpty())
        <div class="empty-state-premium" style="padding: 2rem 1rem;">
            <h3>Belum Ada Organizer</h3>
            <p>Belum ada event yang dibuat, sehingga performa organizer belum dapat diukur.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Organizer</th>
                        <th class="text-right">Event</th>
                        <th class="text-right">Pendaftaran</th>
                        <th class="text-right">Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($organizerStats as $organizer)
                    <tr>
                        <td>
                            <strong class="font-semibold text-slate-900">{{ $organizer->name }}</strong>
                            <div class="text-xs text-slate-500">{{ $organizer->email }}</div>
                        </td>
                        <td class="text-right text-slate-600">{{ $organizer->events_count }}</td>
                        <td class="text-right text-slate-600">{{ $organizer->registrations_count }}</td>
                        <td class="text-right font-semibold text-slate-900">Rp {{ number_format((float) $organizer->revenue, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="premium-card" style="margin-bottom: 0;">
        <div class="premium-card-header">
            <div>
                <h2 class="premium-card-title">Distribusi Role</h2>
                <p class="mt-1 text-xs text-slate-500">Komposisi pengguna per role.</p>
            </div>
        </div>

        @php $maxRoleCount = max(1, (int) $roleDistribution->max('users_count')); @endphp
        <div class="space-y-4">
            @foreach($roleDistribution as $role)
            <div>
                <div class="mb-1 flex items-center justify-between text-xs">
                    <span class="font-semibold text-slate-700">{{ $role->display_name }}</span>
                    <span class="text-slate-500">{{ $role->active_users_count }} aktif / {{ $role->users_count }} total</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-indigo-500" style="width: {{ round(($role->users_count / $maxRoleCount) * 100) }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="premium-card" style="margin-bottom: 0;">
        <div class="premium-card-header">
            <div>
                <h2 class="premium-card-title">Tren Pendaftaran</h2>
                <p class="mt-1 text-xs text-slate-500">Jumlah pendaftaran enam bulan terakhir.</p>
            </div>
        </div>

        @php $maxTrend = max(1, (int) $registrationTrend->max('count')); @endphp
        <div class="space-y-3">
            @foreach($registrationTrend as $month)
            <div class="flex items-center gap-3">
                <span class="w-20 shrink-0 text-xs font-medium text-slate-500">{{ $month['label'] }}</span>
                <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ round(($month['count'] / $maxTrend) * 100) }}%"></div>
                </div>
                <span class="w-8 shrink-0 text-right text-xs font-semibold text-slate-700">{{ $month['count'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="premium-card" style="margin-bottom: 0;">
        <div class="premium-card-header">
            <div>
                <h2 class="premium-card-title">Event Terpopuler</h2>
                <p class="mt-1 text-xs text-slate-500">Event dengan pendaftar terbanyak.</p>
            </div>
        </div>

        @if($topEvents->isEmpty())
        <div class="empty-state-premium" style="padding: 2rem 1rem;">
            <h3>Belum Ada Event</h3>
            <p>Belum ada event yang menerima pendaftaran.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th class="text-right">Pendaftar</th>
                        <th class="text-right">Terverifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topEvents as $event)
                    <tr>
                        <td>
                            <strong class="font-semibold text-slate-900">{{ $event->name }}</strong>
                            <div class="text-xs text-slate-500">{{ $event->category?->name ?? 'Tanpa kategori' }} &middot; kuota {{ $event->quota }}</div>
                        </td>
                        <td class="text-right text-slate-600">{{ $event->registrations_count }}</td>
                        <td class="text-right text-slate-600">{{ $event->verified_registrations_count }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="premium-card" style="margin-bottom: 0;">
        <div class="premium-card-header">
            <div>
                <h2 class="premium-card-title">Event Terbaru</h2>
                <p class="mt-1 text-xs text-slate-500">Event yang paling baru dibuat di platform.</p>
            </div>
            <a href="{{ route('events.index') }}" class="btn-premium btn-premium-outline btn-sm">Semua event</a>
        </div>

        @if($recentEvents->isEmpty())
        <div class="empty-state-premium" style="padding: 2rem 1rem;">
            <div class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 3v3m8-3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                </svg>
            </div>
            <h3>Tidak Ada Event</h3>
            <p>Belum ada event yang dibuat.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Penyelenggara</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentEvents as $event)
                    <tr>
                        <td><strong class="font-semibold text-slate-900">{{ $event->name }}</strong></td>
                        <td class="text-slate-600">{{ $event->organizer->name }}</td>
                        <td>
                            @if($event->status === 'active')
                            <span class="badge-premium badge-premium-success">Aktif</span>
                            @elseif($event->status === 'draft')
                            <span class="badge-premium badge-premium-warning">Draft</span>
                            @else
                            <span class="badge-premium badge-premium-info">{{ $event->status }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="premium-card" style="margin-bottom: 0;">
        <div class="premium-card-header">
            <div>
                <h2 class="premium-card-title">Pembayaran Terbaru</h2>
                <p class="mt-1 text-xs text-slate-500">Aktivitas pembayaran terakhir yang masuk.</p>
            </div>
            <a href="{{ route('payments.all') }}" class="btn-premium btn-premium-outline btn-sm">Semua pembayaran</a>
        </div>

        @if($recentPayments->isEmpty())
        <div class="empty-state-premium" style="padding: 2rem 1rem;">
            <div class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5v-9Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h3" />
                </svg>
            </div>
            <h3>Tidak Ada Pembayaran</h3>
            <p>Belum ada pembayaran yang masuk.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Peserta</th>
                        <th>Event</th>
                        <th class="text-right">Jumlah</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPayments as $payment)
                    <tr>
                        <td><strong class="font-semibold text-slate-900">{{ $payment->registration->user->name }}</strong></td>
                        <td class="text-slate-600">{{ $payment->registration->event->name }}</td>
                        <td class="text-right font-semibold text-slate-900">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                        <td>
                            @if($payment->status === 'verified')
                            <span class="badge-premium badge-premium-success">Terverifikasi</span>
                            @elseif($payment->status === 'pending')
                            <span class="badge-premium badge-premium-warning">Pending</span>
                            @else
                            <span class="badge-premium badge-premium-danger">Ditolak</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <a href="{{ route('owner.users.index') }}" class="btn-premium btn-premium-primary">
        Manajemen Pengguna
    </a>
    <a href="{{ route('events.index') }}" class="btn-premium btn-premium-outline">
        Lihat Semua Event
    </a>
    <a href="{{ route('payments.all') }}" class="btn-premium btn-premium-info">
        Semua Pembayaran
    </a>
    <a href="{{ route('payments.pending') }}" class="btn-premium btn-premium-warning">
        Verifikasi Pembayaran
    </a>
    <a href="{{ route('jury.all-scores') }}" class="btn-premium btn-premium-success">
        Semua Penilaian
    </a>
</div>
@endsection
