import Link from "next/link";
import {
  ArrowRight,
  BarChart3,
  BookOpen,
  Building2,
  CalendarDays,
  Check,
  CreditCard,
  FileText,
  Route as RouteIcon,
  Scale,
  ShieldCheck,
  Sparkles,
  Trophy,
  Users,
} from "lucide-react";
import { BrandLockup } from "@/components/brand/lockup";
import { fetchPublicEvents, type PublicEvent } from "@/components/public/api";
import { CtaLink } from "@/components/public/cta-link";
import { EventCard } from "@/components/public/event-card";
import { formatDate, formatPrice, limit } from "@/components/public/format";
import { PublicShell } from "@/components/public/public-shell";
import { CountUp, Parallax, Reveal, TiltCard } from "@/components/public/reveal";
import { SectionHeading } from "@/components/public/section";
import { HowItWorks } from "@/components/public/steps";

export const metadata = {
  title: "JURI.ON — Platform Penilaian Digital untuk Kompetisi",
  description:
    "JURI.ON menyatukan pendaftaran peserta, penilaian juri, verifikasi pembayaran, dan rekapitulasi hasil dalam satu alur kerja yang rapi.",
};

// Data event diambil dari API saat request; jangan diprerender saat build.
export const instant = false;

const FEATURES = [
  {
    title: "Form builder fleksibel",
    description: "Susun formulir pendaftaran dan penilaian sesuai kebutuhan event tanpa menulis kode.",
    icon: FileText,
  },
  {
    title: "Penilaian multi-juri",
    description: "Setiap juri punya formulir dan kriteria penilaiannya sendiri dalam satu event.",
    icon: Scale,
  },
  {
    title: "Rekapitulasi otomatis",
    description: "Nilai akhir dihitung konsisten dari seluruh juri, tanpa rekap manual.",
    icon: BarChart3,
  },
  {
    title: "Manajemen peserta",
    description: "Pantau pendaftaran, verifikasi, dan status peserta dari satu dasbor.",
    icon: Users,
  },
  {
    title: "Verifikasi pembayaran",
    description: "Unggah bukti bayar dan kelola verifikasi tanpa berpindah aplikasi.",
    icon: CreditCard,
  },
  {
    title: "Leaderboard real-time",
    description: "Peringkat diperbarui saat penilaian masuk dan siap dilihat kapan saja.",
    icon: Trophy,
  },
];

const STEPS = [
  {
    title: "Siapkan event",
    description: "Tentukan kategori, jadwal, kuota, dan formulir pendaftaran yang sesuai dengan kebutuhan kompetisi.",
  },
  {
    title: "Kelola peserta dan juri",
    description: "Verifikasi pembayaran, tugaskan juri ke formulir penilaian, dan pantau progres dari dasbor.",
  },
  {
    title: "Umumkan hasil",
    description: "Rekap nilai otomatis, tinjau leaderboard, dan bagikan hasil kompetisi dengan lebih tenang.",
  },
];

const VALUES = [
  {
    title: "Profesional",
    description: "Alur kerja yang jelas untuk tim penyelenggara, juri, dan peserta.",
    icon: ShieldCheck,
  },
  {
    title: "Transparan",
    description: "Riwayat penilaian dan status peserta dapat ditelusuri dengan mudah.",
    icon: Check,
  },
  {
    title: "Terpercaya",
    description: "Akses berbasis peran menjaga data event dan penilaian tetap aman.",
    icon: Scale,
  },
];

const PREVIEW_ROWS = [
  { initials: "AI", name: "Atlas Idaman", score: "98.5", avatar: "bg-[var(--brand-light-blue)] text-[var(--brand-cobalt-deep)]", width: "98%" },
  { initials: "II", name: "IIQSI", score: "97.0", avatar: "bg-sky-50 text-sky-700", width: "92%" },
  { initials: "JM", name: "Jadid Muazam", score: "96.5", avatar: "bg-emerald-50 text-emerald-700", width: "86%" },
];

const CAPABILITIES = [
  { label: "Pendaftaran", value: "Form dinamis", icon: FileText },
  { label: "Penilaian", value: "Multi-juri", icon: Scale },
  { label: "Rekap", value: "Otomatis", icon: BarChart3 },
  { label: "Akses", value: "Berbasis peran", icon: ShieldCheck },
];

const SHORTCUTS = [
  {
    title: "Alur & panduan",
    description: "Pahami langkah untuk setiap peran.",
    href: "/alur",
    icon: RouteIcon,
  },
  {
    title: "Event",
    description: "Temukan kompetisi yang sedang dibuka.",
    href: "/events",
    icon: CalendarDays,
  },
  {
    title: "Tentang",
    description: "Kenali platform dan nilai kami.",
    href: "/about",
    icon: BookOpen,
  },
  {
    title: "Company profile",
    description: "Profil dan layanan JURI.ON.",
    href: "/company-profile",
    icon: Building2,
  },
];

function FeaturedEvent({ event }: { event: PublicEvent }) {
  const href = `/events/${event.slug ?? event.id}`;

  return (
    <div className="mt-12 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_30px_70px_-50px_rgba(11,31,70,0.4)] lg:grid lg:grid-cols-2">
      <div className="relative min-h-[280px] overflow-hidden bg-[var(--brand-navy)] lg:min-h-[430px]">
        {event.banner_url ? (
          <div className="absolute inset-0">
            <img
              src={event.banner_url}
              alt=""
              aria-hidden="true"
              className="absolute inset-0 h-full w-full scale-110 object-cover opacity-40 blur-2xl"
            />
            <div aria-hidden="true" className="absolute inset-0 bg-[var(--brand-navy)]/20" />
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img
              src={event.banner_url}
              alt={event.name}
              className="relative z-[1] h-full w-full object-contain p-5"
            />
          </div>
        ) : (
          <>
            <div aria-hidden="true" className="jurion-dotgrid absolute inset-0 opacity-30" />
            <div className="absolute inset-0 grid place-items-center">
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img src="/brand/jurion-mark-white.png" alt="" className="h-20 w-20 opacity-90" />
            </div>
          </>
        )}
      </div>

      <div className="flex flex-col justify-center p-7 sm:p-10">
        <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--brand-cobalt-strong)]">Event unggulan</p>
        <div className="mt-3 flex flex-wrap items-center gap-2">
          {event.category ? (
            <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200">
              {event.category}
            </span>
          ) : null}
          <span className="rounded-full bg-[var(--brand-light-blue)] px-3 py-1 text-xs font-semibold text-[var(--brand-cobalt-deep)] ring-1 ring-inset ring-[var(--brand-cobalt)]/15">
            {event.status_label}
          </span>
        </div>
        <h3 className="mt-3 text-balance text-2xl font-semibold tracking-tight text-[var(--brand-navy)] sm:text-3xl">
          {event.name}
        </h3>
        <p className="mt-4 max-w-xl text-base leading-7 text-slate-600">{limit(event.description, 220)}</p>

        <dl className="mt-8 grid grid-cols-2 gap-5 border-t border-slate-100 pt-6 text-sm">
          <div>
            <dt className="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Penyelenggara</dt>
            <dd className="mt-1 font-medium text-slate-700">{event.organizer ?? "-"}</dd>
          </div>
          <div>
            <dt className="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Harga</dt>
            <dd className="mt-1 font-medium text-slate-700">{formatPrice(event.price)}</dd>
          </div>
          <div>
            <dt className="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Jadwal</dt>
            <dd className="mt-1 font-medium text-slate-700">{formatDate(event.event_start)}</dd>
          </div>
          <div>
            <dt className="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Kuota</dt>
            <dd className="mt-1 font-medium text-slate-700">
              {event.available_slots} / {event.quota} tersisa
            </dd>
          </div>
        </dl>

        <CtaLink href={href} size="md" className="cta-sheen mt-8 w-fit">
          Lihat detail event
          <ArrowRight className="h-4 w-4" aria-hidden="true" />
        </CtaLink>
      </div>
    </div>
  );
}

function EventPromoCard() {
  return (
    <article className="group relative flex h-full flex-col justify-between overflow-hidden rounded-3xl bg-[var(--brand-navy)] p-7 text-white">
      <div aria-hidden="true" className="jurion-dotgrid absolute inset-0 opacity-30" />
      <div
        aria-hidden="true"
        className="jurion-aurora absolute -right-12 -top-12 h-40 w-40 rounded-full bg-[var(--brand-cobalt)]/30 blur-3xl"
      />
      <div className="relative">
        <span className="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/6 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-200">
          <Sparkles className="h-3.5 w-3.5 text-[var(--brand-cobalt)]" aria-hidden="true" />
          Untuk penyelenggara
        </span>
        <h3 className="mt-5 text-balance text-xl font-semibold leading-snug">
          Punya event yang ingin dinilai dengan lebih rapi?
        </h3>
        <p className="mt-3 text-sm leading-6 text-slate-300">
          Buat event, susun formulir, tugaskan juri, dan pantau hasil dari satu dasbor.
        </p>
      </div>
      <CtaLink href="/register" variant="secondary" size="md" className="cta-sheen relative mt-8 w-fit">
        Mulai sekarang
        <ArrowRight className="h-4 w-4" aria-hidden="true" />
      </CtaLink>
    </article>
  );
}

function DashboardPreview() {
  return (
    <div className="relative mx-auto w-full max-w-lg lg:max-w-none" aria-hidden="true">
      <div className="absolute -inset-10 rounded-full bg-[var(--brand-cobalt)]/20 blur-3xl" />
      <div className="jurion-aurora absolute -right-10 top-10 h-40 w-40 rounded-full bg-[var(--brand-cobalt)]/25 blur-2xl" />

      <div className="relative rounded-[1.75rem] border border-white/12 bg-white/6 p-2 shadow-2xl ring-1 ring-white/10 backdrop-blur">
        <div className="rounded-[1.35rem] bg-white p-5">
          <div className="flex items-start justify-between gap-4">
            <div>
              <p className="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">Pratinjau dasbor</p>
              <p className="mt-1 text-sm font-semibold text-[var(--brand-navy)]">Festival Banjari</p>
            </div>
            <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
              <span className="jurion-pulse h-1.5 w-1.5 rounded-full bg-emerald-500" />
              Live
            </span>
          </div>

          <div className="mt-5 space-y-3">
            {PREVIEW_ROWS.map((row, index) => (
              <div key={row.name} className="flex items-center gap-3">
                <span className="w-4 text-xs font-semibold text-slate-400">{index + 1}</span>
                <span className={`grid h-8 w-8 place-items-center rounded-full text-[11px] font-semibold ${row.avatar}`}>
                  {row.initials}
                </span>
                <div className="min-w-0 flex-1">
                  <div className="flex items-center justify-between gap-3">
                    <p className="truncate text-xs font-semibold text-slate-700">{row.name}</p>
                    <p className="text-xs font-semibold tabular-nums text-[var(--brand-navy)]">{row.score}</p>
                  </div>
                  <div className="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                    <div
                      className="jurion-bar h-full rounded-full bg-gradient-to-r from-[var(--brand-cobalt-strong)] to-[var(--brand-cobalt)]"
                      style={{ width: row.width, animationDelay: `${index * 120 + 250}ms` }}
                    />
                  </div>
                </div>
              </div>
            ))}
          </div>

          <div className="mt-5 grid grid-cols-3 gap-2 rounded-xl bg-[var(--brand-surface)] p-3 text-center ring-1 ring-inset ring-slate-100">
            {[
              ["24", "Grup"],
              ["5", "Juri"],
              ["3", "Kategori"],
            ].map(([value, label]) => (
              <div key={label}>
                <p className="text-sm font-semibold tabular-nums text-[var(--brand-navy)]">{value}</p>
                <p className="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-slate-400">{label}</p>
              </div>
            ))}
          </div>
        </div>
      </div>

      <div className="absolute -bottom-20 -left-6 hidden w-60 rounded-2xl border border-slate-200 bg-white p-4 shadow-xl sm:block">
        <div className="flex items-start gap-3">
          <span className="grid h-9 w-9 place-items-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-100">
            <ShieldCheck className="h-4 w-4" aria-hidden="true" />
          </span>
          <div>
            <p className="text-xs font-semibold text-[var(--brand-navy)]">Penilaian terverifikasi</p>
            <p className="mt-1 text-[11px] leading-5 text-slate-500">Setiap nilai tercatat dan dapat ditelusuri.</p>
          </div>
        </div>
      </div>
    </div>
  );
}

function FeatureMock() {
  return (
    <div className="mt-6 rounded-2xl border border-[var(--brand-border)] bg-white/80 p-4 shadow-sm backdrop-blur">
      <div className="flex items-center justify-between gap-3">
        <p className="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Form pendaftaran</p>
        <span className="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
          <span className="h-1 w-1 rounded-full bg-emerald-500" />
          Aktif
        </span>
      </div>
      <div className="mt-3 space-y-2">
        {["Nama lengkap", "Kategori lomba", "Asal sekolah"].map((field, index) => (
          <div
            key={field}
            className="flex items-center justify-between rounded-lg border border-slate-200 bg-white px-3 py-2"
          >
            <span className="text-[11px] text-slate-500">{field}</span>
            <span className={`h-1.5 rounded-full bg-[var(--brand-light-blue)] ${index === 0 ? "w-16" : index === 1 ? "w-10" : "w-14"}`} />
          </div>
        ))}
      </div>
      <div className="mt-3 flex items-center gap-2 rounded-lg bg-[var(--brand-navy)] px-3 py-2 text-[11px] font-semibold text-white">
        Simpan formulir
        <ArrowRight className="ml-auto h-3.5 w-3.5" aria-hidden="true" />
      </div>
    </div>
  );
}

function MiniLeaderboard() {
  return (
    <div className="mt-5 space-y-2" aria-hidden="true">
      {[
        ["1", "Atlas Idaman", "98.5", "96%"],
        ["2", "IIQSI", "97.0", "88%"],
        ["3", "Jadid Muazam", "96.5", "80%"],
      ].map(([rank, name, score, width]) => (
        <div key={name} className="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2">
          <span className="text-[10px] font-semibold text-slate-400">{rank}</span>
          <span className="min-w-0 flex-1 truncate text-xs font-medium text-slate-700">{name}</span>
          <span className="w-16 overflow-hidden rounded-full bg-slate-100">
            <span className="jurion-bar block h-1.5 rounded-full bg-[var(--brand-cobalt)]" style={{ width }} />
          </span>
          <span className="text-xs font-semibold tabular-nums text-[var(--brand-navy)]">{score}</span>
        </div>
      ))}
    </div>
  );
}

export default async function HomePage() {
  const events = (await fetchPublicEvents()).slice(0, 3);

  return (
    <PublicShell>
      <section className="relative overflow-hidden bg-[var(--brand-navy)] text-white">
        <div aria-hidden="true" className="jurion-dotgrid pointer-events-none absolute inset-0 opacity-50 [mask-image:linear-gradient(to_bottom,black,transparent_90%)]" />
        <div
          aria-hidden="true"
          className="jurion-aurora pointer-events-none absolute -right-40 -top-56 h-[42rem] w-[42rem] rounded-full bg-[var(--brand-cobalt)]/25 blur-[120px]"
        />
        <div
          aria-hidden="true"
          className="pointer-events-none absolute -bottom-48 -left-24 h-[30rem] w-[30rem] rounded-full bg-[var(--brand-cobalt)]/12 blur-[110px]"
        />

        <div className="relative mx-auto max-w-7xl px-6 pb-24 pt-16 sm:pt-20 lg:px-8 lg:pb-32 lg:pt-24">
          <div className="grid items-center gap-16 lg:grid-cols-[1.02fr_0.98fr]">
            <div>
              <Reveal>
                <span className="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/6 px-3 py-1 text-xs font-medium text-slate-200 backdrop-blur">
                  <span className="jurion-pulse h-1.5 w-1.5 rounded-full bg-emerald-400" />
                  Platform penilaian digital
                </span>
              </Reveal>

              <Reveal delay={0.06}>
                <h1 className="mt-6 max-w-2xl text-balance text-4xl font-semibold leading-[1.05] tracking-tight text-white sm:text-5xl lg:text-[3.75rem]">
                  Penilaian kompetisi yang{" "}
                  <span className="text-[#93c3ff]">adil</span>, transparan, dan efisien.
                </h1>
              </Reveal>

              <Reveal delay={0.12}>
                <p className="mt-6 max-w-xl text-base leading-7 text-slate-300 sm:text-lg">
                  JURI.ON menyatukan pendaftaran peserta, penilaian juri, verifikasi pembayaran, dan rekapitulasi hasil
                  dalam satu alur kerja yang rapi.
                </p>
              </Reveal>

              <Reveal delay={0.18}>
                <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                  <CtaLink href="/register" variant="secondary" size="lg" className="cta-sheen">
                    Mulai sekarang
                    <ArrowRight className="h-4 w-4" aria-hidden="true" />
                  </CtaLink>
                  <Link
                    href="#events"
                    className="group inline-flex items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/5 px-5 py-3 text-sm font-semibold text-white transition hover:border-white/25 hover:bg-white/10"
                  >
                    Jelajahi event
                    <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
                  </Link>
                </div>
              </Reveal>

              <Reveal delay={0.22}>
                <Link
                  href="/alur"
                  className="group mt-5 inline-flex items-center gap-2 text-sm font-medium text-slate-300 transition hover:text-white"
                >
                  Baru di sini? Lihat alur per peran
                  <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
                </Link>
              </Reveal>

              <Reveal delay={0.26}>
                <div className="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-slate-400">
                  {["Form builder", "Multi-juri", "Rekap otomatis"].map((item) => (
                    <span key={item} className="inline-flex items-center gap-2">
                      <Check className="h-4 w-4 text-emerald-400" aria-hidden="true" />
                      {item}
                    </span>
                  ))}
                </div>
              </Reveal>
            </div>

            <Reveal delay={0.16} y={32} className="lg:pl-4">
              <Parallax distance={26}>
                <DashboardPreview />
              </Parallax>
            </Reveal>
          </div>
        </div>
      </section>

      <section className="relative z-10 border-b border-slate-200 bg-white">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <div className="grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-slate-200 bg-slate-200 shadow-[0_20px_50px_-40px_rgba(11,31,70,0.5)] sm:grid-cols-4 lg:-mt-8">
            {CAPABILITIES.map((item, index) => {
              const Icon = item.icon;

              return (
                <Reveal key={item.label} delay={index * 0.05} className="bg-white">
                  <div className="group h-full px-5 py-6 transition hover:bg-[var(--brand-surface)]">
                    <span className="grid h-9 w-9 place-items-center rounded-lg bg-[var(--brand-light-blue)] text-[var(--brand-cobalt-strong)] transition group-hover:scale-105">
                      <Icon className="h-4 w-4" aria-hidden="true" />
                    </span>
                    <p className="mt-4 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                      {item.label}
                    </p>
                    <p className="mt-1.5 text-sm font-semibold text-[var(--brand-navy)]">{item.value}</p>
                  </div>
                </Reveal>
              );
            })}
          </div>
        </div>
      </section>

      <section className="border-b border-slate-100 bg-[var(--brand-surface)] py-20 sm:py-24">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <Reveal>
            <SectionHeading
              eyebrow="Jelajahi"
              title="Temukan halaman yang Anda butuhkan."
              description="Pintasan ke panduan alur, event, dan informasi tentang JURI.ON."
            />
          </Reveal>

          <div className="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {SHORTCUTS.map((item, index) => {
              const Icon = item.icon;

              return (
                <Reveal key={item.title} delay={index * 0.05} className="h-full">
                  <TiltCard className="h-full rounded-2xl border border-slate-200 bg-white hover:border-[var(--brand-cobalt)]/25 hover:shadow-[0_24px_60px_-45px_rgba(11,31,70,0.45)]">
                    <Link href={item.href} className="flex h-full flex-col p-6">
                      <span className="grid h-10 w-10 place-items-center rounded-xl bg-[var(--brand-light-blue)] text-[var(--brand-cobalt-strong)]">
                        <Icon className="h-4 w-4" aria-hidden="true" />
                      </span>
                      <h3 className="mt-4 text-sm font-semibold text-[var(--brand-navy)]">{item.title}</h3>
                      <p className="mt-1.5 text-xs leading-5 text-slate-600">{item.description}</p>
                      <span className="mt-auto inline-flex items-center gap-1.5 pt-5 text-xs font-semibold text-[var(--brand-cobalt-strong)]">
                        Buka
                        <ArrowRight
                          className="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5"
                          aria-hidden="true"
                        />
                      </span>
                    </Link>
                  </TiltCard>
                </Reveal>
              );
            })}
          </div>
        </div>
      </section>

      <section id="fitur" className="scroll-mt-24 bg-white py-20 sm:py-28">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <Reveal>
            <SectionHeading
              eyebrow="Fitur"
              title="Semua yang dibutuhkan untuk kompetisi yang rapi."
              description="Dari formulir pendaftaran sampai pengumuman hasil, setiap langkah punya tempatnya."
              align="center"
            />
          </Reveal>

          <div className="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            {FEATURES.map((feature, index) => {
              const Icon = feature.icon;
              const isHero = index === 0;
              const isWide = index === FEATURES.length - 1;

              return (
                <Reveal
                  key={feature.title}
                  delay={Math.min(index, 5) * 0.05}
                  className={
                    isHero ? "sm:col-span-2 lg:col-span-2 lg:row-span-2" : isWide ? "sm:col-span-2 lg:col-span-4" : ""
                  }
                >
                  <TiltCard
                    className={`flex h-full flex-col rounded-3xl border p-6 transition duration-300 ${
                      isHero
                        ? "border-[var(--brand-border)] bg-gradient-to-br from-[var(--brand-surface)] to-[var(--brand-light-blue)]/70 shadow-[0_20px_60px_-50px_rgba(11,31,70,0.5)]"
                        : "border-slate-200 bg-white hover:border-[var(--brand-cobalt)]/25 hover:shadow-[0_24px_60px_-45px_rgba(11,31,70,0.45)]"
                    }`}
                  >
                    <div className="flex items-start justify-between gap-4">
                      <span
                        className={`grid h-11 w-11 place-items-center rounded-xl ring-1 ring-inset ${
                          isHero
                            ? "bg-white text-[var(--brand-cobalt-strong)] ring-[var(--brand-border)]"
                            : "bg-[var(--brand-light-blue)] text-[var(--brand-cobalt-strong)] ring-[var(--brand-cobalt)]/10"
                        }`}
                      >
                        <Icon className="h-5 w-5" aria-hidden="true" />
                      </span>
                      <span className="text-[10px] font-semibold tabular-nums text-slate-300">
                        {String(index + 1).padStart(2, "0")}
                      </span>
                    </div>

                    <h3 className={`mt-5 font-semibold text-[var(--brand-navy)] ${isHero ? "text-xl" : "text-base"}`}>
                      {feature.title}
                    </h3>
                    <p className={`mt-2 leading-6 text-slate-600 ${isHero ? "max-w-md text-sm" : "text-sm"}`}>
                      {feature.description}
                    </p>

                    {isHero ? <FeatureMock /> : null}
                    {isWide ? (
                      <div className="mt-2 grid gap-4 lg:grid-cols-[1fr_1.1fr] lg:items-center">
                        <p className="text-sm leading-6 text-slate-500">
                          Nilai masuk dari setiap juri langsung memperbarui peringkat. Tidak ada lagi rekap manual
                          menjelang pengumuman.
                        </p>
                        <MiniLeaderboard />
                      </div>
                    ) : null}
                  </TiltCard>
                </Reveal>
              );
            })}
          </div>
        </div>
      </section>

      <section id="cara-kerja" className="scroll-mt-24 bg-[var(--brand-surface)] py-20 sm:py-28">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <HowItWorks steps={STEPS} />
        </div>
      </section>

      <section id="events" className="scroll-mt-24 bg-white py-20 sm:py-28">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <Reveal>
            <SectionHeading
              eyebrow="Event"
              title="Event terbaru"
              description="Lihat event yang sedang berjalan atau sudah dipublikasikan oleh penyelenggara."
              action={
                <Link
                  href="/events"
                  className="group inline-flex items-center gap-2 text-sm font-semibold text-[var(--brand-cobalt-strong)] transition hover:text-[var(--brand-cobalt-deep)]"
                >
                  Lihat semua event
                  <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
                </Link>
              }
            />
          </Reveal>

          {events.length === 0 ? (
            <Reveal className="mt-12">
              <div className="rounded-3xl border border-dashed border-slate-300 bg-[var(--brand-surface)] p-10 text-center">
                <div className="mx-auto grid h-12 w-12 place-items-center rounded-full bg-white text-[var(--brand-cobalt-strong)] shadow-sm">
                  <Sparkles className="h-5 w-5" aria-hidden="true" />
                </div>
                <h3 className="mt-4 text-base font-semibold text-[var(--brand-navy)]">Belum ada event yang dipublikasikan</h3>
                <p className="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">
                  Event baru akan tampil di sini setelah penyelenggara mempublikasikannya. Sementara itu, Anda tetap bisa
                  membuat akun dan menyiapkan event pertama.
                </p>
                <CtaLink href="/register" size="md" className="mt-6">
                  Daftar sekarang
                </CtaLink>
              </div>
            </Reveal>
          ) : events.length === 1 ? (
            <Reveal>
              <FeaturedEvent event={events[0]} />
            </Reveal>
          ) : (
            <div className="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
              {events.map((event, index) => (
                <Reveal key={event.id} className="h-full" delay={index * 0.06}>
                  <EventCard event={event} ctaLabel="Lihat detail" limitDescription={110} />
                </Reveal>
              ))}
              {events.length < 3 ? (
                <Reveal className="h-full" delay={events.length * 0.06}>
                  <EventPromoCard />
                </Reveal>
              ) : null}
            </div>
          )}
        </div>
      </section>

      <section id="tentang" className="scroll-mt-24 bg-[var(--brand-surface)] py-20 sm:py-28">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <div className="grid gap-12 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:gap-16">
            <Reveal>
              <SectionHeading
                eyebrow="Tentang JURI.ON"
                title="Dibangun untuk penyelenggara, juri, dan peserta."
                description="Kami percaya penilaian yang baik dimulai dari proses yang jelas. JURI.ON membantu tim menyimpan data di satu tempat, mengurangi rekap manual, dan membuat hasil kompetisi lebih mudah dipertanggungjawabkan."
              />
              <Link
                href="/about"
                className="group mt-8 inline-flex items-center gap-2 text-sm font-semibold text-[var(--brand-cobalt-strong)] transition hover:text-[var(--brand-cobalt-deep)]"
              >
                Pelajari lebih lanjut
                <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
              </Link>
            </Reveal>

            <div className="grid gap-4">
              <Reveal delay={0.05}>
                <div className="relative overflow-hidden rounded-3xl bg-[var(--brand-navy)] p-7 text-white sm:p-9">
                  <div aria-hidden="true" className="jurion-dotgrid absolute inset-0 opacity-30" />
                  <div
                    aria-hidden="true"
                    className="jurion-aurora absolute -right-16 -top-16 h-48 w-48 rounded-full bg-[var(--brand-cobalt)]/30 blur-3xl"
                  />
                  <div className="relative">
                    <BrandLockup href="/" variant="dark" tile descriptor />
                    <p className="mt-7 max-w-sm text-lg font-medium leading-7 text-white/90">
                      &ldquo;Satu alur kerja untuk pendaftaran, penilaian, dan pengumuman hasil.&rdquo;
                    </p>
                    <div className="mt-7 flex flex-wrap gap-2">
                      {["Profesional", "Transparan", "Terpercaya"].map((item) => (
                        <span
                          key={item}
                          className="rounded-full border border-white/12 bg-white/6 px-3 py-1 text-[11px] font-medium text-slate-200"
                        >
                          {item}
                        </span>
                      ))}
                    </div>
                  </div>
                </div>
              </Reveal>

              <div className="grid gap-4 sm:grid-cols-3">
                {VALUES.map((value, index) => {
                  const Icon = value.icon;

                  return (
                    <Reveal key={value.title} delay={0.08 + index * 0.05}>
                      <TiltCard className="h-full rounded-2xl border border-slate-200 bg-white p-5">
                        <span className="grid h-9 w-9 place-items-center rounded-lg bg-[var(--brand-light-blue)] text-[var(--brand-cobalt-strong)]">
                          <Icon className="h-4 w-4" aria-hidden="true" />
                        </span>
                        <h3 className="mt-4 text-sm font-semibold text-[var(--brand-navy)]">{value.title}</h3>
                        <p className="mt-2 text-xs leading-5 text-slate-600">{value.description}</p>
                      </TiltCard>
                    </Reveal>
                  );
                })}
              </div>
            </div>
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
          <Reveal>
            <div className="grid gap-10 lg:grid-cols-[1.2fr_0.8fr] lg:items-end">
              <div>
                <p className="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#93c3ff]">
                  <span aria-hidden="true" className="h-px w-6 bg-white/40" />
                  Mulai
                </p>
                <h2 className="mt-4 max-w-2xl text-balance text-3xl font-semibold leading-[1.1] tracking-tight sm:text-4xl lg:text-5xl">
                  Siap menjalankan event yang lebih rapi?
                </h2>
                <p className="mt-4 max-w-xl text-base leading-7 text-slate-300">
                  Buat akun, siapkan event, dan mulai penilaian digital bersama JURI.ON.
                </p>
                <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                  <CtaLink href="/register" variant="secondary" size="lg" className="cta-sheen">
                    Mulai sekarang
                    <ArrowRight className="h-4 w-4" aria-hidden="true" />
                  </CtaLink>
                  <Link
                    href="/login"
                    className="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/5 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                  >
                    Masuk ke akun
                  </Link>
                </div>
              </div>

              <div className="grid grid-cols-3 gap-4 rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                {[
                  { value: 3, label: "Peran terhubung" },
                  { value: 6, label: "Fitur inti" },
                  { value: 1, label: "Alur kerja" },
                ].map((item) => (
                  <div key={item.label}>
                    <p className="text-2xl font-semibold tabular-nums text-white">
                      <CountUp value={item.value} />
                    </p>
                    <p className="mt-1 text-[10px] font-medium uppercase tracking-wide text-slate-400">{item.label}</p>
                  </div>
                ))}
              </div>
            </div>
          </Reveal>
        </div>
      </section>
    </PublicShell>
  );
}
