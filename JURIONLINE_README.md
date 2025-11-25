# JuriOnline - Sistem Pendaftaran & Penjurian Event

Sistem manajemen event berbasis web yang mendukung pendaftaran peserta, pembayaran manual, dan penilaian juri dengan 4 role berbeda.

## 🎯 Fitur Utama

### 1. **Multi-Role System**

-   **Admin Owner**: Pengawas sistem dengan akses penuh ke semua event dan transaksi
-   **Admin**: Mengelola event, memverifikasi pembayaran
-   **Organizer**: Membuat dan mengelola event sendiri, atur kuota, harga, periode
-   **Peserta**: Mendaftar event, upload bukti pembayaran, melihat status dan nilai
-   **Juri**: Memberikan penilaian (public) dan evaluasi (private)

### 2. **Event Management**

-   Buat event dengan informasi lengkap (nama, deskripsi, kategori, harga, kuota)
-   Upload banner event
-   Atur periode pendaftaran dan periode pelaksanaan event
-   Event memiliki masa aktif terbatas
-   Monitoring kuota peserta real-time

### 3. **Sistem Pendaftaran**

-   Pendaftaran dengan nomor registrasi unik
-   Status tracking: pending, verified, rejected, cancelled
-   Batasan 1 pendaftaran per user per event

### 4. **Pembayaran Manual**

-   Upload bukti transfer
-   Verifikasi oleh Admin/Organizer
-   Status pembayaran: pending, verified, rejected
-   Riwayat verifikasi dengan catatan

### 5. **Sistem Penjurian**

-   Juri memberikan nilai (0-100)
-   Public feedback: Terlihat oleh peserta
-   Private evaluation: Hanya untuk admin/organizer
-   Perhitungan nilai rata-rata otomatis
-   Leaderboard peserta

### 6. **Dashboard Role-Based**

-   Dashboard disesuaikan untuk setiap role
-   Statistik dan monitoring
-   Quick access ke fitur-fitur utama

## 📋 Requirements

-   PHP >= 8.2
-   Composer
-   MySQL/MariaDB
-   Node.js & NPM (untuk asset compilation)
-   Laravel 11

## 🚀 Instalasi

### 1. Clone atau Extract Project

```bash
cd c:\xampp\htdocs\JuriOnline
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Setup Environment

```bash
copy .env.example .env
```

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jurionline
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Setup Database

Buat database baru:

```sql
CREATE DATABASE jurionline;
```

Jalankan migrations dan seeders:

```bash
php artisan migrate --seed
```

### 6. Setup Storage

Buat symbolic link untuk storage:

```bash
php artisan storage:link
```

### 7. Compile Assets

```bash
npm run build
```

Atau untuk development:

```bash
npm run dev
```

### 8. Jalankan Server

```bash
php artisan serve
```

Akses aplikasi di: `http://localhost:8000`

## 👥 Demo Users

Setelah menjalankan seeder, Anda dapat login dengan akun berikut:

### Admin Owner

-   **Email**: owner@example.com
-   **Password**: password
-   **Akses**: Monitoring semua event dan transaksi

### Admin

-   **Email**: admin@example.com
-   **Password**: password
-   **Akses**: Kelola event, verifikasi pembayaran

### Organizer

-   **Email**: organizer@example.com
-   **Password**: password
-   **Akses**: Buat dan kelola event sendiri

### Peserta

-   **Email**: peserta@example.com
-   **Password**: password
-   **Akses**: Daftar event, upload pembayaran

### Juri

-   **Email**: juri@example.com
-   **Password**: password
-   **Akses**: Beri penilaian ke peserta

## 📁 Struktur Database

### Tabel Utama

1. **roles** - Role pengguna
2. **users** - Data pengguna dengan role_id
3. **categories** - Kategori event
4. **events** - Data event
5. **registrations** - Pendaftaran peserta
6. **payments** - Data pembayaran
7. **jury_scores** - Penilaian juri

### Relationships

-   User belongsTo Role
-   User hasMany Events (sebagai organizer)
-   User hasMany Registrations (sebagai peserta)
-   Event belongsTo User (organizer)
-   Event belongsTo Category
-   Event hasMany Registrations
-   Registration belongsTo Event
-   Registration belongsTo User
-   Registration hasOne Payment
-   Registration hasMany JuryScores
-   Payment belongsTo Registration
-   JuryScore belongsTo Registration
-   JuryScore belongsTo User (jury)

## 🔒 Middleware & Authorization

### Role Middleware

Middleware `CheckRole` digunakan untuk membatasi akses berdasarkan role:

```php
Route::middleware(['role:admin,organizer'])->group(function () {
    // Routes untuk admin dan organizer
});
```

### Authorization Checks

Di controller menggunakan method helper:

```php
if ($user->isOrganizer() && $event->organizer_id !== $user->id) {
    abort(403, 'Unauthorized action.');
}
```

## 📝 Workflow Sistem

### 1. Flow Organizer Membuat Event

1. Organizer login
2. Buat event baru dengan detail lengkap
3. Set status event ke "active"
4. Event muncul di halaman publik

### 2. Flow Peserta Mendaftar

1. Peserta browse event yang tersedia
2. Klik "Daftar" pada event yang diminati
3. Sistem generate nomor registrasi unik
4. Status: "pending"
5. Upload bukti pembayaran
6. Tunggu verifikasi

### 3. Flow Verifikasi Pembayaran

1. Admin/Organizer akses halaman verifikasi
2. Review bukti pembayaran
3. Verifikasi atau tolak dengan catatan
4. Status registrasi otomatis update
5. Peserta mendapat notifikasi (via status)

### 4. Flow Penjurian

1. Juri akses daftar event aktif
2. Pilih event dan lihat daftar peserta terverifikasi
3. Beri nilai (0-100)
4. Input public feedback (terlihat peserta)
5. Input private evaluation (hanya admin/organizer)
6. Sistem hitung nilai rata-rata otomatis

### 5. Flow Admin Owner Monitoring

1. Admin Owner login
2. Dashboard menampilkan overview semua event
3. Lihat statistik transaksi
4. Monitoring aktivitas semua organizer
5. Akses penuh ke semua data

## 🎨 Customization

### Menambah Kategori Event

Edit file `database/seeders/DatabaseSeeder.php` atau tambah via aplikasi.

### Mengubah Range Nilai

Edit validasi di `JuryScoreController`:

```php
'score' => 'required|numeric|min:0|max:100',
```

### Kustomisasi Email Template

(Future: Setup email notifications untuk verifikasi pembayaran)

## 🔧 Troubleshooting

### Error: Storage Link

```bash
php artisan storage:link
```

### Error: Permission Denied (Storage/Cache)

Windows (PowerShell as Admin):

```powershell
icacls "storage" /grant Everyone:F /T
icacls "bootstrap\cache" /grant Everyone:F /T
```

### Error: Class not found

```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Error: SQLSTATE Connection refused

-   Pastikan MySQL/MariaDB running
-   Cek konfigurasi database di `.env`
-   Test koneksi: `php artisan migrate:status`

## 📱 Browser Support

-   Chrome (recommended)
-   Firefox
-   Edge
-   Safari

## 🚀 Deployment

### Production Checklist

1. Set `APP_ENV=production` di `.env`
2. Set `APP_DEBUG=false`
3. Generate production key: `php artisan key:generate`
4. Optimize autoloader: `composer install --optimize-autoloader --no-dev`
5. Cache config: `php artisan config:cache`
6. Cache routes: `php artisan route:cache`
7. Cache views: `php artisan view:cache`
8. Build assets: `npm run build`
9. Setup proper file permissions
10. Enable HTTPS

## 📄 License

Project ini dibuat untuk keperluan pembelajaran dan dapat dimodifikasi sesuai kebutuhan.

## 👨‍💻 Support

Untuk pertanyaan atau issue, silakan hubungi developer atau buat issue report.

---

**JuriOnline** - Sistem Pendaftaran & Penjurian Event
Version 1.0.0
