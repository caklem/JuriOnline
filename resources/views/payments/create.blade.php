@extends('layouts.app')

@section('title', 'Upload Bukti Pembayaran')

@section('content')
<div class="card">
    <h1>Upload Bukti Pembayaran</h1>

    <div style="padding: 1rem; background-color: #f8f9fa; border-radius: 5px; margin-bottom: 1.5rem;">
        <h3>Detail Pendaftaran</h3>
        <p>
            <strong>Event:</strong> {{ $registration->event->name }}<br>
            <strong>No. Pendaftaran:</strong> {{ $registration->registration_number }}<br>
            <strong>Jumlah yang harus dibayar:</strong> <span style="font-size: 1.5rem; color: #27ae60;">Rp {{ number_format($registration->event->price, 0, ',', '.') }}</span>
        </p>
    </div>

    <div style="padding: 1rem; background-color: #fff3cd; border-radius: 5px; margin-bottom: 1.5rem;">
        <h3>Informasi Pembayaran</h3>
        <p>Silakan transfer ke salah satu rekening berikut:</p>
        <ul>
            <li><strong>BCA:</strong> 1234567890 a/n JuriOnline</li>
            <li><strong>Mandiri:</strong> 0987654321 a/n JuriOnline</li>
            <li><strong>BNI:</strong> 1122334455 a/n JuriOnline</li>
        </ul>
        <p><strong>Upload bukti transfer Anda di bawah ini.</strong></p>
    </div>

    <form method="POST" action="{{ route('payments.store', $registration) }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="proof_file">Bukti Transfer (JPG, PNG, PDF - Max 2MB) *</label>
            <input type="file" name="proof_file" id="proof_file" class="form-control" accept="image/jpeg,image/png,application/pdf" required>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-success">Upload Bukti Pembayaran</button>
            <a href="{{ route('registrations.show', $registration) }}" class="btn" style="background: #ddd;">Batal</a>
        </div>
    </form>
</div>
@endsection