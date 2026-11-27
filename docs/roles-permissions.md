# Peran & Hak Akses

## Peran

| Role | Deskripsi |
|---|---|
| `admin_owner` | Pemilik platform; akses global, manajemen pengguna, semua event |
| `admin` | Admin; `event_scope=own` = Admin Event, `event_scope=all` = admin global |
| `jury` | Juri; hanya event/form yang di-assign dan sudah accepted |
| `participant` | Peserta; event publik, pendaftaran, pembayaran, hasil sendiri |

## Scope event

| Kondisi | Kemampuan |
|---|---|
| `admin_owner` | Semua event, semua pengguna |
| `admin` + `event_scope=all` | Semua event, tanpa kuota 3 |
| `admin` + `event_scope=own` | Hanya event sendiri, kuota 3 event tersimpan |

Admin Event tidak dapat melihat/mengubah event admin lain. Pengecualian: daftar akun juri global dapat dilihat untuk kebutuhan assign (password tetap tidak dapat direset jika bukan pembuat akun).

## Matriks hak akses

| Aksi | Admin Owner | Admin global | Admin Event | Juri | Peserta | Publik |
|---|---:|---:|---:|---:|---:|---:|
| Lihat event publik | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Buat event | ✅ | ✅ | ✅ (maks. 3) | ❌ | ❌ | ❌ |
| Edit event sendiri | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Edit event orang lain | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Arsip/duplikasi event | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Kelola form pendaftaran | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Lihat peserta | ✅ | ✅ | ✅ | ✅ (accepted) | ❌ | ❌ |
| Ubah status peserta | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Verifikasi pembayaran | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Buat akun juri | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Assign juri | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Reset password juri | ✅ | ✅ | ✅ (jika pembuat) | ❌ | ❌ | ❌ |
| Buat form penilaian | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Simpan template penilaian | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Terima/tolak undangan juri | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ |
| Menilai peserta | ❌ | ❌ | ❌ | ✅ (assignment accepted) | ❌ | ❌ |
| Edit skor sendiri | ❌ | ❌ | ❌ | ✅ (scoring open) | ❌ | ❌ |
| Edit skor juri lain | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Minta revisi skor | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Review evaluasi | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Publish evaluasi | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Lihat catatan privat | ✅ | ✅ | ✅ | ✅ (milik sendiri) | ❌ | ❌ |
| Lihat hasil sendiri | ❌ | ❌ | ❌ | ❌ | ✅ (published) | ❌ |
| Daftar event | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ |
| Chat event | ✅ | ✅ | ✅ | ✅ (accepted) | ✅ (terdaftar) | ❌ |
| Moderasi chat | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Kelola pengguna global | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

## Aturan penting

### Event

- `canManageEvent()` = Admin Owner, admin global, atau organizer event.
- Admin Event hanya melihat event dengan `organizer_id` sendiri.
- Event publik = `status=active` dan `archived_at=null`.

### Kuota organizer

- Berlaku untuk `admin` dengan `event_scope=own`.
- Batas default 3 event tersimpan, termasuk draft/arsip.
- Dicek dalam transaksi dengan `SELECT ... FOR UPDATE`.
- Admin Owner/global tidak dibatasi.

### Juri

- Assignment harus `accepted` untuk mengakses peserta dan menilai.
- Form penilaian harus di-assign; jika tidak ada form, akses nilai ditolak.
- Hanya juri pemilik skor yang boleh mengubah skor.
- Juri tidak dapat menilai peserta yang belum `verified`.

### Skor & evaluasi

- Admin **tidak** dapat mengubah skor langsung.
- Admin mengirim `revision-request` dengan alasan.
- Juri memperbaiki skor → permintaan otomatis `applied`; atau menolak dengan catatan.
- Permintaan revisi pada skor terbit mencabut publikasi (`published_at=null`).
- Publikasi hanya setelah `reviewed` dan tidak ada revisi pending.
- Peserta hanya menerima evaluasi `published`; `private_evaluation` tidak pernah dikirim.

### File

- Peserta hanya dapat mengunduh file registrasi sendiri.
- Admin hanya dapat mengunduh file event yang dikelolanya.
- Bukti pembayaran dan dokumen peserta memakai disk privat.
- Banner/juknis publik.

### Chat

- Harus login.
- Admin event, peserta terdaftar, atau juri accepted.
- Kirim pesan mengikuti setting `participants_can_send` / `juries_can_send`.
- Pesan hidden tidak terlihat peserta/juri.
- Laporan hanya menandai pesan, tidak menghapus otomatis.

## Perubahan role/scope

Dilakukan Admin Owner melalui `/owner/users` atau API `/api/owner/users/*`. Perubahan scope berdampak langsung pada akses event. Pastikan admin tidak kehilangan akses ke event yang sedang dikelola.
