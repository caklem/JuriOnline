import { AuthShell } from "@/components/auth-shell";
import { ResetForm } from "../reset-form";

export const metadata = { title: "JURI.ON — Buat Password Baru" };
export const instant = false;

export default async function ResetPasswordPage({
  params,
  searchParams,
}: {
  params: Promise<{ token: string }>;
  searchParams: Promise<{ email?: string }>;
}) {
  const { token } = await params;
  const { email } = await searchParams;

  return (
    <AuthShell title="Buat Password Baru" subtitle="Masukkan password baru untuk akun Anda.">
      <ResetForm token={token} email={email ?? ""} />
    </AuthShell>
  );
}
