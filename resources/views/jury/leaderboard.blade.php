@extends('layouts.app')

@section('title', 'Leaderboard - ' . $event->title)

@section('content')
<div class="card">
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('jury.events') }}" style="color: #3498db; text-decoration: none;">
            ← Kembali ke Daftar Event
        </a>
    </div>

    <h1 style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;">🏆 Leaderboard</h1>
    <h2 style="font-size: 1.5rem; color: #666; margin-bottom: 1rem;">{{ $event->title }}</h2>

    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
        <a href="{{ route('jury.participants', $event) }}" class="btn btn-primary">
            Kembali ke Daftar Peserta
        </a>
    </div>
</div>

@if($registrations->count() > 0)
<div style="display: grid; gap: 1rem;">
    @foreach($registrations as $index => $registration)
    <div class="card" style="position: relative; {{ $index < 3 ? 'border: 2px solid ' . ($index === 0 ? '#FFD700' : ($index === 1 ? '#C0C0C0' : '#CD7F32')) : '' }}">
        @if($index < 3)
            <div style="position: absolute; top: -15px; right: 20px; background: {{ $index === 0 ? '#FFD700' : ($index === 1 ? '#C0C0C0' : '#CD7F32') }}; color: white; padding: 0.5rem 1rem; border-radius: 20px; font-weight: bold; font-size: 1.25rem;">
            {{ $index === 0 ? '🥇' : ($index === 1 ? '🥈' : '🥉') }}
            #{{ $index + 1 }}
    </div>
    @else
    <div style="position: absolute; top: 10px; right: 20px; font-size: 1.5rem; font-weight: bold; color: #999;">
        #{{ $index + 1 }}
    </div>
    @endif

    <div style="padding-right: 80px;">
        <h3 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 0.5rem;">
            {{ $registration->user->name }}
        </h3>
        <p style="color: #666; margin-bottom: 1rem;">{{ $registration->user->email }}</p>

        <div style="display: flex; gap: 2rem; align-items: center; margin-bottom: 1rem;">
            <div style="text-align: center; padding: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; color: white; min-width: 120px;">
                <div style="font-size: 0.875rem; margin-bottom: 0.25rem;">Nilai Rata-rata</div>
                <div style="font-size: 2.5rem; font-weight: bold;">
                    {{ $registration->average_score ?? 0 }}
                </div>
            </div>

            <div style="flex: 1;">
                <strong style="display: block; margin-bottom: 0.5rem;">
                    Penilaian dari {{ $registration->juryScores->count() }} Juri:
                </strong>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    @foreach($registration->juryScores as $score)
                    <div style="background: #f8f9fa; padding: 0.5rem 1rem; border-radius: 5px; border-left: 3px solid #3498db;">
                        <strong>{{ $score->jury->name }}:</strong>
                        <span style="font-size: 1.125rem; font-weight: bold; color: #3498db;">{{ $score->score }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        @php
        $publicFeedbacks = $registration->juryScores->filter(fn($s) => !empty($s->public_feedback));
        @endphp

        @if($publicFeedbacks->count() > 0)
        <div style="margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 5px; border-left: 3px solid #27ae60;">
            <strong style="display: block; margin-bottom: 0.5rem;">💬 Feedback Publik:</strong>
            @foreach($publicFeedbacks as $score)
            <div style="margin-bottom: 0.75rem;">
                <strong style="color: #3498db;">{{ $score->jury->name }}:</strong>
                <p style="margin: 0.25rem 0 0 0; color: #666;">{{ $score->public_feedback }}</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endforeach
</div>

@if($registrations->isEmpty())
<div class="card" style="text-align: center; padding: 3rem;">
    <p style="font-size: 1.125rem; color: #999;">
        Belum ada penilaian untuk event ini.
    </p>
</div>
@endif
@else
<div class="card" style="text-align: center; padding: 3rem;">
    <p style="font-size: 1.125rem; color: #999;">
        Belum ada peserta terverifikasi untuk event ini.
    </p>
</div>
@endif
@endsection