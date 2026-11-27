import { AppFrame } from "@/components/app-frame";
import { Badge, Card, CardHeader, EmptyState, PageHeader, StatGrid } from "@/components/kit/ui";
import { Input } from "@/components/arc/input/input";
import { AccessDenied } from "@/components/owner/access-denied";
import { CreateUserForm } from "@/components/owner/create-user-form";
import { Pagination } from "@/components/owner/pagination";
import { UsersTable } from "@/components/owner/users-table";
import type { MeResponse, UsersResponse } from "@/components/owner/types";
import { apiFetch } from "@/lib/api";

export const metadata = { title: "Manajemen Pengguna — JURI.ON" };

// Halaman bergantung pada cookie sesi; jangan diprerender saat build.
export const instant = false;

const SELECT_CLASS =
  "h-10 w-full rounded-lg border border-[var(--border)] bg-[var(--background)] px-3 text-sm text-[var(--foreground)] outline-none transition focus:border-[var(--accent)] focus:ring-2 focus:ring-[var(--accent-subtle)]";

type SearchParams = Promise<Record<string, string | string[] | undefined>>;

export default async function OwnerUsersPage({ searchParams }: { searchParams: SearchParams }) {
  const params = await searchParams;
  const me = await apiFetch<MeResponse>("/me");

  if (me.user.role !== "admin_owner") {
    return (
      <AppFrame>
        <AccessDenied description="Hanya Admin Owner yang dapat mengelola akun pengguna platform." />
      </AppFrame>
    );
  }

  const first = (value: string | string[] | undefined) => (Array.isArray(value) ? (value[0] ?? "") : (value ?? ""));

  const q = first(params.q).trim();
  const role = first(params.role);
  const status = first(params.status);
  const page = Math.max(Number(first(params.page)) || 1, 1);

  const search = new URLSearchParams();
  if (q) search.set("q", q);
  if (role) search.set("role", role);
  if (status) search.set("status", status);
  search.set("page", String(page));

  const data = await apiFetch<UsersResponse>(`/owner/users?${search.toString()}`);

  const activeParams: Record<string, string> = {};
  if (q) activeParams.q = q;
  if (role) activeParams.role = role;
  if (status) activeParams.status = status;

  return (
    <AppFrame>
      <div className="space-y-6">
        <PageHeader
          eyebrow="Area Admin Owner"
          title="Manajemen Pengguna"
          description="Kelola akun, role, dan status aktif seluruh pengguna platform."
          action={<Badge tone="info">{data.summary.owners} admin owner aktif</Badge>}
        />

        <StatGrid
          items={[
            { label: "Total Pengguna", value: data.summary.total },
            { label: "Akun Aktif", value: data.summary.active },
            { label: "Akun Nonaktif", value: data.summary.inactive },
            { label: "Admin Owner Aktif", value: data.summary.owners },
          ]}
        />

        <CreateUserForm roles={data.roles} />

        <Card>
          <CardHeader title="Daftar Pengguna" description={`${data.meta.total} akun ditemukan.`} />

          <form
            method="GET"
            action="/owner/users"
            className="grid gap-3 border-b border-[var(--border)] px-5 py-4 md:grid-cols-[minmax(0,1fr)_14rem_12rem_auto]"
          >
            <Input
              type="search"
              name="q"
              label="Cari pengguna"
              placeholder="Cari nama atau email"
              defaultValue={q}
            />
            <div className="space-y-1.5">
              <label htmlFor="filter-role" className="text-[13px] font-medium text-[var(--foreground)]">
                Role
              </label>
              <select id="filter-role" name="role" className={SELECT_CLASS} defaultValue={role}>
                <option value="">Semua role</option>
                {data.roles.map((item) => (
                  <option key={item.id} value={item.name}>
                    {item.display_name}
                  </option>
                ))}
              </select>
            </div>
            <div className="space-y-1.5">
              <label htmlFor="filter-status" className="text-[13px] font-medium text-[var(--foreground)]">
                Status
              </label>
              <select id="filter-status" name="status" className={SELECT_CLASS} defaultValue={status}>
                <option value="">Semua status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
              </select>
            </div>
            <div className="flex items-end gap-2">
              <button
                type="submit"
                className="h-10 rounded-lg bg-[var(--accent)] px-4 text-sm font-semibold text-white transition hover:opacity-90"
              >
                Filter
              </button>
              <a
                href="/owner/users"
                className="grid h-10 place-items-center rounded-lg border border-[var(--border)] px-4 text-sm font-medium text-[var(--foreground)] transition hover:bg-[var(--surface-muted)]"
              >
                Reset
              </a>
            </div>
          </form>

          {data.data.length === 0 ? (
            <EmptyState
              title="Tidak Ada Pengguna"
              description="Tidak ada akun yang cocok dengan pencarian atau filter Anda."
            />
          ) : (
            <UsersTable users={data.data} roles={data.roles} selfId={me.user.id} />
          )}

          <Pagination
            basePath="/owner/users"
            params={activeParams}
            page={data.meta.current_page}
            lastPage={data.meta.last_page}
          />
        </Card>
      </div>
    </AppFrame>
  );
}
