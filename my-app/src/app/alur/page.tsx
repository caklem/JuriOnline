import Link from "next/link";
import {
  ArrowRight,
  BookOpen,
  Building2,
  CalendarDays,
  ClipboardList,
  Scale,
  Trophy,
  UserPlus,
} from "lucide-react";
import { CtaLink } from "@/components/public/cta-link";
import { PublicShell } from "@/components/public/public-shell";
import { Reveal, TiltCard } from "@/components/public/reveal";
import { RoleFlow } from "@/components/public/role-flow";
import { PageHero, SectionHeading } from "@/components/public/section";

export const instant = false;

export const metadata = {
  title: "Alur & Panduan — JURI.ON",
  description: "Gambaran singkat alur JURI.ON untuk peserta, juri, penyelenggara, dan admin.",
};

const STAGES = [
  {
    step: "01",
    title: "Buat akun",
    description:
      "Daftar sebagai peserta atau juri. Akun penyelenggara dan admin disiapkan oleh pengelola platform.",
    icon: UserPlus,
  },
  {
    step: "02",
    title: "Siapkan event",
    description: "Penyelenggara menyusun event, formulir, jadwal, kuota, dan kriteria penilaian.",
    icon: ClipboardList,
  },
  {
    step: "03",
    title: "Jalankan proses",
    description: "Peserta mendaftar dan membayar; juri menerima undangan lalu menilai peserta.",
    icon: Scale,
  },
  {
    step: "04",
    title: "Umumkan hasil",
    description: "Nilai direkap otomatis, leaderboard diperbarui, dan hasil siap dibagikan.",
    icon: Trophy,
  },
];

const ACTIONS = [
  {
    title: "Buat akun peserta",
    description: "Mulai ikut event sebagai peserta.",
    href: "/register",
    icon: UserPlus,
  },
  {
    title: "Cari event",
    description: "Lihat event yang sedang dibuka.",
    href: "/events",
    icon: CalendarDays,
  },
  {
    title: "Kenali platform",
    description: "Baca tentang JURI.ON dan nilai kami.",
    href: "/about",
    icon: BookOpen,
  },
  {
    title: "Company profile",
    description: "Lihat profil dan layanan JURI.ON.",
    href: "/company-profile",
    icon: Building2,
  },
];

export default function AlurPage() {
  return (
    <PublicShell>
      <PageHero
        eyebrow="Panduan"
        title="Alur JURI.ON"
        description="Gambaran singkat untuk peserta, juri, penyelenggara, dan admin. Pahami langkah, menu, dan hasil di setiap peran sebelum mulai."
      >
        <div className="flex flex-wrap items-center gap-3">
          <CtaLink href="/register" variant="secondary" size="md" className="cta-sheen">
            Buat akun peserta
            <ArrowRight className="h-4 w-4" aria-hidden="true" />
          </CtaLink>
          <Link
            href="/events"
            className="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/5 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
          >
            Lihat event
          </Link>
        </div>
      </PageHero>

      <section className="bg-white py-20 sm:py-24">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <Reveal>
            <SectionHeading
              eyebrow="Gambaran singkat"
              title="Empat tahap, satu alur kerja."
              description="Dari membuat akun sampai hasil diumumkan, setiap pihak melihat langkah yang relevan dengan perannya."
            />
          </Reveal>

          <div className="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {STAGES.map((stage, index) => {
              const Icon = stage.icon;

              return (
                <Reveal key={stage.step} delay={index * 0.06} className="h-full">
                  <TiltCard className="relative h-full rounded-3xl border border-slate-200 bg-white p-6 hover:border-[var(--brand-cobalt)]/25 hover:shadow-[0_24px_60px_-45px_rgba(11,31,70,0.45)]">
                    <div className="flex items-start justify-between gap-3">
                      <span className="grid h-11 w-11 place-items-center rounded-xl bg-[var(--brand-light-blue)] text-[var(--brand-cobalt-strong)] ring-1 ring-inset ring-[var(--brand-cobalt)]/10">
                        <Icon className="h-5 w-5" aria-hidden="true" />
                      </span>
                      <span className="text-[10px] font-semibold tabular-nums text-slate-300">{stage.step}</span>
                    </div>
                    <h3 className="mt-5 text-base font-semibold text-[var(--brand-navy)]">{stage.title}</h3>
                    <p className="mt-2 text-sm leading-6 text-slate-600">{stage.description}</p>

                    {index < STAGES.length - 1 ? (
                      <span
                        aria-hidden="true"
                        className="absolute -right-4 top-1/2 hidden h-8 w-8 -translate-y-1/2 place-items-center rounded-full border border-slate-200 bg-white text-slate-400 shadow-sm lg:grid"
                      >
                        <ArrowRight className="h-3.5 w-3.5" />
                      </span>
                    ) : null}
                  </TiltCard>
                </Reveal>
              );
            })}
          </div>
        </div>
      </section>

      <section className="bg-[var(--brand-surface)] py-20 sm:py-24">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <Reveal>
            <SectionHeading
              eyebrow="Per peran"
              title="Pilih peran, lihat langkahnya."
              description="Setiap peran punya menu dan tanggung jawab sendiri. Pilih peran untuk melihat alur lengkapnya."
            />
          </Reveal>

          <Reveal delay={0.06} className="mt-10">
            <RoleFlow />
          </Reveal>
        </div>
      </section>

      <section className="bg-white py-20 sm:py-24">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <Reveal>
            <SectionHeading
              eyebrow="Mulai"
              title="Pilih halaman yang Anda butuhkan."
              description="Pintasan ke akun, event, dan informasi tentang JURI.ON."
            />
          </Reveal>

          <div className="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {ACTIONS.map((action, index) => {
              const Icon = action.icon;

              return (
                <Reveal key={action.title} delay={index * 0.05} className="h-full">
                  <TiltCard className="h-full rounded-2xl border border-slate-200 bg-white">
                    <Link href={action.href} className="flex h-full flex-col p-6">
                      <span className="grid h-10 w-10 place-items-center rounded-xl bg-[var(--brand-surface)] text-[var(--brand-cobalt-strong)] ring-1 ring-inset ring-slate-200">
                        <Icon className="h-4 w-4" aria-hidden="true" />
                      </span>
                      <h3 className="mt-4 text-sm font-semibold text-[var(--brand-navy)]">{action.title}</h3>
                      <p className="mt-1.5 text-xs leading-5 text-slate-600">{action.description}</p>
                      <span className="mt-5 inline-flex items-center gap-1.5 text-xs font-semibold text-[var(--brand-cobalt-strong)]">
                        Buka
                        <ArrowRight className="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
                      </span>
                    </Link>
                  </TiltCard>
                </Reveal>
              );
            })}
          </div>
        </div>
      </section>
    </PublicShell>
  );
}
