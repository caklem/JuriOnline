import { AppFrame } from "@/components/app-frame";
import { apiFetch } from "@/lib/api";
import { Badge, Card, EmptyState, PageHeader } from "@/components/kit/ui";
import { ActionLink, BackLink, LoadError } from "@/components/jury/chrome";
import type { FormPreviewResponse } from "@/components/jury/types";

export const metadata = { title: "Form Penilaian — JURI.ON" };
export const instant = false;

export default async function JuryFormPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;

  let data: FormPreviewResponse | null = null;
  let loadError: string | null = null;

  try {
    data = await apiFetch<FormPreviewResponse>(`/jury/events/${id}/form`);
  } catch (error) {
    loadError = error instanceof Error ? error.message : "Terjadi kesalahan saat memuat form penilaian.";
  }

  if (!data) {
    return (
      <AppFrame>
        <LoadError title="Form penilaian tidak dapat dimuat" description={loadError ?? "Terjadi kesalahan saat memuat form penilaian."} />
      </AppFrame>
    );
  }

  const fields = data.form.criteria_fields;
  const totalMaxScore = fields.reduce(
    (sum, field) => (field.type === "score" ? sum + Number(field.max ?? 0) : sum),
    0,
  );

  return (
    <AppFrame>
      <div className="space-y-4">
        <BackLink href="/jury/events">Kembali ke Daftar Event</BackLink>

        <PageHeader
          eyebrow="Form penilaian juri"
          title={data.form.name || "Form Penilaian"}
          description={data.event.name}
          action={
            <ActionLink href={`/jury/events/${data.event.id}/participants`} variant="primary">
              Mulai Menilai Peserta
            </ActionLink>
          }
        />

        <Card>
          <div className="border-b border-[var(--border)] px-5 py-4">
            <h2 className="text-sm font-semibold text-[var(--foreground)]">Kriteria yang Akan Anda Nilai</h2>
            <p className="mt-1 text-xs leading-5 text-[var(--text-secondary)]">
              Form ini yang muncul saat Anda menekan <strong>Berikan Penilaian</strong> pada peserta.
            </p>
          </div>

          <div className="grid gap-3 p-5 sm:grid-cols-3">
            <div className="rounded-xl bg-[var(--surface-muted)] px-4 py-3 ring-1 ring-[var(--border)]">
              <p className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">Jumlah Kriteria</p>
              <p className="mt-1 text-sm font-semibold text-[var(--foreground)]">{fields.length} kriteria</p>
            </div>
            <div className="rounded-xl bg-[var(--surface-muted)] px-4 py-3 ring-1 ring-[var(--border)]">
              <p className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">Total Maksimal Poin</p>
              <p className="mt-1 text-sm font-semibold text-[var(--foreground)]">
                {totalMaxScore > 0 ? `${totalMaxScore} poin` : "-"}
              </p>
            </div>
            <div className="rounded-xl bg-[var(--surface-muted)] px-4 py-3 ring-1 ring-[var(--border)]">
              <p className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">Nilai Akhir</p>
              <p className="mt-1 text-sm font-semibold text-[var(--foreground)]">Dijumlahkan otomatis</p>
            </div>
          </div>

          <div className="px-5 pb-5">
            {fields.length === 0 ? (
              <EmptyState
                title="Form masih kosong"
                description="Panitia belum mengisi kriteria penilaian untuk event ini. Hubungi panitia."
              />
            ) : (
              <div className="grid gap-3">
                {fields.map((field, index) => {
                  const typeLabel = field.type === "score" ? "Nilai" : field.type === "textarea" ? "Catatan" : "Isian";

                  return (
                    <div key={index} className="rounded-xl border border-[var(--border)] px-4 py-3">
                      <div className="flex flex-wrap items-start justify-between gap-2">
                        <div className="min-w-0">
                          <p className="text-sm font-semibold text-[var(--foreground)]">
                            {index + 1}. {field.label || `Kriteria ${index + 1}`}
                            {field.required ? <span className="text-rose-600"> *</span> : null}
                          </p>
                          {field.description ? (
                            <p className="mt-1 text-xs leading-5 text-[var(--text-secondary)]">{field.description}</p>
                          ) : null}
                        </div>
                        <div className="flex shrink-0 items-center gap-2">
                          <Badge tone="info">{typeLabel}</Badge>
                          {field.type === "score" ? <Badge tone="warning">Maks {field.max ?? 100}</Badge> : null}
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>
            )}

            <p className="mt-4 text-xs text-[var(--text-muted)]">
              Kolom bertanda <span className="text-rose-600">*</span> wajib diisi. Nilai akhir adalah jumlah seluruh
              kriteria dan dihitung otomatis oleh sistem.
            </p>
          </div>
        </Card>

        <Card className="p-5">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <div className="min-w-0">
              <p className="text-sm font-semibold text-[var(--foreground)]">Sudah paham kriterianya?</p>
              <p className="mt-1 text-xs text-[var(--text-secondary)]">
                Pilih peserta terverifikasi, isi kriteria di atas, lalu simpan. Sistem lanjut otomatis ke peserta
                berikutnya.
              </p>
            </div>
            <div className="flex flex-wrap gap-2">
              <ActionLink href={`/jury/events/${data.event.id}/participants`} variant="primary">
                Nilai Peserta
              </ActionLink>
              <ActionLink href={`/jury/events/${data.event.id}/leaderboard`} variant="outline">
                Leaderboard
              </ActionLink>
            </div>
          </div>
        </Card>
      </div>
    </AppFrame>
  );
}
