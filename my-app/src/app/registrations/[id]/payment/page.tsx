import Link from "next/link";
import { redirect } from "next/navigation";
import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Badge, Card, CardHeader, EmptyState, PageHeader } from "@/components/kit/ui";
import { isNextRedirect, participantErrorMessage } from "@/components/participant/api-errors";
import { buttonLinkClass } from "@/components/participant/button-link";
import type { PaymentFormResponse } from "@/components/participant/types";
import { PaymentForm } from "./payment-form";

export const metadata = { title: "Upload Bukti Pembayaran — JURI.ON" };

// Halaman membaca cookie sesi melalui apiFetch; jangan diprerender saat build.
export const instant = false;

const BANK_ACCOUNTS = [
  { bank: "BCA", number: "1234567890" },
  { bank: "Mandiri", number: "0987654321" },
  { bank: "BNI", number: "1122334455" },
];

async function loadPaymentForm(
  id: string,
): Promise<{ registration: PaymentFormResponse["registration"] } | { error: string }> {
  try {
    const data = await apiFetch<PaymentFormResponse>(`/registrations/${id}/payment`);

    return { registration: data.registration };
  } catch (error) {
    if (isNextRedirect(error)) {
      throw error;
    }

    if (error instanceof Error && error.message === "Pembayaran sudah diupload sebelumnya.") {
      redirect(`/registrations/${id}`);
    }

    return { error: participantErrorMessage(error, "Gagal memuat formulir pembayaran.") };
  }
}

export default async function PaymentUploadPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const result = await loadPaymentForm(id);

  if ("error" in result) {
    return (
      <AppFrame>
        <PageHeader
          eyebrow="Pembayaran"
          title="Upload Bukti Pembayaran"
          description="Lengkapi bukti transfer agar pendaftaran Anda dapat diverifikasi oleh penyelenggara."
        />
        <Card className="mt-6">
          <EmptyState
            title="Bukti pembayaran tidak dapat diunggah"
            description={result.error}
            action={
              <Link href={`/registrations/${id}`} className={buttonLinkClass("secondary")}>
                Kembali ke pendaftaran
              </Link>
            }
          />
        </Card>
      </AppFrame>
    );
  }

  const registration = result.registration;

  return (
    <AppFrame>
      <div className="space-y-6">
        <PageHeader
          eyebrow="Pembayaran"
          title="Upload Bukti Pembayaran"
          description="Lengkapi bukti transfer agar pendaftaran Anda dapat diverifikasi oleh penyelenggara."
          action={
            <Link href={`/registrations/${id}`} className={buttonLinkClass("secondary", "sm")}>
              Kembali ke pendaftaran
            </Link>
          }
        />

        <div className="grid gap-6 lg:grid-cols-3">
          <div className="space-y-6 lg:col-span-2">
            <Card>
              <CardHeader
                title="Detail Pendaftaran"
                action={<Badge tone="warning">Menunggu pembayaran</Badge>}
              />
              <dl className="divide-y divide-[var(--border)] px-5 py-2">
                <div className="flex flex-wrap items-center justify-between gap-2 py-3">
                  <dt className="text-sm text-[var(--text-secondary)]">Event</dt>
                  <dd className="text-sm font-semibold text-[var(--foreground)]">{registration.event.name}</dd>
                </div>
                <div className="flex flex-wrap items-center justify-between gap-2 py-3">
                  <dt className="text-sm text-[var(--text-secondary)]">No. Pendaftaran</dt>
                  <dd>
                    <code className="rounded-md bg-[var(--surface-muted)] px-2 py-1 font-mono text-xs text-[var(--text-secondary)]">
                      {registration.registration_number}
                    </code>
                  </dd>
                </div>
                <div className="flex flex-wrap items-center justify-between gap-2 py-3">
                  <dt className="text-sm text-[var(--text-secondary)]">Jumlah yang harus dibayar</dt>
                  <dd className="text-2xl font-semibold tracking-tight text-[var(--foreground)]">
                    {registration.amount_label}
                  </dd>
                </div>
              </dl>
            </Card>

            <div className="rounded-2xl border border-amber-200 bg-amber-50 p-6">
              <div className="mb-4 border-b border-amber-200 pb-3">
                <h2 className="text-base font-semibold text-amber-900">Informasi Pembayaran</h2>
              </div>
              <p className="text-sm leading-6 text-amber-900">Silakan transfer ke salah satu rekening berikut:</p>
              <ul className="mt-3 space-y-2">
                {BANK_ACCOUNTS.map((account) => (
                  <li
                    key={account.bank}
                    className="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-white/70 px-3 py-2 text-sm ring-1 ring-inset ring-amber-200"
                  >
                    <span className="font-semibold text-amber-900">{account.bank}</span>
                    <span className="font-mono text-slate-800">
                      {account.number} <span className="font-sans text-amber-800">a/n JURI.ON</span>
                    </span>
                  </li>
                ))}
              </ul>
              <p className="mt-4 text-sm font-medium text-amber-900">
                Upload bukti transfer Anda pada formulir di samping.
              </p>
            </div>
          </div>

          <Card className="h-fit">
            <CardHeader
              title="Unggah Bukti Transfer"
              description="Format JPG, PNG, atau PDF. Maksimal 2MB."
            />
            <div className="px-5 py-5">
              <PaymentForm registrationId={registration.id} />
            </div>
          </Card>
        </div>
      </div>
    </AppFrame>
  );
}
