import { AppFrame } from "@/components/app-frame";
import { apiFetch } from "@/lib/api";
import { Badge, Card, EmptyState, PageHeader } from "@/components/kit/ui";
import { ActionLink, BackLink, LoadError } from "@/components/jury/chrome";
import { ScoreForm } from "@/components/jury/score-form";
import type { ScoreFormResponse } from "@/components/jury/types";

export const metadata = { title: "Edit Penilaian — JURI.ON" };
export const instant = false;

export default async function EditScorePage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;

  let data: ScoreFormResponse | null = null;
  let loadError: string | null = null;

  try {
    data = await apiFetch<ScoreFormResponse>(`/jury/scores/${id}`);
  } catch (error) {
    loadError = error instanceof Error ? error.message : "Terjadi kesalahan saat memuat penilaian.";
  }

  if (!data) {
    return (
      <AppFrame>
        {loadError === "Penilaian event ini sedang ditutup." ? (
          <Card>
            <EmptyState
              title="Penilaian ditutup"
              description="Penilaian event ini sedang ditutup."
              action={
                <ActionLink href="/jury/events" variant="outline">
                  Kembali ke Daftar Event
                </ActionLink>
              }
            />
          </Card>
        ) : (
          <LoadError title="Penilaian tidak dapat dimuat" description={loadError ?? "Terjadi kesalahan saat memuat penilaian."} />
        )}
      </AppFrame>
    );
  }

  return (
    <AppFrame>
      <div className="space-y-4">
        <BackLink href={`/jury/events/${data.event.id}/participants`}>Kembali ke Daftar Peserta</BackLink>

        <PageHeader
          title="Edit Penilaian"
          description={`${data.registration.user?.name} · ${data.event.name}`}
        />

        {!data.scoring_open ? (
          <Card>
            <EmptyState
              title="Penilaian ditutup"
              description="Penilaian event ini sedang ditutup."
            />
          </Card>
        ) : (
          <>
            <Card className="p-5">
              <h2 className="mb-4 text-base font-semibold text-[var(--foreground)]">Informasi Peserta</h2>

              <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                  <p className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">Nama Peserta</p>
                  <p className="mt-1 text-sm font-semibold text-[var(--foreground)]">{data.registration.user?.name}</p>
                </div>
                <div>
                  <p className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">Email</p>
                  <p className="mt-1 break-all text-sm text-[var(--text-secondary)]">{data.registration.user?.email}</p>
                </div>
                <div>
                  <p className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">Event</p>
                  <p className="mt-1 text-sm text-[var(--text-secondary)]">{data.event.name}</p>
                </div>
                <div>
                  <p className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">Status</p>
                  <p className="mt-1">
                    <Badge tone="success">{data.registration.status_label}</Badge>
                  </p>
                </div>
              </div>

              {data.registration.notes ? (
                <div className="mt-4 rounded-xl bg-[var(--surface-muted)] px-4 py-3 text-sm text-[var(--text-secondary)] ring-1 ring-[var(--border)]">
                  <strong className="text-[var(--foreground)]">Catatan Peserta:</strong>
                  <span className="mt-1 block whitespace-pre-line">{data.registration.notes}</span>
                </div>
              ) : null}

              {data.average_score ? (
                <div className="mt-4 rounded-xl bg-sky-50 px-4 py-3 text-sm text-sky-800 ring-1 ring-sky-100">
                  <strong>Rata-rata nilai peserta dari seluruh juri:</strong> {data.average_score.toFixed(2)}
                  <span className="text-xs text-sky-700"> (nilai Anda akan ditambahkan ke rata-rata ini)</span>
                </div>
              ) : null}
            </Card>

            <Card className="p-5">
              <ScoreForm
                registrationId={data.registration.id}
                eventId={data.event.id}
                form={data.form}
                existingScore={data.existing_score}
                mode="edit"
              />
            </Card>
          </>
        )}
      </div>
    </AppFrame>
  );
}
