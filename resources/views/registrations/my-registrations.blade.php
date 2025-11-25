@extends('layouts.app')

@section('title', 'Pendaftaran Saya')

@section('content')
<div class="card">
    <h1>Pendaftaran Saya</h1>
    <p>Daftar event yang Anda ikuti</p>
</div>

@if($registrations->isEmpty())
<div class="card">
    <p>Anda belum mendaftar event apapun.</p>
    <a href="{{ route('events.public') }}" class="btn btn-primary">Browse Event</a>
</div>
@else
<div class="card">
    <table>
        <thead>
            <tr>
                <th>Event</th>
                <th>No. Pendaftaran</th>
                <th>Tanggal Daftar</th>
                <th>Status Pendaftaran</th>
                <th>Status Pembayaran</th>
                <th>Nilai</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registrations as $registration)
            <tr>
                <td>
                    <strong>{{ $registration->event->name }}</strong><br>
                    <small style="color: #666;">{{ $registration->event->event_start->format('d M Y') }}</small>
                </td>
                <td>{{ $registration->registration_number }}</td>
                <td>{{ $registration->registered_at->format('d M Y') }}</td>
                <td>
                    @if($registration->status === 'verified')
                    <span class="badge badge-success">Terverifikasi</span>
                    @elseif($registration->status === 'pending')
                    <span class="badge badge-warning">Pending</span>
                    @elseif($registration->status === 'rejected')
                    <span class="badge badge-danger">Ditolak</span>
                    @else
                    <span class="badge badge-info">{{ ucfirst($registration->status) }}</span>
                    @endif
                </td>
                <td>
                    @if($registration->payment)
                    @if($registration->payment->status === 'verified')
                    <span class="badge badge-success">Terverifikasi</span>
                    @elseif($registration->payment->status === 'pending')
                    <span class="badge badge-warning">Menunggu</span>
                    @else
                    <span class="badge badge-danger">Ditolak</span>
                    @endif
                    @else
                    <span class="badge badge-danger">Belum Upload</span>
                    @endif
                </td>
                <td>
                    @php
                    $avgScore = $registration->getAverageScore();
                    @endphp
                    @if($avgScore > 0)
                    <strong style="font-size: 1.1rem; color: #27ae60;">{{ number_format($avgScore, 2) }}</strong>
                    @else
                    <span style="color: #999;">-</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('registrations.show', $registration) }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Detail</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 1.5rem;">
        {{ $registrations->links() }}
    </div>
</div>
@endif
@endsection