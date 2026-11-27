import Link from "next/link";
import {
  ArrowRight,
  BarChart3,
  CalendarCheck,
  Check,
  CreditCard,
  FileText,
  Globe,
  Scale,
  ShieldCheck,
  Sparkles,
  Trophy,
  UserRound,
  Users,
} from "lucide-react";
import { BrandLockup } from "@/components/brand/lockup";
import { CtaLink } from "@/components/public/cta-link";
import { PublicShell } from "@/components/public/public-shell";
import { CountUp, Parallax, Reveal, TiltCard } from "@/components/public/reveal";
import { PageHero, SectionHeading } from "@/components/public/section";

// Halaman publik dirender per request (footer memakai tahun berjalan).
export const instant = false;

export const metadata = {
  title: "Tentang JURI.ON",
  description: "Platform Penilaian Digital untuk Event Kompetisi.",
};

const FEATURES = [
  {
    title: "Form Pendaftaran Custom",
    description: "Susun formulir pendaftaran dengan drag & drop builder dan kumpulkan data peserta secara terstruktur.",
    icon: FileText,
  },
  {
    title: "Form Penilaian Juri",
    description: "Satu event dapat memiliki beberapa formulir penilaian dengan kriteria yang berbeda untuk setiap juri.",
    icon: Scale,
  },
  {
    title: "Manajemen Peserta",
    description: "Kelola pendaftaran, verifikasi data, kuota, dan periode pendaftaran dalam satu dasbor.",
    icon: Users,
  },
  {
    title: "Verifikasi Pembayaran",
    description: "Unggah bukti pembayaran dan verifikasi oleh admin, terhubung langsung dengan status peserta.",
    icon: CreditCard,
  },
  {
    title: "Rekapitulasi Nilai",
    description: "Nilai dari seluruh juri dan formulir digabungkan otomatis dengan perhitungan yang konsisten.",
    icon: BarChart3,
  },
  {
    title: "Leaderboard Real-time",
    description: "Peringkat peserta diperbarui saat penilaian masuk, siap dipantau penyelenggara dan peserta.",
    icon: Trophy,
  },
];

const AUDIENCE = [
  {
    title: "Admin / Penyelenggara",
    description: "Mengelola event, peserta, pembayaran, dan penugasan juri dengan alur yang terintegrasi.",
    icon: CalendarCheck,
  },
  {
    title: "Juri",
    description: "Memberikan penilaian terstruktur melalui formulir yang sudah disiapkan penyelenggara.",
    icon: Scale,
  },
  {
    title: "Peserta",
    description: "Mendaftar, memantau status pendaftaran, dan melihat hasil penilaian dengan jelas.",
    icon: UserRound,
  },
  {
    title: "Admin",
    description: "Mengawasi seluruh sistem, verifikasi pembayaran, dan memantau event yang berjalan.",
    icon: ShieldCheck,
  },
];

const ADVANTAGES = [
  "Sistem terintegrasi untuk seluruh proses event",
  "Form builder yang mudah digunakan tanpa coding",
  "Dukungan beberapa formulir penilaian per event",
  "Rekapitulasi nilai otomatis dan konsisten",
  "Antarmuka yang bersih dan mudah dipahami",
  "Pembaruan status secara real-time",
  "Akses berbasis peran untuk setiap pengguna",
  "Data event tersimpan di satu tempat",
];

const VALUES = [
  {
    title: "Profesional",
    description: "Alur kerja yang jelas untuk penyelenggara, juri, dan peserta dalam setiap tahap kompetisi.",
    icon: ShieldCheck,
  },
  {
    title: "Transparan",
    description: "Riwayat penilaian dan status peserta dapat ditelusuri oleh pihak yang berwenang.",
    icon: Check,
  },
  {
    title: "Terpercaya",
    description: "Akses berbasis peran menjaga data event dan penilaian tetap aman dan terkendali.",
    icon: Globe,
  },
];

const FLOW = [
  { title: "Pendaftaran", detail: "Form dinamis dan verifikasi data" },
  { title: "Penilaian", detail: "Multi-juri dengan kriteria sendiri" },
  { title: "Rekap", detail: "Nilai akhir dan leaderboard" },
];

export default function AboutPage() {
  return (
    <PublicShell>
      <PageHero
        eyebrow="Tentang kami"
        title="Tentang JURI.ON"
        description="Platform penilaian digital untuk event kompetisi — menyatukan pendaftaran, penilaian juri, verifikasi pembayaran, dan rekapitulasi hasil dalam satu alur kerja."
      >
        <div className="grid max-w-xl grid-cols-3 gap-4 rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
          {[
            { value: 6, label: "Fitur inti" },
            { value: 4, label: "Peran pengguna" },
            { value: 3, label: "Langkah kerja" },
          ].map((item) => (
            <div key={item.label}>
              <p className="text-2xl font-semibold tabular-nums text-white sm:text-3xl">
                <CountUp value={item.value} />
              </p>
              <p className="mt-1 text-[10px] font-medium uppercase tracking-wide text-slate-400">{item.label}</p>
            </div>
          ))}
        </div>
      </PageHero>

      <section className="bg-white py-20 sm:py-24">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <div className="grid gap-12 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:gap-16">
            <Reveal>
              <SectionHeading
                eyebrow="Apa itu JURI.ON"
                title="Satu tempat untuk seluruh siklus kompetisi."
                description="JURI.ON adalah sistem manajemen event dan penilaian digital yang dirancang untuk memudahkan penyelenggaraan kompetisi, lomba, dan acara penjurian lainnya."
              />
              <p className="mt-5 max-w-2xl text-base leading-8 text-slate-600">
                Platform ini menyediakan solusi lengkap mulai dari pendaftaran peserta hingga penilaian oleh juri
                profesional. Setiap peran melihat informasi yang relevan, sehingga pekerjaan tidak lagi tersebar di
                spreadsheet, pesan grup, dan rekap manual.
              </p>
              <Link
                href="/company-profile"
                className="group mt-8 inline-flex items-center gap-2 text-sm font-semibold text-[var(--brand-cobalt-strong)] transition hover:text-[var(--brand-cobalt-deep)]"
              >
                Lihat company profile
                <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
              </Link>
            </Reveal>

            <Reveal delay={0.08} y={28}>
              <Parallax distance={22}>
                <div className="relative overflow-hidden rounded-3xl bg-[var(--brand-navy)] p-8 text-white">
                  <div aria-hidden="true" className="jurion-dotgrid absolute inset-0 opacity-30" />
                  <div
                    aria-hidden="true"
                    className="jurion-aurora absolute -right-16 -top-16 h-52 w-52 rounded-full bg-[var(--brand-cobalt)]/30 blur-3xl"
                  />
                  <div className="relative">
                    <BrandLockup href="/" variant="dark" tile descriptor />
                    <p className="mt-8 text-balance text-xl font-medium leading-8 text-white/90">
                      Penilaian yang baik dimulai dari proses yang jelas.
                    </p>
                    <div className="mt-8 space-y-3">
                      {FLOW.map((item, index) => (
                        <div key={item.title} className="flex items-center gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                          <span className="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-[var(--brand-cobalt)]/20 text-[10px] font-semibold text-white">
                            {index + 1}
                          </span>
                          <div className="min-w-0">
                            <p className="text-xs font-semibold text-white">{item.title}</p>
                            <p className="text-[11px] text-slate-400">{item.detail}</p>
                          </div>
                          <Check className="ml-auto h-4 w-4 shrink-0 text-emerald-400" aria-hidden="true" />
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              </Parallax>
            </Reveal>
          </div>
        </div>
      </section>

      <section className="bg-[var(--brand-surface)] py-20 sm:py-24">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <Reveal>
            <SectionHeading
              eyebrow="Fitur utama"
              title="Dibangun untuk kebutuhan nyata penyelenggara."
              description="Setiap fitur mengikuti alur kompetisi, dari formulir pendaftaran sampai pengumuman hasil."
            />
          </Reveal>

          <div className="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            {FEATURES.map((feature, index) => {
              const Icon = feature.icon;

              return (
                <Reveal key={feature.title} delay={Math.min(index, 5) * 0.05} className="h-full">
                  <TiltCard className="h-full rounded-2xl border border-slate-200 bg-white p-6 hover:border-[var(--brand-cobalt)]/25 hover:shadow-[0_24px_60px_-45px_rgba(11,31,70,0.45)]">
                    <div className="flex items-start justify-between gap-3">
                      <span className="grid h-11 w-11 place-items-center rounded-xl bg-[var(--brand-light-blue)] text-[var(--brand-cobalt-strong)] ring-1 ring-inset ring-[var(--brand-cobalt)]/10">
                        <Icon className="h-5 w-5" aria-hidden="true" />
                      </span>
                      <span className="text-[10px] font-semibold tabular-nums text-slate-300">
                        {String(index + 1).padStart(2, "0")}
                      </span>
                    </div>
                    <h3 className="mt-5 text-base font-semibold text-[var(--brand-navy)]">{feature.title}</h3>
                    <p className="mt-2 text-sm leading-6 text-slate-600">{feature.description}</p>
                  </TiltCard>
                </Reveal>
              );
            })}
          </div>
        </div>
      </section>

      <section className="bg-white py-20 sm:py-24">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <Reveal>
            <SectionHeading
              eyebrow="Untuk siapa"
              title="Satu platform, empat peran."
              description="Setiap pengguna masuk dengan ruang kerja dan izin yang sesuai dengan perannya."
            />
          </Reveal>

          <div className="mt-12 grid gap-5 sm:grid-cols-2">
            {AUDIENCE.map((item, index) => {
              const Icon = item.icon;

              return (
                <Reveal key={item.title} delay={index * 0.06} className="h-full">
                  <div className="group flex h-full items-start gap-5 rounded-2xl border border-slate-200 bg-white p-6 transition duration-300 hover:-translate-y-0.5 hover:border-[var(--brand-cobalt)]/25 hover:shadow-[0_24px_60px_-45px_rgba(11,31,70,0.4)]">
                    <span className="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-[var(--brand-navy)] text-white transition group-hover:bg-[var(--brand-cobalt-deep)]">
                      <Icon className="h-5 w-5" aria-hidden="true" />
                    </span>
                    <div>
                      <h3 className="text-base font-semibold text-[var(--brand-navy)]">{item.title}</h3>
                      <p className="mt-2 text-sm leading-6 text-slate-600">{item.description}</p>
                    </div>
                  </div>
                </Reveal>
              );
            })}
          </div>
        </div>
      </section>

      <section className="relative overflow-hidden bg-[var(--brand-navy)] py-20 text-white sm:py-24">
        <div aria-hidden="true" className="jurion-dotgrid absolute inset-0 opacity-40 [mask-image:linear-gradient(to_bottom,black,transparent_95%)]" />
        <div
          aria-hidden="true"
          className="jurion-aurora pointer-events-none absolute -left-24 top-0 h-80 w-80 rounded-full bg-[var(--brand-cobalt)]/25 blur-[100px]"
        />
        <div className="relative mx-auto max-w-7xl px-6 lg:px-8">
          <div className="grid gap-12 lg:grid-cols-[0.95fr_1.05fr] lg:items-center lg:gap-16">
            <Reveal>
              <SectionHeading
                eyebrow="Keunggulan"
                title="Kenapa tim memilih JURI.ON."
                description="Bukan sekadar formulir digital. Alur kerja, data, dan hasil penilaian berada dalam satu sistem."
                tone="dark"
              />
              <CtaLink href="/register" variant="secondary" size="md" className="cta-sheen mt-8">
                Mulai menggunakan JURI.ON
                <ArrowRight className="h-4 w-4" aria-hidden="true" />
              </CtaLink>
            </Reveal>

            <div className="grid gap-3 sm:grid-cols-2">
              {ADVANTAGES.map((item, index) => (
                <Reveal key={item} delay={index * 0.04}>
                  <div className="flex items-start gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3.5 text-sm text-slate-200 backdrop-blur">
                    <span className="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-emerald-400/15 text-emerald-300 ring-1 ring-inset ring-emerald-400/25">
                      <Check className="h-3 w-3" aria-hidden="true" />
                    </span>
                    {item}
                  </div>
                </Reveal>
              ))}
            </div>
          </div>
        </div>
      </section>

      <section className="bg-white py-20 sm:py-24">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <Reveal>
            <SectionHeading
              eyebrow="Nilai kami"
              title="Prinsip yang menjaga kualitas penilaian."
              align="center"
            />
          </Reveal>

          <div className="mt-12 grid gap-5 md:grid-cols-3">
            {VALUES.map((value, index) => {
              const Icon = value.icon;

              return (
                <Reveal key={value.title} delay={index * 0.07} className="h-full">
                  <TiltCard className="h-full rounded-2xl border border-slate-200 bg-[var(--brand-surface)] p-7">
                    <span className="text-[11px] font-semibold tabular-nums text-[var(--brand-cobalt)]">
                      {String(index + 1).padStart(2, "0")}
                    </span>
                    <span className="mt-5 grid h-11 w-11 place-items-center rounded-xl bg-white text-[var(--brand-cobalt-strong)] shadow-sm ring-1 ring-inset ring-slate-200">
                      <Icon className="h-5 w-5" aria-hidden="true" />
                    </span>
                    <h3 className="mt-5 text-lg font-semibold text-[var(--brand-navy)]">{value.title}</h3>
                    <p className="mt-2 text-sm leading-6 text-slate-600">{value.description}</p>
                  </TiltCard>
                </Reveal>
              );
            })}
          </div>
        </div>
      </section>

      <section className="border-t border-slate-100 bg-[var(--brand-surface)] py-16 sm:py-20">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <Reveal>
            <div className="relative overflow-hidden rounded-3xl border border-[var(--brand-border)] bg-white p-8 shadow-[0_30px_70px_-55px_rgba(11,31,70,0.45)] sm:p-10">
              <div
                aria-hidden="true"
                className="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-[var(--brand-cobalt)]/10 blur-3xl"
              />
              <div className="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div className="max-w-2xl">
                  <p className="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-[var(--brand-cobalt-strong)]">
                    <Sparkles className="h-3.5 w-3.5" aria-hidden="true" />
                    Mulai
                  </p>
                  <h2 className="mt-3 text-2xl font-semibold tracking-tight text-[var(--brand-navy)] sm:text-3xl">
                    Mulai menggunakan JURI.ON
                  </h2>
                  <p className="mt-3 text-sm leading-6 text-slate-600">
                    Daftarkan event Anda dan rasakan kemudahan mengelola kompetisi dengan sistem digital yang
                    terintegrasi.
                  </p>
                </div>
                <div className="flex flex-col gap-3 sm:flex-row">
                  <CtaLink href="/register" size="md" className="cta-sheen">
                    Daftar sekarang
                    <ArrowRight className="h-4 w-4" aria-hidden="true" />
                  </CtaLink>
                  <CtaLink href="/events" variant="secondary" size="md">
                    Lihat event tersedia
                  </CtaLink>
                </div>
              </div>
            </div>
          </Reveal>
        </div>
      </section>
    </PublicShell>
  );
}
