"use client";

import { useState, useTransition } from "react";
import type { ReactNode } from "react";
import { useRouter } from "next/navigation";
import { Button, type ButtonSize, type ButtonVariant } from "@/components/arc/button/button";
import type { ActionResult } from "@/app/admin/_lib/api-action";

/**
 * Tombol aksi yang memanggil Server Action, menampilkan galat dalam bahasa Indonesia,
 * lalu memuat ulang data halaman setelah berhasil.
 */
export function ActionButton({
  action,
  children,
  variant = "secondary",
  size = "md",
  confirm,
  disabled = false,
  fullWidth = false,
}: {
  action: () => Promise<ActionResult>;
  children: ReactNode;
  variant?: ButtonVariant;
  size?: ButtonSize;
  confirm?: string;
  disabled?: boolean;
  fullWidth?: boolean;
}) {
  const router = useRouter();
  const [pending, startTransition] = useTransition();
  const [error, setError] = useState<string | null>(null);

  return (
    <span className={fullWidth ? "flex w-full flex-col gap-1.5" : "inline-flex flex-col gap-1.5"}>
      <Button
        type="button"
        variant={variant}
        size={size}
        loading={pending}
        disabled={disabled || pending}
        className={fullWidth ? "w-full" : undefined}
        onClick={() => {
          if (confirm && !window.confirm(confirm)) {
            return;
          }

          setError(null);

          startTransition(async () => {
            try {
              const result = await action();

              if (result.error) {
                setError(result.error);
                return;
              }

              router.refresh();
            } catch {
              setError("Terjadi kesalahan. Coba lagi.");
            }
          });
        }}
      >
        {children}
      </Button>
      {error ? (
        <span role="alert" className="text-xs font-medium text-rose-600">
          {error}
        </span>
      ) : null}
    </span>
  );
}
