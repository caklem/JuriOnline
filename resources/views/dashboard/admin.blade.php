@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="page-header-premium">
    <h1>Dashboard Admin</h1>
    <p>Selamat datang, <strong>{{ auth()->user()->name }}</strong>! Kelola semua event dan sistem dari sini.</p>
</div>

<div class="stats-grid-premium">
    <div class="stat-card-premium">
        <div class="icon">📅</div>
        <h3>Total Event</h3>
        <div class="number">{{ $totalEvents }}</div>
    </div>

    <div class="stat-card-premium">
        <div class="icon">✅</div>
        <h3>Event Aktif</h3>
        <div class="number">{{ $activeEvents }}</div>
    </div>

    <div class="stat-card-premium">
        <div class="icon">⏳</div>
        <h3>Pembayaran Pending</h3>
        <div class="number">{{ $pendingPayments }}</div>
    </div>

    <div class="stat-card-premium">
        <div class="icon">👥</div>
        <h3>Total Pendaftaran</h3>
        <div class="number">{{ $totalRegistrations }}</div>
    </div>
</div>

    <div class="premium-card">
        <div class="premium-card-header">
            <h2 class="premium-card-title">Event Terbaru</h2>
        </div>

    @if($recentEvents->isEmpty())
    <div class="empty-state-premium">
        <div class="empty-state-premium-icon">📭</div>
        <h3>Belum Ada Event</h3>
        <p>Belum ada event yang dibuat. Mulai dengan membuat event pertama Anda.</p>
    </div>
    @else
    <div style="overflow-x: auto;">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Organizer</th>
                    <th>Status</th>
                    <th>Peserta</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentEvents as $event)
                <tr>
                    <td><strong>{{ $event->name }}</strong></td>
                    <td>{{ $event->organizer->name }}</td>
                    <td>
                        @if($event->status === 'active')
                        <span class="badge-premium badge-premium-success">Aktif</span>
                        @elseif($event->status === 'draft')
                        <span class="badge-premium badge-premium-warning">Draft</span>
                        @else
                        <span class="badge-premium badge-premium-info">{{ $event->status }}</span>
                        @endif
                    </td>
                    <td>{{ $event->registered_count }} / {{ $event->quota }}</td>
                    <td>
                        <a href="{{ route('events.show', $event) }}" class="btn-premium btn-premium-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
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

<div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 2rem;">
    <a href="{{ route('events.index') }}" class="btn-premium btn-premium-primary">
        Lihat Semua Event
    </a>
    <a href="{{ route('payments.pending') }}" class="btn-premium btn-premium-warning">
        Verifikasi Pembayaran
    </a>
    <a href="{{ route('jury.events') }}" class="btn-premium btn-premium-success">
        Kelola Penilaian
    </a>
    <a href="{{ route('jury.all-scores') }}" class="btn-premium btn-premium-info">
        Lihat Semua Penilaian
    </a>
</div>
@endsection
