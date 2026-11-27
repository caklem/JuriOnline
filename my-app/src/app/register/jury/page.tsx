import { cookies } from "next/headers";
import { redirect } from "next/navigation";
import { AuthShell } from "@/components/auth-shell";
import { RegisterForm } from "../register-form";

export const metadata = { title: "JURI.ON — Daftar Akun Juri" };
export const instant = false;

export default async function RegisterJuryPage() {
  if ((await cookies()).get("jl_token")) redirect("/dashboard");

  return (
    <AuthShell title="Daftar Akun Juri" subtitle="Buat akun juri, lalu minta panitia meng-assign event">
      <div className="mb-6 rounded-2xl border border-indigo-100 bg-indigo-50 p-4 text-sm leading-6 text-indigo-900">
        <p className="font-semibold">Dua cara mendapatkan akun juri:</p>
        <ul className="mt-2 list-disc space-y-1 pl-5">
          <li>
            <strong>Disiapkan panitia:</strong> jika Anda sudah menerima email dan kode akses, tidak perlu daftar di sini. Langsung{" "}
            <a href="/login" className="font-semibold underline">masuk</a>.
          </li>
          <li>
            <strong>Daftar sendiri:</strong> buat akun di bawah, lalu hubungi panitia untuk di-assign ke event. Anda belum bisa menilai sebelum mendapat assignment.
          </li>
        </ul>
      </div>
      <RegisterForm role="jury" submitLabel="Daftar Sebagai Juri" />
    </AuthShell>
  );
}
