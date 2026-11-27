import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Badge, Card, EmptyState, PageHeader, StatGrid, Table } from "@/components/kit/ui";
import { ActionLink, Pagination } from "@/components/admin/ui";
import { LoadError } from "@/components/admin/load-error";
import { formatScore } from "@/components/admin/format";
import { ParticipantStatusControl } from "@/components/admin/participant-status-control";
import type { ApiMeta, ParticipantRow } from "@/components/admin/types";
import { updateRegistrationStatusAction } from "./actions";
import { EventNav } from "@/components/admin/event-nav";

export const metadata = { title: "Daftar Peserta — JURI.ON" };

export const instant = false;

type ParticipantsResponse = {
  event: { id: number; name: string };
  stats: { total: number; verified: number; pending: number; rejected: number };
  data: ParticipantRow[];
  meta: ApiMeta;
};

function registrationTone(status: string): "success" | "warning" | "danger" | "info" | "neutral" {
  switch (status) {
    case "verified":
      return "success";
    case "pending":
      return "warning";
    case "rejected":
      return "danger";
    case "cancelled":
      return "neutral";
    default:
      return "info";
  }
}

export default async function EventParticipantsPage({
  params,
  searchParams,
}: {
  params: Promise<{ id: string }>;
  searchParams: Promise<{ page?: string }>;
}) {
  const { id } = await params;
  const { page } = await searchParams;
  const query = page ? `?page=${encodeURIComponent(page)}` : "";
  let data: ParticipantsResponse;

  try {
    data = await apiFetch<ParticipantsResponse>(`/admin/events/${id}/participants${query}`);
  } catch (error) {
    return (
      <AppFrame>
        <LoadError
          title="Daftar peserta tidak dapat dimuat"
          description={error instanceof Error ? error.message : "Terjadi kesalahan saat memuat peserta."}
        />
      </AppFrame>
    );
  }

  return (
    <AppFrame>
      <div className="flex flex-col gap-6">
        <PageHeader
          title="Daftar Peserta"
          description={data.event.name}
          action={<div className="flex flex-wrap gap-2"><a href={`/admin/events/${data.event.id}/participants/export`} className="inline-flex items-center rounded-lg border border-[var(--border)] px-3 py-2 text-sm font-semibold text-[var(--text-secondary)]">Ekspor CSV</a><ActionLink href={`/admin/events/${data.event.id}`}>Kembali ke Event</ActionLink></div>}
        />

        <EventNav eventId={data.event.id} />

        <StatGrid
          items={[
            { label: "Total Peserta", value: data.stats.total },
            { label: "Terverifikasi", value: data.stats.verified },
            { label: "Pending", value: data.stats.pending },
            { label: "Ditolak", value: data.stats.rejected },
          ]}
        />

        <Card>
          {data.data.length === 0 ? (
            <EmptyState title="Belum ada peserta" description="Belum ada peserta yang mendaftar pada event ini." />
          ) : (
            <>
              <Table
                head={[
                  "No. Pendaftaran",
                  "Nama Peserta",
                  "Status Pendaftaran",
                  "Status Pembayaran",
                  "Nilai Rata-rata",
                  "Aksi",
                ]}
              >
                {data.data.map((registration) => (
                  <tr key={registration.id} className="align-middle">
                    <td className="px-5 py-4">
                      <span className="rounded-md bg-[var(--surface-muted)] px-2 py-1 font-mono text-xs text-[var(--text-secondary)]">
                        {registration.registration_number}
                      </span>
                    </td>
                    <td className="px-5 py-4">
                      <div className="flex items-center gap-3">
                        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[var(--foreground)] text-sm font-semibold text-[var(--background)]">
                          {(registration.user?.name ?? "?").slice(0, 1).toUpperCase()}
                        </span>
                        <div className="min-w-0">
                          <div className="font-semibold text-[var(--foreground)]">{registration.user?.name ?? "-"}</div>
                          <div className="truncate text-xs text-[var(--text-secondary)]">{registration.user?.email ?? "-"}</div>
                        </div>
                      </div>
                    </td>
                    <td className="px-5 py-4">
                      <Badge tone={registrationTone(registration.status)}>{registration.status_label}</Badge>
                    </td>
                    <td className="px-5 py-4">
                      {registration.payment ? (
                        <Badge tone={registrationTone(registration.payment.status)}>
                          {registration.payment.status_label}
                        </Badge>
                      ) : (
                        <Badge tone="danger">Belum Upload</Badge>
                      )}
                    </td>
                    <td className="px-5 py-4 text-center">
                      {registration.average_score > 0 ? (
                        <span className="inline-flex items-center rounded-lg bg-amber-50 px-3 py-1.5 text-sm font-semibold text-amber-700 ring-1 ring-amber-200">
                          {formatScore(registration.average_score)}
                        </span>
                      ) : (
                        <span className="text-[var(--text-muted)]">-</span>
                      )}
                    </td>
                    <td className="px-5 py-4">
                      <div className="flex flex-wrap items-center gap-2">
                        <ActionLink href={`/admin/registrations/${registration.id}`}>Detail</ActionLink>
                        <ParticipantStatusControl
                        action={updateRegistrationStatusAction.bind(null, registration.id)}
                          currentStatus={registration.status}
                        />
                      </div>
                    </td>
                  </tr>
                ))}
              </Table>
              <Pagination
                meta={data.meta}
                basePath={`/admin/events/${data.event.id}/participants`}
              />
            </>
          )}
        </Card>
      </div>
    </AppFrame>
  );
}
