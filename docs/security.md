# Keamanan

## Model ancaman utama

- Akses tidak sah ke event/peserta milik organizer lain.
- Juri mengubah skor milik juri lain.
- Admin mengubah skor tanpa jejak.
- Peserta mengunduh dokumen peserta lain.
- Kebocoran bukti pembayaran/dokumen privat.
- Spam chat.
- Eskalasi role/akun nonaktif.

## Kontrol yang sudah ada

### Autentikasi

- Laravel Sanctum bearer token untuk API.
- Session database untuk Blade.
- Email verification tersedia, namun tidak dipaksa untuk semua akun.
- Akun nonaktif diblokir middleware `EnsureUserIsActive`.
- Login/register/upload/chat memiliki rate limit.

### Otorisasi

- Role + event scope + kepemilikan resource.
- Admin Event hanya event sendiri.
- Juri hanya bisa menilai assignment accepted.
- Skor hanya dapat diubah juri pemilik.
- Admin harus meminta revisi; tidak ada endpoint edit skor admin.
- Peserta hanya data sendiri.
- File download memeriksa owner/admin.
- Chat memeriksa admin/peserta terdaftar/juri accepted.

### Integritas data

- Kuota event memakai transaksi + row lock.
- Kuota peserta memakai row lock saat registrasi.
- Revisi skor mencabut publikasi.
- Review/publish memblokir revisi pending.
- Form snapshot menjaga konteks historis.

### File

- Bukti bayar: `storage/app/private/payment-proofs`.
- Dokumen peserta: `storage/app/private/registration-documents/{id}`.
- Akses lewat route ber-otorisasi; bukan URL publik.
- Banner/juknis/logo di disk publik.
- Nama file asli disimpan di DB; path internal tidak diekspos.

### Header & browser

Middleware `SecurityHeaders` menambahkan header pertahanan (CSP/frame/referrer sesuai konfigurasi). Root `.htaccess` menolak akses langsung ke `.env`, `.git`, `vendor`, `storage`, dll.

### Audit

`audit_logs` mencatat:
- permintaan revisi skor,
- review evaluasi,
- publish satuan/semua.

Log server mencatat 403 untuk investigasi.

## Rekomendasi produksi

- `APP_DEBUG=false`, `APP_ENV=production`.
- HTTPS wajib; `SESSION_SECURE_COOKIE=true`.
- Ganti password database, gunakan user terbatas.
- Hapus/ganti `DEMO_PASSWORD`, nonaktifkan akun demo.
- Batasi akses `/owner/*`.
- Aktifkan backup terenkripsi.
- Gunakan queue worker dengan user sistem terpisah.
- Rotasi token/kunci bila ada indikasi bocor.
- Review dependency (`composer audit`, `pnpm audit`).
- Tambahkan antivirus/scan file upload bila menerima dokumen publik luas.
- Pertimbangkan token expiration Sanctum.
- Tambahkan MFA untuk Admin Owner (belum tersedia).
- Pastikan direktori `storage` dan `.env` tidak dapat diakses web.

## Celah yang diketahui

- Belum ada MFA.
- Belum ada halaman audit log.
- Belum ada enkripsi at-rest khusus untuk dokumen (bergantung disk/OS).
- Integrasi WhatsApp belum memakai API resmi.
- Belum ada antivirus upload.
- Token Sanctum tidak kedaluwarsa otomatis.
- Rate limit login berbasis IP, bukan akun.

## Pelaporan insiden

1. Nonaktifkan akun terkait.
2. Rotasi token/kredensial.
3. Periksa `audit_logs` dan `storage/logs/laravel.log`.
4. Backup bukti sebelum pembersihan.
5. Perbaiki akar masalah + tambah regression test.
