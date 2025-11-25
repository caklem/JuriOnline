@extends('layouts.app')

@section('title', 'JuriOnline - Platform Penilaian Digital Terpercaya')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<!-- Hero Section -->
<section style="background: #2563eb; color: white; padding: 4rem 2rem; text-align: center; margin-bottom: 3rem; border-radius: 8px; position: relative; overflow: hidden; box-shadow: 0 4px 16px rgba(37, 99, 235, 0.3);">
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,<svg width=\"100\" height=\"100\" xmlns=\"http://www.w3.org/2000/svg\"><defs><pattern id=\"grid\" width=\"100\" height=\"100\" patternUnits=\"userSpaceOnUse\"><path d=\"M 100 0 L 0 0 0 100\" fill=\"none\" stroke=\"rgba(255,255,255,0.05)\" stroke-width=\"1\"/></pattern></defs><rect width=\"100\" height=\"100\" fill=\"url(%23grid)\"/></svg>'); opacity: 0.3;"></div>
    <div style="position: relative; z-index: 1; max-width: 900px; margin: 0 auto;">
        <div style="font-size: 4rem; margin-bottom: 1rem;">⚖️</div>
        <h1 style="font-size: 3rem; font-weight: 900; margin-bottom: 1rem; letter-spacing: -1px;">JuriOnline</h1>
        <p style="font-size: 1.5rem; margin-bottom: 1.5rem; opacity: 0.95; font-weight: 300;">Platform Penilaian Digital Terpercaya untuk Event Kompetisi</p>
        <p style="font-size: 1.1rem; margin-bottom: 2rem; opacity: 0.9; max-width: 700px; margin-left: auto; margin-right: auto;">
            Kelola event kompetisi, penilaian juri, dan pendaftaran peserta dengan sistem digital yang profesional, transparan, dan mudah digunakan.
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('register') }}" class="btn-premium btn-premium-success" style="padding: 1rem 2.5rem; font-size: 1.1rem; text-decoration: none;">
                🚀 Mulai Sekarang
            </a>
            <a href="#events" class="btn-premium btn-premium-outline" style="padding: 1rem 2.5rem; font-size: 1.1rem; text-decoration: none; background: rgba(255,255,255,0.1); color: white; border: 2px solid white;">
                📅 Lihat Event
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section style="margin-bottom: 4rem;">
    <div class="page-header-premium" style="text-align: center; margin-bottom: 3rem;">
        <h2>Mengapa Pilih JuriOnline?</h2>
        <p>Solusi lengkap untuk manajemen event dan penilaian digital</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
        <div class="premium-card" style="text-align: center; margin-bottom: 0;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
            <h3 style="color: var(--primary-color); margin-bottom: 0.75rem; font-size: 1.3rem;">Form Builder Custom</h3>
            <p style="color: var(--gray); margin: 0;">Buat form pendaftaran dan penilaian sesuai kebutuhan dengan drag & drop builder yang mudah digunakan.</p>
        </div>

        <div class="premium-card" style="text-align: center; margin-bottom: 0;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">⚖️</div>
            <h3 style="color: var(--primary-color); margin-bottom: 0.75rem; font-size: 1.3rem;">Multiple Form Penilaian</h3>
            <p style="color: var(--gray); margin: 0;">Satu event dapat memiliki beberapa form penilaian untuk juri berbeda (Vokal, Adab, Terbang, dll).</p>
        </div>

        <div class="premium-card" style="text-align: center; margin-bottom: 0;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
            <h3 style="color: var(--primary-color); margin-bottom: 0.75rem; font-size: 1.3rem;">Rekapitulasi Otomatis</h3>
            <p style="color: var(--gray); margin: 0;">Sistem menghitung rata-rata nilai dari semua juri secara otomatis dan transparan.</p>
        </div>

        <div class="premium-card" style="text-align: center; margin-bottom: 0;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">👥</div>
            <h3 style="color: var(--primary-color); margin-bottom: 0.75rem; font-size: 1.3rem;">Manajemen Peserta</h3>
            <p style="color: var(--gray); margin: 0;">Kelola pendaftaran, verifikasi, dan tracking status peserta dengan mudah dan real-time.</p>
        </div>

        <div class="premium-card" style="text-align: center; margin-bottom: 0;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">💳</div>
            <h3 style="color: var(--primary-color); margin-bottom: 0.75rem; font-size: 1.3rem;">Verifikasi Pembayaran</h3>
            <p style="color: var(--gray); margin: 0;">Upload bukti pembayaran dan verifikasi terintegrasi dengan status pendaftaran.</p>
        </div>

        <div class="premium-card" style="text-align: center; margin-bottom: 0;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🏆</div>
            <h3 style="color: var(--primary-color); margin-bottom: 0.75rem; font-size: 1.3rem;">Leaderboard Real-time</h3>
            <p style="color: var(--gray); margin: 0;">Peringkat peserta yang update secara real-time berdasarkan penilaian juri profesional.</p>
        </div>
    </div>
</section>

<!-- About Section -->
<section style="margin-bottom: 4rem;">
    <div class="premium-card">
        <div class="premium-card-header">
            <h2 class="premium-card-title">Tentang JuriOnline</h2>
        </div>
        
        <div style="line-height: 1.8; color: var(--dark);">
            <p style="font-size: 1.1rem; margin-bottom: 1.5rem;">
                <strong>JuriOnline</strong> adalah platform digital inovatif yang dirancang khusus untuk memudahkan proses penilaian dan manajemen event kompetisi, lomba, dan acara penjurian lainnya. Kami menyediakan solusi terintegrasi untuk mengelola seluruh siklus event, mulai dari pendaftaran peserta hingga penilaian oleh juri profesional.
            </p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-top: 2rem;">
                <div style="padding: 1.5rem; background: #eff6ff; border-radius: 8px; border-left: 4px solid var(--primary-color);">
                    <h4 style="color: var(--primary-color); margin-bottom: 0.5rem; font-size: 1.1rem;">🎯 Visi</h4>
                    <p style="margin: 0; font-size: 0.95rem;">Menjadi platform terdepan di Indonesia untuk sistem penilaian digital yang profesional, transparan, dan terpercaya.</p>
                </div>
                <div style="padding: 1.5rem; background: #f0fdf4; border-radius: 8px; border-left: 4px solid var(--success-color);">
                    <h4 style="color: var(--success-color); margin-bottom: 0.5rem; font-size: 1.1rem;">🔒 Transparan</h4>
                    <p style="margin: 0; font-size: 0.95rem;">Transparansi dalam proses penilaian dan manajemen event adalah prioritas utama kami.</p>
                </div>
                <div style="padding: 1.5rem; background: #fffbeb; border-radius: 8px; border-left: 4px solid var(--warning-color);">
                    <h4 style="color: var(--warning-color); margin-bottom: 0.5rem; font-size: 1.1rem;">⚡ Inovatif</h4>
                    <p style="margin: 0; font-size: 0.95rem;">Kami terus berinovasi untuk memberikan solusi terbaik dan fitur-fitur terkini.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Events Section -->
<section id="events" style="margin-bottom: 4rem;">
    <div class="page-header-premium" style="text-align: center; margin-bottom: 3rem;">
        <h2>🎯 Event Tersedia</h2>
        <p>Temukan event menarik dan daftar sekarang juga!</p>
    </div>

    @php
        $events = \App\Models\Event::where('status', 'active')
            ->where('registration_end', '>=', now())
            ->with(['organizer', 'category'])
            ->latest()
            ->take(6)
            ->get();
    @endphp

    @if($events->isEmpty())
    <div class="empty-state-premium">
        <div class="empty-state-premium-icon">📅</div>
        <h3>Tidak ada event yang tersedia saat ini</h3>
        <p>Silakan kembali lagi nanti untuk event terbaru!</p>
    </div>
    @else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
        @foreach($events as $event)
        <div class="premium-card" style="margin-bottom: 0; transition: all 0.3s; cursor: pointer;" onclick="window.location.href='{{ route('events.detail', $event) }}'">
            <!-- Banner Image -->
            @if($event->banner)
            <div style="position: relative; overflow: hidden; border-radius: 8px; margin: -2rem -2rem 1.5rem -2rem; height: 200px; background: #2563eb;">
                <img src="{{ Storage::url($event->banner) }}" alt="{{ $event->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                @if($event->getAvailableSlots() > 0)
                <span style="position: absolute; top: 10px; left: 10px; background: #16a34a; color: white; padding: 0.4rem 0.8rem; border-radius: 6px; font-size: 0.8rem; font-weight: bold;">✓ Tersedia</span>
                @else
                <span style="position: absolute; top: 10px; left: 10px; background: #dc2626; color: white; padding: 0.4rem 0.8rem; border-radius: 6px; font-size: 0.8rem; font-weight: bold;">✕ Penuh</span>
                @endif
            </div>
            @else
            <div style="height: 200px; background: #2563eb; display: flex; align-items: center; justify-content: center; border-radius: 8px; margin: -2rem -2rem 1.5rem -2rem; color: white; font-size: 3rem;">🎯</div>
            @endif

            <!-- Category Badge -->
            @if($event->category)
            <div style="margin-bottom: 1rem;">
                <span class="badge-premium badge-premium-info">{{ $event->category->name }}</span>
            </div>
            @endif

            <!-- Event Title -->
            <h3 style="margin-bottom: 1rem; font-size: 1.3rem; font-weight: 700; color: var(--dark); line-height: 1.3;">
                {{ $event->name }}
            </h3>

            <!-- Description -->
            <p style="color: var(--gray); font-size: 0.95rem; margin-bottom: 1.5rem; line-height: 1.6; height: 3em; overflow: hidden;">
                {{ Str::limit($event->description, 100) }}
            </p>

            <!-- Event Info -->
            <div style="background: #f1f5f9; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem; border: 1px solid #e5e7eb;">
                <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-size: 1.2rem; margin-right: 0.5rem;">👤</span>
                    <strong>Organizer:</strong>&nbsp;{{ $event->organizer->name }}
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-size: 1.2rem; margin-right: 0.5rem;">💰</span>
                    <strong>Harga:</strong>&nbsp;<span style="color: #16a34a; font-weight: bold;">Rp {{ number_format($event->price, 0, ',', '.') }}</span>
                </div>
                <div style="display: flex; align-items: center;">
                    <span style="font-size: 1.2rem; margin-right: 0.5rem;">👥</span>
                    <strong>Kuota:</strong>&nbsp;
                    @if($event->getAvailableSlots() > 0)
                    <span style="color: #16a34a; font-weight: bold;">{{ $event->getAvailableSlots() }} / {{ $event->quota }} tersisa</span>
                    @else
                    <span style="color: #dc2626; font-weight: bold;">Penuh</span>
                    @endif
                </div>
            </div>

            <!-- Action Button -->
            <a href="{{ route('events.detail', $event) }}" class="btn-premium btn-premium-primary" style="width: 100%; text-align: center; padding: 0.9rem; font-size: 1rem; text-decoration: none;">
                👁️ Lihat Detail & Daftar
            </a>
        </div>
        @endforeach
    </div>

    <div style="text-align: center; margin-top: 2rem;">
        <a href="{{ route('events.public') }}" class="btn-premium btn-premium-outline" style="padding: 1rem 2.5rem; font-size: 1.1rem; text-decoration: none;">
            Lihat Semua Event →
        </a>
    </div>
    @endif
</section>

<!-- CTA Section -->
<section style="background: #2563eb; color: white; padding: 3rem 2rem; text-align: center; border-radius: 8px; margin-bottom: 3rem;">
    <h2 style="font-size: 2rem; font-weight: 700; margin-bottom: 1rem;">Siap Memulai Event Anda?</h2>
    <p style="font-size: 1.2rem; margin-bottom: 2rem; opacity: 0.95;">Daftar sekarang dan kelola event kompetisi Anda dengan mudah dan profesional</p>
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="{{ route('register') }}" class="btn-premium" style="background: white; color: #2563eb; padding: 1rem 2.5rem; font-size: 1.1rem; text-decoration: none; border: none;">
            🚀 Daftar Sekarang
        </a>
        <a href="{{ route('login') }}" class="btn-premium" style="background: rgba(255,255,255,0.2); color: white; padding: 1rem 2.5rem; font-size: 1.1rem; text-decoration: none; border: 2px solid white;">
            🔐 Masuk ke Akun
        </a>
    </div>
</section>

<!-- Contact Section -->
<section style="margin-bottom: 2rem;">
    <div class="premium-card">
        <div class="premium-card-header">
            <h2 class="premium-card-title">Hubungi Kami</h2>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
            <div>
                <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">📧 Email</h4>
                <p style="margin: 0; color: var(--gray);">info@jurionline.com</p>
            </div>
            <div>
                <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">🌐 Website</h4>
                <p style="margin: 0; color: var(--gray);">www.jurionline.com</p>
            </div>
            <div>
                <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">💬 Support</h4>
                <p style="margin: 0; color: var(--gray);">support@jurionline.com</p>
            </div>
        </div>
    </div>
</section>

<style>
    section {
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .premium-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 768px) {
        section h1 {
            font-size: 2rem !important;
        }
        
        section p {
            font-size: 1rem !important;
        }
    }
</style>
@endsection

