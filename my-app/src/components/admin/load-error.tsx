import { Card, EmptyState } from "@/components/kit/ui";
import { ActionLink } from "./ui";

/** Tampilan galat saat data halaman gagal dimuat (mis. 403 atau 404 dari API). */
export function LoadError({
  title,
  description,
  backHref = "/admin/events",
  backLabel = "Kembali ke daftar event",
}: {
  title: string;
  description: string;
  backHref?: string;
  backLabel?: string;
}) {
  return (
    <Card>
      <EmptyState title={title} description={description} action={<ActionLink href={backHref}>{backLabel}</ActionLink>} />
    </Card>
  );
}
