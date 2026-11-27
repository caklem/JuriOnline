# Troubleshooting

## Aplikasi tidak bisa login / langsung kembali ke login

Penyebab umum:

- Cookie `jl_token` hilang/kedaluwarsa.
- `API_BASE_URL` Next.js salah.
- Laravel API tidak berjalan.
- Token di database sudah dihapus.

Cek:

```bash
curl -H "Accept: application/json" http://127.0.0.1:8082/api/public/events
```

Jika gagal, jalankan API:

```bash
php artisan serve --host=127.0.0.1 --port=8082
```

## Halaman Next.js 500 “Akses ditolak”

Artinya request server-side ke API menerima 403.

- Pastikan cookie milik role yang benar. Beberapa tab browser berbagi cookie; login admin bisa tertimpa login peserta.
- Cek log Next.js dan Laravel.
- Pastikan admin memang mengelola event tersebut.

Uji API langsung:

```bash
curl -H "Authorization: Bearer <token>" \
     -H "Accept: application/json" \
     http://127.0.0.1:8082/api/admin/dashboard
```

## 419 Page Expired (Blade)

- Session kedaluwarsa.
- `APP_KEY` berubah.
- Cookie/CSRF tidak cocok karena domain berbeda.

Solusi: login ulang, samakan origin, `php artisan optimize:clear`.

## 403 saat reset password juri

Admin Event hanya dapat mereset password akun juri yang **ia buat**. Admin Owner/global dapat mereset semua. Ini perilaku keamanan yang disengaja.

## File upload gagal / 413

- Batas `upload_max_filesize`/`post_max_size` PHP terlalu kecil.
- Batas proxy (Nginx `client_max_body_size`).
- Ukuran melebihi aturan aplikasi.

Cek:

```bash
php -i | grep -E "upload_max_filesize|post_max_size"
```

## File tidak bisa dibuka

- Pastikan file berada di disk `local` untuk dokumen privat.
- Pastikan user adalah pemilik atau admin event.
- Untuk aset publik, jalankan `php artisan storage:link`.

## Gambar/banner tidak tampil

- `APP_URL` salah sehingga URL asset menunjuk host lama.
- `public/storage` belum dibuat.
- File tidak ada di `storage/app/public`.

## QR tidak muncul

- Dependency `qrcode` belum terpasang: `cd my-app && pnpm install`.
- `NEXT_PUBLIC_APP_URL` kosong → QR memakai default `http://localhost:8081`.
- Build Next.js belum di-restart setelah perubahan env.

## Next.js build gagal

```bash
cd my-app
pnpm install --frozen-lockfile
pnpm exec tsc --noEmit
pnpm build
```

Jika error “Failed to find Server Action”, browser memakai HTML/build lama. Restart Next.js dan hard refresh.

## Test gagal karena user/token bercampur

Dalam satu test, setelah request dengan token user A lalu ingin memakai token user B, panggil:

```php
$this->app['auth']->forgetGuards();
```

Ini menghindari guard Sanctum memakai user sebelumnya.

## MySQL tidak berjalan (XAMPP)

- Jalankan MySQL dari XAMPP Control Panel.
- Cek port di `my.ini` (3306/3307) dan samakan `.env`.
- Pastikan database `jurionline` ada.

## Queue notifikasi tidak terkirim

- `QUEUE_CONNECTION=database` tetapi worker tidak berjalan.
- Jalankan `php artisan queue:work`.
- Cek tabel `jobs`/`failed_jobs`.

Untuk development cepat: `QUEUE_CONNECTION=sync`.

## Chat tidak bisa dikirim

- Chat event belum diaktifkan admin.
- Role peserta/juri tidak diizinkan mengirim.
- User bukan peserta terdaftar / juri accepted.
- Pesan kosong/terlalu panjang.

## Publikasi evaluasi ditolak

- Masih ada evaluasi `submitted` (belum direview).
- Ada permintaan revisi pending.
- Evaluasi belum `reviewed`.

## Skor tidak bisa diubah admin

Ini desain: hanya juri pemilik yang boleh mengubah skor. Gunakan **Minta Revisi**, lalu juri memperbaiki.

## Warna/tema tidak konsisten

- Beberapa halaman Blade legacy memakai CSS lama (`premium-global.css`).
- Next.js memakai token Arc/Tailwind.
- Bersihkan cache build Next.js dan hard refresh.

## Perintah diagnostik

```bash
php artisan about
php artisan route:list
php artisan migrate:status
php artisan optimize:clear
tail -n 100 storage/logs/laravel.log
cd my-app && pnpm exec tsc --noEmit
```
