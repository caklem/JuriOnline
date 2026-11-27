import Link from "next/link";
import { notFound } from "next/navigation";
import type { Metadata } from "next";
import type { ReactNode } from "react";
import { ArrowLeft, Check, FileText, Mail, ShieldAlert } from "lucide-react";
import { Badge, Card, CardHeader } from "@/components/kit/ui";
import { fetchPublicEvent, fetchPublicEvents, type PublicEvent } from "@/components/public/api";
import { BannerShowcase } from "@/components/public/banner-showcase";
import { CtaLink } from "@/components/public/cta-link";
import { formatDate, formatPrice, limit } from "@/components/public/format";
import { PublicShell } from "@/components/public/public-shell";
import { Reveal } from "@/components/public/reveal";

// Data event diambil dari API saat request; jangan diprerender saat build.
export const instant = false;

type PageProps = { params: Promise<{ id: string }> };

/** Menerima ID numerik maupun slug; fallback ke daftar publik selama route slug belum aktif. */
async function resolveEvent(idOrSlug: string): Promise<PublicEvent | null> {
  const direct = await fetchPublicEvent(idOrSlug);

  if (direct) {
    return direct;
  }

  const events = await fetchPublicEvents();

  return events.find((event) => event.slug === idOrSlug) ?? null;
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { id } = await params;
  const event = await resolveEvent(id);

  if (!event) {
    return { title: "Event tidak ditemukan — JURI.ON" };
  }

  return {
    title: `${event.name} — JURI.ON`,
    description: limit(event.description, 160),
  };
}

function Banner({ event }: { event: PublicEvent }) {
  if (event.banner_url) {
    return <BannerShowcase src={event.banner_url} alt={`Banner ${event.name}`} />;
  }

  return (
    <div className="relative grid h-56 w-full place-items-center bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 text-white/70 sm:h-72 lg:h-80">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.2" className="h-16 w-16" aria-hidden="true">
        <path strokeLinecap="round" strokeLinejoin="round" d="M12 3v18M8 21h8M5 7h14M5 7l-3 6h6L5 7Zm14 0-3 6h6l-3-6Z" />
        <path strokeLinecap="round" strokeLinejoin="round" d="M2 13a3 3 0 0 0 6 0M16 13a3 3 0 0 0 6 0" />
      </svg>
    </div>
  );
}

function MetaRow({ label, children }: { label: string; children: ReactNode }) {
  return (
    <div className="py-3">
      <dt className="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{label}</dt>
      <dd className="mt-0.5 text-sm font-medium text-slate-800">{children}</dd>
    </div>
  );
}

function domains(event: PublicEvent): string[] {
  return (event.allowed_email_domains ?? "")
    .split(",")
    .map((domain) => domain.trim())
    .filter(Boolean);
}

export default async function EventDetailPage({ params }: PageProps) {
  const { id } = await params;
  const event = await resolveEvent(id);

  if (!event) {
    notFound();
  }

  const filledPercent =
    event.quota > 0
      ? Math.round(Math.min(100, Math.max(0, ((event.quota - event.available_slots) / event.quota) * 100)))
      : 100;
  const allowedDomains = domains(event);

  return (
    <PublicShell>
      <div className="mx-auto max-w-7xl px-6 py-8 lg:px-8 lg:py-12">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <nav className="flex min-w-0 items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
            <Link href="/" className="transition hover:text-slate-900">Beranda</Link>
            <span aria-hidden="true" className="text-slate-300">/</span>
            <Link href="/events" className="transition hover:text-slate-900">Semua Event</Link>
            <span aria-hidden="true" className="text-slate-300">/</span>
            <span className="truncate font-medium text-slate-900">{limit(event.name, 48)}</span>
          </nav>

          <Link href="/events" className="inline-flex items-center gap-1.5 text-sm font-medium text-slate-600 transition hover:text-slate-950">
            <ArrowLeft className="h-4 w-4" aria-hidden="true" />
            Kembali ke daftar event
          </Link>
        </div>

        <Reveal className="mt-6">
          <div className="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-900">
            <Banner event={event} />
            <div className="absolute inset-x-5 top-5 flex flex-wrap items-center gap-2">
              {event.category ? (
                <span className="rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm backdrop-blur">
                  {event.category}
                </span>
              ) : null}
              <span className="rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm backdrop-blur">
                {event.status_label}
              </span>
            </div>
          </div>
        </Reveal>

        <div className="mt-6 grid gap-6 lg:grid-cols-3 lg:items-start">
          <div className="space-y-6 lg:col-span-2">
            <Reveal>
              <Card className="p-6 sm:p-7">
                <h1 className="text-2xl font-semibold tracking-tight text-[var(--foreground)] sm:text-3xl">{event.name}</h1>
                <p className="mt-2 text-sm text-slate-500">
                  Diselenggarakan oleh <span className="font-semibold text-slate-700">{event.organizer ?? "-"}</span>
                </p>

                <div className="mt-6 border-t border-slate-100 pt-6">
                  <h2 className="text-base font-semibold text-slate-950">Deskripsi Event</h2>
                  <p className="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">
                    {event.description || "Belum ada deskripsi untuk event ini."}
                  </p>
                </div>
              </Card>
            </Reveal>

            {event.juknis_url ? (
              <Reveal>
                <Card className="p-6">
                  <div className="flex flex-wrap items-center justify-between gap-4">
                    <div className="flex min-w-0 items-start gap-3">
                      <span className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-100">
                        <FileText className="h-5 w-5" aria-hidden="true" />
                      </span>
                      <div className="min-w-0">
                        <h2 className="text-sm font-semibold text-[var(--foreground)]">Juknis Event</h2>
                        <p className="mt-1 break-all text-sm text-slate-600">
                          {event.juknis_name ?? "Petunjuk teknis event"}
                        </p>
                        <p className="mt-1 text-xs text-slate-500">
                          Baca juknis terlebih dahulu sebelum mendaftar.
                        </p>
                      </div>
                    </div>
                    <a
                      href={event.juknis_url}
                      target="_blank"
                      rel="noopener"
                      className="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                      Lihat Juknis
                      <span aria-hidden="true">→</span>
                    </a>
                  </div>
                </Card>
              </Reveal>
            ) : null}

            {event.require_official_email && allowedDomains.length > 0 ? (
              <Reveal>
                <Card className="p-6">
                  <div className="flex items-start gap-3 rounded-xl bg-indigo-50 p-4 ring-1 ring-inset ring-indigo-100">
                    <span className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-indigo-600 ring-1 ring-inset ring-indigo-100">
                      <Mail className="h-5 w-5" aria-hidden="true" />
                    </span>
                    <div>
                      <h2 className="text-sm font-semibold text-indigo-900">Persyaratan Email Resmi</h2>
                      <p className="mt-1 text-sm leading-6 text-indigo-800">
                        Pendaftaran event ini <strong>HANYA untuk email resmi</strong> dengan domain berikut:
                      </p>
                    </div>
                  </div>

                  <div className="mt-4 flex flex-wrap gap-2">
                    {allowedDomains.map((domain) => (
                      <span
                        key={domain}
                        className="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200"
                      >
                        <Check className="h-3.5 w-3.5 text-emerald-600" aria-hidden="true" />
                        {domain}
                      </span>
                    ))}
                  </div>
                </Card>
              </Reveal>
            ) : null}

            {!event.registration_open ? (
              <Reveal>
                <div className="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                  <p className="flex items-center gap-2 font-semibold">
                    <ShieldAlert className="h-4 w-4" aria-hidden="true" />
                    Pendaftaran Tidak Tersedia
                  </p>
                  <ul className="mt-2 list-disc space-y-1 pl-5">
                    {event.registration_state === "before" ? (
                      <li>
                        Pendaftaran belum dibuka. Dibuka pada: <strong>{formatDate(event.registration_start)}</strong>
                      </li>
                    ) : null}
                    {event.registration_state === "after" ? (
                      <li>
                        Pendaftaran sudah ditutup pada: <strong>{formatDate(event.registration_end)}</strong>
                      </li>
                    ) : null}
                    {event.registration_state === "full" ? (
                      <li>
                        Kuota sudah penuh ({event.registered_count}/{event.quota} peserta)
                      </li>
                    ) : null}
                    {event.registration_state === "inactive" ? (
                      <li>Event tidak aktif (Status: {event.status})</li>
                    ) : null}
                  </ul>
                </div>
              </Reveal>
            ) : null}

            <Reveal>
              <Card className="p-6">
                <div className="flex items-start gap-3">
                  <span className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-100">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" className="h-5 w-5" aria-hidden="true">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1" />
                      <circle cx="9.5" cy="7" r="3" />
                      <path strokeLinecap="round" strokeLinejoin="round" d="M20 19v-1a4 4 0 0 0-3-3.87" />
                    </svg>
                  </span>
                  <div>
                    <h2 className="text-base font-semibold text-slate-950">Masuk untuk mendaftar</h2>
                    <p className="mt-1 text-sm leading-6 text-slate-600">
                      Anda harus login untuk mendaftar event ini. Belum punya akun? Daftar terlebih dahulu, lalu kembali ke halaman ini.
                    </p>
                  </div>
                </div>

                <div className="mt-6 flex flex-wrap gap-3">
                  <CtaLink href="/login" size="md">Login</CtaLink>
                  <CtaLink href={`/events/${event.id}/register`} variant="primary" size="md">Daftar Sekarang</CtaLink>
                </div>
              </Card>
            </Reveal>
          </div>

          <aside className="space-y-6 lg:sticky lg:top-24">
            <Reveal>
              <Card>
                <CardHeader
                  title="Informasi Event"
                  action={
                    event.registration_open ? (
                      <Badge tone="success">
                        <span className="mr-1.5 inline-block h-1.5 w-1.5 rounded-full bg-emerald-500" />
                        Pendaftaran dibuka
                      </Badge>
                    ) : (
                      <Badge tone="neutral">
                        <span className="mr-1.5 inline-block h-1.5 w-1.5 rounded-full bg-slate-400" />
                        Pendaftaran ditutup
                      </Badge>
                    )
                  }
                />

                <dl className="divide-y divide-slate-100 px-5 pb-5">
                  <MetaRow label="Penyelenggara">{event.organizer ?? "-"}</MetaRow>
                  <MetaRow label="Lokasi">{event.location || "Online"}</MetaRow>
                  <MetaRow label="Harga">
                    <span className="font-semibold text-slate-900">{formatPrice(event.price)}</span>
                  </MetaRow>
                  <MetaRow label="Kuota">
                    {event.available_slots > 0 ? (
                      <span>{event.available_slots} dari {event.quota} tersisa</span>
                    ) : (
                      <span className="text-rose-600">Penuh ({event.registered_count}/{event.quota})</span>
                    )}
                    <span className="mt-2 block h-1.5 overflow-hidden rounded-full bg-slate-100">
                      <span
                        className={`block h-full rounded-full ${filledPercent >= 100 ? "bg-rose-500" : "bg-indigo-500"}`}
                        style={{ width: `${filledPercent}%` }}
                      />
                    </span>
                  </MetaRow>
                  <MetaRow label="Periode Pendaftaran">
                    {formatDate(event.registration_start)} – {formatDate(event.registration_end)}
                  </MetaRow>
                  <MetaRow label="Tanggal Hari H">
                    {event.event_date
                      ? formatDate(event.event_date)
                      : `${formatDate(event.event_start)} – ${formatDate(event.event_end)}`}
                  </MetaRow>
                </dl>
              </Card>
            </Reveal>
          </aside>
        </div>
      </div>
    </PublicShell>
  );
}
