@extends('layouts.app')

@section('title', 'Daftar Peserta - ' . $event->name)

@section('content')
<div class="page-header-premium flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1>Daftar Peserta</h1>
        <p>{{ $event->name }}</p>
    </div>
    <a href="{{ route('events.show', $event) }}" class="btn btn-outline-secondary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12l7.5-7.5M3 12h18" />
        </svg>
        Kembali ke Event
    </a>
</div>

<div class="stats-grid-premium">
    <div class="stat-card-premium">
        <h3>Total Peserta</h3>
        <p class="number">{{ $event->registrations->count() }}</p>
    </div>

    <div class="stat-card-premium">
        <h3>Terverifikasi</h3>
        <p class="number !text-emerald-600">{{ $event->registrations->where('status', 'verified')->count() }}</p>
    </div>

    <div class="stat-card-premium">
        <h3>Pending</h3>
        <p class="number !text-amber-600">{{ $event->registrations->where('status', 'pending')->count() }}</p>
    </div>

    <div class="stat-card-premium">
        <h3>Ditolak</h3>
        <p class="number !text-rose-600">{{ $event->registrations->where('status', 'rejected')->count() }}</p>
    </div>
</div>

<div class="premium-card">
    @if($registrations->isEmpty())
    <div class="empty-state-premium">
        <h3>Belum ada peserta</h3>
        <p>Belum ada peserta yang mendaftar pada event ini.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="participants-table">
            <thead class="table-header-black">
                <tr>
                    <th>No. Pendaftaran</th>
                    <th>Nama Peserta</th>
                    <th>Status Pendaftaran</th>
                    <th>Status Pembayaran</th>
                    <th class="text-center">Nilai Rata-rata</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registrations as $index => $registration)
                <tr>
                    <td>
                        <span class="font-mono text-xs text-slate-600 bg-slate-100 px-2 py-1 rounded-md">{{ $registration->registration_number }}</span>
                    </td>
                    <td>
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">
                                {{ strtoupper(substr($registration->user->name, 0, 1)) }}
                            </span>
                            <div>
                                <div class="font-semibold text-slate-900">{{ $registration->user->name }}</div>
                                <div class="text-xs text-slate-500">{{ $registration->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($registration->status === 'verified')
                        <span class="badge badge-success">Terverifikasi</span>
                        @elseif($registration->status === 'pending')
                        <span class="badge badge-warning">Pending</span>
                        @elseif($registration->status === 'rejected')
                        <span class="badge badge-danger">Ditolak</span>
                        @else
                        <span class="badge badge-info">{{ $registration->status }}</span>
                        @endif
                    </td>
                    <td>
                        @if($registration->payment)
                        @if($registration->payment->status === 'verified')
                        <span class="badge badge-success">Terverifikasi</span>
                        @elseif($registration->payment->status === 'pending')
                        <span class="badge badge-warning">Pending</span>
                        @else
                        <span class="badge badge-danger">Ditolak</span>
                        @endif
                        @else
                        <span class="badge badge-danger">Belum Upload</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @php
                        $avgScore = $registration->getAverageScore();
                        @endphp
                        @if($avgScore > 0)
                        <span class="inline-flex items-center rounded-lg bg-amber-50 px-3 py-1.5 text-base font-semibold text-amber-700 ring-1 ring-amber-200">
                            {{ number_format($avgScore, 2) }}
                        </span>
                        @else
                        <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('registrations.show', $registration) }}" class="btn btn-primary btn-sm">
                            Detail
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($registrations->hasPages())
    <div class="mt-5 border-t border-slate-100 pt-4">
        {{ $registrations->links() }}
    </div>
    @endif
    @endif
</div>

<style>
    .participants-table thead.table-header-black,
    .participants-table thead.table-header-black th {
        background: #0f172a;
        color: #ffffff;
        border-color: rgba(255, 255, 255, 0.1);
    }

    .participants-table th,
    .participants-table td {
        text-align: left;
    }

    .participants-table th.text-center,
    .participants-table td.text-center {
        text-align: center;
    }

    @media (max-width: 768px) {
        .participants-table {
            min-width: 800px;
        }

        .premium-card {
            padding: 1rem;
        }
    }

    @media (max-width: 480px) {
        .participants-table {
            font-size: 0.85rem;
        }
    }
</style>
@endsection
