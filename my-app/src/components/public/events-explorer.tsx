"use client";

import { useMemo, useState } from "react";
import { AnimatePresence, motion, useReducedMotion } from "motion/react";
import { Search, SlidersHorizontal, X } from "lucide-react";
import type { PublicEvent } from "./api";
import { EventCard } from "./event-card";
import { Reveal } from "./reveal";

type FilterId = "all" | "open" | "soon" | "done";

const FILTERS: { id: FilterId; label: string }[] = [
  { id: "all", label: "Semua" },
  { id: "open", label: "Dibuka" },
  { id: "soon", label: "Segera" },
  { id: "done", label: "Selesai" },
];

function matchesFilter(event: PublicEvent, filter: FilterId): boolean {
  if (filter === "all") return true;
  if (filter === "done") return ["done", "full", "closed"].includes(event.status_tone);
  return event.status_tone === filter;
}

/** Pencarian dan filter ringan di sisi klien; data event tetap dari server. */
export function EventsExplorer({ events }: { events: PublicEvent[] }) {
  const reduced = useReducedMotion();
  const [query, setQuery] = useState("");
  const [filter, setFilter] = useState<FilterId>("all");

  const counts = useMemo(
    () =>
      FILTERS.reduce<Record<FilterId, number>>(
        (acc, item) => {
          acc[item.id] = events.filter((event) => matchesFilter(event, item.id)).length;
          return acc;
        },
        { all: 0, open: 0, soon: 0, done: 0 },
      ),
    [events],
  );

  const filtered = useMemo(() => {
    const needle = query.trim().toLowerCase();

    return events.filter((event) => {
      if (!matchesFilter(event, filter)) return false;
      if (!needle) return true;

      return [event.name, event.organizer, event.category, event.location]
        .filter(Boolean)
        .some((value) => String(value).toLowerCase().includes(needle));
    });
  }, [events, filter, query]);

  return (
    <div>
      <Reveal>
      <div className="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-3 shadow-[0_1px_2px_rgba(11,31,70,0.04)] sm:flex-row sm:items-center sm:justify-between">
        <div className="flex flex-wrap items-center gap-1.5" role="group" aria-label="Filter status event">
          <span className="mr-1 hidden items-center gap-1.5 px-2 text-xs font-medium text-slate-400 sm:flex">
            <SlidersHorizontal className="h-3.5 w-3.5" aria-hidden="true" />
            Filter
          </span>
          {FILTERS.map((item) => {
            const active = filter === item.id;

            return (
              <button
                key={item.id}
                type="button"
                onClick={() => setFilter(item.id)}
                aria-pressed={active}
                className={`relative rounded-full px-3 py-1.5 text-[11px] font-semibold transition-colors ${
                  active ? "text-white" : "text-slate-600 hover:text-[var(--brand-navy)]"
                }`}
              >
                {active ? (
                  <motion.span
                    aria-hidden="true"
                    layoutId="event-filter-pill"
                    className="absolute inset-0 rounded-full bg-[var(--brand-navy)]"
                    transition={reduced ? { duration: 0 } : { type: "spring", stiffness: 380, damping: 34 }}
                  />
                ) : null}
                <span className="relative">
                  {item.label}
                  <span className={`ml-1 text-[9px] ${active ? "text-white/60" : "text-slate-400"}`}>
                    {counts[item.id]}
                  </span>
                </span>
              </button>
            );
          })}
        </div>

        <label className="relative block w-full sm:w-72">
          <span className="sr-only">Cari event</span>
          <Search className="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" aria-hidden="true" />
          <input
            value={query}
            onChange={(event) => setQuery(event.target.value)}
            type="search"
            placeholder="Cari event atau penyelenggara"
            className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 pl-10 pr-9 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-[var(--brand-cobalt)] focus:bg-white focus:ring-4 focus:ring-[var(--brand-cobalt)]/10"
          />
          {query ? (
            <button
              type="button"
              onClick={() => setQuery("")}
              aria-label="Hapus pencarian"
              className="absolute right-2.5 top-1/2 grid h-6 w-6 -translate-y-1/2 place-items-center rounded-full text-slate-400 transition hover:bg-slate-200 hover:text-slate-700"
            >
              <X className="h-3.5 w-3.5" aria-hidden="true" />
            </button>
          ) : null}
        </label>
      </div>
      </Reveal>

      <Reveal delay={0.04}>
      <div className="mt-6 flex items-center justify-between gap-4">
        <p className="text-sm text-slate-500" aria-live="polite">
          Menampilkan <span className="font-semibold text-[var(--brand-navy)]">{filtered.length}</span> dari{" "}
          {events.length} event
        </p>
        {filter !== "all" || query ? (
          <button
            type="button"
            onClick={() => {
              setFilter("all");
              setQuery("");
            }}
            className="text-xs font-semibold text-[var(--brand-cobalt-strong)] transition hover:text-[var(--brand-cobalt-deep)]"
          >
            Reset filter
          </button>
        ) : null}
      </div>
      </Reveal>

      {filtered.length === 0 ? (
        <motion.div
          initial={reduced ? { opacity: 0 } : { opacity: 0, y: 16 }}
          whileInView={reduced ? { opacity: 1 } : { opacity: 1, y: 0 }}
          viewport={{ once: true, margin: "-60px 0px -24px 0px" }}
          className="mt-6 rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center"
        >
          <span className="mx-auto grid h-12 w-12 place-items-center rounded-full bg-[var(--brand-light-blue)] text-[var(--brand-cobalt-strong)]">
            <Search className="h-5 w-5" aria-hidden="true" />
          </span>
          <h2 className="mt-4 text-base font-semibold text-[var(--brand-navy)]">Tidak ada event yang cocok</h2>
          <p className="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-600">
            Coba kata kunci lain atau kembalikan filter ke semua event.
          </p>
          <button
            type="button"
            onClick={() => {
              setFilter("all");
              setQuery("");
            }}
            className="mt-5 rounded-xl bg-[var(--brand-navy)] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[var(--brand-cobalt-deep)]"
          >
            Tampilkan semua event
          </button>
        </motion.div>
      ) : (
        <motion.div
          layout
          className={`mt-6 grid gap-6 ${
            filtered.length === 1
              ? "max-w-2xl"
              : filtered.length === 2
                ? "sm:grid-cols-2"
                : "sm:grid-cols-2 xl:grid-cols-3"
          }`}
        >
          <AnimatePresence mode={reduced ? "sync" : "popLayout"}>
            {filtered.map((event, index) => (
              <motion.div
                layout
                key={event.id}
                initial={reduced ? { opacity: 0 } : { opacity: 0, y: 18, scale: 0.99 }}
                whileInView={reduced ? { opacity: 1 } : { opacity: 1, y: 0, scale: 1 }}
                viewport={{ once: true, margin: "-60px 0px -24px 0px" }}
                exit={reduced ? { opacity: 0 } : { opacity: 0, y: -8, scale: 0.99 }}
                transition={
                  reduced
                    ? { duration: 0.3, delay: Math.min(index, 5) * 0.03 }
                    : { duration: 0.4, delay: Math.min(index, 5) * 0.04, ease: [0.22, 1, 0.36, 1] }
                }
                className="h-full"
              >
                <EventCard event={event} ctaLabel="Lihat detail" limitDescription={110} />
              </motion.div>
            ))}
          </AnimatePresence>
        </motion.div>
      )}
    </div>
  );
}
