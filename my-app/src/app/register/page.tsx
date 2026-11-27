import { cookies } from "next/headers";
import { redirect } from "next/navigation";
import { AuthShell } from "@/components/auth-shell";
import { safeNextPath } from "@/lib/next-path";
import { RegisterForm } from "./register-form";

export const metadata = { title: "JURI.ON — Buat Akun Baru" };
export const instant = false;

type RegisterPageProps = {
  searchParams: Promise<{ next?: string | string[] }>;
};

export default async function RegisterPage({ searchParams }: RegisterPageProps) {
  const { next: nextParam } = await searchParams;
  const next = safeNextPath(nextParam);

  if ((await cookies()).get("jl_token")) redirect(next);

  return (
    <AuthShell title="Buat Akun Baru" subtitle="Daftar untuk mulai menggunakan JURI.ON">
      <RegisterForm role="participant" submitLabel="Daftar" next={next} />
      <p className="mt-6 text-center text-sm text-[var(--text-secondary)]">
        Sudah punya akun?{" "}
        <a
          href={`/login?next=${encodeURIComponent(next)}`}
          className="font-semibold underline underline-offset-2"
        >
          Masuk
        </a>
      </p>
    </AuthShell>
  );
}
