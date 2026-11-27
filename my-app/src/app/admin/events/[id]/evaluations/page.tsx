import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Badge, Card, CardHeader, EmptyState, PageHeader } from "@/components/kit/ui";
import { ActionButton } from "@/components/admin/action-button";
import { ActionLink, BackLink } from "@/components/admin/ui";
import { formatDateTime } from "@/components/admin/format";
import { publishAllEvaluationsAction, publishEvaluationAction } from "./actions";
import { ReviewForm } from "./review-form";

export const metadata = { title: "Review Evaluasi — JURI.ON" };
export const instant = false;

type Evaluation = {
  id: number;
  score: number;
  status: string;
  submitted_at: string | null;
  published_at: string | null;
  public_feedback: string | null;
  private_evaluation: string | null;
  review_notes: string | null;
  reviewer: string | null;
  jury: string | null;
  form: string | null;
  participant: { id: number | null; name: string | null; email: string | null };
  pending_revision: { id: number; reason: string } | null;
};

type Response = {
  event: { id: number; name: string; evaluations_published_at: string | null };
  data: Evaluation[];
  summary: { total: number; submitted: number; reviewed: number; published: number; needs_revision: number };
};

const STATUS_TONE: Record<string, "success" | "warning" | "danger" | "info" | "neutral"> = {
  published: "success",
  reviewed: "info",
  submitted: "warning",
  needs_revision: "danger",
};

export default async function EvaluationsPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;

  let data: Response;

  try {
    data = await apiFetch<Response>(`/admin/events/${id}/evaluations`);
  } catch (error) {
    return (
      <AppFrame>
        <p className="text-sm text-rose-700">{error instanceof Error ? error.message : "Gagal memuat evaluasi."}</p>
      </AppFrame>
    );
  }

  return (
    <AppFrame>
      <div className="mb-4">
        <BackLink href={`/admin/events/${id}`}>Kembali ke Detail Event</BackLink>
      </div>
      <PageHeader
        eyebrow="Evaluasi"
        title="Review & Publikasi Penilaian"
        description="Periksa catatan juri sebelum diterbitkan. Peserta hanya melihat catatan publik; catatan privat tetap internal."
        action={
          <ActionButton
            action={publishAllEvaluationsAction.bind(null, Number(id))}
            variant="primary"
            confirm="Terbitkan semua evaluasi yang sudah direview? Peserta akan melihat nilai dan catatan publik."
          >
            Terbitkan Semua yang Direview
          </ActionButton>
        }
      />

      <div className="mt-4 grid gap-3 sm:grid-cols-4">
        {[
          ["Menunggu review", data.summary.submitted],
          ["Sudah direview", data.summary.reviewed],
          ["Terbit", data.summary.published],
          ["Perlu revisi", data.summary.needs_revision],
        ].map(([label, value]) => (
          <div key={String(label)} className="rounded-xl border border-[var(--border)] bg-[var(--background)] p-4">
            <p className="text-xs uppercase tracking-wide text-[var(--text-muted)]">{label}</p>
            <p className="mt-1 text-2xl font-semibold tabular-nums text-[var(--foreground)]">{value}</p>
          </div>
        ))}
      </div>

      {data.event.evaluations_published_at ? (
        <p className="mt-3 text-xs text-[var(--text-secondary)]">
          Publikasi terakhir: {formatDateTime(data.event.evaluations_published_at)}
        </p>
      ) : null}

      <Card className="mt-5">
        <CardHeader title={`${data.data.length} evaluasi`} description="Urut dari yang terbaru." />
        {data.data.length === 0 ? (
          <EmptyState title="Belum ada evaluasi" description="Evaluasi muncul setelah juri menyimpan penilaian peserta." />
        ) : (
          <div className="divide-y divide-[var(--border)]">
            {data.data.map((item) => (
              <div key={item.id} className="p-5">
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div>
                    <p className="font-semibold text-[var(--foreground)]">{item.participant.name ?? "Peserta"}</p>
                    <p className="text-xs text-[var(--text-secondary)]">
                      Juri: {item.jury ?? "-"} · Form: {item.form ?? "-"}
                      {item.submitted_at ? ` · ${formatDateTime(item.submitted_at)}` : ""}
                    </p>
                  </div>
                  <div className="flex items-center gap-2">
                    <Badge tone={STATUS_TONE[item.status] ?? "neutral"}>{item.status}</Badge>
                    <span className="text-lg font-semibold tabular-nums text-[var(--foreground)]">{item.score}</span>
                  </div>
                </div>

                <div className="mt-3 grid gap-3 lg:grid-cols-2">
                  <div className="rounded-lg border border-[var(--border)] bg-[var(--surface-muted)] p-3">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-[var(--text-muted)]">Catatan publik</p>
                    <p className="mt-1 whitespace-pre-line text-sm text-[var(--text-secondary)]">{item.public_feedback || "-"}</p>
                  </div>
                  <div className="rounded-lg border border-amber-200 bg-amber-50 p-3">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-amber-700">Catatan internal (privat)</p>
                    <p className="mt-1 whitespace-pre-line text-sm text-amber-900">{item.private_evaluation || "-"}</p>
                  </div>
                </div>

                {item.review_notes ? (
                  <p className="mt-2 text-xs text-[var(--text-secondary)]">
                    Catatan review {item.reviewer ? `(${item.reviewer})` : ""}: {item.review_notes}
                  </p>
                ) : null}

                {item.pending_revision ? (
                  <p className="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                    Menunggu revisi juri: {item.pending_revision.reason}
                  </p>
                ) : item.status === "published" ? (
                  <p className="mt-2 text-xs text-emerald-700">
                    Terbit {item.published_at ? formatDateTime(item.published_at) : ""}. Perubahan skor oleh juri akan menarik publikasi ini.
                  </p>
                ) : item.status === "reviewed" ? (
                  <div className="mt-3">
                    <ActionButton action={publishEvaluationAction.bind(null, item.id, Number(id))} variant="primary" confirm="Terbitkan evaluasi ini ke peserta?">
                      Terbitkan Evaluasi
                    </ActionButton>
                  </div>
                ) : (
                  <ReviewForm scoreId={item.id} eventId={Number(id)} />
                )}
              </div>
            ))}
          </div>
        )}
      </Card>

      <div className="mt-4">
        <ActionLink href={`/admin/events/${id}/recap`}>Lihat Rekapitulasi Nilai</ActionLink>
      </div>
    </AppFrame>
  );
}
