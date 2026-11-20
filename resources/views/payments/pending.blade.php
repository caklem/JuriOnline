@extends('layouts.app')

@section('title', 'Verifikasi Pembayaran')

@section('content')
<div class="page-header-premium">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Administrasi</p>
            <h1>Verifikasi Pembayaran</h1>
            <p>Daftar pembayaran yang menunggu verifikasi.</p>
        </div>
        <span class="badge-premium badge-premium-warning">{{ $payments->total() }} menunggu</span>
    </div>
</div>

@if($payments->isEmpty())
<div class="premium-card">
    <div class="empty-state-premium">
        <div class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-600">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
            </svg>
        </div>
        <h3>Tidak Ada Pembayaran Pending</h3>
        <p>Semua pembayaran sudah diverifikasi. Tidak ada yang menunggu tindakan.</p>
    </div>
</div>
@else
<div class="premium-card">
    <div class="premium-card-header">
        <div>
            <h2 class="premium-card-title">Antrean Verifikasi</h2>
            <p class="mt-1 text-xs text-slate-500">Periksa bukti transfer sebelum menyetujui atau menolak pembayaran.</p>
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
                    <th>Tanggal Upload</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr>
                    <td><code class="rounded-md bg-slate-100 px-2 py-1 font-mono text-xs text-slate-700">{{ $payment->registration->registration_number }}</code></td>
                    <td><strong class="font-semibold text-slate-900">{{ $payment->registration->user->name }}</strong></td>
                    <td class="text-slate-600">{{ $payment->registration->event->name }}</td>
                    <td class="whitespace-nowrap font-semibold text-slate-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-slate-600">{{ $payment->created_at->format('d M Y H:i') }}</td>
                    <td>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('payments.proof', $payment) }}" target="_blank" rel="noopener" class="btn-premium btn-premium-outline btn-sm">Lihat Bukti</a>
                            <button type="button" data-payment-id="{{ $payment->id }}" data-payment-name="{{ $payment->registration->user->name }}" onclick="showVerifyModal(this.dataset.paymentId, this.dataset.paymentName)" class="btn btn-success btn-sm">Verifikasi</button>
                            <button type="button" data-payment-id="{{ $payment->id }}" data-payment-name="{{ $payment->registration->user->name }}" onclick="showRejectModal(this.dataset.paymentId, this.dataset.paymentName)" class="btn btn-outline-danger btn-sm">Tolak</button>
                        </div>
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

<!-- Verify Modal -->
<div id="verifyModal" style="display: none;" class="fixed inset-0 z-50 bg-slate-950/60 p-4">
    <div class="flex min-h-full items-center justify-center">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <h3 class="text-base font-semibold text-slate-900">Verifikasi Pembayaran</h3>
            <p class="mt-1 text-sm text-slate-500">Verifikasi pembayaran dari <strong id="verifyUserName" class="font-semibold text-slate-900"></strong>?</p>

            <form method="POST" id="verifyForm" class="mt-5">
                @csrf
                <input type="hidden" name="status" value="verified">

                <div class="form-group">
                    <label class="form-label">Catatan (opsional)</label>
                    <textarea name="verification_notes" class="form-control" rows="3"></textarea>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-success">Verifikasi</button>
                    <button type="button" onclick="closeModals()" class="btn btn-cancel">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" style="display: none;" class="fixed inset-0 z-50 bg-slate-950/60 p-4">
    <div class="flex min-h-full items-center justify-center">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-200">
            <h3 class="text-base font-semibold text-slate-900">Tolak Pembayaran</h3>
            <p class="mt-1 text-sm text-slate-500">Tolak pembayaran dari <strong id="rejectUserName" class="font-semibold text-slate-900"></strong>?</p>

            <form method="POST" id="rejectForm" class="mt-5">
                @csrf
                <input type="hidden" name="status" value="rejected">

                <div class="form-group">
                    <label class="form-label">Alasan Penolakan *</label>
                    <textarea name="verification_notes" class="form-control" rows="3" required></textarea>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-danger">Tolak</button>
                    <button type="button" onclick="closeModals()" class="btn btn-cancel">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showVerifyModal(paymentId, userName) {
        document.getElementById('verifyUserName').textContent = userName;
        document.getElementById('verifyForm').action = '/payments/' + paymentId + '/verify';
        document.getElementById('verifyModal').style.display = 'block';
    }

    function showRejectModal(paymentId, userName) {
        document.getElementById('rejectUserName').textContent = userName;
        document.getElementById('rejectForm').action = '/payments/' + paymentId + '/verify';
        document.getElementById('rejectModal').style.display = 'block';
    }

    function closeModals() {
        document.getElementById('verifyModal').style.display = 'none';
        document.getElementById('rejectModal').style.display = 'none';
    }
</script>
@endpush
@endsection
