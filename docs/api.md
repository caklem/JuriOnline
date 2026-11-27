# API

Base URL development: `http://127.0.0.1:8082/api`

## Autentikasi

API privat memakai **Laravel Sanctum bearer token**.

```http
Authorization: Bearer <plain-text-token>
Accept: application/json
```

Token dibuat saat login/register. Next.js menyimpan token di cookie httpOnly `jl_token`; kode server mengirimkannya sebagai Bearer.

### Login

```http
POST /api/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "********"
}
```

Respons:

```json
{
  "token": "1|...",
  "user": { "id": 1, "name": "Admin", "role": "admin", "role_label": "Admin" }
}
```

### Register peserta

`POST /api/register` dengan `name`, `email`, `password`, `password_confirmation`.

### Register juri mandiri

`POST /api/register/jury` — hanya jika kebijakan membuka pendaftaran juri.

### Logout

`POST /api/logout` — menghapus token aktif.

## Konvensi respons

Sukses:

```json
{ "message": "Berhasil.", "data": [] }
```

Validasi gagal — HTTP 422:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field": ["Pesan kesalahan."]
  }
}
```

| Status | Arti |
|---|---|
| 200 | Sukses |
| 201 | Resource dibuat |
| 401 | Token tidak ada/kedaluwarsa |
| 403 | Role/scope/kepemilikan tidak sesuai |
| 404 | Resource tidak ditemukan |
| 409 | Konflik (mis. pembayaran sudah ada) |
| 422 | Validasi/aturan bisnis gagal |
| 429 | Rate limit |
| 500 | Kesalahan server |

Pagination memakai format Laravel:

```json
{
  "data": [],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
  }
}
```

## Endpoint publik

| Method | Path | Keterangan |
|---|---|---|
| GET | `/public/events` | Daftar event aktif non-arsip |
| GET | `/public/events/{id}` | Detail berdasarkan ID |
| GET | `/public/events/by-slug/{slug}` | Detail berdasarkan slug |

Event nonaktif/arsip menghasilkan 404.

## Autentikasi & profil

| Method | Path | Keterangan |
|---|---|---|
| POST | `/login` | Login (throttle 10/menit) |
| POST | `/register` | Registrasi peserta |
| POST | `/register/jury` | Registrasi juri mandiri |
| POST | `/forgot-password` | Kirim reset password |
| POST | `/reset-password` | Reset password |
| POST | `/logout` | Logout |
| GET | `/me` | User aktif + ringkasan |
| GET | `/profile` | Profil |
| PATCH | `/profile` | Update nama/email |
| PUT | `/profile/password` | Ganti password |
| DELETE | `/profile` | Hapus akun |
| PUT | `/profile/jury` | Profil juri + consent WA |
| GET | `/notifications` | Daftar notifikasi |
| POST | `/notifications/read` | Tandai dibaca |

## Admin Event / Admin Owner

Prefix `/admin`. Semua endpoint memeriksa role dan event scope.

### Dashboard & profil organizer

| Method | Path | Keterangan |
|---|---|---|
| GET | `/admin/dashboard` | Statistik + kuota event |
| GET | `/admin/organizer-profile` | Profil organizer |
| PUT/POST | `/admin/organizer-profile` | Simpan profil + logo |
| GET | `/admin/categories` | Kategori global |

### Event

| Method | Path | Keterangan |
|---|---|---|
| GET | `/admin/events` | Daftar event (`q`, `status`, pagination) |
| POST | `/admin/events` | Buat draft, cek kuota transaksional |
| GET | `/admin/events/{event}` | Detail + statistik |
| PUT/POST | `/admin/events/{event}` | Update (multipart untuk banner/juknis) |
| POST | `/admin/events/{event}/status` | Ubah status/arsip |
| POST | `/admin/events/{event}/scoring` | Buka/tutup/auto penilaian |
| POST | `/admin/events/{event}/duplicate` | Duplikasi sebagai draft |
| GET | `/admin/events/{event}/participants` | Daftar peserta |
| GET | `/admin/events/{event}/participants/export` | Unduh CSV peserta |
| GET | `/admin/events/{event}/recap` | Rekap nilai |
| GET | `/admin/events/{event}/registration-form` | Form pendaftaran |
| PUT | `/admin/events/{event}/registration-form` | Simpan form |
| GET | `/admin/events/{event}/jury-forms` | Form juri + template |
| POST | `/admin/events/{event}/jury-forms` | Simpan form juri |
| POST | `/admin/events/{event}/jury-forms/quick` | Buat form dari preset + assign |
| POST | `/admin/events/{event}/jury-forms/save-template` | Simpan template custom |
| GET | `/admin/events/{event}/jury-assignments` | Daftar assignment |
| POST | `/admin/events/{event}/jury-assignments` | Assign juri |
| DELETE | `/admin/events/{event}/jury-assignments/{assignment}` | Hapus assignment |
| POST | `/admin/events/{event}/jury-assignments/{assignment}/reset-password` | Reset password juri |
| POST | `/admin/events/{event}/jury-accounts` | Buat akun juri baru |
| GET | `/admin/events/{event}/score-revisions` | Riwayat permintaan revisi |
| GET | `/admin/events/{event}/evaluations` | Daftar evaluasi |
| POST | `/admin/events/{event}/evaluations/publish` | Publish semua yang direview |

### Peserta, pembayaran, skor

| Method | Path | Keterangan |
|---|---|---|
| GET | `/admin/registrations/{registration}` | Detail pendaftaran |
| PATCH | `/admin/registrations/{registration}/status` | Ubah status peserta |
| GET | `/admin/payments` | Pembayaran pending |
| GET | `/admin/payments/all` | Semua pembayaran |
| GET | `/admin/payments/{payment}/proof` | Unduh bukti (privat) |
| POST | `/admin/payments/{payment}/verify` | Verifikasi/tolak |
| POST | `/admin/scores/{juryScore}/revision-request` | Minta revisi ke juri |
| POST | `/admin/scores/{juryScore}/review` | Tandai review/perlu revisi |
| POST | `/admin/scores/{juryScore}/publish` | Terbitkan evaluasi |

### Chat

| Method | Path | Keterangan |
|---|---|---|
| GET | `/admin/events/{event}/chat-settings` | Pengaturan chat |
| PUT/POST | `/admin/events/{event}/chat-settings` | Simpan pengaturan |
| GET | `/admin/events/{event}/chat/messages` | Semua pesan + status |
| POST | `/admin/events/{event}/chat/messages` | Kirim sebagai panitia |
| POST | `/admin/messages/{message}/hide` | Sembunyikan pesan |
| POST | `/admin/messages/{message}/unhide` | Tampilkan kembali |

## Juri

Prefix `/jury`.

| Method | Path | Keterangan |
|---|---|---|
| GET | `/jury/invitations` | Undangan pending |
| POST | `/jury/invitations/{assignment}/accept` | Terima undangan |
| POST | `/jury/invitations/{assignment}/decline` | Tolak |
| GET | `/jury/events` | Event yang dinilai |
| GET | `/jury/events/{event}/participants` | Peserta + progres |
| GET | `/jury/events/{event}/leaderboard` | Leaderboard |
| GET | `/jury/events/{event}/form` | Form penilaian yang di-assign |
| GET | `/jury/registrations/{registration}/form` | Form untuk peserta |
| POST | `/jury/registrations/{registration}/score` | Simpan skor baru |
| GET | `/jury/scores/{juryScore}` | Detail skor |
| PATCH | `/jury/scores/{juryScore}` | Edit skor sendiri |
| GET | `/jury/revisions` | Permintaan revisi pending |
| POST | `/jury/revisions/{revision}/reject` | Tolak revisi + alasan |

**Aturan skor:** hanya juri pemilik skor yang boleh PATCH. Admin tidak dapat mengubah skor langsung.

## Peserta

| Method | Path | Keterangan |
|---|---|---|
| GET | `/registrations` | Pendaftaran milik user |
| GET | `/registrations/{registration}` | Detail |
| GET | `/registrations/{registration}/results` | Hasil evaluasi terbit |
| GET | `/registrations/{registration}/files/{file}` | Unduh dokumen privat |
| GET | `/registrations/{registration}/payment` | Info pembayaran |
| POST | `/registrations/{registration}/payment` | Upload bukti (throttle 10/menit) |
| GET | `/registrations/{registration}/payment/proof` | Lihat bukti |
| GET | `/events/{event}/registration-form` | Form pendaftaran publik + status |
| POST | `/events/{event}/register` | Daftar event (throttle 10/menit) |
| GET | `/events/{event}/chat` | Chat event |
| POST | `/events/{event}/chat` | Kirim pesan (throttle 30/menit) |
| POST | `/messages/{message}/report` | Laporkan pesan |

## Admin Owner

Prefix `/owner`.

| Method | Path | Keterangan |
|---|---|---|
| GET | `/owner/users` | Daftar pengguna |
| POST | `/owner/users` | Buat pengguna |
| PATCH | `/owner/users/{user}/role` | Ubah role |
| PATCH | `/owner/users/{user}/scope` | Ubah scope event |
| PATCH | `/owner/users/{user}/status` | Aktif/nonaktif |
| GET | `/owner/scores` | Semua skor |

## Contoh request

### Buat event

```http
POST /api/admin/events
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "Festival Inovasi 2027",
  "description": "Kompetisi inovasi pelajar.",
  "format": "hybrid",
  "timezone": "Asia/Jakarta",
  "participation_mode": "team",
  "team_min_members": 2,
  "team_max_members": 4,
  "price": 50000,
  "quota": 100,
  "registration_start": "2027-01-01",
  "registration_end": "2027-02-01",
  "event_date": "2027-02-10",
  "event_start": "2027-02-10",
  "event_end": "2027-02-11"
}
```

### Permintaan revisi skor

```http
POST /api/admin/scores/12/revision-request
Authorization: Bearer <token>
Content-Type: application/json

{
  "reason": "Nilai kreativitas tidak konsisten dengan rubrik."
}
```

### Publish evaluasi

```http
POST /api/admin/scores/12/publish
Authorization: Bearer <token>
```

## Rate limit

| Aksi | Batas |
|---|---|
| Login | 10/menit |
| Upload bukti pembayaran | 10/menit |
| Kirim chat | 30/menit |
| Lapor pesan | 10/menit |
| Register event | 10/menit |

## Catatan integrasi

- Selalu kirim `Accept: application/json`.
- Untuk multipart (banner/juknis), gunakan `POST` ke endpoint update event; Laravel tidak mem-parse multipart pada `PUT` murni.
- Jangan menyimpan token di localStorage; gunakan cookie httpOnly.
- Endpoint download mengembalikan file, bukan JSON.
