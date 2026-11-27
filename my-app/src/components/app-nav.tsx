"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import type { ComponentType } from "react";
import {
  Award,
  CalendarDays,
  CirclePlus,
  LayoutDashboard,
  Receipt,
  Ticket,
  UserRound,
  UsersRound,
  Wallet,
  type LucideProps,
} from "lucide-react";
import type { NavIcon, NavSection } from "@/lib/nav";

const ICONS: Record<NavIcon, ComponentType<LucideProps>> = {
  dashboard: LayoutDashboard,
  calendar: CalendarDays,
  plus: CirclePlus,
  wallet: Wallet,
  receipt: Receipt,
  award: Award,
  users: UsersRound,
  user: UserRound,
  bell: LayoutDashboard,
  ticket: Ticket,
};

export function AppNav({ sections }: { sections: NavSection[] }) {
  const pathname = usePathname();

  return (
    <nav className="mt-6 flex flex-col gap-5" aria-label="Navigasi utama">
      {sections.map((section) => (
        <div key={section.title}>
          <p className="px-3 text-[10px] font-semibold uppercase tracking-[0.18em] text-white/35">{section.title}</p>
          <div className="mt-2 flex flex-col gap-0.5">
            {section.items.map((item) => {
              const active =
                pathname === item.href ||
                (item.href !== "/dashboard" && pathname.startsWith(item.href.split("?")[0]));
              const Icon = ICONS[item.icon] ?? LayoutDashboard;

              return (
                <Link
                  key={item.href}
                  href={item.href}
                  aria-current={active ? "page" : undefined}
                  className={
                    active
                      ? "group flex items-center gap-3 rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white ring-1 ring-inset ring-white/10"
                      : "group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-white/60 transition hover:bg-white/5 hover:text-white"
                  }
                >
                  <span
                    className={
                      active
                        ? "grid h-7 w-7 place-items-center rounded-lg bg-indigo-400/20 text-indigo-200"
                        : "grid h-7 w-7 place-items-center rounded-lg bg-white/5 text-white/45 group-hover:text-white/70"
                    }
                  >
                    <Icon size={15} strokeWidth={1.8} aria-hidden="true" />
                  </span>
                  <span className="min-w-0 flex-1 truncate">{item.label}</span>
                </Link>
              );
            })}
          </div>
        </div>
      ))}
    </nav>
  );
}
