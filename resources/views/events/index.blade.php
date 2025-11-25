@extends('layouts.app')

@section('title', 'Event Saya')

@section('content')
<div class="page-header-premium">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1>Event Saya</h1>
            <p>Kelola semua event Anda dari sini</p>
        </div>
        @if(auth()->user()->isOrganizer() || auth()->user()->isAdmin())
        <a href="{{ route('events.create') }}" class="btn-premium btn-premium-success">
            ➕ Buat Event Baru
        </a>
        @endif
    </div>
</div>

@if($events->isEmpty())
<div class="premium-card">
    <div class="empty-state-premium">
        <div class="empty-state-premium-icon">📭</div>
        <h3>Belum Ada Event</h3>
        <p>Mulai dengan membuat event pertama Anda.</p>
        @if(auth()->user()->isOrganizer())
        <div style="margin-top: 1.5rem;">
            <a href="{{ route('events.create') }}" class="btn-premium btn-premium-primary">
                ➕ Buat Event Pertama
            </a>
        </div>
        @endif
    </div>
</div>
@else
<div class="premium-card">
    <div class="premium-card-header">
        <h2 class="premium-card-title">📋 Daftar Event</h2>
    </div>
    
    <div style="overflow-x: auto;">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>Nama Event</th>
                    <th>Kategori</th>
                    @if(!auth()->user()->isOrganizer())
                    <th>Organizer</th>
                    @endif
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
                    <td>{{ $event->category->name ?? '-' }}</td>
                    @if(!auth()->user()->isOrganizer())
                    <td>{{ $event->organizer->name }}</td>
                    @endif
                    <td>
                        @if($event->status === 'active')
                        <span class="badge-premium badge-premium-success">Aktif</span>
                        @elseif($event->status === 'draft')
                        <span class="badge-premium badge-premium-warning">Draft</span>
                        @elseif($event->status === 'closed')
                        <span class="badge-premium badge-premium-danger">Ditutup</span>
                        @else
                        <span class="badge-premium badge-premium-info">{{ ucfirst($event->status) }}</span>
                        @endif
                    </td>
                    <td>
                        {{ $event->registered_count }} / {{ $event->quota }}
                        @if($event->getAvailableSlots() === 0)
                        <span class="badge-premium badge-premium-danger" style="margin-left: 0.5rem;">Penuh</span>
                        @endif
                    </td>
                    <td>{{ $event->registration_start->format('d M') }} - {{ $event->registration_end->format('d M Y') }}</td>
                    <td>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <a href="{{ route('events.show', $event) }}" class="btn-premium btn-premium-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                Detail
                            </a>

                            @if(auth()->user()->isOrganizer() && $event->organizer_id === auth()->id() || auth()->user()->isAdmin() || auth()->user()->isAdminOwner())
                            <a href="{{ route('events.edit', $event) }}" class="btn-premium btn-premium-warning" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                Edit
                            </a>

                            @if($event->status === 'draft')
                            <form method="POST" action="{{ route('events.update-status', $event) }}" style="display: inline;">
                                @csrf
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="btn-premium btn-premium-success" style="padding: 0.5rem 1rem; font-size: 0.875rem;" onclick="return confirm('Aktifkan event ini?')">
                                    Aktifkan
                                </button>
                            </form>
                            @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 2rem; display: flex; justify-content: center;">
        {{ $events->links() }}
    </div>
</div>
@endif
@endsection
