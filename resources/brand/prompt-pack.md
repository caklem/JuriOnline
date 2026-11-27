# JURI.ON — Prompt Pack untuk Agen AI

Aturan ini wajib dibaca sebelum agen menghasilkan UI, aset, atau konten JURI.ON.

## Konteks

Baca `PRD_JURI_ON_Brand_Identity.docx` dan `BRAND_GUIDELINES.md`. Periksa repo sebelum mengedit. Jangan mengarang fitur, peran, sertifikasi, atau klaim keamanan.

## Aturan wajib

1. Ejaan resmi: `JURI.ON` — navy `JURI`, titik kobalt, `ON`. Deskriptor: `Juri Online`.
2. Jangan menggambar ulang logo. Pakai `public/images/brand/jurion-mark.png` (atau `<x-brand.logo>` / `<BrandLockup>`).
3. Palet hanya dari token: navy `#0B1F46`, kobalt `#2F80FF`, cobalt strong `#1E6BE6`, light blue `#E8F2FF`, slate `#667085`, border `#DCE4EF`, surface `#F7FAFF`.
4. Tipografi: Montserrat untuk heading/wordmark, Inter untuk body/UI. Jangan menambah keluarga font ketiga.
5. Pattern hanya dekorasi, 5–12% opacity, `aria-hidden="true"`, tidak di belakang teks/input.
6. Jangan memakai motif kasino, neon, sirkuit, atau 3D berat.
7. Semua kontrol punya focus state terlihat; target WCAG 2.2 AA.
8. Hormati `prefers-reduced-motion`.
9. Jangan menyunting file yang sedang diubah agen lain. Tambah file baru bila memungkinkan; jangan menimpa perubahan yang belum di-commit.
10. Setelah perubahan: jalankan build/lint yang relevan dan laporkan file yang berubah, asumsi, serta pertanyaan terbuka.

## Prompt aset gambar

Gunakan bahasa yang menekankan: minimal, lapang, profesional, mudah diingat; monogram J abstrak dengan pita membulat; navy pekat dan kobalt cerah; latar putih; tanpa teks tambahan; tanpa bayangan 3D; tanpa motif sirkuit; komposisi center dengan ruang kosong 12%.

Contoh:

> A minimal brand visual for JURI.ON: an abstract J monogram formed by two rounded ribbons, deep navy `#0B1F46` and bright cobalt `#2F80FF`, on a clean white background, centered with 12% padding, flat vector look, no text, no 3D, no neon, no circuit motifs, professional and memorable.

## Prompt kode

> Apply the JURI.ON brand system to the existing interface. Reuse `<x-brand.logo>` (Laravel) or `<BrandLockup>` (Next.js). Use only brand tokens from `public/css/brand.css` / `resources/brand/design-tokens.json`. Do not invent product modules or copy. Preserve existing layout structure, keyboard focus, and reduced-motion behavior. Do not overwrite files owned by other agents; inspect before editing and list assumptions.

## Larangan

- Menulis `JuriOnline` di UI baru.
- Menghapus titik kobalt atau menggantinya dengan titik navy.
- Memakai ungu/indigo sebagai aksen utama.
- Menaruh pattern di kartu form atau di belakang tabel.
- Menyalin aset logo ke ukuran kecil tanpa padding (favicon butuh safe area).
