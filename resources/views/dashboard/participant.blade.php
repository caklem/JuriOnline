@extends('layouts.app')

@section('title', 'Dashboard Participant')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="page-header-premium">
    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Ruang kerja peserta</p>
    <h1>Dashboard Peserta</h1>
    <p>Selamat datang, <strong class="text-slate-700">{{ auth()->user()->name }}</strong>. Kelola pendaftaran dan lihat hasil penilaian Anda.</p>
</div>

<div class="premium-card">
    <div class="premium-card-header">
        <div>
            <h2 class="premium-card-title">Pendaftaran Saya</h2>
            <p class="mt-1 text-xs text-slate-500">Status pendaftaran, pembayaran, dan nilai rata-rata Anda.</p>
        </div>
        <a href="{{ route('events.public') }}" class="btn-premium btn-premium-outline btn-sm">Jelajahi event</a>
    </div>

    @if($myRegistrations->isEmpty())
    <div class="empty-state-premium">
        <div class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h4M7 3h10a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z" />
            </svg>
        </div>
        <h3>Belum Ada Pendaftaran</h3>
        <p>Anda belum mendaftar event apapun. Jelajahi event yang tersedia di bawah ini.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>No. Pendaftaran</th>
                    <th>Status</th>
                    <th>Pembayaran</th>
                    <th>Nilai Rata-rata</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($myRegistrations as $registration)
                <tr>
                    <td><strong class="font-semibold text-slate-900">{{ $registration->event->name }}</strong></td>
                    <td><code class="rounded-md bg-slate-100 px-2 py-1 font-mono text-xs text-slate-700">{{ $registration->registration_number }}</code></td>
                    <td>
                        @if($registration->status === 'verified')
                        <span class="badge-premium badge-premium-success">Terverifikasi</span>
                        @elseif($registration->status === 'pending')
                        <span class="badge-premium badge-premium-warning">Menunggu</span>
                        @elseif($registration->status === 'rejected')
                        <span class="badge-premium badge-premium-danger">Ditolak</span>
                        @else
                        <span class="badge-premium badge-premium-info">{{ $registration->status }}</span>
                        @endif
                    </td>
                    <td>
                        @if($registration->payment)
                        @if($registration->payment->status === 'verified')
                        <span class="badge-premium badge-premium-success">Terverifikasi</span>
                        @elseif($registration->payment->status === 'pending')
                        <span class="badge-premium badge-premium-warning">Menunggu Verifikasi</span>
                        @else
                        <span class="badge-premium badge-premium-danger">Ditolak</span>
                        @endif
                        @else
                        <span class="badge-premium badge-premium-danger">Belum Upload</span>
                        @endif
                    </td>
                    <td>
                        @php
                        $avgScore = $registration->getAverageScore();
                        @endphp
                        @if($avgScore > 0)
                        <span class="text-base font-semibold text-emerald-600">
                            {{ number_format($avgScore, 2) }}
                        </span>
                        @else
                        <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('registrations.show', $registration) }}" class="btn-premium btn-premium-outline btn-sm">
                            Detail
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<div class="premium-card">
    <div class="premium-card-header">
        <div>
            <h2 class="premium-card-title">Event Tersedia</h2>
            <p class="mt-1 text-xs text-slate-500">Event aktif yang masih membuka pendaftaran.</p>
        </div>
    </div>

    @if($availableEvents->isEmpty())
    <div class="empty-state-premium">
        <div class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 3v3m8-3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
            </svg>
        </div>
        <h3>Tidak Ada Event Tersedia</h3>
        <p>Tidak ada event yang tersedia saat ini. Coba lagi nanti.</p>
    </div>
    @else
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($availableEvents as $event)
        <div class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-indigo-200 hover:shadow-md">
            <div class="h-40 w-full overflow-hidden bg-slate-100">
                @if($event->banner)
                <img src="{{ Storage::url($event->banner) }}" alt="{{ $event->name }}" class="h-full w-full object-cover">
                @else
                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 text-slate-400">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" class="h-10 w-10" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5 9 11l4 4 2.5-2.5L20 17M5 5h14a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                    </svg>
                </div>
                @endif
            </div>

            <div class="flex flex-1 flex-col p-5">
                <h3 class="text-base font-semibold leading-6 text-slate-900">{{ $event->name }}</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">{{ Str::limit($event->description, 100) }}</p>

                <div class="mt-4 space-y-2 rounded-xl bg-slate-50 p-3.5 ring-1 ring-inset ring-slate-100">
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span class="text-slate-500">Harga</span>
                        <strong class="font-semibold text-slate-900">Rp {{ number_format($event->price, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span class="text-slate-500">Kuota</span>
                        <strong class="font-semibold text-slate-900">{{ $event->getAvailableSlots() }} / {{ $event->quota }} tersisa</strong>
                    </div>
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span class="text-slate-500">Pendaftaran sampai</span>
                        <strong class="font-semibold text-slate-900">{{ $event->registration_end->format('d M Y') }}</strong>
                    </div>
                </div>

                <a href="{{ route('events.detail', $event) }}" class="btn-premium btn-premium-primary mt-4 w-full">
                    Lihat Detail
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
