# JuriOnline - Installation Commands

## Step-by-Step Installation

### 1. Install Dependencies

```bash
cd c:\xampp\htdocs\JuriOnline
composer install
npm install
```

### 2. Setup Environment

```bash
# Copy environment file
copy .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Configure Database

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jurionline
DB_USERNAME=root
DB_PASSWORD=
```

Create database:

```sql
CREATE DATABASE jurionline;
```

### 4. Install Laravel Breeze (Authentication)

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
```

### 5. Run Migrations & Seeders

```bash
php artisan migrate --seed
```

This creates:

-   ✅ All database tables
-   ✅ 5 demo users with different roles
-   ✅ 4 event categories

### 6. Setup Storage

```bash
php artisan storage:link
```

### 7. Build Assets

```bash
# For production
npm run build

# For development (with hot reload)
npm run dev
```

### 8. Start Server

```bash
php artisan serve
```

Access: http://localhost:8000

## Demo Login Credentials

### Admin Owner (Full Access)

```
Email: owner@example.com
Password: password
```

### Admin (Manage Events)

```
Email: admin@example.com
Password: password
```

### Organizer (Create Events)

```
Email: organizer@example.com
Password: password
```

### Participant (Register to Events)

```
Email: peserta@example.com
Password: password
```

### Jury (Score Participants)

```
Email: juri@example.com
Password: password
```

## Troubleshooting Commands

### Clear All Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Regenerate Autoloader

```bash
composer dump-autoload
```

### Fix Storage Permissions (Windows PowerShell as Admin)

```powershell
icacls "storage" /grant Everyone:F /T
icacls "bootstrap\cache" /grant Everyone:F /T
```

### Reset Database

```bash
php artisan migrate:fresh --seed
```

## Production Optimization

```bash
# Install production dependencies only
composer install --optimize-autoloader --no-dev

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build optimized assets
npm run build
```

## Database Backup

```bash
# Export database
mysqldump -u root -p jurionline > backup.sql

# Import database
mysql -u root -p jurionline < backup.sql
```

## Useful Laravel Commands

```bash
# List all routes
php artisan route:list

# Create new migration
php artisan make:migration create_table_name

# Create new model
php artisan make:model ModelName

# Create new controller
php artisan make:controller ControllerName

# Create new seeder
php artisan make:seeder SeederName

# Check migration status
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback

# Run specific seeder
php artisan db:seed --class=SeederName
```

## Development Tips

### Enable Debug Mode (Development Only)

```env
APP_ENV=local
APP_DEBUG=true
```

### Disable Debug Mode (Production)

```env
APP_ENV=production
APP_DEBUG=false
```

### Watch Assets During Development

```bash
npm run dev
```

### Check for Errors

```bash
# Check syntax errors
php artisan about

# Check environment
php artisan env

# Check database connection
php artisan migrate:status
```

---

Need help? Check SETUP_GUIDE.md and JURIONLINE_README.md for detailed documentation.
