# Dokumentasi JURI.ON

JURI.ON adalah platform manajemen event kompetisi dan penjurian digital. Repo ini berisi dua antarmuka yang berjalan berdampingan:

- **Backend Laravel + API** untuk autentikasi, event, pendaftaran, pembayaran, penjurian, publikasi hasil, chat, dan audit.
- **Frontend Next.js** untuk pengalaman admin, peserta, juri, dan halaman publik.
- **Blade legacy** masih tersedia sebagai fallback dan untuk beberapa halaman lama.

Dokumen ini adalah sumber utama untuk onboarding developer, operator, dan deployment.

## Daftar dokumen

| Dokumen | Isi |
|---|---|
| [architecture.md](architecture.md) | Arsitektur, request flow, struktur folder, keputusan teknis |
| [installation.md](installation.md) | Prasyarat, setup lokal, menjalankan API + Next.js, akun demo |
| [configuration.md](configuration.md) | Semua environment variable penting, storage, session, proxy |
| [database.md](database.md) | Skema data, relasi, migrasi, lifecycle data, kuota |
| [api.md](api.md) | Konvensi API, autentikasi, daftar endpoint per domain |
| [roles-permissions.md](roles-permissions.md) | Matriks peran, scoping event, aturan kepemilikan |
| [features.md](features.md) | Panduan fitur per peran dan status implementasi |
| [workflows.md](workflows.md) | Alur operasional langkah demi langkah |
| [testing.md](testing.md) | Test backend, build frontend, smoke test manual |
| [maintenance.md](maintenance.md) | Housekeeping, konvensi kode, release, dependency |
| [security.md](security.md) | Model keamanan, file privat, rate limit, audit |
| [deployment.md](deployment.md) | Checklist produksi, reverse proxy, queue, backup, rollback |
| [troubleshooting.md](troubleshooting.md) | Masalah umum dan cara diagnosis |

Dokumen lama di root (`COMPLETE_GUIDE.md`, `SETUP_GUIDE.md`, `JURIONLINE_README.md`, dan lain-lain) dipertahankan sebagai arsip. Jika berbeda dengan folder `docs/`, **dokumentasi di `docs/` yang berlaku**.

## Ringkasan produk

| Area | Kemampuan |
|---|---|
| Event | Draft, publikasi, jadwal, kuota peserta, format offline/online/hybrid, zona waktu, kontak, banner, juknis, slug publik, QR, preview, duplikasi, arsip |
| Kuota organizer | Admin Event maksimal 3 event tersimpan; dicek transaksional agar aman dari request paralel. Admin Owner/global tidak dibatasi |
| Pendaftaran | Form dinamis, field file privat, mode individu/tim, validasi domain email resmi, kuota peserta, pembayaran manual |
| Juri | Buat akun juri atau assign akun existing, password custom/acak, reset password, profil juri, kategori posisi juri |
| Form penilaian | Template bawaan + custom, builder kriteria, preview, pencarian template, snapshot form per skor |
| Penilaian | Skor per kriteria, catatan publik/privat, koreksi hanya oleh juri, permintaan revisi oleh admin, pencabutan publikasi saat revisi |
| Publikasi | Review admin, publish satuan/semua, hanya evaluasi terbit yang terlihat peserta |
| Chat | Chat event opsional, izin kirim peserta/juri, moderasi pesan, laporan, tautan WhatsApp |
| Audit | Log permintaan revisi, review, publikasi, aksi penting lain |
| Publik | Halaman event dengan slug, detail, pendaftaran, status, dan hasil evaluasi peserta |

## Versi teknologi

| Komponen | Versi |
|---|---|
| PHP | 8.2+ (lingkungan pengembangan: 8.5) |
| Laravel | 12.x |
| Laravel Sanctum | 4.x |
| Database | MySQL/MariaDB / SQLite untuk test |
| Next.js | 16.4.x |
| React | 19.3.x |
| Tailwind CSS | 4.x |
| UI | Arc UI + komponen internal |
| Package manager frontend | pnpm 10.x |
| Node.js | 20+ |

## Struktur repo singkat

```text
JuriOnline/
├── app/                 # Controller, model, middleware, service Laravel
├── bootstrap/           # Bootstrap Laravel + middleware global
├── config/              # Konfigurasi Laravel
├── database/
│   ├── migrations/      # Skema database
│   └── seeders/         # Role + akun demo
├── docs/                # Dokumentasi ini
├── public/              # Web root Laravel, aset publik, storage link
├── resources/views/     # Tampilan Blade legacy
├── routes/
│   ├── web.php          # Route Blade
│   └── api/             # Route API per domain
├── storage/app/private/ # Dokumen privat (bukti bayar, dokumen peserta)
├── storage/app/public/  # Banner, juknis, logo organizer
├── tests/Feature/       # Test integrasi Laravel
└── my-app/              # Frontend Next.js
    └── src/
        ├── app/         # Halaman App Router
        ├── components/  # Komponen admin/publik/juri/peserta
        └── lib/         # API client, navigasi, util
```

## Mulai cepat

```bash
# Backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

# Frontend
cd my-app
pnpm install
pnpm build

# Jalankan
php artisan serve --host=127.0.0.1 --port=8082
pnpm start -H 127.0.0.1 -p 3000
```

Buka aplikasi melalui reverse proxy Apache di `http://localhost:8081` (Next.js di belakangnya), atau akses Next.js langsung di `http://127.0.0.1:3000`. API berjalan di `http://127.0.0.1:8082/api`.

Detail lengkap: [installation.md](installation.md).

## Konvensi dokumentasi

- Bahasa: Indonesia, istilah teknis tetap dalam bentuk aslinya.
- Contoh kredensial selalu placeholder.
- Perubahan fitur wajib memperbarui dokumen terkait dan test.
- Jangan menulis secret, token, password produksi, atau data pribadi nyata ke repo.
