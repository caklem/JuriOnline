@extends('layouts.app')

@section('title', 'Company Profile - JuriOnline')

@section('content')
<div class="page-header-premium">
    <h1>Company Profile</h1>
    <p>Tentang JuriOnline - Platform Penilaian Digital Terpercaya</p>
</div>

<div class="premium-card">
    <div class="premium-card-header">
        <h2 class="premium-card-title">Tentang JuriOnline</h2>
    </div>
    
    <div style="line-height: 1.8; color: var(--dark);">
        <p style="font-size: 1.1rem; margin-bottom: 1.5rem;">
            <strong>JuriOnline</strong> adalah platform digital inovatif yang dirancang khusus untuk memudahkan proses penilaian dan manajemen event kompetisi, lomba, dan acara penjurian lainnya. Kami menyediakan solusi terintegrasi untuk mengelola seluruh siklus event, mulai dari pendaftaran peserta hingga penilaian oleh juri profesional.
        </p>

        <h3 style="color: var(--primary-color); margin-top: 2rem; margin-bottom: 1rem; font-size: 1.5rem;">Visi</h3>
        <p style="margin-bottom: 1.5rem;">
            Menjadi platform terdepan di Indonesia untuk sistem penilaian digital yang profesional, transparan, dan terpercaya, mendukung pengembangan talenta dan kompetisi berkualitas di berbagai bidang.
        </p>

        <h3 style="color: var(--primary-color); margin-top: 2rem; margin-bottom: 1rem; font-size: 1.5rem;">Misi</h3>
        <ul style="margin-bottom: 1.5rem; padding-left: 1.5rem;">
            <li style="margin-bottom: 0.75rem;">Menyediakan sistem penilaian yang akurat, transparan, dan mudah digunakan</li>
            <li style="margin-bottom: 0.75rem;">Mendukung penyelenggara event dalam mengelola kompetisi secara profesional</li>
            <li style="margin-bottom: 0.75rem;">Memfasilitasi juri dalam memberikan penilaian yang objektif dan terstruktur</li>
            <li style="margin-bottom: 0.75rem;">Memberikan pengalaman terbaik bagi peserta dalam proses pendaftaran dan tracking</li>
            <li style="margin-bottom: 0.75rem;">Mengembangkan teknologi yang terus berinovasi sesuai kebutuhan pengguna</li>
        </ul>

        <h3 style="color: var(--primary-color); margin-top: 2rem; margin-bottom: 1rem; font-size: 1.5rem;">Nilai-Nilai Kami</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
            <div style="padding: 1.5rem; background: #eff6ff; border-radius: 8px; border-left: 4px solid var(--primary-color);">
                <h4 style="color: var(--primary-color); margin-bottom: 0.5rem; font-size: 1.1rem;">🎯 Profesional</h4>
                <p style="margin: 0; font-size: 0.95rem;">Kami berkomitmen memberikan layanan profesional dengan standar tinggi dalam setiap aspek platform.</p>
            </div>
            <div style="padding: 1.5rem; background: #f0fdf4; border-radius: 8px; border-left: 4px solid var(--success-color);">
                <h4 style="color: var(--success-color); margin-bottom: 0.5rem; font-size: 1.1rem;">🔒 Transparan</h4>
                <p style="margin: 0; font-size: 0.95rem;">Transparansi dalam proses penilaian dan manajemen event adalah prioritas utama kami.</p>
            </div>
            <div style="padding: 1.5rem; background: #fffbeb; border-radius: 8px; border-left: 4px solid var(--warning-color);">
                <h4 style="color: var(--warning-color); margin-bottom: 0.5rem; font-size: 1.1rem;">⚡ Inovatif</h4>
                <p style="margin: 0; font-size: 0.95rem;">Kami terus berinovasi untuk memberikan solusi terbaik dan fitur-fitur terkini.</p>
            </div>
            <div style="padding: 1.5rem; background: #fef2f2; border-radius: 8px; border-left: 4px solid var(--danger-color);">
                <h4 style="color: var(--danger-color); margin-bottom: 0.5rem; font-size: 1.1rem;">🤝 Terpercaya</h4>
                <p style="margin: 0; font-size: 0.95rem;">Membangun kepercayaan melalui layanan yang konsisten dan dapat diandalkan.</p>
            </div>
        </div>

        <h3 style="color: var(--primary-color); margin-top: 2.5rem; margin-bottom: 1rem; font-size: 1.5rem;">Layanan Kami</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
            <div class="premium-card" style="margin-bottom: 0;">
                <h4 style="color: var(--dark); margin-bottom: 0.75rem; font-size: 1.1rem;">📋 Manajemen Event</h4>
                <p style="margin: 0; color: var(--gray); font-size: 0.95rem;">Kelola event dari awal hingga akhir dengan fitur lengkap untuk penyelenggara.</p>
            </div>
            <div class="premium-card" style="margin-bottom: 0;">
                <h4 style="color: var(--dark); margin-bottom: 0.75rem; font-size: 1.1rem;">⚖️ Sistem Penilaian</h4>
                <p style="margin: 0; color: var(--gray); font-size: 0.95rem;">Form penilaian custom yang dapat disesuaikan dengan kebutuhan event Anda.</p>
            </div>
            <div class="premium-card" style="margin-bottom: 0;">
                <h4 style="color: var(--dark); margin-bottom: 0.75rem; font-size: 1.1rem;">👥 Manajemen Peserta</h4>
                <p style="margin: 0; color: var(--gray); font-size: 0.95rem;">Tracking pendaftaran, verifikasi, dan status peserta secara real-time.</p>
            </div>
            <div class="premium-card" style="margin-bottom: 0;">
                <h4 style="color: var(--dark); margin-bottom: 0.75rem; font-size: 1.1rem;">💳 Sistem Pembayaran</h4>
                <p style="margin: 0; color: var(--gray); font-size: 0.95rem;">Verifikasi pembayaran yang terintegrasi dan mudah dikelola.</p>
            </div>
            <div class="premium-card" style="margin-bottom: 0;">
                <h4 style="color: var(--dark); margin-bottom: 0.75rem; font-size: 1.1rem;">📊 Rekapitulasi Nilai</h4>
                <p style="margin: 0; color: var(--gray); font-size: 0.95rem;">Laporan penilaian lengkap dengan peringkat dan statistik detail.</p>
            </div>
            <div class="premium-card" style="margin-bottom: 0;">
                <h4 style="color: var(--dark); margin-bottom: 0.75rem; font-size: 1.1rem;">🏆 Leaderboard</h4>
                <p style="margin: 0; color: var(--gray); font-size: 0.95rem;">Peringkat peserta yang update secara real-time berdasarkan penilaian juri.</p>
            </div>
        </div>

        <h3 style="color: var(--primary-color); margin-top: 2.5rem; margin-bottom: 1rem; font-size: 1.5rem;">Kontak Kami</h3>
        <div style="background: #f1f5f9; padding: 1.5rem; border-radius: 8px; margin-top: 1rem; border: 1px solid #e5e7eb;">
            <p style="margin: 0.5rem 0;"><strong>Email:</strong> info@jurionline.com</p>
            <p style="margin: 0.5rem 0;"><strong>Website:</strong> www.jurionline.com</p>
            <p style="margin: 0.5rem 0;"><strong>Support:</strong> support@jurionline.com</p>
        </div>
    </div>
</div>
@endsection


