import Link from "next/link";
import { AppFrame } from "@/components/app-frame";
import { apiFetch } from "@/lib/api";
import { Badge, Card, EmptyState, PageHeader } from "@/components/kit/ui";
import { ActionLink, BackLink, LoadError, SuccessNotice } from "@/components/jury/chrome";
import { ProgressBar } from "@/components/jury/progress-bar";
import { ScoringClosedNotice } from "@/components/jury/scoring-notice";
import type { ParticipantsResponse } from "@/components/jury/types";

export const metadata = { title: "Peserta — JURI.ON" };
export const instant = false;

export default async function ParticipantsPage({
  params,
  searchParams,
}: {
  params: Promise<{ id: string }>;
  searchParams: Promise<{ page?: string; notice?: string }>;
}) {
  const { id } = await params;
  const { page, notice } = await searchParams;
  const currentPage = Math.max(1, Number(page ?? 1) || 1);

  let data: ParticipantsResponse | null = null;
  let loadError: string | null = null;

  try {
    data = await apiFetch<ParticipantsResponse>(`/jury/events/${id}/participants?page=${currentPage}`);
  } catch (error) {
    loadError = error instanceof Error ? error.message : "Terjadi kesalahan saat memuat peserta.";
  }

  if (!data) {
    return (
      <AppFrame>
        <LoadError title="Peserta tidak dapat dimuat" description={loadError ?? "Terjadi kesalahan saat memuat peserta."} />
      </AppFrame>
    );
  }

  return (
    <AppFrame>
      <div className="space-y-4">
        <BackLink href="/jury/events">Kembali ke Daftar Event</BackLink>

        <PageHeader
          title={data.event.name}
          description="Berikan penilaian untuk peserta yang telah terverifikasi."
          action={
            <ActionLink href={`/jury/events/${data.event.id}/leaderboard`} variant="success">
              Lihat Leaderboard
            </ActionLink>
          }
        />

        {notice ? <SuccessNotice message={notice} /> : null}

        {!data.scoring_open ? (
          <ScoringClosedNotice mode={data.scoring_mode} opensAt={data.scoring_opens_at_label} />
        ) : null}

        <Card className="p-5">
          <div className="flex flex-wrap items-start justify-between gap-3">
            <div className="min-w-0">
              <p className="text-sm font-semibold text-[var(--foreground)]">Progres penilaian Anda</p>
              <p className="mt-1 text-xs leading-5 text-[var(--text-secondary)]">
                Sudah dinilai <strong>{data.my_scored_total}</strong> dari <strong>{data.verified_total}</strong>{" "}
                peserta terverifikasi.{" "}
                {data.scoring_open ? (
                  <>
                    Klik <strong>Berikan Penilaian</strong> pada peserta, isi semua kriteria, lalu simpan.
                  </>
                ) : (
                  "Tombol penilaian akan muncul begitu penilaian dibuka."
                )}
              </p>
            </div>
            <Badge tone="info">{data.progress_percent}%</Badge>
          </div>
          <ProgressBar percent={data.progress_percent} />
        </Card>

        {data.assigned_form ? (
          <div className="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 px-4 py-3">
            <p className="flex flex-wrap items-center gap-2 text-sm">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" className="h-4 w-4 text-emerald-600" aria-hidden="true">
                <path strokeLinecap="round" strokeLinejoin="round" d="M9 5h6m-7 4h8m-8 4h5m-5 4h8M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
              </svg>
              <strong className="text-emerald-700">Form Penilaian Anda:</strong>
              <span className="font-medium text-[var(--foreground)]">{data.assigned_form.name || "Form Juri"}</span>
              <Link
                href={`/jury/events/${data.event.id}/form`}
                className="text-xs font-semibold text-emerald-700 underline hover:text-emerald-800 sm:ml-auto"
              >
                Lihat isi form
              </Link>
            </p>
          </div>
        ) : null}

        {data.registrations.length === 0 ? (
          <Card>
            <EmptyState
              title="Belum ada peserta"
              description="Belum ada peserta terverifikasi untuk event ini."
            />
          </Card>
        ) : (
          <div className="grid gap-4">
            {data.registrations.map((registration) => {
              const otherScores = registration.scores.filter(
                (score) => score.id !== registration.my_score?.id,
              );

              return (
                <Card key={registration.id} className="p-5">
                  <div className="flex flex-wrap items-start justify-between gap-6">
                    <div className="min-w-0 flex-1">
                      <div className="flex items-start gap-3">
                        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">
                          {(registration.user?.name ?? "?").charAt(0).toUpperCase()}
                        </span>
                        <div>
                          <h3 className="text-base font-semibold text-[var(--foreground)]">{registration.user?.name}</h3>
                          <p className="text-sm text-[var(--text-muted)]">{registration.user?.email}</p>
                        </div>
                      </div>

                      <div className="mt-3 flex flex-wrap items-center gap-2">
                        <Badge tone="success">{registration.status_label}</Badge>
                        <Badge tone="info">
                          Rata-rata:{" "}
                          {registration.average_score !== null ? registration.average_score.toFixed(2) : "Belum ada"}
                        </Badge>
                        <Badge tone="warning">{registration.jury_count} juri menilai</Badge>
                      </div>

                      {registration.notes ? (
                        <div className="mt-3 rounded-xl bg-[var(--surface-muted)] px-4 py-3 text-sm text-[var(--text-secondary)] ring-1 ring-[var(--border)]">
                          <strong className="text-[var(--foreground)]">Catatan Peserta:</strong>
                          <span className="mt-1 block whitespace-pre-line">{registration.notes}</span>
                        </div>
                      ) : null}
                    </div>

                    <div className="w-full shrink-0 sm:w-48">
                      {registration.my_score ? (
                        <>
                          <div className="rounded-xl bg-emerald-50 px-4 py-3 text-center ring-1 ring-emerald-100">
                            <p className="text-xs font-medium uppercase tracking-wide text-emerald-700">Nilai Anda</p>
                            <p className="mt-1 text-2xl font-semibold text-emerald-700">
                              {registration.my_score.score.toFixed(2)}
                            </p>
                          </div>
                          {data.scoring_open ? (
                            <ActionLink
                              href={`/jury/scores/${registration.my_score.id}/edit`}
                              variant="warning"
                              className="mt-3 w-full"
                            >
                              Edit Penilaian
                            </ActionLink>
                          ) : (
                            <span className="mt-3 flex h-10 items-center justify-center rounded-lg bg-amber-500 px-4 text-sm font-semibold text-white opacity-60" aria-disabled="true">
                              Penilaian Ditutup
                            </span>
                          )}
                        </>
                      ) : data.scoring_open ? (
                        <ActionLink
                          href={`/jury/registrations/${registration.id}/score`}
                          variant="primary"
                          className="w-full"
                        >
                          Berikan Penilaian
                        </ActionLink>
                      ) : (
                        <span className="flex h-10 items-center justify-center rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white opacity-60" aria-disabled="true">
                          Belum Bisa Menilai
                        </span>
                      )}
                    </div>
                  </div>

                  {otherScores.length > 0 ? (
                    <div className="mt-4 border-t border-[var(--border)] pt-4">
                      <p className="text-xs font-semibold uppercase tracking-wide text-[var(--text-muted)]">
                        Penilaian dari Juri Lain:
                      </p>
                      <div className="mt-2 flex flex-wrap gap-2">
                        {otherScores.map((score) => (
                          <div key={score.id} className="rounded-lg bg-[var(--surface-muted)] px-3 py-1.5 text-sm ring-1 ring-[var(--border)]">
                            <strong className="text-[var(--foreground)]">{score.jury_name}:</strong>{" "}
                            <span className="text-[var(--text-secondary)]">{score.score}</span>
                          </div>
                        ))}
                      </div>
                    </div>
                  ) : null}
                </Card>
              );
            })}
          </div>
        )}

        {data.meta.last_page > 1 ? (
          <nav className="flex items-center justify-center gap-3 text-sm" aria-label="Navigasi halaman">
            {data.meta.current_page > 1 ? (
              <Link
                href={`/jury/events/${data.event.id}/participants?page=${data.meta.current_page - 1}`}
                className="rounded-lg border border-[var(--border)] px-3 py-1.5 font-medium text-[var(--foreground)] hover:bg-[var(--surface-muted)]"
              >
                Sebelumnya
              </Link>
            ) : null}
            <span className="text-xs text-[var(--text-muted)]">
              Halaman {data.meta.current_page} dari {data.meta.last_page}
            </span>
            {data.meta.current_page < data.meta.last_page ? (
              <Link
                href={`/jury/events/${data.event.id}/participants?page=${data.meta.current_page + 1}`}
                className="rounded-lg border border-[var(--border)] px-3 py-1.5 font-medium text-[var(--foreground)] hover:bg-[var(--surface-muted)]"
              >
                Berikutnya
              </Link>
            ) : null}
          </nav>
        ) : null}
      </div>
    </AppFrame>
  );
}
