@echo off
echo ========================================
echo    Run Database Migration & Seeding
echo ========================================
echo.
echo This will:
echo - Create all database tables
echo - Seed demo users with 5 roles
echo - Seed 4 event categories
echo.
echo Make sure you have:
echo 1. Created the database: CREATE DATABASE jurionline;
echo 2. Configured .env with correct database credentials
echo.
pause

echo.
echo Running migrations...
call php artisan migrate --seed

if errorlevel 1 (
    echo.
    echo ========================================
    echo    ERROR: Migration failed!
    echo ========================================
    echo.
    echo Please check:
    echo - Database exists: jurionline
    echo - Database credentials in .env are correct
    echo - MySQL server is running
    echo.
    pause
) else (
    echo.
    echo ========================================
    echo    Migration Complete!
    echo ========================================
    echo.
    echo Demo accounts created:
    echo - owner@example.com / password (Admin Owner)
    echo - admin@example.com / password (Admin)
    echo - organizer@example.com / password (Organizer)
    echo - peserta@example.com / password (Peserta)
    echo - juri@example.com / password (Juri)
    echo.
    echo You can now run: php artisan serve
    echo.
    pause
)
