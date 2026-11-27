"use client";

import Link from "next/link";
import { useActionState } from "react";
import { AnimatePresence, motion, useReducedMotion } from "motion/react";
import { Button } from "@/components/arc/button/button";
import { buttonLinkClass } from "@/components/participant/button-link";
import { uploadProof, type UploadProofState } from "./actions";

const initialState: UploadProofState = {};

export function PaymentForm({ registrationId }: { registrationId: number }) {
  const [state, formAction, pending] = useActionState(uploadProof.bind(null, registrationId), initialState);
  const reduceMotion = useReducedMotion() ?? false;

  return (
    <form action={formAction} className="flex flex-col gap-5">
      <div className="flex flex-col gap-1.5">
        <label htmlFor="proof_file" className="text-sm font-medium text-[var(--foreground)]">
          Bukti Transfer (JPG, PNG, PDF - Max 2MB) *
        </label>
        <input
          id="proof_file"
          name="proof_file"
          type="file"
          required
          accept="image/jpeg,image/png,application/pdf"
          aria-invalid={state.fieldError ? true : undefined}
          aria-describedby={state.fieldError ? "proof_file-error" : undefined}
          className="w-full cursor-pointer rounded-lg border border-[var(--border)] bg-[var(--background)] px-3 py-2 text-sm text-[var(--text-secondary)] file:mr-3 file:rounded-md file:border-0 file:bg-[var(--foreground)] file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-[var(--background)]"
        />
        <p className="text-xs leading-5 text-[var(--text-muted)]">
          Pastikan nominal transfer sesuai dengan jumlah yang tertera.
        </p>
        <AnimatePresence initial={false}>
          {state.fieldError ? (
            <motion.p
              key="field-error"
              id="proof_file-error"
              role="alert"
              initial={reduceMotion ? false : { opacity: 0, y: -4 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0 }}
              transition={{ duration: reduceMotion ? 0 : 0.2 }}
              className="text-xs font-medium text-rose-600"
            >
              {state.fieldError}
            </motion.p>
          ) : null}
        </AnimatePresence>
      </div>

      <AnimatePresence initial={false}>
        {state.error ? (
          <motion.div
            key="form-error"
            role="alert"
            initial={reduceMotion ? false : { opacity: 0, y: -4 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0 }}
            transition={{ duration: reduceMotion ? 0 : 0.2 }}
            className="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700"
          >
            {state.error}
          </motion.div>
        ) : null}
      </AnimatePresence>

      <div className="flex flex-wrap gap-2">
        <Button type="submit" variant="primary" loading={pending}>
          Upload Bukti Pembayaran
        </Button>
        <Link href={`/registrations/${registrationId}`} className={buttonLinkClass("secondary")}>
          Batal
        </Link>
      </div>
    </form>
  );
}
