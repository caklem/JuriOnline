@extends('layouts.app')

@section('title', 'Detail Pendaftaran')

@section('content')
<div class="page-header-premium">
    <h1>Detail Pendaftaran</h1>
    <p>Ringkasan event, pendaftaran, dan pembayaran peserta.</p>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="premium-card !mb-0">
        <h3 class="mb-4 text-base font-semibold text-slate-900">Informasi Event</h3>
        <dl class="space-y-3 text-sm">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Nama Event</dt>
                <dd class="mt-0.5 font-medium text-slate-900">{{ $registration->event->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Penyelenggara</dt>
                <dd class="mt-0.5 text-slate-700">{{ $registration->event->organizer->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Tanggal Event</dt>
                <dd class="mt-0.5 text-slate-700">{{ $registration->event->event_start->format('d M Y') }} - {{ $registration->event->event_end->format('d M Y') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Lokasi</dt>
                <dd class="mt-0.5 text-slate-700">{{ $registration->event->location ?? 'Online' }}</dd>
            </div>
        </dl>
    </div>

    <div class="premium-card !mb-0">
        <h3 class="mb-4 text-base font-semibold text-slate-900">Informasi Pendaftaran</h3>
        <dl class="space-y-3 text-sm">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">No. Pendaftaran</dt>
                <dd class="mt-0.5">
                    <span class="font-mono text-xs text-slate-600 bg-slate-100 px-2 py-1 rounded-md">{{ $registration->registration_number }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Nama Peserta</dt>
                <dd class="mt-0.5 font-medium text-slate-900">{{ $registration->user->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Email</dt>
                <dd class="mt-0.5 text-slate-700">{{ $registration->user->email }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Tanggal Daftar</dt>
                <dd class="mt-0.5 text-slate-700">{{ $registration->registered_at->format('d M Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Status</dt>
                <dd class="mt-1">
                    @if($registration->status === 'verified')
                    <span class="badge badge-success">Terverifikasi</span>
                    @elseif($registration->status === 'pending')
                    <span class="badge badge-warning">Menunggu</span>
                    @elseif($registration->status === 'rejected')
                    <span class="badge badge-danger">Ditolak</span>
                    @else
                    <span class="badge badge-info">{{ $registration->status }}</span>
                    @endif
                </dd>
            </div>
            @if($registration->notes)
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Catatan</dt>
                <dd class="mt-0.5 whitespace-pre-line text-slate-700">{{ $registration->notes }}</dd>
            </div>
            @endif
        </dl>
    </div>
</div>

@if($registration->response)
<div class="premium-card !border-l-4 !border-emerald-500">
    <h3 class="mb-4 flex items-center gap-2 text-base font-semibold text-slate-900">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5 text-emerald-600" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6m-7 4h8m-8 4h5m-5 4h8M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
        </svg>
        Data Form Pendaftaran
    </h3>
    <div class="space-y-4">
        @foreach($registration->response->responses as $label => $answer)
        <div class="border-b border-slate-100 pb-3 last:border-0 last:pb-0">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">{{ $label }}</p>
            <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $answer ?? '-' }}</p>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="premium-card">
    <h3 class="mb-4 text-base font-semibold text-slate-900">Status Pembayaran</h3>

    @if($registration->payment)
    <dl class="space-y-3 text-sm">
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Jumlah</dt>
            <dd class="mt-0.5 font-semibold text-slate-900">Rp {{ number_format($registration->payment->amount, 0, ',', '.') }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Status</dt>
            <dd class="mt-1">
                @if($registration->payment->status === 'verified')
                <span class="badge badge-success">Terverifikasi</span>
                @elseif($registration->payment->status === 'pending')
                <span class="badge badge-warning">Menunggu Verifikasi</span>
                @else
                <span class="badge badge-danger">Ditolak</span>
                @endif
            </dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Tanggal Upload</dt>
            <dd class="mt-0.5 text-slate-700">{{ $registration->payment->created_at->format('d M Y H:i') }}</dd>
        </div>
    </dl>

    <div class="mt-4">
        <a href="{{ route('payments.proof', $registration->payment) }}" target="_blank" class="btn btn-primary">
            Lihat Bukti Transfer
        </a>
    </div>

    @if($registration->payment->verified_at)
    <dl class="mt-4 space-y-2 border-t border-slate-100 pt-4 text-sm">
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Diverifikasi oleh</dt>
            <dd class="mt-0.5 text-slate-700">{{ $registration->payment->verifier->name }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Tanggal Verifikasi</dt>
            <dd class="mt-0.5 text-slate-700">{{ $registration->payment->verified_at->format('d M Y H:i') }}</dd>
        </div>
        @if($registration->payment->verification_notes)
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Catatan</dt>
            <dd class="mt-0.5 text-slate-700">{{ $registration->payment->verification_notes }}</dd>
        </div>
        @endif
    </dl>
    @endif
    @else
    <div class="alert alert-warning">
        <strong>Bukti pembayaran belum diupload!</strong>
        <p class="mt-1">Silakan upload bukti transfer Anda untuk memverifikasi pendaftaran.</p>
    </div>

    @if(auth()->user()->isParticipant() && $registration->user_id === auth()->id())
    <a href="{{ route('payments.create', $registration) }}" class="btn btn-success">
        Upload Bukti Pembayaran
    </a>
    @endif
    @endif
</div>

@if($registration->juryScores->isNotEmpty())
<div class="premium-card">
    <h3 class="mb-4 text-base font-semibold text-slate-900">Penilaian Juri</h3>

    <div class="overflow-x-auto">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>Juri</th>
                    <th>Nilai</th>
                    <th>Feedback</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registration->juryScores as $score)
                <tr>
                    <td class="font-medium text-slate-900">{{ $score->jury->name }}</td>
                    <td><strong>{{ number_format($score->score, 2) }}</strong></td>
                    <td>{{ $score->public_feedback ?? '-' }}</td>
                    <td>{{ $score->created_at->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex items-center gap-3 rounded-xl bg-emerald-50 px-4 py-3 ring-1 ring-emerald-100">
        <span class="text-sm font-medium text-emerald-700">Nilai Rata-rata:</span>
        <span class="text-2xl font-semibold text-emerald-700">{{ number_format($registration->getAverageScore(), 2) }}</span>
    </div>
</div>
@endif

@if(auth()->user()->canManageEvent($registration->event))
<div class="premium-card">
    <div class="premium-card-header">
        <h2 class="premium-card-title">Kelola Status Pendaftaran</h2>
    </div>

    <form method="POST" action="{{ route('registrations.update-status', $registration) }}" class="space-y-4">
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="status" class="form-label">Status Pendaftaran</label>
            <select id="status" name="status" class="form-control">
                <option value="pending" @selected($registration->status === 'pending')>Menunggu</option>
                <option value="verified" @selected($registration->status === 'verified')>Terverifikasi</option>
                <option value="rejected" @selected($registration->status === 'rejected')>Ditolak</option>
                <option value="cancelled" @selected($registration->status === 'cancelled')>Dibatalkan</option>
            </select>
        </div>

        <div class="form-group">
            <label for="notes" class="form-label">Catatan</label>
            <textarea id="notes" name="notes" rows="3" class="form-control" placeholder="Catatan untuk peserta (opsional)">{{ $registration->notes }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Status</button>
    </form>
</div>
@endif

<div class="flex flex-wrap gap-2">
    @if(auth()->user()->isParticipant())
    <a href="{{ route('registrations.my') }}" class="btn btn-outline-secondary">Kembali ke Pendaftaran Saya</a>
    @else
    <a href="{{ route('events.participants', $registration->event) }}" class="btn btn-outline-secondary">Kembali ke Daftar Peserta</a>
    @endif
</div>
@endsection
