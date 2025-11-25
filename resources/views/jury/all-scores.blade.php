@extends('layouts.app')

@section('title', 'Semua Penilaian')

@section('content')
<div class="card">
    <h1 style="font-size: 2rem; font-weight: bold; margin-bottom: 1rem;">Semua Penilaian Juri</h1>
    <p style="color: #666; margin-bottom: 2rem;">Monitor dan kelola semua penilaian dari juri</p>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Penilaian</h3>
            <div class="number">{{ $stats['total_scores'] }}</div>
        </div>
        <div class="stat-card">
            <h3>Event Dinilai</h3>
            <div class="number">{{ $stats['total_events_scored'] }}</div>
        </div>
        <div class="stat-card">
            <h3>Rata-rata Nilai</h3>
            <div class="number">{{ $stats['average_score'] }}</div>
        </div>
        <div class="stat-card">
            <h3>Total Juri Aktif</h3>
            <div class="number">{{ $stats['total_juries'] }}</div>
        </div>
    </div>
</div>

<div class="card">
    <h2 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 1.5rem;">Daftar Penilaian</h2>

    @if($scores->count() > 0)
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Event</th>
                    <th>Peserta</th>
                    <th>Juri</th>
                    <th>Nilai</th>
                    <th>Feedback Publik</th>
                    <th>Evaluasi Private</th>
                </tr>
            </thead>
            <tbody>
                @foreach($scores as $score)
                <tr>
                    <td>{{ $score->created_at->format('d M Y H:i') }}</td>
                    <td>
                        <a href="{{ route('events.show', $score->registration->event) }}" style="color: #3498db; text-decoration: underline;">
                            {{ $score->registration->event->title }}
                        </a>
                    </td>
                    <td>{{ $score->registration->user->name }}</td>
                    <td>{{ $score->jury->name }}</td>
                    <td>
                        <span class="badge badge-info" style="font-size: 1rem;">
                            {{ $score->score }}/100
                        </span>
                    </td>
                    <td>
                        @if($score->public_feedback)
                        <button
                            type="button"
                            class="btn btn-primary"
                            style="padding: 0.5rem 1rem; font-size: 0.875rem;"
                            onclick="showFeedback('{{ $score->id }}', 'public')">
                            Lihat Feedback
                        </button>
                        <div id="public-{{ $score->id }}" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f8f9fa; border-radius: 5px; border-left: 3px solid #3498db;">
                            {{ $score->public_feedback }}
                        </div>
                        @else
                        <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td>
                        @if($score->private_evaluation)
                        <button
                            type="button"
                            class="btn btn-warning"
                            style="padding: 0.5rem 1rem; font-size: 0.875rem;"
                            onclick="showFeedback('{{ $score->id }}', 'private')">
                            Lihat Evaluasi
                        </button>
                        <div id="private-{{ $score->id }}" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #fff3cd; border-radius: 5px; border-left: 3px solid #f39c12;">
                            {{ $score->private_evaluation }}
                        </div>
                        @else
                        <span style="color: #999;">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $scores->links() }}
    </div>
    @else
    <div style="text-align: center; padding: 3rem; color: #999;">
        <p style="font-size: 1.125rem;">Belum ada penilaian.</p>
    </div>
    @endif
</div>

@push('scripts')
<script>
    function showFeedback(scoreId, type) {
        const element = document.getElementById(`${type}-${scoreId}`);
        if (element.style.display === 'none') {
            element.style.display = 'block';
        } else {
            element.style.display = 'none';
        }
    }
</script>
@endpush

@endsection