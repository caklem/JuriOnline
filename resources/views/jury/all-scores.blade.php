@extends('layouts.app')

@section('title', 'Semua Penilaian')

@section('content')
<div class="page-header-premium">
    <h1>Semua Penilaian Juri</h1>
    <p>Monitor dan kelola semua penilaian dari juri.</p>
</div>

<div class="stats-grid-premium">
    <div class="stat-card-premium">
        <h3>Total Penilaian</h3>
        <p class="number">{{ $stats['total_scores'] }}</p>
    </div>
    <div class="stat-card-premium">
        <h3>Event Dinilai</h3>
        <p class="number">{{ $stats['total_events_scored'] }}</p>
    </div>
    <div class="stat-card-premium">
        <h3>Rata-rata Nilai</h3>
        <p class="number">{{ $stats['average_score'] }}</p>
    </div>
    <div class="stat-card-premium">
        <h3>Total Juri Aktif</h3>
        <p class="number">{{ $stats['total_juries'] }}</p>
    </div>
</div>

<div class="premium-card">
    <h2 class="mb-4 text-base font-semibold text-slate-900">Daftar Penilaian</h2>

    @if($scores->count() > 0)
    <div class="overflow-x-auto">
        <table class="table-premium">
            <thead class="table-header-black">
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
                        <a href="{{ route('events.show', $score->registration->event) }}" class="font-medium text-indigo-600 underline hover:text-indigo-700">
                            {{ $score->registration->event->name }}
                        </a>
                    </td>
                    <td>{{ $score->registration->user->name }}</td>
                    <td>{{ $score->jury->name }}</td>
                    <td>
                        <span class="badge badge-info">{{ $score->score }}/100</span>
                    </td>
                    <td>
                        @if($score->public_feedback)
                        <button
                            type="button"
                            class="btn btn-primary btn-sm"
                            onclick="showFeedback('{{ $score->id }}', 'public')">
                            Lihat Feedback
                        </button>
                        <div id="public-{{ $score->id }}" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #f8fafc; border-radius: 0.5rem; border-left: 3px solid #4f46e5;">
                            {{ $score->public_feedback }}
                        </div>
                        @else
                        <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td>
                        @if($score->private_evaluation)
                        <button
                            type="button"
                            class="btn btn-warning btn-sm"
                            onclick="showFeedback('{{ $score->id }}', 'private')">
                            Lihat Evaluasi
                        </button>
                        <div id="private-{{ $score->id }}" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: #fffbeb; border-radius: 0.5rem; border-left: 3px solid #d97706;">
                            {{ $score->private_evaluation }}
                        </div>
                        @else
                        <span class="text-slate-400">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-5 border-t border-slate-100 pt-4">
        {{ $scores->links() }}
    </div>
    @else
    <div class="empty-state-premium">
        <h3>Belum ada penilaian</h3>
        <p>Penilaian dari juri akan muncul di sini setelah dikirim.</p>
    </div>
    @endif
</div>

@push('styles')
<style>
    .table-premium thead.table-header-black,
    .table-premium thead.table-header-black th {
        background: #0f172a;
        color: #ffffff;
        border-color: rgba(255, 255, 255, 0.1);
    }
</style>
@endpush

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
