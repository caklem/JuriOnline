import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Card, PageHeader } from "@/components/kit/ui";
import { ActionLink } from "@/components/admin/ui";
import { LoadError } from "@/components/admin/load-error";
import { EventForm } from "@/components/admin/event-form";
import type { CategoryOption, EventSummary } from "@/components/admin/types";
import { updateEventAction } from "../../actions";

export const metadata = { title: "Edit Event — JURI.ON" };

export const instant = false;

type EventDetailResponse = { event: EventSummary };

export default async function EditEventPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  let detail: EventDetailResponse;
  let categories: { data: CategoryOption[] };

  try {
    [detail, categories] = await Promise.all([
      apiFetch<EventDetailResponse>(`/admin/events/${id}`),
      apiFetch<{ data: CategoryOption[] }>("/admin/categories"),
    ]);
  } catch (error) {
    return (
      <AppFrame>
        <LoadError
          title="Event tidak dapat dimuat"
          description={error instanceof Error ? error.message : "Terjadi kesalahan saat memuat event."}
        />
      </AppFrame>
    );
  }

  const event = detail.event;

  return (
    <AppFrame>
      <div className="flex flex-col gap-6">
        <PageHeader
          title="Edit Event"
          description="Perbarui informasi, jadwal, dan status event."
          action={<ActionLink href={`/admin/events/${event.id}`}>Kembali</ActionLink>}
        />

        <Card className="p-5 sm:p-6">
          <EventForm
            mode="edit"
            event={event}
            categories={categories.data}
            action={updateEventAction.bind(null, event.id)}
          />
        </Card>
      </div>
    </AppFrame>
  );
}
