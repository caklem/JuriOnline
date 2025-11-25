@extends('layouts.app')

@section('title', 'Detail Pendaftaran')

@section('content')
<div class="card">
    <h1>Detail Pendaftaran</h1>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div>
            <h3>Informasi Event</h3>
            <p>
                <strong>Nama Event:</strong> {{ $registration->event->name }}<br>
                <strong>Organizer:</strong> {{ $registration->event->organizer->name }}<br>
                <strong>Tanggal Event:</strong> {{ $registration->event->event_start->format('d M Y') }} - {{ $registration->event->event_end->format('d M Y') }}<br>
                <strong>Lokasi:</strong> {{ $registration->event->location ?? 'Online' }}
            </p>
        </div>

        <div>
            <h3>Informasi Pendaftaran</h3>
            <p>
                <strong>No. Pendaftaran:</strong> {{ $registration->registration_number }}<br>
                <strong>Nama Peserta:</strong> {{ $registration->user->name }}<br>
                <strong>Email:</strong> {{ $registration->user->email }}<br>
                <strong>Tanggal Daftar:</strong> {{ $registration->registered_at->format('d M Y H:i') }}<br>
                <strong>Status:</strong>
                @if($registration->status === 'verified')
                <span class="badge badge-success">Terverifikasi</span>
                @elseif($registration->status === 'pending')
                <span class="badge badge-warning">Menunggu</span>
                @elseif($registration->status === 'rejected')
                <span class="badge badge-danger">Ditolak</span>
                @else
                <span class="badge badge-info">{{ $registration->status }}</span>
                @endif
            </p>

            @if($registration->notes)
            <p>
                <strong>Catatan:</strong><br>
                {{ $registration->notes }}
            </p>
            @endif
        </div>
    </div>

    @if($registration->response)
    <div style="margin-bottom: 2rem; padding: 1.5rem; background-color: #e8f5e9; border-radius: 5px; border-left: 4px solid #27ae60;">
        <h3>📝 Data Form Pendaftaran</h3>
        <div style="margin-top: 1rem;">
            @foreach($registration->response->responses as $label => $answer)
            <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #ddd;">
                <strong style="color: #27ae60;">{{ $label }}:</strong><br>
                <span style="color: #333; white-space: pre-line;">{{ $answer ?? '-' }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div style="margin-bottom: 2rem; padding: 1.5rem; background-color: #f8f9fa; border-radius: 5px;">
        <h3>Status Pembayaran</h3>

        @if($registration->payment)
        <p>
            <strong>Jumlah:</strong> Rp {{ number_format($registration->payment->amount, 0, ',', '.') }}<br>
            <strong>Status:</strong>
            @if($registration->payment->status === 'verified')
            <span class="badge badge-success">Terverifikasi</span>
            @elseif($registration->payment->status === 'pending')
            <span class="badge badge-warning">Menunggu Verifikasi</span>
            @else
            <span class="badge badge-danger">Ditolak</span>
            @endif
            <br>
            <strong>Tanggal Upload:</strong> {{ $registration->payment->created_at->format('d M Y H:i') }}
        </p>

        <div style="margin-top: 1rem;">
            <a href="{{ Storage::url($registration->payment->proof_file) }}" target="_blank" class="btn btn-primary">Lihat Bukti Transfer</a>
        </div>

        @if($registration->payment->verified_at)
        <p style="margin-top: 1rem;">
            <strong>Diverifikasi oleh:</strong> {{ $registration->payment->verifier->name }}<br>
            <strong>Tanggal Verifikasi:</strong> {{ $registration->payment->verified_at->format('d M Y H:i') }}
            @if($registration->payment->verification_notes)
            <br><strong>Catatan:</strong> {{ $registration->payment->verification_notes }}
            @endif
        </p>
        @endif
        @else
        <div class="alert alert-warning">
            <strong>Bukti pembayaran belum diupload!</strong>
            <p>Silakan upload bukti transfer Anda untuk memverifikasi pendaftaran.</p>
        </div>

        @if(auth()->user()->isParticipant() && $registration->user_id === auth()->id())
        <a href="{{ route('payments.create', $registration) }}" class="btn btn-success">Upload Bukti Pembayaran</a>
        @endif
        @endif
    </div>

    @if($registration->juryScores->isNotEmpty())
    <div style="margin-bottom: 2rem; padding: 1.5rem; background-color: #f8f9fa; border-radius: 5px;">
        <h3>Penilaian Juri</h3>

        <table>
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
                    <td>{{ $score->jury->name }}</td>
                    <td><strong>{{ number_format($score->score, 2) }}</strong></td>
                    <td>{{ $score->public_feedback ?? '-' }}</td>
                    <td>{{ $score->created_at->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 1rem; padding: 1rem; background-color: #d4edda; border-radius: 5px;">
            <strong>Nilai Rata-rata:</strong>
            <span style="font-size: 1.5rem; color: #155724;">{{ number_format($registration->getAverageScore(), 2) }}</span>
        </div>
    </div>
    @endif

    <div style="display: flex; gap: 0.5rem;">
        @if(auth()->user()->isParticipant())
        <a href="{{ route('registrations.my') }}" class="btn" style="background: #ddd;">Kembali ke Pendaftaran Saya</a>
        @else
        <a href="{{ route('events.participants', $registration->event) }}" class="btn" style="background: #ddd;">Kembali ke Daftar Peserta</a>
        @endif
    </div>
</div>
@endsection