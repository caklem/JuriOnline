# JuriOnline - Sistem Pendaftaran & Penjurian Event

Sistem manajemen event berbasis web yang mendukung pendaftaran peserta, pembayaran manual, dan penilaian juri dengan 4 role: `admin_owner`, `admin`, `participant`, dan `jury`. Role `admin` memiliki data scope `all` (Admin Global) atau `own` (Admin Event).

## 🎯 Fitur Utama

### 1. **Multi-Role System**

-   **Admin Owner**: Pengawas sistem dengan akses penuh ke semua event, transaksi, dan pengguna, termasuk manajemen role serta aktivasi/nonaktivasi akun
-   **Admin**: Role pengelola event. `event_scope = all` (Admin Global) mengelola semua event; `event_scope = own` (Admin Event) hanya event yang dibuatnya. Form pendaftaran, form penilaian, juri, pembayaran, dan rekap mengikuti scope event.
-   **Peserta**: Mendaftar event, upload bukti pembayaran, melihat status dan nilai
-   **Juri**: Memberikan penilaian (public) dan evaluasi (private) hanya untuk event yang di-assign

### Matriks Role & Data Scope

| Role | Scope Data | Event | Pembayaran | Nilai Juri | Owner Area |
| --- | --- | --- | --- | --- | --- |
| `admin_owner` | Semua | Semua | Semua | Semua | Ya |
| `admin` (`event_scope=all`) | Semua event | Semua | Semua | Semua | Tidak |
| `admin` (`event_scope=own`) | Event miliknya | Miliknya | Miliknya | Event miliknya | Tidak |
| `participant` | Miliknya | Daftar/lihat | Upload bukti sendiri | Nilai sendiri | Tidak |
| `jury` | Event yang di-assign | Hanya event assignment | Tidak | Hanya assignment | Tidak |

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
-   Verifikasi oleh Admin Global atau Admin Event pemilik event
-   Status pembayaran: pending, verified, rejected
-   Riwayat verifikasi dengan catatan

### 5. **Sistem Penjurian**

-   Juri memberikan nilai (0-100)
-   Public feedback: Terlihat oleh peserta
-   Private evaluation: Hanya untuk admin yang mengelola event
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
-   Laravel 12

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

### 9. Lingkungan Laragon (setup saat ini)

Proyek ini berjalan di Laragon, bukan XAMPP:

-   Document root aplikasi: `C:\xampp\htdocs\JuriOnline\public`
-   Virtual host: `C:\laragon\etc\apache2\sites-enabled\jurionline-8081.conf`
-   URL: `http://localhost:8081`
-   PHP Laragon: 8.4.24 (FastCGI)
-   MySQL Laragon: 8.0.30 pada port `3307`
-   Database: `jurionline`

Jangan menjalankan Apache/MySQL XAMPP bersamaan dengan Laragon. Untuk produksi/development lokal, gunakan Apache + MySQL Laragon dan akses lewat port 8081.

## 👥 Demo Users

Setelah menjalankan seeder, Anda dapat login dengan akun berikut:

### Admin Owner

-   **Email**: owner@example.com
-   **Password**: password
-   **Akses**: Monitoring semua event dan transaksi, manajemen pengguna (buat akun, ubah role, aktif/nonaktif)

### Admin Global (role `admin`, `event_scope=all`)

-   **Email**: admin@example.com
-   **Password**: password
-   **Akses**: Semua event, semua pembayaran, semua nilai juri, form, dan rekap; bukan Owner Area

### Admin Event (role `admin`, `event_scope=own`)

-   **Email**: organizer@example.com
-   **Password**: password
-   **Akses**: Hanya event miliknya, pembayaran event miliknya, form, juri, rekap, dan penilaian event miliknya

### Peserta

-   **Email**: peserta@example.com
-   **Password**: password
-   **Akses**: Daftar event, upload pembayaran

### Juri

-   **Email**: juri@example.com
-   **Password**: password
-   **Akses**: Beri penilaian ke peserta

### Dua Opsi Akun Juri

#### Opsi 1 — Panitia menyiapkan akun (disarankan untuk juri non-teknis)

1. Login sebagai Admin Global atau Admin Event.
2. Buka event → **Assign Form ke Juri**.
3. Pada **Opsi 1: Panitia Membuatkan Akun Juri**, isi nama, email, dan pilih form penilaian.
4. Klik **Buat Akun + Assign**. Sistem membuat akun role `jury`, meng-assign ke form, dan menampilkan **kode akses 8 karakter satu kali**.
5. Berikan email + kode akses kepada juri. Juri login di `/login` menggunakan kode akses sebagai password, lalu mengganti password melalui menu **Profil**.

Jika email sudah terdaftar sebagai juri, akun tidak dibuat ulang; sistem langsung meng-assign. Jika email milik role lain, sistem menolak dan meminta email lain atau perubahan role melalui Admin Owner.

#### Opsi 2 — Juri mendaftar sendiri

1. Juri membuka `/register/jury` dan membuat akun dengan nama, email, dan password sendiri.
2. Panitia membuka event → **Assign Form ke Juri**, lalu memilih akun juri tersebut pada form yang sesuai.
3. Sebelum di-assign, dashboard juri menampilkan peringatan dan juri tidak dapat membuka data peserta event mana pun.

Assignment dari panitia adalah langkah persetujuan; akun juri mandiri tidak otomatis mendapat akses ke event.

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
-   User hasMany Events (sebagai admin event)
-   User hasMany Registrations (sebagai peserta)
-   Event belongsTo User (admin event)
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
Route::middleware(['role:admin,admin_owner'])->group(function () {
    // Routes untuk admin dan admin owner
});
```

### Authorization Checks

Di controller menggunakan method helper:

```php
if (!$user->canManageEvents()) {
    abort(403, 'Unauthorized action.');
}

if (!$user->canManageEvent($event)) {
    abort(403, 'Unauthorized action.');
}
```

`canManageEvents()` menentukan apakah user boleh mengakses area pengelolaan event. `canManageEvent($event)` adalah pemeriksaan data scope: Admin Global lolos untuk semua event; Admin Event hanya lolos jika `event->organizer_id === user->id`; Juri hanya untuk event yang di-assign.

### Batas Keamanan yang Diterapkan

-   Admin Event tidak bisa membuka, mengubah, atau menghapus event admin lain (403).
-   Pembayaran, pendaftaran, nilai juri, form, dan rekap selalu dicek terhadap event induknya.
-   Custom template event/juri hanya bisa diubah atau dipakai oleh pembuatnya atau Admin Global.
-   Juri hanya bisa menilai event yang di-assign dan peserta berstatus `verified`.
-   Peserta hanya bisa melihat dan membayar pendaftarannya sendiri.
-   Akun nonaktif tidak bisa login dan sesi aktifnya dihentikan oleh middleware `active`.
-   Owner Area hanya untuk `admin_owner`; Admin Global tetap mendapat 403 di `/owner/users`.

Regresi keamanan dan scope diuji di `tests/Feature/AdminEventScopeTest.php` dan `tests/Feature/PaymentTest.php`.

## 📝 Workflow Sistem

### 1. Flow Admin Event Membuat Event

1. Admin Event login
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

1. Admin Global atau Admin Event pemilik event akses halaman verifikasi
2. Review bukti pembayaran
3. Verifikasi atau tolak dengan catatan
4. Status registrasi otomatis update
5. Peserta mendapat notifikasi (via status)

### 4. Flow Penjurian

1. Juri akses daftar event aktif
2. Pilih event dan lihat daftar peserta terverifikasi
3. Beri nilai (0-100)
4. Input public feedback (terlihat peserta)
5. Input private evaluation (hanya admin pengelola event)
6. Sistem hitung nilai rata-rata otomatis

### 5. Flow Admin Owner Monitoring

1. Admin Owner login
2. Dashboard menampilkan overview semua event
3. Lihat statistik transaksi
4. Monitoring aktivitas semua admin event
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
