import Link from "next/link";
import type { ReactNode } from "react";
import { Card, EmptyState } from "@/components/kit/ui";

const variantClasses = {
  primary: "bg-indigo-600 text-white hover:bg-indigo-700",
  success: "bg-emerald-600 text-white hover:bg-emerald-700",
  warning: "bg-amber-500 text-white hover:bg-amber-600",
  outline: "border border-[var(--border)] bg-[var(--background)] text-[var(--foreground)] hover:bg-[var(--surface-muted)]",
  secondary: "bg-[var(--surface-muted)] text-[var(--foreground)] hover:bg-[var(--border)]",
} as const;

export type ActionVariant = keyof typeof variantClasses;

export function ActionLink({
  href,
  children,
  variant = "primary",
  className = "",
}: {
  href: string;
  children: ReactNode;
  variant?: ActionVariant;
  className?: string;
}) {
  return (
    <Link
      href={href}
      className={`inline-flex h-10 items-center justify-center gap-2 rounded-lg px-4 text-sm font-semibold transition ${variantClasses[variant]} ${className}`}
    >
      {children}
    </Link>
  );
}

export function BackLink({ href, children }: { href: string; children: ReactNode }) {
  return (
    <Link
      href={href}
      className="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-700"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" className="h-4 w-4" aria-hidden="true">
        <path strokeLinecap="round" strokeLinejoin="round" d="M10.5 19.5 3 12l7.5-7.5M3 12h18" />
      </svg>
      {children}
    </Link>
  );
}

export function SuccessNotice({ message }: { message: string }) {
  return (
    <div
      className="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800"
      role="status"
    >
      {message}
    </div>
  );
}

export function LoadError({ title, description }: { title: string; description: string }) {
  return (
    <Card>
      <EmptyState
        title={title}
        description={description}
        action={<ActionLink href="/jury/events" variant="outline">Kembali ke Daftar Event</ActionLink>}
      />
    </Card>
  );
}
