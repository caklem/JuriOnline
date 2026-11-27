import Link from "next/link";
import { apiFetch } from "@/lib/api";
import { PublicShell } from "@/components/public/public-shell";
import { Badge, Card, CardHeader } from "@/components/kit/ui";
import { formatDate, formatRupiah } from "@/components/admin/format";
import { BackLink } from "@/components/admin/ui";
import type { EventSummary } from "@/components/admin/types";

export const metadata = { title: "Preview Event — JURI.ON" };
export const instant = false;

type Response = { event: EventSummary; stats: { registrations: number } };

export default async function EventPreviewPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;

  let data: Response;

  try {
    data = await apiFetch<Response>(`/admin/events/${id}`);
  } catch (error) {
    return (
      <div className="mx-auto max-w-3xl px-6 py-16">
        <Card className="p-6 text-sm text-rose-700">
          {error instanceof Error ? error.message : "Preview tidak dapat dimuat."}
        </Card>
      </div>
    );
  }

  const event = data.event;
  const available = event.available_slots;

  return (
    <PublicShell>
      <div className="mx-auto max-w-6xl px-6 py-8 lg:px-8">
        <div className="mb-5 flex flex-wrap items-center justify-between gap-3">
          <BackLink href={`/admin/events/${event.id}`}>Kembali ke Detail Event</BackLink>
          <Badge tone="warning">Mode Preview — belum dipublikasikan</Badge>
        </div>

        {event.banner_url ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img src={event.banner_url} alt={event.name} className="h-64 w-full rounded-3xl object-cover sm:h-80" />
        ) : (
          <div className="grid h-64 w-full place-items-center rounded-3xl bg-slate-900 text-sm text-white/60 sm:h-80">
            Banner event belum diunggah
          </div>
        )}

        <div className="mt-8 grid gap-6 lg:grid-cols-[1fr_340px]">
          <div>
            <div className="flex flex-wrap gap-2">
              {event.category ? <Badge tone="info">{event.category.name}</Badge> : null}
              <Badge tone="neutral">{event.format ?? "offline"}</Badge>
            </div>
            <h1 className="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{event.name}</h1>
            <p className="mt-2 text-sm text-slate-500">
              {event.organizer?.name ?? "Penyelenggara"} · {event.location || "Online"} · {formatDate(event.event_date ?? event.event_start)}
            </p>
            <div className="prose prose-slate mt-6 max-w-none whitespace-pre-line text-sm leading-7 text-slate-700">
              {event.description}
            </div>
          </div>

          <aside className="space-y-4">
            <Card>
              <CardHeader title="Informasi Event" />
              <dl className="space-y-3 p-5 text-sm">
                <div className="flex justify-between gap-3"><dt className="text-slate-500">Harga</dt><dd className="font-semibold text-slate-900">{formatRupiah(event.price)}</dd></div>
                <div className="flex justify-between gap-3"><dt className="text-slate-500">Kuota</dt><dd className="font-semibold text-slate-900">{available} dari {event.quota} tersisa</dd></div>
                <div className="flex justify-between gap-3"><dt className="text-slate-500">Pendaftaran</dt><dd className="text-right text-slate-700">{formatDate(event.registration_start)} – {formatDate(event.registration_end)}</dd></div>
                <div className="flex justify-between gap-3"><dt className="text-slate-500">Zona waktu</dt><dd className="text-slate-700">{event.timezone ?? "Asia/Jakarta"}</dd></div>
                {event.contact_name || event.contact_email || event.contact_phone ? (
                  <div className="border-t border-slate-100 pt-3">
                    <dt className="text-slate-500">Kontak panitia</dt>
                    <dd className="mt-1 text-slate-800">{event.contact_name}</dd>
                    <dd className="text-xs text-slate-500">{event.contact_email} {event.contact_phone}</dd>
                  </div>
                ) : null}
              </dl>
            </Card>

            <Card className="border-dashed p-5">
              <p className="text-sm font-semibold text-slate-900">Mode preview</p>
              <p className="mt-1 text-xs leading-5 text-slate-500">
                Peserta belum dapat mendaftar dari halaman ini. Aktifkan event untuk membuka pendaftaran dan URL publik.
              </p>
              <div className="mt-4 flex flex-wrap gap-2">
                <Link href={`/admin/events/${event.id}/edit`} className="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white">
                  Edit Event
                </Link>
                {event.slug ? (
                  <a href={`/events/${event.slug}`} target="_blank" rel="noopener" className="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700">
                    Buka URL Publik
                  </a>
                ) : null}
              </div>
            </Card>
          </aside>
        </div>
      </div>
    </PublicShell>
  );
}
