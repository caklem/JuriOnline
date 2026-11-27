"use client";

import { useActionState } from "react";
import { Button } from "@/components/arc/button/button";
import { rejectRevisionAction, type RevisionActionState } from "./actions";

export function RejectRevisionForm({ revisionId }: { revisionId: number }) {
  const [state, formAction, pending] = useActionState<RevisionActionState, FormData>(
    rejectRevisionAction.bind(null, revisionId),
    {},
  );

  return (
    <form action={formAction} className="mt-3 flex flex-col gap-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] p-3">
      {state.error ? <p className="text-xs text-rose-600">{state.error}</p> : null}
      <label htmlFor={`reject-${revisionId}`} className="text-xs font-semibold text-[var(--foreground)]">
        Tolak dengan alasan (jika nilai sudah sesuai rubrik)
      </label>
      <textarea
        id={`reject-${revisionId}`}
        name="notes"
        rows={2}
        required
        maxLength={1000}
        className="w-full rounded-lg border border-[var(--border)] bg-[var(--background)] px-2.5 py-2 text-xs outline-none focus:border-[var(--foreground)]/40"
      />
      <Button type="submit" variant="secondary" disabled={pending}>
        {pending ? "Memproses…" : "Tolak Permintaan"}
      </Button>
    </form>
  );
}
