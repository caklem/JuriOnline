import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Badge, Card, CardHeader, EmptyState, PageHeader, Table } from "@/components/kit/ui";
import { ActionButton } from "@/components/admin/action-button";
import { ActionLink, Pagination } from "@/components/admin/ui";
import { LoadError } from "@/components/admin/load-error";
import { formatDate, formatShortDate } from "@/components/admin/format";
import type { ApiMeta, EventSummary } from "@/components/admin/types";
import { updateEventStatusAction } from "./actions";

export const metadata = { title: "Event — JURI.ON" };

// Membaca cookie sesi melalui AppFrame/apiFetch.
export const instant = false;

type EventsResponse = {
  can_manage_all: boolean;
  title: string;
  description: string;
  data: EventSummary[];
  meta: ApiMeta;
};

function statusTone(status: string): "success" | "warning" | "danger" | "info" | "neutral" {
  switch (status) {
    case "active":
      return "success";
    case "draft":
      return "warning";
    case "closed":
      return "danger";
    case "completed":
      return "info";
    default:
      return "neutral";
  }
}

export default async function AdminEventsPage({
  searchParams,
}: {
  searchParams: Promise<{ page?: string }>;
}) {
  const { page } = await searchParams;
  const query = page ? `?page=${encodeURIComponent(page)}` : "";
  let data: EventsResponse;

  try {
    data = await apiFetch<EventsResponse>(`/admin/events${query}`);
  } catch (error) {
    return (
      <AppFrame>
        <LoadError
          title="Event tidak dapat dimuat"
          description={error instanceof Error ? error.message : "Terjadi kesalahan saat memuat event."}
          backHref="/dashboard"
          backLabel="Kembali ke dashboard"
        />
      </AppFrame>
    );
  }

  return (
    <AppFrame>
      <div className="flex flex-col gap-6">
        <PageHeader
          title={data.title}
          description={data.description}
          action={<ActionLink href="/admin/events/create" tone="solid">Buat Event Baru</ActionLink>}
        />

        {data.data.length === 0 ? (
          <Card>
            <EmptyState
              title="Belum Ada Event"
              description="Mulai dengan membuat event pertama Anda."
              action={
                <ActionLink href="/admin/events/create" tone="solid">
                  Buat Event Pertama
                </ActionLink>
              }
            />
          </Card>
        ) : (
          <Card>
            <CardHeader
              title="Daftar Event"
              action={<Badge>{data.meta.total} event</Badge>}
            />
            <Table
              head={[
                "Nama Event",
                "Kategori",
                "Penyelenggara",
                "Status",
                "Peserta",
                "Periode Pendaftaran",
                "Aksi",
              ]}
            >
              {data.data.map((event) => (
                <tr key={event.id} className="align-top">
                  <td className="px-5 py-4">
                    <strong className="font-semibold text-[var(--foreground)]">{event.name}</strong>
                  </td>
                  <td className="px-5 py-4 text-[var(--text-secondary)]">{event.category?.name ?? "-"}</td>
                  <td className="px-5 py-4 text-[var(--text-secondary)]">{event.organizer?.name ?? "-"}</td>
                  <td className="px-5 py-4">
                    <Badge tone={statusTone(event.status)}>{event.status_label}</Badge>
                  </td>
                  <td className="px-5 py-4">
                    <span className="whitespace-nowrap text-[var(--foreground)]">
                      {event.registered_count} / {event.quota}
                    </span>
                    {event.is_full ? (
                      <span className="ml-2">
                        <Badge tone="danger">Penuh</Badge>
                      </span>
                    ) : null}
                  </td>
                  <td className="whitespace-nowrap px-5 py-4 text-[var(--text-secondary)]">
                    {formatShortDate(event.registration_start)} - {formatDate(event.registration_end)}
                  </td>
                  <td className="px-5 py-4">
                    <div className="flex flex-wrap items-center gap-2">
                      <ActionLink href={`/admin/events/${event.id}`} tone="solid">
                        Detail
                      </ActionLink>
                      <ActionLink href={`/admin/events/${event.id}/edit`}>Edit</ActionLink>
                      {event.status === "draft" ? (
                        <ActionButton
                          action={updateEventStatusAction.bind(null, event.id, "active")}
                          variant="primary"
                          size="sm"
                          confirm="Aktifkan event ini?"
                        >
                          Aktifkan
                        </ActionButton>
                      ) : null}
                    </div>
                  </td>
                </tr>
              ))}
            </Table>
            <Pagination meta={data.meta} basePath="/admin/events" />
          </Card>
        )}
      </div>
    </AppFrame>
  );
}
