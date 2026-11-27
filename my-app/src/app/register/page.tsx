import { cookies } from "next/headers";
import { redirect } from "next/navigation";
import { AuthShell } from "@/components/auth-shell";
import { RegisterForm } from "./register-form";

export const metadata = { title: "JURI.ON — Buat Akun Baru" };
export const instant = false;

export default async function RegisterPage() {
  if ((await cookies()).get("jl_token")) redirect("/dashboard");

  return (
    <AuthShell title="Buat Akun Baru" subtitle="Daftar untuk mulai menggunakan JURI.ON">
      <RegisterForm role="participant" submitLabel="Daftar" />
    </AuthShell>
  );
}
