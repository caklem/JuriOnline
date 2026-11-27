import { redirect } from "next/navigation";
import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Card, CardHeader, PageHeader } from "@/components/kit/ui";
import { EventRegisterForm, type EventFormField } from "./register-form";

export const metadata = { title: "Daftar Event — JURI.ON" };
export const instant = false;

type RegistrationFormResponse = {
  event: {
    id: number;
    name: string;
    price: number;
    quota: number;
    registered_count: number;
    event_date: string | null;
    registration_open: boolean;
    participation_mode: "individual" | "team";
    team_min_members: number;
    team_max_members: number;
    require_official_email: boolean;
    allowed_email_domains: string[];
    email_domain_allowed: boolean;
  };
  fields: EventFormField[];
  registered: boolean;
  registration_id: number | null;
};

export default async function RegisterEventPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const data = await apiFetch<RegistrationFormResponse>(`/events/${id}/registration-form`);

  if (data.registered && data.registration_id) {
    redirect(`/registrations/${data.registration_id}`);
  }

  const blocked = !data.event.registration_open || !data.event.email_domain_allowed;

  return (
    <AppFrame>
      <PageHeader
        eyebrow="Pendaftaran event"
        title={data.event.name}
        description="Lengkapi data berikut untuk mendaftar. Setelah mendaftar, unggah bukti pembayaran agar pendaftaran diverifikasi panitia."
      />

      {blocked ? (
        <Card className="mt-6">
          <CardHeader title="Pendaftaran Tidak Tersedia" />
          <div className="px-5 py-6 text-sm leading-6 text-[var(--text-secondary)]">
            {!data.event.registration_open ? (
              <p>Pendaftaran event ini sudah ditutup atau kuota penuh.</p>
            ) : (
              <p>
                Pendaftaran event ini hanya untuk email resmi dengan domain:{" "}
                <strong>{data.event.allowed_email_domains.join(", ")}</strong>.
              </p>
            )}
          </div>
        </Card>
      ) : (
        <Card className="mt-6">
          <CardHeader
            title="Data Pendaftaran"
            description={`Kuota ${data.event.registered_count}/${data.event.quota} peserta terisi.`}
          />
          <div className="px-5 py-5">
            <EventRegisterForm
              eventId={data.event.id}
              fields={data.fields}
              participationMode={data.event.participation_mode}
              teamMin={data.event.team_min_members}
              teamMax={data.event.team_max_members}
              submitLabel="Daftar Sekarang"
            />
          </div>
        </Card>
      )}
    </AppFrame>
  );
}
