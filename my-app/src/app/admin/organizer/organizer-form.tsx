"use client";

import { useActionState } from "react";
import { Button } from "@/components/arc/button/button";
import { Input } from "@/components/arc/input/input";
import { saveOrganizerProfileAction } from "./actions";
import type { ActionResult } from "../_lib/api-action";

function FieldError({ message }: { message?: string }) {
  return message ? <p className="mt-1 text-xs text-rose-600">{message}</p> : null;
}

type Profile = {
  organizer_name: string;
  description: string | null;
  contact_email: string | null;
  phone: string | null;
  logo_url: string | null;
  is_complete: boolean;
};

export function OrganizerProfileForm({ profile }: { profile: Profile }) {
  const [state, formAction, pending] = useActionState<ActionResult, FormData>(
    saveOrganizerProfileAction,
    {},
  );

  return (
    <form action={formAction} className="flex flex-col gap-5">
      {state.error ? (
        <p className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{state.error}</p>
      ) : null}
      {state.ok ? (
        <p className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
          {state.message ?? "Profil organizer tersimpan."}
        </p>
      ) : null}

      <Input
        label="Nama Organizer *"
        name="organizer_name"
        defaultValue={profile.organizer_name}
        required
        error={state.errors?.organizer_name}
        description="Nama ini tampil sebagai identitas penyelenggara pada event dan halaman publik."
      />
      <div>
        <label htmlFor="description" className="mb-2 block text-sm font-semibold text-[var(--foreground)]">
          Deskripsi Singkat
        </label>
        <textarea
          id="description"
          name="description"
          defaultValue={profile.description ?? ""}
          rows={4}
          maxLength={2000}
          className="w-full rounded-lg border border-[var(--border)] bg-[var(--background)] px-3 py-2 text-sm text-[var(--foreground)] outline-none focus:border-[var(--foreground)]/40"
        />
        <p className="mt-1 text-xs text-[var(--text-secondary)]">Ceritakan komunitas atau lembaga yang Anda wakili (maks. 2000 karakter).</p>
        <FieldError message={state.errors?.description} />
      </div>
      <div className="grid gap-4 sm:grid-cols-2">
        <Input
          label="Email Kontak *"
          name="contact_email"
          type="email"
          defaultValue={profile.contact_email ?? ""}
          required
          error={state.errors?.contact_email}
          description="Dipakai untuk komunikasi verifikasi dan koordinasi event."
        />
        <Input
          label="Nomor Telepon / WhatsApp"
          name="phone"
          defaultValue={profile.phone ?? ""}
          error={state.errors?.phone}
          description="Opsional, membantu peserta menghubungi panitia."
        />
      </div>
      <div>
        <label htmlFor="logo" className="mb-2 block text-sm font-semibold text-[var(--foreground)]">
          Logo Organizer
        </label>
        {profile.logo_url ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img src={profile.logo_url} alt="Logo organizer" className="mb-3 h-16 w-16 rounded-xl border border-[var(--border)] object-cover" />
        ) : null}
        <input
          id="logo"
          name="logo"
          type="file"
          accept="image/jpeg,image/png,image/webp"
          className="block w-full rounded-lg border border-[var(--border)] bg-[var(--background)] px-3 py-2 text-sm"
        />
        <p className="mt-1 text-xs text-[var(--text-secondary)]">Maks. 2 MB, format JPG/PNG/WEBP.</p>
        <FieldError message={state.errors?.logo} />
      </div>

      <div className="flex justify-end">
        <Button type="submit" variant="primary" disabled={pending}>
          {pending ? "Menyimpan…" : "Simpan Profil"}
        </Button>
      </div>
    </form>
  );
}
