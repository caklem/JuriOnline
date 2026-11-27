"use client";

import { useActionState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { Button } from "@/components/arc/button/button";
import { Input } from "@/components/arc/input/input";
import { loginAction, type LoginState } from "./actions";

const initialState: LoginState = {};

export function LoginForm({ next = "/dashboard" }: { next?: string }) {
  const router = useRouter();
  const [state, formAction, pending] = useActionState(loginAction, initialState);

  useEffect(() => {
    if (!state.ok) return;
    router.replace(next);
    router.refresh();
  }, [state.ok, next, router]);

  return (
    <form action={formAction} className="mt-8 flex flex-col gap-4">
      <Input
        label="Email"
        name="email"
        type="email"
        autoComplete="email"
        placeholder="nama@email.com"
        required
      />
      <Input
        label="Password"
        name="password"
        type="password"
        autoComplete="current-password"
        placeholder="Masukkan password Anda"
        required
        error={state.error}
      />

      <div className="flex flex-wrap items-center justify-between gap-3 text-sm">
        <label className="flex items-center gap-2 text-[var(--text-secondary)]">
          <input type="checkbox" name="remember" className="h-4 w-4 rounded border-[var(--border-strong)]" />
          Ingat saya
        </label>
        <a href="/forgot-password" className="font-medium text-[var(--text-secondary)] underline underline-offset-2">
          Lupa password?
        </a>
      </div>

      <Button type="submit" variant="primary" size="lg" loading={pending} className="mt-2 w-full">
        Masuk
      </Button>

      <div className="text-center text-xs leading-5 text-[var(--text-muted)]">
        <p>
          Belum punya akun?{" "}
          <a href={`/register?next=${encodeURIComponent(next)}`} className="font-semibold underline underline-offset-2">Daftar sekarang</a>
        </p>
        <p className="mt-2">
          Juri yang mendapat akun dari panitia: masukkan email dan <strong>kode akses</strong> pada kolom password.
        </p>
        <p className="mt-1">
          Ingin mendaftar sebagai juri?{" "}
          <a href="/register/jury" className="font-semibold underline underline-offset-2">Daftar akun juri</a>
        </p>
      </div>
    </form>
  );
}
