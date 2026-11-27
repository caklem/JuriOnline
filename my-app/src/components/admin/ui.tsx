import Link from "next/link";
import type { ReactNode } from "react";
import type { ApiMeta } from "./types";

type LinkTone = "solid" | "outline" | "muted";

const TONES: Record<LinkTone, string> = {
  solid:
    "bg-[var(--foreground)] text-[var(--background)] hover:opacity-90",
  outline:
    "border border-[var(--border)] bg-[var(--background)] text-[var(--foreground)] hover:bg-[var(--surface-muted)]",
  muted: "text-[var(--text-secondary)] hover:text-[var(--foreground)]",
};

/** Tautan navigasi yang tampil seperti tombol; tidak bisa memakai <Button> karena harus <a>. */
export function ActionLink({
  href,
  children,
  tone = "outline",
  className = "",
}: {
  href: string;
  children: ReactNode;
  tone?: LinkTone;
  className?: string;
}) {
  return (
    <Link
      href={href}
      className={`inline-flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium transition ${TONES[tone]} ${className}`}
    >
      {children}
    </Link>
  );
}

export function BackLink({ href, children }: { href: string; children: ReactNode }) {
  return (
    <Link
      href={href}
      className="inline-flex items-center gap-1.5 text-sm font-medium text-[var(--text-secondary)] transition hover:text-[var(--foreground)]"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className="h-4 w-4" aria-hidden="true">
        <path strokeLinecap="round" strokeLinejoin="round" d="M15 6l-6 6 6 6" />
      </svg>
      {children}
    </Link>
  );
}

/** Navigasi halaman daftar. Query lain dipertahankan. */
export function Pagination({
  meta,
  basePath,
  params = {},
}: {
  meta: ApiMeta;
  basePath: string;
  params?: Record<string, string | undefined>;
}) {
  if (meta.last_page <= 1) {
    return null;
  }

  const buildHref = (page: number) => {
    const query = new URLSearchParams();

    for (const [key, value] of Object.entries(params)) {
      if (value) {
        query.set(key, value);
      }
    }

    if (page > 1) {
      query.set("page", String(page));
    }

    const search = query.toString();

    return search ? `${basePath}?${search}` : basePath;
  };

  return (
    <nav className="flex flex-wrap items-center justify-between gap-3 border-t border-[var(--border)] px-5 py-4 text-sm">
      <p className="text-xs text-[var(--text-secondary)]">
        Halaman {meta.current_page} dari {meta.last_page} · {meta.total} data
      </p>
      <div className="flex items-center gap-2">
        {meta.current_page > 1 ? (
          <ActionLink href={buildHref(meta.current_page - 1)}>Sebelumnya</ActionLink>
        ) : (
          <span className="rounded-lg border border-[var(--border)] px-3 py-2 text-sm text-[var(--text-muted)] opacity-60">
            Sebelumnya
          </span>
        )}
        {meta.current_page < meta.last_page ? (
          <ActionLink href={buildHref(meta.current_page + 1)}>Berikutnya</ActionLink>
        ) : (
          <span className="rounded-lg border border-[var(--border)] px-3 py-2 text-sm text-[var(--text-muted)] opacity-60">
            Berikutnya
          </span>
        )}
      </div>
    </nav>
  );
}
