"use client";

import { useState, useTransition } from "react";
import { useRouter } from "next/navigation";
import { AnimatePresence, motion, useReducedMotion } from "motion/react";
import { GripVertical } from "lucide-react";
import { Button } from "@/components/arc/button/button";
import { Input } from "@/components/arc/input/input";

export type BuilderField = {
  label: string;
  type: string;
  required: boolean;
  max?: number;
  description?: string;
  options?: string[];
};

const inputClass =
  "w-full rounded-xl border border-[var(--border-strong)] bg-[var(--surface)] px-3 py-2 text-sm text-[var(--foreground)] outline-none focus:border-[var(--foreground)]";

export function FormBuilder({
  mode,
  initialFields,
  typeOptions,
  onSave,
  onSaveTemplate,
  extra,
}: {
  mode: "registration" | "jury";
  initialFields: BuilderField[];
  typeOptions: { value: string; label: string }[];
  onSave: (payload: { fields: BuilderField[]; name?: string; useDefaultScore?: boolean; templateId?: number | null }) => Promise<{ ok?: boolean; error?: string; message?: string; formId?: number }>;
  onSaveTemplate?: (payload: { name: string; description: string; fields: BuilderField[]; useDefaultScore: boolean }) => Promise<{ error?: string; message?: string }>;
  extra?: {
    forms?: { id: number; name: string }[];
    formOptions?: { id: number; name: string; fields: BuilderField[]; useDefaultScore: boolean }[];
    selectedFormId?: number | null;
    formName?: string;
    useDefaultScore?: boolean;
    presets?: { key: string; name: string; fields: BuilderField[] }[];
    templates?: { key: string; name: string; description?: string | null; fields: BuilderField[]; useDefaultScore: boolean }[];
  };
}) {
  const reduced = useReducedMotion();
  const router = useRouter();
  const [fields, setFields] = useState<BuilderField[]>(initialFields);
  const [name, setName] = useState(extra?.formName ?? "");
  const [useDefaultScore, setUseDefaultScore] = useState(extra?.useDefaultScore ?? true);
  const [templateId, setTemplateId] = useState<number | null>(extra?.selectedFormId ?? null);
  const [state, setState] = useState<{ error?: string; message?: string }>({});
  const [pending, startTransition] = useTransition();
  const [dragIndex, setDragIndex] = useState<number | null>(null);
  const [preview, setPreview] = useState<{ name: string; fields: BuilderField[] } | null>(null);
  const [templateOpen, setTemplateOpen] = useState(false);
  const [templateName, setTemplateName] = useState("");
  const [templateDescription, setTemplateDescription] = useState("");
  const [templateState, setTemplateState] = useState<{ error?: string; message?: string }>({});
  const [templatePending, setTemplatePending] = useState(false);
  const [templateQuery, setTemplateQuery] = useState("");

  const update = (index: number, patch: Partial<BuilderField>) => {
    setFields((current) => current.map((field, i) => (i === index ? { ...field, ...patch } : field)));
    setState({});
  };

  const addField = () => {
    setFields((current) => [
      ...current,
      mode === "jury"
        ? { label: "", type: "score", required: true, max: 100, description: "" }
        : { label: "", type: "text", required: true, options: [] },
    ]);
  };

  const removeField = (index: number) => {
    setFields((current) => current.filter((_, i) => i !== index));
    setState({});
  };

  const moveField = (from: number, to: number) => {
    if (from === to) return;
    setFields((current) => {
      const next = [...current];
      const [moved] = next.splice(from, 1);
      next.splice(to, 0, moved);
      return next;
    });
    setState({});
  };

  const save = () => {
    startTransition(async () => {
      const result = await onSave({ fields, name, useDefaultScore, templateId });
      setState(result);

      if (result.ok && result.formId) {
        setTemplateId(result.formId);
        router.refresh();
      }
    });
  };

  const filteredTemplates = (extra?.templates ?? []).filter((template) => {
    const query = templateQuery.trim().toLowerCase();

    if (!query) return true;

    return `${template.name} ${template.description ?? ""}`.toLowerCase().includes(query);
  });

  const loadForm = (value: string) => {
    if (value === "") {
      setTemplateId(null);
      setName("");
      setUseDefaultScore(true);
      setFields([]);
      setState({});
      return;
    }

    const id = Number(value);
    const form = extra?.formOptions?.find((item) => item.id === id);

    if (!form) {
      setTemplateId(id);
      return;
    }

    setTemplateId(id);
    setName(form.name);
    setUseDefaultScore(form.useDefaultScore);
    setFields(form.fields.map((field) => ({ ...field, max: field.max ?? 100, description: field.description ?? "", options: field.options ?? [] })));
    setState({});
  };

  const applyTemplate = (template: { name: string; fields: BuilderField[]; useDefaultScore: boolean }) => {
    setTemplateId(null);
    setName(template.name);
    setUseDefaultScore(template.useDefaultScore);
    setFields(template.fields.map((field) => ({
      ...field,
      required: field.required ?? true,
      max: field.max ?? 100,
      description: field.description ?? "",
      options: field.options ?? [],
    })));
    setState({});
  };

  const saveTemplate = async () => {
    if (!onSaveTemplate) return;
    setTemplateState({});
    setTemplatePending(true);

    const result = await onSaveTemplate({
      name: templateName.trim(),
      description: templateDescription.trim(),
      fields,
      useDefaultScore,
    });

    setTemplateState(result);
    setTemplatePending(false);

    if (!result.error) {
      setState({ message: result.message ?? "Template berhasil disimpan." });
      setTemplateOpen(false);
      setTemplateName("");
      setTemplateDescription("");
      router.refresh();
    }
  };

  return (
    <div className="flex flex-col gap-4">
      {mode === "jury" ? (
        <div className="grid gap-4 sm:grid-cols-2">
          <Input label="Nama Form" name="form_name" value={name} onChange={(event) => setName(event.target.value)} required />
          {extra?.forms && extra.forms.length > 0 ? (
            <label className="flex flex-col gap-2 text-sm font-medium text-[var(--foreground)]">
              Form yang diedit
              <select
                className={inputClass}
                value={templateId ?? ""}
                onChange={(event) => loadForm(event.target.value)}
              >
                <option value="">Form baru</option>
                {extra.forms.map((form) => (
                  <option key={form.id} value={form.id}>{form.name}</option>
                ))}
              </select>
            </label>
          ) : null}
          <label className="flex items-center gap-2 text-sm text-[var(--foreground)]">
            <input type="checkbox" checked={useDefaultScore} onChange={(event) => setUseDefaultScore(event.target.checked)} />
            Nilai akhir dijumlahkan otomatis
          </label>
        </div>
      ) : null}

      {mode === "jury" && extra?.templates?.length ? (
        <details className="rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] p-4" open={fields.length === 0 || templateQuery.trim().length > 0}>
          <summary className="cursor-pointer text-sm font-semibold text-[var(--foreground)]">
            Template Penilaian ({extra.templates.length})
          </summary>
          <p className="mt-1 text-xs text-[var(--text-secondary)]">
            Gunakan template bawaan atau template custom untuk mengisi kriteria. Form yang sudah ada tidak ditimpa sampai Anda menekan Simpan Form.
          </p>
          <div className="mt-3">
            <label className="sr-only" htmlFor="template-search">Cari template penilaian</label>
            <div className="flex items-center gap-2 rounded-lg border border-[var(--border)] bg-[var(--background)] px-3 py-2">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className="h-4 w-4 shrink-0 text-[var(--text-muted)]" aria-hidden="true">
                <circle cx="11" cy="11" r="7" />
                <path strokeLinecap="round" d="m20 20-3.5-3.5" />
              </svg>
              <input
                id="template-search"
                type="search"
                value={templateQuery}
                onChange={(event) => setTemplateQuery(event.target.value)}
                placeholder="Cari template berdasarkan nama atau deskripsi…"
                className="min-w-0 flex-1 bg-transparent text-sm text-[var(--foreground)] outline-none placeholder:text-[var(--text-muted)]"
              />
              {templateQuery ? (
                <button type="button" onClick={() => setTemplateQuery("")} className="text-xs font-semibold text-[var(--text-secondary)] underline underline-offset-2">
                  Reset
                </button>
              ) : null}
            </div>
            <p className="mt-1.5 text-[11px] text-[var(--text-secondary)]">
              {filteredTemplates.length} dari {extra.templates.length} template ditampilkan.
            </p>
          </div>
          {filteredTemplates.length === 0 ? (
            <p className="mt-3 rounded-lg border border-dashed border-[var(--border)] bg-[var(--background)] px-4 py-5 text-center text-sm text-[var(--text-secondary)]">
              Tidak ada template yang cocok dengan pencarian.
            </p>
          ) : (
          <div className="mt-3 grid gap-2 sm:grid-cols-2">
            {filteredTemplates.map((template) => (
              <div key={template.key} className="rounded-lg border border-[var(--border)] bg-[var(--background)] p-3">
                <div className="flex items-start justify-between gap-2">
                  <div className="min-w-0">
                    <p className="truncate text-sm font-semibold text-[var(--foreground)]">{template.name}</p>
                    <p className="mt-0.5 text-[11px] text-[var(--text-secondary)]">
                      {template.fields.filter((field) => field.type === "score").length} kriteria skor
                    </p>
                  </div>
                </div>
                <div className="mt-2 flex flex-wrap gap-1.5">
                  <Button type="button" variant="secondary" size="sm" onClick={() => applyTemplate(template)}>Gunakan</Button>
                  <Button type="button" variant="secondary" size="sm" onClick={() => setPreview({ name: template.name, fields: template.fields })}>Preview</Button>
                </div>
              </div>
            ))}
          </div>
          )}
        </details>
      ) : null}

      {mode === "registration" && extra?.presets?.length ? (
        <div className="rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] p-4">
          <p className="text-sm font-semibold text-[var(--foreground)]">Gunakan Template</p>
          <p className="mt-1 text-xs text-[var(--text-secondary)]">Pilih template untuk mengisi field secara otomatis, lalu sesuaikan bila perlu.</p>
          <div className="mt-3 flex flex-wrap gap-2">
            {extra.presets.map((preset) => (
              <Button
                key={preset.key}
                type="button"
                variant="secondary"
                size="sm"
                onClick={() => {
                  setFields(preset.fields.map((field) => ({ ...field, required: field.required ?? true, options: field.options ?? [] })));
                  setState({});
                }}
              >
                {preset.name}
              </Button>
            ))}
          </div>
        </div>
      ) : null}

      <div className="flex flex-col gap-3">
        <AnimatePresence initial={false}>
          {fields.map((field, index) => (
            <motion.div
              key={index}
              layout
              draggable
              onDragStart={() => setDragIndex(index)}
              onDragOver={(event) => event.preventDefault()}
              onDrop={() => {
                if (dragIndex !== null) moveField(dragIndex, index);
                setDragIndex(null);
              }}
              onDragEnd={() => setDragIndex(null)}
              initial={reduced ? false : { opacity: 0, y: 6 }}
              animate={{ opacity: 1, y: 0 }}
              exit={reduced ? undefined : { opacity: 0, y: -6 }}
              className={
                dragIndex === index
                  ? "rounded-xl border border-[var(--border)] bg-[var(--surface)] p-4 opacity-60 ring-2 ring-indigo-200"
                  : "rounded-xl border border-[var(--border)] bg-[var(--surface)] p-4 transition hover:border-[var(--border-strong)]"
              }
            >
              <div className="mb-3 flex items-center justify-between gap-2">
                <div className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-[var(--text-muted)]">
                  <GripVertical size={16} strokeWidth={1.8} className="cursor-grab text-[var(--text-muted)]" aria-hidden="true" />
                  {mode === "jury" ? "Kriteria" : "Field"} {index + 1}
                </div>
                <span className="text-[11px] text-[var(--text-muted)]">Tarik untuk mengubah urutan</span>
              </div>
              <div className="grid gap-3 sm:grid-cols-2">
                <label className="flex flex-col gap-2 text-sm font-medium text-[var(--foreground)]">
                  Label {mode === "jury" ? "Kriteria" : "Field"}
                  <input className={inputClass} value={field.label} onChange={(event) => update(index, { label: event.target.value })} />
                </label>
                <label className="flex flex-col gap-2 text-sm font-medium text-[var(--foreground)]">
                  Tipe
                  <select className={inputClass} value={field.type} onChange={(event) => update(index, { type: event.target.value })}>
                    {typeOptions.map((option) => (
                      <option key={option.value} value={option.value}>{option.label}</option>
                    ))}
                  </select>
                </label>
                {field.type === "score" ? (
                  <label className="flex flex-col gap-2 text-sm font-medium text-[var(--foreground)]">
                    Nilai Maksimal
                    <input
                      type="number"
                      min={1}
                      max={100}
                      className={inputClass}
                      value={field.max ?? 100}
                      onChange={(event) => update(index, { max: Number(event.target.value) })}
                    />
                  </label>
                ) : null}
                <label className="flex items-center gap-2 text-sm text-[var(--foreground)] sm:col-span-2">
                  <input type="checkbox" checked={field.required} onChange={(event) => update(index, { required: event.target.checked })} />
                  Wajib diisi
                </label>
              </div>
              <div className="mt-3 flex justify-end">
                <Button type="button" variant="danger" size="sm" onClick={() => removeField(index)}>
                  Hapus
                </Button>
              </div>
            </motion.div>
          ))}
        </AnimatePresence>
      </div>

      {state.error ? <p role="alert" className="text-sm font-medium text-rose-600">{state.error}</p> : null}
      {state.message ? <p role="status" className="text-sm font-medium text-emerald-600">{state.message}</p> : null}

      <div className="flex flex-wrap gap-2">
        <Button type="button" variant="secondary" onClick={addField}>
          Tambah {mode === "jury" ? "Kriteria" : "Field"}
        </Button>
        {mode === "jury" ? (
          <>
            <Button type="button" variant="secondary" onClick={() => setPreview({ name: name || "Form Penilaian", fields })}>
              Lihat Preview
            </Button>
            {onSaveTemplate ? (
              <Button type="button" variant="secondary" onClick={() => { setTemplateName(name); setTemplateOpen(true); }}>
                Simpan sebagai Template
              </Button>
            ) : null}
          </>
        ) : null}
        <Button type="button" variant="primary" loading={pending} onClick={save}>
          Simpan Form
        </Button>
      </div>

      {preview ? (
        <div className="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-slate-950/50 p-4 sm:items-center" role="dialog" aria-modal="true" aria-label="Preview form penilaian">
          <div className="w-full max-w-3xl rounded-2xl bg-white shadow-2xl">
            <div className="flex items-start justify-between gap-4 border-b border-slate-200 p-5">
              <div>
                <p className="text-xs font-semibold uppercase tracking-wide text-indigo-600">Preview Form Penilaian</p>
                <h2 className="mt-1 text-xl font-semibold text-slate-950">{preview.name}</h2>
                <p className="mt-1 text-xs text-slate-500">Tampilan ini sama dengan yang akan dilihat juri. Tidak ada nilai yang tersimpan.</p>
              </div>
              <Button type="button" variant="secondary" size="sm" onClick={() => setPreview(null)}>Tutup</Button>
            </div>
            <div className="max-h-[70vh] overflow-y-auto p-5">
              <PreviewFields fields={preview.fields} />
            </div>
            <div className="flex items-center justify-between gap-3 border-t border-slate-200 p-5">
              <p className="text-xs text-slate-500">Total nilai maksimal: <strong className="text-slate-900">{preview.fields.filter((field) => field.type === "score").reduce((total, field) => total + (field.max ?? 0), 0)}</strong></p>
              <Button type="button" variant="primary" onClick={() => setPreview(null)}>Tutup Preview</Button>
            </div>
          </div>
        </div>
      ) : null}

      {templateOpen ? (
        <div className="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-slate-950/50 p-4 sm:items-center" role="dialog" aria-modal="true" aria-label="Simpan sebagai template">
          <div className="w-full max-w-lg rounded-2xl bg-white p-5 shadow-2xl">
            <h2 className="text-lg font-semibold text-slate-950">Simpan sebagai Template Custom</h2>
            <p className="mt-1 text-xs text-slate-500">Template dapat digunakan kembali pada event lain oleh akun ini.</p>
            <div className="mt-4 flex flex-col gap-3">
              {templateState.error ? <p className="text-xs text-rose-600">{templateState.error}</p> : null}
              {templateState.message ? <p className="text-xs text-emerald-700">{templateState.message}</p> : null}
              <label className="flex flex-col gap-1 text-sm font-medium text-slate-800">
                Nama Template
                <input className={inputClass} value={templateName} onChange={(event) => setTemplateName(event.target.value)} maxLength={255} placeholder="Contoh: Penilaian Vokal Daerah" />
              </label>
              <label className="flex flex-col gap-1 text-sm font-medium text-slate-800">
                Deskripsi
                <textarea className={inputClass} rows={3} value={templateDescription} onChange={(event) => setTemplateDescription(event.target.value)} maxLength={1000} placeholder="Opsional" />
              </label>
              <p className="text-xs text-slate-500">{fields.length} kriteria akan disimpan.</p>
            </div>
            <div className="mt-5 flex flex-wrap justify-end gap-2">
              <Button type="button" variant="secondary" onClick={() => setTemplateOpen(false)}>Batal</Button>
              <Button type="button" variant="primary" loading={templatePending} disabled={!templateName.trim() || fields.length === 0} onClick={saveTemplate}>
                Simpan Template
              </Button>
            </div>
          </div>
        </div>
      ) : null}
    </div>
  );
}

function PreviewFields({ fields }: { fields: BuilderField[] }) {
  if (fields.length === 0) {
    return <p className="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">Belum ada kriteria untuk dipreview.</p>;
  }

  return (
    <div className="flex flex-col gap-5">
      {fields.map((field, index) => (
        <div key={`${field.label}-${index}`} className="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
          <label className="flex flex-col gap-2 text-sm font-semibold text-slate-800">
            <span>
              {index + 1}. {field.label || "Kriteria tanpa nama"} {field.required ? <span className="text-rose-500">*</span> : null}
              {field.type === "score" ? <span className="ml-2 text-xs font-normal text-slate-500">(0-{field.max ?? 100})</span> : null}
            </span>
            {field.description ? <span className="text-xs font-normal text-slate-500">{field.description}</span> : null}
            {field.type === "textarea" ? (
              <textarea disabled rows={3} className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500" />
            ) : field.type === "select" ? (
              <select disabled className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500">
                <option>{(field.options ?? []).join(" / ") || "Pilihan"}</option>
              </select>
            ) : (
              <input disabled type={field.type === "score" ? "number" : "text"} placeholder={field.type === "score" ? "0" : "Jawaban juri"} className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500" />
            )}
          </label>
        </div>
      ))}
    </div>
  );
}
