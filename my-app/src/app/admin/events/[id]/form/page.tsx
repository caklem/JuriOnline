import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Card, CardHeader, PageHeader } from "@/components/kit/ui";
import { BackLink } from "@/components/admin/ui";
import { EventNav } from "@/components/admin/event-nav";
import { FormBuilder, type BuilderField } from "@/components/admin/form-builder";
import { saveRegistrationFormAction } from "./actions";

export const metadata = { title: "Kelola Form Pendaftaran — JURI.ON" };
export const instant = false;

type Response = { fields: BuilderField[]; presets?: { key: string; name: string; fields: BuilderField[] }[] };

export default async function EventFormBuilderPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const eventId = Number(id);
  const data = await apiFetch<Response>(`/admin/events/${id}/registration-form`);

  return (
    <AppFrame>
      <div className="mb-4"><BackLink href={`/admin/events/${id}`}>Kembali ke detail event</BackLink></div>
      <PageHeader
        eyebrow="Kelola event"
        title="Form Pendaftaran"
        description="Atur kolom yang harus diisi peserta saat mendaftar event ini."
      />
      <EventNav eventId={eventId} />

      <Card className="mt-6">
        <CardHeader title="Field Pendaftaran" description="Seret tidak diperlukan — urutan mengikuti urutan field di bawah." />
        <div className="p-5">
          <FormBuilder
            mode="registration"
            initialFields={data.fields}
            typeOptions={[
              { value: "text", label: "Teks" },
              { value: "email", label: "Email" },
              { value: "number", label: "Angka" },
              { value: "url", label: "URL" },
              { value: "textarea", label: "Teks Panjang" },
              { value: "date", label: "Tanggal" },
              { value: "select", label: "Pilihan" },
              { value: "file", label: "Upload File" },
            ]}
            extra={{ presets: data.presets ?? [] }}
            onSave={saveRegistrationFormAction.bind(null, eventId)}
          />
        </div>
      </Card>
    </AppFrame>
  );
}
