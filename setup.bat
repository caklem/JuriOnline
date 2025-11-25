@echo off
echo ========================================
echo    JuriOnline Quick Setup Script
echo ========================================
echo.

echo [1/8] Installing Composer dependencies...
call composer install
if errorlevel 1 goto error

echo.
echo [2/8] Installing NPM dependencies...
call npm install
if errorlevel 1 goto error

echo.
echo [3/8] Copying .env file...
if not exist .env (
    copy .env.example .env
    echo .env file created!
) else (
    echo .env already exists, skipping...
)

echo.
echo [4/8] Generating application key...
call php artisan key:generate
if errorlevel 1 goto error

echo.
echo [5/8] Installing Laravel Breeze...
call composer require laravel/breeze --dev
call php artisan breeze:install blade
if errorlevel 1 goto error

echo.
echo [6/8] Building assets...
call npm run build
if errorlevel 1 goto error

echo.
echo [7/8] Creating storage link...
call php artisan storage:link
if errorlevel 1 goto error

echo.
echo ========================================
echo    Setup Complete!
echo ========================================
echo.
echo Next steps:
echo 1. Edit .env file and configure your database
echo 2. Create database: CREATE DATABASE jurionline;
echo 3. Run: php artisan migrate --seed
echo 4. Run: php artisan serve
echo 5. Access: http://localhost:8000
echo.
echo Demo accounts:
echo - Admin Owner: owner@example.com / password
echo - Admin: admin@example.com / password
echo - Organizer: organizer@example.com / password
echo - Peserta: peserta@example.com / password
echo - Juri: juri@example.com / password
echo.
pause
goto end

:error
echo.
echo ========================================
echo    ERROR: Setup failed!
echo ========================================
echo.
echo Please check the error message above.
echo You may need to:
echo - Install Composer: https://getcomposer.org/
echo - Install Node.js: https://nodejs.org/
echo - Start XAMPP MySQL server
echo.
pause
goto end

:end
