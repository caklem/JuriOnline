import Link from "next/link";
import { CalendarDays, CheckCircle2, Sparkles, UsersRound, Wallet } from "lucide-react";
import { Badge, Card, CardHeader, EmptyState, Table } from "@/components/kit/ui";
import { Button } from "@/components/arc/button/button";

type Stat = { label: string; value: number };
type RecentEvent = {
  id: number;
  name: string;
  status: string;
  status_label: string;
  registered_count: number;
  quota: number;
  organizer: string | null;
  event_date: string | null;
};

export type AdminDashboardData = {
  can_manage_all: boolean;
  stats: Stat[];
  recent_events: RecentEvent[];
  event_quota?: {
    limit: number | null;
    used: number;
    remaining: number | null;
    can_create: boolean;
    unlimited: boolean;
  };
  organizer_profile_complete?: boolean;
};

function tone(status: string): "success" | "warning" | "info" | "danger" | "neutral" {
  if (status === "active") return "success";
  if (status === "draft") return "warning";
  if (status === "closed") return "danger";
  if (status === "completed") return "info";
  return "neutral";
}

export function AdminDashboard({
  data,
  userName,
  roleLabel,
}: {
  data: AdminDashboardData;
  userName: string;
  roleLabel: string;
}) {
  const pending = data.stats.find((stat) => stat.label === "Pembayaran Pending")?.value ?? 0;
  const quota = data.event_quota;
  const quotaFull = quota ? !quota.can_create : false;

  return (
    <>
      <section className="relative overflow-hidden rounded-3xl bg-[linear-gradient(120deg,#0b1220_0%,#1e1b4b_55%,#312e81_100%)] px-6 py-8 text-white sm:px-8">
        <div className="pointer-events-none absolute -right-16 -top-24 h-64 w-64 rounded-full bg-indigo-400/20 blur-3xl" aria-hidden="true" />
        <div className="pointer-events-none absolute -bottom-24 left-1/3 h-56 w-56 rounded-full bg-fuchsia-400/10 blur-3xl" aria-hidden="true" />
        <div className="relative flex flex-wrap items-start justify-between gap-6">
          <div className="min-w-0">
            <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/45">Ringkasan platform</p>
            <h1 className="mt-2 text-3xl font-semibold tracking-tight">Dashboard {roleLabel}</h1>
            <p className="mt-2 max-w-xl text-sm leading-6 text-white/65">
              Selamat datang, {userName}. {data.can_manage_all ? "Kelola seluruh event dan sistem dari sini." : "Kelola event yang Anda selenggarakan."}
            </p>
          </div>
          <div className="flex flex-wrap gap-2">
            {quotaFull ? (
              <Button variant="secondary" disabled>Kuota Event Penuh</Button>
            ) : (
              <Link href="/admin/events/create">
                <Button variant="secondary">Buat Event Baru</Button>
              </Link>
            )}
            <Link href="/admin/payments">
              <Button variant="secondary">Verifikasi Pembayaran</Button>
            </Link>
          </div>
        </div>
      </section>

      <div className="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        {data.stats.map((stat, index) => {
          const icons = [CalendarDays, CheckCircle2, Wallet, UsersRound];
          const Icon = icons[index] ?? Sparkles;
          return (
            <div key={stat.label} className="rounded-2xl border border-[var(--border)] bg-[var(--background)] p-5">
              <div className="flex items-start justify-between gap-3">
                <p className="text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">{stat.label}</p>
                <span className="grid h-9 w-9 place-items-center rounded-xl bg-[var(--surface-muted)] text-[var(--text-secondary)]">
                  <Icon size={17} strokeWidth={1.8} aria-hidden="true" />
                </span>
              </div>
              <p className="mt-3 text-3xl font-semibold tabular-nums text-[var(--foreground)]">{stat.value}</p>
            </div>
          );
        })}
      </div>

      {quota ? (
        <div className={`mt-4 flex flex-wrap items-center justify-between gap-4 rounded-2xl border px-5 py-4 ${quotaFull ? "border-amber-200 bg-amber-50" : "border-[var(--border)] bg-[var(--background)]"}`}>
          <div>
            <p className="text-sm font-semibold text-[var(--foreground)]">
              Kuota event {quota.unlimited ? "tanpa batas" : `${quota.used} / ${quota.limit}`}
            </p>
            <p className="mt-1 text-xs text-[var(--text-secondary)]">
              {quota.unlimited
                ? "Akun ini dapat menyelenggarakan event tanpa batas."
                : quotaFull
                  ? "Kuota tersimpan sudah penuh. Selesaikan atau arsipkan event sebelum membuat event baru."
                  : `Sisa ${quota.remaining} event. Semua event tersimpan (termasuk draft) dihitung.`}
            </p>
          </div>
          {data.organizer_profile_complete === false ? (
            <Link href="/admin/organizer" className="text-xs font-semibold text-[var(--text-secondary)] underline underline-offset-2">
              Lengkapi profil organizer
            </Link>
          ) : null}
        </div>
      ) : null}

      <div className="mt-2 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        {quotaFull ? (
          <div className="rounded-xl border border-[var(--border)] bg-[var(--surface-muted)] p-4 opacity-70">
            <p className="text-sm font-semibold text-[var(--foreground)]">Buat Event</p>
            <p className="mt-1 text-xs text-[var(--text-secondary)]">Kuota event tersimpan sudah penuh.</p>
          </div>
        ) : (
          <Link href="/admin/events/create" className="rounded-xl border border-[var(--border)] bg-[var(--background)] p-4 transition hover:border-[var(--foreground)]/20 hover:shadow-sm">
            <p className="text-sm font-semibold text-[var(--foreground)]">Buat Event</p>
            <p className="mt-1 text-xs text-[var(--text-secondary)]">Mulai event baru dalam beberapa menit.</p>
          </Link>
        )}
        <Link href="/admin/events" className="rounded-xl border border-[var(--border)] bg-[var(--background)] p-4 transition hover:border-[var(--foreground)]/20 hover:shadow-sm">
          <p className="text-sm font-semibold text-[var(--foreground)]">Event Saya</p>
          <p className="mt-1 text-xs text-[var(--text-secondary)]">Lihat seluruh event yang Anda kelola.</p>
        </Link>
        <Link href="/admin/payments" className="rounded-xl border border-[var(--border)] bg-[var(--background)] p-4 transition hover:border-[var(--foreground)]/20 hover:shadow-sm">
          <p className="text-sm font-semibold text-[var(--foreground)]">Verifikasi Pembayaran</p>
          <p className="mt-1 text-xs text-[var(--text-secondary)]">Periksa bukti transfer peserta.</p>
        </Link>
        {data.can_manage_all ? (
          <Link href="/admin/payments/all" className="rounded-xl border border-[var(--border)] bg-[var(--background)] p-4 transition hover:border-[var(--foreground)]/20 hover:shadow-sm">
            <p className="text-sm font-semibold text-[var(--foreground)]">Semua Pembayaran</p>
            <p className="mt-1 text-xs text-[var(--text-secondary)]">Riwayat pembayaran seluruh event.</p>
          </Link>
        ) : null}
      </div>

      <Card className="mt-2">
        <CardHeader
          title="Event Terbaru"
          description="Pantau event terbaru beserta status dan jumlah pesertanya."
          action={
            <Link href="/admin/events" className="text-sm font-semibold text-[var(--text-secondary)] underline underline-offset-2">
              {data.can_manage_all ? "Semua event" : "Event Saya"}
            </Link>
          }
        />
        {data.recent_events.length === 0 ? (
          <EmptyState
            title="Belum Ada Event"
            description="Belum ada event yang dibuat. Mulai dengan membuat event pertama Anda."
            action={
              <Link href="/admin/events/create">
                <Button variant="primary">Buat Event Baru</Button>
              </Link>
            }
          />
        ) : (
          <Table head={["Event", "Penyelenggara", "Status", "Peserta", "Aksi"]}>
            {data.recent_events.map((event) => (
              <tr key={event.id}>
                <td className="px-5 py-3 font-semibold text-[var(--foreground)]">{event.name}</td>
                <td className="px-5 py-3 text-[var(--text-secondary)]">{event.organizer ?? "-"}</td>
                <td className="px-5 py-3"><Badge tone={tone(event.status)}>{event.status_label}</Badge></td>
                <td className="px-5 py-3 tabular-nums text-[var(--text-secondary)]">
                  {event.registered_count} / {event.quota}
                </td>
                <td className="px-5 py-3">
                  <Link href={`/admin/events/${event.id}`} className="inline-flex items-center rounded-lg border border-[var(--border)] px-3 py-1.5 text-xs font-semibold text-[var(--text-secondary)] transition hover:bg-[var(--surface-muted)]">
                    Detail
                  </Link>
                </td>
              </tr>
            ))}
          </Table>
        )}
      </Card>

      {pending > 0 ? (
        <div className="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
          <strong>{pending} pembayaran menunggu verifikasi.</strong>{" "}
          <Link href="/admin/payments" className="font-semibold underline underline-offset-2">Buka verifikasi pembayaran</Link>.
        </div>
      ) : null}
    </>
  );
}
