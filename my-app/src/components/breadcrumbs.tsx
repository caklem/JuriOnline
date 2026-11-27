"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

const LABELS: Record<string, string> = {
  dashboard: "Dashboard",
  admin: "Admin",
  events: "Event",
  create: "Buat Event",
  edit: "Edit",
  participants: "Peserta",
  form: "Form Pendaftaran",
  "jury-form": "Form Penilaian",
  "jury-assignments": "Juri",
  recap: "Rekap",
  payments: "Pembayaran",
  all: "Semua",
  registrations: "Pendaftaran",
  owner: "Owner",
  users: "Pengguna",
  jury: "Juri",
  invitations: "Undangan",
  scores: "Penilaian",
  profile: "Profil",
  notifications: "Notifikasi",
  login: "Masuk",
};

export function Breadcrumbs() {
  const pathname = usePathname();
  const segments = pathname.split("/").filter(Boolean);

  if (segments.length === 0) return null;

  return (
    <nav aria-label="Breadcrumb" className="min-w-0">
      <ol className="flex items-center gap-1.5 text-xs text-[var(--text-muted)]">
        <li>
          <Link href="/dashboard" className="hover:text-[var(--foreground)]">JuriOnline</Link>
        </li>
        {segments.map((segment, index) => {
          const href = "/" + segments.slice(0, index + 1).join("/");
          const label = /^\d+$/.test(segment) ? "Detail" : LABELS[segment] ?? segment;
          const last = index === segments.length - 1;

          return (
            <li key={href} className="flex items-center gap-1.5">
              <span aria-hidden="true">/</span>
              {last ? (
                <span aria-current="page" className="truncate font-medium text-[var(--text-secondary)]">{label}</span>
              ) : (
                <Link href={href} className="truncate hover:text-[var(--foreground)]">{label}</Link>
              )}
            </li>
          );
        })}
      </ol>
    </nav>
  );
}
