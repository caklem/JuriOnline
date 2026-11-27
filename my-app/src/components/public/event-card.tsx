import Link from "next/link";
import { ArrowRight, CalendarDays, MapPin } from "lucide-react";
import type { PublicEvent } from "./api";
import { CtaLink } from "./cta-link";
import { formatDate, formatPrice, limit } from "./format";

const TONES: Record<PublicEvent["status_tone"], string> = {
  open: "bg-emerald-50/95 text-emerald-800 ring-emerald-600/20",
  soon: "bg-[var(--brand-light-blue)]/95 text-[var(--brand-cobalt-deep)] ring-[var(--brand-cobalt)]/25",
  done: "bg-slate-100/95 text-slate-600 ring-slate-500/15",
  full: "bg-amber-50/95 text-amber-800 ring-amber-600/20",
  closed: "bg-slate-100/95 text-slate-600 ring-slate-500/15",
};

function Banner({ event }: { event: PublicEvent }) {
  if (event.banner_url) {
    return (
      <div className="relative h-full w-full overflow-hidden bg-[var(--brand-navy)]">
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img
          src={event.banner_url}
          alt=""
          aria-hidden="true"
          loading="lazy"
          decoding="async"
          className="absolute inset-0 h-full w-full scale-110 object-cover opacity-40 blur-2xl transition duration-700 group-hover:scale-125"
        />
        <div aria-hidden="true" className="absolute inset-0 bg-[var(--brand-navy)]/20" />
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img
          src={event.banner_url}
          alt=""
          loading="lazy"
          decoding="async"
          className="relative z-[1] h-full w-full object-contain p-3"
        />
      </div>
    );
  }

  return (
    <div className="relative h-full w-full overflow-hidden bg-gradient-to-br from-[var(--brand-navy)] via-[#102a5c] to-[var(--brand-cobalt-deep)]">
      <div aria-hidden="true" className="jurion-dotgrid absolute inset-0 opacity-40" />
      <div
        aria-hidden="true"
        className="absolute -right-10 -top-14 h-48 w-48 rounded-full bg-[var(--brand-cobalt)]/30 blur-3xl"
      />
      <div className="relative grid h-full place-items-center">
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img
          src="/brand/jurion-mark-white.png"
          alt=""
          className="h-16 w-16 opacity-90 transition duration-700 group-hover:scale-110"
        />
      </div>
    </div>
  );
}

export function EventCard({
  event,
  ctaLabel = "Lihat Detail Event",
  limitDescription = 120,
}: {
  event: PublicEvent;
  ctaLabel?: string;
  limitDescription?: number;
}) {
  const percent =
    event.quota > 0 ? Math.min(100, Math.round((event.registered_count / event.quota) * 100)) : 0;
  const date = event.event_start ?? event.event_date;
  const href = `/events/${event.slug ?? event.id}`;

  return (
    <article className="group relative flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(11,31,70,0.04)] transition duration-300 hover:-translate-y-1 hover:border-[var(--brand-cobalt)]/30 hover:shadow-[0_30px_60px_-35px_rgba(11,31,70,0.45)]">
      <Link
        href={href}
        className="relative block aspect-[4/3] overflow-hidden bg-[var(--brand-navy)]"
        tabIndex={-1}
        aria-hidden="true"
      >
        <Banner event={event} />
      </Link>

      <div className="flex flex-1 flex-col p-6">
        <div className="flex flex-wrap items-center gap-2">
          {event.category ? (
            <span className="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700 ring-1 ring-inset ring-slate-200">
              {event.category}
            </span>
          ) : null}
          <span
            className={`rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset ${
              TONES[event.status_tone] ?? TONES.done
            }`}
          >
            {event.status_label}
          </span>
          {event.format ? (
            <span className="rounded-full bg-[var(--brand-surface)] px-2.5 py-1 text-[11px] font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">
              {event.format === "online" ? "Online" : "Offline"}
            </span>
          ) : null}
        </div>

        <p className="mt-4 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">
          {event.organizer ?? "Penyelenggara"}
        </p>
        <h3 className="mt-2 text-lg font-semibold leading-snug tracking-tight text-[var(--brand-navy)] transition-colors group-hover:text-[var(--brand-cobalt-strong)]">
          <Link href={href}>{event.name}</Link>
        </h3>
        <p className="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">
          {limit(event.description, limitDescription)}
        </p>

        <div className="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
          {event.location ? (
            <span className="flex items-center gap-1.5">
              <MapPin className="h-3.5 w-3.5" aria-hidden="true" />
              {event.location}
            </span>
          ) : null}
          <span className="flex items-center gap-1.5">
            <CalendarDays className="h-3.5 w-3.5" aria-hidden="true" />
            {formatDate(date)}
          </span>
        </div>

        <div className="mt-5">
          <div className="flex items-center justify-between text-[11px] font-medium">
            <span className="text-slate-500">
              Kuota{" "}
              <span className="font-semibold text-slate-700">
                {event.registered_count}/{event.quota}
              </span>
            </span>
            <span className={event.available_slots > 0 ? "text-emerald-700" : "text-rose-600"}>
              {event.available_slots > 0 ? `${event.available_slots} tersisa` : "Penuh"}
            </span>
          </div>
          <div
            className="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100"
            role="progressbar"
            aria-valuenow={percent}
            aria-valuemin={0}
            aria-valuemax={100}
            aria-label={`Kuota terisi ${percent}%`}
          >
            <div
              className={`jurion-bar h-full rounded-full ${
                percent >= 100
                  ? "bg-rose-500"
                  : "bg-gradient-to-r from-[var(--brand-cobalt-strong)] to-[var(--brand-cobalt)]"
              }`}
              style={{ width: `${percent}%` }}
            />
          </div>
        </div>

        <div className="mt-5 flex items-end justify-between gap-4 border-t border-slate-100 pt-5">
          <div>
            <p className="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Biaya</p>
            <p className="mt-1 text-base font-semibold text-[var(--brand-navy)]">{formatPrice(event.price)}</p>
          </div>
          <CtaLink href={href} size="sm" className="cta-sheen">
            {ctaLabel}
            <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
          </CtaLink>
        </div>
      </div>
    </article>
  );
}
