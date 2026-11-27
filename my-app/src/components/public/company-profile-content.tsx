"use client";

import { useEffect, useState } from "react";
import { motion, useReducedMotion } from "motion/react";
import {
  ArrowRight,
  BadgeCheck,
  BarChart3,
  CalendarDays,
  CreditCard,
  Eye,
  Globe,
  LifeBuoy,
  Mail,
  Scale,
  ShieldCheck,
  Sparkles,
  Trophy,
  Users,
  Zap,
} from "lucide-react";
import { BrandLockup } from "@/components/brand/lockup";
import { CtaLink } from "./cta-link";
import { Parallax, Reveal, TiltCard } from "./reveal";
import { SectionHeading } from "./section";

const SECTIONS = [
  { id: "tentang", label: "Tentang" },
  { id: "visi-misi", label: "Visi & Misi" },
  { id: "nilai", label: "Nilai" },
  { id: "layanan", label: "Layanan" },
  { id: "kontak", label: "Kontak" },
];

const VALUES = [
  {
    title: "Profesional",
    description: "Layanan dengan standar tinggi di setiap aspek platform, dari pendaftaran sampai pengumuman.",
    icon: BadgeCheck,
  },
  {
    title: "Transparan",
    description: "Proses penilaian dan manajemen event dapat ditelusuri oleh pihak yang berwenang.",
    icon: Eye,
  },
  {
    title: "Inovatif",
    description: "Fitur terus dikembangkan mengikuti kebutuhan nyata penyelenggara dan juri.",
    icon: Zap,
  },
  {
    title: "Terpercaya",
    description: "Layanan yang konsisten, akses berbasis peran, dan data yang terkendali.",
    icon: ShieldCheck,
  },
];

const SERVICES = [
  {
    title: "Manajemen Event",
    description: "Kelola event dari awal hingga akhir dengan fitur lengkap untuk penyelenggara.",
    icon: CalendarDays,
  },
  {
    title: "Sistem Penilaian",
    description: "Form penilaian custom yang dapat disesuaikan dengan kebutuhan setiap event.",
    icon: Scale,
  },
  {
    title: "Manajemen Peserta",
    description: "Tracking pendaftaran, verifikasi, dan status peserta secara real-time.",
    icon: Users,
  },
  {
    title: "Sistem Pembayaran",
    description: "Verifikasi pembayaran yang terintegrasi dan mudah dikelola admin.",
    icon: CreditCard,
  },
  {
    title: "Rekapitulasi Nilai",
    description: "Laporan penilaian lengkap dengan peringkat dan statistik yang jelas.",
    icon: BarChart3,
  },
  {
    title: "Leaderboard",
    description: "Peringkat peserta yang diperbarui berdasarkan penilaian juri.",
    icon: Trophy,
  },
];

const MISSIONS = [
  "Menyajikan hasil penilaian dan umpan balik juri secara jelas kepada peserta",
  "Membantu peserta memahami kekuatan dan area yang perlu dikembangkan",
  "Menjaga transparansi proses penilaian bagi penyelenggara, juri, dan peserta",
  "Memudahkan juri memberikan penilaian yang terstruktur dan umpan balik yang membangun",
  "Mendukung penyelenggara menyampaikan hasil kompetisi secara konsisten dan bertanggung jawab",
];

const CONTACTS = [
  { label: "Email", value: "info@jurionline.com", href: "mailto:info@jurionline.com", icon: Mail },
  { label: "Website", value: "www.jurionline.com", href: null, icon: Globe },
  { label: "Support", value: "support@jurionline.com", href: "mailto:support@jurionline.com", icon: LifeBuoy },
];

function useActiveSection() {
  const [active, setActive] = useState(SECTIONS[0].id);

  useEffect(() => {
    const nodes = SECTIONS.map((section) => document.getElementById(section.id)).filter(
      (node): node is HTMLElement => node !== null,
    );

    if (nodes.length === 0 || typeof IntersectionObserver === "undefined") {
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        const visible = entries
          .filter((entry) => entry.isIntersecting)
          .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

        if (visible) {
          setActive(visible.target.id);
        }
      },
      { rootMargin: "-45% 0px -45% 0px", threshold: [0, 0.25, 0.5, 1] },
    );

    nodes.forEach((node) => observer.observe(node));

    return () => observer.disconnect();
  }, []);

  return active;
}

export function CompanyProfileContent() {
  const reduced = useReducedMotion();
  const active = useActiveSection();

  return (
    <div className="bg-white">
      <section className="relative overflow-hidden bg-[var(--brand-navy)] text-white">
        <div
          aria-hidden="true"
          className="jurion-dotgrid pointer-events-none absolute inset-0 opacity-50 [mask-image:linear-gradient(to_bottom,black,transparent_90%)]"
        />
        <div
          aria-hidden="true"
          className="jurion-aurora pointer-events-none absolute -right-40 -top-56 h-[42rem] w-[42rem] rounded-full bg-[var(--brand-cobalt)]/25 blur-[120px]"
        />

        <div className="relative mx-auto max-w-7xl px-6 pb-20 pt-16 sm:pt-20 lg:px-8 lg:pb-24 lg:pt-24">
          <div className="grid gap-14 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
            <div>
              <motion.p
                initial={reduced ? false : { opacity: 0, y: 14 }}
                animate={{ opacity: 1, y: 0 }}
                transition={reduced ? { duration: 0 } : { duration: 0.55, delay: 0.04, ease: [0.22, 1, 0.36, 1] }}
                className="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/6 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-200 backdrop-blur"
              >
                <span aria-hidden="true" className="jurion-pulse h-1.5 w-1.5 rounded-full bg-[var(--brand-cobalt)]" />
                Company profile
              </motion.p>

              <motion.h1
                initial={reduced ? false : { opacity: 0, y: 18 }}
                animate={{ opacity: 1, y: 0 }}
                transition={reduced ? { duration: 0 } : { duration: 0.6, delay: 0.08, ease: [0.22, 1, 0.36, 1] }}
                className="mt-6 text-balance text-4xl font-semibold leading-[1.06] tracking-tight sm:text-5xl lg:text-6xl"
              >
                Company Profile
              </motion.h1>

              <motion.p
                initial={reduced ? false : { opacity: 0, y: 18 }}
                animate={{ opacity: 1, y: 0 }}
                transition={reduced ? { duration: 0 } : { duration: 0.6, delay: 0.14, ease: [0.22, 1, 0.36, 1] }}
                className="mt-5 max-w-2xl text-base leading-7 text-slate-300 sm:text-lg"
              >
                Tentang JURI.ON — platform penilaian digital yang menghubungkan penyelenggara, juri, dan peserta
                melalui alur yang jelas serta hasil penilaian yang dapat dipahami.
              </motion.p>

              <motion.div
                initial={reduced ? false : { opacity: 0, y: 18 }}
                animate={{ opacity: 1, y: 0 }}
                transition={reduced ? { duration: 0 } : { duration: 0.6, delay: 0.2, ease: [0.22, 1, 0.36, 1] }}
                className="mt-8 flex flex-col gap-3 sm:flex-row"
              >
                <CtaLink href="/register" variant="secondary" size="lg" className="cta-sheen">
                  Mulai sekarang
                  <ArrowRight className="h-4 w-4" aria-hidden="true" />
                </CtaLink>
                <a
                  href="#kontak"
                  className="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/5 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                >
                  Hubungi kami
                </a>
              </motion.div>
            </div>

            <Reveal delay={0.18} y={28}>
              <Parallax distance={24}>
                <div className="relative overflow-hidden rounded-3xl border border-white/10 bg-white/6 p-7 backdrop-blur-xl sm:p-8">
                  <div
                    aria-hidden="true"
                    className="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-[var(--brand-cobalt)]/25 blur-3xl"
                  />
                  <div className="relative">
                    <BrandLockup href="/company-profile" variant="dark" tile descriptor />
                    <p className="mt-7 text-sm leading-6 text-slate-300">
                      Satu alur kerja untuk pendaftaran peserta, penilaian juri, verifikasi pembayaran, dan
                      rekapitulasi hasil.
                    </p>

                    <div className="mt-7 flex flex-wrap gap-2">
                      {["Pendaftaran", "Penilaian", "Rekap"].map((item) => (
                        <span
                          key={item}
                          className="rounded-full border border-white/12 bg-white/6 px-3 py-1 text-[11px] font-medium text-slate-200"
                        >
                          {item}
                        </span>
                      ))}
                    </div>

                    <dl className="mt-7 grid grid-cols-2 gap-4 border-t border-white/10 pt-6 text-xs">
                      <div>
                        <dt className="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">Fokus</dt>
                        <dd className="mt-1.5 font-medium text-white">Penilaian digital</dd>
                      </div>
                      <div>
                        <dt className="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">Peran</dt>
                        <dd className="mt-1.5 font-medium text-white">Admin, juri, peserta</dd>
                      </div>
                    </dl>
                  </div>
                </div>
              </Parallax>
            </Reveal>
          </div>
        </div>
      </section>

      <nav
        aria-label="Bagian company profile"
        className="sticky top-20 z-40 border-b border-slate-200 bg-white/90 backdrop-blur"
      >
        <div className="mx-auto flex max-w-7xl gap-1 overflow-x-auto px-6 py-2.5 lg:px-8">
          {SECTIONS.map((section) => (
            <a
              key={section.id}
              href={`#${section.id}`}
              aria-current={active === section.id ? "true" : undefined}
              className={`relative shrink-0 rounded-full px-4 py-2 text-sm font-medium transition ${
                active === section.id ? "text-white" : "text-slate-500 hover:text-[var(--brand-navy)]"
              }`}
            >
              {active === section.id ? (
                <motion.span
                  layoutId="company-profile-tab"
                  className="absolute inset-0 rounded-full bg-[var(--brand-navy)]"
                  transition={reduced ? { duration: 0 } : { type: "spring", stiffness: 380, damping: 32 }}
                />
              ) : null}
              <span className="relative">{section.label}</span>
            </a>
          ))}
        </div>
      </nav>

      <div className="mx-auto max-w-7xl px-6 lg:px-8">
        <section id="tentang" className="scroll-mt-40 border-b border-slate-100 py-16 sm:py-20">
          <div className="grid gap-12 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:gap-16">
            <Reveal>
              <SectionHeading
                eyebrow="Tentang"
                title="Tentang JURI.ON"
                description="Platform digital yang dirancang untuk memudahkan proses penilaian dan manajemen event kompetisi, lomba, dan acara penjurian."
              />
              <p className="mt-5 max-w-2xl text-base leading-8 text-slate-600">
                Kami menyediakan solusi terintegrasi untuk mengelola seluruh siklus event, mulai dari pendaftaran
                peserta hingga penilaian oleh juri profesional. Tim penyelenggara, juri, dan peserta melihat informasi
                yang relevan sesuai perannya, tanpa berpindah-pindah aplikasi.
              </p>
              <p className="mt-4 max-w-2xl text-base leading-8 text-slate-600">
                Peserta dapat melihat nilai dan umpan balik juri melalui halaman pendaftarannya, sehingga hasil
                kompetisi bukan hanya menjadi peringkat, tetapi juga bahan evaluasi untuk berkembang.
              </p>

              <div className="mt-8 grid gap-3 sm:grid-cols-3">
                {[
                  { title: "Satu alur", detail: "Pendaftaran sampai hasil" },
                  { title: "Berbasis peran", detail: "Akses sesuai kebutuhan" },
                  { title: "Real-time", detail: "Status dan nilai terbaru" },
                ].map((item) => (
                  <div key={item.title} className="rounded-xl border border-slate-200 bg-[var(--brand-surface)] px-4 py-3">
                    <p className="text-xs font-semibold text-[var(--brand-navy)]">{item.title}</p>
                    <p className="mt-1 text-[11px] leading-5 text-slate-500">{item.detail}</p>
                  </div>
                ))}
              </div>
            </Reveal>

            <Reveal delay={0.08} y={26}>
              <div className="relative overflow-hidden rounded-3xl bg-[var(--brand-navy)] p-8 text-white">
                <div aria-hidden="true" className="jurion-dotgrid absolute inset-0 opacity-30" />
                <div
                  aria-hidden="true"
                  className="jurion-aurora absolute -right-16 -top-16 h-48 w-48 rounded-full bg-[var(--brand-cobalt)]/30 blur-3xl"
                />
                <div className="relative">
                  <BrandLockup href="/company-profile" variant="dark" tile descriptor />
                  <p className="mt-8 text-balance text-xl font-medium leading-8 text-white/90">
                    Platform Penilaian Digital Terpercaya.
                  </p>
                  <div className="mt-8 flex flex-wrap gap-2">
                    {["Profesional", "Transparan", "Inovatif", "Terpercaya"].map((value) => (
                      <span
                        key={value}
                        className="rounded-full border border-white/12 bg-white/6 px-3 py-1 text-[11px] font-medium text-slate-200"
                      >
                        {value}
                      </span>
                    ))}
                  </div>
                </div>
              </div>
            </Reveal>
          </div>
        </section>

        <section id="visi-misi" className="scroll-mt-40 border-b border-slate-100 py-16 sm:py-20">
          <Reveal>
            <SectionHeading
              eyebrow="Visi & Misi"
              title="Membantu peserta memahami evaluasi juri agar dapat berkembang."
              description="Visi ini menjadi arah JURI.ON: penilaian yang transparan, hasil yang jelas, dan umpan balik yang bisa ditindaklanjuti."
            />
          </Reveal>

          <div className="mt-10 grid gap-5 lg:grid-cols-2">
            <Reveal>
              <div className="relative h-full overflow-hidden rounded-3xl bg-[var(--brand-navy)] p-8 text-white">
                <div aria-hidden="true" className="jurion-dotgrid absolute inset-0 opacity-25" />
                <span
                  aria-hidden="true"
                  className="pointer-events-none absolute right-6 top-2 select-none text-[8rem] font-semibold leading-none text-white/10"
                >
                  &ldquo;
                </span>
                <div className="relative">
                  <p className="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/6 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-200">
                    <Eye className="h-3.5 w-3.5" aria-hidden="true" />
                    Visi
                  </p>
                  <p className="mt-5 text-lg leading-8 text-white/90">
                    Setiap peserta berhak mengetahui penilaian jurinya dan memahami capaiannya. Hasil kompetisi bukan
                    hanya angka akhir, tetapi juga dasar untuk berkembang.
                  </p>
                </div>
              </div>
            </Reveal>

            <Reveal delay={0.08}>
              <div className="h-full rounded-3xl border border-slate-200 bg-[var(--brand-surface)] p-8">
                <p className="inline-flex items-center gap-2 rounded-full border border-[var(--brand-border)] bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--brand-cobalt-strong)]">
                  <Sparkles className="h-3.5 w-3.5" aria-hidden="true" />
                  Misi
                </p>
                <ul className="mt-5 space-y-3">
                  {MISSIONS.map((mission, index) => (
                    <motion.li
                      key={mission}
                      initial={reduced ? { opacity: 0 } : { opacity: 0, x: -10 }}
                      whileInView={reduced ? { opacity: 1 } : { opacity: 1, x: 0 }}
                      viewport={{ once: true, margin: "-40px 0px -24px 0px" }}
                      transition={
                        reduced
                          ? { duration: 0.3, delay: index * 0.04 }
                          : { duration: 0.45, delay: index * 0.05, ease: [0.22, 1, 0.36, 1] }
                      }
                      className="flex items-start gap-3 text-sm leading-6 text-slate-600"
                    >
                      <span className="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-[var(--brand-light-blue)] text-[10px] font-semibold text-[var(--brand-cobalt-deep)] ring-1 ring-inset ring-[var(--brand-cobalt)]/15">
                        {index + 1}
                      </span>
                      {mission}
                    </motion.li>
                  ))}
                </ul>
              </div>
            </Reveal>
          </div>
        </section>

        <section id="nilai" className="scroll-mt-40 border-b border-slate-100 py-16 sm:py-20">
          <Reveal>
            <SectionHeading eyebrow="Nilai" title="Nilai-Nilai Kami" />
          </Reveal>

          <div className="mt-10 grid gap-5 sm:grid-cols-2">
            {VALUES.map((value, index) => {
              const Icon = value.icon;

              return (
                <Reveal key={value.title} delay={index * 0.06} className="h-full">
                  <TiltCard className="h-full rounded-2xl border border-slate-200 bg-white p-6 hover:border-[var(--brand-cobalt)]/25 hover:shadow-[0_24px_60px_-45px_rgba(11,31,70,0.45)]">
                    <div className="flex items-center gap-3">
                      <span className="grid h-10 w-10 place-items-center rounded-xl bg-[var(--brand-light-blue)] text-[var(--brand-cobalt-strong)] ring-1 ring-inset ring-[var(--brand-cobalt)]/10">
                        <Icon className="h-5 w-5" aria-hidden="true" />
                      </span>
                      <h3 className="text-base font-semibold text-[var(--brand-navy)]">{value.title}</h3>
                      <span className="ml-auto text-[10px] font-semibold tabular-nums text-slate-300">
                        {String(index + 1).padStart(2, "0")}
                      </span>
                    </div>
                    <p className="mt-4 text-sm leading-6 text-slate-600">{value.description}</p>
                  </TiltCard>
                </Reveal>
              );
            })}
          </div>
        </section>

        <section id="layanan" className="scroll-mt-40 border-b border-slate-100 py-16 sm:py-20">
          <Reveal>
            <SectionHeading
              eyebrow="Layanan"
              title="Layanan Kami"
              description="Modul yang menyusun alur kerja JURI.ON dari pendaftaran sampai hasil."
            />
          </Reveal>

          <div className="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            {SERVICES.map((service, index) => {
              const Icon = service.icon;

              return (
                <Reveal key={service.title} delay={(index % 3) * 0.06} className="h-full">
                  <TiltCard className="h-full rounded-2xl border border-slate-200 bg-[var(--brand-surface)] p-6 hover:border-[var(--brand-cobalt)]/25 hover:bg-white hover:shadow-[0_24px_60px_-45px_rgba(11,31,70,0.45)]">
                    <div className="flex items-start justify-between gap-3">
                      <span className="grid h-10 w-10 place-items-center rounded-xl bg-white text-[var(--brand-cobalt-strong)] shadow-sm ring-1 ring-inset ring-slate-200">
                        <Icon className="h-5 w-5" aria-hidden="true" />
                      </span>
                      <span className="text-[10px] font-semibold tabular-nums text-slate-300">
                        {String(index + 1).padStart(2, "0")}
                      </span>
                    </div>
                    <h3 className="mt-4 text-sm font-semibold text-[var(--brand-navy)]">{service.title}</h3>
                    <p className="mt-2 text-sm leading-6 text-slate-600">{service.description}</p>
                  </TiltCard>
                </Reveal>
              );
            })}
          </div>
        </section>

        <section id="kontak" className="scroll-mt-40 py-16 sm:py-20">
          <Reveal>
            <SectionHeading
              eyebrow="Kontak"
              title="Kontak Kami"
              description="Ada pertanyaan tentang event, penilaian, atau kerja sama? Hubungi tim JURI.ON."
            />
          </Reveal>

          <div className="mt-10 grid gap-5 sm:grid-cols-3">
            {CONTACTS.map((contact, index) => {
              const Icon = contact.icon;

              return (
                <Reveal key={contact.label} delay={index * 0.06}>
                  <div className="h-full rounded-2xl border border-slate-200 bg-white p-6">
                    <span className="grid h-10 w-10 place-items-center rounded-xl bg-[var(--brand-light-blue)] text-[var(--brand-cobalt-strong)]">
                      <Icon className="h-5 w-5" aria-hidden="true" />
                    </span>
                    <p className="mt-4 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                      {contact.label}
                    </p>
                    {contact.href ? (
                      <a
                        href={contact.href}
                        className="mt-1.5 block text-sm font-medium text-slate-700 transition hover:text-[var(--brand-cobalt-strong)]"
                      >
                        {contact.value}
                      </a>
                    ) : (
                      <p className="mt-1.5 text-sm font-medium text-slate-700">{contact.value}</p>
                    )}
                  </div>
                </Reveal>
              );
            })}
          </div>

          <Reveal delay={0.1}>
            <div className="relative mt-10 overflow-hidden rounded-3xl border border-[var(--brand-border)] bg-[var(--brand-surface)] p-8 sm:p-10">
              <div
                aria-hidden="true"
                className="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-[var(--brand-cobalt)]/10 blur-3xl"
              />
              <div className="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div className="max-w-2xl">
                  <h2 className="text-xl font-semibold tracking-tight text-[var(--brand-navy)] sm:text-2xl">
                    Siap menggelar kompetisi yang lebih rapi?
                  </h2>
                  <p className="mt-2 text-sm leading-6 text-slate-600">
                    Bergabung bersama penyelenggara, juri, dan peserta yang menggunakan JURI.ON untuk pengalaman
                    kompetisi yang adil dan transparan.
                  </p>
                </div>
                <div className="flex flex-col gap-3 sm:flex-row">
                  <CtaLink href="/register" size="md" className="cta-sheen">
                    Daftar sekarang
                    <ArrowRight className="h-4 w-4" aria-hidden="true" />
                  </CtaLink>
                  <CtaLink href="/login" variant="secondary" size="md">
                    Masuk
                  </CtaLink>
                </div>
              </div>
            </div>
          </Reveal>
        </section>
      </div>
    </div>
  );
}
