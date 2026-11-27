import { AppFrame } from "@/components/app-frame";
import { Card, CardHeader, EmptyState, PageHeader, StatGrid } from "@/components/kit/ui";
import { AccessDenied } from "@/components/owner/access-denied";
import { Pagination } from "@/components/owner/pagination";
import { ScoresTable } from "@/components/owner/scores-table";
import type { MeResponse, ScoresResponse } from "@/components/owner/types";
import { apiFetch } from "@/lib/api";

export const metadata = { title: "Semua Penilaian — JURI.ON" };

// Halaman bergantung pada cookie sesi; jangan diprerender saat build.
export const instant = false;

type SearchParams = Promise<Record<string, string | string[] | undefined>>;

export default async function AllScoresPage({ searchParams }: { searchParams: SearchParams }) {
  const params = await searchParams;
  const me = await apiFetch<MeResponse>("/me");

  const allowed = me.user.role === "admin_owner" || (me.user.role === "admin" && me.user.event_scope !== "own");

  if (!allowed) {
    return (
      <AppFrame>
        <AccessDenied description="Hanya Admin Owner dan Admin dengan akses semua event yang dapat melihat seluruh penilaian." />
      </AppFrame>
    );
  }

  const rawPage = Array.isArray(params.page) ? params.page[0] : params.page;
  const page = Math.max(Number(rawPage) || 1, 1);

  const data = await apiFetch<ScoresResponse>(`/owner/scores?page=${page}`);

  return (
    <AppFrame>
      <div className="space-y-6">
        <PageHeader title="Semua Penilaian Juri" description="Monitor dan kelola semua penilaian dari juri." />

        <StatGrid
          items={[
            { label: "Total Penilaian", value: data.stats.total_scores },
            { label: "Event Dinilai", value: data.stats.total_events_scored },
            { label: "Rata-rata Nilai", value: data.stats.total_scores > 0 ? data.stats.average_score : "-" },
            { label: "Total Juri Aktif", value: data.stats.total_juries },
          ]}
        />

        <Card>
          <CardHeader title="Daftar Penilaian" description={`${data.meta.total} penilaian ditemukan.`} />

          {data.data.length === 0 ? (
            <EmptyState
              title="Belum ada penilaian"
              description="Penilaian dari juri akan muncul di sini setelah dikirim."
            />
          ) : (
            <ScoresTable scores={data.data} />
          )}

          <Pagination
            basePath="/admin/scores"
            params={{}}
            page={data.meta.current_page}
            lastPage={data.meta.last_page}
          />
        </Card>
      </div>
    </AppFrame>
  );
}
