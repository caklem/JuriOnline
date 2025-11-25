@extends('layouts.app')

@section('title', 'Dashboard Admin Owner')

@section('content')
<div class="page-header-premium">
    <h1>Dashboard Admin Owner</h1>
    <p>Selamat datang, <strong>{{ auth()->user()->name }}</strong>! Anda memiliki akses penuh untuk monitoring semua event dan transaksi.</p>
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
        <div class="icon">👥</div>
        <h3>Total Pendaftaran</h3>
        <div class="number">{{ $totalRegistrations }}</div>
    </div>

    <div class="stat-card-premium">
        <div class="icon">💰</div>
        <h3>Total Pendapatan</h3>
        <div class="number" style="font-size: 1.75rem;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
    </div>

    <div class="stat-card-premium">
        <div class="icon">⏳</div>
        <h3>Pembayaran Pending</h3>
        <div class="number">{{ $pendingPayments }}</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 2rem;">
    <div class="premium-card">
        <div class="premium-card-header">
            <h2 class="premium-card-title">Event Terbaru</h2>
        </div>

        @if($recentEvents->isEmpty())
        <div class="empty-state-premium" style="padding: 2rem;">
            <div class="empty-state-premium-icon">📭</div>
            <h3>Tidak Ada Event</h3>
            <p>Belum ada event yang dibuat.</p>
        </div>
        @else
        <div style="overflow-x: auto;">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Organizer</th>
                        <th>Status</th>
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
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="premium-card">
        <div class="premium-card-header">
            <h2 class="premium-card-title">Pembayaran Terbaru</h2>
        </div>

        @if($recentPayments->isEmpty())
        <div class="empty-state-premium" style="padding: 2rem;">
            <div class="empty-state-premium-icon">💳</div>
            <h3>Tidak Ada Pembayaran</h3>
            <p>Belum ada pembayaran yang masuk.</p>
        </div>
        @else
        <div style="overflow-x: auto;">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Peserta</th>
                        <th>Event</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPayments as $payment)
                    <tr>
                        <td><strong>{{ $payment->registration->user->name }}</strong></td>
                        <td>{{ $payment->registration->event->name }}</td>
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
