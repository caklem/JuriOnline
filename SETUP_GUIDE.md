# Setup Instruksi JuriOnline

## Quick Start Guide

### 1. Install Authentication (Laravel Breeze)

Sistem ini memerlukan Laravel Breeze untuk authentication:

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run build
```

### 2. Jalankan Migration

```bash
php artisan migrate --seed
```

Ini akan membuat:

-   Tabel users, roles, events, registrations, payments, jury_scores, categories
-   5 demo users dengan role berbeda
-   4 kategori event

### 3. Setup Storage

```bash
php artisan storage:link
```

### 4. Jalankan Server

```bash
php artisan serve
```

Akses: http://localhost:8000

## Demo Accounts

| Role        | Email                 | Password | Access                     |
| ----------- | --------------------- | -------- | -------------------------- |
| Admin Owner | owner@example.com     | password | Full monitoring            |
| Admin       | admin@example.com     | password | Manage all events          |
| Organizer   | organizer@example.com | password | Create & manage own events |
| Peserta     | peserta@example.com   | password | Register to events         |
| Juri        | juri@example.com      | password | Score participants         |

## Struktur File Penting

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php      # Dashboard untuk semua role
│   │   ├── EventController.php          # CRUD event
│   │   ├── RegistrationController.php   # Pendaftaran peserta
│   │   ├── PaymentController.php        # Upload & verifikasi pembayaran
│   │   └── JuryScoreController.php      # Penjurian
│   └── Middleware/
│       └── CheckRole.php                # Role-based access control
├── Models/
│   ├── Role.php                         # Model role
│   ├── User.php                         # Model user + role methods
│   ├── Event.php                        # Model event + helpers
│   ├── Registration.php                 # Model pendaftaran
│   ├── Payment.php                      # Model pembayaran
│   ├── JuryScore.php                    # Model penilaian
│   └── Category.php                     # Model kategori

database/
├── migrations/
│   ├── 2024_01_01_000001_create_roles_table.php
│   ├── 2024_01_01_000002_create_categories_table.php
│   ├── 2024_01_01_000003_create_events_table.php
│   ├── 2024_01_01_000004_create_registrations_table.php
│   ├── 2024_01_01_000005_create_payments_table.php
│   └── 2024_01_01_000006_create_jury_scores_table.php
└── seeders/
    ├── RoleSeeder.php
    └── DatabaseSeeder.php

resources/views/
├── layouts/
│   └── app.blade.php                   # Main layout
├── dashboard/
│   ├── admin-owner.blade.php
│   ├── admin.blade.php
│   ├── organizer.blade.php
│   ├── participant.blade.php
│   └── jury.blade.php
├── events/
│   ├── public.blade.php               # Browse events
│   ├── detail.blade.php               # Event detail + register
│   └── create.blade.php               # Form create event
└── payments/
    ├── create.blade.php               # Upload bukti transfer
    └── pending.blade.php              # Verifikasi pembayaran

routes/
└── web.php                            # All routes with middleware
```

## Testing Workflow

### Sebagai Organizer:

1. Login sebagai organizer@example.com
2. Klik "Buat Event Baru"
3. Isi form event (nama, deskripsi, harga, kuota, tanggal)
4. Upload banner (optional)
5. Submit - event dibuat dengan status "draft"
6. Edit event dan ubah status ke "active"

### Sebagai Peserta:

1. Login sebagai peserta@example.com
2. Browse event di halaman utama
3. Klik event yang menarik
4. Klik "Daftar Event"
5. Sistem generate nomor registrasi
6. Upload bukti pembayaran
7. Tunggu verifikasi

### Sebagai Admin/Organizer (Verifikasi):

1. Login sebagai admin atau organizer event
2. Klik "Verifikasi Pembayaran"
3. Lihat daftar pembayaran pending
4. Klik "Lihat Bukti" untuk melihat file
5. Klik "Verifikasi" atau "Tolak"
6. Isi catatan (opsional untuk verifikasi, wajib untuk tolak)
7. Submit

### Sebagai Juri:

1. Login sebagai juri@example.com
2. Klik "Penilaian"
3. Pilih event
4. Lihat daftar peserta terverifikasi
5. Klik "Beri Nilai"
6. Isi nilai (0-100)
7. Isi public feedback (terlihat peserta)
8. Isi private evaluation (hanya admin/organizer)
9. Submit

### Sebagai Admin Owner:

1. Login sebagai owner@example.com
2. Dashboard menampilkan semua statistik
3. Lihat semua event dari semua organizer
4. Lihat semua transaksi pembayaran
5. Monitor seluruh sistem

## Fitur Status Tracking

### Status Event:

-   **draft**: Event belum dipublikasikan
-   **active**: Event aktif dan bisa didaftari
-   **closed**: Pendaftaran ditutup
-   **completed**: Event selesai

### Status Registrasi:

-   **pending**: Menunggu upload/verifikasi pembayaran
-   **verified**: Pembayaran terverifikasi, peserta sah
-   **rejected**: Pembayaran ditolak
-   **cancelled**: Dibatalkan peserta/admin

### Status Payment:

-   **pending**: Menunggu verifikasi
-   **verified**: Pembayaran valid
-   **rejected**: Pembayaran ditolak

## File Upload Locations

-   Event banners: `storage/app/public/banners/`
-   Payment proofs: `storage/app/public/payment-proofs/`

Public access via: `http://localhost:8000/storage/`

## Database Configuration

Default menggunakan MySQL. Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jurionline
DB_USERNAME=root
DB_PASSWORD=
```

## Common Issues

### 1. Class "Auth" not found

**Solution**: Install Laravel Breeze

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
```

### 2. Storage link not working

**Solution**:

```bash
php artisan storage:link
```

### 3. Permission denied on storage

**Solution** (Windows PowerShell as Admin):

```powershell
icacls "storage" /grant Everyone:F /T
icacls "bootstrap\cache" /grant Everyone:F /T
```

### 4. 404 on routes

**Solution**:

```bash
php artisan route:clear
php artisan cache:clear
php artisan config:clear
```

### 5. CSRF token mismatch

**Solution**: Clear browser cookies and cache

## Next Steps

1. **Install Laravel Breeze** untuk authentication lengkap
2. **Customize views** sesuai brand Anda
3. **Add email notifications** untuk verifikasi pembayaran
4. **Add export functionality** untuk laporan
5. **Add search & filter** di daftar event
6. **Add image optimization** untuk upload
7. **Add activity logs** untuk audit trail
8. **Add notification system** untuk updates

## Production Deployment

1. Set proper environment:

```env
APP_ENV=production
APP_DEBUG=false
```

2. Optimize:

```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

3. Setup proper database backups
4. Setup SSL/HTTPS
5. Configure email for notifications
6. Set up proper file permissions
7. Enable rate limiting

---

Happy coding! 🚀
