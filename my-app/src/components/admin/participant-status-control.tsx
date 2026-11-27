"use client";

import { useState, useTransition } from "react";
import { useRouter } from "next/navigation";
import { Button } from "@/components/arc/button/button";
import type { ActionResult } from "@/app/admin/_lib/api-action";

const STATUS_OPTIONS = [
  { value: "pending", label: "Pending" },
  { value: "verified", label: "Terverifikasi" },
  { value: "rejected", label: "Ditolak" },
  { value: "cancelled", label: "Dibatalkan" },
];

export function ParticipantStatusControl({
  action,
  currentStatus,
}: {
  action: (status: string) => Promise<ActionResult>;
  currentStatus: string;
}) {
  const router = useRouter();
  const [status, setStatus] = useState(currentStatus);
  const [pending, startTransition] = useTransition();
  const [error, setError] = useState<string | null>(null);

  const save = () => {
    if (status === currentStatus) {
      return;
    }

    setError(null);
    startTransition(async () => {
      try {
        const result = await action(status);

        if (result.error) {
          setError(result.error);
          return;
        }

        router.refresh();
      } catch {
        setError("Terjadi kesalahan. Coba lagi.");
      }
    });
  };

  return (
    <div className="flex min-w-[190px] flex-col gap-1.5">
      <div className="flex items-center gap-2">
        <select
          aria-label="Status Pendaftaran"
          value={status}
          onChange={(event) => setStatus(event.target.value)}
          className="w-full rounded-lg border border-[var(--border)] bg-[var(--background)] px-2.5 py-1.5 text-xs text-[var(--foreground)] outline-none focus:border-[var(--foreground)]"
        >
          {STATUS_OPTIONS.map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
        <Button
          type="button"
          variant="secondary"
          size="sm"
          loading={pending}
          disabled={pending || status === currentStatus}
          onClick={save}
        >
          Simpan
        </Button>
      </div>
      {error ? (
        <span role="alert" className="text-[11px] font-medium text-rose-600">
          {error}
        </span>
      ) : null}
    </div>
  );
}
