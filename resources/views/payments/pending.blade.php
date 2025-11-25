@extends('layouts.app')

@section('title', 'Verifikasi Pembayaran')

@section('content')
<div class="card">
    <h1>Verifikasi Pembayaran</h1>
    <p>Daftar pembayaran yang menunggu verifikasi.</p>
</div>

@if($payments->isEmpty())
<div class="card">
    <p>Tidak ada pembayaran yang menunggu verifikasi.</p>
</div>
@else
<div class="card">
    <table>
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
                <td>{{ $payment->registration->registration_number }}</td>
                <td>{{ $payment->registration->user->name }}</td>
                <td>{{ $payment->registration->event->name }}</td>
                <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                <td>{{ $payment->created_at->format('d M Y H:i') }}</td>
                <td>
                    <a href="{{ Storage::url($payment->proof_file) }}" target="_blank" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Lihat Bukti</a>
                    <button onclick="showVerifyModal({{ $payment->id }}, '{{ $payment->registration->user->name }}')" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Verifikasi</button>
                    <button onclick="showRejectModal({{ $payment->id }}, '{{ $payment->registration->user->name }}')" class="btn btn-danger" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Tolak</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 1.5rem;">
        {{ $payments->links() }}
    </div>
</div>
@endif

<!-- Verify Modal -->
<div id="verifyModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 8px; min-width: 400px;">
        <h3>Verifikasi Pembayaran</h3>
        <p>Verifikasi pembayaran dari <strong id="verifyUserName"></strong>?</p>

        <form method="POST" id="verifyForm">
            @csrf
            <input type="hidden" name="status" value="verified">

            <div class="form-group">
                <label>Catatan (opsional)</label>
                <textarea name="verification_notes" class="form-control" rows="3"></textarea>
            </div>

            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-success">Verifikasi</button>
                <button type="button" onclick="closeModals()" class="btn" style="background: #ddd;">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 8px; min-width: 400px;">
        <h3>Tolak Pembayaran</h3>
        <p>Tolak pembayaran dari <strong id="rejectUserName"></strong>?</p>

        <form method="POST" id="rejectForm">
            @csrf
            <input type="hidden" name="status" value="rejected">

            <div class="form-group">
                <label>Alasan Penolakan *</label>
                <textarea name="verification_notes" class="form-control" rows="3" required></textarea>
            </div>

            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-danger">Tolak</button>
                <button type="button" onclick="closeModals()" class="btn" style="background: #ddd;">Batal</button>
            </div>
        </form>
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