import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Badge, Card, EmptyState, PageHeader } from "@/components/kit/ui";
import { MarkReadButton } from "./mark-read-button";

export const metadata = { title: "Notifikasi — JURI.ON" };
export const instant = false;

type NotificationItem = {
  id: string;
  title: string;
  message: string;
  url: string;
  read_at: string | null;
  created_at: string | null;
};

type NotificationsResponse = { unread_count: number; notifications: NotificationItem[] };

function formatDate(value: string | null): string {
  if (!value) return "";
  return new Date(value).toLocaleString("id-ID", { dateStyle: "medium", timeStyle: "short" });
}

export default async function NotificationsPage() {
  const data = await apiFetch<NotificationsResponse>("/notifications");

  return (
    <AppFrame>
      <PageHeader
        eyebrow="Akun"
        title="Notifikasi"
        description="Pemberitahuan terbaru tentang undangan juri dan aktivitas event Anda."
        action={data.unread_count > 0 ? <MarkReadButton /> : null}
      />

      <Card className="mt-6">
        {data.notifications.length === 0 ? (
          <EmptyState title="Belum ada notifikasi baru." />
        ) : (
          <ul className="divide-y divide-[var(--border)]">
            {data.notifications.map((notification) => (
              <li key={notification.id} className="px-5 py-4">
                <a href={notification.url} className="block">
                  <div className="flex flex-wrap items-center gap-2">
                    <p className="text-sm font-semibold text-[var(--foreground)]">{notification.title}</p>
                    {!notification.read_at ? <Badge tone="info">Baru</Badge> : null}
                  </div>
                  <p className="mt-1 text-sm leading-6 text-[var(--text-secondary)]">{notification.message}</p>
                  <p className="mt-1 text-[11px] text-[var(--text-muted)]">{formatDate(notification.created_at)}</p>
                </a>
              </li>
            ))}
          </ul>
        )}
      </Card>
    </AppFrame>
  );
}
