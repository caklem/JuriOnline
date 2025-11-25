# 🎯 SISTEM JURIONLINE - COMPLETE OVERVIEW

## 📋 Deskripsi Sistem

**JuriOnline** adalah sistem manajemen event berbasis web yang mendukung:

-   Pendaftaran peserta dengan pembayaran manual
-   Verifikasi pembayaran oleh admin/organizer
-   Penjurian dengan feedback public dan private
-   Multi-role access control
-   Event dengan masa aktif terbatas

---

## 👥 ROLE SYSTEM - 5 ROLES

### 1. 🔱 Admin Owner (Super Administrator)

**Fungsi**: Pengawas sistem dengan akses penuh

**Hak Akses**:

-   ✅ Monitoring semua event dari semua organizer
-   ✅ Melihat semua transaksi pembayaran
-   ✅ Statistik lengkap sistem (total event, revenue, dll)
-   ✅ Akses ke semua fitur tanpa batasan
-   ✅ Dapat melakukan semua fungsi admin

**Use Case**:

-   Monitor kesehatan sistem
-   Oversight semua organizer
-   Tracking revenue total
-   Audit trail

### 2. 👨‍💼 Admin

**Fungsi**: Administrator yang mengelola sistem

**Hak Akses**:

-   ✅ Kelola semua event
-   ✅ Verifikasi pembayaran untuk semua event
-   ✅ Lihat semua pendaftaran
-   ✅ Moderasi konten

**Use Case**:

-   Approve/reject event
-   Verifikasi pembayaran
-   Handle dispute
-   Moderasi sistem

### 3. 🎪 Organizer (Event Organizer)

**Fungsi**: Pembuat dan pengelola event

**Hak Akses**:

-   ✅ Buat event baru
-   ✅ Atur kuota peserta
-   ✅ Set harga pendaftaran
-   ✅ Tentukan periode pendaftaran
-   ✅ Tentukan periode pelaksanaan event
-   ✅ Upload banner event
-   ✅ Kelola pendaftaran event sendiri
-   ✅ Verifikasi pembayaran untuk event sendiri
-   ✅ Lihat statistik event sendiri

**Batasan**:

-   ❌ Tidak bisa mengakses event organizer lain
-   ❌ Tidak bisa verifikasi pembayaran event lain

**Use Case**:

-   Buat workshop/seminar
-   Kelola peserta
-   Verifikasi pembayaran peserta
-   Monitor kuota

### 4. 👤 Peserta (Participant)

**Fungsi**: Pendaftar event

**Hak Akses**:

-   ✅ Browse event tersedia
-   ✅ Daftar event (1 user = 1 event)
-   ✅ Upload bukti pembayaran
-   ✅ Tracking status pendaftaran
-   ✅ Lihat nilai dan feedback dari juri
-   ✅ Dashboard pendaftaran pribadi

**Batasan**:

-   ❌ Tidak bisa daftar 2x di event yang sama
-   ❌ Tidak bisa daftar jika kuota penuh

**Use Case**:

-   Cari event menarik
-   Daftar dan bayar
-   Monitor status verifikasi
-   Lihat hasil penilaian

### 5. 🎓 Juri (Jury)

**Fungsi**: Penilai peserta event

**Hak Akses**:

-   ✅ Lihat daftar event aktif
-   ✅ Lihat daftar peserta terverifikasi
-   ✅ Beri nilai (0-100)
-   ✅ Input public feedback (terlihat peserta)
-   ✅ Input private evaluation (hanya admin/organizer)
-   ✅ Edit penilaian yang sudah dibuat
-   ✅ Lihat leaderboard

**Batasan**:

-   ❌ Hanya bisa nilai peserta yang sudah verified
-   ❌ 1 juri hanya bisa nilai 1x per peserta

**Use Case**:

-   Evaluasi peserta kompetisi
-   Beri feedback konstruktif
-   Beri catatan internal ke organizer

---

## 🔄 WORKFLOW SISTEM

### A. Event Creation Flow (Organizer)

```
1. Organizer login
2. Klik "Buat Event Baru"
3. Isi form:
   - Nama event
   - Kategori (Kompetisi/Workshop/Seminar/Webinar)
   - Deskripsi
   - Harga (Rp)
   - Kuota peserta
   - Tanggal mulai & akhir pendaftaran
   - Tanggal mulai & akhir event
   - Lokasi (opsional)
   - Banner (opsional, JPG/PNG max 2MB)
4. Submit → Event dibuat dengan status "draft"
5. Edit event → Ubah status ke "active"
6. Event muncul di halaman public
```

### B. Registration Flow (Peserta)

```
1. Peserta browse event di halaman public
2. Klik event yang menarik
3. Lihat detail event:
   - Deskripsi
   - Harga
   - Kuota tersisa
   - Periode pendaftaran
   - Lokasi
4. Klik "Daftar Event Ini"
5. Sistem generate nomor registrasi unik
   Format: REG-YYYYMMDD-XXXX-RAND
   Contoh: REG-20241109-0001-A7F2
6. Status: "pending"
7. Redirect ke halaman detail pendaftaran
8. Upload bukti transfer (JPG/PNG/PDF max 2MB)
9. Tunggu verifikasi
```

### C. Payment Verification Flow (Admin/Organizer)

```
1. Login sebagai admin atau organizer
2. Klik "Verifikasi Pembayaran" di navbar
3. Lihat daftar pembayaran pending:
   - No. registrasi
   - Nama peserta
   - Nama event
   - Jumlah
   - Tanggal upload
4. Klik "Lihat Bukti" → Buka file di tab baru
5. Review bukti transfer:
   - Nominal sesuai?
   - Tanggal transfer valid?
   - Rekening tujuan benar?
6. Pilih aksi:

   VERIFIKASI:
   - Klik "Verifikasi"
   - Isi catatan (opsional)
   - Submit
   - Status payment → "verified"
   - Status registration → "verified"
   - Peserta bisa dinilai juri

   TOLAK:
   - Klik "Tolak"
   - Isi alasan penolakan (wajib)
   - Submit
   - Status payment → "rejected"
   - Status registration → "rejected"
   - Peserta lihat alasan di dashboard
```

### D. Jury Scoring Flow (Juri)

```
1. Juri login
2. Klik "Penilaian" di navbar
3. Lihat daftar event aktif yang punya peserta verified
4. Pilih event
5. Lihat daftar peserta terverifikasi
6. Klik "Beri Nilai" pada peserta
7. Form penilaian:
   - Nilai (0-100, required)
   - Public Feedback (opsional, terlihat peserta)
     Contoh: "Presentasi sangat baik, keep it up!"
   - Private Evaluation (opsional, hanya admin/organizer)
     Contoh: "Perlu improvement di technical skills"
8. Submit → Nilai tersimpan
9. Sistem otomatis hitung rata-rata jika ada multiple juri
10. Peserta bisa lihat nilai & public feedback di dashboard
```

### E. Monitoring Flow (Admin Owner)

```
1. Admin Owner login
2. Dashboard menampilkan:
   - Total event (semua organizer)
   - Event aktif
   - Total pendaftaran
   - Total revenue (Rp)
   - Pembayaran pending
3. Lihat recent events (10 terbaru)
4. Lihat recent payments (10 terbaru)
5. Klik "Lihat Semua Event" → Semua event
6. Klik "Lihat Semua Pembayaran" → History lengkap
7. Klik "Verifikasi Pembayaran" → Handle pending
```

---

## 📊 STATUS TRACKING

### Event Status

| Status      | Arti                        | Kapan Digunakan                      |
| ----------- | --------------------------- | ------------------------------------ |
| `draft`     | Event belum dipublikasikan  | Saat baru dibuat                     |
| `active`    | Event aktif, bisa didaftari | Setelah di-approve                   |
| `closed`    | Pendaftaran ditutup         | Manual atau otomatis setelah tanggal |
| `completed` | Event sudah selesai         | Setelah event berlangsung            |

### Registration Status

| Status      | Arti                                  | Kapan Digunakan          |
| ----------- | ------------------------------------- | ------------------------ |
| `pending`   | Menunggu upload/verifikasi pembayaran | Setelah daftar           |
| `verified`  | Pembayaran terverifikasi, peserta sah | Setelah admin approve    |
| `rejected`  | Pembayaran ditolak                    | Admin tolak pembayaran   |
| `cancelled` | Dibatalkan                            | User atau admin batalkan |

### Payment Status

| Status     | Arti                | Kapan Digunakan      |
| ---------- | ------------------- | -------------------- |
| `pending`  | Menunggu verifikasi | Setelah upload bukti |
| `verified` | Pembayaran valid    | Admin approve        |
| `rejected` | Pembayaran ditolak  | Admin tolak          |

---

## 🗄️ DATABASE SCHEMA

### Tabel: roles

```sql
- id (PK)
- name (admin_owner, admin, organizer, participant, jury)
- display_name
- description
- timestamps
```

### Tabel: users

```sql
- id (PK)
- role_id (FK → roles)
- name
- email (unique)
- password
- timestamps
```

### Tabel: categories

```sql
- id (PK)
- name (Kompetisi, Workshop, Seminar, Webinar)
- description
- timestamps
```

### Tabel: events

```sql
- id (PK)
- organizer_id (FK → users)
- category_id (FK → categories)
- name
- description
- price (decimal 10,2)
- quota (integer)
- registered_count (integer, default 0)
- registration_start (date)
- registration_end (date)
- event_start (date)
- event_end (date)
- status (draft/active/closed/completed)
- location (nullable)
- banner (nullable)
- timestamps
```

### Tabel: registrations

```sql
- id (PK)
- event_id (FK → events)
- user_id (FK → users)
- registration_number (unique)
- status (pending/verified/rejected/cancelled)
- notes (nullable)
- registered_at (timestamp)
- timestamps
- UNIQUE(event_id, user_id) ← Prevent duplicate
```

### Tabel: payments

```sql
- id (PK)
- registration_id (FK → registrations)
- amount (decimal 10,2)
- proof_file (path to uploaded file)
- status (pending/verified/rejected)
- verified_by (FK → users, nullable)
- verified_at (timestamp, nullable)
- verification_notes (nullable)
- timestamps
```

### Tabel: jury_scores

```sql
- id (PK)
- registration_id (FK → registrations)
- jury_id (FK → users)
- score (decimal 5,2, range 0-100)
- public_feedback (nullable, terlihat peserta)
- private_evaluation (nullable, hanya admin/organizer)
- timestamps
- UNIQUE(registration_id, jury_id) ← 1 juri 1x per peserta
```

---

## 🔒 AUTHORIZATION MATRIX

| Feature                    | Admin Owner | Admin | Organizer  | Participant | Jury        |
| -------------------------- | ----------- | ----- | ---------- | ----------- | ----------- |
| Browse public events       | ✅          | ✅    | ✅         | ✅          | ✅          |
| Create event               | ✅          | ✅    | ✅         | ❌          | ❌          |
| Edit own event             | ✅          | ✅    | ✅         | ❌          | ❌          |
| Edit any event             | ✅          | ✅    | ❌         | ❌          | ❌          |
| Delete event               | ✅          | ✅    | Own only   | ❌          | ❌          |
| Register to event          | ❌          | ❌    | ❌         | ✅          | ❌          |
| Upload payment             | ❌          | ❌    | ❌         | ✅          | ❌          |
| Verify payment (own event) | ✅          | ✅    | ✅         | ❌          | ❌          |
| Verify payment (any event) | ✅          | ✅    | ❌         | ❌          | ❌          |
| Give score                 | ✅          | ✅    | ❌         | ❌          | ✅          |
| View all events            | ✅          | ✅    | Own only   | Public only | Active only |
| View all payments          | ✅          | ✅    | Own events | Own only    | ❌          |
| View private evaluation    | ✅          | ✅    | Own events | ❌          | Own only    |
| View statistics            | ✅          | ✅    | Own only   | Own only    | Own only    |

---

## 📁 FILE STRUCTURE

```
JuriOnline/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php       (Dashboard semua role)
│   │   │   ├── EventController.php           (CRUD event)
│   │   │   ├── RegistrationController.php    (Pendaftaran)
│   │   │   ├── PaymentController.php         (Payment & verifikasi)
│   │   │   └── JuryScoreController.php       (Penjurian)
│   │   └── Middleware/
│   │       └── CheckRole.php                 (Role middleware)
│   └── Models/
│       ├── Role.php                          (Model role)
│       ├── User.php                          (Model user + helpers)
│       ├── Category.php                      (Model kategori)
│       ├── Event.php                         (Model event + helpers)
│       ├── Registration.php                  (Model pendaftaran)
│       ├── Payment.php                       (Model pembayaran)
│       └── JuryScore.php                     (Model penilaian)
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_roles_table.php
│   │   ├── 2024_01_01_000002_create_categories_table.php
│   │   ├── 2024_01_01_000003_create_events_table.php
│   │   ├── 2024_01_01_000004_create_registrations_table.php
│   │   ├── 2024_01_01_000005_create_payments_table.php
│   │   └── 2024_01_01_000006_create_jury_scores_table.php
│   └── seeders/
│       ├── RoleSeeder.php                    (Seed 5 roles)
│       └── DatabaseSeeder.php                (Seed users + categories)
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php                     (Main layout)
│   ├── dashboard/
│   │   ├── admin-owner.blade.php
│   │   ├── admin.blade.php
│   │   ├── organizer.blade.php
│   │   ├── participant.blade.php
│   │   └── jury.blade.php
│   ├── events/
│   │   ├── public.blade.php                  (Browse events)
│   │   ├── detail.blade.php                  (Event detail + register)
│   │   ├── index.blade.php                   (Manage events)
│   │   ├── show.blade.php                    (Event detail organizer)
│   │   └── create.blade.php                  (Create event form)
│   ├── registrations/
│   │   └── show.blade.php                    (Detail pendaftaran)
│   └── payments/
│       ├── create.blade.php                  (Upload bukti)
│       └── pending.blade.php                 (Verifikasi)
├── routes/
│   ├── web.php                               (All routes)
│   └── auth.php                              (Auth routes placeholder)
├── storage/app/public/
│   ├── banners/                              (Event banners)
│   └── payment-proofs/                       (Bukti transfer)
├── JURIONLINE_README.md                       (Dokumentasi lengkap)
├── SETUP_GUIDE.md                            (Panduan setup)
├── INSTALL.md                                (Installation commands)
├── PROJECT_SUMMARY.md                        (Summary & checklist)
├── COMPLETE_GUIDE.md                         (File ini - Overview lengkap)
├── setup.bat                                 (Windows setup script)
├── migrate.bat                               (Database migration script)
└── start.bat                                 (Start server script)
```

---

## 🚀 INSTALLATION QUICKSTART

### Method 1: Automatic (Windows)

```batch
# Double-click files:
1. setup.bat          → Install dependencies
2. migrate.bat        → Setup database
3. start.bat          → Run server
```

### Method 2: Manual

```bash
# 1. Install dependencies
composer install
npm install

# 2. Setup environment
copy .env.example .env
php artisan key:generate

# 3. Install authentication
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install && npm run build

# 4. Configure database in .env
# Create database: jurionline

# 5. Run migrations
php artisan migrate --seed

# 6. Setup storage
php artisan storage:link

# 7. Start server
php artisan serve
```

---

## 🎮 DEMO SCENARIO

### Scenario 1: Workshop Coding

```
1. Login as organizer@example.com
2. Create event:
   - Name: "Workshop Laravel Advanced"
   - Category: Workshop
   - Price: Rp 250,000
   - Quota: 30
   - Registration: 2024-11-10 to 2024-11-20
   - Event: 2024-11-25 to 2024-11-26
3. Set status → active
4. Logout

5. Login as peserta@example.com
6. Browse events
7. Register for "Workshop Laravel Advanced"
8. Get registration number: REG-20241109-0001-X7A2
9. Upload payment proof (fake screenshot)
10. Logout

11. Login as organizer@example.com
12. Go to "Verifikasi Pembayaran"
13. See pending payment from peserta
14. Click "Lihat Bukti"
15. Click "Verifikasi" → Approve
16. Logout

17. Login as juri@example.com
18. Go to "Penilaian"
19. Select "Workshop Laravel Advanced"
20. See "Peserta Demo" (verified)
21. Give score:
    - Score: 85
    - Public: "Great presentation skills!"
    - Private: "Needs more technical depth"
22. Submit
23. Logout

24. Login as peserta@example.com
25. Go to dashboard
26. See status: Verified
27. See score: 85
28. See feedback: "Great presentation skills!"
29. (Cannot see private evaluation)

30. Login as owner@example.com
31. Dashboard shows:
    - Total events: 1
    - Active events: 1
    - Total registrations: 1
    - Total revenue: Rp 250,000
32. Can see all events and payments
```

---

## 💡 FEATURE HIGHLIGHTS

### 1. Unique Registration Number

-   Format: `REG-YYYYMMDD-XXXX-RAND`
-   Auto-generated
-   Trackable
-   Unique per registration

### 2. Real-time Quota Tracking

-   Increments on registration
-   Shows available slots
-   Prevents over-registration
-   Lock when full

### 3. Dual Feedback System

-   **Public Feedback**: Visible to participant
    -   Motivational
    -   Constructive
    -   Encourage improvement
-   **Private Evaluation**: Only admin/organizer
    -   Internal assessment
    -   Sensitive remarks
    -   Management notes

### 4. Automatic Average Calculation

-   Multiple juri dapat nilai 1 peserta
-   Sistem hitung rata-rata otomatis
-   Update real-time
-   Leaderboard sorting

### 5. File Upload Security

-   Type validation (JPG, PNG, PDF only)
-   Size limit (2MB)
-   Stored in private storage
-   Accessed via controller

### 6. Role-based Dashboard

-   Setiap role punya dashboard berbeda
-   Statistik relevan per role
-   Quick access ke fitur utama
-   Personalized experience

### 7. Event Lifecycle Management

-   Draft → Active → Closed → Completed
-   Auto-close berdasarkan tanggal
-   Manual status change
-   Lifecycle tracking

### 8. Payment Verification Trail

-   Who verified
-   When verified
-   Verification notes
-   Audit trail

---

## 🎓 BEST PRACTICES

### For Organizers:

1. Set realistic quota
2. Clear event description
3. Upload attractive banner
4. Quick payment verification
5. Monitor participant progress

### For Participants:

1. Upload clear payment proof
2. Screenshot with date & amount
3. Check email for updates
4. Monitor dashboard regularly

### For Jury:

1. Fair scoring (0-100 scale)
2. Constructive public feedback
3. Honest private evaluation
4. Consistent criteria

### For Admins:

1. Regular monitoring
2. Quick payment verification
3. Handle disputes fairly
4. Backup database regularly

### For Admin Owner:

1. Monitor system health
2. Review organizer performance
3. Check payment accuracy
4. Strategic oversight

---

## 🐛 TROUBLESHOOTING COMMON ISSUES

### Issue 1: Can't login

**Solution**: Install Laravel Breeze first

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
```

### Issue 2: Storage link error

**Solution**:

```bash
php artisan storage:link
```

### Issue 3: Upload failed

**Check**:

-   File size < 2MB?
-   File type allowed?
-   Storage folder writable?

### Issue 4: Duplicate registration

**Reason**: UNIQUE constraint (event_id, user_id)
**Solution**: Each user can only register once per event

### Issue 5: Can't see uploaded image

**Solution**:

```bash
php artisan storage:link
# Check public/storage folder exists
```

---

## 📈 FUTURE ENHANCEMENTS

### Prioritas Tinggi:

1. ✨ Email notifications untuk verifikasi
2. ✨ Export laporan (Excel/PDF)
3. ✨ Search & filter events
4. ✨ Payment gateway integration

### Prioritas Medium:

5. ✨ Certificate generation
6. ✨ Event rating by participants
7. ✨ Multi-language support
8. ✨ Mobile responsive improvements

### Prioritas Rendah:

9. ✨ Chat/messaging system
10. ✨ Event recommendation engine
11. ✨ Social media sharing
12. ✨ Analytics dashboard

---

## 📞 SUPPORT & DOCUMENTATION

-   **Full Documentation**: JURIONLINE_README.md
-   **Setup Guide**: SETUP_GUIDE.md
-   **Installation**: INSTALL.md
-   **Project Summary**: PROJECT_SUMMARY.md
-   **This Guide**: COMPLETE_GUIDE.md

---

**JuriOnline v1.0.0**
Complete Event Management & Jury Scoring System
Built with Laravel 11 | Created November 9, 2025

🚀 Ready to Launch!
