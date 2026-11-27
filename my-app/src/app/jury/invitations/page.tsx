import { AppFrame } from "@/components/app-frame";
import { apiFetch } from "@/lib/api";
import { Badge, Card, CardHeader, EmptyState, PageHeader, Table } from "@/components/kit/ui";
import { InvitationActions } from "@/components/jury/invitation-actions";
import { LoadError, SuccessNotice } from "@/components/jury/chrome";
import type { InvitationsResponse } from "@/components/jury/types";

export const metadata = { title: "Undangan Juri — JURI.ON" };
export const instant = false;

export default async function InvitationsPage({
  searchParams,
}: {
  searchParams: Promise<{ notice?: string }>;
}) {
  const { notice } = await searchParams;
  let data: InvitationsResponse | null = null;
  let loadError: string | null = null;

  try {
    data = await apiFetch<InvitationsResponse>("/jury/invitations");
  } catch (error) {
    loadError = error instanceof Error ? error.message : "Terjadi kesalahan saat memuat undangan.";
  }

  if (!data) {
    return (
      <AppFrame>
        <LoadError title="Undangan tidak dapat dimuat" description={loadError ?? "Terjadi kesalahan saat memuat undangan."} />
      </AppFrame>
    );
  }

  return (
    <AppFrame>
      <div className="space-y-6">
        <PageHeader
          eyebrow="Ruang kerja juri"
          title="Undangan Juri"
          description="Panitia mengundang Anda untuk menilai event. Setujui undangan agar Anda bisa mengakses peserta dan memberi nilai."
        />

        {notice ? <SuccessNotice message={notice} /> : null}

        {data.pending.length === 0 ? (
          <Card>
            <EmptyState
              title="Tidak Ada Undangan Baru"
              description="Semua undangan sudah Anda jawab. Undangan baru akan muncul di sini."
            />
          </Card>
        ) : (
          <div className="grid gap-4">
            {data.pending.map((invitation) => (
              <Card key={invitation.id} className="p-5">
                <div className="flex flex-wrap items-start justify-between gap-4">
                  <div className="min-w-0 flex-1">
                    <div className="flex flex-wrap items-center gap-2">
                      <Badge tone="warning">Menunggu persetujuan</Badge>
                      {invitation.jury_role ? <Badge tone="info">{invitation.jury_role}</Badge> : null}
                    </div>

                    <h2 className="mt-3 text-lg font-semibold text-[var(--foreground)]">
                      {invitation.event?.name}
                    </h2>

                    <dl className="mt-3 grid gap-2 text-sm text-[var(--text-secondary)] sm:grid-cols-2">
                      <div>
                        <dt className="text-xs font-semibold uppercase tracking-wide text-[var(--text-muted)]">Panitia</dt>
                        <dd className="font-medium text-[var(--foreground)]">{invitation.organizer ?? "-"}</dd>
                      </div>
                      <div>
                        <dt className="text-xs font-semibold uppercase tracking-wide text-[var(--text-muted)]">Form penilaian</dt>
                        <dd className="font-medium text-[var(--foreground)]">{invitation.form ?? "-"}</dd>
                      </div>
                      <div>
                        <dt className="text-xs font-semibold uppercase tracking-wide text-[var(--text-muted)]">Dikirim</dt>
                        <dd className="font-medium text-[var(--foreground)]">{invitation.created_at_label ?? "-"}</dd>
                      </div>
                      <div>
                        <dt className="text-xs font-semibold uppercase tracking-wide text-[var(--text-muted)]">Posisi juri</dt>
                        <dd className="font-medium text-[var(--foreground)]">{invitation.jury_role || "Juri"}</dd>
                      </div>
                    </dl>
                  </div>

                  <InvitationActions assignmentId={invitation.id} />
                </div>
              </Card>
            ))}
          </div>
        )}

        {data.responded.length > 0 ? (
          <Card>
            <CardHeader
              title="Riwayat Undangan"
              description="Undangan yang sudah Anda jawab."
            />
            <Table head={["Event", "Posisi", "Form", "Jawaban", "Dijawab"]}>
              {data.responded.map((invitation) => (
                <tr key={invitation.id}>
                  <td className="px-5 py-3 font-semibold text-[var(--foreground)]">{invitation.event?.name}</td>
                  <td className="px-5 py-3 text-[var(--text-secondary)]">{invitation.jury_role || "Juri"}</td>
                  <td className="px-5 py-3 text-[var(--text-secondary)]">{invitation.form ?? "-"}</td>
                  <td className="px-5 py-3">
                    {invitation.status === "accepted" ? (
                      <Badge tone="success">Diterima</Badge>
                    ) : (
                      <Badge tone="danger">Ditolak</Badge>
                    )}
                  </td>
                  <td className="whitespace-nowrap px-5 py-3 text-[var(--text-secondary)]">
                    {invitation.responded_at_label ?? "-"}
                  </td>
                </tr>
              ))}
            </Table>
          </Card>
        ) : null}
      </div>
    </AppFrame>
  );
}
