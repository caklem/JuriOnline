import { CURRENT_YEAR } from "@/lib/year";
import type { ReactNode } from "react";
import { BrandLockup } from "@/components/brand/lockup";

export function AuthShell({
  title,
  subtitle,
  children,
}: {
  title: string;
  subtitle: string;
  children: ReactNode;
}) {
  return (
    <main className="grid min-h-screen lg:grid-cols-2">
      <section className="hidden flex-col justify-between bg-[var(--brand-navy)] p-12 text-white lg:flex">
        <BrandLockup href="/login" variant="dark" tile descriptor />
        <div className="max-w-md">
          <h1 className="text-3xl font-semibold leading-tight">
            Penilaian kompetisi yang adil, transparan, dan efisien.
          </h1>
          <p className="mt-4 text-sm leading-6 text-white/70">
            Satu alur kerja untuk pendaftaran peserta, penilaian juri, verifikasi pembayaran, dan rekapitulasi hasil.
          </p>
        </div>
        <p className="text-xs text-white/50">
          © {CURRENT_YEAR} JURI.ON. Dibuat untuk kompetisi yang lebih adil.
        </p>
      </section>

      <section className="flex items-center justify-center px-5 py-12">
        <div className="w-full max-w-md">
          <div className="lg:hidden">
            <BrandLockup href="/login" />
          </div>
          <h2 className="mt-2 text-2xl font-semibold text-[var(--foreground)]">{title}</h2>
          <p className="mt-2 text-sm text-[var(--text-secondary)]">{subtitle}</p>
          {children}
          <p className="mt-8 text-center text-[11px] text-[var(--text-muted)]">
            © {CURRENT_YEAR} JURI.ON. Semua hak dilindungi.
          </p>
        </div>
      </section>
    </main>
  );
}
