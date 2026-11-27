"use client";

import { useEffect, useRef, useState, useTransition } from "react";
import { useRouter } from "next/navigation";
import { AnimatePresence, motion, useReducedMotion } from "motion/react";
import { Button } from "@/components/arc/button/button";
import { Input } from "@/components/arc/input/input";
import { createUserAction, type ActionState } from "@/app/owner/users/actions";
import type { Role } from "./types";

const SELECT_CLASS =
  "h-10 w-full rounded-lg border border-[var(--border)] bg-[var(--background)] px-3 text-sm text-[var(--foreground)] outline-none transition focus:border-[var(--accent)] focus:ring-2 focus:ring-[var(--accent-subtle)]";

export function CreateUserForm({ roles }: { roles: Role[] }) {
  const [open, setOpen] = useState(false);
  const [feedback, setFeedback] = useState<ActionState | null>(null);
  const [pending, startTransition] = useTransition();
  const formRef = useRef<HTMLFormElement>(null);
  const reduced = useReducedMotion();
  const router = useRouter();

  useEffect(() => {
    if (feedback?.success) {
      formRef.current?.reset();
    }
  }, [feedback?.success]);

  function submit(formData: FormData) {
    setFeedback(null);
    startTransition(async () => {
      const result = await createUserAction({}, formData);
      setFeedback(result);
      if (result.success) {
        router.refresh();
      }
    });
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center gap-2">
        <Button type="button" variant="primary" onClick={() => setOpen((value) => !value)}>
          {open ? "Tutup Form" : "Tambah Pengguna"}
        </Button>
        {feedback?.success ? (
          <p role="status" className="text-xs font-medium text-emerald-600">
            {feedback.success}
          </p>
        ) : null}
      </div>

      <AnimatePresence initial={false}>
        {open ? (
          <motion.section
            key="create-user"
            initial={reduced ? false : { opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: "auto" }}
            exit={reduced ? { opacity: 0 } : { opacity: 0, height: 0 }}
            transition={{ duration: reduced ? 0 : 0.28, ease: [0.22, 1, 0.36, 1] }}
            className="overflow-hidden rounded-xl border border-[var(--border)] bg-[var(--background)]"
          >
            <div className="border-b border-[var(--border)] px-5 py-4">
              <h2 className="text-sm font-semibold text-[var(--foreground)]">Tambah Pengguna Baru</h2>
              <p className="mt-1 text-xs text-slate-500">
                Akun langsung aktif dan dapat login dengan password yang Anda tetapkan.
              </p>
            </div>

            <form
              ref={formRef}
              action={submit}
              className="grid gap-5 px-5 py-5 md:grid-cols-2"
            >
              <Input name="name" label="Nama" autoComplete="off" required />
              <Input name="email" type="email" label="Email" autoComplete="off" required />
              <div className="space-y-1.5">
                <label htmlFor="create-role_id" className="text-[13px] font-medium text-[var(--foreground)]">
                  Role
                </label>
                <select id="create-role_id" name="role_id" className={SELECT_CLASS} required defaultValue="">
                  <option value="">Pilih role</option>
                  {roles.map((role) => (
                    <option key={role.id} value={role.id}>
                      {role.display_name}
                    </option>
                  ))}
                </select>
              </div>
              <div className="space-y-1.5">
                <label htmlFor="create-event_scope" className="text-[13px] font-medium text-[var(--foreground)]">
                  Akses Event (khusus Admin)
                </label>
                <select id="create-event_scope" name="event_scope" className={SELECT_CLASS} defaultValue="all">
                  <option value="all">Semua event</option>
                  <option value="own">Hanya event sendiri</option>
                </select>
              </div>
              <Input
                name="password"
                type="password"
                label="Password"
                autoComplete="new-password"
                minLength={8}
                required
              />
              <Input
                name="password_confirmation"
                type="password"
                label="Konfirmasi Password"
                autoComplete="new-password"
                minLength={8}
                required
              />

              {feedback?.error ? (
                <p role="alert" className="text-xs font-medium text-rose-600 md:col-span-2">
                  {feedback.error}
                </p>
              ) : null}

              <div className="flex flex-wrap gap-3 md:col-span-2">
                <Button type="submit" variant="primary" loading={pending}>
                  Simpan Pengguna
                </Button>
                <Button type="button" variant="secondary" onClick={() => setOpen(false)}>
                  Batal
                </Button>
              </div>
            </form>
          </motion.section>
        ) : null}
      </AnimatePresence>
    </div>
  );
}
