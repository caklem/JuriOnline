"use client";

import { useActionState, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { Button } from "@/components/arc/button/button";
import { Input } from "@/components/arc/input/input";
import { Card, CardHeader } from "@/components/kit/ui";
import {
  deleteAccountAction,
  updatePasswordAction,
  updateProfileAction,
  type ActionState,
} from "./actions";

const initialState: ActionState = {};

function Feedback({ state }: { state: ActionState }) {
  if (!state.error && !state.message) return null;

  return (
    <p
      role="status"
      className={
        state.error
          ? "text-sm font-medium text-rose-600"
          : "text-sm font-medium text-emerald-600"
      }
    >
      {state.error ?? state.message}
    </p>
  );
}

export function ProfileForms({ name, email }: { name: string; email: string }) {
  const router = useRouter();
  const [profileState, profileAction, profilePending] = useActionState(updateProfileAction, initialState);
  const [passwordState, passwordAction, passwordPending] = useActionState(updatePasswordAction, initialState);
  const [deleteState, deleteAction, deletePending] = useActionState(deleteAccountAction, initialState);
  const [confirmDelete, setConfirmDelete] = useState(false);

  useEffect(() => {
    if (profileState.ok) router.refresh();
  }, [profileState.ok, router]);

  useEffect(() => {
    if (deleteState.ok) {
      router.replace("/login");
      router.refresh();
    }
  }, [deleteState.ok, router]);

  return (
    <div className="mt-8 flex flex-col gap-4">
      <Card>
        <CardHeader title="Informasi Profil" description="Perbarui nama dan alamat email akun Anda." />
        <form action={profileAction} className="flex flex-col gap-4 p-5">
          <Input label="Nama" name="name" defaultValue={name} autoComplete="name" required />
          <Input label="Email" name="email" type="email" defaultValue={email} autoComplete="username" required />
          <Feedback state={profileState} />
          <div>
            <Button type="submit" variant="primary" loading={profilePending}>
              Simpan
            </Button>
          </div>
        </form>
      </Card>

      <Card>
        <CardHeader
          title="Perbarui Password"
          description="Gunakan password yang panjang dan unik agar akun tetap aman."
        />
        <form action={passwordAction} className="flex flex-col gap-4 p-5">
          <Input label="Password saat ini" name="current_password" type="password" autoComplete="current-password" required />
          <Input label="Password baru" name="password" type="password" autoComplete="new-password" required />
          <Input
            label="Konfirmasi password baru"
            name="password_confirmation"
            type="password"
            autoComplete="new-password"
            required
          />
          <Feedback state={passwordState} />
          <div>
            <Button type="submit" variant="primary" loading={passwordPending}>
              Simpan
            </Button>
          </div>
        </form>
      </Card>

      <Card>
        <CardHeader
          title="Hapus Akun"
          description="Setelah akun dihapus, seluruh data dan sumber daya yang terkait akan dihapus secara permanen. Unduh data yang ingin Anda simpan sebelum melanjutkan."
        />
        <div className="flex flex-col gap-4 p-5">
          {!confirmDelete ? (
            <div>
              <Button type="button" variant="danger" onClick={() => setConfirmDelete(true)}>
                Hapus Akun
              </Button>
            </div>
          ) : (
            <form action={deleteAction} className="flex max-w-md flex-col gap-4">
              <h3 className="text-base font-semibold text-[var(--foreground)]">Yakin ingin menghapus akun Anda?</h3>
              <p className="text-sm leading-6 text-[var(--text-secondary)]">
                Tindakan ini permanen. Masukkan password Anda untuk mengonfirmasi penghapusan akun.
              </p>
              <Input label="Password" name="password" type="password" autoComplete="current-password" required />
              <Feedback state={deleteState} />
              <div className="flex flex-wrap gap-2">
                <Button type="button" variant="secondary" onClick={() => setConfirmDelete(false)}>
                  Batal
                </Button>
                <Button type="submit" variant="danger" loading={deletePending}>
                  Hapus Akun
                </Button>
              </div>
            </form>
          )}
        </div>
      </Card>
    </div>
  );
}
