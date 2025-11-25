@extends('layouts.app')

@section('title', 'Event untuk Penilaian')

@section('content')
<div class="page-header-premium">
    <h1>⚖️ Event untuk Penilaian</h1>
    <p>Pilih event untuk memberikan penilaian kepada peserta</p>
</div>

@if($events->count() > 0)
<div style="display: grid; gap: 2rem;">
    @foreach($events as $event)
    <div class="premium-card">
        <div style="display: flex; justify-content: space-between; align-items: start; gap: 2rem; flex-wrap: wrap;">
            <div style="flex: 1;">
                <h2 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--dark);">
                    {{ $event->name }}
                </h2>

                <div style="display: flex; gap: 0.75rem; margin-bottom: 1rem; flex-wrap: wrap;">
                    <span class="badge-premium badge-premium-info">{{ $event->category->name }}</span>
                    <span class="badge-premium badge-premium-success">{{ ucfirst($event->status) }}</span>
                </div>

                <p style="color: var(--gray); margin-bottom: 1.5rem; line-height: 1.7;">
                    {{ Str::limit($event->description, 200) }}
                </p>

                @php
                $assignedForm = auth()->user()->getAssignedFormTemplate($event);
                @endphp
                @if($assignedForm)
                <div style="background: #f0fdf4; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border-left: 4px solid #16a34a; box-shadow: var(--shadow-sm);">
                    <strong style="color: #27ae60; display: block; margin-bottom: 0.25rem;">📋 Form Penilaian Anda:</strong> 
                    <span style="font-weight: 600; color: var(--dark);">{{ $assignedForm->name ?? 'Form Juri' }}</span>
                </div>
                @endif

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div style="padding: 1rem; background: var(--gray-light); border-radius: var(--radius-md);">
                        <strong style="display: block; margin-bottom: 0.5rem; color: var(--gray); font-size: 0.875rem;">Organizer:</strong>
                        <span style="font-weight: 600; color: var(--dark);">{{ $event->organizer->name }}</span>
                    </div>
                    <div style="padding: 1rem; background: var(--gray-light); border-radius: var(--radius-md);">
                        <strong style="display: block; margin-bottom: 0.5rem; color: var(--gray); font-size: 0.875rem;">Periode:</strong>
                        <span style="font-weight: 600; color: var(--dark);">{{ $event->registration_start->format('d M Y') }} - {{ $event->registration_end->format('d M Y') }}</span>
                    </div>
                    <div style="padding: 1rem; background: var(--gray-light); border-radius: var(--radius-md);">
                        <strong style="display: block; margin-bottom: 0.5rem; color: var(--gray); font-size: 0.875rem;">Total Peserta:</strong>
                        <span style="font-weight: 600; color: var(--dark);">{{ $event->registrations->count() }} peserta</span>
                        @php
                        $verifiedCount = $event->registrations->where('status', 'verified')->count();
                        @endphp
                        @if($verifiedCount > 0)
                        <br><span style="color: #27ae60; font-size: 0.875rem; margin-top: 0.25rem; display: block;">{{ $verifiedCount }} terverifikasi</span>
                        @else
                        <br><span style="color: var(--gray); font-size: 0.875rem; margin-top: 0.25rem; display: block;">Belum ada yang terverifikasi</span>
                        @endif
                    </div>
                </div>
            </div>

            @if($event->banner_url)
            <div style="flex-shrink: 0;">
                <img src="{{ Storage::url($event->banner_url) }}"
                    alt="{{ $event->name }}"
                    style="width: 250px; height: 180px; object-fit: cover; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
            </div>
            @endif
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap;">
            <a href="{{ route('jury.participants', $event) }}" class="btn-premium btn-premium-primary">
                👥 Lihat & Nilai Peserta
            </a>
            <a href="{{ route('jury.leaderboard', $event) }}" class="btn-premium btn-premium-success">
                🏆 Lihat Leaderboard
            </a>
            <a href="{{ route('events.show', $event) }}" class="btn-premium btn-premium-outline" style="border-color: var(--gray);">
                📄 Detail Event
            </a>
        </div>
    </div>
    @endforeach
</div>

<div style="margin-top: 2rem; display: flex; justify-content: center;">
    {{ $events->links() }}
</div>
@else
<div class="premium-card">
    <div class="empty-state-premium">
        <div class="empty-state-premium-icon">📭</div>
        <h3>Tidak Ada Event Aktif</h3>
        <p>Event yang statusnya aktif akan muncul di sini untuk dinilai.</p>
    </div>
</div>
@endif
@endsection
