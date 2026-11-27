import Link from "next/link";
import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Card, CardHeader, EmptyState, PageHeader, Table } from "@/components/kit/ui";
import { isNextRedirect, participantErrorMessage } from "@/components/participant/api-errors";
import { buttonLinkClass } from "@/components/participant/button-link";
import { InfoList, InfoRow } from "@/components/participant/info-list";
import { PaymentStatusBadge, RegistrationStatusBadge } from "@/components/participant/status-badges";
import type { ParticipantRegistrationDetail, RegistrationDetailResponse } from "@/components/participant/types";

export const metadata = { title: "Detail Pendaftaran — JURI.ON" };

// Halaman membaca cookie sesi melalui apiFetch; jangan diprerender saat build.
export const instant = false;

type DetailPageProps = {
  params: Promise<{ id: string }>;
  searchParams: Promise<{ uploaded?: string | string[] }>;
};

type ParticipantResults = {
  published: boolean;
  average_score: number | null;
  rank: number | null;
  total_participants: number;
  jury_count: number;
  evaluations: { id: number; jury: string | null; score: number; public_feedback: string | null }[];
};

async function loadResults(id: string): Promise<ParticipantResults | null> {
  try {
    return await apiFetch<ParticipantResults>(`/registrations/${id}/results`);
  } catch {
    return null;
  }
}

async function loadRegistration(
  id: string,
): Promise<{ registration: ParticipantRegistrationDetail } | { error: string }> {
  try {
    const data = await apiFetch<RegistrationDetailResponse>(`/registrations/${id}`);

    return { registration: data.registration };
  } catch (error) {
    if (isNextRedirect(error)) {
      throw error;
    }

    return { error: participantErrorMessage(error, "Gagal memuat detail pendaftaran.") };
  }
}

export default async function RegistrationDetailPage({ params, searchParams }: DetailPageProps) {
  const { id } = await params;
  const { uploaded } = await searchParams;
  const [result, results] = await Promise.all([loadRegistration(id), loadResults(id)]);

  if ("error" in result) {
    return (
      <AppFrame>
        <PageHeader
          title="Detail Pendaftaran"
          description="Ringkasan event, pendaftaran, dan pembayaran peserta."
        />
        <Card className="mt-6">
          <EmptyState
            title="Pendaftaran tidak dapat ditampilkan"
            description={result.error}
            action={
              <Link href="/my-registrations" className={buttonLinkClass("secondary")}>
                Kembali ke Pendaftaran Saya
              </Link>
            }
          />
        </Card>
      </AppFrame>
    );
  }

  const registration = result.registration;

  const event = registration.event;
  const payment = registration.payment;
  const responses = Object.entries(registration.response ?? {});
  const eventRange =
    event?.event_start_label && event?.event_end_label
      ? `${event.event_start_label} - ${event.event_end_label}`
      : (event?.event_date_label ?? "-");

  return (
    <AppFrame>
      <div className="space-y-6">
        <PageHeader
          title="Detail Pendaftaran"
          description="Ringkasan event, pendaftaran, dan pembayaran peserta."
        />

        {uploaded ? (
          <div
            role="status"
            className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800"
          >
            Bukti pembayaran berhasil diupload! Mohon tunggu verifikasi.
          </div>
        ) : null}

        <div className="grid gap-6 lg:grid-cols-2">
          <Card>
            <CardHeader title="Informasi Event" />
            <div className="px-5 py-4">
              <InfoList>
                <InfoRow label="Nama Event">
                  <span className="font-medium">{event?.name ?? "-"}</span>
                </InfoRow>
                <InfoRow label="Penyelenggara">
                  <span className="text-[var(--text-secondary)]">{event?.organizer ?? "-"}</span>
                </InfoRow>
                <InfoRow label="Tanggal Event">
                  <span className="text-[var(--text-secondary)]">{eventRange}</span>
                </InfoRow>
                <InfoRow label="Lokasi">
                  <span className="text-[var(--text-secondary)]">{event?.location ?? "Online"}</span>
                </InfoRow>
              </InfoList>
            </div>
          </Card>

          <Card>
            <CardHeader title="Informasi Pendaftaran" />
            <div className="px-5 py-4">
              <InfoList>
                <InfoRow label="No. Pendaftaran">
                  <span className="rounded-md bg-[var(--surface-muted)] px-2 py-1 font-mono text-xs text-[var(--text-secondary)]">
                    {registration.registration_number}
                  </span>
                </InfoRow>
                <InfoRow label="Nama Peserta">
                  <span className="font-medium">{registration.user.name ?? "-"}</span>
                </InfoRow>
                <InfoRow label="Email">
                  <span className="text-[var(--text-secondary)]">{registration.user.email ?? "-"}</span>
                </InfoRow>
                <InfoRow label="Tanggal Daftar">
                  <span className="text-[var(--text-secondary)]">
                    {registration.registered_at_time_label ?? registration.registered_at_label ?? "-"}
                  </span>
                </InfoRow>
                <InfoRow label="Status">
                  <RegistrationStatusBadge status={registration.status} variant="detail" />
                </InfoRow>
                {registration.team_name ? (
                  <InfoRow label="Tim">
                    <span className="font-medium">{registration.team_name}</span>
                    <span className="mt-1 block text-xs text-[var(--text-secondary)]">
                      {(registration.team_members ?? []).map((member) => member.name).join(", ")}
                    </span>
                  </InfoRow>
                ) : null}
                {registration.notes ? (
                  <InfoRow label="Catatan">
                    <span className="whitespace-pre-line text-[var(--text-secondary)]">
                      {registration.notes}
                    </span>
                  </InfoRow>
                ) : null}
              </InfoList>
            </div>
          </Card>
        </div>

        {registration.files && registration.files.length > 0 ? (
          <Card>
            <CardHeader title="Dokumen Pendaftaran" description="File tersimpan privat dan hanya dapat diakses Anda serta panitia event." />
            <ul className="divide-y divide-[var(--border)] px-5">
              {registration.files.map((file) => (
                <li key={file.id} className="flex flex-wrap items-center justify-between gap-3 py-3">
                  <div>
                    <p className="text-sm font-semibold text-[var(--foreground)]">{file.label}</p>
                    <p className="text-xs text-[var(--text-secondary)]">{file.name} · {(file.size / 1024).toFixed(0)} KB</p>
                  </div>
                  <a href={`/registrations/${registration.id}/files/${file.id}`} className={buttonLinkClass("secondary")}>
                    Unduh
                  </a>
                </li>
              ))}
            </ul>
          </Card>
        ) : null}

        {responses.length > 0 ? (
          <Card>
            <CardHeader title="Data Form Pendaftaran" />
            <div className="space-y-4 px-5 py-4">
              {responses.map(([label, answer]) => (
                <div key={label} className="border-b border-[var(--border)] pb-3 last:border-0 last:pb-0">
                  <p className="text-xs font-semibold uppercase tracking-wide text-emerald-700">{label}</p>
                  <p className="mt-1 whitespace-pre-line text-sm text-[var(--text-secondary)]">
                    {answer === null || answer === undefined || answer === "" ? "-" : String(answer)}
                  </p>
                </div>
              ))}
            </div>
          </Card>
        ) : null}

        <Card>
          <CardHeader title="Status Pembayaran" />
          <div className="px-5 py-4">
            {payment ? (
              <>
                <InfoList>
                  <InfoRow label="Jumlah">
                    <span className="font-semibold">{payment.amount_label}</span>
                  </InfoRow>
                  <InfoRow label="Status">
                    <PaymentStatusBadge payment={payment} variant="detail" />
                  </InfoRow>
                  <InfoRow label="Tanggal Upload">
                    <span className="text-[var(--text-secondary)]">{payment.created_at_label ?? "-"}</span>
                  </InfoRow>
                </InfoList>

                {payment.has_proof ? (
                  <div className="mt-4">
                    <a
                      href={`/registrations/${registration.id}/payment/proof`}
                      target="_blank"
                      rel="noreferrer"
                      className={buttonLinkClass("primary")}
                    >
                      Lihat Bukti Transfer
                    </a>
                  </div>
                ) : null}

                {payment.verified_at_label ? (
                  <div className="mt-4 border-t border-[var(--border)] pt-4">
                    <InfoList>
                      <InfoRow label="Diverifikasi oleh">
                        <span className="text-[var(--text-secondary)]">{payment.verifier ?? "-"}</span>
                      </InfoRow>
                      <InfoRow label="Tanggal Verifikasi">
                        <span className="text-[var(--text-secondary)]">{payment.verified_at_label}</span>
                      </InfoRow>
                      {payment.verification_notes ? (
                        <InfoRow label="Catatan">
                          <span className="text-[var(--text-secondary)]">{payment.verification_notes}</span>
                        </InfoRow>
                      ) : null}
                    </InfoList>
                  </div>
                ) : null}
              </>
            ) : (
              <>
                <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                  <p className="font-semibold">Bukti pembayaran belum diupload!</p>
                  <p className="mt-1">Silakan upload bukti transfer Anda untuk memverifikasi pendaftaran.</p>
                </div>

                <Link
                  href={`/registrations/${registration.id}/payment`}
                  className={`${buttonLinkClass("primary")} mt-4`}
                >
                  Upload Bukti Pembayaran
                </Link>
              </>
            )}
          </div>
        </Card>

        <div>
          <Link href={`/events/${registration.event?.id}/chat`} className={buttonLinkClass("secondary")}>
            Buka Chat Event
          </Link>
        </div>

        {results?.published ? (
          <Card className="border-emerald-200 bg-emerald-50/40 p-5">
            <div className="flex flex-wrap items-center justify-between gap-4">
              <div>
                <p className="text-xs font-semibold uppercase tracking-wide text-emerald-700">Hasil evaluasi telah terbit</p>
                <p className="mt-1 text-sm text-[var(--text-secondary)]">
                  Nilai rata-rata dari {results.jury_count} juri. Catatan internal panitia tidak ditampilkan.
                </p>
              </div>
              <div className="flex gap-6">
                <div>
                  <p className="text-[11px] uppercase tracking-wide text-[var(--text-muted)]">Rata-rata</p>
                  <p className="text-2xl font-semibold text-[var(--foreground)]">{results.average_score ?? "-"}</p>
                </div>
                <div>
                  <p className="text-[11px] uppercase tracking-wide text-[var(--text-muted)]">Peringkat</p>
                  <p className="text-2xl font-semibold text-[var(--foreground)]">
                    {results.rank ? `#${results.rank}` : "-"}
                    <span className="ml-1 text-xs font-normal text-[var(--text-secondary)]">/ {results.total_participants}</span>
                  </p>
                </div>
              </div>
            </div>
          </Card>
        ) : null}

        {registration.jury_scores.length > 0 ? (
          <Card className="overflow-hidden">
            <CardHeader title="Penilaian Juri (Terbit)" />
            <Table head={["Juri", "Nilai", "Feedback", "Tanggal"]}>
              {registration.jury_scores.map((score, index) => (
                <tr key={`${score.jury ?? "juri"}-${index}`}>
                  <td className="px-5 py-3 font-medium text-[var(--foreground)]">{score.jury ?? "-"}</td>
                  <td className="px-5 py-3 font-semibold">{score.score_label}</td>
                  <td className="px-5 py-3 text-[var(--text-secondary)]">{score.public_feedback ?? "-"}</td>
                  <td className="whitespace-nowrap px-5 py-3 text-[var(--text-secondary)]">
                    {score.created_at_label ?? "-"}
                  </td>
                </tr>
              ))}
            </Table>
            <div className="mx-5 mb-5 mt-4 flex items-center gap-3 rounded-xl bg-emerald-50 px-4 py-3 ring-1 ring-inset ring-emerald-100">
              <span className="text-sm font-medium text-emerald-700">Nilai Rata-rata:</span>
              <span className="text-2xl font-semibold text-emerald-700">
                {registration.average_score.toFixed(2)}
              </span>
            </div>
          </Card>
        ) : null}

        <div className="flex flex-wrap gap-2">
          <Link href="/my-registrations" className={buttonLinkClass("secondary")}>
            Kembali ke Pendaftaran Saya
          </Link>
        </div>
      </div>
    </AppFrame>
  );
}
