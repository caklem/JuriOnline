import { CURRENT_YEAR } from "@/lib/year";
import Link from "next/link";
import type { ReactNode } from "react";
import type { OptionalViewer } from "@/lib/viewer";
import { SiteHeader } from "./site-header";
import { BrandLockup } from "@/components/brand/lockup";
import { Reveal, ScrollProgress } from "./reveal";

export function SiteFooter({ viewer = null }: { viewer?: OptionalViewer | null }) {
  return (
    <footer className="relative overflow-hidden border-t border-white/10 bg-slate-950 text-slate-300">
      <div aria-hidden="true" className="jurion-dotgrid pointer-events-none absolute inset-0 opacity-25" />
      <div
        aria-hidden="true"
        className="pointer-events-none absolute -top-40 right-10 h-80 w-80 rounded-full bg-[var(--brand-cobalt)]/15 blur-[100px]"
      />
      <div className="relative mx-auto max-w-7xl px-6 py-14 lg:px-8">
        <Reveal>
        <div className="grid gap-10 md:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
          <div>
            <BrandLockup href="/" variant="dark" tile descriptor />
            <p className="mt-4 max-w-sm text-sm leading-6 text-slate-400">
              Platform penilaian digital untuk event kompetisi yang adil, transparan, dan efisien.
            </p>
          </div>

          <div>
            <h2 className="text-sm font-semibold text-white">Produk</h2>
            <ul className="mt-4 space-y-3 text-sm">
              <li><Link href="/alur" className="transition hover:text-white">Alur &amp; panduan</Link></li>
              <li><Link href="/#fitur" className="transition hover:text-white">Fitur</Link></li>
              <li><Link href="/#cara-kerja" className="transition hover:text-white">Cara kerja</Link></li>
              <li><Link href="/#events" className="transition hover:text-white">Event</Link></li>
            </ul>
          </div>

          <div>
            <h2 className="text-sm font-semibold text-white">Perusahaan</h2>
            <ul className="mt-4 space-y-3 text-sm">
              <li><Link href="/about" className="transition hover:text-white">Tentang</Link></li>
              <li><Link href="/company-profile" className="transition hover:text-white">Company profile</Link></li>
              <li><a href="mailto:info@jurionline.com" className="transition hover:text-white">Kontak</a></li>
            </ul>
          </div>

          <div>
            <h2 className="text-sm font-semibold text-white">Akun</h2>
            <ul className="mt-4 space-y-3 text-sm">
              {viewer ? (
                <>
                  <li><Link href="/dashboard" className="transition hover:text-white">Dashboard</Link></li>
                  <li><Link href="/profile" className="transition hover:text-white">Profil</Link></li>
                </>
              ) : (
                <>
                  <li><Link href="/login" className="transition hover:text-white">Masuk</Link></li>
                  <li><Link href="/register" className="transition hover:text-white">Daftar gratis</Link></li>
                </>
              )}
            </ul>
          </div>
        </div>
        </Reveal>

        <Reveal delay={0.06}>
        <div className="mt-12 flex flex-col gap-4 border-t border-white/10 pt-6 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
          <p>&copy; {CURRENT_YEAR} JURI.ON. All rights reserved.</p>
          <p>Dibuat untuk kompetisi yang lebih adil.</p>
        </div>
        </Reveal>
      </div>
    </footer>
  );
}

export function PublicShell({
  children,
  viewer = null,
}: {
  children: ReactNode;
  viewer?: OptionalViewer | null;
}) {
  return (
    <div className="flex min-h-screen flex-col bg-white">
      <ScrollProgress />
      <SiteHeader viewer={viewer} />
      <main className="flex-1">{children}</main>
      <SiteFooter viewer={viewer} />
    </div>
  );
}
