import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Card, PageHeader } from "@/components/kit/ui";
import { ActionLink } from "@/components/admin/ui";
import { LoadError } from "@/components/admin/load-error";
import { EventForm } from "@/components/admin/event-form";
import type { CategoryOption } from "@/components/admin/types";
import { createEventAction } from "../actions";

export const metadata = { title: "Buat Event Baru — JURI.ON" };

export const instant = false;

export default async function CreateEventPage() {
  let categories: { data: CategoryOption[] };
  type Quota = { can_create: boolean; limit: number | null; used: number; remaining: number | null; unlimited: boolean };
  let quota: Quota | null = null;

  try {
    const [categoryData, dashboard] = await Promise.all([
      apiFetch<{ data: CategoryOption[] }>("/admin/categories"),
      apiFetch<{ event_quota: Quota }>("/admin/dashboard"),
    ]);
    categories = categoryData;
    quota = dashboard.event_quota;
  } catch (error) {
    return (
      <AppFrame>
        <LoadError
          title="Form event tidak dapat dimuat"
          description={error instanceof Error ? error.message : "Terjadi kesalahan saat memuat kategori."}
        />
      </AppFrame>
    );
  }

  return (
    <AppFrame>
      <div className="flex flex-col gap-6">
        <PageHeader
          title="Buat Event Baru"
          description="Lengkapi informasi event, periode pendaftaran, dan banner event Anda."
          action={<ActionLink href="/admin/events">Kembali</ActionLink>}
        />

        {quota && !quota.can_create ? (
          <div className="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
            <strong>Kuota event tersimpan sudah penuh{quota.limit ? ` (${quota.used}/${quota.limit})` : ""}.</strong>{" "}
            Event baru tidak dapat dibuat. Selesaikan atau arsipkan event lama terlebih dahulu.
          </div>
        ) : quota && !quota.unlimited ? (
          <div className="rounded-xl border border-[var(--border)] bg-[var(--background)] px-5 py-4 text-sm text-[var(--text-secondary)]">
            Kuota tersisa: <strong className="text-[var(--foreground)]">{quota.remaining}</strong> dari {quota.limit} event.
          </div>
        ) : null}
        <Card className="p-5 sm:p-6">
          <EventForm mode="create" categories={categories.data} action={createEventAction} />
        </Card>
      </div>
    </AppFrame>
  );
}
