"use client";

import { useActionState, useState } from "react";
import Link from "next/link";
import { AnimatePresence, motion, useReducedMotion } from "motion/react";
import { Button } from "@/components/arc/button/button";
import { Input } from "@/components/arc/input/input";
import type { ExistingScore, FormField, JuryRoleForm } from "./types";
import { saveScoreAction, type ScoreFormState } from "@/app/jury/actions";

const initialState: ScoreFormState = {};
const easeOut: [number, number, number, number] = [0.22, 1, 0.36, 1];

function initialValues(form: JuryRoleForm | null, existing: ExistingScore | null): Record<string, string> {
  const values: Record<string, string> = {};

  form?.criteria_fields.forEach((field, index) => {
    const stored = existing?.criteria_responses?.[field.label];
    let value = "";

    if (stored && typeof stored === "object" && "score" in stored) {
      const score = stored.score;
      value = typeof score === "number" || typeof score === "string" ? String(score) : "";
    } else if (stored !== undefined && stored !== null) {
      value = String(stored);
    }

    values[`criteria_${index}`] = value;
  });

  return values;
}

function scoreFields(form: JuryRoleForm | null): { field: FormField; index: number }[] {
  return (form?.criteria_fields ?? [])
    .map((field, index) => ({ field, index }))
    .filter(({ field }) => field.type === "score");
}

export function ScoreForm({
  registrationId,
  eventId,
  form,
  existingScore,
  mode,
}: {
  registrationId: number;
  eventId: number;
  form: JuryRoleForm | null;
  existingScore: ExistingScore | null;
  mode: "create" | "edit";
}) {
  const [state, formAction, pending] = useActionState(saveScoreAction, initialState);
  const [values, setValues] = useState<Record<string, string>>(() =>
    initialValues(form, existingScore),
  );
  const reduced = useReducedMotion();

  const total = scoreFields(form).reduce(
    (sum, { index }) => sum + (parseFloat(values[`criteria_${index}`] ?? "") || 0),
    0,
  );
  const totalMax = scoreFields(form).reduce(
    (sum, { field }) => sum + Number(field.max ?? 0),
    0,
  );
  const totalText = Number.isInteger(total) ? String(total) : total.toFixed(2);

  return (
    <form action={formAction}>
      <input type="hidden" name="registration_id" value={registrationId} />
      <input type="hidden" name="event_id" value={eventId} />
      <input type="hidden" name="mode" value={mode} />
      {mode === "edit" && existingScore ? (
        <input type="hidden" name="score_id" value={existingScore.id} />
      ) : null}
      {form ? <input type="hidden" name="jury_form_template_id" value={form.id} /> : null}

      {state.message && !state.errors ? (
        <div className="mb-4 rounded-xl border-l-4 border-rose-400 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
          {state.message}
        </div>
      ) : null}

      {form ? (
        <div className="rounded-xl border border-[var(--border)] border-l-4 border-l-indigo-500 bg-[var(--background)] p-5">
          <h3 className="flex items-center gap-2 text-base font-semibold text-[var(--foreground)]">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" className="h-5 w-5 text-indigo-600" aria-hidden="true">
              <path strokeLinecap="round" strokeLinejoin="round" d="M4 19V5m0 14h16M8 16v-5m4 5V8m4 8v-3" />
            </svg>
            Kriteria Penilaian
          </h3>
          {form.name ? <p className="mt-1 text-sm font-semibold text-indigo-700">Form: {form.name}</p> : null}
          <p className="mt-1 text-sm text-[var(--text-secondary)]">
            Isi semua kolom bertanda <span className="text-rose-600">*</span>. Total nilai dijumlahkan otomatis.
          </p>

          <div className="mt-5 space-y-4">
            {form.criteria_fields.map((field, index) => {
              const name = `criteria_${index}`;
              const error = state.errors?.[name];
              const label =
                field.type === "score"
                  ? `${field.label}${field.required ? " *" : ""} (Max: ${field.max ?? 100} poin)`
                  : `${field.label}${field.required ? " *" : ""}`;

              if (field.type === "textarea") {
                return (
                  <div key={name}>
                    <label htmlFor={name} className="block text-sm font-medium text-[var(--foreground)]">
                      {label}
                    </label>
                    {field.description ? (
                      <p className="mt-1 text-sm text-[var(--text-muted)]">{field.description}</p>
                    ) : null}
                    <textarea
                      id={name}
                      name={name}
                      rows={3}
                      required={field.required}
                      value={values[name] ?? ""}
                      onChange={(event) =>
                        setValues((current) => ({ ...current, [name]: event.target.value }))
                      }
                      className="mt-1.5 w-full rounded-lg border border-[var(--border)] bg-[var(--background)] px-3 py-2 text-sm text-[var(--foreground)] outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                    />
                    {error ? (
                      <p className="mt-1 text-xs font-medium text-rose-600" role="alert">
                        {error}
                      </p>
                    ) : null}
                  </div>
                );
              }

              return (
                <Input
                  key={name}
                  id={name}
                  name={name}
                  label={label}
                  description={field.description}
                  error={error}
                  type={field.type === "score" ? "number" : "text"}
                  min={field.type === "score" ? 0 : undefined}
                  max={field.type === "score" ? field.max ?? 100 : undefined}
                  step={field.type === "score" ? 0.5 : undefined}
                  required={field.required}
                  value={values[name] ?? ""}
                  onChange={(event) =>
                    setValues((current) => ({ ...current, [name]: event.target.value }))
                  }
                />
              );
            })}
          </div>

          {totalMax > 0 ? (
            <div className="mt-4 rounded-xl bg-[var(--surface-muted)] px-4 py-3 ring-1 ring-[var(--border)]">
              <div className="flex flex-wrap items-center justify-between gap-2">
                <strong className="text-[var(--foreground)]">
                  Total Nilai:{" "}
                  <span className="relative inline-grid overflow-hidden align-bottom text-indigo-700 tabular-nums">
                    <AnimatePresence mode="popLayout" initial={false}>
                      <motion.span
                        key={totalText}
                        initial={reduced ? false : { y: "0.5em", opacity: 0 }}
                        animate={{ y: 0, opacity: 1 }}
                        exit={reduced ? { opacity: 0 } : { y: "-0.5em", opacity: 0 }}
                        transition={reduced ? { duration: 0 } : { duration: 0.25, ease: easeOut }}
                      >
                        {totalText}
                      </motion.span>
                    </AnimatePresence>
                  </span>
                </strong>
                <span className="text-xs font-medium text-[var(--text-muted)]">Maksimal {totalMax} poin</span>
              </div>
              <p className="mt-1 text-xs text-[var(--text-muted)]">
                Nilai akhir dihitung otomatis dari jumlah kriteria. Angka di atas berubah saat Anda mengisi.
              </p>
              {state.errors?.score ? (
                <p className="mt-1 text-xs font-medium text-rose-600" role="alert">
                  {state.errors.score}
                </p>
              ) : null}
            </div>
          ) : null}
        </div>
      ) : (
        <Input
          id="score"
          name="score"
          label="Nilai (0-100) *"
          description="Berikan nilai antara 0 sampai 100."
          error={state.errors?.score}
          type="number"
          min={0}
          max={100}
          step={0.01}
          required
          defaultValue={existingScore ? String(existingScore.score) : undefined}
        />
      )}

      {form ? <input type="hidden" name="score" value="0" /> : null}

      <div className="mt-5 space-y-5">
        <div>
          <label htmlFor="public_feedback" className="block text-sm font-medium text-[var(--foreground)]">
            Feedback Publik
          </label>
          <textarea
            id="public_feedback"
            name="public_feedback"
            rows={4}
            placeholder="Feedback yang dapat dilihat oleh peserta..."
            defaultValue={existingScore?.public_feedback ?? ""}
            className="mt-1.5 w-full rounded-lg border border-[var(--border)] bg-[var(--background)] px-3 py-2 text-sm text-[var(--foreground)] outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
          />
          <p className="mt-1 text-xs text-[var(--text-muted)]">
            Feedback ini akan terlihat oleh peserta dan publik.
          </p>
        </div>

        <div>
          <label htmlFor="private_evaluation" className="block text-sm font-medium text-[var(--foreground)]">
            Evaluasi Private
          </label>
          <textarea
            id="private_evaluation"
            name="private_evaluation"
            rows={4}
            placeholder="Evaluasi internal untuk admin..."
            defaultValue={existingScore?.private_evaluation ?? ""}
            className="mt-1.5 w-full rounded-lg border border-[var(--border)] bg-[var(--background)] px-3 py-2 text-sm text-[var(--foreground)] outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
          />
          <p className="mt-1 text-xs text-[var(--text-muted)]">Evaluasi ini hanya terlihat oleh admin dan juri.</p>
        </div>
      </div>

      <div className="mt-5 flex flex-wrap gap-2">
        <Button type="submit" variant="primary" loading={pending}>
          {mode === "edit" ? "Update Penilaian" : "Simpan Penilaian"}
        </Button>
        <Link
          href={`/jury/events/${eventId}/participants`}
          className="inline-flex h-10 items-center justify-center rounded-lg border border-[var(--border)] px-4 text-sm font-semibold text-[var(--foreground)] transition hover:bg-[var(--surface-muted)]"
        >
          Batal
        </Link>
      </div>
      <p className="mt-3 text-xs text-[var(--text-muted)]">
        Setelah disimpan, sistem otomatis membawa Anda ke peserta berikutnya yang belum dinilai.
      </p>
    </form>
  );
}
