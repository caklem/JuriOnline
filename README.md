# JURI.ON

Platform manajemen event kompetisi dan penjurian digital: pendaftaran peserta, pembayaran manual, penugasan juri, penilaian digital, review & publikasi hasil, chat event, dan audit.

## Stack

- **Backend:** Laravel 12, PHP 8.2+ (dikembangkan di PHP 8.5), Sanctum
- **Frontend:** Next.js 16, React 19, Tailwind CSS 4, Arc UI
- **Database:** MySQL/MariaDB; SQLite in-memory untuk test
- **Storage:** disk privat untuk dokumen sensitif, disk publik untuk banner/juknis

## Dokumentasi

Dokumentasi lengkap ada di [`docs/`](docs/README.md):

| Dokumen | Isi |
|---|---|
| [Arsitektur](docs/architecture.md) | Request flow, struktur, keputusan teknis |
| [Instalasi](docs/installation.md) | Setup lokal dan menjalankan aplikasi |
| [Konfigurasi](docs/configuration.md) | Environment, storage, session, proxy |
| [Database](docs/database.md) | Skema, relasi, migrasi, backup |
| [API](docs/api.md) | Autentikasi dan daftar endpoint |
| [Peran & Hak Akses](docs/roles-permissions.md) | Matriks izin |
| [Fitur](docs/features.md) | Fitur per peran + status implementasi |
| [Alur Kerja](docs/workflows.md) | SOP operasional |
| [Testing](docs/testing.md) | Test, build, smoke test |
| [Maintenance](docs/maintenance.md) | Housekeeping, konvensi, release |
| [Keamanan](docs/security.md) | Kontrol dan rekomendasi |
| [Deployment](docs/deployment.md) | Produksi, proxy, queue, backup |
| [Troubleshooting](docs/troubleshooting.md) | Masalah umum |

> Dokumen lama di root (`COMPLETE_GUIDE.md`, `SETUP_GUIDE.md`, `JURIONLINE_README.md`, dll.) adalah arsip. Jika berbeda, gunakan `docs/`.

## Fitur utama

- Event draft → aktif → tutup → selesai → arsip, dengan slug publik, QR, preview, dan duplikasi.
- Kuota 3 event tersimpan untuk Admin Event, aman dari request paralel.
- Form pendaftaran dinamis, mode individu/tim, upload dokumen privat.
- Buat akun juri baru atau assign akun existing; password custom/acak, reset dan copy.
- Banyak form penilaian per event, template bawaan/custom, pencarian template, preview.
- Hanya juri pemilik yang dapat mengubah skor; admin melalui permintaan revisi.
- Review dan publikasi evaluasi; peserta hanya melihat evaluasi terbit.
- Chat event dengan moderasi dan tautan WhatsApp.
- Ekspor peserta CSV dan audit log.

## Mulai cepat

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

cd my-app
pnpm install
pnpm build

# Terminal 1 - API
php artisan serve --host=127.0.0.1 --port=8082

# Terminal 2 - frontend
cd my-app && pnpm start -H 127.0.0.1 -p 3000
```

Buka `http://127.0.0.1:3000` atau lewat reverse proxy `http://localhost:8081`.

Detail: [docs/installation.md](docs/installation.md).

## Akun demo

Password mengikuti `DEMO_PASSWORD` di `.env` (default development: `password`). Jangan dipakai di produksi.

| Peran | Email |
|---|---|
| Admin Owner | `owner@example.com` |
| Admin | `admin@example.com` |
| Admin Event | `organizer@example.com` |
| Juri | `juri@example.com` |
| Peserta | `peserta@example.com` |

## Test & build

```bash
php vendor/bin/phpunit --no-coverage
cd my-app && pnpm exec tsc --noEmit && pnpm lint && pnpm build
```

Status terakhir: **140 test / 580 assertion lulus**, build frontend sukses, lint 0 error (2 warning lama `<img>`).

## Struktur singkat

```text
app/                 # Backend Laravel
database/migrations/ # Skema
docs/                # Dokumentasi
my-app/              # Frontend Next.js
resources/views/     # Blade legacy
routes/              # web + API
tests/Feature/       # Test integrasi
```

## Keamanan

- Jangan commit `.env`, token, atau password asli.
- Dokumen peserta dan bukti bayar disimpan privat dan diakses lewat route ber-otorisasi.
- `APP_DEBUG=false` dan HTTPS wajib di produksi.
- Lihat [docs/security.md](docs/security.md).
