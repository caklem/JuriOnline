@extends('layouts.app')

@section('title', 'Semua Pembayaran')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="page-header-premium">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Administrasi</p>
            <h1>Semua Pembayaran</h1>
            <p>Riwayat seluruh pembayaran peserta beserta status verifikasinya.</p>
        </div>
        <a href="{{ route('payments.pending') }}" class="btn-premium btn-premium-warning btn-sm">Verifikasi pembayaran pending</a>
    </div>
</div>

@if($payments->isEmpty())
<div class="premium-card">
    <div class="empty-state-premium">
        <div class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5v-9Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h3" />
            </svg>
        </div>
        <h3>Belum Ada Pembayaran</h3>
        <p>Belum ada pembayaran yang tercatat di sistem.</p>
    </div>
</div>
@else
<div class="premium-card">
    <div class="premium-card-header">
        <div>
            <h2 class="premium-card-title">Riwayat Pembayaran</h2>
            <p class="mt-1 text-xs text-slate-500">Total {{ $payments->total() }} pembayaran tercatat.</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>No. Pendaftaran</th>
                    <th>Peserta</th>
                    <th>Event</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Diverifikasi Oleh</th>
                    <th>Tanggal Upload</th>
                    <th>Bukti</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr>
                    <td>
                        <code class="rounded-md bg-slate-100 px-2 py-1 font-mono text-xs text-slate-700">{{ $payment->registration->registration_number }}</code>
                    </td>
                    <td><strong class="font-semibold text-slate-900">{{ $payment->registration->user->name }}</strong></td>
                    <td class="text-slate-600">{{ $payment->registration->event->name }}</td>
                    <td class="whitespace-nowrap font-semibold text-slate-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                    <td>
                        @if($payment->status === 'verified')
                        <span class="badge-premium badge-premium-success">Terverifikasi</span>
                        @elseif($payment->status === 'pending')
                        <span class="badge-premium badge-premium-warning">Pending</span>
                        @else
                        <span class="badge-premium badge-premium-danger">Ditolak</span>
                        @endif
                    </td>
                    <td class="text-slate-600">
                        @if($payment->verifier)
                        {{ $payment->verifier->name }}
                        @else
                        <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap text-slate-600">{{ $payment->created_at->format('d M Y H:i') }}</td>
                    <td>
                        @if($payment->proof_file)
                        <a href="{{ route('payments.proof', $payment) }}" target="_blank" rel="noopener" class="btn-premium btn-premium-outline btn-sm">Lihat Bukti</a>
                        @else
                        <span class="text-slate-400">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $payments->links() }}
    </div>
</div>
@endif
@endsection
