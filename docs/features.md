# Fitur

Dokumen ini menjelaskan fitur yang tersedia dan status implementasinya.

## Publik

| Fitur | Detail |
|---|---|
| Landing page | Informasi produk, alur, event |
| Daftar event | Event aktif non-arsip |
| Detail event | `/{slug}`; fallback ID; banner, jadwal, kuota, harga, kontak, format, timezone |
| Juknis | File publik dapat dilihat sebelum daftar |
| QR event | QR menuju URL publik, dibuat di halaman admin |
| Pendaftaran | Form dinamis, tim/individu, upload dokumen privat |
| Hasil peserta | Hanya evaluasi yang sudah dipublikasikan |

## Peserta

| Fitur | Detail |
|---|---|
| Registrasi | Email resmi/domain jika diwajibkan |
| Form pendaftaran | Field teks/email/number/url/select/date/file |
| Tim | Nama tim + anggota sesuai min/maks |
| Pembayaran | Upload bukti privat; status pending/verified/rejected |
| Status pendaftaran | pending/verified/rejected/cancelled |
| Dokumen | Unduh file sendiri |
| Hasil evaluasi | Rata-rata, peringkat, catatan publik per juri |
| Chat event | Jika diaktifkan panitia |
| Profil | Nama, email, password |

Catatan: peserta tidak dapat melihat catatan privat juri.

## Juri

| Fitur | Detail |
|---|---|
| Undangan | Terima/tolak assignment |
| Event | Hanya event accepted |
| Daftar peserta | Progres penilaian |
| Form penilaian | Form yang di-assign, snapshot historis |
| Menilai | Skor per kriteria, total otomatis |
| Catatan | `public_feedback` dan `private_evaluation` |
| Edit skor | Hanya skor sendiri, selama penilaian dibuka |
| Revisi | Inbox permintaan revisi; perbaiki atau tolak dengan alasan |
| Leaderboard | Peringkat event |
| Chat event | Jika diizinkan |
| Profil juri | Institusi, keahlian, bio, nomor WA + consent |

## Admin Event

| Fitur | Detail |
|---|---|
| Dashboard | Statistik, kuota event, event terbaru, pembayaran pending |
| Profil organizer | Nama, deskripsi, kontak, logo |
| Event | Buat draft, edit, aktifkan, tutup, selesaikan, arsip |
| Identitas publik | Slug, format, timezone, kontak, QR, preview |
| Duplikasi | Salin event + form + template juri sebagai draft |
| Kuota | Maks. 3 event tersimpan |
| Form pendaftaran | Builder field + template + field file |
| Peserta | Daftar, detail, ubah status, ekspor CSV |
| Pembayaran | Lihat bukti privat, verifikasi/tolak |
| Juri | Buat akun baru/assign existing, password custom/acak, reset + copy |
| Form juri | Banyak form per event, template bawaan/custom, preview, search, simpan template |
| Review evaluasi | Catatan publik/privat, review, publish satuan/semua |
| Revisi skor | Minta revisi ke juri; tidak bisa edit skor langsung |
| Chat | Aktifkan, izin kirim, pengumuman, WhatsApp link, moderasi |
| Rekap | Nilai per form, rata-rata, peringkat |

## Admin Owner

| Fitur | Detail |
|---|---|
| Semua fitur admin | Tanpa batas kuota |
| Pengguna | Daftar, buat, ubah role/scope/status |
| Semua skor | Melihat skor seluruh event |
| Semua pembayaran | Riwayat pembayaran global |

## Form penilaian

- Tipe field: `score`, `text`, `textarea`, `select`.
- `score` punya nilai maksimal; total dijumlahkan jika `use_default_score`.
- Form dapat diedit; skor menyimpan `form_snapshot` agar konteks historis tetap.
- Template bawaan: Kompetisi Dasar, Presentasi, Performance, Desain, Riset.
- Template custom disimpan di `custom_jury_form_templates`.
- Pencarian template berdasarkan nama/deskripsi.
- Preview menampilkan form seperti yang dilihat juri.

## Alur skor (ringkas)

1. Admin membuat form + assign juri.
2. Juri menerima undangan dan menilai.
3. Skor tersimpan `submitted`.
4. Admin review → `reviewed`.
5. Admin publish → `published`; peserta dapat melihat.
6. Jika ada masalah, admin meminta revisi; publikasi dicabut.
7. Juri memperbaiki → `submitted`; admin review/publish ulang.

## Chat & WhatsApp

- Chat event default nonaktif.
- Admin dapat mengatur peserta/juri boleh mengirim.
- Pesan dapat dilaporkan dan disembunyikan.
- WhatsApp saat ini berupa tautan grup + consent nomor juri; belum ada pengiriman otomatis via WhatsApp Business API.

## Status implementasi vs PRD

| Area | Status |
|---|---|
| Kuota 3 event | ✅ |
| Slug, QR, preview, arsip, duplikasi | ✅ |
| Form pendaftaran file privat | ✅ |
| Tim/anggota | ✅ |
| Template juri + preview + search + simpan custom | ✅ |
| Hanya juri dapat mengubah skor | ✅ |
| Review/publish evaluasi | ✅ |
| Chat + moderasi | ✅ |
| Audit log data | ✅ (tanpa UI khusus) |
| Kategori/cabang per event | ⚠️ masih kategori global |
| Lifecycle 8 status | ⚠️ draft/active/closed/completed + arsip |
| Builder bobot/rubrik lanjutan | ⚠️ kriteria + max; belum bobot kompleks |
| Manual add peserta, waitlist/withdraw | ⚠️ belum |
| WhatsApp API | ⚠️ hanya tautan/consent |
| Halaman audit | ⚠️ data ada, UI belum |
| Token visual PRD penuh | ⚠️ palet mendekati, belum identik |

## Batasan operasional

- Hapus event tidak menghapus file fisik otomatis.
- Perubahan template juri tidak mengubah `form_snapshot` skor lama.
- Reset password juri oleh admin hanya untuk akun yang ia buat (kecuali Admin Owner/global).
- Arsip event tetap dihitung kuota.
