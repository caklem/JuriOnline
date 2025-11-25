@extends('layouts.app')

@section('title', 'Dashboard Organizer')

@section('content')
<div class="page-header-premium">
    <h1>Dashboard Organizer</h1>
    <p>Selamat datang, <strong>{{ auth()->user()->name }}</strong>! Kelola event Anda dari sini.</p>
</div>

<div class="stats-grid-premium">
    <div class="stat-card-premium">
        <div class="icon">📅</div>
        <h3>Total Event</h3>
        <div class="number">{{ $myEvents }}</div>
    </div>

    <div class="stat-card-premium">
        <div class="icon">✅</div>
        <h3>Event Aktif</h3>
        <div class="number">{{ $activeEvents }}</div>
    </div>

    <div class="stat-card-premium">
        <div class="icon">👥</div>
        <h3>Total Peserta</h3>
        <div class="number">{{ $totalParticipants }}</div>
    </div>

    <div class="stat-card-premium">
        <div class="icon">⏳</div>
        <h3>Pembayaran Pending</h3>
        <div class="number">{{ $pendingPayments }}</div>
    </div>
</div>

<div class="premium-card">
    <div class="premium-card-header">
        <h2 class="premium-card-title">Event Saya</h2>
        <a href="{{ route('events.create') }}" class="btn-premium btn-premium-success">
            Buat Event Baru
        </a>
    </div>

    @if($events->isEmpty())
    <div class="empty-state-premium">
        <div class="empty-state-premium-icon">📭</div>
        <h3>Belum Ada Event</h3>
        <p>Anda belum membuat event apapun. Mulai dengan membuat event pertama Anda.</p>
        <div style="margin-top: 1.5rem;">
            <a href="{{ route('events.create') }}" class="btn-premium btn-premium-primary">
                Buat Event Pertama
            </a>
        </div>
    </div>
    @else
    <div style="overflow-x: auto;">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>Nama Event</th>
                    <th>Status</th>
                    <th>Peserta</th>
                    <th>Periode Pendaftaran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $event)
                <tr>
                    <td><strong>{{ $event->name }}</strong></td>
                    <td>
                        @if($event->status === 'active')
                        <span class="badge-premium badge-premium-success">Aktif</span>
                        @elseif($event->status === 'draft')
                        <span class="badge-premium badge-premium-warning">Draft</span>
                        @elseif($event->status === 'closed')
                        <span class="badge-premium badge-premium-danger">Ditutup</span>
                        @else
                        <span class="badge-premium badge-premium-info">{{ $event->status }}</span>
                        @endif
                    </td>
                    <td>{{ $event->registrations_count }} / {{ $event->quota }}</td>
                    <td>{{ $event->registration_start->format('d M') }} - {{ $event->registration_end->format('d M Y') }}</td>
                    <td>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <a href="{{ route('events.show', $event) }}" class="btn-premium btn-premium-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                Detail
                            </a>
                            <a href="{{ route('events.edit', $event) }}" class="btn-premium btn-premium-warning" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                Edit
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
