"use client";

import { useState, useTransition } from "react";
import { useRouter } from "next/navigation";
import { Button } from "@/components/arc/button/button";
import { respondInvitation } from "@/app/jury/actions";

export function InvitationActions({ assignmentId }: { assignmentId: number }) {
  const router = useRouter();
  const [pending, startTransition] = useTransition();
  const [error, setError] = useState<string | null>(null);

  const respond = (action: "accept" | "decline") => {
    setError(null);
    startTransition(async () => {
      const result = await respondInvitation(assignmentId, action);

      if (!result.ok) {
        setError(result.message ?? "Undangan gagal diproses. Coba lagi.");
        return;
      }

      if (action === "accept" && result.eventId) {
        const notice = result.message ? `?notice=${encodeURIComponent(result.message)}` : "";
        router.push(`/jury/events/${result.eventId}/participants${notice}`);
        return;
      }

      router.replace(
        result.message
          ? `/jury/invitations?notice=${encodeURIComponent(result.message)}`
          : "/jury/invitations",
      );
    });
  };

  return (
    <div className="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
      <Button
        type="button"
        variant="primary"
        loading={pending}
        onClick={() => respond("accept")}
        className="w-full sm:w-auto"
      >
        Terima Undangan
      </Button>
      <Button
        type="button"
        variant="secondary"
        disabled={pending}
        onClick={() => {
          if (window.confirm("Tolak undangan menjadi juri di event ini?")) {
            respond("decline");
          }
        }}
        className="w-full sm:w-auto"
      >
        Tolak
      </Button>
      {error ? (
        <p className="self-center text-xs font-medium text-rose-600" role="alert">
          {error}
        </p>
      ) : null}
    </div>
  );
}
