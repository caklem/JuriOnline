import Link from "next/link";
import type { ReactNode } from "react";
import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Badge, Card, CardHeader, PageHeader } from "@/components/kit/ui";
import { ActionButton } from "@/components/admin/action-button";
import { ActionLink, BackLink } from "@/components/admin/ui";
import { EventNav } from "@/components/admin/event-nav";
import { LoadError } from "@/components/admin/load-error";
import { formatDate, formatRupiah } from "@/components/admin/format";
import type { EventSummary } from "@/components/admin/types";
import { updateEventStatusAction, updateScoringAction, duplicateEventAction, archiveEventAction } from "../actions";

export const metadata = { title: "Detail Event — JURI.ON" };

export const instant = false;

type EventDetailResponse = {
  event: EventSummary;
  stats: {
    registrations: number;
    verified: number;
    pending: number;
    rejected: number;
    payments_pending: number;
    juries: number;
  };
  has_event_form: boolean;
  jury_form_templates: { id: number; name: string }[];
};

const STATUS_TONE: Record<string, "success" | "warning" | "danger" | "info" | "neutral"> = {
  active: "success",
  draft: "warning",
  closed: "danger",
  completed: "info",
  archived: "neutral",
};

const TILE =
  "group flex items-start gap-3 rounded-xl border border-[var(--border)] bg-[var(--background)] p-4 transition hover:-translate-y-0.5 hover:border-[var(--foreground)]/20 hover:shadow-sm";

function TileLink({
  href,
  title,
  description,
}: {
  href: string;
  title: string;
  description: string;
}) {
  return (
    <ActionLinkCard href={href}>
      <span className="block text-sm font-semibold text-[var(--foreground)]">{title}</span>
      <span className="mt-0.5 block text-xs text-[var(--text-secondary)]">{description}</span>
    </ActionLinkCard>
  );
}

function ActionLinkCard({ href, children }: { href: string; children: ReactNode }) {
  return (
    <Link href={href} className={TILE}>
      <span className="min-w-0 flex-1">{children}</span>
      <svg
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="1.8"
        className="ml-auto mt-1 h-4 w-4 shrink-0 text-[var(--text-muted)] transition group-hover:text-[var(--foreground)]"
        aria-hidden="true"
      >
        <path strokeLinecap="round" strokeLinejoin="round" d="M5 12h14m-5-5 5 5-5 5" />
      </svg>
    </Link>
  );
}

export default async function EventDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  let data: EventDetailResponse;

  try {
    data = await apiFetch<EventDetailResponse>(`/admin/events/${id}`);
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

  const event = data.event;

  const scoringFootnote =
    event.scoring_mode === "auto"
      ? " · penilaian terbuka otomatis sejak tanggal tersebut."
      : event.scoring_mode === "open"
        ? " · dibuka manual oleh panitia, juri sudah bisa menilai."
        : " · ditutup manual, juri tidak bisa menilai sampai dibuka.";

  return (
    <AppFrame>
      <div className="flex flex-col gap-6">
        {event.banner_url ? (
          <Card className="overflow-hidden">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img
              src={event.banner_url}
              alt={event.name}
              className="h-56 w-full object-cover object-top sm:h-72 lg:h-80"
            />
          </Card>
        ) : null}

        <div>
          <BackLink href="/admin/events">Kembali ke daftar event</BackLink>
          <PageHeader
            title={event.name}
            action={
              <div className="flex flex-wrap items-center gap-2">
                {event.category ? <Badge tone="info">{event.category.name}</Badge> : null}
                <Badge tone={STATUS_TONE[event.status] ?? "neutral"}>{event.status_label}</Badge>
              </div>
            }
          />
        </div>

        <EventNav eventId={event.id} />

        <Card>
          <CardHeader
            title="Identitas Publik"
            description="URL publik event, format pelaksanaan, dan kontak resmi yang dilihat peserta."
            action={<Badge tone={event.archived_at ? "neutral" : "success"}>{event.archived_at ? "Diarsipkan" : "Publik"}</Badge>}
          />
          <div className="grid gap-4 p-5 lg:grid-cols-[1fr_auto]">
            <div className="flex flex-col gap-3 text-sm">
              <div>
                <p className="text-[11px] font-semibold uppercase tracking-wide text-[var(--text-muted)]">URL Publik</p>
                {event.slug ? (
                  <a href={event.public_url ?? `/events/${event.slug}`} target="_blank" rel="noopener" className="mt-1 inline-block break-all font-semibold text-indigo-700 underline underline-offset-2">
                    {event.public_url ?? `/events/${event.slug}`}
                  </a>
                ) : (
                  <p className="mt-1 text-[var(--text-secondary)]">Slug belum dibuat.</p>
                )}
              </div>
              <dl className="grid gap-3 sm:grid-cols-3">
                <div>
                  <dt className="text-[11px] font-semibold uppercase tracking-wide text-[var(--text-muted)]">Format</dt>
                  <dd className="mt-1 font-medium capitalize">{event.format ?? "offline"}</dd>
                </div>
                <div>
                  <dt className="text-[11px] font-semibold uppercase tracking-wide text-[var(--text-muted)]">Zona Waktu</dt>
                  <dd className="mt-1 font-medium">{event.timezone ?? "Asia/Jakarta"}</dd>
                </div>
                <div>
                  <dt className="text-[11px] font-semibold uppercase tracking-wide text-[var(--text-muted)]">Kontak</dt>
                  <dd className="mt-1 font-medium">{event.contact_name ?? "-"}</dd>
                  <dd className="text-xs text-[var(--text-secondary)]">{event.contact_email ?? event.contact_phone ?? ""}</dd>
                </div>
              </dl>
              <div className="flex flex-wrap gap-2">
                <ActionLink href={`/admin/events/${event.id}/preview`}>Preview Halaman Publik</ActionLink>
                <ActionButton action={duplicateEventAction.bind(null, event.id)} variant="secondary" confirm="Duplikasi event ini? Salinan dibuat sebagai draft dan menghitung kuota event.">
                  Duplikasi Event
                </ActionButton>
                {event.status !== "archived" ? (
                  <ActionButton action={archiveEventAction.bind(null, event.id)} variant="danger" confirm="Arsipkan event ini? Event tidak tampil publik, tetapi tetap dihitung dalam kuota 3 event.">
                    Arsipkan
                  </ActionButton>
                ) : null}
              </div>
            </div>
            <div className="justify-self-center rounded-xl border border-[var(--border)] bg-white p-3">
              {event.slug ? <EventQr slug={event.slug} /> : null}
            </div>
          </div>
        </Card>

        <Card>
          <CardHeader
            title="Penilaian Peserta"
            description="Buka penilaian lebih awal, tutup sementara, atau biarkan otomatis terbuka sejak hari H."
            action={
              <Badge tone={event.scoring_open ? "success" : "warning"}>{event.scoring_status_label}</Badge>
            }
          />
          <div className="flex flex-col gap-4 p-5">
            <div className="grid gap-3 sm:grid-cols-3">
              <ActionButton
                action={updateScoringAction.bind(null, event.id, "open")}
                variant="primary"
                disabled={event.scoring_mode === "open"}
                fullWidth
              >
                {event.scoring_mode === "open" ? "Penilaian Sudah Dibuka" : "Buka Penilaian Sekarang"}
              </ActionButton>
              <ActionButton
                action={updateScoringAction.bind(null, event.id, "closed")}
                variant="danger"
                disabled={event.scoring_mode === "closed"}
                fullWidth
              >
                {event.scoring_mode === "closed" ? "Penilaian Sedang Ditutup" : "Tutup Penilaian"}
              </ActionButton>
              <ActionButton
                action={updateScoringAction.bind(null, event.id, "auto")}
                variant="secondary"
                disabled={event.scoring_mode === "auto"}
                fullWidth
              >
                {event.scoring_mode === "auto" ? "Mode Otomatis Aktif" : "Otomatis (Mulai Hari H)"}
              </ActionButton>
            </div>

            <p className="text-xs text-[var(--text-secondary)]">
              Hari H:{" "}
              <strong className="text-[var(--foreground)]">
                {event.scoring_opens_at ? formatDate(event.scoring_opens_at) : "belum diisi"}
              </strong>
              {scoringFootnote}
            </p>
          </div>
        </Card>

        <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
          <TileLink href={`/admin/events/${event.id}/edit`} title="Edit Event" description="Perbarui informasi dan jadwal" />
          <TileLink
            href={`/admin/events/${event.id}/participants`}
            title="Daftar Peserta"
            description={`${data.stats.registrations} Peserta Terdaftar`}
          />
          <TileLink
            href={`/admin/events/${event.id}/recap`}
            title="Rekapitulasi Nilai"
            description="Nilai akhir per form dan peringkat"
          />
          <TileLink
            href={`/admin/events/${event.id}/evaluations`}
            title="Review & Publikasi Evaluasi"
            description="Tinjau catatan juri sebelum terbit"
          />
          <TileLink
            href={`/admin/events/${event.id}/chat`}
            title="Chat Event"
            description="Komunikasi & moderasi pesan"
          />

          <TileLink
            href={`/admin/events/${event.id}/jury-assignments`}
            title="Assign Form ke Juri"
            description={`${data.stats.juries} juri terdaftar`}
          />
          <TileLink
            href={`/admin/events/${event.id}/form`}
            title="Form Pendaftaran"
            description="Atur kolom pendaftaran peserta"
          />
          <TileLink
            href={`/admin/events/${event.id}/jury-form`}
            title="Form Penilaian Juri"
            description="Atur kriteria penilaian juri"
          />
          <TileLink
            href={`/admin/payments?event=${event.id}`}
            title="Verifikasi Pembayaran"
            description="Periksa bukti bayar peserta"
          />
          <TileLink
            href={`/jury/events/${event.id}/leaderboard`}
            title="Leaderboard"
            description="Peringkat peserta saat ini"
          />
        </div>

        <div className="flex flex-wrap items-center gap-3">
          {event.status === "draft" ? (
            <ActionButton
              action={updateEventStatusAction.bind(null, event.id, "active")}
              variant="primary"
              confirm="Aktifkan event ini? Event akan terlihat oleh peserta."
            >
              Aktifkan Event
            </ActionButton>
          ) : event.status === "active" ? (
            <ActionButton
              action={updateEventStatusAction.bind(null, event.id, "closed")}
              variant="secondary"
              confirm="Tutup event ini? Peserta tidak bisa mendaftar lagi."
            >
              Tutup Event
            </ActionButton>
          ) : event.status === "closed" ? (
            <ActionButton
              action={updateEventStatusAction.bind(null, event.id, "completed")}
              variant="secondary"
              confirm="Tandai event ini sebagai selesai?"
            >
              Selesaikan Event
            </ActionButton>
          ) : null}
          {data.stats.payments_pending > 0 ? (
            <ActionLink href="/admin/payments">Verifikasi Pembayaran ({data.stats.payments_pending})</ActionLink>
          ) : null}
        </div>

        <div className="grid gap-6 lg:grid-cols-3">
          <Card className="lg:col-span-2">
            <CardHeader title="Deskripsi" />
            <p className="whitespace-pre-line p-5 text-sm leading-7 text-[var(--text-secondary)]">
              {event.description || "-"}
            </p>
          </Card>

          <Card>
            <CardHeader title="Informasi Event" />
            <dl className="flex flex-col gap-3 p-5">
              <div className="rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] p-3">
                <dt className="text-[11px] font-semibold uppercase tracking-wide text-[var(--text-muted)]">Penyelenggara</dt>
                <dd className="mt-1.5 text-sm font-medium text-[var(--foreground)]">{event.organizer?.name ?? "-"}</dd>
              </div>
              <div className="rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] p-3">
                {event.juknis_url ? (
                  <div className="rounded-xl border border-indigo-100 bg-indigo-50/60 p-3 sm:col-span-2">
                    <dt className="text-[11px] font-semibold uppercase tracking-wide text-indigo-600">Dokumen Juknis</dt>
                    <dd className="mt-1 flex flex-wrap items-center justify-between gap-2">
                      <span className="break-all text-sm font-medium text-[var(--foreground)]">{event.juknis_name ?? "Juknis event"}</span>
                      <a href={event.juknis_url} target="_blank" rel="noopener" className="text-xs font-semibold text-indigo-700 underline underline-offset-2">
                        Lihat Juknis
                      </a>
                    </dd>
                  </div>
                ) : null}

                <dt className="text-[11px] font-semibold uppercase tracking-wide text-[var(--text-muted)]">Lokasi</dt>
                <dd className="mt-1.5 text-sm font-medium text-[var(--foreground)]">{event.location ?? "Online"}</dd>
              </div>
              <div className="rounded-xl border border-indigo-100 bg-indigo-50/60 p-3">
                <dt className="text-[11px] font-semibold uppercase tracking-wide text-indigo-600">Harga</dt>
                <dd className="mt-1.5 text-xl font-semibold tracking-tight text-indigo-700">
                  {formatRupiah(event.price)}
                </dd>
              </div>
              <div className="rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] p-3">
                <dt className="text-[11px] font-semibold uppercase tracking-wide text-[var(--text-muted)]">Kuota</dt>
                <dd className="mt-1.5">
                  <div className="text-sm font-semibold text-[var(--foreground)]">
                    {event.registered_count} / {event.quota} peserta
                  </div>
                  <div className="mt-2">
                    {event.available_slots > 0 ? (
                      <Badge tone="success">{event.available_slots} slot tersisa</Badge>
                    ) : (
                      <Badge tone="danger">Kuota penuh</Badge>
                    )}
                  </div>
                </dd>
              </div>
              <div className="rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] p-3">
                <dt className="text-[11px] font-semibold uppercase tracking-wide text-[var(--text-muted)]">
                  Periode Pendaftaran
                </dt>
                <dd className="mt-1.5 text-sm text-[var(--foreground)]">
                  {formatDate(event.registration_start)}
                  <br />
                  s/d {formatDate(event.registration_end)}
                </dd>
              </div>
              <div className="rounded-xl border border-indigo-100 bg-indigo-50/60 p-3">
                <dt className="text-[11px] font-semibold uppercase tracking-wide text-indigo-600">Tanggal Hari H</dt>
                <dd className="mt-1.5 text-sm font-semibold text-indigo-900">
                  {formatDate(event.event_date ?? event.event_start)}
                </dd>
              </div>
              <div className="rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] p-3">
                <dt className="text-[11px] font-semibold uppercase tracking-wide text-[var(--text-muted)]">Periode Event</dt>
                <dd className="mt-1.5 text-sm text-[var(--foreground)]">
                  {formatDate(event.event_start)}
                  <br />
                  s/d {formatDate(event.event_end)}
                </dd>
              </div>
            </dl>
          </Card>
        </div>

        <div className="flex flex-wrap items-center gap-3">
          <ActionLink href="/admin/events">Kembali</ActionLink>
        </div>
      </div>
    </AppFrame>
  );
}

async function EventQr({ slug }: { slug: string }) {
  const QRCode = (await import("qrcode")).default;
  const target = `${process.env.NEXT_PUBLIC_APP_URL ?? "http://localhost:8081"}/events/${slug}`;
  const svg = await QRCode.toString(target, { type: "svg", margin: 1, width: 180 });

  return (
    <div className="flex flex-col items-center gap-2">
      <div className="h-[180px] w-[180px]" dangerouslySetInnerHTML={{ __html: svg }} />
      <p className="max-w-[180px] break-all text-center text-[10px] text-[var(--text-secondary)]">{target}</p>
    </div>
  );
}
