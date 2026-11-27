import { cookies } from "next/headers";
import { redirect } from "next/navigation";
import { AppShell, type SessionUser, type SummaryItem } from "@/components/app-shell";
import { navSectionsFor } from "@/lib/nav";
import { apiFetch } from "@/lib/api";
import { PageHeader, StatGrid } from "@/components/kit/ui";
import { AdminDashboard, type AdminDashboardData } from "@/components/admin/admin-dashboard";

type MeResponse = { user: SessionUser & { role: string; event_scope?: string }; summary: SummaryItem[] };

export const metadata = { title: "Dashboard — JuriOnline" };
export const instant = false;

export default async function DashboardPage() {
  const token = (await cookies()).get("jl_token")?.value;

  if (!token) {
    redirect("/login");
  }

  const data = await apiFetch<MeResponse>("/me");
  const isAdmin = data.user.role === "admin" || data.user.role === "admin_owner";

  const adminData = isAdmin
    ? await apiFetch<AdminDashboardData>("/admin/dashboard")
    : null;

  return (
    <AppShell user={data.user} sections={navSectionsFor(data.user.role, data.user.event_scope ?? "all")} unreadCount={0}>
      {adminData ? (
        <AdminDashboard data={adminData} userName={data.user.name} roleLabel={data.user.role_label} />
      ) : (
        <>
          <PageHeader
            eyebrow="Ruang kerja"
            title={`Selamat datang, ${data.user.name}`}
            description={`Masuk sebagai ${data.user.role_label} · ${data.user.email}`}
          />
          <div className="mt-6">
            <StatGrid items={data.summary} />
          </div>
        </>
      )}
    </AppShell>
  );
}
