import { apiFetch } from "@/lib/api";
import { navSectionsFor } from "@/lib/nav";
import { AppShell, type SessionUser } from "./app-shell";

type MeResponse = { user: SessionUser & { role: string; event_scope?: string } };
type NotificationsResponse = { unread_count: number };

export async function AppFrame({ children }: { children: React.ReactNode }) {
  const data = await apiFetch<MeResponse>("/me");
  const notifications = await apiFetch<NotificationsResponse>("/notifications").catch(() => null);

  return (
    <AppShell
      user={data.user}
      sections={navSectionsFor(data.user.role, data.user.event_scope ?? "all")}
      unreadCount={notifications?.unread_count ?? 0}
    >
      {children}
    </AppShell>
  );
}
