# Instalasi & Menjalankan Aplikasi

## 1. Prasyarat

| Kebutuhan | Versi / catatan |
|---|---|
| PHP | 8.2+; repo dikembangkan dengan PHP 8.5 |
| Ekstensi PHP | `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `zip`, `gd`/`imagick` sesuai kebutuhan gambar |
| Composer | 2.x |
| MySQL/MariaDB | MySQL 8 atau MariaDB 10.4+; port mengikuti konfigurasi lokal |
| Node.js | 20+ |
| pnpm | 10.x (frontend Next.js) |
| Apache/Nginx | Reverse proxy untuk produksi; opsional saat development |

Lingkungan Windows + XAMPP yang dipakai repo ini:

- MySQL biasanya pada port `3307` (cek `.env`).
- Laravel API di `127.0.0.1:8082`.
- Next.js di `127.0.0.1:3000`.
- Apache di `8081` sebagai gerbang utama.

## 2. Setup backend

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Buat database:

```sql
CREATE DATABASE jurionline CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Atur koneksi database di `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jurionline
DB_USERNAME=root
DB_PASSWORD=
```

> Jika MySQL XAMPP memakai port lain (mis. 3307), ganti `DB_PORT`.

Jalankan migrasi + seeder:

```bash
php artisan migrate --seed
php artisan storage:link
```

## 3. Setup frontend

```bash
cd my-app
pnpm install
```

Buat `my-app/.env.local`:

```dotenv
API_BASE_URL=http://127.0.0.1:8082/api
NEXT_PUBLIC_APP_NAME=JURI.ON
```

Build produksi:

```bash
pnpm build
```

## 4. Menjalankan development

### Backend API

```bash
php artisan serve --host=127.0.0.1 --port=8082
```

API dapat diuji di `http://127.0.0.1:8082/api`.

### Frontend Next.js

Mode development:

```bash
cd my-app
pnpm dev
```

Mode hasil build:

```bash
cd my-app
pnpm start -H 127.0.0.1 -p 3000
```

Akses langsung: `http://127.0.0.1:3000`.

### Reverse proxy Apache (opsional)

Untuk pengalaman seperti aplikasi utama, arahkan `http://localhost:8081` ke Next.js `:3000` dan teruskan `/api`/`/storage` ke Laravel `public`/`:8082` sesuai konfigurasi server. Contoh konfigurasi produksi ada di [deployment.md](deployment.md).

### Queue worker

Notifikasi memakai queue `database`:

```bash
php artisan queue:work --tries=1
```

Untuk development cepat boleh memakai `QUEUE_CONNECTION=sync`, tetapi produksi wajib worker.

## 5. Akun demo

Seeder membuat role dan akun demo. Password mengikuti `DEMO_PASSWORD` di `.env`; jika tidak diisi, seeder membuat password acak dan menampilkannya di output.

| Peran | Email contoh |
|---|---|
| Admin Owner | `owner@example.com` |
| Admin Event / Admin | `admin@example.com` |
| Admin Event tambahan | `organizer@example.com` |
| Juri | `juri@example.com` |
| Peserta | `peserta@example.com` |

> Jangan gunakan `DEMO_PASSWORD` yang lemah di staging/produksi.

## 6. Verifikasi instalasi

```bash
php artisan about
php artisan migrate:status
php artisan route:list --path=api | tail -5
php vendor/bin/phpunit --no-coverage
cd my-app && pnpm exec tsc --noEmit && pnpm build
```

Smoke minimum:

1. Buka `/login`, masuk sebagai admin.
2. Buka `/dashboard`, pastikan statistik tampil.
3. Buka `/admin/events`, buat draft.
4. Buka halaman publik event via slug.
5. Masuk sebagai juri, cek undangan/form penilaian.
6. Masuk sebagai peserta, cek pendaftaran dan upload pembayaran.

## 7. Perintah harian

| Tujuan | Perintah |
|---|---|
| Bersihkan cache config/route/view | `php artisan optimize:clear` |
| Migrasi baru | `php artisan migrate` |
| Seed ulang | `php artisan db:seed` |
| Test backend | `php vendor/bin/phpunit` |
| Test satu file | `php vendor/bin/phpunit tests/Feature/NamaTest.php` |
| Typecheck frontend | `cd my-app && pnpm exec tsc --noEmit` |
| Lint frontend | `cd my-app && pnpm lint` |
| Build frontend | `cd my-app && pnpm build` |
| Format PHP | `vendor/bin/pint` (jika tersedia) |

## 8. Struktur akun dan akses awal

- Role dibuat oleh `RoleSeeder`.
- Admin Owner dapat mengelola pengguna global.
- Admin Event dengan `event_scope=own` dibatasi 3 event tersimpan.
- Juri dapat dibuat oleh admin event atau memakai akun juri existing.
- Peserta mendaftar sendiri melalui halaman publik.

Detail peran: [roles-permissions.md](roles-permissions.md).
