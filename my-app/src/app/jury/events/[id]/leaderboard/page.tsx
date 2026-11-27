import { AppFrame } from "@/components/app-frame";
import { apiFetch } from "@/lib/api";
import { Card, EmptyState, PageHeader } from "@/components/kit/ui";
import { ActionLink, BackLink, LoadError } from "@/components/jury/chrome";
import type { LeaderboardEntry, LeaderboardResponse } from "@/components/jury/types";

export const metadata = { title: "Leaderboard — JURI.ON" };
export const instant = false;

function rankStyles(rank: number) {
  if (rank === 1) {
    return { border: "border-amber-300", badge: "bg-amber-100 text-amber-800" };
  }
  if (rank === 2) {
    return { border: "border-slate-300", badge: "bg-slate-200 text-slate-700" };
  }
  if (rank === 3) {
    return { border: "border-orange-300", badge: "bg-orange-100 text-orange-800" };
  }
  return { border: "border-[var(--border)]", badge: "bg-[var(--surface-muted)] text-[var(--text-muted)]" };
}

export default async function LeaderboardPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;

  let data: LeaderboardResponse | null = null;
  let loadError: string | null = null;

  try {
    data = await apiFetch<LeaderboardResponse>(`/jury/events/${id}/leaderboard`);
  } catch (error) {
    loadError = error instanceof Error ? error.message : "Terjadi kesalahan saat memuat leaderboard.";
  }

  if (!data) {
    return (
      <AppFrame>
        <LoadError title="Leaderboard tidak dapat dimuat" description={loadError ?? "Terjadi kesalahan saat memuat leaderboard."} />
      </AppFrame>
    );
  }

  return (
    <AppFrame>
      <div className="space-y-4">
        <BackLink href="/jury/events">Kembali ke Daftar Event</BackLink>

        <PageHeader
          title="Leaderboard"
          description={data.event.name}
          action={
            <ActionLink href={`/jury/events/${data.event.id}/participants`} variant="primary">
              Kembali ke Daftar Peserta
            </ActionLink>
          }
        />

        {data.registrations.length === 0 ? (
          <Card>
            <EmptyState
              title="Belum ada peserta"
              description="Belum ada peserta terverifikasi untuk event ini."
            />
          </Card>
        ) : (
          <div className="grid gap-4">
            {data.registrations.map((entry: LeaderboardEntry) => {
              const styles = rankStyles(entry.rank);
              const publicFeedbacks = entry.scores.filter((score) => score.public_feedback);

              return (
                <section
                  key={entry.registration_id}
                  className={`rounded-xl border-2 bg-[var(--background)] p-5 ${styles.border}`}
                >
                  <div className="flex flex-wrap items-start justify-between gap-4">
                    <div className="flex min-w-0 flex-1 items-start gap-3">
                      <span
                        className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-base font-semibold ${styles.badge}`}
                      >
                        {entry.rank}
                      </span>
                      <div className="min-w-0">
                        <h3 className="text-lg font-semibold text-[var(--foreground)]">{entry.user?.name}</h3>
                        <p className="text-sm text-[var(--text-muted)]">{entry.user?.email}</p>
                      </div>
                    </div>

                    <div className="flex flex-wrap items-center gap-4">
                      <div className="min-w-[120px] rounded-xl bg-slate-900 px-4 py-3 text-center text-white">
                        <p className="text-xs font-medium uppercase tracking-wide text-slate-300">Nilai Rata-rata</p>
                        <p className="mt-1 text-2xl font-semibold">{entry.average_score.toFixed(2)}</p>
                      </div>
                    </div>
                  </div>

                  <div className="mt-4 border-t border-[var(--border)] pt-4">
                    <strong className="text-sm text-[var(--foreground)]">
                      Penilaian dari {entry.jury_count} Juri:
                    </strong>
                    <div className="mt-2 flex flex-wrap gap-2">
                      {entry.scores.map((score) => (
                        <div
                          key={score.id}
                          className="rounded-lg border-l-2 border-indigo-500 bg-[var(--surface-muted)] px-3 py-1.5 text-sm"
                        >
                          <strong className="text-[var(--foreground)]">{score.jury_name}:</strong>{" "}
                          <span className="font-semibold text-indigo-700">{score.score.toFixed(2)}</span>
                        </div>
                      ))}
                    </div>
                  </div>

                  {publicFeedbacks.length > 0 ? (
                    <div className="mt-4 rounded-xl border-l-4 border-emerald-500 bg-emerald-50 px-4 py-3">
                      <strong className="flex items-center gap-2 text-sm text-emerald-800">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" className="h-4 w-4" aria-hidden="true">
                          <path strokeLinecap="round" strokeLinejoin="round" d="M8 10h8m-8 4h5m-9 6V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H8l-4 4Z" />
                        </svg>
                        Feedback Publik:
                      </strong>
                      <div className="mt-2 space-y-3">
                        {publicFeedbacks.map((score) => (
                          <div key={score.id}>
                            <strong className="text-sm text-[var(--foreground)]">{score.jury_name}:</strong>
                            <p className="mt-0.5 text-sm text-[var(--text-secondary)]">{score.public_feedback}</p>
                          </div>
                        ))}
                      </div>
                    </div>
                  ) : null}
                </section>
              );
            })}
          </div>
        )}
      </div>
    </AppFrame>
  );
}
