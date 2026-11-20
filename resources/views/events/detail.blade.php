@extends('layouts.app')

@section('title', $event->name)

@php
use Illuminate\Support\Facades\Storage;

$eventStatusClass = match ($event->status) {
    'active' => 'badge-success',
    'cancelled' => 'badge-danger',
    'completed' => 'badge-info',
    default => '',
};

$availableSlots = $event->getAvailableSlots();
$filledPercent = $event->quota > 0
    ? (int) round(min(100, max(0, (($event->quota - $availableSlots) / $event->quota) * 100)))
    : 100;
@endphp

@section('content')
    <div class="space-y-6">
        {{-- Breadcrumb & back link --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <nav class="flex min-w-0 items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="transition hover:text-slate-900">Beranda</a>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 shrink-0 text-slate-300" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" />
                </svg>
                <a href="{{ route('events.public') }}" class="transition hover:text-slate-900">Semua Event</a>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 shrink-0 text-slate-300" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" />
                </svg>
                <span class="truncate font-medium text-slate-900">{{ Str::limit($event->name, 48) }}</span>
            </nav>

            <a href="{{ route('events.public') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-600 transition hover:text-slate-950">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m5 5-5-5 5-5" />
                </svg>
                Kembali ke daftar event
            </a>
        </div>

        {{-- Banner --}}
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-900">
            @if($event->banner)
                <img src="{{ Storage::url($event->banner) }}" alt="{{ $event->name }}" class="h-56 w-full object-cover object-top sm:h-72 lg:h-80">
            @else
                <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900"></div>
                <div class="relative grid h-56 w-full place-items-center text-white/70 sm:h-72 lg:h-80">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" class="h-16 w-16" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 21h8M5 7h14M5 7l-3 6h6L5 7Zm14 0-3 6h6l-3-6Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2 13a3 3 0 0 0 6 0M16 13a3 3 0 0 0 6 0" />
                    </svg>
                </div>
            @endif

            <div class="absolute inset-x-5 top-5 flex flex-wrap items-center gap-2">
                @if($event->category)
                    <span class="rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm backdrop-blur">{{ $event->category->name }}</span>
                @endif
                <span class="badge {{ $eventStatusClass }}">{{ ucfirst($event->status) }}</span>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3 lg:items-start">
            {{-- Main column --}}
            <div class="space-y-6 lg:col-span-2">
                {{-- Title & description --}}
                <div class="premium-card">
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">{{ $event->name }}</h1>
                    <p class="mt-2 text-sm text-slate-500">
                        Diselenggarakan oleh
                        <span class="font-semibold text-slate-700">{{ $event->organizer->name }}</span>
                    </p>

                    <div class="mt-6 border-t border-slate-100 pt-6">
                        <h2 class="text-base font-semibold text-slate-950">Deskripsi Event</h2>
                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $event->description }}</p>
                    </div>
                </div>

                {{-- Official email requirement --}}
                @if($event->require_official_email && $event->allowed_email_domains)
                    <div class="premium-card">
                        <div class="flex items-start gap-3 rounded-xl bg-indigo-50 p-4 ring-1 ring-inset ring-indigo-100">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-indigo-600 ring-1 ring-inset ring-indigo-100">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5v-9Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m3.5 6.5 8.5 6 8.5-6" />
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-sm font-semibold text-indigo-900">Persyaratan Email Resmi</h2>
                                <p class="mt-1 text-sm leading-6 text-indigo-800">
                                    Pendaftaran event ini <strong>HANYA untuk email resmi</strong> dengan domain berikut:
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach(array_map('trim', explode(',', $event->allowed_email_domains)) as $domain)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5 text-emerald-600" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                                    </svg>
                                    {{ $domain }}
                                </span>
                            @endforeach
                        </div>

                        @auth
                            @php
                                $userEmailDomain = substr(strrchr(auth()->user()->email, "@"), 1);
                                $allowedDomains = array_map('trim', explode(',', $event->allowed_email_domains));
                                $isEmailAllowed = in_array($userEmailDomain, $allowedDomains);
                            @endphp

                            @if($isEmailAllowed)
                                <div class="mt-4 flex items-start gap-2 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800 ring-1 ring-inset ring-emerald-200">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                                    </svg>
                                    <p><strong>Email Anda ({{ auth()->user()->email }}) memenuhi syarat.</strong></p>
                                </div>
                            @else
                                <div class="mt-4 flex items-start gap-2 rounded-xl bg-rose-50 p-3 text-sm text-rose-800 ring-1 ring-inset ring-rose-200">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 h-4 w-4 shrink-0 text-rose-600" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5m0 3h.01M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" />
                                    </svg>
                                    <div>
                                        <p><strong>Email Anda ({{ auth()->user()->email }}) tidak memenuhi syarat.</strong></p>
                                        <p class="mt-1 text-rose-700">Gunakan email resmi dari domain yang diizinkan untuk mendaftar.</p>
                                    </div>
                                </div>
                            @endif
                        @endauth
                    </div>
                @endif

                {{-- Registration panel --}}
                @auth
                    @if(auth()->user()->isParticipant() || !auth()->user()->role)
                        <div class="premium-card">
                            @if($userRegistration)
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h2 class="premium-card-title">Status Pendaftaran Anda</h2>
                                        <p class="mt-1 text-sm text-slate-500">
                                            Nomor pendaftaran
                                            <span class="font-semibold text-slate-700">{{ $userRegistration->registration_number }}</span>
                                        </p>
                                    </div>
                                </div>

                                <dl class="mt-6 grid gap-4 border-t border-slate-100 pt-5 text-sm sm:grid-cols-2">
                                    <div>
                                        <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Status Pendaftaran</dt>
                                        <dd class="mt-1.5">
                                            @if($userRegistration->status === 'verified')
                                                <span class="badge badge-success">Terverifikasi</span>
                                            @elseif($userRegistration->status === 'pending')
                                                <span class="badge badge-warning">Menunggu Verifikasi</span>
                                            @elseif($userRegistration->status === 'rejected')
                                                <span class="badge badge-danger">Ditolak</span>
                                            @endif
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Status Pembayaran</dt>
                                        <dd class="mt-1.5">
                                            @if($userRegistration->payment)
                                                @if($userRegistration->payment->status === 'verified')
                                                    <span class="badge badge-success">Terverifikasi</span>
                                                @elseif($userRegistration->payment->status === 'pending')
                                                    <span class="badge badge-warning">Menunggu Verifikasi</span>
                                                @else
                                                    <span class="badge badge-danger">Ditolak</span>
                                                @endif
                                            @else
                                                <span class="text-sm font-medium text-rose-600">Bukti pembayaran belum diupload</span>
                                            @endif
                                        </dd>
                                    </div>
                                </dl>

                                <div class="mt-6 flex flex-wrap gap-3">
                                    @if(!$userRegistration->payment)
                                        <a href="{{ route('payments.create', $userRegistration) }}" class="btn btn-warning">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-4 w-4" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4 4 4M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3" />
                                            </svg>
                                            Upload Bukti Pembayaran
                                        </a>
                                    @endif
                                    <a href="{{ route('registrations.show', $userRegistration) }}" class="btn btn-primary">Lihat Detail Pendaftaran</a>
                                </div>
                            @else
                                @if(!auth()->user()->role)
                                    <div class="alert alert-danger">
                                        <strong>Anda belum bisa mendaftar.</strong><br>
                                        Akun Anda belum memiliki role. Silakan logout dan login kembali, atau hubungi administrator.
                                    </div>
                                @elseif($event->isRegistrationOpen())
                                    <div>
                                        <h2 class="premium-card-title">Daftar Sekarang</h2>
                                        <p class="mt-1 text-sm leading-6 text-slate-600">Event ini masih membuka pendaftaran. Daftar sekarang sebelum kuota penuh.</p>
                                    </div>

                                    <form method="POST" action="{{ route('registrations.store', $event) }}" class="mt-6">
                                        @csrf

                                        @if($event->eventForm)
                                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                                                <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-950">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-4 w-4 text-indigo-600" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6m-7 4h8m-8 4h5m-5 4h8M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                                                    </svg>
                                                    Isi Form Pendaftaran
                                                </h3>

                                                <div class="mt-4">
                                                    @foreach($event->eventForm->form_fields as $index => $field)
                                                        <div class="form-group">
                                                            <label for="field_{{ $index }}" class="form-label">
                                                                {{ $field['label'] }}
                                                                @if($field['required'])
                                                                    <span class="text-rose-600">*</span>
                                                                @endif
                                                            </label>

                                                            @if(isset($field['description']) && $field['description'])
                                                                <p class="mb-2 mt-1 text-xs text-slate-500">{{ $field['description'] }}</p>
                                                            @endif

                                                            @if($field['type'] === 'textarea')
                                                                <textarea
                                                                    class="form-control"
                                                                    id="field_{{ $index }}"
                                                                    name="field_{{ $index }}"
                                                                    rows="4"
                                                                    {{ $field['required'] ? 'required' : '' }}></textarea>
                                                            @elseif($field['type'] === 'select')
                                                                <select
                                                                    class="form-control"
                                                                    id="field_{{ $index }}"
                                                                    name="field_{{ $index }}"
                                                                    {{ $field['required'] ? 'required' : '' }}>
                                                                    <option value="">Pilih...</option>
                                                                    @if(isset($field['options']))
                                                                        @foreach($field['options'] as $option)
                                                                            <option value="{{ $option }}">{{ $option }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            @else
                                                                <input
                                                                    type="{{ $field['type'] }}"
                                                                    class="form-control"
                                                                    id="field_{{ $index }}"
                                                                    name="field_{{ $index }}"
                                                                    {{ $field['required'] ? 'required' : '' }}>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <button type="submit" class="btn btn-success mt-6">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                                            </svg>
                                            Daftar Event Ini
                                        </button>
                                    </form>
                                @else
                                    <div class="alert alert-warning">
                                        <strong>Pendaftaran Tidak Tersedia</strong>
                                        <ul class="mt-2 list-disc space-y-1 pl-5">
                                            @if($event->status !== 'active')
                                                <li>Event tidak aktif (Status: {{ $event->status }})</li>
                                            @endif
                                            @php
                                                $now = \Carbon\Carbon::now();
                                            @endphp
                                            @if($now->lt($event->registration_start))
                                                <li>Pendaftaran belum dibuka. Dibuka pada: <strong>{{ $event->registration_start->format('d M Y H:i') }}</strong></li>
                                            @endif
                                            @if($now->gt($event->registration_end))
                                                <li>Pendaftaran sudah ditutup pada: <strong>{{ $event->registration_end->format('d M Y H:i') }}</strong></li>
                                            @endif
                                            @if($event->registered_count >= $event->quota)
                                                <li>Kuota sudah penuh ({{ $event->registered_count }}/{{ $event->quota }} peserta)</li>
                                            @endif
                                        </ul>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endif
                @else
                    <div class="premium-card">
                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-100">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1" />
                                    <circle cx="9.5" cy="7" r="3" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 19v-1a4 4 0 0 0-3-3.87" />
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-base font-semibold text-slate-950">Masuk untuk mendaftar</h2>
                                <p class="mt-1 text-sm leading-6 text-slate-600">
                                    Anda harus login untuk mendaftar event ini. Belum punya akun? Daftar terlebih dahulu, lalu kembali ke halaman ini.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                            <a href="{{ route('register') }}" class="btn btn-success">Register</a>
                        </div>
                    </div>
                @endauth
            </div>

            {{-- Meta sidebar --}}
            <aside class="space-y-6 lg:sticky lg:top-20">
                <div class="premium-card">
                    <div class="premium-card-header">
                        <h2 class="premium-card-title">Informasi Event</h2>
                        @if($event->isRegistrationOpen())
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Pendaftaran dibuka
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600 ring-1 ring-inset ring-slate-500/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                Pendaftaran ditutup
                            </span>
                        @endif
                    </div>

                    <dl class="mt-5 divide-y divide-slate-100 text-sm">
                        <div class="flex items-start gap-3 py-3 first:pt-0">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1" />
                                <circle cx="9.5" cy="7" r="3" />
                            </svg>
                            <div class="min-w-0">
                                <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Penyelenggara</dt>
                                <dd class="mt-0.5 font-medium text-slate-800">{{ $event->organizer->name }}</dd>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 py-3">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.3 7-11a7 7 0 1 0-14 0c0 5.7 7 11 7 11Z" />
                                <circle cx="12" cy="10" r="2.5" />
                            </svg>
                            <div class="min-w-0">
                                <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Lokasi</dt>
                                <dd class="mt-0.5 font-medium text-slate-800">{{ $event->location ?? 'Online' }}</dd>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 py-3">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5v-9Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h3" />
                            </svg>
                            <div class="min-w-0">
                                <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Harga</dt>
                                <dd class="mt-0.5 font-semibold text-slate-900">Rp {{ number_format($event->price, 0, ',', '.') }}</dd>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 py-3">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1" />
                                <circle cx="10.5" cy="7" r="3" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 19v-1a4 4 0 0 0-3-3.87M16 4.13A4 4 0 0 1 16 11" />
                            </svg>
                            <div class="min-w-0 flex-1">
                                <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Kuota</dt>
                                <dd class="mt-0.5 font-medium">
                                    @if($availableSlots > 0)
                                        <span class="text-slate-800">{{ $availableSlots }} dari {{ $event->quota }} tersisa</span>
                                    @else
                                        <span class="text-rose-600">Penuh ({{ $event->registered_count }}/{{ $event->quota }})</span>
                                    @endif
                                </dd>
                                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $filledPercent >= 100 ? 'bg-rose-500' : 'bg-indigo-500' }}" style="width: {{ $filledPercent }}%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 py-3">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 3v3m10-3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                            </svg>
                            <div class="min-w-0">
                                <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Periode Pendaftaran</dt>
                                <dd class="mt-0.5 font-medium text-slate-800">
                                    {{ $event->registration_start->format('d M Y') }} &ndash; {{ $event->registration_end->format('d M Y') }}
                                </dd>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 py-3 last:pb-0">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 3v3m10-3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 13h3v3H8z" />
                            </svg>
                            <div class="min-w-0">
                                <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Tanggal Hari H</dt>
                                <dd class="mt-0.5 font-medium text-slate-800">
                                    @if($event->event_date)
                                    {{ $event->event_date->format('d M Y') }}
                                    @else
                                    {{ $event->event_start->format('d M Y') }} &ndash; {{ $event->event_end->format('d M Y') }}
                                    @endif
                                </dd>
                            </div>
                        </div>
                    </dl>
                </div>
            </aside>
        </div>
    </div>
@endsection
