"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useEffect, useState } from "react";
import { AnimatePresence, motion, useReducedMotion } from "motion/react";
import { Menu, X } from "lucide-react";
import Button from "@/components/arc/button/button";
import { CtaLink } from "./cta-link";
import { BrandLockup } from "@/components/brand/lockup";
import type { OptionalViewer } from "@/lib/viewer";

const LINKS = [
  { href: "/", label: "Beranda" },
  { href: "/events", label: "Event" },
  { href: "/alur", label: "Alur" },
  { href: "/about", label: "Tentang" },
  { href: "/company-profile", label: "Company Profile" },
];

function isActive(pathname: string, href: string): boolean {
  return href === "/" ? pathname === "/" : pathname === href || pathname.startsWith(`${href}/`);
}

export function SiteHeader({ viewer = null }: { viewer?: OptionalViewer | null }) {
  const pathname = usePathname();
  const reduced = useReducedMotion();
  const [open, setOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 8);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });

    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  return (
    <header
      className={`sticky top-0 z-50 border-b bg-white/85 backdrop-blur transition-shadow duration-300 ${
        scrolled ? "border-slate-200/90 shadow-[0_10px_30px_-24px_rgba(11,31,70,0.5)]" : "border-slate-200/60"
      }`}
    >
      <div className="mx-auto flex h-20 max-w-7xl items-center justify-between px-6 lg:px-8">
        <BrandLockup href="/" />

        <nav className="hidden items-center gap-6 md:flex lg:gap-8" aria-label="Navigasi utama">
          {LINKS.map((link) => {
            const active = isActive(pathname, link.href);

            return (
              <Link
                key={link.href}
                href={link.href}
                aria-current={active ? "page" : undefined}
                className={`relative text-sm font-medium transition ${
                  active ? "text-[var(--brand-navy)]" : "text-slate-600 hover:text-[var(--brand-navy)]"
                }`}
              >
                {link.label}
                {active ? (
                  <motion.span
                    layoutId="public-nav-active"
                    className="absolute -bottom-1.5 left-0 right-0 h-0.5 rounded-full bg-[var(--brand-cobalt)]"
                    transition={reduced ? { duration: 0 } : { type: "spring", stiffness: 380, damping: 32 }}
                  />
                ) : null}
              </Link>
            );
          })}
        </nav>

        <div className="hidden items-center gap-3 md:flex">
          {viewer ? (
            <>
              <span className="max-w-[12rem] truncate text-sm font-medium text-slate-600" title={viewer.name}>
                {viewer.name}
              </span>
              <CtaLink href="/dashboard" size="sm">
                Dashboard
              </CtaLink>
            </>
          ) : (
            <>
              <Link
                href="/login"
                className="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 hover:text-slate-950"
              >
                Masuk
              </Link>
              <CtaLink href="/register" size="sm">
                Daftar gratis
              </CtaLink>
            </>
          )}
        </div>

        <div className="md:hidden">
          <Button
            variant="secondary"
            size="sm"
            aria-label={open ? "Tutup menu navigasi" : "Buka menu navigasi"}
            aria-expanded={open}
            aria-controls="public-mobile-nav"
            onClick={() => setOpen((value) => !value)}
          >
            {open ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
          </Button>
        </div>
      </div>

      <AnimatePresence initial={false}>
        {open ? (
          <motion.div
            key="mobile-menu"
            className="overflow-hidden border-t border-slate-200 bg-white md:hidden"
            initial={reduced ? false : { height: 0, opacity: 0 }}
            animate={{ height: "auto", opacity: 1 }}
            exit={reduced ? { opacity: 0 } : { height: 0, opacity: 0 }}
            transition={reduced ? { duration: 0 } : { duration: 0.24, ease: [0.22, 1, 0.36, 1] }}
          >
            <nav className="mx-auto flex max-w-7xl flex-col gap-1 px-6 py-4" aria-label="Navigasi mobile">
              {LINKS.map((link) => (
                <Link
                  key={link.href}
                  href={link.href}
                  onClick={() => setOpen(false)}
                  className={`rounded-lg px-3 py-2.5 text-sm font-medium transition ${
                    isActive(pathname, link.href)
                      ? "bg-slate-100 text-slate-950"
                      : "text-slate-700 hover:bg-slate-50"
                  }`}
                >
                  {link.label}
                </Link>
              ))}
              <div className="mt-3 flex flex-col gap-2 border-t border-slate-100 pt-4">
                {viewer ? (
                  <>
                    <p className="truncate px-1 text-xs font-medium text-slate-500">{viewer.email || viewer.name}</p>
                    <Link
                      href="/dashboard"
                      onClick={() => setOpen(false)}
                      className="rounded-lg bg-slate-950 px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                      Dashboard
                    </Link>
                  </>
                ) : (
                  <>
                    <Link
                      href="/login"
                      onClick={() => setOpen(false)}
                      className="rounded-lg border border-slate-200 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                      Masuk
                    </Link>
                    <Link
                      href="/register"
                      onClick={() => setOpen(false)}
                      className="rounded-lg bg-slate-950 px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                      Daftar gratis
                    </Link>
                  </>
                )}
              </div>
            </nav>
          </motion.div>
        ) : null}
      </AnimatePresence>
    </header>
  );
}
