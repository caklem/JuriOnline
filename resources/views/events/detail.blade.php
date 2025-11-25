@extends('layouts.app')

@section('title', 'Detail Event')

@section('content')
<div class="card">
    @if($event->banner)
    <img src="{{ Storage::url($event->banner) }}" alt="{{ $event->name }}" style="width: 100%; max-height: 400px; object-fit: cover; border-radius: 5px; margin-bottom: 1.5rem;">
    @endif

    <div style="margin-bottom: 1rem;">
        @if($event->category)
        <span class="badge badge-info">{{ $event->category->name }}</span>
        @endif
        <span class="badge badge-success">{{ ucfirst($event->status) }}</span>
    </div>

    <h1 style="margin-bottom: 1rem;">{{ $event->name }}</h1>

    <div style="margin-bottom: 1.5rem; padding: 1rem; background-color: #f8f9fa; border-radius: 5px;">
        <strong>Organizer:</strong> {{ $event->organizer->name }}<br>
        <strong>Lokasi:</strong> {{ $event->location ?? 'Online' }}<br>
        <strong>Harga:</strong> Rp {{ number_format($event->price, 0, ',', '.') }}<br>
        <strong>Kuota:</strong> {{ $event->getAvailableSlots() }} dari {{ $event->quota }} tersisa<br>
        <strong>Periode Pendaftaran:</strong> {{ $event->registration_start->format('d M Y') }} - {{ $event->registration_end->format('d M Y') }}<br>
        <strong>Periode Event:</strong> {{ $event->event_start->format('d M Y') }} - {{ $event->event_end->format('d M Y') }}
    </div>

    <h3>Deskripsi Event</h3>
    <p style="white-space: pre-line;">{{ $event->description }}</p>

    @if($event->require_official_email && $event->allowed_email_domains)
    <div class="alert alert-info" style="margin-top: 1.5rem; background: #2563eb; color: white; padding: 1.25rem; border-radius: 8px; border: none;">
        <h4 style="margin-top: 0; color: white; display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 1.5rem;">📧</span>
            <strong>Persyaratan Email Resmi</strong>
        </h4>
        <p style="margin-bottom: 0.5rem;">Pendaftaran event ini <strong>HANYA untuk email resmi</strong> dengan domain berikut:</p>
        <div style="background: rgba(255,255,255,0.2); padding: 0.75rem; border-radius: 8px; margin-top: 0.75rem; backdrop-filter: blur(10px);">
            @foreach(array_map('trim', explode(',', $event->allowed_email_domains)) as $domain)
            <span style="background: rgba(255,255,255,0.9); color: #2c3e50; padding: 0.4rem 0.8rem; border-radius: 20px; display: inline-block; margin: 0.25rem; font-weight: bold;">
                ✓ @{{ $domain }}
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
        <p style="margin-top: 1rem; margin-bottom: 0; background: rgba(46, 204, 113, 0.3); padding: 0.75rem; border-radius: 8px; border: 2px solid rgba(46, 204, 113, 0.6);">
            <strong>✅ Email Anda ({{ auth()->user()->email }}) memenuhi syarat!</strong>
        </p>
        @else
        <p style="margin-top: 1rem; margin-bottom: 0; background: rgba(231, 76, 60, 0.3); padding: 0.75rem; border-radius: 8px; border: 2px solid rgba(231, 76, 60, 0.6);">
            <strong>❌ Email Anda ({{ auth()->user()->email }}) tidak memenuhi syarat!</strong><br>
            <small>Gunakan email resmi dari domain yang diizinkan untuk mendaftar.</small>
        </p>
        @endif
        @endauth
    </div>
    @endif

    @auth
    @if(auth()->user()->isParticipant() || !auth()->user()->role)
    <div style="margin-top: 2rem; padding: 1.5rem; background-color: #f8f9fa; border-radius: 5px;">
        @if($userRegistration)
        <h3>Status Pendaftaran Anda</h3>
        <p>
            <strong>No. Pendaftaran:</strong> {{ $userRegistration->registration_number }}<br>
            <strong>Status:</strong>
            @if($userRegistration->status === 'verified')
            <span class="badge badge-success">Terverifikasi</span>
            @elseif($userRegistration->status === 'pending')
            <span class="badge badge-warning">Menunggu Verifikasi</span>
            @elseif($userRegistration->status === 'rejected')
            <span class="badge badge-danger">Ditolak</span>
            @endif
        </p>

        @if($userRegistration->payment)
        <p>
            <strong>Status Pembayaran:</strong>
            @if($userRegistration->payment->status === 'verified')
            <span class="badge badge-success">Terverifikasi</span>
            @elseif($userRegistration->payment->status === 'pending')
            <span class="badge badge-warning">Menunggu Verifikasi</span>
            @else
            <span class="badge badge-danger">Ditolak</span>
            @endif
        </p>
        @else
        <p style="color: #e74c3c;"><strong>Bukti pembayaran belum diupload!</strong></p>
        <a href="{{ route('payments.create', $userRegistration) }}" class="btn btn-warning">Upload Bukti Pembayaran</a>
        @endif

        <a href="{{ route('registrations.show', $userRegistration) }}" class="btn btn-primary">Lihat Detail Pendaftaran</a>
        @else
        @if(!auth()->user()->role)
        <div class="alert alert-danger">
            ⚠️ <strong>Anda belum bisa mendaftar!</strong><br>
            Akun Anda belum memiliki role. Silakan logout dan login kembali, atau hubungi administrator.
        </div>
        @elseif($event->isRegistrationOpen())
        <h3>Daftar Sekarang!</h3>
        <p>Event ini masih membuka pendaftaran. Daftar sekarang sebelum kuota penuh!</p>

        <form method="POST" action="{{ route('registrations.store', $event) }}">
            @csrf

            @if($event->eventForm)
            <div style="background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 1rem; border: 2px solid #3498db;">
                <h4 style="margin-bottom: 1rem; color: #3498db;">📝 Isi Form Pendaftaran</h4>

                @foreach($event->eventForm->form_fields as $index => $field)
                <div class="form-group">
                    <label for="field_{{ $index }}">
                        {{ $field['label'] }}
                        @if($field['required'])
                        <span style="color: red;">*</span>
                        @endif
                    </label>
                    @if(isset($field['description']) && $field['description'])
                    <p style="color: #666; font-size: 0.9rem; margin-top: 0.25rem; margin-bottom: 0.5rem;">{{ $field['description'] }}</p>
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
            @endif

            <button type="submit" class="btn btn-success" style="font-size: 1.125rem; padding: 1rem 2rem;">
                ✅ Daftar Event Ini
            </button>
        </form>
        @else
        <div class="alert alert-warning">
            <strong>❌ Pendaftaran Tidak Tersedia</strong><br>
            @if($event->status !== 'active')
            • Event tidak aktif (Status: {{ $event->status }})<br>
            @endif
            @php
            $now = \Carbon\Carbon::now();
            @endphp
            @if($now->lt($event->registration_start))
            • Pendaftaran belum dibuka. Dibuka pada: <strong>{{ $event->registration_start->format('d M Y H:i') }}</strong><br>
            @endif
            @if($now->gt($event->registration_end))
            • Pendaftaran sudah ditutup pada: <strong>{{ $event->registration_end->format('d M Y H:i') }}</strong><br>
            @endif
            @if($event->registered_count >= $event->quota)
            • Kuota sudah penuh ({{ $event->registered_count }}/{{ $event->quota }} peserta)<br>
            @endif
        </div>
        @endif
        @endif
    </div>
    @endif
    @else
    <div style="margin-top: 2rem; padding: 1.5rem; background-color: #fff3cd; border-radius: 5px;">
        <p><strong>Anda harus login untuk mendaftar event ini.</strong></p>
        <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
        <a href="{{ route('register') }}" class="btn btn-success">Register</a>
    </div>
    @endauth
</div>
@endsection