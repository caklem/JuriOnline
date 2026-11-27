"use client";

import { useActionState, useEffect, useRef } from "react";
import { useRouter } from "next/navigation";
import { Button } from "@/components/arc/button/button";
import type { ActionResult } from "@/app/admin/_lib/api-action";

const DIALOG =
  "m-auto w-full max-w-md rounded-2xl border border-[var(--border)] bg-[var(--background)] p-6 text-[var(--foreground)] shadow-xl backdrop:bg-slate-950/70";

const INITIAL_STATE: ActionResult = {};

const CONTROL =
  "mt-1.5 w-full rounded-lg border border-[var(--border)] bg-[var(--background)] px-3 py-2 text-sm text-[var(--foreground)] outline-none transition focus:border-[var(--foreground)]";

function FieldError({ message }: { message?: string }) {
  if (!message) return null;

  return (
    <p role="alert" className="mt-1.5 rounded-lg bg-rose-50 px-3 py-2 text-xs font-medium text-rose-700">
      {message}
    </p>
  );
}

export function PaymentActions({
  paymentId,
  userName,
  action,
}: {
  paymentId: number;
  userName: string;
  action: (prev: ActionResult, formData: FormData) => Promise<ActionResult>;
}) {
  const router = useRouter();
  const verifyDialog = useRef<HTMLDialogElement>(null);
  const rejectDialog = useRef<HTMLDialogElement>(null);
  const [verifyState, verifyFormAction, verifyPending] = useActionState(action, INITIAL_STATE);
  const [rejectState, rejectFormAction, rejectPending] = useActionState(action, INITIAL_STATE);

  useEffect(() => {
    if (verifyState.ok) {
      verifyDialog.current?.close();
      router.refresh();
    }
  }, [verifyState.ok, router]);

  useEffect(() => {
    if (rejectState.ok) {
      rejectDialog.current?.close();
      router.refresh();
    }
  }, [rejectState.ok, router]);

  return (
    <div className="flex flex-wrap items-center gap-2">
      <a
        href={`/admin/payments/proof/${paymentId}`}
        target="_blank"
        rel="noopener"
        className="inline-flex items-center justify-center rounded-lg border border-[var(--border)] px-3 py-2 text-sm font-medium text-[var(--foreground)] transition hover:bg-[var(--surface-muted)]"
      >
        Lihat Bukti
      </a>
      <Button type="button" variant="primary" size="sm" onClick={() => verifyDialog.current?.showModal()}>
        Verifikasi
      </Button>
      <Button type="button" variant="danger" size="sm" onClick={() => rejectDialog.current?.showModal()}>
        Tolak
      </Button>

      <dialog ref={verifyDialog} className={DIALOG} aria-labelledby={`verify-title-${paymentId}`}>
        <h2 id={`verify-title-${paymentId}`} className="text-base font-semibold text-[var(--foreground)]">
          Verifikasi Pembayaran
        </h2>
        <p className="mt-1 text-sm text-[var(--text-secondary)]">
          Verifikasi pembayaran dari <strong className="font-semibold text-[var(--foreground)]">{userName}</strong>?
        </p>

        <form action={verifyFormAction} className="mt-5">
          <input type="hidden" name="status" value="verified" />

          <label className="block text-sm font-medium text-[var(--foreground)]" htmlFor={`verify-notes-${paymentId}`}>
            Catatan (opsional)
          </label>
          <textarea id={`verify-notes-${paymentId}`} name="verification_notes" rows={3} className={CONTROL} />

          <FieldError message={verifyState.error} />

          <div className="mt-4 flex flex-wrap gap-2">
            <Button type="submit" variant="primary" loading={verifyPending}>
              Verifikasi
            </Button>
            <Button type="button" variant="secondary" onClick={() => verifyDialog.current?.close()}>
              Batal
            </Button>
          </div>
        </form>
      </dialog>

      <dialog ref={rejectDialog} className={DIALOG} aria-labelledby={`reject-title-${paymentId}`}>
        <h2 id={`reject-title-${paymentId}`} className="text-base font-semibold text-[var(--foreground)]">
          Tolak Pembayaran
        </h2>
        <p className="mt-1 text-sm text-[var(--text-secondary)]">
          Tolak pembayaran dari <strong className="font-semibold text-[var(--foreground)]">{userName}</strong>?
        </p>

        <form action={rejectFormAction} className="mt-5">
          <input type="hidden" name="status" value="rejected" />

          <label className="block text-sm font-medium text-[var(--foreground)]" htmlFor={`reject-notes-${paymentId}`}>
            Alasan Penolakan *
          </label>
          <textarea id={`reject-notes-${paymentId}`} name="verification_notes" rows={3} required className={CONTROL} />

          <FieldError message={rejectState.error} />

          <div className="mt-4 flex flex-wrap gap-2">
            <Button type="submit" variant="danger" loading={rejectPending}>
              Tolak
            </Button>
            <Button type="button" variant="secondary" onClick={() => rejectDialog.current?.close()}>
              Batal
            </Button>
          </div>
        </form>
      </dialog>
    </div>
  );
}
