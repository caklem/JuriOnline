@extends('layouts.app')

@section('title', 'Peserta - ' . $event->name)

@section('content')
<div class="card">
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('jury.events') }}" style="color: #3498db; text-decoration: none;">
            ← Kembali ke Daftar Event
        </a>
    </div>

    <h1 style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;">{{ $event->name }}</h1>
    <p style="color: #666; margin-bottom: 1rem;">Berikan penilaian untuk peserta yang telah terverifikasi</p>

    @if(isset($assignedFormTemplate) && $assignedFormTemplate)
    <div style="background: #e8f5e9; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border-left: 4px solid #27ae60;">
        <strong style="color: #27ae60;">📋 Form Penilaian Anda:</strong> 
        <span style="font-weight: 600; color: #2c3e50;">{{ $assignedFormTemplate->name ?? 'Form Juri' }}</span>
    </div>
    @endif

    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
        <a href="{{ route('jury.leaderboard', $event) }}" class="btn btn-success">
            Lihat Leaderboard
        </a>
    </div>
</div>

@if($registrations->count() > 0)
<div style="display: grid; gap: 1rem;">
    @foreach($registrations as $registration)
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; gap: 1.5rem;">
            <div style="flex: 1;">
                <h3 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 0.5rem;">
                    {{ $registration->user->name }}
                </h3>
                <p style="color: #666; margin-bottom: 0.5rem;">{{ $registration->user->email }}</p>

                <div style="margin: 1rem 0;">
                    <span class="badge badge-success">Terverifikasi</span>
                    <span class="badge badge-info">
                        Nilai Rata-rata: {{ $registration->getAverageScore() ?? 'Belum ada' }}
                    </span>
                </div>

                @if($registration->notes)
                <div style="background: #f8f9fa; padding: 0.75rem; border-radius: 5px; margin-top: 0.5rem;">
                    <strong>Catatan Peserta:</strong><br>
                    {{ $registration->notes }}
                </div>
                @endif
            </div>

            <div style="flex-shrink: 0;">
                @php
                $myScore = $registration->juryScores->first();
                @endphp

                @if($myScore)
                <div style="text-align: center; padding: 1rem; background: #d4edda; border-radius: 8px; margin-bottom: 1rem;">
                    <div style="font-size: 0.875rem; color: #155724; margin-bottom: 0.25rem;">Nilai Anda</div>
                    <div style="font-size: 2rem; font-weight: bold; color: #155724;">{{ $myScore->score }}</div>
                </div>
                <a href="{{ route('jury.edit-score', $myScore) }}" class="btn btn-warning" style="width: 100%;">
                    Edit Penilaian
                </a>
                @else
                <a href="{{ route('jury.score', $registration) }}" class="btn btn-primary" style="width: 100%;">
                    Berikan Penilaian
                </a>
                @endif
            </div>
        </div>

        @if($registration->juryScores->count() > 0)
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #ddd;">
            <strong style="display: block; margin-bottom: 0.5rem;">Penilaian dari Juri Lain:</strong>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                @foreach($registration->juryScores as $score)
                @if($score->jury_id !== auth()->id())
                <div style="background: #f8f9fa; padding: 0.5rem 1rem; border-radius: 5px;">
                    <strong>{{ $score->jury->name }}:</strong> {{ $score->score }}
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endforeach
</div>

<div style="margin-top: 1.5rem;">
    {{ $registrations->links() }}
</div>
@else
<div class="card" style="text-align: center; padding: 3rem;">
    <p style="font-size: 1.125rem; color: #999;">
        Belum ada peserta terverifikasi untuk event ini.
    </p>
</div>
@endif
@endsection