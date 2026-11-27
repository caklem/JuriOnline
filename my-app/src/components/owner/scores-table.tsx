"use client";

import Link from "next/link";
import { useState } from "react";
import { AnimatePresence, motion, useReducedMotion } from "motion/react";
import { Button } from "@/components/arc/button/button";
import { Badge, Table } from "@/components/kit/ui";
import { formatDateTime } from "./format";
import type { ScoreRow } from "./types";

function FeedbackCell({ scoreId, kind, text }: { scoreId: number; kind: "public" | "private"; text: string | null }) {
  const [open, setOpen] = useState(false);
  const reduced = useReducedMotion();
  const panelId = `${kind}-${scoreId}`;

  if (!text) {
    return <span className="text-slate-400">-</span>;
  }

  return (
    <div className="max-w-xs">
      <Button
        type="button"
        variant={kind === "public" ? "secondary" : "ghost"}
        size="sm"
        aria-expanded={open}
        aria-controls={panelId}
        onClick={() => setOpen((value) => !value)}
      >
        {kind === "public" ? "Lihat Feedback" : "Lihat Evaluasi"}
      </Button>
      <AnimatePresence initial={false}>
        {open ? (
          <motion.div
            id={panelId}
            initial={reduced ? false : { opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: "auto" }}
            exit={reduced ? { opacity: 0 } : { opacity: 0, height: 0 }}
            transition={{ duration: reduced ? 0 : 0.24, ease: [0.22, 1, 0.36, 1] }}
            className={`mt-2 overflow-hidden rounded-lg border-l-[3px] px-3 py-2 text-xs leading-5 ${
              kind === "public"
                ? "border-indigo-500 bg-slate-50 text-[var(--text-secondary)]"
                : "border-amber-500 bg-amber-50/70 text-[var(--text-secondary)]"
            }`}
          >
            <p className="whitespace-pre-line">{text}</p>
          </motion.div>
        ) : null}
      </AnimatePresence>
    </div>
  );
}

export function ScoresTable({ scores }: { scores: ScoreRow[] }) {
  const reduced = useReducedMotion();

  return (
    <Table
      head={[
        "Tanggal",
        "Event",
        "Peserta",
        "Juri",
        "Nilai",
        "Feedback Publik",
        "Evaluasi Private",
      ]}
    >
      {scores.map((score, index) => (
        <motion.tr
          key={score.id}
          initial={reduced ? false : { opacity: 0, y: 6 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: reduced ? 0 : 0.24, delay: reduced ? 0 : Math.min(index, 10) * 0.025 }}
          className="align-top"
        >
          <td className="whitespace-nowrap px-5 py-4 text-[var(--text-secondary)]">
            {formatDateTime(score.created_at)}
          </td>
          <td className="px-5 py-4">
            {score.event.id ? (
              <Link
                href={`/events/${score.event.id}`}
                className="font-medium text-indigo-600 underline underline-offset-2 hover:text-indigo-700"
              >
                {score.event.name}
              </Link>
            ) : (
              <span className="text-slate-500">-</span>
            )}
          </td>
          <td className="px-5 py-4 text-[var(--foreground)]">{score.participant.name ?? "-"}</td>
          <td className="px-5 py-4 text-[var(--foreground)]">{score.jury.name ?? "-"}</td>
          <td className="px-5 py-4">
            <Badge tone="info">{score.score.toFixed(2)}/100</Badge>
          </td>
          <td className="px-5 py-4">
            <FeedbackCell scoreId={score.id} kind="public" text={score.public_feedback} />
          </td>
          <td className="px-5 py-4">
            <FeedbackCell scoreId={score.id} kind="private" text={score.private_evaluation} />
          </td>
        </motion.tr>
      ))}
    </Table>
  );
}
