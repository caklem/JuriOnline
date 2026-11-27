import Link from "next/link";
import type { ReactNode } from "react";
import { Bell, Sparkles } from "lucide-react";
import { UserMenu } from "./user-menu";
import { AppNav } from "./app-nav";
import { BrandLockup } from "./brand/lockup";
import { CommandMenu } from "./command-menu";
import { Breadcrumbs } from "./breadcrumbs";
import type { NavSection } from "@/lib/nav";

export type SummaryItem = { label: string; value: number; hint?: string };
export type SessionUser = { name: string; email: string; role_label: string };

const ROLE_HEADLINE: Record<string, string> = {
  "Admin Owner": "Admin Owner",
  "Admin": "Admin",
  "Admin Event": "Admin Event",
  "Juri": "Juri",
  "Peserta": "Peserta",
};

export function AppShell({
  user,
  sections,
  unreadCount = 0,
  children,
}: {
  user: SessionUser;
  sections: NavSection[];
  unreadCount?: number;
  summary?: SummaryItem[];
  children?: ReactNode;
}) {
  const isAdmin = user.role_label === "Admin" || user.role_label === "Admin Owner" || user.role_label === "Admin Event";

  return (
    <div className="min-h-screen bg-[linear-gradient(180deg,#f8fafc_0%,#f1f5f9_100%)]">
      <div className="flex min-h-screen">
        <aside className="sticky top-0 hidden h-screen w-[264px] shrink-0 flex-col bg-[linear-gradient(180deg,#0b1220_0%,#111827_100%)] px-4 py-5 lg:flex">
          <div className="px-2 text-white">
            <BrandLockup href="/dashboard" variant="dark" />
          </div>

          <div className="mt-6 rounded-2xl border border-white/10 bg-white/[0.04] p-3">
            <p className="text-[10px] font-semibold uppercase tracking-[0.18em] text-white/35">Ruang kerja</p>
            <p className="mt-1 truncate text-sm font-semibold text-white">{ROLE_HEADLINE[user.role_label] ?? user.role_label}</p>
            <p className="mt-0.5 truncate text-xs text-white/45">{user.email}</p>
            {isAdmin ? (
              <Link
                href="/admin/events"
                className="mt-3 inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-white/10 px-2.5 py-1.5 text-[11px] font-semibold text-white/80 transition hover:bg-white/15 hover:text-white"
              >
                <Sparkles size={12} aria-hidden="true" />
                Ganti event
              </Link>
            ) : null}
          </div>

          <div className="min-h-0 flex-1 overflow-y-auto pb-4">
            <AppNav sections={sections} />
          </div>

          <Link
            href="/"
            className="mt-2 flex items-center justify-between rounded-xl border border-white/10 px-3 py-2.5 text-xs font-medium text-white/55 transition hover:bg-white/5 hover:text-white"
          >
            Lihat situs
            <span aria-hidden="true">→</span>
          </Link>
        </aside>

        <div className="flex min-w-0 flex-1 flex-col">
          <header className="sticky top-0 z-30 border-b border-[var(--border)] bg-white/80 backdrop-blur">
            <div className="flex items-center gap-3 px-4 py-3 sm:px-6">
              <div className="min-w-0 flex-1">
                <p className="truncate text-[10px] font-semibold uppercase tracking-[0.18em] text-[var(--text-muted)]">
                  {ROLE_HEADLINE[user.role_label] ?? "Ruang kerja"}
                </p>
                <Breadcrumbs />
              </div>

              <CommandMenu sections={sections} />

              <Link
                href="/notifications"
                aria-label="Notifikasi"
                className="relative grid h-10 w-10 place-items-center rounded-xl border border-[var(--border)] bg-[var(--background)] text-[var(--text-secondary)] transition hover:bg-[var(--surface-muted)]"
              >
                <Bell size={17} strokeWidth={1.8} aria-hidden="true" />
                {unreadCount > 0 ? (
                  <span className="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">
                    {unreadCount > 9 ? "9+" : unreadCount}
                  </span>
                ) : null}
              </Link>

              <UserMenu name={user.name} role={user.role_label} />
            </div>
          </header>

          <main className="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            <div className="mx-auto w-full max-w-[1240px]">
              {children ?? null}
            </div>
          </main>
        </div>
      </div>
    </div>
  );
}
