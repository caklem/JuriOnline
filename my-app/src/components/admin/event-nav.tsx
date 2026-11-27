"use client";

import Link from "next/link";
import { usePathname, useSearchParams } from "next/navigation";

export function EventNav({ eventId }: { eventId: number }) {
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const base = `/admin/events/${eventId}`;

  const items = [
    { href: base, label: "Detail" },
    { href: `${base}/participants`, label: "Peserta" },
    { href: `${base}/form`, label: "Form Pendaftaran" },
    { href: `${base}/jury-form`, label: "Form Penilaian" },
    { href: `${base}/jury-assignments`, label: "Juri" },
    { href: `${base}/recap`, label: "Rekap" },
    { href: `/admin/payments?event=${eventId}`, label: "Pembayaran" },
  ];

  return (
    <div className="mb-5 flex flex-wrap gap-1 rounded-xl border border-[var(--border)] bg-[var(--background)] p-1">
      {items.map((item) => {
        const active =
          item.href === base
            ? pathname === base
            : pathname.startsWith(item.href.split("?")[0]) ||
              (item.href.startsWith("/admin/payments") && searchParams.get("event") === String(eventId));

        return (
          <Link
            key={item.href}
            href={item.href}
            aria-current={active ? "page" : undefined}
            className={
              active
                ? "rounded-lg bg-[var(--accent-subtle)] px-3 py-2 text-sm font-semibold text-[var(--foreground)]"
                : "rounded-lg px-3 py-2 text-sm font-medium text-[var(--text-secondary)] transition hover:bg-[var(--surface-muted)] hover:text-[var(--foreground)]"
            }
          >
            {item.label}
          </Link>
        );
      })}
    </div>
  );
}
