"use client";

import { useState } from "react";
import { Button } from "@/components/arc/button/button";
import type { ActionResult } from "../../../_lib/api-action";
import { reviewEvaluationAction } from "./actions";

export function ReviewForm({ scoreId, eventId }: { scoreId: number; eventId: number }) {
  const [notes, setNotes] = useState("");
  const [pending, setPending] = useState<"reviewed" | "needs_revision" | null>(null);
  const [state, setState] = useState<ActionResult>({});

  async function submit(status: "reviewed" | "needs_revision") {
    setPending(status);
    setState({});
    const result = await reviewEvaluationAction(scoreId, eventId, status, notes);
    setState(result);
    setPending(null);
  }

  return (
    <div className="mt-3 flex flex-col gap-2">
      {state.error ? <p className="text-xs text-rose-600">{state.error}</p> : null}
      {state.ok ? <p className="text-xs text-emerald-700">Status review tersimpan.</p> : null}
      <textarea
        value={notes}
        onChange={(event) => setNotes(event.target.value)}
        rows={2}
        maxLength={2000}
        placeholder="Catatan review internal (opsional)"
        className="w-full rounded-lg border border-[var(--border)] bg-[var(--background)] px-2.5 py-2 text-xs outline-none focus:border-[var(--foreground)]/40"
      />
      <div className="flex flex-wrap gap-2">
        <Button type="button" variant="primary" disabled={pending !== null} onClick={() => submit("reviewed")}>
          {pending === "reviewed" ? "Menyimpan…" : "Tandai Reviewed"}
        </Button>
        <Button type="button" variant="secondary" disabled={pending !== null} onClick={() => submit("needs_revision")}>
          {pending === "needs_revision" ? "Menyimpan…" : "Perlu Revisi"}
        </Button>
      </div>
    </div>
  );
}
