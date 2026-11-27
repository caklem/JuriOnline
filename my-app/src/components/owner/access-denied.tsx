import Link from "next/link";
import { Card, EmptyState, PageHeader } from "@/components/kit/ui";

export function AccessDenied({ description }: { description: string }) {
  return (
    <div className="space-y-6">
      <PageHeader title="Akses Ditolak" description={description} />
      <Card>
        <EmptyState
          title="Butuh akses khusus"
          description="Hubungi Admin Owner bila Anda merasa akses ini seharusnya tersedia."
          action={
            <Link
              href="/dashboard"
              className="rounded-lg border border-[var(--border)] px-4 py-2 text-sm font-medium text-[var(--foreground)] transition hover:bg-[var(--surface-muted)]"
            >
              Kembali ke Dashboard
            </Link>
          }
        />
      </Card>
    </div>
  );
}
