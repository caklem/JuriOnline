"use client";

import { useActionState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { Button } from "@/components/arc/button/button";
import { Input } from "@/components/arc/input/input";
import { registerAction, type RegisterState } from "./actions";

const initialState: RegisterState = {};

export function RegisterForm({ role, submitLabel }: { role: "participant" | "jury"; submitLabel: string }) {
  const router = useRouter();
  const [state, formAction, pending] = useActionState(registerAction, initialState);

  useEffect(() => {
    if (!state.ok) return;
    router.replace("/dashboard");
    router.refresh();
  }, [state.ok, router]);

  return (
    <form action={formAction} className="mt-6 flex flex-col gap-4">
      <input type="hidden" name="role" value={role} />
      <Input label="Nama Lengkap" name="name" autoComplete="name" placeholder="Masukkan nama lengkap Anda" required />
      <Input label="Email" name="email" type="email" autoComplete="username" placeholder="nama@email.com" required />
      <Input
        label="Password"
        name="password"
        type="password"
        autoComplete="new-password"
        placeholder="Minimal 8 karakter"
        required
        error={state.error}
      />
      <Input
        label="Konfirmasi Password"
        name="password_confirmation"
        type="password"
        autoComplete="new-password"
        placeholder="Ulangi password Anda"
        required
      />
      <Button type="submit" variant="primary" size="lg" loading={pending} className="mt-2 w-full">
        {submitLabel}
      </Button>
    </form>
  );
}
