"use client";

import { useRef, useState, type KeyboardEvent } from "react";
import { AnimatePresence, motion, useReducedMotion } from "motion/react";
import {
  ArrowRight,
  Building2,
  Check,
  LifeBuoy,
  Scale,
  ShieldCheck,
  Sparkles,
  UserRound,
  type LucideIcon,
} from "lucide-react";
import { CtaLink } from "./cta-link";

type RoleStep = { title: string; description: string; menu: string };
type RoleCta = { label: string; href: string };
type Role = {
  id: string;
  label: string;
  title: string;
  tagline: string;
  icon: LucideIcon;
  steps: RoleStep[];
  access: string[];
  note?: string;
  primary: RoleCta;
  secondary: RoleCta;
};

const ROLES: Role[] = [
  {
    id: "penyelenggara",
    label: "Penyelenggara",
    title: "Penyelenggara / Admin Event",
    tagline: "Mengelola event dari pembukaan pendaftaran sampai pengumuman hasil.",
    icon: Building2,
    steps: [
      {
        title: "Buat event",
        description: "Isi nama, kategori, jadwal, kuota, harga, lalu publikasikan event.",
        menu: "Buat Event",
      },
      {
        title: "Susun formulir",
        description: "Siapkan form pendaftaran peserta dan form penilaian untuk juri.",
        menu: "Form Pendaftaran & Form Penilaian Juri",
      },
      {
        title: "Verifikasi pembayaran",
        description: "Periksa bukti transfer peserta dari antrean verifikasi.",
        menu: "Verifikasi Pembayaran",
      },
      {
        title: "Tugaskan juri",
        description: "Kirim undangan dan hubungkan juri dengan form penilaian.",
        menu: "Assign Form ke Juri",
      },
      {
        title: "Pantau dan umumkan",
        description: "Lihat progres penilaian, rekap nilai, dan leaderboard event.",
        menu: "Rekapitulasi Nilai & Leaderboard",
      },
    ],
    access: ["Dashboard event", "Form builder", "Verifikasi pembayaran", "Rekap nilai"],
    note: "Akun penyelenggara/admin event disiapkan oleh pengelola platform. Jika belum punya akses, hubungi admin.",
    primary: { label: "Masuk", href: "/login" },
    secondary: { label: "Lihat event publik", href: "/events" },
  },
  {
    id: "juri",
    label: "Juri",
    title: "Juri",
    tagline: "Menerima undangan, menilai peserta, dan melihat hasil.",
    icon: Scale,
    steps: [
      {
        title: "Terima undangan",
        description: "Buka Undangan Juri dan konfirmasi undangan dari penyelenggara.",
        menu: "Undangan Juri",
      },
      {
        title: "Buka event",
        description: "Pilih event yang ditugaskan dan lihat peserta yang perlu dinilai.",
        menu: "Penilaian",
      },
      {
        title: "Isi penilaian",
        description: "Nilai setiap peserta sesuai kriteria pada form yang disiapkan.",
        menu: "Form penilaian",
      },
      {
        title: "Pantau hasil",
        description: "Lihat leaderboard event jika penyelenggara membukanya.",
        menu: "Leaderboard",
      },
    ],
    access: ["Undangan juri", "Daftar peserta", "Form penilaian", "Leaderboard"],
    note: "Akun juri bisa dibuat sendiri, lalu panitia meng-assign Anda ke event. Anda belum bisa menilai sebelum mendapat assignment.",
    primary: { label: "Masuk sebagai juri", href: "/login" },
    secondary: { label: "Daftar akun juri", href: "/register/jury" },
  },
  {
    id: "peserta",
    label: "Peserta",
    title: "Peserta",
    tagline: "Mendaftar event, mengunggah pembayaran, dan memantau hasil.",
    icon: UserRound,
    steps: [
      {
        title: "Buat akun",
        description: "Daftar dan lengkapi profil peserta.",
        menu: "Daftar",
      },
      {
        title: "Pilih event",
        description: "Cari event yang dibuka dan isi formulir pendaftaran.",
        menu: "Event",
      },
      {
        title: "Unggah pembayaran",
        description: "Untuk event berbayar, unggah bukti transfer agar diverifikasi admin.",
        menu: "Upload Bukti Pembayaran",
      },
      {
        title: "Pantau status",
        description: "Cek status pendaftaran dan hasil penilaian dari satu halaman.",
        menu: "Pendaftaran Saya",
      },
    ],
    access: ["Katalog event", "Form pendaftaran", "Upload pembayaran", "Status & hasil"],
    primary: { label: "Cari event", href: "/events" },
    secondary: { label: "Buat akun", href: "/register" },
  },
  {
    id: "admin-owner",
    label: "Admin Owner",
    title: "Admin Owner",
    tagline: "Mengawasi platform, pengguna, dan seluruh event.",
    icon: ShieldCheck,
    steps: [
      {
        title: "Pantau platform",
        description: "Lihat ringkasan event, pembayaran, dan penilaian dari dashboard.",
        menu: "Dashboard",
      },
      {
        title: "Kelola event",
        description: "Akses seluruh event dan penyelenggara di platform.",
        menu: "Event",
      },
      {
        title: "Awasi pembayaran & nilai",
        description: "Periksa antrean verifikasi dan nilai dari seluruh juri.",
        menu: "Verifikasi Pembayaran & Semua Penilaian",
      },
      {
        title: "Kelola pengguna",
        description: "Atur akun dan peran pengguna platform.",
        menu: "Kelola Pengguna",
      },
    ],
    access: ["Dashboard platform", "Semua event", "Pembayaran & nilai", "Pengguna & peran"],
    note: "Akun Admin Owner diberikan oleh pengelola platform dan memiliki akses penuh ke seluruh event.",
    primary: { label: "Masuk", href: "/login" },
    secondary: { label: "Lihat event publik", href: "/events" },
  },
];

export function RoleFlow() {
  const reduced = useReducedMotion();
  const [activeId, setActiveId] = useState(ROLES[0].id);
  const tabRefs = useRef<(HTMLButtonElement | null)[]>([]);
  const active = ROLES.find((role) => role.id === activeId) ?? ROLES[0];
  const ActiveIcon = active.icon;

  function select(index: number) {
    setActiveId(ROLES[index].id);
    tabRefs.current[index]?.focus();
  }

  function handleKeyDown(event: KeyboardEvent<HTMLDivElement>) {
    const index = ROLES.findIndex((role) => role.id === activeId);
    let next = index;

    if (event.key === "ArrowRight") next = (index + 1) % ROLES.length;
    else if (event.key === "ArrowLeft") next = (index - 1 + ROLES.length) % ROLES.length;
    else if (event.key === "Home") next = 0;
    else if (event.key === "End") next = ROLES.length - 1;
    else return;

    event.preventDefault();
    select(next);
  }

  return (
    <div>
      <div
        role="tablist"
        aria-label="Pilih peran"
        onKeyDown={handleKeyDown}
        className="flex flex-wrap gap-1 rounded-2xl border border-slate-200 bg-white p-1.5 shadow-[0_1px_2px_rgba(11,31,70,0.04)] sm:flex-nowrap"
      >
        {ROLES.map((role, index) => {
          const isActive = role.id === activeId;
          const Icon = role.icon;

          return (
            <button
              key={role.id}
              ref={(node) => {
                tabRefs.current[index] = node;
              }}
              type="button"
              role="tab"
              id={`role-tab-${role.id}`}
              aria-selected={isActive}
              aria-controls={`role-panel-${role.id}`}
              tabIndex={isActive ? 0 : -1}
              onClick={() => setActiveId(role.id)}
              className={`relative flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-colors ${
                isActive ? "text-white" : "text-slate-600 hover:text-[var(--brand-navy)]"
              }`}
            >
              {isActive ? (
                <motion.span
                  aria-hidden="true"
                  layoutId="role-flow-tab"
                  className="absolute inset-0 rounded-xl bg-[var(--brand-navy)]"
                  transition={reduced ? { duration: 0 } : { type: "spring", stiffness: 380, damping: 34 }}
                />
              ) : null}
              <Icon className="relative h-4 w-4" aria-hidden="true" />
              <span className="relative">{role.label}</span>
            </button>
          );
        })}
      </div>

      <AnimatePresence mode="wait" initial={false}>
        <motion.div
          key={active.id}
          id={`role-panel-${active.id}`}
          role="tabpanel"
          aria-labelledby={`role-tab-${active.id}`}
          initial={reduced ? { opacity: 0 } : { opacity: 0, y: 12 }}
          animate={reduced ? { opacity: 1 } : { opacity: 1, y: 0 }}
          exit={reduced ? { opacity: 0 } : { opacity: 0, y: -8 }}
          transition={reduced ? { duration: 0.25 } : { duration: 0.35, ease: [0.22, 1, 0.36, 1] }}
          className="mt-6 grid gap-6 lg:grid-cols-[1.12fr_0.88fr]"
        >
          <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_24px_60px_-50px_rgba(11,31,70,0.4)] sm:p-8">
            <div className="flex items-start gap-4">
              <span className="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-[var(--brand-navy)] text-white">
                <ActiveIcon className="h-5 w-5" aria-hidden="true" />
              </span>
              <div>
                <h3 className="text-lg font-semibold text-[var(--brand-navy)]">{active.title}</h3>
                <p className="mt-1 text-sm leading-6 text-slate-600">{active.tagline}</p>
              </div>
            </div>

            <ol className="relative mt-8">
              <div
                aria-hidden="true"
                className="absolute bottom-5 left-5 top-5 w-px bg-slate-200"
              />
              {active.steps.map((step, index) => (
                <motion.li
                  key={step.title}
                  initial={reduced ? false : { opacity: 0, x: -8 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={
                    reduced
                      ? { duration: 0 }
                      : { duration: 0.4, delay: 0.06 + index * 0.05, ease: [0.22, 1, 0.36, 1] }
                  }
                  className="relative flex gap-4 pb-7 last:pb-0"
                >
                  <span className="relative z-10 grid h-10 w-10 shrink-0 place-items-center rounded-full border border-[var(--brand-border)] bg-white text-xs font-semibold text-[var(--brand-cobalt-deep)] shadow-sm">
                    {String(index + 1).padStart(2, "0")}
                  </span>
                  <div className="min-w-0 pt-1">
                    <h4 className="text-sm font-semibold text-[var(--brand-navy)]">{step.title}</h4>
                    <p className="mt-1 text-sm leading-6 text-slate-600">{step.description}</p>
                    <p className="mt-2 inline-flex items-center gap-1.5 rounded-full bg-[var(--brand-surface)] px-2.5 py-1 text-[10px] font-semibold text-slate-500 ring-1 ring-inset ring-slate-200">
                      <Sparkles className="h-3 w-3 text-[var(--brand-cobalt)]" aria-hidden="true" />
                      Menu: {step.menu}
                    </p>
                  </div>
                </motion.li>
              ))}
            </ol>
          </div>

          <div className="flex flex-col gap-4">
            <div className="rounded-3xl border border-slate-200 bg-[var(--brand-surface)] p-6 sm:p-7">
              <p className="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                Akses setelah masuk
              </p>
              <ul className="mt-4 space-y-2.5">
                {active.access.map((item) => (
                  <li key={item} className="flex items-center gap-2.5 text-sm text-slate-700">
                    <span className="grid h-5 w-5 shrink-0 place-items-center rounded-full bg-[var(--brand-light-blue)] text-[var(--brand-cobalt-strong)]">
                      <Check className="h-3 w-3" aria-hidden="true" />
                    </span>
                    {item}
                  </li>
                ))}
              </ul>

              {active.note ? (
                <p className="mt-4 rounded-xl bg-white px-3.5 py-3 text-[11px] leading-5 text-slate-500 ring-1 ring-inset ring-slate-200">
                  {active.note}
                </p>
              ) : null}

              <div className="mt-6 flex flex-col gap-2.5 border-t border-slate-200 pt-5">
                <CtaLink href={active.primary.href} size="md" className="cta-sheen w-full">
                  {active.primary.label}
                  <ArrowRight className="h-4 w-4" aria-hidden="true" />
                </CtaLink>
                <CtaLink href={active.secondary.href} variant="secondary" size="md" className="w-full">
                  {active.secondary.label}
                </CtaLink>
              </div>
            </div>

            <div className="relative overflow-hidden rounded-3xl bg-[var(--brand-navy)] p-6 text-white">
              <div aria-hidden="true" className="jurion-dotgrid absolute inset-0 opacity-25" />
              <div
                aria-hidden="true"
                className="pointer-events-none absolute -right-12 -top-12 h-36 w-36 rounded-full bg-[var(--brand-cobalt)]/25 blur-3xl"
              />
              <div className="relative flex items-start gap-3">
                <span className="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white/10 text-[var(--brand-cobalt)] ring-1 ring-inset ring-white/15">
                  <LifeBuoy className="h-4 w-4" aria-hidden="true" />
                </span>
                <div>
                  <p className="text-sm font-semibold">Butuh bantuan?</p>
                  <p className="mt-1 text-xs leading-5 text-slate-300">
                    Menu di dasbor menyesuaikan peran akun. Hubungi tim JURI.ON jika ada kendala saat memakai alur.
                  </p>
                  <a
                    href="mailto:support@jurionline.com"
                    className="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-[#93c3ff] transition hover:text-white"
                  >
                    support@jurionline.com
                    <ArrowRight className="h-3.5 w-3.5" aria-hidden="true" />
                  </a>
                </div>
              </div>
            </div>
          </div>
        </motion.div>
      </AnimatePresence>
    </div>
  );
}
