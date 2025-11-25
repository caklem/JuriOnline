# ✅ CHECKLIST INSTALASI JURIONLINE

## 📋 Pre-Installation Checklist

-   [ ] XAMPP installed (PHP 8.2+, MySQL)
-   [ ] Composer installed
-   [ ] Node.js & NPM installed
-   [ ] Git (optional, untuk version control)

---

## 🚀 Installation Steps

### Step 1: Dependencies

-   [ ] Run `composer install`
-   [ ] Run `npm install`
-   [ ] Check for errors

### Step 2: Environment Setup

-   [ ] Copy `.env.example` to `.env`
-   [ ] Edit `.env` - set database credentials
-   [ ] Run `php artisan key:generate`
-   [ ] Verify `.env` file has APP_KEY

### Step 3: Database Setup

-   [ ] Start XAMPP MySQL
-   [ ] Create database: `CREATE DATABASE jurionline;`
-   [ ] Verify database exists in phpMyAdmin
-   [ ] Check connection: `php artisan migrate:status`

### Step 4: Authentication (IMPORTANT!)

-   [ ] Run `composer require laravel/breeze --dev`
-   [ ] Run `php artisan breeze:install blade`
-   [ ] Run `npm install` (again, for Breeze assets)
-   [ ] Run `npm run build`
-   [ ] Verify auth routes exist

### Step 5: Database Migration

-   [ ] Run `php artisan migrate --seed`
-   [ ] Check 7 tables created:
    -   [ ] roles
    -   [ ] users (updated with role_id)
    -   [ ] categories
    -   [ ] events
    -   [ ] registrations
    -   [ ] payments
    -   [ ] jury_scores
-   [ ] Check 5 demo users created
-   [ ] Check 4 categories created

### Step 6: Storage Setup

-   [ ] Run `php artisan storage:link`
-   [ ] Verify `public/storage` folder exists
-   [ ] Check `storage/app/public` exists

### Step 7: Test Run

-   [ ] Run `php artisan serve`
-   [ ] Access http://localhost:8000
-   [ ] Homepage loads successfully
-   [ ] Can access login page
-   [ ] Can access register page

---

## 🧪 Testing Checklist

### Test 1: Authentication

-   [ ] Login as owner@example.com / password
-   [ ] Redirects to dashboard
-   [ ] Can see Admin Owner dashboard
-   [ ] Can logout successfully

### Test 2: Event Creation (Organizer)

-   [ ] Login as organizer@example.com / password
-   [ ] Click "Buat Event Baru"
-   [ ] Fill form with test data:
    -   Name: "Test Event"
    -   Category: Kompetisi
    -   Description: "Test description"
    -   Price: 100000
    -   Quota: 10
    -   Dates: (pick valid dates)
-   [ ] Submit → Event created
-   [ ] Event visible in "Event Saya"
-   [ ] Edit event → Change status to "active"
-   [ ] Event now visible in public page

### Test 3: Registration (Peserta)

-   [ ] Logout, login as peserta@example.com / password
-   [ ] Browse events
-   [ ] Can see "Test Event"
-   [ ] Click event detail
-   [ ] Click "Daftar Event Ini"
-   [ ] Registration created with unique number
-   [ ] See registration in "Pendaftaran Saya"

### Test 4: Payment Upload (Peserta)

-   [ ] From registration detail
-   [ ] Click "Upload Bukti Pembayaran"
-   [ ] Upload a test image (JPG/PNG)
-   [ ] Payment uploaded successfully
-   [ ] Status shows "Menunggu Verifikasi"

### Test 5: Payment Verification (Organizer)

-   [ ] Logout, login as organizer@example.com / password
-   [ ] Click "Verifikasi Pembayaran"
-   [ ] See pending payment
-   [ ] Click "Lihat Bukti" → Image opens
-   [ ] Click "Verifikasi"
-   [ ] Enter notes (optional)
-   [ ] Submit
-   [ ] Payment status → "Terverifikasi"

### Test 6: Jury Scoring (Juri)

-   [ ] Logout, login as juri@example.com / password
-   [ ] Click "Penilaian"
-   [ ] See "Test Event" in list
-   [ ] Click event
-   [ ] See verified participant
-   [ ] Click "Beri Nilai"
-   [ ] Enter score: 85
-   [ ] Enter public feedback: "Good job!"
-   [ ] Enter private evaluation: "Needs improvement"
-   [ ] Submit
-   [ ] Score saved successfully

### Test 7: View Score (Peserta)

-   [ ] Logout, login as peserta@example.com / password
-   [ ] Go to dashboard
-   [ ] See "Test Event" registration
-   [ ] Status: "Terverifikasi"
-   [ ] Score: 85
-   [ ] Can see public feedback
-   [ ] Cannot see private evaluation

### Test 8: Admin Owner Monitoring

-   [ ] Logout, login as owner@example.com / password
-   [ ] Dashboard shows statistics:
    -   [ ] Total events: 1
    -   [ ] Active events: 1
    -   [ ] Total registrations: 1
    -   [ ] Total revenue: Rp 100,000
-   [ ] Click "Lihat Semua Event" → See test event
-   [ ] Click "Lihat Semua Pembayaran" → See payment
-   [ ] All monitoring features work

### Test 9: File Upload/Download

-   [ ] Event banner displays correctly
-   [ ] Payment proof can be viewed
-   [ ] Files stored in correct folders:
    -   [ ] storage/app/public/banners/
    -   [ ] storage/app/public/payment-proofs/
-   [ ] Public access via /storage/ works

### Test 10: Authorization

-   [ ] Peserta cannot access organizer pages (403)
-   [ ] Organizer cannot access other organizer's events
-   [ ] Juri cannot give score to unverified participant
-   [ ] Role middleware working correctly

---

## 🔍 Post-Installation Checks

### Database

-   [ ] All migrations ran successfully
-   [ ] Foreign keys properly set
-   [ ] Unique constraints working
-   [ ] Seeders populated data correctly

### Files & Folders

-   [ ] storage/app/public exists
-   [ ] public/storage symlink exists
-   [ ] vendor folder complete
-   [ ] node_modules folder complete

### Configuration

-   [ ] .env file configured correctly
-   [ ] APP_KEY generated
-   [ ] DB credentials correct
-   [ ] APP_URL set (http://localhost:8000)

### Assets

-   [ ] CSS compiles correctly
-   [ ] JS compiles correctly
-   [ ] No console errors
-   [ ] Styling displays properly

### Routes

-   [ ] Run `php artisan route:list`
-   [ ] Verify all routes exist:
    -   [ ] Public routes (/, /events)
    -   [ ] Auth routes (login, register, logout)
    -   [ ] Dashboard routes (/dashboard)
    -   [ ] Event routes (/events/\*)
    -   [ ] Registration routes (/registrations/\*)
    -   [ ] Payment routes (/payments/\*)
    -   [ ] Jury routes (/jury/\*)

### Middleware

-   [ ] Auth middleware working
-   [ ] Role middleware working
-   [ ] CSRF protection active
-   [ ] Unauthorized access returns 403

---

## ⚠️ Common Issues & Solutions

### Issue: "Class 'Auth' not found"

-   [ ] Solution: Install Laravel Breeze

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
```

### Issue: "SQLSTATE[HY000] [1049] Unknown database"

-   [ ] Solution: Create database

```sql
CREATE DATABASE jurionline;
```

### Issue: "404 Not Found" on routes

-   [ ] Solution: Clear cache

```bash
php artisan route:clear
php artisan cache:clear
```

### Issue: "Storage link already exists"

-   [ ] Solution: Delete and recreate

```bash
# Windows
rmdir public\storage
php artisan storage:link
```

### Issue: "Permission denied" on storage

-   [ ] Solution: Fix permissions (Windows PowerShell as Admin)

```powershell
icacls "storage" /grant Everyone:F /T
icacls "bootstrap\cache" /grant Everyone:F /T
```

### Issue: Can't upload files

-   [ ] Check file size < 2MB
-   [ ] Check file type (JPG, PNG, PDF)
-   [ ] Check storage permissions
-   [ ] Check php.ini upload settings

### Issue: Images not displaying

-   [ ] Run `php artisan storage:link`
-   [ ] Check symlink exists: `public/storage`
-   [ ] Check file path in database
-   [ ] Try accessing directly: http://localhost:8000/storage/filename

---

## 📊 Success Criteria

### Functionality ✅

-   [ ] All 5 roles can login
-   [ ] Organizer can create events
-   [ ] Peserta can register
-   [ ] Payment upload works
-   [ ] Payment verification works
-   [ ] Jury scoring works
-   [ ] Admin Owner can monitor all

### Security ✅

-   [ ] Role-based access control working
-   [ ] Unauthorized access blocked
-   [ ] CSRF protection active
-   [ ] File upload validated
-   [ ] SQL injection protected

### User Experience ✅

-   [ ] Navigation intuitive
-   [ ] Forms validate properly
-   [ ] Success/error messages show
-   [ ] Status tracking clear
-   [ ] Dashboard informative

### Data Integrity ✅

-   [ ] No duplicate registrations
-   [ ] Unique constraints enforced
-   [ ] Foreign keys working
-   [ ] Cascading deletes proper
-   [ ] Transactions handled

---

## 🎓 Learning Outcomes

After completing this checklist, you should understand:

-   [x] Laravel project structure
-   [x] Database migrations & seeders
-   [x] Eloquent ORM & relationships
-   [x] Middleware & authorization
-   [x] File upload handling
-   [x] Blade templating
-   [x] MVC architecture
-   [x] Role-based access control

---

## 📚 Documentation Reference

-   [ ] Read JURIONLINE_README.md for full documentation
-   [ ] Read SETUP_GUIDE.md for detailed setup
-   [ ] Read INSTALL.md for commands
-   [ ] Read COMPLETE_GUIDE.md for comprehensive overview
-   [ ] Read PROJECT_SUMMARY.md for quick reference

---

## 🎉 Final Verification

-   [ ] All tests passed
-   [ ] All features working
-   [ ] No critical errors
-   [ ] Documentation reviewed
-   [ ] Ready for use!

---

## 📝 Notes

Use this space to note any issues or modifications:

```
[Your notes here]
```

---

**Checklist Version**: 1.0.0
**Date**: November 9, 2025
**Status**: [ ] Complete / [ ] In Progress / [ ] Not Started

---

## 🎯 Next Steps After Installation

1. [ ] Customize views to match your brand
2. [ ] Add email notifications
3. [ ] Setup production environment
4. [ ] Configure backup system
5. [ ] Add SSL certificate
6. [ ] Setup monitoring
7. [ ] Create user documentation
8. [ ] Train users on system

---

✅ **SYSTEM READY FOR USE!**

Once all items checked, your JuriOnline system is ready!
