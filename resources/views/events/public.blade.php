@extends('layouts.app')

@section('title', 'Semua Event — JURI.ON')

@php
use Illuminate\Support\Facades\Storage;

$eventStatus = function ($event) {
    $now = now();

    if ($now->gt($event->event_end)) {
        return ['label' => 'Selesai', 'class' => 'bg-slate-100 text-slate-600 ring-slate-500/20'];
    }

    if ($event->isRegistrationOpen()) {
        return ['label' => 'Pendaftaran dibuka', 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'];
    }

    if ($now->lt($event->registration_start)) {
        return ['label' => 'Segera dibuka', 'class' => 'bg-sky-50 text-sky-700 ring-sky-600/20'];
    }

    if ($event->getAvailableSlots() <= 0) {
        return ['label' => 'Kuota penuh', 'class' => 'bg-amber-50 text-amber-700 ring-amber-600/20'];
    }

    return ['label' => 'Pendaftaran ditutup', 'class' => 'bg-amber-50 text-amber-700 ring-amber-600/20'];
};
@endphp

@section('content')
    <div class="space-y-8">
        {{-- Page header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-600">Katalog Event</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">Semua Event</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                    Jelajahi event yang dipublikasikan oleh penyelenggara. Buka detail untuk melihat jadwal, kuota, dan status pendaftaran.
                </p>
            </div>

            <div class="inline-flex items-center gap-2 self-start rounded-full border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-medium text-slate-600 shadow-sm sm:self-auto">
                <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                {{ method_exists($events, 'total') ? $events->total() : $events->count() }} event
            </div>
        </div>

        @if($events->isEmpty())
            <div class="empty-state-premium">
                <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-slate-500">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 3v3m10-3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-semibold text-slate-950">Tidak ada event yang tersedia saat ini</h3>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">
                    Event baru akan tampil di sini setelah dibuka pendaftarannya. Silakan kembali lagi nanti untuk event terbaru.
                </p>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($events as $event)
                    @php $status = $eventStatus($event); @endphp

                    <article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg">
                        {{-- Banner --}}
                        <a href="{{ route('events.detail', $event) }}" class="relative block aspect-[16/10] overflow-hidden bg-slate-900">
                            @if($event->banner)
                                <img
                                    src="{{ Storage::url($event->banner) }}"
                                    alt="{{ $event->name }}"
                                    class="h-full w-full object-cover object-top transition duration-500 group-hover:scale-105">
                            @else
                                <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900"></div>
                                <div class="absolute inset-0 grid place-items-center text-white/70">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" class="h-12 w-12" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 21h8M5 7h14M5 7l-3 6h6L5 7Zm14 0-3 6h6l-3-6Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2 13a3 3 0 0 0 6 0M16 13a3 3 0 0 0 6 0" />
                                    </svg>
                                </div>
                            @endif

                            <div class="absolute inset-x-4 top-4 flex flex-wrap items-center gap-2">
                                @if($event->category)
                                    <span class="rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-semibold text-slate-700 shadow-sm backdrop-blur">{{ $event->category->name }}</span>
                                @endif
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold shadow-sm ring-1 ring-inset {{ $status['class'] }}">{{ $status['label'] }}</span>
                            </div>
                        </a>

                        {{-- Body --}}
                        <div class="flex flex-1 flex-col p-6">
                            <h2 class="text-lg font-semibold tracking-tight text-slate-950">
                                <a href="{{ route('events.detail', $event) }}" class="transition group-hover:text-indigo-700">{{ $event->name }}</a>
                            </h2>

                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ Str::limit($event->description, 120) }}</p>

                            <dl class="mt-5 grid grid-cols-2 gap-3 border-t border-slate-100 pt-5 text-sm">
                                <div>
                                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Penyelenggara</dt>
                                    <dd class="mt-1 truncate font-medium text-slate-700">{{ $event->organizer->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Harga</dt>
                                    <dd class="mt-1 font-medium text-slate-700">Rp {{ number_format($event->price, 0, ',', '.') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Jadwal</dt>
                                    <dd class="mt-1 font-medium text-slate-700">{{ $event->event_start->format('d M Y') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Kuota</dt>
                                    <dd class="mt-1 font-medium">
                                        @if($event->getAvailableSlots() > 0)
                                            <span class="text-emerald-700">{{ $event->getAvailableSlots() }} / {{ $event->quota }} tersisa</span>
                                        @else
                                            <span class="text-rose-600">Penuh</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>

                            <a href="{{ route('events.detail', $event) }}" class="btn btn-primary mt-6 w-full">
                                Lihat Detail Event
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" />
                                </svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $events->links() }}
            </div>
        @endif
    </div>
@endsection
