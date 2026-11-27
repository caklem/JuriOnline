import type { ReactNode } from "react";

export function InfoList({ children, className = "" }: { children: ReactNode; className?: string }) {
  return <dl className={`space-y-3 text-sm ${className}`}>{children}</dl>;
}

export function InfoRow({ label, children }: { label: string; children: ReactNode }) {
  return (
    <div>
      <dt className="text-[11px] font-medium uppercase tracking-wide text-[var(--text-muted)]">{label}</dt>
      <dd className="mt-1 text-[var(--foreground)]">{children}</dd>
    </div>
  );
}
