"use client";

import { useActionState, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { Button } from "@/components/arc/button/button";
import { Input } from "@/components/arc/input/input";
import { registerEventAction, type RegisterEventState } from "./actions";

export type EventFormField = {
  label: string;
  type: string;
  required?: boolean;
  options?: string[];
};

const initialState: RegisterEventState = {};

export function EventRegisterForm({
  eventId,
  fields,
  participationMode = "individual",
  teamMin = 2,
  teamMax = 5,
  submitLabel,
}: {
  eventId: number;
  fields: EventFormField[];
  participationMode?: "individual" | "team";
  teamMin?: number;
  teamMax?: number;
  submitLabel: string;
}) {
  const [memberCount, setMemberCount] = useState(Math.max(1, teamMin));
  const router = useRouter();
  const action = registerEventAction.bind(null, eventId);
  const [state, formAction, pending] = useActionState(action, initialState);

  useEffect(() => {
    if (state.ok && state.registrationId) {
      router.replace(`/registrations/${state.registrationId}`);
      router.refresh();
    }
  }, [state.ok, state.registrationId, router]);

  return (
    <form action={formAction} className="mt-6 flex flex-col gap-4">
      {participationMode === "team" ? (
        <div className="flex flex-col gap-4 rounded-2xl border border-[var(--border)] bg-[var(--surface-muted)] p-4">
          <p className="text-sm font-semibold text-[var(--foreground)]">Data Tim</p>
          <label className="flex flex-col gap-2 text-sm font-medium text-[var(--foreground)]">
            Nama Tim *
            <input
              name="team_name"
              required
              maxLength={255}
              className="w-full rounded-xl border border-[var(--border-strong)] bg-[var(--surface)] px-3 py-2 text-sm text-[var(--foreground)] outline-none focus:border-[var(--foreground)]"
            />
          </label>
          <p className="text-xs text-[var(--text-secondary)]">Anggota {teamMin}-{teamMax} orang, termasuk ketua tim.</p>
          {Array.from({ length: memberCount }).map((_, index) => (
            <div key={index} className="grid gap-3 sm:grid-cols-2">
              <label className="flex flex-col gap-2 text-sm font-medium text-[var(--foreground)]">
                Nama Anggota {index + 1} *
                <input
                  name={`team_members[${index}][name]`}
                  required
                  maxLength={255}
                  className="w-full rounded-xl border border-[var(--border-strong)] bg-[var(--surface)] px-3 py-2 text-sm text-[var(--foreground)] outline-none focus:border-[var(--foreground)]"
                />
              </label>
              <label className="flex flex-col gap-2 text-sm font-medium text-[var(--foreground)]">
                Email Anggota {index + 1}
                <input
                  name={`team_members[${index}][email]`}
                  type="email"
                  className="w-full rounded-xl border border-[var(--border-strong)] bg-[var(--surface)] px-3 py-2 text-sm text-[var(--foreground)] outline-none focus:border-[var(--foreground)]"
                />
              </label>
            </div>
          ))}
          <div className="flex gap-2">
            <Button type="button" variant="secondary" size="sm" disabled={memberCount >= teamMax} onClick={() => setMemberCount((count) => Math.min(teamMax, count + 1))}>
              Tambah Anggota
            </Button>
            <Button type="button" variant="secondary" size="sm" disabled={memberCount <= Math.max(1, teamMin)} onClick={() => setMemberCount((count) => Math.max(Math.max(1, teamMin), count - 1))}>
              Kurangi Anggota
            </Button>
          </div>
        </div>
      ) : null}

      {fields.map((field, index) => {
        const name = `field_${index}`;
        const label = `${field.label}${field.required ? " *" : ""}`;

        if (field.type === "textarea") {
          return (
            <label key={name} className="flex flex-col gap-2 text-sm font-medium text-[var(--foreground)]">
              {label}
              <textarea
                name={name}
                rows={3}
                required={field.required}
                className="w-full rounded-xl border border-[var(--border-strong)] bg-[var(--surface)] px-3 py-2 text-sm text-[var(--foreground)] outline-none focus:border-[var(--foreground)]"
              />
            </label>
          );
        }

        if (field.type === "file") {
          return (
            <label key={name} className="flex flex-col gap-2 text-sm font-medium text-[var(--foreground)]">
              {label}
              <input
                type="file"
                name={name}
                required={field.required}
                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                className="w-full rounded-xl border border-[var(--border-strong)] bg-[var(--surface)] px-3 py-2 text-sm text-[var(--foreground)]"
              />
              <span className="text-xs font-normal text-[var(--text-secondary)]">PDF/JPG/PNG/DOC/DOCX, maks. 5MB. File disimpan privat.</span>
            </label>
          );
        }

        if (field.type === "select" && field.options?.length) {
          return (
            <label key={name} className="flex flex-col gap-2 text-sm font-medium text-[var(--foreground)]">
              {label}
              <select
                name={name}
                required={field.required}
                className="w-full rounded-xl border border-[var(--border-strong)] bg-[var(--surface)] px-3 py-2 text-sm text-[var(--foreground)] outline-none focus:border-[var(--foreground)]"
              >
                <option value="">Pilih {field.label}</option>
                {field.options.map((option) => (
                  <option key={option} value={option}>{option}</option>
                ))}
              </select>
            </label>
          );
        }

        return (
          <Input
            key={name}
            label={label}
            name={name}
            type={field.type === "number" ? "number" : field.type === "email" ? "email" : field.type === "url" ? "url" : "text"}
            required={field.required}
          />
        );
      })}

      {state.error ? (
        <p role="alert" className="text-sm font-medium text-rose-600">{state.error}</p>
      ) : null}

      <Button type="submit" variant="primary" size="lg" loading={pending} className="mt-2 w-full sm:w-auto">
        {submitLabel}
      </Button>
    </form>
  );
}
