# Alur Kerja Operasional

## 1. Menyiapkan event baru

1. Login sebagai Admin Event.
2. Buka **Event → Buat Event**.
3. Isi:
   - Nama, deskripsi, kategori.
   - Harga dan kuota peserta.
   - Format (offline/online/hybrid), timezone, kontak panitia.
   - Jadwal pendaftaran, hari H, periode event.
   - Mode partisipasi individu/tim.
4. Simpan sebagai **draft**.
5. Lengkapi **Profil Organizer** agar identitas publik kredibel.
6. Unggah banner/juknis melalui edit event.
7. Cek **Preview Halaman Publik**.
8. Aktifkan event jika sudah siap.

Kuota Admin Event maksimal 3 event tersimpan. Draft dan arsip tetap dihitung.

## 2. Menyiapkan form pendaftaran

1. Buka event → **Form Pendaftaran**.
2. Gunakan template atau tambah field:
   - Teks, email, angka, URL, tanggal, pilihan, upload file.
3. Tandai field wajib.
4. Simpan.
5. Uji dengan akun peserta di event draft? Form hanya bisa diuji saat pendaftaran dibuka.

Field file disimpan privat; peserta dan admin event dapat mengunduh.

## 3. Menyiapkan form penilaian

1. Buka event → **Form Penilaian**.
2. Pilih form existing atau **Form baru**.
3. Isi nama form (mis. “Juri Vokal”) dan kriteria.
4. Gunakan template bawaan/custom bila perlu.
5. Cari template melalui kolom pencarian.
6. Klik **Lihat Preview** untuk memeriksa tampilan juri.
7. Klik **Simpan sebagai Template** jika ingin memakai ulang di event lain.
8. Klik **Simpan Form**.

Satu event dapat memiliki beberapa form, misalnya form berbeda untuk kategori lomba.

## 4. Menambahkan juri

### A. Membuat akun baru

1. Buka event → **Juri**.
2. Tab **Buat Akun Juri Baru**.
3. Isi nama, email, form, posisi.
4. Password:
   - Isi manual (min. 8 karakter), atau
   - Kosongkan untuk dibuat otomatis.
5. Submit.
6. Salin email/password melalui tombol **Salin** / **Salin Semua**.
7. Undangan terkirim; juri harus menerima undangan.

### B. Memakai akun existing

1. Tab **Tambah Akun Juri yang Sudah Ada**.
2. Cari nama/email.
3. Pilih satu/beberapa juri aktif.
4. Pilih form dan posisi.
5. Submit.

### Reset/ganti password

1. Pada kartu juri ter-assign, isi password baru atau kosongkan.
2. Klik **Reset / Ganti Password**.
3. Salin kredensial baru.
4. Hanya pembuat akun atau Admin Owner/global yang dapat mereset.

## 5. Juri menerima undangan & menilai

1. Juri login → **Undangan Juri**.
2. Terima undangan.
3. Buka **Penilaian → Event**.
4. Pilih peserta.
5. Isi kriteria, catatan publik, catatan internal.
6. Simpan.

Penilaian hanya dapat dilakukan jika:
- Assignment accepted.
- Form di-assign.
- Peserta verified.
- Jendela penilaian terbuka.

## 6. Review & publikasi evaluasi

1. Admin buka event → **Review & Publikasi Evaluasi**.
2. Periksa catatan publik dan internal.
3. Tandai **Reviewed** atau **Perlu Revisi**.
4. Publikasikan:
   - Satu evaluasi: tombol **Terbitkan Evaluasi**.
   - Semua: **Terbitkan Semua yang Direview**.
5. Setelah publish, peserta melihat nilai dan catatan publik.

Publish massal ditolak jika:
- Ada evaluasi belum direview.
- Ada permintaan revisi pending.

## 7. Meminta revisi skor

1. Admin buka detail pendaftaran peserta.
2. Pada skor juri, isi alasan revisi (min. 10 karakter).
3. Kirim permintaan.
4. Skor yang sudah terbit otomatis ditarik dari tampilan peserta.
5. Juri menerima notifikasi dan membuka **Permintaan Revisi**.
6. Juri:
   - Memperbaiki skor → permintaan `applied`; admin review/publish ulang.
   - Menolak dengan alasan → permintaan `rejected`; admin dapat meninjau.

Admin tidak dapat mengedit skor langsung.

## 8. Peserta mendaftar & membayar

1. Peserta membuka event publik.
2. Login/daftar akun.
3. Isi form (termasuk tim dan file).
4. Submit; kuota dicek transaksional.
5. Upload bukti pembayaran.
6. Admin memverifikasi/menolak.
7. Peserta melihat status dan hasil evaluasi setelah publish.

## 9. Chat event

1. Admin buka event → **Chat Event**.
2. Aktifkan chat.
3. Atur izin peserta/juri.
4. Tambahkan pengumuman/tautan WhatsApp jika perlu.
5. Peserta/juri membuka chat event.
6. Pesan dapat dilaporkan; admin menyembunyikan pesan bermasalah.

## 10. Menutup & mengarsipkan event

1. Event aktif → **Tutup Event** setelah pendaftaran selesai.
2. Setelah penilaian selesai → **Selesaikan Event**.
3. Jika tidak lagi ditampilkan → **Arsipkan**.
4. Arsip tidak menghapus data dan tetap dihitung kuota.

## 11. Duplikasi event

1. Buka detail event → **Duplikasi Event**.
2. Sistem membuat draft baru:
   - Informasi inti event.
   - Form pendaftaran.
   - Template form juri.
3. Registrasi, pembayaran, skor, dan assignment tidak disalin.
4. Lengkapi jadwal dan aktifkan.

Kuota event berlaku saat duplikasi.

## 12. Ekspor peserta

1. Buka event → **Peserta**.
2. Klik **Ekspor CSV**.
3. File berisi nomor pendaftaran, nama, email, status, tim, pembayaran, rata-rata nilai, jumlah juri, tanggal daftar.

## Checklist sebelum event dibuka

- [ ] Profil organizer lengkap
- [ ] Banner dan juknis benar
- [ ] Slug publik tidak bentrok
- [ ] Form pendaftaran diuji
- [ ] Kuota peserta dan tim benar
- [ ] Form penilaian + preview diperiksa
- [ ] Juri dibuat/di-assign dan sudah menerima undangan
- [ ] Pembayaran manual/verifikasi siap
- [ ] Chat/WhatsApp sesuai kebutuhan
- [ ] Preview publik dicek di mobile
