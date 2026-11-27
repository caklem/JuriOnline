import { cookies } from "next/headers";
import { redirect } from "next/navigation";
import { BrandLockup } from "@/components/brand/lockup";
import { LoginForm } from "./login-form";

export const metadata = { title: "Masuk — JURI.ON" };

// Halaman bergantung pada cookie sesi; jangan diprerender saat build.
export const instant = false;

export default async function LoginPage() {
  if ((await cookies()).get("jl_token")) {
    redirect("/dashboard");
  }

  return (
    <main className="grid min-h-screen lg:grid-cols-2">
      <section className="hidden flex-col justify-between bg-[var(--brand-navy)] p-12 text-white lg:flex">
        <BrandLockup href="/login" variant="dark" tile descriptor />
        <div className="max-w-md">
          <h1 className="text-3xl font-semibold leading-tight">
            Satu tempat untuk mengelola event, juri, dan penilaian.
          </h1>
          <p className="mt-4 text-sm leading-6 text-white/70">
            Masuk untuk melihat undangan juri, menilai peserta, memverifikasi pembayaran,
            dan memantau hasil lomba secara real time.
          </p>
        </div>
        <p className="text-xs text-white/50">Chrome, Edge, Firefox, dan Safari terbaru.</p>
      </section>

      <section className="flex items-center justify-center px-5 py-12">
        <div className="w-full max-w-md">
          <div className="lg:hidden">
            <BrandLockup href="/login" />
          </div>
          <h2 className="mt-2 text-2xl font-semibold text-[var(--foreground)]">Masuk ke Akun</h2>
          <p className="mt-2 text-sm text-[var(--text-secondary)]">
            Selamat datang kembali! Silakan masuk ke akun Anda
          </p>
          <LoginForm />
        </div>
      </section>
    </main>
  );
}
