@extends('layouts.app')

@section('title', 'Dashboard Juri')

@section('content')
<div class="page-header-premium">
    <h1>Dashboard Juri</h1>
    <p>Selamat datang, <strong>{{ auth()->user()->name }}</strong>! Mulai berikan penilaian untuk peserta.</p>
</div>

<div class="stats-grid-premium">
    <div class="stat-card-premium">
        <div class="icon">📊</div>
        <h3>Total Penilaian</h3>
        <div class="number">{{ $totalScored }}</div>
    </div>

    <div class="stat-card-premium">
        <div class="icon">📅</div>
        <h3>Event Tersedia</h3>
        <div class="number">{{ $eventsToScore }}</div>
    </div>
</div>

<div class="premium-card">
    <div class="premium-card-header">
        <h2 class="premium-card-title">Penilaian Terbaru</h2>
    </div>

    @if($recentScores->isEmpty())
    <div class="empty-state-premium">
        <div class="empty-state-premium-icon">📝</div>
        <h3>Belum Ada Penilaian</h3>
        <p>Anda belum memberikan penilaian. Mulai dengan memilih event untuk dinilai.</p>
        <div style="margin-top: 1.5rem;">
            <a href="{{ route('jury.events') }}" class="btn-premium btn-premium-primary">
                Mulai Penilaian
            </a>
        </div>
    </div>
    @else
    <div style="overflow-x: auto;">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>Peserta</th>
                    <th>Event</th>
                    <th>Nilai</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentScores as $score)
                <tr>
                    <td><strong>{{ $score->registration->user->name }}</strong></td>
                    <td>{{ $score->registration->event->name }}</td>
                    <td>
                        <span style="font-size: 1.25rem; font-weight: 700; color: var(--success-color);">
                            {{ number_format($score->score, 2) }}
                        </span>
                    </td>
                    <td>{{ $score->created_at->format('d M Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<div style="margin-top: 2rem;">
    <a href="{{ route('jury.events') }}" class="btn-premium btn-premium-primary">
        Mulai Penilaian
    </a>
</div>
@endsection
