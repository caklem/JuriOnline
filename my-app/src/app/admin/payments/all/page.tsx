import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Badge, Card, CardHeader, EmptyState, PageHeader, Table } from "@/components/kit/ui";
import { ActionLink, Pagination } from "@/components/admin/ui";
import { LoadError } from "@/components/admin/load-error";
import { formatDateTime, formatRupiah } from "@/components/admin/format";
import type { ApiMeta, PaymentRow } from "@/components/admin/types";

export const metadata = { title: "Semua Pembayaran — JURI.ON" };

export const instant = false;

type PaymentsResponse = { data: PaymentRow[]; meta: ApiMeta };

function paymentTone(status: string): "success" | "warning" | "danger" {
  if (status === "verified") return "success";
  if (status === "pending") return "warning";

  return "danger";
}

export default async function AllPaymentsPage({
  searchParams,
}: {
  searchParams: Promise<{ page?: string }>;
}) {
  const { page } = await searchParams;
  const query = page ? `?page=${encodeURIComponent(page)}` : "";
  let data: PaymentsResponse;

  try {
    data = await apiFetch<PaymentsResponse>(`/admin/payments/all${query}`);
  } catch (error) {
    return (
      <AppFrame>
        <LoadError
          title="Riwayat pembayaran tidak dapat dimuat"
          description={error instanceof Error ? error.message : "Terjadi kesalahan saat memuat pembayaran."}
          backHref="/admin/payments"
          backLabel="Kembali ke verifikasi pembayaran"
        />
      </AppFrame>
    );
  }

  return (
    <AppFrame>
      <div className="flex flex-col gap-6">
        <PageHeader
          eyebrow="Administrasi"
          title="Semua Pembayaran"
          description="Riwayat seluruh pembayaran peserta beserta status verifikasinya."
          action={
            <ActionLink href="/admin/payments" tone="solid">
              Verifikasi pembayaran pending
            </ActionLink>
          }
        />

        <Card>
          {data.data.length === 0 ? (
            <EmptyState title="Belum Ada Pembayaran" description="Belum ada pembayaran yang tercatat di sistem." />
          ) : (
            <>
              <CardHeader
                title="Riwayat Pembayaran"
                description={`Total ${data.meta.total} pembayaran tercatat.`}
              />
              <Table
                head={[
                  "No. Pendaftaran",
                  "Peserta",
                  "Event",
                  "Jumlah",
                  "Status",
                  "Diverifikasi Oleh",
                  "Tanggal Upload",
                  "Bukti",
                ]}
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
                    <td className="px-5 py-4">
                      <Badge tone={paymentTone(payment.status)}>{payment.status_label}</Badge>
                    </td>
                    <td className="px-5 py-4 text-[var(--text-secondary)]">{payment.verifier?.name ?? "-"}</td>
                    <td className="whitespace-nowrap px-5 py-4 text-[var(--text-secondary)]">
                      {formatDateTime(payment.created_at)}
                    </td>
                    <td className="px-5 py-4">
                      {payment.has_proof ? (
                        <a
                          href={`/admin/payments/proof/${payment.id}`}
                          target="_blank"
                          rel="noopener"
                          className="inline-flex items-center justify-center rounded-lg border border-[var(--border)] px-3 py-2 text-sm font-medium text-[var(--foreground)] transition hover:bg-[var(--surface-muted)]"
                        >
                          Lihat Bukti
                        </a>
                      ) : (
                        <span className="text-[var(--text-muted)]">-</span>
                      )}
                    </td>
                  </tr>
                ))}
              </Table>
              <Pagination meta={data.meta} basePath="/admin/payments/all" />
            </>
          )}
        </Card>
      </div>
    </AppFrame>
  );
}
