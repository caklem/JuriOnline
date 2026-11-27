import Link from "next/link";
import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Card, EmptyState, PageHeader, Table } from "@/components/kit/ui";
import { buttonLinkClass } from "@/components/participant/button-link";
import { PaymentStatusBadge, RegistrationStatusBadge } from "@/components/participant/status-badges";
import type { RegistrationListResponse } from "@/components/participant/types";

export const metadata = { title: "Pendaftaran Saya — JURI.ON" };

// Halaman membaca cookie sesi melalui apiFetch; jangan diprerender saat build.
export const instant = false;

export default async function MyRegistrationsPage() {
  const { registrations } = await apiFetch<RegistrationListResponse>("/registrations");

  return (
    <AppFrame>
      <PageHeader
        title="Pendaftaran Saya"
        description="Daftar event yang Anda ikuti beserta status pendaftaran dan penilaiannya."
      />

      {registrations.length === 0 ? (
        <Card className="mt-6">
          <EmptyState
            title="Belum ada pendaftaran"
            description="Anda belum mendaftar event apapun. Telusuri event yang tersedia untuk memulai."
            action={
              <Link href="/events" className={buttonLinkClass("primary")}>
                Browse Event
              </Link>
            }
          />
        </Card>
      ) : (
        <Card className="mt-6 overflow-hidden">
          <Table
            head={[
              "Event",
              "No. Pendaftaran",
              "Tanggal Daftar",
              "Status Pendaftaran",
              "Status Pembayaran",
              "Nilai",
              "Aksi",
            ]}
          >
            {registrations.map((registration) => (
              <tr key={registration.id} className="align-top">
                <td className="px-5 py-4">
                  <p className="font-semibold text-[var(--foreground)]">{registration.event?.name ?? "-"}</p>
                  <p className="mt-0.5 text-xs text-[var(--text-muted)]">
                    {registration.event?.event_date_label ?? "-"}
                  </p>
                </td>
                <td className="px-5 py-4">
                  <span className="rounded-md bg-[var(--surface-muted)] px-2 py-1 font-mono text-xs text-[var(--text-secondary)]">
                    {registration.registration_number}
                  </span>
                </td>
                <td className="whitespace-nowrap px-5 py-4 text-sm text-[var(--text-secondary)]">
                  {registration.registered_at_label ?? "-"}
                </td>
                <td className="px-5 py-4">
                  <RegistrationStatusBadge status={registration.status} />
                </td>
                <td className="px-5 py-4">
                  <PaymentStatusBadge payment={registration.payment} />
                </td>
                <td className="px-5 py-4">
                  {registration.average_score > 0 ? (
                    <span className="text-base font-semibold text-emerald-700">
                      {registration.average_score.toFixed(2)}
                    </span>
                  ) : (
                    <span className="text-[var(--text-muted)]">-</span>
                  )}
                </td>
                <td className="px-5 py-4">
                  <Link href={`/registrations/${registration.id}`} className={buttonLinkClass("primary", "sm")}>
                    Detail
                  </Link>
                </td>
              </tr>
            ))}
          </Table>
        </Card>
      )}
    </AppFrame>
  );
}
