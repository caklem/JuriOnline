import { cookies } from "next/headers";
import { redirect } from "next/navigation";
import { AuthShell } from "@/components/auth-shell";
import { ForgotForm } from "./forgot-form";

export const metadata = { title: "JURI.ON — Lupa Password" };
export const instant = false;

export default async function ForgotPasswordPage() {
  if ((await cookies()).get("jl_token")) redirect("/dashboard");

  return (
    <AuthShell
      title="Lupa Password"
      subtitle="Masukkan email akun Anda. Kami akan mengirimkan link untuk membuat password baru."
    >
      <ForgotForm />
    </AuthShell>
  );
}
