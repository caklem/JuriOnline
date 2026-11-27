import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Card, CardHeader, PageHeader } from "@/components/kit/ui";
import { BackLink } from "@/components/admin/ui";
import { EventNav } from "@/components/admin/event-nav";
import { FormBuilder, type BuilderField } from "@/components/admin/form-builder";
import { saveJuryFormAction, saveJuryTemplateAction } from "./actions";

export const metadata = { title: "Form Penilaian Juri — JURI.ON" };
export const instant = false;

type JuryForm = { id: number; name: string; criteria_fields: BuilderField[]; use_default_score: boolean };
type TemplateForm = { id?: number; key?: string; name: string; description?: string | null; criteria_fields: BuilderField[]; use_default_score: boolean };
type Response = { forms: JuryForm[]; custom_templates: TemplateForm[]; built_in_templates: TemplateForm[] };

export default async function JuryFormBuilderPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const eventId = Number(id);
  const data = await apiFetch<Response>(`/admin/events/${id}/jury-forms`);
  const first = data.forms[0];
  const templates = [...(data.built_in_templates ?? []), ...(data.custom_templates ?? [])].map((template, index) => ({
    key: template.key ?? `custom-${template.id ?? index}`,
    name: template.name,
    description: template.description ?? null,
    fields: template.criteria_fields,
    useDefaultScore: template.use_default_score,
  }));

  return (
    <AppFrame>
      <div className="mb-4"><BackLink href={`/admin/events/${id}`}>Kembali ke detail event</BackLink></div>
      <PageHeader
        eyebrow="Kelola event"
        title="Form Penilaian Juri"
        description="Tentukan kriteria penilaian yang akan dipakai juri untuk menilai peserta."
      />
      <EventNav eventId={eventId} />

      <Card className="mt-6">
        <CardHeader title="Kriteria Penilaian" description="Nilai akhir adalah jumlah kriteria jika opsi penjumlahan otomatis aktif." />
        <div className="p-5">
          <FormBuilder
            mode="jury"
            initialFields={(first?.criteria_fields ?? []).map((field) => ({
              ...field,
              max: field.max ?? 100,
              description: field.description ?? "",
            }))}
            typeOptions={[
              { value: "score", label: "Nilai (skor)" },
              { value: "text", label: "Teks" },
              { value: "textarea", label: "Catatan" },
              { value: "select", label: "Pilihan" },
            ]}
            extra={{
              forms: data.forms.map((form) => ({ id: form.id, name: form.name })),
              formOptions: data.forms.map((form) => ({
                id: form.id,
                name: form.name,
                fields: form.criteria_fields ?? [],
                useDefaultScore: form.use_default_score,
              })),
              selectedFormId: first?.id ?? null,
              formName: first?.name ?? "",
              useDefaultScore: first?.use_default_score ?? true,
              templates,
            }}
            onSave={saveJuryFormAction.bind(null, eventId)}
            onSaveTemplate={saveJuryTemplateAction.bind(null, eventId)}
          />
        </div>
      </Card>
    </AppFrame>
  );
}
