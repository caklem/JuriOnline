import { apiFetch } from "@/lib/api";
import { AppFrame } from "@/components/app-frame";
import { Badge, Card, EmptyState, PageHeader } from "@/components/kit/ui";
import { ActionLink, BackLink } from "@/components/admin/ui";
import { LoadError } from "@/components/admin/load-error";
import { formatScore } from "@/components/admin/format";
import { PrintButton } from "@/components/admin/print-button";
import type { RecapRow } from "@/components/admin/types";
import { EventNav } from "@/components/admin/event-nav";

export const metadata = { title: "Rekapitulasi Nilai — JURI.ON" };

export const instant = false;

type RecapResponse = {
  event: { id: number; name: string };
  jury_form_templates: { id: number; name: string }[];
  total_form_templates: number;
  total_juries: number;
  has_legacy_scores: boolean;
  rows: RecapRow[];
};

function TrophyIcon() {
  return (
    <svg
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.8"
      className="mr-1 inline-block h-4 w-4 text-amber-500"
      aria-hidden="true"
    >
      <path
        strokeLinecap="round"
        strokeLinejoin="round"
        d="M8 21h8m-4-4v4m-5-9H4a2 2 0 0 1-2-2V8h4m14 4h-3m3 0a2 2 0 0 1-2 2h-1M8 8h8m-8 0a4 4 0 0 0 8 0M6 8V5h12v3"
      />
    </svg>
  );
}

export default async function EventRecapPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  let data: RecapResponse;

  try {
    data = await apiFetch<RecapResponse>(`/admin/events/${id}/recap`);
  } catch (error) {
    return (
      <AppFrame>
        <LoadError
          title="Rekapitulasi tidak dapat dimuat"
          description={error instanceof Error ? error.message : "Terjadi kesalahan saat memuat rekapitulasi."}
        />
      </AppFrame>
    );
  }

  const forms = data.jury_form_templates;
  const hasForms = forms.length > 0;

  return (
    <AppFrame>
      <div className="flex flex-col gap-6">
        <div>
          <BackLink href={`/admin/events/${data.event.id}`}>Kembali ke Detail Event</BackLink>
          <div className="mt-4">
            <PageHeader
              title={`REKAPITULASI NILAI ${data.event.name.toUpperCase()}`}
              description={`Event: ${data.event.name}`}
              action={<PrintButton />}
            />
          </div>
        </div>

        <EventNav eventId={data.event.id} />

        {data.total_juries > 0 ? (
          <div className="flex flex-wrap gap-x-6 gap-y-2 rounded-xl border-l-4 border-emerald-500 bg-emerald-50 px-4 py-3 text-sm">
            <p>
              <strong className="text-emerald-700">Jumlah Juri:</strong>{" "}
              <span className="text-[var(--foreground)]">{data.total_juries} Juri</span>
            </p>
            <p>
              <strong className="text-emerald-700">Jumlah Form Juri:</strong>{" "}
              <span className="text-[var(--foreground)]">{data.total_form_templates} Form</span>
            </p>
            <p>
              <strong className="text-emerald-700">Sistem Penilaian:</strong>{" "}
              <span className="text-[var(--foreground)]">
                Rata-rata dari {data.total_form_templates} Form Juri
              </span>
            </p>
          </div>
        ) : null}

        <Card>
          {data.rows.length === 0 ? (
            <EmptyState title="Belum ada data" description="Belum ada data penilaian untuk event ini." />
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full border-collapse text-sm">
                <thead>
                  <tr className="border-b border-[var(--border)] bg-[var(--surface-muted)] text-[11px] uppercase tracking-wide text-[var(--text-muted)]">
                    <th scope="col" className="px-3 py-3 text-center font-semibold">NO</th>
                    <th scope="col" className="px-3 py-3 text-center font-semibold">NO. PESERTA</th>
                    <th scope="col" className="px-3 py-3 text-left font-semibold">NAMA GROUP</th>
                    <th scope="col" className="px-3 py-3 text-left font-semibold">ALAMAT</th>
                    {hasForms ? (
                      <th scope="colgroup" colSpan={forms.length} className="px-3 py-3 text-center font-semibold">
                        NILAI AKHIR PER FORM
                      </th>
                    ) : (
                      <th scope="col" className="px-3 py-3 text-center font-semibold">NILAI</th>
                    )}
                    {data.has_legacy_scores && hasForms ? (
                      <th scope="col" className="px-3 py-3 text-center font-semibold">NILAI LAIN</th>
                    ) : null}
                    <th scope="col" className="px-3 py-3 text-center font-semibold">JUMLAH</th>
                    <th scope="col" className="px-3 py-3 text-center font-semibold">RATA-RATA</th>
                    <th scope="col" className="px-3 py-3 text-center font-semibold">JURI</th>
                    <th scope="col" className="px-3 py-3 text-center font-semibold">RANK</th>
                  </tr>
                  {hasForms ? (
                    <tr className="border-b border-[var(--border)] text-xs text-[var(--text-secondary)]">
                      <th className="px-3 py-2" />
                      <th className="px-3 py-2" />
                      <th className="px-3 py-2" />
                      <th className="px-3 py-2" />
                      {forms.map((form) => (
                        <th key={form.id} scope="col" className="px-3 py-2 text-center font-medium">
                          {form.name}
                        </th>
                      ))}
                      {data.has_legacy_scores ? <th className="px-3 py-2" /> : null}
                      <th className="px-3 py-2" />
                      <th className="px-3 py-2" />
                      <th className="px-3 py-2" />
                      <th className="px-3 py-2" />
                    </tr>
                  ) : null}
                </thead>
                <tbody className="divide-y divide-[var(--border)]">
                  {data.rows.map((row, index) => (
                    <tr key={`${row.registration_number}-${index}`} className="even:bg-[var(--surface-muted)]/50">
                      <td className="px-3 py-3 text-center font-medium">{index + 1}</td>
                      <td className="px-3 py-3 text-center font-semibold">{row.registration_number}</td>
                      <td className="px-3 py-3 text-left font-semibold">{row.participant_name ?? "-"}</td>
                      <td className="px-3 py-3 text-left">{row.address}</td>
                      {hasForms ? (
                        forms.map((form) => {
                          const detail = row.form_details.find((item) => item.id === form.id);
                          const value = detail?.score ?? 0;

                          return (
                            <td key={form.id} className="px-3 py-3 text-center font-medium">
                              {value > 0 ? formatScore(value) : "-"}
                            </td>
                          );
                        })
                      ) : (
                        <td className="px-3 py-3 text-center font-medium">{formatScore(row.total_score)}</td>
                      )}
                      {data.has_legacy_scores && hasForms ? (
                        <td className="px-3 py-3 text-center font-medium">
                          {row.legacy_score > 0 ? formatScore(row.legacy_score) : "-"}
                        </td>
                      ) : null}
                      <td className="px-3 py-3 text-center font-bold">{formatScore(row.total_score)}</td>
                      <td className="px-3 py-3 text-center font-bold">{formatScore(row.average_score)}</td>
                      <td className="px-3 py-3 text-center">
                        <Badge tone="info">{row.jury_count} Juri</Badge>
                      </td>
                      <td className="px-3 py-3 text-center text-base font-bold">
                        {row.rank <= 3 ? <TrophyIcon /> : null}
                        <span>{row.rank}</span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </Card>

        <div className="flex flex-wrap items-center gap-3">
          <ActionLink href={`/admin/events/${data.event.id}`}>Kembali ke Detail Event</ActionLink>
        </div>
      </div>
    </AppFrame>
  );
}
