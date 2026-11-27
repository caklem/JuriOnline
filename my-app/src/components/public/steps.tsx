"use client";

import Link from "next/link";
import { ArrowRight, Check } from "lucide-react";
import {
  motion,
  useMotionValueEvent,
  useReducedMotion,
  useScroll,
  useSpring,
} from "motion/react";
import { useRef, useState } from "react";
import { Reveal } from "./reveal";

export type StepItem = { title: string; description: string };

/** Langkah kerja dengan garis progres yang mengikuti scroll dan kartu aktif. */
export function HowItWorks({ steps }: { steps: StepItem[] }) {
  const reduced = useReducedMotion();
  const listRef = useRef<HTMLOListElement>(null);
  const { scrollYProgress } = useScroll({
    target: listRef,
    offset: ["start 72%", "end 72%"],
  });
  const line = useSpring(scrollYProgress, { stiffness: 180, damping: 30, restDelta: 0.001 });
  const [active, setActive] = useState(0);

  useMotionValueEvent(scrollYProgress, "change", (value) => {
    const next = Math.max(0, Math.min(steps.length - 1, Math.floor(value * steps.length)));
    setActive(next);
  });

  return (
    <div className="grid gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:gap-16">
      <div className="lg:sticky lg:top-28 lg:self-start">
        <Reveal>
        <p className="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-[var(--brand-cobalt-strong)]">
          <span aria-hidden="true" className="h-px w-6 bg-[var(--brand-cobalt)]/50" />
          Cara kerja
        </p>
        <h2 className="mt-3 max-w-md text-balance text-3xl font-semibold leading-[1.12] tracking-tight text-[var(--brand-navy)] sm:text-4xl">
          Dari pendaftaran hingga pengumuman, dalam satu tempat.
        </h2>
        <p className="mt-4 max-w-md text-base leading-7 text-slate-600">
          Tidak perlu menggabungkan spreadsheet, pesan grup, dan rekap manual. JURI.ON menjaga alur kerja tetap
          terlihat oleh semua pihak yang berwenang.
        </p>
        <Link
          href="/register"
          className="group mt-8 inline-flex items-center gap-2 text-sm font-semibold text-[var(--brand-cobalt-strong)] transition hover:text-[var(--brand-cobalt-deep)]"
        >
          Mulai sebagai penyelenggara
          <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" aria-hidden="true" />
        </Link>

        <div className="mt-10 hidden items-center gap-3 lg:flex">
          <div className="flex -space-x-2">
            {["AD", "JR", "PS"].map((initials, index) => (
              <span
                key={initials}
                className="grid h-9 w-9 place-items-center rounded-full border-2 border-white bg-[var(--brand-light-blue)] text-[10px] font-semibold text-[var(--brand-cobalt-deep)]"
                style={{ zIndex: 3 - index }}
              >
                {initials}
              </span>
            ))}
          </div>
          <p className="text-xs leading-5 text-slate-500">
            Admin, juri, dan peserta
            <br />
            memakai satu alur yang sama.
          </p>
        </div>
        </Reveal>
      </div>

      <div className="relative">
        <div aria-hidden="true" className="absolute bottom-4 left-5 top-4 w-px bg-slate-200" />
        <motion.div
          aria-hidden="true"
          className="absolute left-5 top-4 w-px origin-top bg-gradient-to-b from-[var(--brand-cobalt)] to-[var(--brand-navy)]"
          style={{ height: "calc(100% - 2rem)", scaleY: reduced ? 1 : line }}
        />

        <ol ref={listRef} className="space-y-4">
          {steps.map((step, index) => {
            const isActive = active === index;

            return (
              <li key={step.title} className="relative pl-14">
                <motion.span
                  aria-hidden="true"
                  className={`absolute left-0 top-5 z-10 grid h-10 w-10 place-items-center rounded-full border text-xs font-semibold transition-colors duration-300 ${
                    isActive
                      ? "border-[var(--brand-cobalt)] bg-[var(--brand-cobalt)] text-white shadow-[0_10px_30px_-10px_rgba(47,128,255,0.8)]"
                      : "border-slate-200 bg-white text-slate-400"
                  }`}
                  layout
                >
                  {isActive ? <Check className="h-4 w-4" aria-hidden="true" /> : String(index + 1).padStart(2, "0")}
                </motion.span>

                <motion.div
                  initial={reduced ? { opacity: 0 } : { opacity: 0, y: 24 }}
                  whileInView={reduced ? { opacity: 1 } : { opacity: 1, y: 0 }}
                  viewport={{ once: true, margin: "-80px 0px -24px 0px" }}
                  transition={
                    reduced
                      ? { duration: 0.3, delay: index * 0.03 }
                      : { duration: 0.55, delay: 0.04, ease: [0.22, 1, 0.36, 1] }
                  }
                  className={`rounded-2xl border p-6 transition-all duration-300 ${
                    isActive
                      ? "border-[var(--brand-cobalt)]/25 bg-white shadow-[0_20px_50px_-30px_rgba(11,31,70,0.35)]"
                      : "border-transparent bg-slate-50/60"
                  }`}
                >
                  <div className="flex items-start justify-between gap-4">
                    <h3 className={`text-base font-semibold ${isActive ? "text-[var(--brand-navy)]" : "text-slate-600"}`}>
                      {step.title}
                    </h3>
                    <span className="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                      Langkah {index + 1}
                    </span>
                  </div>
                  <p className="mt-2 text-sm leading-6 text-slate-600">{step.description}</p>
                </motion.div>
              </li>
            );
          })}
        </ol>
      </div>
    </div>
  );
}
