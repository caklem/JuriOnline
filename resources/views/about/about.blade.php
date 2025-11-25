@extends('layouts.app')

@section('title', 'Tentang JuriOnline')

@section('content')
<div class="page-header-premium">
    <h1>Tentang JuriOnline</h1>
    <p>Platform Penilaian Digital untuk Event Kompetisi</p>
</div>

<div class="premium-card">
    <div class="premium-card-header">
        <h2 class="premium-card-title">Apa itu JuriOnline?</h2>
    </div>
    
    <div style="line-height: 1.8; color: var(--dark);">
        <p style="font-size: 1.1rem; margin-bottom: 1.5rem;">
            <strong>JuriOnline</strong> adalah sistem manajemen event dan penilaian digital yang dirancang untuk memudahkan penyelenggaraan kompetisi, lomba, dan acara penjurian lainnya. Platform ini menyediakan solusi lengkap mulai dari pendaftaran peserta hingga penilaian oleh juri profesional.
        </p>

        <h3 style="color: var(--primary-color); margin-top: 2rem; margin-bottom: 1rem; font-size: 1.5rem;">Fitur Utama</h3>
        
        <div style="margin-top: 1.5rem;">
            <h4 style="color: var(--dark); margin-bottom: 0.75rem; font-size: 1.2rem; display: flex; align-items: center; gap: 0.5rem;">
                <span>📋</span> Form Pendaftaran Custom
            </h4>
            <p style="margin-bottom: 1.5rem; padding-left: 2rem; color: var(--gray);">
                Buat form pendaftaran sesuai kebutuhan event Anda dengan drag & drop builder yang mudah digunakan. Kumpulkan informasi peserta yang relevan dan terstruktur.
            </p>

            <h4 style="color: var(--dark); margin-bottom: 0.75rem; font-size: 1.2rem; display: flex; align-items: center; gap: 0.5rem;">
                <span>⚖️</span> Form Penilaian Juri
            </h4>
            <p style="margin-bottom: 1.5rem; padding-left: 2rem; color: var(--gray);">
                Sistem penilaian yang fleksibel dengan kriteria yang dapat disesuaikan. Satu event dapat memiliki multiple form penilaian untuk juri yang berbeda (contoh: Form Vokal, Form Adab, Form Terbang).
            </p>

            <h4 style="color: var(--dark); margin-bottom: 0.75rem; font-size: 1.2rem; display: flex; align-items: center; gap: 0.5rem;">
                <span>👥</span> Manajemen Peserta
            </h4>
            <p style="margin-bottom: 1.5rem; padding-left: 2rem; color: var(--gray);">
                Kelola pendaftaran peserta, verifikasi data, dan tracking status dengan mudah. Sistem otomatis mengelola kuota dan periode pendaftaran.
            </p>

            <h4 style="color: var(--dark); margin-bottom: 0.75rem; font-size: 1.2rem; display: flex; align-items: center; gap: 0.5rem;">
                <span>💳</span> Verifikasi Pembayaran
            </h4>
            <p style="margin-bottom: 1.5rem; padding-left: 2rem; color: var(--gray);">
                Upload bukti pembayaran dan verifikasi oleh admin/organizer. Status pembayaran terintegrasi dengan status pendaftaran peserta.
            </p>

            <h4 style="color: var(--dark); margin-bottom: 0.75rem; font-size: 1.2rem; display: flex; align-items: center; gap: 0.5rem;">
                <span>📊</span> Rekapitulasi Nilai
            </h4>
            <p style="margin-bottom: 1.5rem; padding-left: 2rem; color: var(--gray);">
                Sistem rekapitulasi otomatis yang menggabungkan penilaian dari multiple juri dan form. Menghitung rata-rata nilai dengan akurat dan transparan.
            </p>

            <h4 style="color: var(--dark); margin-bottom: 0.75rem; font-size: 1.2rem; display: flex; align-items: center; gap: 0.5rem;">
                <span>🏆</span> Leaderboard Real-time
            </h4>
            <p style="margin-bottom: 1.5rem; padding-left: 2rem; color: var(--gray);">
                Peringkat peserta yang update secara real-time berdasarkan penilaian juri. Tampilkan hasil kompetisi dengan jelas dan profesional.
            </p>
        </div>

        <h3 style="color: var(--primary-color); margin-top: 2.5rem; margin-bottom: 1rem; font-size: 1.5rem;">Siapa yang Dapat Menggunakan JuriOnline?</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
            <div class="premium-card" style="margin-bottom: 0;">
                <h4 style="color: var(--primary-color); margin-bottom: 0.75rem; font-size: 1.1rem;">🎯 Organizer</h4>
                <p style="margin: 0; color: var(--gray); font-size: 0.95rem;">
                    Penyelenggara event yang ingin mengelola kompetisi secara profesional dengan sistem yang terintegrasi.
                </p>
            </div>
            <div class="premium-card" style="margin-bottom: 0;">
                <h4 style="color: var(--primary-color); margin-bottom: 0.75rem; font-size: 1.1rem;">⚖️ Juri</h4>
                <p style="margin: 0; color: var(--gray); font-size: 0.95rem;">
                    Juri profesional yang membutuhkan platform untuk memberikan penilaian yang terstruktur dan objektif.
                </p>
            </div>
            <div class="premium-card" style="margin-bottom: 0;">
                <h4 style="color: var(--primary-color); margin-bottom: 0.75rem; font-size: 1.1rem;">👤 Peserta</h4>
                <p style="margin: 0; color: var(--gray); font-size: 0.95rem;">
                    Peserta kompetisi yang ingin mendaftar dan tracking status pendaftaran serta hasil penilaian.
                </p>
            </div>
            <div class="premium-card" style="margin-bottom: 0;">
                <h4 style="color: var(--primary-color); margin-bottom: 0.75rem; font-size: 1.1rem;">👨‍💼 Admin</h4>
                <p style="margin: 0; color: var(--gray); font-size: 0.95rem;">
                    Administrator yang mengelola seluruh sistem, verifikasi pembayaran, dan monitoring event.
                </p>
            </div>
        </div>

        <h3 style="color: var(--primary-color); margin-top: 2.5rem; margin-bottom: 1rem; font-size: 1.5rem;">Keunggulan JuriOnline</h3>
        <ul style="margin-top: 1rem; padding-left: 1.5rem;">
            <li style="margin-bottom: 0.75rem;">✅ Sistem terintegrasi untuk seluruh proses event</li>
            <li style="margin-bottom: 0.75rem;">✅ Form builder yang mudah digunakan tanpa coding</li>
            <li style="margin-bottom: 0.75rem;">✅ Support multiple form penilaian per event</li>
            <li style="margin-bottom: 0.75rem;">✅ Rekapitulasi nilai otomatis dan akurat</li>
            <li style="margin-bottom: 0.75rem;">✅ Interface yang user-friendly dan profesional</li>
            <li style="margin-bottom: 0.75rem;">✅ Real-time updates untuk semua pengguna</li>
            <li style="margin-bottom: 0.75rem;">✅ Sistem keamanan data yang terjamin</li>
        </ul>

        <div style="background: #eff6ff; padding: 2rem; border-radius: 8px; margin-top: 2rem; border-left: 4px solid var(--primary-color);">
            <h4 style="color: var(--primary-color); margin-bottom: 0.75rem; font-size: 1.2rem;">💡 Mulai Menggunakan JuriOnline</h4>
            <p style="margin: 0; color: var(--dark);">
                Daftarkan event Anda sekarang dan rasakan kemudahan mengelola kompetisi dengan sistem digital yang profesional. 
                <a href="{{ route('register') }}" class="auth-link" style="font-weight: 600;">Daftar Sekarang</a> atau 
                <a href="{{ route('events.public') }}" class="auth-link" style="font-weight: 600;">Lihat Event Tersedia</a>.
            </p>
        </div>
    </div>
</div>
@endsection


