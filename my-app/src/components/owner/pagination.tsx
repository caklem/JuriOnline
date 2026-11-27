import Link from "next/link";
import type { ReactNode } from "react";

export function Pagination({
  basePath,
  params,
  page,
  lastPage,
}: {
  basePath: string;
  params: Record<string, string>;
  page: number;
  lastPage: number;
}) {
  if (lastPage <= 1) {
    return null;
  }

  const pages = Array.from({ length: Math.min(lastPage, 7) }, (_, index) => {
    if (lastPage <= 7) return index + 1;

    const start = Math.min(Math.max(page - 3, 1), lastPage - 6);

    return start + index;
  });

  return (
    <nav aria-label="Navigasi halaman" className="flex flex-wrap items-center justify-center gap-2 px-5 py-5">
      <PageLink basePath={basePath} params={params} target={Math.max(page - 1, 1)} disabled={page <= 1}>
        Sebelumnya
      </PageLink>
      {pages.map((target) => (
        <Link
          key={target}
          href={`${basePath}?${new URLSearchParams({ ...params, page: String(target) })}`}
          aria-current={target === page ? "page" : undefined}
          className={
            target === page
              ? "grid h-9 min-w-9 place-items-center rounded-lg bg-[var(--accent)] px-2 text-sm font-semibold text-white"
              : "grid h-9 min-w-9 place-items-center rounded-lg border border-[var(--border)] bg-[var(--background)] px-2 text-sm text-[var(--text-secondary)] transition hover:text-[var(--foreground)]"
          }
        >
          {target}
        </Link>
      ))}
      <PageLink
        basePath={basePath}
        params={params}
        target={Math.min(page + 1, lastPage)}
        disabled={page >= lastPage}
      >
        Berikutnya
      </PageLink>
    </nav>
  );
}

function PageLink({
  basePath,
  params,
  target,
  disabled,
  children,
}: {
  basePath: string;
  params: Record<string, string>;
  target: number;
  disabled: boolean;
  children: ReactNode;
}) {
  if (disabled) {
    return (
      <span className="rounded-lg border border-[var(--border)] px-3 py-1.5 text-sm text-slate-300">
        {children}
      </span>
    );
  }

  return (
    <Link
      href={`${basePath}?${new URLSearchParams({ ...params, page: String(target) })}`}
      className="rounded-lg border border-[var(--border)] bg-[var(--background)] px-3 py-1.5 text-sm text-[var(--text-secondary)] transition hover:text-[var(--foreground)]"
    >
      {children}
    </Link>
  );
}
