import Link from "next/link";
import { AppFrame } from "@/components/app-frame";
import { apiFetch } from "@/lib/api";
import { Badge, Card, EmptyState, PageHeader } from "@/components/kit/ui";
import { ActionLink, LoadError } from "@/components/jury/chrome";
import type { EventsResponse } from "@/components/jury/types";

export const metadata = { title: "Event untuk Penilaian — JURI.ON" };
export const instant = false;

export default async function JuryEventsPage({
  searchParams,
}: {
  searchParams: Promise<{ page?: string }>;
}) {
  const { page } = await searchParams;
  const currentPage = Math.max(1, Number(page ?? 1) || 1);

  let data: EventsResponse | null = null;
  let loadError: string | null = null;

  try {
    data = await apiFetch<EventsResponse>(`/jury/events?page=${currentPage}`);
  } catch (error) {
    loadError = error instanceof Error ? error.message : "Terjadi kesalahan saat memuat daftar event.";
  }

  if (!data) {
    return (
      <AppFrame>
        <LoadError title="Event tidak dapat dimuat" description={loadError ?? "Terjadi kesalahan saat memuat daftar event."} />
      </AppFrame>
    );
  }

  return (
    <AppFrame>
      <div className="space-y-6">
        <PageHeader
          title="Event untuk Penilaian"
          description="Pilih event untuk memberikan penilaian kepada peserta."
        />

        {data.events.length === 0 ? (
          <Card>
            <EmptyState
              title="Tidak Ada Event Aktif"
              description="Event yang statusnya aktif akan muncul di sini untuk dinilai."
            />
          </Card>
        ) : (
          <div className="grid gap-6">
            {data.events.map((event) => (
              <Card key={event.id} className="p-5">
                <div className="flex flex-wrap items-start justify-between gap-6">
                  <div className="min-w-0 flex-1">
                    <h2 className="text-xl font-semibold text-[var(--foreground)]">{event.name}</h2>

                    <div className="mt-3 flex flex-wrap gap-2">
                      {event.category ? <Badge tone="info">{event.category}</Badge> : null}
                      <Badge tone="success">{event.status_label}</Badge>
                      {!event.scoring_open ? (
                        <Badge tone="warning">
                          {event.scoring_mode === "closed"
                            ? "Penilaian ditutup panitia"
                            : "Penilaian dibuka pada hari H"}
                        </Badge>
                      ) : event.pending_count > 0 ? (
                        <Badge tone="warning">{event.pending_count} peserta menunggu nilai</Badge>
                      ) : (
                        <Badge tone="success">Semua peserta sudah dinilai</Badge>
                      )}
                    </div>

                    <p className="mt-3 text-sm leading-relaxed text-[var(--text-secondary)]">
                      {event.description_excerpt}
                    </p>

                    {event.assigned_form ? (
                      <div className="mt-4 rounded-xl border-l-4 border-emerald-500 bg-emerald-50 px-4 py-3">
                        <p className="flex flex-wrap items-center gap-2 text-sm">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" className="h-4 w-4 text-emerald-600" aria-hidden="true">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M9 5h6m-7 4h8m-8 4h5m-5 4h8M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                          </svg>
                          <strong className="text-emerald-700">Form Penilaian Anda:</strong>
                          <span className="font-medium text-[var(--foreground)]">{event.assigned_form.name || "Form Juri"}</span>
                          <Link
                            href={`/jury/events/${event.id}/form`}
                            className="text-xs font-semibold text-emerald-700 underline hover:text-emerald-800 sm:ml-auto"
                          >
                            Lihat isi form
                          </Link>
                        </p>
                      </div>
                    ) : null}

                    <div className="mt-4 grid gap-3 sm:grid-cols-3">
                      <div className="rounded-xl bg-[var(--surface-muted)] px-4 py-3 ring-1 ring-[var(--border)]">
                        <p className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">Penyelenggara</p>
                        <p className="mt-1 text-sm font-semibold text-[var(--foreground)]">{event.organizer}</p>
                      </div>
                      <div className="rounded-xl bg-[var(--surface-muted)] px-4 py-3 ring-1 ring-[var(--border)]">
                        <p className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">Hari Pelaksanaan</p>
                        <p className="mt-1 text-sm font-semibold text-[var(--foreground)]">{event.event_date_label}</p>
                      </div>
                      <div className="rounded-xl bg-[var(--surface-muted)] px-4 py-3 ring-1 ring-[var(--border)]">
                        <p className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">Total Peserta</p>
                        <p className="mt-1 text-sm font-semibold text-[var(--foreground)]">{event.participants_count} peserta</p>
                        {event.verified_count > 0 ? (
                          <p className="mt-1 text-xs font-medium text-emerald-700">{event.verified_count} terverifikasi</p>
                        ) : (
                          <p className="mt-1 text-xs text-[var(--text-muted)]">Belum ada yang terverifikasi</p>
                        )}
                      </div>
                    </div>
                  </div>

                  {event.banner_url ? (
                    <div className="w-full shrink-0 sm:w-auto">
                      <img
                        src={event.banner_url}
                        alt={event.name}
                        className="h-40 w-full rounded-xl object-cover shadow-sm ring-1 ring-[var(--border)] sm:w-60"
                      />
                    </div>
                  ) : null}
                </div>

                <div className="mt-5 flex flex-wrap gap-2 border-t border-[var(--border)] pt-4">
                  {event.scoring_open ? (
                    <ActionLink href={`/jury/events/${event.id}/participants`} variant="primary">
                      {event.pending_count > 0 ? `Nilai ${event.pending_count} Peserta` : "Lihat Peserta"}
                    </ActionLink>
                  ) : (
                    <span
                      className="inline-flex h-10 cursor-not-allowed items-center justify-center rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white opacity-60"
                      aria-disabled="true"
                      title="Penilaian belum dibuka"
                    >
                      Belum Bisa Menilai
                    </span>
                  )}
                  <ActionLink href={`/events/${event.id}/chat`} variant="outline">
                    Chat Event
                  </ActionLink>
                  <ActionLink href={`/jury/events/${event.id}/leaderboard`} variant="success">
                    Lihat Leaderboard
                  </ActionLink>
                  <ActionLink href={`/events/${event.id}`} variant="outline">
                    Detail Event
                  </ActionLink>
                </div>
              </Card>
            ))}
          </div>
        )}

        {data.meta.last_page > 1 ? (
          <nav className="flex items-center justify-center gap-3 text-sm" aria-label="Navigasi halaman">
            {data.meta.current_page > 1 ? (
              <Link
                href={`/jury/events?page=${data.meta.current_page - 1}`}
                className="rounded-lg border border-[var(--border)] px-3 py-1.5 font-medium text-[var(--foreground)] hover:bg-[var(--surface-muted)]"
              >
                Sebelumnya
              </Link>
            ) : null}
            <span className="text-xs text-[var(--text-muted)]">
              Halaman {data.meta.current_page} dari {data.meta.last_page}
            </span>
            {data.meta.current_page < data.meta.last_page ? (
              <Link
                href={`/jury/events?page=${data.meta.current_page + 1}`}
                className="rounded-lg border border-[var(--border)] px-3 py-1.5 font-medium text-[var(--foreground)] hover:bg-[var(--surface-muted)]"
              >
                Berikutnya
              </Link>
            ) : null}
          </nav>
        ) : null}
      </div>
    </AppFrame>
  );
}
