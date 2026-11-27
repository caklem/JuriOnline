"use client";

import { useActionState, useState } from "react";
import { Button } from "@/components/arc/button/button";
import { Input } from "@/components/arc/input/input";
import {
  resetJuryPasswordAction,
  type Credentials,
} from "@/app/admin/events/[id]/jury-assignments/actions";
import type { ActionResult } from "@/app/admin/_lib/api-action";

type State = ActionResult & { credentials?: Credentials };
const initialState: State = {};

function CopyButton({ value }: { value: string }) {
  const [copied, setCopied] = useState(false);

  return (
    <button
      type="button"
      onClick={async () => {
        try {
          await navigator.clipboard.writeText(value);
          setCopied(true);
          window.setTimeout(() => setCopied(false), 1500);
        } catch {
          setCopied(false);
        }
      }}
      className="rounded-md border border-emerald-200 bg-white px-2 py-1 text-[11px] font-semibold text-emerald-800 transition hover:bg-emerald-100"
    >
      {copied ? "Tersalin" : "Salin"}
    </button>
  );
}

function CredentialsBox({ credentials, message }: { credentials?: Credentials; message?: string }) {
  if (!credentials) {
    return message ? <p role="status" className="text-sm font-medium text-emerald-600">{message}</p> : null;
  }

  const all = `Nama: ${credentials.name}\nEmail: ${credentials.email}\nPassword: ${credentials.code}`;

  return (
    <div className="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs text-emerald-950">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <p className="font-semibold">{message ?? "Password juri berhasil diperbarui."}</p>
        <CopyButton value={all} />
      </div>
      <div className="mt-2 grid gap-2">
        <p className="flex items-center justify-between gap-2">
          <span className="break-all">Email: <strong>{credentials.email}</strong></span>
          <CopyButton value={credentials.email} />
        </p>
        <p className="flex items-center justify-between gap-2">
          <span className="break-all">Password: <strong className="font-mono">{credentials.code}</strong></span>
          <CopyButton value={credentials.code} />
        </p>
      </div>
      <p className="mt-2 text-[11px] text-emerald-800">Salin sekarang — password tidak dapat ditampilkan lagi setelah halaman dimuat ulang.</p>
    </div>
  );
}

export function ResetJuryPasswordForm({ eventId, assignmentId }: { eventId: number; assignmentId: number }) {
  const [state, formAction, pending] = useActionState(
    resetJuryPasswordAction.bind(null, eventId, assignmentId),
    initialState,
  );

  return (
    <form action={formAction} className="flex flex-col gap-2">
      <Input label="Password baru (opsional)" name="password" type="text" placeholder="Kosongkan untuk kode acak" description="Minimal 8 karakter." />
      {state.error ? <p role="alert" className="text-xs font-medium text-rose-600">{state.error}</p> : null}
      <CredentialsBox credentials={state.credentials} message={state.message} />
      <div className="flex justify-end">
        <Button type="submit" variant="secondary" size="sm" loading={pending}>Reset / Ganti Password</Button>
      </div>
    </form>
  );
}
