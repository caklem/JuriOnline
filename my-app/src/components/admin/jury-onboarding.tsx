"use client";

import { useActionState, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { Button } from "@/components/arc/button/button";
import { Input } from "@/components/arc/input/input";
import { Badge } from "@/components/kit/ui";
import {
  assignJuryAction,
  createJuryAccountAction,
  type Credentials,
} from "@/app/admin/events/[id]/jury-assignments/actions";
import type { ActionResult } from "@/app/admin/_lib/api-action";
import type { JuryOption } from "./types";

type State = ActionResult & { credentials?: Credentials };
const initialState: State = {};

const SELECT =
  "w-full rounded-xl border border-[var(--border-strong)] bg-[var(--surface)] px-3 py-2 text-sm text-[var(--foreground)] outline-none focus:border-[var(--foreground)]";

function CopyButton({ value, label = "Salin" }: { value: string; label?: string }) {
  const [copied, setCopied] = useState(false);

  return (
    <button
      type="button"
      onClick={async () => {
        try {
          await navigator.clipboard.writeText(value);
          setCopied(true);
          window.setTimeout(() => setCopied(false), 1500);
        } catch {
          setCopied(false);
        }
      }}
      className="rounded-md border border-emerald-200 bg-white px-2 py-1 text-[11px] font-semibold text-emerald-800 transition hover:bg-emerald-100"
    >
      {copied ? "Tersalin" : label}
    </button>
  );
}

function CredentialsPanel({ credentials, message }: { credentials?: Credentials; message?: string }) {
  if (!credentials) {
    return message ? <p role="status" className="text-sm font-medium text-emerald-600">{message}</p> : null;
  }

  const all = `Nama: ${credentials.name}\nEmail: ${credentials.email}\nPassword: ${credentials.code}`;

  return (
    <div className="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-950">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <p className="font-semibold">{message ?? "Akun juri berhasil dibuat."}</p>
        <CopyButton value={all} label="Salin Semua" />
      </div>
      <dl className="mt-3 grid gap-2 sm:grid-cols-2">
        <div className="rounded-lg border border-emerald-200 bg-white/70 p-2.5">
          <dt className="text-[10px] font-semibold uppercase tracking-wide text-emerald-700">Email</dt>
          <dd className="mt-0.5 flex items-center justify-between gap-2">
            <span className="break-all">{credentials.email}</span>
            <CopyButton value={credentials.email} />
          </dd>
        </div>
        <div className="rounded-lg border border-emerald-200 bg-white/70 p-2.5">
          <dt className="text-[10px] font-semibold uppercase tracking-wide text-emerald-700">Password</dt>
          <dd className="mt-0.5 flex items-center justify-between gap-2">
            <span className="break-all font-mono">{credentials.code}</span>
            <CopyButton value={credentials.code} />
          </dd>
        </div>
      </dl>
      <p className="mt-2 text-xs text-emerald-800">
        Simpan password sekarang — password tidak dapat dilihat lagi. Jika lupa, gunakan tombol Reset Password pada daftar juri di bawah.
      </p>
    </div>
  );
}

export function JuryOnboarding({
  eventId,
  templates,
  juries,
}: {
  eventId: number;
  templates: { id: number; name: string }[];
  juries: JuryOption[];
}) {
  const [tab, setTab] = useState<"new" | "existing">("new");
  const [query, setQuery] = useState("");
  const [selected, setSelected] = useState<number[]>([]);

  return (
    <div className="flex flex-col gap-4">
      <div className="inline-flex w-full rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] p-1 sm:w-auto">
        <button
          type="button"
          onClick={() => setTab("new")}
          className={`flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition ${tab === "new" ? "bg-[var(--background)] text-[var(--foreground)] shadow-sm" : "text-[var(--text-secondary)]"}`}
        >
          Buat Akun Juri Baru
        </button>
        <button
          type="button"
          onClick={() => setTab("existing")}
          className={`flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition ${tab === "existing" ? "bg-[var(--background)] text-[var(--foreground)] shadow-sm" : "text-[var(--text-secondary)]"}`}
        >
          Tambah Akun Juri yang Sudah Ada
        </button>
      </div>

      {tab === "new" ? (
        <NewJuryAccount eventId={eventId} templates={templates} />
      ) : (
        <ExistingJuryAssignment
          eventId={eventId}
          templates={templates}
          juries={juries}
          query={query}
          setQuery={setQuery}
          selected={selected}
          setSelected={setSelected}
        />
      )}
    </div>
  );
}

function NewJuryAccount({
  eventId,
  templates,
}: {
  eventId: number;
  templates: { id: number; name: string }[];
}) {
  const router = useRouter();
  const [state, action, pending] = useActionState(createJuryAccountAction.bind(null, eventId), initialState);

  useEffect(() => {
    if (state.credentials) router.refresh();
  }, [state.credentials, router]);

  return (
    <form action={action} className="flex flex-col gap-4">
      <div className="grid gap-4 sm:grid-cols-2">
        <Input label="Nama Lengkap Juri *" name="name" placeholder="Nama lengkap juri" required />
        <Input label="Email Juri *" name="email" type="email" placeholder="juri@email.com" required />
        <Input
          label="Password (opsional)"
          name="password"
          type="text"
          placeholder="Kosongkan untuk dibuat otomatis"
          description="Minimal 8 karakter. Bisa diubah lagi kapan saja melalui Reset Password."
        />
        <label className="flex flex-col gap-2 text-sm font-medium text-[var(--foreground)]">
          Assign ke Form Penilaian *
          <select name="jury_form_template_id" required className={SELECT}>
            {templates.map((template) => (
              <option key={template.id} value={template.id}>{template.name}</option>
            ))}
          </select>
        </label>
        <Input label="Posisi Juri (opsional)" name="jury_role" placeholder="Contoh: Juri Vokal" />
      </div>

      {state.error ? <p role="alert" className="rounded-lg bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700">{state.error}</p> : null}
      <CredentialsPanel credentials={state.credentials} message={state.message} />

      <div className="flex flex-wrap items-center gap-3">
        <Button type="submit" variant="primary" loading={pending}>Buat Akun & Kirim Undangan</Button>
        <p className="text-xs text-[var(--text-secondary)]">Undangan juri langsung dikirim setelah akun dibuat.</p>
      </div>
    </form>
  );
}

function ExistingJuryAssignment({
  eventId,
  templates,
  juries,
  query,
  setQuery,
  selected,
  setSelected,
}: {
  eventId: number;
  templates: { id: number; name: string }[];
  juries: JuryOption[];
  query: string;
  setQuery: (value: string) => void;
  selected: number[];
  setSelected: (ids: number[]) => void;
}) {
  const router = useRouter();
  const [state, action, pending] = useActionState(assignJuryAction.bind(null, eventId), initialState);
  const [guard, setGuard] = useState<string | null>(null);

  useEffect(() => {
    if (state.ok) {
      router.refresh();
      setSelected([]);
    }
  }, [state.ok, router, setSelected]);

  const filtered = juries.filter((jury) => {
    const q = query.trim().toLowerCase();

    if (!q) return true;

    return `${jury.name ?? ""} ${jury.email ?? ""}`.toLowerCase().includes(q);
  });

  const toggle = (id: number, active: boolean) => {
    if (!active) return;
    setGuard(null);
    setSelected(selected.includes(id) ? selected.filter((value) => value !== id) : [...selected, id]);
  };

  return (
    <form
      action={action}
      className="flex flex-col gap-4"
      onSubmit={(event) => {
        if (selected.length === 0) {
          event.preventDefault();
          setGuard("Pilih minimal satu akun juri.");
        }
      }}
    >
      <div className="grid gap-4 sm:grid-cols-2">
        <label className="flex flex-col gap-2 text-sm font-medium text-[var(--foreground)]">
          Assign ke Form Penilaian *
          <select name="jury_form_template_id" required className={SELECT}>
            {templates.map((template) => (
              <option key={template.id} value={template.id}>{template.name}</option>
            ))}
          </select>
        </label>
        <Input label="Posisi Juri (opsional)" name="jury_role" placeholder="Contoh: Juri Vokal" />
      </div>

      <div>
        <div className="flex flex-wrap items-center justify-between gap-2">
          <p className="text-sm font-medium text-[var(--foreground)]">Pilih Akun Juri</p>
          <Badge tone="info">{selected.length} dipilih</Badge>
        </div>
        <div className="mt-2 flex items-center gap-2 rounded-lg border border-[var(--border)] bg-[var(--background)] px-3 py-2">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className="h-4 w-4 text-[var(--text-muted)]" aria-hidden="true">
            <circle cx="11" cy="11" r="7" />
            <path strokeLinecap="round" d="m20 20-3.5-3.5" />
          </svg>
          <input
            type="search"
            value={query}
            onChange={(event) => setQuery(event.target.value)}
            placeholder="Cari nama atau email juri…"
            className="min-w-0 flex-1 bg-transparent text-sm text-[var(--foreground)] outline-none placeholder:text-[var(--text-muted)]"
          />
        </div>
        <div className="mt-2 max-h-[320px] overflow-y-auto rounded-xl border border-[var(--border)] bg-[var(--background)] p-2">
          {filtered.length === 0 ? (
            <p className="px-3 py-6 text-center text-sm text-[var(--text-secondary)]">
              {juries.length === 0 ? "Belum ada akun juri. Buat akun baru terlebih dahulu." : "Tidak ada juri yang cocok dengan pencarian."}
            </p>
          ) : (
            <div className="grid gap-1.5 sm:grid-cols-2">
              {filtered.map((jury) => (
                <label
                  key={jury.id}
                  className={`flex items-center gap-2 rounded-lg border px-3 py-2 text-sm ${jury.is_active ? "border-[var(--border)] bg-[var(--background)]" : "border-[var(--border)] bg-[var(--surface-muted)] opacity-60"}`}
                >
                  <input
                    type="checkbox"
                    name="jury_ids"
                    value={jury.id}
                    checked={selected.includes(jury.id)}
                    disabled={!jury.is_active}
                    onChange={() => toggle(jury.id, jury.is_active)}
                    className="h-4 w-4 shrink-0 accent-[var(--foreground)]"
                  />
                  <span className="min-w-0">
                    <span className="block truncate font-medium text-[var(--foreground)]">{jury.name ?? "Juri"}</span>
                    <span className="block truncate text-xs text-[var(--text-secondary)]">{jury.email ?? "-"}</span>
                  </span>
                  {!jury.is_active ? <span className="ml-auto text-[10px] font-semibold text-rose-600">Nonaktif</span> : null}
                </label>
              ))}
            </div>
          )}
        </div>
      </div>

      {guard ? <p role="alert" className="rounded-lg bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700">{guard}</p> : null}
      {state.error ? <p role="alert" className="rounded-lg bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700">{state.error}</p> : null}
      {state.ok && state.message ? <p role="status" className="rounded-lg bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700">{state.message}</p> : null}

      <div className="flex justify-end">
        <Button type="submit" variant="primary" loading={pending}>Tambah Juri ke Form</Button>
      </div>
    </form>
  );
}
