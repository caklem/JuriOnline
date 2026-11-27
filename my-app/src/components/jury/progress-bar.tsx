"use client";

import { motion, useReducedMotion } from "motion/react";

const easeOut: [number, number, number, number] = [0.22, 1, 0.36, 1];

export function ProgressBar({ percent }: { percent: number }) {
  const reduced = useReducedMotion();
  const width = `${Math.min(100, Math.max(0, percent))}%`;

  return (
    <div
      className="mt-3 h-2 w-full overflow-hidden rounded-full bg-[var(--surface-muted)]"
      role="progressbar"
      aria-valuenow={percent}
      aria-valuemin={0}
      aria-valuemax={100}
    >
      <motion.div
        className="h-full rounded-full bg-indigo-500"
        initial={reduced ? false : { width: 0 }}
        animate={{ width }}
        transition={reduced ? { duration: 0 } : { duration: 0.7, ease: easeOut }}
      />
    </div>
  );
}
