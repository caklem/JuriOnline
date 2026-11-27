import Link from "next/link";
import { ArrowRight, CalendarDays, Sparkles } from "lucide-react";
import { fetchPublicEvents } from "@/components/public/api";
import { CtaLink } from "@/components/public/cta-link";
import { EventsExplorer } from "@/components/public/events-explorer";
import { PublicShell } from "@/components/public/public-shell";
import { Reveal } from "@/components/public/reveal";
import { PageHero } from "@/components/public/section";

export const metadata = {
  title: "Semua Event — JURI.ON",
  description: "Event kompetisi yang dipublikasikan penyelenggara di JURI.ON.",
};

// Data event diambil dari API saat request; jangan diprerender saat build.
export const instant = false;

export default async function EventsPage() {
  const events = await fetchPublicEvents();
  const openCount = events.filter((event) => event.registration_open).length;

  return (
    <PublicShell>
      <PageHero
        eyebrow="Katalog event"
        title="Semua Event"
        description="Jelajahi event yang dipublikasikan oleh penyelenggara. Buka detail untuk melihat jadwal, kuota, dan status pendaftaran."
      >
        <div className="flex flex-wrap items-center gap-3">
          <span className="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/6 px-3.5 py-2 text-xs font-medium text-slate-200 backdrop-blur">
            <span className="jurion-pulse h-1.5 w-1.5 rounded-full bg-[var(--brand-cobalt)]" />
            {events.length} event dipublikasikan
          </span>
          <span className="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/6 px-3.5 py-2 text-xs font-medium text-slate-200 backdrop-blur">
            <CalendarDays className="h-3.5 w-3.5 text-emerald-400" aria-hidden="true" />
            {openCount} pendaftaran dibuka
          </span>
          <CtaLink href="/register" variant="secondary" size="sm" className="cta-sheen">
            Ikut sebagai peserta
            <ArrowRight className="h-3.5 w-3.5" aria-hidden="true" />
          </CtaLink>
        </div>
      </PageHero>

      <section className="bg-white py-12 sm:py-16 lg:py-20">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <EventsExplorer events={events} />
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
                    Untuk penyelenggara
                  </p>
                  <h2 className="mt-3 text-2xl font-semibold tracking-tight text-[var(--brand-navy)] sm:text-3xl">
                    Punya event yang ingin dinilai dengan lebih rapi?
                  </h2>
                  <p className="mt-3 text-sm leading-6 text-slate-600">
                    Buat event, susun formulir, tugaskan juri, dan pantau hasil dari satu dasbor.
                  </p>
                </div>
                <div className="flex flex-col gap-3 sm:flex-row">
                  <CtaLink href="/register" size="md" className="cta-sheen">
                    Mulai sebagai penyelenggara
                    <ArrowRight className="h-4 w-4" aria-hidden="true" />
                  </CtaLink>
                  <Link
                    href="/about"
                    className="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-[var(--brand-cobalt)]/30 hover:text-[var(--brand-cobalt-strong)]"
                  >
                    Pelajari JURI.ON
                  </Link>
                </div>
              </div>
            </div>
          </Reveal>
        </div>
      </section>
    </PublicShell>
  );
}
