import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Badge, Card, CardHeader, EmptyState, PageHeader } from "@/components/kit/ui";
import { BackLink } from "@/components/admin/ui";
import { formatDateTime, formatRupiah } from "@/components/admin/format";
import { RegistrationStatusBadge } from "@/components/participant/status-badges";
import { ScoreRevisionForm } from "./revision-form";

export const metadata = { title: "Detail Pendaftaran — JURI.ON" };
export const instant = false;

type RegistrationDetail = {
  id: number;
  registration_number: string;
  status: string;
  status_label: string;
  notes: string | null;
  registered_at: string | null;
  user: { name: string | null; email: string | null };
  event: { id: number; name: string; event_date: string | null };
  payment: {
    id: number;
    status: string;
    status_label: string;
    amount: number;
    verification_notes: string | null;
    verified_at: string | null;
    verifier: string | null;
  } | null;
  responses: Record<string, unknown>;
  team_name: string | null;
  team_members: { name: string; email?: string | null }[] | null;
  files: { id: number; label: string; name: string; size: number; url: string }[];
  scores: {
    id: number;
    jury: string | null;
    score: number;
    public_feedback: string | null;
    pending_revision: { id: number; reason: string; created_at: string | null } | null;
  }[];
};

const currency = (value: number) => formatRupiah(value);

export default async function AdminRegistrationDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const data = await apiFetch<{ registration: RegistrationDetail }>(`/admin/registrations/${id}`);
  const registration = data.registration;

  return (
    <AppFrame>
      <div className="mb-4">
        <BackLink href={`/admin/events/${registration.event.id}/participants`}>Kembali ke Daftar Peserta</BackLink>
      </div>

      <PageHeader
        eyebrow="Detail pendaftaran"
        title={registration.user.name ?? "Peserta"}
        description={`${registration.registration_number} · ${registration.event.name}`}
        action={<RegistrationStatusBadge status={registration.status} variant="detail" />}
      />

      <div className="mt-6 grid gap-4 lg:grid-cols-3">
        <Card className="lg:col-span-2">
          <CardHeader title="Data Pendaftaran" />
          <dl className="grid gap-4 p-5 sm:grid-cols-2">
            <div>
              <dt className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">Nama</dt>
              <dd className="mt-1 text-sm font-semibold text-[var(--foreground)]">{registration.user.name ?? "-"}</dd>
            </div>
            <div>
              <dt className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">Email</dt>
              <dd className="mt-1 break-all text-sm text-[var(--text-secondary)]">{registration.user.email ?? "-"}</dd>
            </div>
            <div>
              <dt className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">Status Pendaftaran</dt>
              <dd className="mt-1"><RegistrationStatusBadge status={registration.status} variant="detail" /></dd>
            </div>
            <div>
              <dt className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">Tanggal Daftar</dt>
              <dd className="mt-1 text-sm text-[var(--text-secondary)]">
                {registration.registered_at ? formatDateTime(registration.registered_at) : "-"}
              </dd>
            </div>
            {registration.team_name ? (
              <div className="sm:col-span-2 rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] p-3">
                <dt className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">Tim</dt>
                <dd className="mt-1 text-sm font-semibold text-[var(--foreground)]">{registration.team_name}</dd>
                <dd className="mt-1 flex flex-wrap gap-2 text-xs text-[var(--text-secondary)]">
                  {(registration.team_members ?? []).map((member, index) => (
                    <span key={`${member.name}-${index}`} className="rounded-full border border-[var(--border)] bg-[var(--background)] px-2 py-0.5">
                      {member.name}{member.email ? ` · ${member.email}` : ""}
                    </span>
                  ))}
                </dd>
              </div>
            ) : null}

            {registration.notes ? (
              <div className="sm:col-span-2">
                <dt className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">Catatan</dt>
                <dd className="mt-1 whitespace-pre-line text-sm text-[var(--text-secondary)]">{registration.notes}</dd>
              </div>
            ) : null}
          </dl>

          {registration.files && registration.files.length > 0 ? (
            <>
              <CardHeader title="Dokumen Peserta" description="File privat; tautan hanya dapat dibuka panitia event ini." />
              <ul className="divide-y divide-[var(--border)] px-5">
                {registration.files.map((file) => (
                  <li key={file.id} className="flex flex-wrap items-center justify-between gap-3 py-3">
                    <div>
                      <p className="text-sm font-semibold text-[var(--foreground)]">{file.label}</p>
                      <p className="text-xs text-[var(--text-secondary)]">{file.name} · {(file.size / 1024).toFixed(0)} KB</p>
                    </div>
                    <a href={`/admin/registrations/${registration.id}/files/${file.id}`} className="rounded-lg border border-[var(--border)] px-3 py-1.5 text-xs font-semibold text-[var(--text-secondary)]">
                      Unduh
                    </a>
                  </li>
                ))}
              </ul>
            </>
          ) : null}

          <CardHeader title="Jawaban Form Pendaftaran" />
          {Object.keys(registration.responses ?? {}).length === 0 ? (
            <EmptyState title="Tidak ada jawaban form" description="Peserta tidak mengisi form tambahan." />
          ) : (
            <dl className="grid gap-4 p-5 sm:grid-cols-2">
              {Object.entries(registration.responses).map(([label, value]) => (
                <div key={label}>
                  <dt className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">{label}</dt>
                  <dd className="mt-1 break-words text-sm text-[var(--text-secondary)]">
                    {value === null || value === "" ? "-" : String(value)}
                  </dd>
                </div>
              ))}
            </dl>
          )}
        </Card>

        <div className="flex flex-col gap-4">
          <Card>
            <CardHeader title="Pembayaran" />
            <div className="p-5">
              {registration.payment ? (
                <div className="flex flex-col gap-2 text-sm">
                  <div className="flex items-center justify-between">
                    <span className="text-[var(--text-secondary)]">Status</span>
                    <Badge tone={registration.payment.status === "verified" ? "success" : registration.payment.status === "rejected" ? "danger" : "warning"}>
                      {registration.payment.status_label}
                    </Badge>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="text-[var(--text-secondary)]">Jumlah</span>
                    <strong className="text-[var(--foreground)]">{currency(registration.payment.amount)}</strong>
                  </div>
                  {registration.payment.verified_at ? (
                    <div className="flex items-center justify-between">
                      <span className="text-[var(--text-secondary)]">Diverifikasi</span>
                      <span className="text-[var(--foreground)]">{formatDateTime(registration.payment.verified_at)}</span>
                    </div>
                  ) : null}
                  {registration.payment.verifier ? (
                    <div className="flex items-center justify-between">
                      <span className="text-[var(--text-secondary)]">Oleh</span>
                      <span className="text-[var(--foreground)]">{registration.payment.verifier}</span>
                    </div>
                  ) : null}
                  {registration.payment.verification_notes ? (
                    <p className="mt-2 rounded-lg bg-[var(--surface-muted)] px-3 py-2 text-xs text-[var(--text-secondary)]">
                      {registration.payment.verification_notes}
                    </p>
                  ) : null}
                </div>
              ) : (
                <p className="text-sm text-[var(--text-secondary)]">Belum upload bukti pembayaran.</p>
              )}
            </div>
          </Card>

          <Card>
            <CardHeader title="Penilaian Juri" />
            {registration.scores.length === 0 ? (
              <p className="px-5 py-6 text-sm text-[var(--text-secondary)]">Belum ada penilaian.</p>
            ) : (
              <ul className="divide-y divide-[var(--border)]">
                {registration.scores.map((score) => (
                  <li key={score.id} className="px-5 py-3 text-sm">
                    <div className="flex items-center justify-between gap-2">
                      <span className="font-medium text-[var(--foreground)]">{score.jury ?? "Juri"}</span>
                      <strong className="text-[var(--foreground)]">{score.score}</strong>
                    </div>
                    {score.public_feedback ? (
                      <p className="mt-1 text-xs text-[var(--text-secondary)]">{score.public_feedback}</p>
                    ) : null}
                    {score.pending_revision ? (
                      <div className="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                        <strong>Revisi menunggu juri.</strong> {score.pending_revision.reason}
                      </div>
                    ) : (
                      <ScoreRevisionForm scoreId={score.id} />
                    )}
                  </li>
                ))}
              </ul>
            )}
          </Card>
        </div>
      </div>
    </AppFrame>
  );
}
