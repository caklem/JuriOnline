"use client";

import { useActionState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { Button } from "@/components/arc/button/button";
import { Input } from "@/components/arc/input/input";
import { resetPasswordAction, type ResetState } from "./actions";

const initialState: ResetState = {};

export function ResetForm({ token, email }: { token: string; email: string }) {
  const router = useRouter();
  const [state, formAction, pending] = useActionState(resetPasswordAction, initialState);

  useEffect(() => {
    if (!state.ok) return;
    const timer = setTimeout(() => router.replace("/login"), 1200);
    return () => clearTimeout(timer);
  }, [state.ok, router]);

  return (
    <form action={formAction} className="mt-6 flex flex-col gap-4">
      <input type="hidden" name="token" value={token} />
      <Input label="Email" name="email" type="email" defaultValue={email} autoComplete="username" required />
      <Input label="Password baru" name="password" type="password" autoComplete="new-password" required error={state.error} />
      <Input
        label="Konfirmasi password baru"
        name="password_confirmation"
        type="password"
        autoComplete="new-password"
        required
      />
      {state.message ? (
        <p role="status" className="text-sm font-medium text-emerald-600">{state.message}</p>
      ) : null}
      <Button type="submit" variant="primary" size="lg" loading={pending} className="mt-2 w-full">
        Simpan password baru
      </Button>
    </form>
  );
}
