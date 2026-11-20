@extends('layouts.app')

@section('title', 'Pendaftaran Saya')

@section('content')
<div class="page-header-premium">
    <h1>Pendaftaran Saya</h1>
    <p>Daftar event yang Anda ikuti beserta status pendaftaran dan penilaiannya.</p>
</div>

@if($registrations->isEmpty())
<div class="empty-state-premium">
    <h3>Belum ada pendaftaran</h3>
    <p>Anda belum mendaftar event apapun. Telusuri event yang tersedia untuk memulai.</p>
    <a href="{{ route('events.public') }}" class="btn btn-primary mt-4">Browse Event</a>
</div>
@else
<div class="premium-card">
    <div class="overflow-x-auto">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>No. Pendaftaran</th>
                    <th>Tanggal Daftar</th>
                    <th>Status Pendaftaran</th>
                    <th>Status Pembayaran</th>
                    <th>Nilai</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registrations as $registration)
                <tr>
                    <td>
                        <div class="font-semibold text-slate-900">{{ $registration->event->name }}</div>
                        <div class="text-xs text-slate-500">{{ $registration->event->event_start->format('d M Y') }}</div>
                    </td>
                    <td>
                        <span class="font-mono text-xs text-slate-600 bg-slate-100 px-2 py-1 rounded-md">{{ $registration->registration_number }}</span>
                    </td>
                    <td>{{ $registration->registered_at->format('d M Y') }}</td>
                    <td>
                        @if($registration->status === 'verified')
                        <span class="badge badge-success">Terverifikasi</span>
                        @elseif($registration->status === 'pending')
                        <span class="badge badge-warning">Pending</span>
                        @elseif($registration->status === 'rejected')
                        <span class="badge badge-danger">Ditolak</span>
                        @else
                        <span class="badge badge-info">{{ ucfirst($registration->status) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($registration->payment)
                        @if($registration->payment->status === 'verified')
                        <span class="badge badge-success">Terverifikasi</span>
                        @elseif($registration->payment->status === 'pending')
                        <span class="badge badge-warning">Menunggu</span>
                        @else
                        <span class="badge badge-danger">Ditolak</span>
                        @endif
                        @else
                        <span class="badge badge-danger">Belum Upload</span>
                        @endif
                    </td>
                    <td>
                        @php
                        $avgScore = $registration->getAverageScore();
                        @endphp
                        @if($avgScore > 0)
                        <span class="text-base font-semibold text-emerald-700">{{ number_format($avgScore, 2) }}</span>
                        @else
                        <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('registrations.show', $registration) }}" class="btn btn-primary btn-sm">Detail</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-5 border-t border-slate-100 pt-4">
        {{ $registrations->links() }}
    </div>
</div>
@endif
@endsection
