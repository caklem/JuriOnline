@extends('layouts.app')

@section('title', auth()->user()->canManageAllEvents() ? 'Semua Event' : 'Event Saya')

@section('content')
<div class="page-header-premium">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1>{{ auth()->user()->canManageAllEvents() ? 'Semua Event' : 'Event Saya' }}</h1>
            <p>{{ auth()->user()->canManageAllEvents() ? 'Kelola seluruh event dan penyelenggara dari sini.' : 'Kelola event yang Anda selenggarakan.' }}</p>
        </div>
        @if(auth()->user()->canManageEvents())
        <a href="{{ route('events.create') }}" class="btn-premium btn-premium-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
            </svg>
            Buat Event Baru
        </a>
        @endif
    </div>
</div>

@if($events->isEmpty())
<div class="premium-card">
    <div class="empty-state-premium">
        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 3v3m8-3v3M4 8h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="m9.5 14 5 5m0-5-5 5" />
            </svg>
        </div>
        <h3>Belum Ada Event</h3>
        <p>Mulai dengan membuat event pertama Anda.</p>
        @if(auth()->user()->canManageEvents())
        <div class="mt-6">
            <a href="{{ route('events.create') }}" class="btn-premium btn-premium-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                </svg>
                Buat Event Pertama
            </a>
        </div>
        @endif
    </div>
</div>
@else
<div class="premium-card">
    <div class="premium-card-header">
        <h2 class="premium-card-title">Daftar Event</h2>
        <span class="badge-premium">{{ $events->total() }} event</span>
    </div>

    <div class="overflow-x-auto">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>Nama Event</th>
                    <th>Kategori</th>
                    <th>Penyelenggara</th>
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
                    <td>{{ $event->organizer->name }}</td>
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
                        <span class="whitespace-nowrap">{{ $event->registered_count }} / {{ $event->quota }}</span>
                        @if($event->getAvailableSlots() === 0)
                        <span class="badge-premium badge-premium-danger ml-2">Penuh</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap">{{ $event->registration_start->format('d M') }} - {{ $event->registration_end->format('d M Y') }}</td>
                    <td>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('events.show', $event) }}" class="btn-premium btn-premium-primary btn-sm">
                                Detail
                            </a>

                            @if(auth()->user()->canManageEvent($event))
                            <a href="{{ route('events.edit', $event) }}" class="btn-premium btn-premium-warning btn-sm">
                                Edit
                            </a>

                            @if($event->status === 'draft')
                            <form method="POST" action="{{ route('events.update-status', $event) }}" class="inline">
                                @csrf
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="btn-premium btn-premium-success btn-sm" onclick="return confirm('Aktifkan event ini?')">
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

    <div class="mt-8 flex justify-center">
        {{ $events->links() }}
    </div>
</div>
@endif
@endsection
