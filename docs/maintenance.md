# Maintenance & Pengembangan

## Rutin harian/mingguan

| Frekuensi | Tugas |
|---|---|
| Harian | Cek error log, queue gagal, disk storage |
| Mingguan | Cek pembayaran pending, event aktif, akun nonaktif |
| Bulanan | Backup + uji restore, review akses admin, update dependency |
| Per event | Arsipkan event selesai, bersihkan draft yang tidak dipakai |

Perintah:

```bash
php artisan about
php artisan queue:failed
php artisan queue:retry all
php artisan optimize:clear
df -h
```

## Housekeeping data

- Arsip event yang selesai; arsip tetap dihitung kuota.
- Hapus draft event yang benar-benar tidak dipakai; file terkait perlu dibersihkan manual.
- Bersihkan sesi/token lama bila perlu:
  ```bash
  php artisan session:prune --hours=48
  php artisan sanctum:prune-expired --hours=24
  ```
- Audit log disimpan sesuai kebijakan retensi; jangan hapus saat investigasi.

## Menambah fitur

1. Tentukan kontrak API dan perubahan skema.
2. Buat migration baru; backfill jika perlu.
3. Implementasi backend + validasi + otorisasi.
4. Tambah test fitur.
5. Implementasi Next.js (Server Component/Action).
6. Update dokumentasi terkait (`docs/`).
7. Jalankan test, tsc, lint, build, smoke.

## Konvensi kode

### Backend

- Controller tipis; logika lintas fitur di `app/Services`.
- Validasi di controller/Form Request; jangan percaya input.
- Otorisasi eksplisit per resource.
- Gunakan transaksi + row lock untuk kuota/competition state.
- Jangan mengembalikan path file privat.

### Frontend

- Server Component untuk data awal; Client Component hanya untuk interaksi.
- Akses API lewat helper yang membawa cookie token.
- Jangan menyimpan token di localStorage.
- Gunakan komponen `kit/`, `arc/`, dan token warna yang ada.
- Form memakai Server Action; tampilkan error per field.

### Test

- Setiap bugfix diberi regression test.
- Test harus deterministik dan tidak bergantung data produksi.
- Jangan menguji teks statis yang mudah berubah.

## Release

```bash
php vendor/bin/phpunit --no-coverage
cd my-app && pnpm exec tsc --noEmit && pnpm lint && pnpm build
git status
git add ...
git commit -m "feat: ..."
git push origin main
```

Untuk produksi, ikuti [deployment.md](deployment.md).

## Dependency

```bash
composer outdated
composer audit
cd my-app && pnpm outdated && pnpm audit
```

Perbarui satu kelompok dependency, jalankan test, lalu deploy bertahap. Jangan update framework dan UI sekaligus tanpa smoke test.

## Log & observability

- Laravel: `storage/logs/laravel.log`.
- Next.js: stdout process manager.
- Queue: log worker.
- 403 tercatat dengan path/user/role.
- Audit domain: tabel `audit_logs`.

## Backup & restore

- Backup sebelum migrasi besar.
- Simpan salinan di luar server.
- Uji restore minimal sekali per kuartal.
- Pastikan `storage/app/private` ikut dibackup.

## Utang teknis yang diketahui

Lihat bagian status di [features.md](features.md): kategori per event, lifecycle status penuh, builder rubrik lanjutan, manual add peserta, UI audit, WhatsApp API, MFA, antivirus upload.
