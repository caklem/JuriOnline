@extends('layouts.app')

@section('title', 'Upload Bukti Pembayaran')

@section('content')
<div class="page-header-premium">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Pembayaran</p>
            <h1>Upload Bukti Pembayaran</h1>
            <p>Lengkapi bukti transfer agar pendaftaran Anda dapat diverifikasi oleh penyelenggara.</p>
        </div>
        <a href="{{ route('registrations.show', $registration) }}" class="btn-premium btn-premium-outline btn-sm">
            Kembali ke pendaftaran
        </a>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <div class="premium-card">
            <div class="premium-card-header">
                <h2 class="premium-card-title">Detail Pendaftaran</h2>
                <span class="badge-premium badge-premium-warning">Menunggu pembayaran</span>
            </div>

            <dl class="divide-y divide-slate-100">
                <div class="flex flex-wrap items-center justify-between gap-2 py-3 first:pt-0">
                    <dt class="text-sm text-slate-500">Event</dt>
                    <dd class="text-sm font-semibold text-slate-900">{{ $registration->event->name }}</dd>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2 py-3">
                    <dt class="text-sm text-slate-500">No. Pendaftaran</dt>
                    <dd>
                        <code class="rounded-md bg-slate-100 px-2 py-1 font-mono text-xs text-slate-700">{{ $registration->registration_number }}</code>
                    </dd>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2 py-3 last:pb-0">
                    <dt class="text-sm text-slate-500">Jumlah yang harus dibayar</dt>
                    <dd class="text-2xl font-semibold tracking-tight text-slate-900">
                        Rp {{ number_format($registration->event->price, 0, ',', '.') }}
                    </dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
            <div class="mb-4 border-b border-amber-200 pb-3">
                <h2 class="text-base font-semibold text-amber-900">Informasi Pembayaran</h2>
            </div>
            <p class="text-sm leading-6 text-amber-900">Silakan transfer ke salah satu rekening berikut:</p>
            <ul class="mt-3 space-y-2">
                <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-white/70 px-3 py-2 text-sm ring-1 ring-inset ring-amber-200">
                    <span class="font-semibold text-amber-900">BCA</span>
                    <span class="font-mono text-slate-800">1234567890 <span class="font-sans text-amber-800">a/n JURI.ON</span></span>
                </li>
                <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-white/70 px-3 py-2 text-sm ring-1 ring-inset ring-amber-200">
                    <span class="font-semibold text-amber-900">Mandiri</span>
                    <span class="font-mono text-slate-800">0987654321 <span class="font-sans text-amber-800">a/n JURI.ON</span></span>
                </li>
                <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-white/70 px-3 py-2 text-sm ring-1 ring-inset ring-amber-200">
                    <span class="font-semibold text-amber-900">BNI</span>
                    <span class="font-mono text-slate-800">1122334455 <span class="font-sans text-amber-800">a/n JURI.ON</span></span>
                </li>
            </ul>
            <p class="mt-4 text-sm font-medium text-amber-900">Upload bukti transfer Anda pada formulir di samping.</p>
        </div>
    </div>

    <div class="form-card h-fit">
        <div class="form-header">
            <div>
                <h2 class="form-title">Unggah Bukti Transfer</h2>
                <p class="form-meta">Format JPG, PNG, atau PDF. Maksimal 2MB.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('payments.store', $registration) }}" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="proof_file" class="form-label">Bukti Transfer (JPG, PNG, PDF - Max 2MB) *</label>
                <input type="file" name="proof_file" id="proof_file" class="form-control file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white" accept="image/jpeg,image/png,application/pdf" required>
                @error('proof_file')
                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
                <span class="form-help">Pastikan nominal transfer sesuai dengan jumlah yang tertera.</span>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="submit" class="btn btn-success">Upload Bukti Pembayaran</button>
                <a href="{{ route('registrations.show', $registration) }}" class="btn btn-cancel">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
