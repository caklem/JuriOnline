# Testing & Verifikasi

## Backend

Test memakai PHPUnit + Laravel `RefreshDatabase` dengan SQLite in-memory (`phpunit.xml`), sehingga database pengembangan tidak tersentuh.

```bash
php vendor/bin/phpunit --no-coverage
```

Satu file:

```bash
php vendor/bin/phpunit tests/Feature/ScoreRevisionTest.php
```

Satu metode:

```bash
php vendor/bin/phpunit --filter test_admin_cannot_edit_jury_score_directly
```

Status terakhir: **140 test, 580 assertion — OK**.

## Cakupan test penting

| File | Fokus |
|---|---|
| `AdminEventScopeTest` | Isolasi event Admin Event vs event lain |
| `EventQuotaTest` | Kuota 3, dashboard, ekspor CSV |
| `EventPublicIdentityTest` | Slug, format, arsip, duplikasi |
| `RegistrationFileTest` | Upload privat + otorisasi unduh |
| `TeamRegistrationTest` | Validasi dan simpan tim |
| `ScoreRevisionTest` | Admin tidak bisa edit skor; revisi juri |
| `EvaluationPublicationTest` | Review/publish; privasi catatan |
| `EventChatTest` | Akses chat, kirim/lapor/moderasi |
| `JuryAssignmentFlowTest` | Password custom, existing jury, reset |
| `JuryFormTemplateApiTest` | Built-in/custom template |
| `JuryOnboardingTest` | Alur juri legacy |
| `SecurityHardeningTest` | Header, file, otorisasi |

## Frontend

```bash
cd my-app
pnpm exec tsc --noEmit
pnpm lint
pnpm build
```

Status terakhir:

- TypeScript: bersih.
- Lint: **0 error**, 2 warning lama `<img>`.
- Build: sukses.

## Smoke test manual

### Admin

1. Login admin.
2. Dashboard tampil; kuota event benar.
3. Buat event draft; cek slug dan QR.
4. Preview halaman publik.
5. Duplikasi event; salinan draft.
6. Buka form penilaian; cari template; preview; simpan template.
7. Onboarding juri:
   - Buat akun dengan password manual; cek copy.
   - Tab existing; cari juri; assign.
8. Reset password juri; copy kredensial.
9. Detail peserta; kirim permintaan revisi.
10. Review/publish evaluasi.
11. Chat: aktifkan; kirim pesan; sembunyikan.

### Juri

1. Login juri.
2. Terima undangan.
3. Buka form penilaian; isi skor.
4. Buka inbox revisi; perbaiki/tolak.
5. Coba akses skor juri lain → harus 403.

### Peserta

1. Buka event publik via slug.
2. Daftar; upload file jika ada.
3. Upload bukti pembayaran.
4. Lihat hasil evaluasi hanya setelah publish.
5. Buka chat event jika aktif.

### Publik

1. Event arsip tidak muncul di daftar.
2. Event nonaktif menghasilkan 404.
3. QR menuju URL slug yang benar.

## Menulis test baru

- Gunakan `RefreshDatabase` dan seed role.
- Gunakan `Storage::fake('local')` untuk upload.
- Gunakan `Notification::fake()` untuk notifikasi.
- Untuk berpindah user dengan Bearer token dalam satu test, panggil `$this->app['auth']->forgetGuards()` sebelum request user berikutnya (menghindari guard cache).
- Jangan menguji teks/format UI yang rapuh; uji perilaku dan kontrak API.
- Jangan memakai database produksi.

## Regression wajib saat mengubah

| Perubahan | Test minimal |
|---|---|
| Skor/revisi | `ScoreRevisionTest`, `EvaluationPublicationTest` |
| Kuota/event | `EventQuotaTest`, `EventPublicIdentityTest` |
| File peserta | `RegistrationFileTest` |
| Juri/assignment | `JuryAssignmentFlowTest`, `JuryOnboardingTest` |
| Chat | `EventChatTest` |
| Auth/role | `AdminEventScopeTest`, `SecurityHardeningTest` |

## CI yang disarankan

```yaml
# contoh ringkas
- composer install --no-interaction
- cp .env.example .env && php artisan key:generate
- php vendor/bin/phpunit --no-coverage
- cd my-app && pnpm install --frozen-lockfile
- pnpm exec tsc --noEmit
- pnpm build
```

Gunakan database service atau SQLite untuk test; jangan menyimpan `.env` produksi di CI.
