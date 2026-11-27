# JURI.ON — Brand Guidelines

Sumber kebenaran: `PRD_JURI_ON_Brand_Identity.docx` v1.0. Dokumen ini adalah ringkasan operasional untuk manusia dan agen.

## Identitas

- Nama resmi: **JURI.ON** (titik kobalt setelah `JURI`, tanpa spasi).
- Deskriptor: **Juri Online** (sentence case, bukan uppercase).
- Mark: monogram J abstrak dari berkas logo terbaru. Jangan menggambar ulang logo dengan CSS/SVG buatan sendiri.
- Slogan visual: navy + kobalt + ruang putih yang lapang. Hindari nuansa kasino, neon, sirkuit, dan 3D berat.

## Aset

| Aset | Path | Pemakaian |
|---|---|---|
| Mark berwarna (transparan) | `public/images/brand/jurion-mark.png` | header, sidebar, footer di permukaan terang |
| Mark putih (monokrom) | `public/images/brand/jurion-mark-white.png` | permukaan navy/gelap tanpa tile |
| Favicon ICO (16/32/48/64) | `public/favicon.ico` | browser tab, shortcut |
| Favicon PNG | `public/images/brand/favicon-16x16.png`, `favicon-32x32.png` | tab, bookmark |
| Apple touch icon | `public/images/brand/apple-touch-icon.png` (180) | iOS home screen |
| App icon | `public/images/brand/jurion-icon-192.png`, `jurion-icon-512.png` | PWA/manifest |
| Manifest | `public/site.webmanifest` | PWA |
| Pattern tile | `public/images/brand/pattern.svg` | dekorasi latar, 5–12% prominence |
| Source raster | `dev-tools/brand/source/jurion-mark-source.png` | regenerasi aset |
| Generator | `dev-tools/brand/generate_assets.py` | membuat ulang turunan dari source |

Aplikasi Next.js memakai salinan di `my-app/public/brand/` dan `my-app/src/app/favicon.ico`.

## Lockup dan komponen

Blade: `<x-brand.logo :href="route('home')" variant="dark" size="sm" tile descriptor />`

- `variant`: `light` (wordmark navy) atau `dark` (wordmark putih).
- `size`: `sm` (32 px), `md` (40 px), `lg` (52 px).
- `tile`: bingkai putih membulat di belakang mark — wajib di permukaan navy agar mark berwarna tetap terbaca.
- `descriptor`: menampilkan "Juri Online" di bawah wordmark.
- Teks wordmark harus tetap `JURI.ON`; titik kobalt adalah bagian dari nama, jangan di-`aria-hidden`.

Next.js: `<BrandLockup href="/dashboard" variant="dark" tile descriptor />` dari `@/components/brand/lockup`.

## Palet

| Token | Nilai | Catatan |
|---|---|---|
| Navy | `#0B1F46` | teks utama, permukaan gelap |
| Cobalt | `#2F80FF` | aksen, titik wordmark, active state, focus ring |
| Cobalt strong | `#1E6BE6` | tautan/teks kobalt di putih (kontras 4.88:1, AA) |
| Cobalt deep | `#1552B8` | hover/active, teks di putih (7.16:1) |
| Light blue | `#E8F2FF` | latar lembut, selected state |
| Slate | `#667085` | teks sekunder (4.97:1 di putih) |
| Border | `#DCE4EF` | garis kartu/input |
| Surface | `#F7FAFF` | latar halaman |

Jangan memakai kobalt murni untuk teks kecil di putih (3.72:1). Gunakan `--brand-cobalt-strong`.

## Tipografi

- Display/heading: Montserrat 600–700.
- Body/UI: Inter 400–600.
- Fallback: Figtree, Arial, sans-serif.
- Uppercase hanya untuk wordmark. UI memakai sentence case.

## Spacing, radius, shadow

- Basis 4 px, ritme 8 px: 8/12/16/24/32/40/48.
- Kartu 12–16 px, panel besar sampai 24 px, kontrol minimal 44 px.
- Shadow hemat: `0 6px 24px rgba(11,31,70,0.08)`.

## Pattern

- Hanya dekoratif (`aria-hidden="true"`), 5–12% prominence.
- Jangan di belakang teks padat, input, atau pesan validasi.
- Jangan mengulang wordmark sebagai tekstur latar.

## Motion

- Setiap section, heading, kartu, item list, langkah, dan footer publik dibungkus `Reveal` atau `whileInView`; tidak boleh ada blok isi yang muncul tanpa animasi.
- Motion penuh: opacity + rise 24px + blur 6px, 0.7s. `prefers-reduced-motion: reduce` → fade 0.35s saja tanpa translate/blur, jadi konten tetap beranimasi saat scroll.
- Viewport margin bawah `-24px` supaya elemen paling bawah (footer/CTA) tetap terpicu saat halaman mencapai scroll maksimum.
- Scroll progress, parallax, count-up, dan tilt card hanya aktif pada motion penuh.
- Ringkasan implementasi: `src/components/public/reveal.tsx` (Reveal, Parallax, CountUp, TiltCard, ScrollProgress), `steps.tsx`, `events-explorer.tsx`, `section.tsx` (PageHero/SectionHeading), `public-shell.tsx` (footer).

## Aksesibilitas

- Target WCAG 2.2 AA untuk teks dan kontrol.
- `:focus-visible` wajib terlihat (2 px cobalt, offset 2 px).
- Logo link punya nama aksesibel dari teks `JURI.ON`.
- Uji lebar 320/375/768/1024/1440 px — tanpa overflow horizontal.
- Hormati `prefers-reduced-motion`; tidak ada animasi dekoratif panjang.

## Implementasi di repo ini

- Token CSS: `public/css/brand.css` (dimuat setelah `premium-global.css`).
- Skala Tailwind `indigo-*` dipetakan ke ramp kobalt, `slate-950` ke navy (`tailwind.config.js`). Warna baru wajib memakai token, bukan hex lepas.
- Halaman Laravel: header/sidebar/footer dan seluruh auth memakai `<x-brand.logo>`.
- Next.js: `my-app/src/components/arc/brand.css` + `BrandLockup`; accent `brand` di `data-accent`.

## Batas kerja agen (anti-tabrakan)

- **Milik brand pass ini:** `public/css/brand.css`, `public/images/brand/*`, `public/favicon*`, `public/apple-touch-icon.png`, `public/site.webmanifest`, `resources/views/components/brand/*`, `resources/brand/*`, `BRAND_GUIDELINES.md`, `dev-tools/brand/*`, dan blok `<head>`/logo di `layouts/app.blade.php`, `layouts/guest.blade.php`, `errors/403.blade.php`.
- **Aplikasi Next.js** memakai brand layer `src/components/arc/brand.css`, `src/components/brand/lockup.tsx`, aset `public/brand/*`, dan `src/app/favicon.ico`. Halaman publik (Beranda, Event, Tentang, Company Profile) memakai motion primitives di `src/components/public/reveal.tsx`, `section.tsx`, `steps.tsx`, dan `events-explorer.tsx`. Aturan ringkasnya ada di `my-app/AGENTS.md`.
- **Jangan mengembalikan** perubahan brand atau menimpa file agen lain. Jika menambah halaman Next, baca `my-app/AGENTS.md` dan pakai `BrandLockup`; jangan menulis `JuriOnline`.
- **Build/deploy Next.js:** build live 8081/3000 sudah dijalankan setelah agen migrasi idle. Setelah mengubah halaman publik, jalankan `pnpm build` di `my-app` lalu restart service `nextapp`. Jangan restart `nextapp`/`laravel-api` saat agen lain sedang menulis.
- Jalankan `npm run build` hanya untuk aset Laravel (Vite). Build Next diuji terisolasi di salinan sementara agar `.next` live tidak tersentuh.

## Status implementasi

| Cek | Hasil |
|---|---|
| Laravel: home, about, company profile, login, register, events | 200, judul `JURI.ON`, 0 `JuriOnline` |
| Laravel: sidebar navy, wordmark Montserrat, titik kobalt `rgb(47,128,255)`, body Inter | terverifikasi di browser |
| Laravel: favicon.ico 200 (ICO 16/32/48/64), manifest, apple-touch, mark 512 px | terverifikasi |
| Laravel: 403, login desktop/mobile, footer | terverifikasi, tanpa overflow 320/375/1440 |
| Next: build terisolasi (salinan sementara) | sukses; `tsc --noEmit` dan eslint bersih (1 warning lama di file agen) |
| Next: home, login, register, about, company profile | lockup `JURI.ON`, navy/cobalt, Montserrat/Inter, tanpa overflow |
| Next: redesign publik (Beranda, Event, Tentang, Company Profile, Alur) | hero navy, bento fitur, sticky steps, filter event, panduan per peran di `/alur`, pintasan halaman di beranda; reveal di setiap section/kartu/langkah/footer, fade fallback untuk reduced motion; terverifikasi 390/1440/768 |
| Next: build live 8081/3000 | deployed 2026-11-27; 8081 dan 3000 membalas 200, tanpa console error |

## Checklist rilis

- [x] Wordmark `JURI.ON` dan titik kobalt.
- [x] Logo utama, varian gelap, mark transparan, favicon set.
- [x] Token warna/tipografi melalui CSS + Tailwind.
- [x] Pattern low-contrast dan dekoratif.
- [x] Focus visible, ukuran kontrol 44 px.
- [x] Uji visual 320/375/1440 px pada halaman Laravel dan Next (build terisolasi).
- [x] Rebuild + restart service Next live (dijalankan saat agen migrasi idle).
- [ ] Clearance merek oleh tim legal (di luar cakupan kode).
