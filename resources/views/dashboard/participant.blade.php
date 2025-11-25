@extends('layouts.app')

@section('title', 'Dashboard Participant')

@section('content')
<div class="page-header-premium">
    <h1>Dashboard Peserta</h1>
    <p>Selamat datang, <strong>{{ auth()->user()->name }}</strong>! Kelola pendaftaran dan lihat hasil penilaian Anda.</p>
</div>

<div class="premium-card">
    <div class="premium-card-header">
        <h2 class="premium-card-title">Pendaftaran Saya</h2>
    </div>

    @if($myRegistrations->isEmpty())
    <div class="empty-state-premium">
        <div class="empty-state-premium-icon">📭</div>
        <h3>Belum Ada Pendaftaran</h3>
        <p>Anda belum mendaftar event apapun. Jelajahi event yang tersedia di bawah ini.</p>
    </div>
    @else
    <div style="overflow-x: auto;">
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
                    <td><strong>{{ $registration->event->name }}</strong></td>
                    <td><code style="background: var(--gray-light); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem;">{{ $registration->registration_number }}</code></td>
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
                        <span style="font-size: 1.1rem; font-weight: 700; color: var(--success-color);">
                            {{ number_format($avgScore, 2) }}
                        </span>
                        @else
                        <span style="color: var(--gray);">-</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('registrations.show', $registration) }}" class="btn-premium btn-premium-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
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
        <h2 class="premium-card-title">Event Tersedia</h2>
    </div>

    @if($availableEvents->isEmpty())
    <div class="empty-state-premium">
        <div class="empty-state-premium-icon">📅</div>
        <h3>Tidak Ada Event Tersedia</h3>
        <p>Tidak ada event yang tersedia saat ini. Coba lagi nanti.</p>
    </div>
    @else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
        @foreach($availableEvents as $event)
        <div class="premium-card" style="margin: 0; padding: 0; overflow: hidden;">
            @if($event->banner)
            <img src="{{ Storage::url($event->banner) }}" alt="{{ $event->name }}" style="width: 100%; height: 200px; object-fit: cover;">
            @endif
            <div style="padding: 1.5rem;">
                <h3 style="margin: 0 0 0.75rem 0; font-size: 1.25rem; font-weight: 700; color: var(--dark);">
                    {{ $event->name }}
                </h3>
                <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 1.25rem; line-height: 1.6;">
                    {{ Str::limit($event->description, 100) }}
                </p>

                <div style="margin-bottom: 1.25rem; padding: 1rem; background: var(--gray-light); border-radius: var(--radius-md);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="color: var(--gray); font-size: 0.875rem;">Harga:</span>
                        <strong style="color: var(--dark);">Rp {{ number_format($event->price, 0, ',', '.') }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="color: var(--gray); font-size: 0.875rem;">Kuota:</span>
                        <strong style="color: var(--dark);">{{ $event->getAvailableSlots() }} / {{ $event->quota }} tersisa</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--gray); font-size: 0.875rem;">Pendaftaran sampai:</span>
                        <strong style="color: var(--dark); font-size: 0.875rem;">{{ $event->registration_end->format('d M Y') }}</strong>
                    </div>
                </div>

                <a href="{{ route('events.detail', $event) }}" class="btn-premium btn-premium-primary" style="width: 100%; justify-content: center;">
                    Lihat Detail
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
