import type { ReactNode } from "react";
import { Reveal } from "./reveal";

/** Kepala bagian yang konsisten di seluruh halaman publik. */
export function SectionHeading({
  eyebrow,
  title,
  description,
  action,
  align = "left",
  tone = "light",
  className = "",
}: {
  eyebrow?: string;
  title: string;
  description?: string;
  action?: ReactNode;
  align?: "left" | "center";
  tone?: "light" | "dark";
  className?: string;
}) {
  const dark = tone === "dark";

  return (
    <div
      className={`flex flex-col gap-6 ${
        align === "center" ? "items-center text-center" : "sm:flex-row sm:items-end sm:justify-between"
      } ${className}`}
    >
      <div className={align === "center" ? "max-w-2xl" : "max-w-2xl"}>
        {eyebrow ? (
          <p
            className={`inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.18em] ${
              dark ? "text-[#93c3ff]" : "text-[var(--brand-cobalt-strong)]"
            }`}
          >
            <span
              aria-hidden="true"
              className={`h-px w-6 ${dark ? "bg-white/40" : "bg-[var(--brand-cobalt)]/50"}`}
            />
            {eyebrow}
          </p>
        ) : null}
        <h2
          className={`mt-3 text-balance text-3xl font-semibold leading-[1.12] tracking-tight sm:text-4xl ${
            dark ? "text-white" : "text-[var(--brand-navy)]"
          }`}
        >
          {title}
        </h2>
        {description ? (
          <p
            className={`mt-4 text-base leading-7 ${dark ? "text-slate-300" : "text-slate-600"} ${
              align === "center" ? "mx-auto" : ""
            }`}
          >
            {description}
          </p>
        ) : null}
      </div>
      {action ? <div className="shrink-0">{action}</div> : null}
    </div>
  );
}

/** Hero navy untuk halaman dalam (Event, Tentang, Company Profile). */
export function PageHero({
  eyebrow,
  title,
  description,
  children,
}: {
  eyebrow: string;
  title: string;
  description: string;
  children?: ReactNode;
}) {
  return (
    <section className="relative overflow-hidden bg-[var(--brand-navy)] text-white">
      <div
        aria-hidden="true"
        className="jurion-dotgrid pointer-events-none absolute inset-0 opacity-70 [mask-image:linear-gradient(to_bottom,black,transparent_85%)]"
      />
      <div
        aria-hidden="true"
        className="jurion-aurora pointer-events-none absolute -right-24 -top-56 h-[38rem] w-[38rem] rounded-full bg-[var(--brand-cobalt)]/25 blur-[110px]"
      />
      <div
        aria-hidden="true"
        className="pointer-events-none absolute -bottom-40 left-1/4 h-80 w-80 rounded-full bg-[var(--brand-cobalt)]/10 blur-[100px]"
      />

      <div className="relative mx-auto max-w-7xl px-6 pb-16 pt-14 sm:pb-20 sm:pt-20 lg:px-8">
        <div className="max-w-3xl">
          <Reveal y={14}>
            <p className="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/6 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-200 backdrop-blur">
              <span aria-hidden="true" className="jurion-pulse h-1.5 w-1.5 rounded-full bg-[var(--brand-cobalt)]" />
              {eyebrow}
            </p>
          </Reveal>
          <Reveal delay={0.06} y={18}>
            <h1 className="mt-6 text-balance text-4xl font-semibold leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl">
              {title}
            </h1>
          </Reveal>
          <Reveal delay={0.12} y={18}>
            <p className="mt-5 max-w-2xl text-base leading-7 text-slate-300 sm:text-lg">{description}</p>
          </Reveal>
          {children ? (
            <Reveal delay={0.18} y={16} className="mt-9">
              {children}
            </Reveal>
          ) : null}
        </div>
      </div>

      <div aria-hidden="true" className="absolute inset-x-0 bottom-0 h-px bg-white/10" />
    </section>
  );
}
