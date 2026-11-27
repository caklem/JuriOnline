import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Badge, Card, CardHeader, EmptyState, PageHeader, Table } from "@/components/kit/ui";
import { ActionLink, Pagination } from "@/components/admin/ui";
import { LoadError } from "@/components/admin/load-error";
import { formatDateTime, formatRupiah } from "@/components/admin/format";
import { PaymentActions } from "@/components/admin/payment-actions";
import type { ApiMeta, PaymentRow } from "@/components/admin/types";
import { verifyPaymentAction } from "./actions";

export const metadata = { title: "Verifikasi Pembayaran — JURI.ON" };

export const instant = false;

type PendingResponse = { data: PaymentRow[]; pending_total: number; meta: ApiMeta };

export default async function PendingPaymentsPage({
  searchParams,
}: {
  searchParams: Promise<{ page?: string; event?: string }>;
}) {
  const { page, event } = await searchParams;
  const filterEventId = event ? Number(event) : null;
  const params = new URLSearchParams({ status: "pending" });
  if (page) params.set("page", page);
  if (filterEventId) params.set("event", String(filterEventId));
  let data: PendingResponse;
  let filterEventName: string | null = null;

  try {
    data = await apiFetch<PendingResponse>(`/admin/payments?${params.toString()}`);
    if (filterEventId) {
      const scope = await apiFetch<{ event?: { name?: string } }>(`/admin/events/${filterEventId}`).catch(() => null);
      filterEventName = scope?.event?.name ?? null;
    }
  } catch (error) {
    return (
      <AppFrame>
        <LoadError
          title="Pembayaran tidak dapat dimuat"
          description={error instanceof Error ? error.message : "Terjadi kesalahan saat memuat pembayaran."}
          backHref="/dashboard"
          backLabel="Kembali ke dashboard"
        />
      </AppFrame>
    );
  }

  return (
    <AppFrame>
      <div className="flex flex-col gap-6">
        <PageHeader
          eyebrow="Administrasi"
          title="Verifikasi Pembayaran"
          description={
            filterEventId
              ? `Daftar pembayaran yang menunggu verifikasi untuk event ${filterEventName ?? "#" + filterEventId}.`
              : "Daftar pembayaran yang menunggu verifikasi."
          }
          action={
            <div className="flex flex-wrap items-center gap-2">
              <Badge tone="warning">{data.pending_total} menunggu</Badge>
              {filterEventId ? (
                <ActionLink href={filterEventName ? `/admin/events/${filterEventId}` : "/admin/payments"}>
                  Hapus filter event
                </ActionLink>
              ) : null}
              <ActionLink href="/admin/payments/all">Semua Pembayaran</ActionLink>
            </div>
          }
        />

        <Card>
          {data.data.length === 0 ? (
            <EmptyState
              title="Tidak Ada Pembayaran Pending"
              description="Semua pembayaran sudah diverifikasi. Tidak ada yang menunggu tindakan."
            />
          ) : (
            <>
              <CardHeader
                title="Antrean Verifikasi"
                description="Periksa bukti transfer sebelum menyetujui atau menolak pembayaran."
              />
              <Table
                head={["No. Pendaftaran", "Peserta", "Event", "Jumlah", "Tanggal Upload", "Aksi"]}
              >
                {data.data.map((payment) => (
                  <tr key={payment.id} className="align-middle">
                    <td className="px-5 py-4">
                      <code className="rounded-md bg-[var(--surface-muted)] px-2 py-1 font-mono text-xs text-[var(--text-secondary)]">
                        {payment.registration?.registration_number ?? "-"}
                      </code>
                    </td>
                    <td className="px-5 py-4">
                      <strong className="font-semibold text-[var(--foreground)]">
                        {payment.registration?.user?.name ?? "-"}
                      </strong>
                    </td>
                    <td className="px-5 py-4 text-[var(--text-secondary)]">
                      {payment.registration?.event?.name ?? "-"}
                    </td>
                    <td className="whitespace-nowrap px-5 py-4 font-semibold text-[var(--foreground)]">
                      {formatRupiah(payment.amount)}
                    </td>
                    <td className="whitespace-nowrap px-5 py-4 text-[var(--text-secondary)]">
                      {formatDateTime(payment.created_at)}
                    </td>
                    <td className="px-5 py-4">
                      <PaymentActions
                        paymentId={payment.id}
                        userName={payment.registration?.user?.name ?? "peserta"}
                        action={verifyPaymentAction.bind(null, payment.id)}
                      />
                    </td>
                  </tr>
                ))}
              </Table>
              <Pagination meta={data.meta} basePath="/admin/payments" params={{ status: "pending" }} />
            </>
          )}
        </Card>
      </div>
    </AppFrame>
  );
}
