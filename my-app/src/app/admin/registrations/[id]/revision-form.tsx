"use client";

import { useActionState } from "react";
import { Button } from "@/components/arc/button/button";
import type { ActionResult } from "../../_lib/api-action";
import { requestScoreRevisionAction } from "./actions";

export function ScoreRevisionForm({ scoreId }: { scoreId: number }) {
  const [state, formAction, pending] = useActionState<ActionResult, FormData>(
    requestScoreRevisionAction.bind(null, scoreId),
    {},
  );

  return (
    <form action={formAction} className="mt-2 flex flex-col gap-2 rounded-lg border border-[var(--border)] bg-[var(--surface-muted)] p-3">
      {state.error ? <p className="text-xs text-rose-600">{state.error}</p> : null}
      {state.ok ? (
        <p className="text-xs text-emerald-700">{state.message ?? "Permintaan revisi terkirim ke juri."}</p>
      ) : (
        <>
          <label htmlFor={`revision-${scoreId}`} className="text-xs font-semibold text-[var(--foreground)]">
            Minta juri meninjau ulang nilai ini
          </label>
          <textarea
            id={`revision-${scoreId}`}
            name="reason"
            rows={3}
            required
            minLength={10}
            maxLength={1000}
            placeholder="Jelaskan alasan konkret, mis. nilai tidak konsisten dengan rubrik atau bukti penilaian."
            className="w-full rounded-lg border border-[var(--border)] bg-[var(--background)] px-2.5 py-2 text-xs outline-none focus:border-[var(--foreground)]/40"
          />
          <Button type="submit" variant="secondary" disabled={pending}>
            {pending ? "Mengirim…" : "Kirim Permintaan Revisi"}
          </Button>
        </>
      )}
    </form>
  );
}
