# Konfigurasi

## File environment

| File | Dipakai oleh | Status git |
|---|---|---|
| `.env` | Laravel | Di-ignore |
| `.env.example` | Template Laravel | Dilacak |
| `my-app/.env.local` | Next.js | Di-ignore oleh `my-app/.gitignore` |
| `phpunit.xml` | Test Laravel | Dilacak |

Jangan commit kredensial nyata.

## Variabel Laravel penting

### Aplikasi

| Variabel | Default | Keterangan |
|---|---|---|
| `APP_NAME` | `JURI.ON` | Nama aplikasi |
| `APP_ENV` | `local` | `local`, `staging`, `production` |
| `APP_KEY` | kosong | Wajib di-generate |
| `APP_DEBUG` | `true` | **`false` untuk produksi** |
| `APP_URL` | `http://localhost:8081` | URL utama untuk link/asset |
| `APP_LOCALE` | `en` | Bahasa backend |
| `LOG_LEVEL` | `debug` | Turunkan ke `warning`/`error` di produksi |

Zona waktu aplikasi Laravel saat ini **UTC**. Tanggal event dikirim sebagai string tanggal; perhitungan “hari H” memakai `Carbon::now()` server. Jika ingin seluruh perhitungan mengikuti WIB, ubah `config/app.php` secara sadar dan sesuaikan test.

### Database

| Variabel | Contoh | Keterangan |
|---|---|---|
| `DB_CONNECTION` | `mysql` | `sqlite` untuk test |
| `DB_HOST` | `127.0.0.1` | |
| `DB_PORT` | `3306` | XAMPP repo ini kadang `3307` |
| `DB_DATABASE` | `jurionline` | |
| `DB_USERNAME` | `root` | Gunakan user khusus di produksi |
| `DB_PASSWORD` | kosong | Jangan commit |

### Session & cookie

| Variabel | Default | Keterangan |
|---|---|---|
| `SESSION_DRIVER` | `database` | Tabel `sessions` |
| `SESSION_LIFETIME` | `120` | Menit |
| `SESSION_ENCRYPT` | `true` | Enkripsi cookie session |
| `SESSION_SAME_SITE` | `lax` | |
| `SESSION_SECURE_COOKIE` | `false` | Set `true` di HTTPS |

### Storage & upload

| Variabel | Default | Keterangan |
|---|---|---|
| `FILESYSTEM_DISK` | `local` | Disk default untuk file privat |
| `MAX_UPLOAD` (server) | tergantung PHP | Batas upload PHP/Apache |

Batas aplikasi:

- Banner: JPG/PNG, maks. 2 MB.
- Juknis: PDF/DOC/DOCX/XLS/XLSX/PPT/PPTX, maks. 10 MB.
- Bukti pembayaran: JPG/PNG/PDF, maks. 2 MB.
- Dokumen peserta: PDF/JPG/PNG/DOC/DOCX, maks. 5 MB per file.

### Queue, cache, mail

| Variabel | Default | Keterangan |
|---|---|---|
| `QUEUE_CONNECTION` | `database` | Produksi butuh worker |
| `CACHE_STORE` | `database` | |
| `MAIL_MAILER` | `log` | Ganti SMTP untuk email nyata |
| `MAIL_FROM_ADDRESS` | `hello@example.com` | Ganti di produksi |

### Demo & integrasi opsional

| Variabel | Keterangan |
|---|---|
| `DEMO_PASSWORD` | Password akun demo; hapus/ganti di produksi |
| `HERDR_BASE_URL` | Integrasi opsional Kenari/Herdr |
| `HERDR_API_KEY` | **Jangan commit nilai asli**; isi lewat env server |

## Konfigurasi Next.js

`my-app/.env.local`:

```dotenv
API_BASE_URL=http://127.0.0.1:8082/api
NEXT_PUBLIC_APP_NAME=JURI.ON
```

| Variabel | Keterangan |
|---|---|
| `API_BASE_URL` | Base URL API Laravel yang dipanggil Server Component/Action |
| `NEXT_PUBLIC_APP_NAME` | Nama tampilan klien |
| `NEXT_PUBLIC_APP_URL` | Opsional; dipakai QR untuk URL absolut |

Jika API berjalan di host lain, ubah `API_BASE_URL`. Jangan memakai `localhost` dari dalam container produksi jika Laravel berada di service lain.

## Konfigurasi Sanctum

`config/sanctum.php`:

- Token expiration: `null` (tidak kedaluwarsa otomatis).
- Token prefix dapat diatur lewat `SANCTUM_TOKEN_PREFIX`.
- Next.js menyimpan token di cookie `jl_token` (httpOnly).
- API menerima `Authorization: Bearer <token>`.

Logout menghapus token aktif. Untuk produksi, pertimbangkan expiration token dan rotasi.

## Disk penyimpanan

| Disk | Root | Visibility | Pemakaian |
|---|---|---|---|
| `local` | `storage/app/private` | privat | Bukti bayar, dokumen peserta |
| `public` | `storage/app/public` | publik | Banner, juknis, logo organizer |

Jalankan:

```bash
php artisan storage:link
```

Link membuat `public/storage` menuju `storage/app/public`.

## Reverse proxy

Aplikasi utama diharapkan diakses lewat satu origin (mis. `https://domain.example`) agar cookie dan URL API konsisten. Dua pola yang didukung:

1. **Apache melayani Next.js** di `/` dan meneruskan `/api`, `/storage`, `/sanctum` ke Laravel.
2. **Nginx** sebagai reverse proxy: `/` → Next.js `:3000`, `/api` → Laravel `:8082` atau PHP-FPM.

Contoh lengkap ada di [deployment.md](deployment.md).

## Konfigurasi test

`phpunit.xml` memaksa:

- `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`
- `CACHE_STORE=array`
- `MAIL_MAILER=array`
- `QUEUE_CONNECTION=sync`
- `SESSION_DRIVER=array`

Test tidak menyentuh database pengembangan.

## Audit dan log

- Log Laravel: `storage/logs/laravel.log`.
- 403 dicatat sebagai warning berisi method, path, user, role.
- Audit domain: tabel `audit_logs` (revisi, review, publikasi).

## Konfigurasi yang tidak boleh diubah tanpa migrasi

- Nama role (`admin_owner`, `admin`, `jury`, `participant`).
- Status event yang dipakai guard (`draft`, `active`, `closed`, `completed`, arsip via `archived_at`).
- Status assignment (`pending`, `accepted`, `declined`).
- Status registrasi (`pending`, `verified`, `rejected`, `cancelled`).
- Status evaluasi (`submitted`, `reviewed`, `published`, `needs_revision`).

Perubahan nilai-nilai ini harus disertai migrasi data dan penyesuaian UI/test.
