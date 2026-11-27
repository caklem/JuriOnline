"use client";

import { useActionState } from "react";
import { Button } from "@/components/arc/button/button";
import { Input } from "@/components/arc/input/input";
import { Card, CardHeader } from "@/components/kit/ui";
import { updateJuryProfileAction, type ActionState } from "./actions";

type JuryProfile = {
  phone: string | null;
  organization: string | null;
  expertise: string | null;
  bio: string | null;
  whatsapp_number: string | null;
  whatsapp_consent: boolean;
};

export function JuryProfileForm({ profile }: { profile: JuryProfile }) {
  const [state, action, pending] = useActionState(updateJuryProfileAction, {} as ActionState);

  return (
    <Card>
      <CardHeader
        title="Profil Juri"
        description="Data ini membantu panitia memetakan bidang penilaian dan menghubungi Anda. Nomor WhatsApp hanya ditampilkan jika Anda memberi izin."
      />
      <form action={action} className="grid gap-4 p-5 sm:grid-cols-2">
        {state.error ? <p className="sm:col-span-2 text-sm font-medium text-rose-600">{state.error}</p> : null}
        {state.message ? <p className="sm:col-span-2 text-sm font-medium text-emerald-600">{state.message}</p> : null}
        <Input label="Nomor Telepon" name="phone" defaultValue={profile.phone ?? ""} />
        <Input label="Institusi / Organisasi" name="organization" defaultValue={profile.organization ?? ""} />
        <Input label="Bidang Keahlian" name="expertise" defaultValue={profile.expertise ?? ""} description="Contoh: Desain, Musik, Riset." />
        <Input label="Nomor WhatsApp" name="whatsapp_number" defaultValue={profile.whatsapp_number ?? ""} description="Opsional." />
        <div className="sm:col-span-2">
          <label className="mb-2 block text-sm font-semibold text-[var(--foreground)]" htmlFor="bio">Bio Singkat</label>
          <textarea
            id="bio"
            name="bio"
            rows={3}
            maxLength={2000}
            defaultValue={profile.bio ?? ""}
            className="w-full rounded-lg border border-[var(--border)] bg-[var(--background)] px-3 py-2 text-sm outline-none focus:border-[var(--foreground)]/40"
          />
        </div>
        <label className="sm:col-span-2 flex items-start gap-2 text-sm text-[var(--text-secondary)]">
          <input type="checkbox" name="whatsapp_consent" value="1" defaultChecked={profile.whatsapp_consent} className="mt-0.5" />
          Saya setuju nomor WhatsApp ditampilkan kepada panitia event untuk koordinasi penilaian.
        </label>
        <div className="sm:col-span-2">
          <Button type="submit" variant="primary" loading={pending}>Simpan Profil Juri</Button>
        </div>
      </form>
    </Card>
  );
}
