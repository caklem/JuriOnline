import Link from "next/link";
import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Button } from "@/components/arc/button/button";
import { Card, EmptyState, PageHeader } from "@/components/kit/ui";
import { formatDateTime } from "@/components/admin/format";
import { RejectRevisionForm } from "./reject-form";

export const metadata = { title: "Permintaan Revisi — JURI.ON" };
export const instant = false;

type Revision = {
  id: number;
  reason: string;
  created_at: string | null;
  event: { id: number; name: string } | null;
  participant: { name: string | null; registration_number: string | null };
  score_id: number;
  edit_url: string;
};

export default async function JuryRevisionsPage() {
  const data = await apiFetch<{ data: Revision[] }>("/jury/revisions");

  return (
    <AppFrame>
      <PageHeader
        eyebrow="Penilaian"
        title="Permintaan Revisi"
        description="Panitia meminta Anda meninjau ulang penilaian berikut. Perbarui skor melalui halaman penilaian; permintaan otomatis selesai setelah skor disimpan."
      />

      {data.data.length === 0 ? (
        <Card className="mt-5">
          <EmptyState
            title="Tidak ada permintaan revisi"
            description="Semua penilaian Anda sudah sesuai. Permintaan baru dari panitia akan muncul di sini dan pada notifikasi."
            action={
              <Link href="/jury/events">
                <Button variant="secondary">Kembali ke Penilaian</Button>
              </Link>
            }
          />
        </Card>
      ) : (
        <div className="mt-5 grid gap-4">
          {data.data.map((revision) => (
            <Card key={revision.id} className="p-5">
              <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <p className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">{revision.event?.name ?? "Event"}</p>
                  <h2 className="mt-1 text-base font-semibold text-[var(--foreground)]">
                    {revision.participant.name ?? "Peserta"}
                  </h2>
                  <p className="mt-1 text-xs text-[var(--text-secondary)]">
                    {revision.participant.registration_number ?? "-"}
                    {revision.created_at ? ` · ${formatDateTime(revision.created_at)}` : ""}
                  </p>
                </div>
                <Link href={revision.edit_url}>
                  <Button variant="primary">Buka & Perbaiki Nilai</Button>
                </Link>
              </div>
              <div className="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                <strong>Alasan panitia:</strong> {revision.reason}
              </div>
              <RejectRevisionForm revisionId={revision.id} />
            </Card>
          ))}
        </div>
      )}
    </AppFrame>
  );
}
