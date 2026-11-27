# Deployment

## Checklist pra-deploy

- [ ] `.env` produksi sudah diisi; `APP_DEBUG=false`, `APP_ENV=production`
- [ ] `APP_URL` HTTPS final
- [ ] Database produksi + user terbatas
- [ ] `DEMO_PASSWORD` dihapus/diganti, akun demo dinonaktifkan
- [ ] `php artisan key:generate` (hanya instalasi baru)
- [ ] `php artisan migrate --force`
- [ ] `php artisan storage:link`
- [ ] Permission `storage/` dan `bootstrap/cache/`
- [ ] Frontend `pnpm build`
- [ ] Reverse proxy `/` → Next.js, `/api` → Laravel
- [ ] Queue worker berjalan
- [ ] Backup database + storage sebelum migrasi

## Build & deploy

```bash
# backend
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# frontend
cd my-app
pnpm install --frozen-lockfile
pnpm build
```

Restart:

```bash
php artisan optimize:clear
# lalu restart PHP-FPM/Next.js/queue worker
```

Opsional:

```bash
php artisan event:cache
```

## Proses runtime

| Proses | Perintah | Catatan |
|---|---|---|
| Laravel API | PHP-FPM / `php artisan serve` (dev) | Produksi gunakan PHP-FPM |
| Next.js | `pnpm start -H 127.0.0.1 -p 3000` | Jalankan dengan process manager |
| Queue | `php artisan queue:work --tries=1` | Supervisor/systemd |
| Scheduler | cron `* * * * * php artisan schedule:run` | Jika ada task terjadwal |

Supervisor contoh:

```ini
[program:jurionline-queue]
command=php /var/www/jurionline/artisan queue:work --sleep=3 --tries=1 --max-time=3600
directory=/var/www/jurionline
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/jurionline-queue.log
```

## Nginx contoh

```nginx
server {
    listen 443 ssl http2;
    server_name jurionline.example;

    root /var/www/jurionline/public;
    index index.php;

    # Frontend Next.js
    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # API Laravel
    location /api/ {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location /sanctum/ {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location /storage/ {
        alias /var/www/jurionline/storage/app/public/;
        access_log off;
        expires 30d;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Catatan:

- Jika Next.js menangani `/events`, `/admin`, `/jury`, dll., pastikan fallback proxy tidak menangkap `/api`.
- `NEXT_PUBLIC_APP_URL` harus URL publik untuk QR.
- `API_BASE_URL` Next.js harus dapat dijangkau dari server Next.js.

## Apache contoh

VirtualHost utama dapat melayani Next.js sebagai reverse proxy, sementara PHP Laravel ditangani FastCGI:

```apache
<VirtualHost *:443>
    ServerName jurionline.example
    DocumentRoot "C:/xampp/htdocs/JuriOnline/public"

    ProxyPreserveHost On
    ProxyPass /api !
    ProxyPass / http://127.0.0.1:3000/
    ProxyPassReverse / http://127.0.0.1:3000/
</VirtualHost>
```

Pastikan modul `proxy`, `proxy_http`, dan FastCGI aktif. Jangan menaruh `DocumentRoot` di root repo; hanya `public/`.

## Permission

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
```

File `.env` hanya dibaca user web.

## Migrasi produksi

1. Backup.
2. Mode maintenance (opsional):
   ```bash
   php artisan down --retry=60
   ```
3. `php artisan migrate --force`
4. `php artisan optimize:clear && php artisan config:cache && php artisan route:cache`
5. `php artisan up`

## Rollback

- Kode: deploy commit sebelumnya, build ulang frontend.
- Database: restore backup; hindari `migrate:rollback` jika data baru sudah masuk tanpa rencana.
- File: restore `storage/` bila upload ikut berubah.

## Backup

```bash
mysqldump -u user -p jurionline | gzip > jurionline-$(date +%F).sql.gz
tar -czf storage-$(date +%F).tar.gz storage/app
```

Simpan di luar server, enkripsi, dan uji restore berkala.

## Monitoring

- Health check Laravel: `/up`.
- Log: `storage/logs/laravel.log`, log Next.js, log queue.
- Metrik: response time, error 5xx, queue length, disk usage.
- Alert untuk 403 berulang dan kegagalan upload.

## Setelah deploy

- Login admin, cek dashboard.
- Buka event publik.
- Cek QR mengarah ke domain produksi.
- Upload file uji dan pastikan tetap privat.
- Kirim chat uji.
- Cek notifikasi juri.
