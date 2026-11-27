"use client";

import { useActionState } from "react";
import { Button } from "@/components/arc/button/button";
import { Input } from "@/components/arc/input/input";
import { forgotPasswordAction, type RegisterState } from "@/app/register/actions";

const initialState: RegisterState = {};

export function ForgotForm() {
  const [state, formAction, pending] = useActionState(forgotPasswordAction, initialState);

  return (
    <form action={formAction} className="mt-6 flex flex-col gap-4">
      <Input
        label="Email"
        name="email"
        type="email"
        autoComplete="username"
        placeholder="nama@email.com"
        required
        error={state.error}
      />
      {state.message ? (
        <p role="status" className="text-sm font-medium text-emerald-600">
          {state.message}
        </p>
      ) : null}
      <Button type="submit" variant="primary" size="lg" loading={pending} className="mt-2 w-full">
        Kirim link reset
      </Button>
      <p className="text-center text-xs text-[var(--text-muted)]">
        Ingat password Anda?{" "}
        <a href="/login" className="font-semibold underline underline-offset-2">Masuk</a>
      </p>
    </form>
  );
}
