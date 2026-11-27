"use client";

import { useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import { AnimatePresence, motion } from "motion/react";
import { Search, X } from "lucide-react";
import type { NavSection } from "@/lib/nav";

export function CommandMenu({ sections }: { sections: NavSection[] }) {
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState("");

  useEffect(() => {
    const onKeyDown = (event: KeyboardEvent) => {
      if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === "k") {
        event.preventDefault();
        setOpen((current) => !current);
      }

      if (event.key === "Escape") setOpen(false);
    };

    window.addEventListener("keydown", onKeyDown);
    return () => window.removeEventListener("keydown", onKeyDown);
  }, []);

  const results = useMemo(() => {
    const needle = query.trim().toLowerCase();

    return sections
      .map((section) => ({
        ...section,
        items: section.items.filter(
          (item) =>
            !needle ||
            item.label.toLowerCase().includes(needle) ||
            (item.description ?? "").toLowerCase().includes(needle),
        ),
      }))
      .filter((section) => section.items.length > 0);
  }, [query, sections]);

  return (
    <>
      <button
        type="button"
        onClick={() => setOpen(true)}
        className="hidden items-center gap-2 rounded-xl border border-[var(--border)] bg-[var(--background)] px-3 py-2 text-sm text-[var(--text-secondary)] transition hover:bg-[var(--surface-muted)] sm:flex"
      >
        <Search size={15} strokeWidth={1.8} aria-hidden="true" />
        <span>Cari menu</span>
        <kbd className="ml-2 rounded-md border border-[var(--border)] bg-[var(--surface-muted)] px-1.5 py-0.5 text-[10px] font-semibold">Ctrl K</kbd>
      </button>

      <button
        type="button"
        onClick={() => setOpen(true)}
        className="flex items-center gap-1.5 rounded-xl border border-[var(--border)] px-2.5 py-2 text-xs font-semibold text-[var(--text-secondary)] sm:hidden"
      >
        <Search size={15} strokeWidth={1.8} aria-hidden="true" />
        Menu
      </button>

      <AnimatePresence>
        {open ? (
          <motion.div
            className="fixed inset-0 z-50 flex items-start justify-center bg-slate-950/50 px-4 pt-24 backdrop-blur-sm"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={() => setOpen(false)}
          >
            <motion.div
              role="dialog"
              aria-modal="true"
              aria-label="Cari menu"
              className="w-full max-w-lg overflow-hidden rounded-2xl border border-[var(--border)] bg-[var(--background)] shadow-2xl"
              initial={{ opacity: 0, y: -8, scale: 0.98 }}
              animate={{ opacity: 1, y: 0, scale: 1 }}
              exit={{ opacity: 0, y: -8, scale: 0.98 }}
              transition={{ duration: 0.18 }}
              onClick={(event) => event.stopPropagation()}
            >
              <div className="flex items-center gap-3 border-b border-[var(--border)] px-4 py-3">
                <Search size={16} strokeWidth={1.8} className="text-[var(--text-muted)]" aria-hidden="true" />
                <input
                  autoFocus
                  value={query}
                  onChange={(event) => setQuery(event.target.value)}
                  placeholder="Cari halaman atau tindakan..."
                  className="w-full bg-transparent text-sm text-[var(--foreground)] outline-none placeholder:text-[var(--text-muted)]"
                />
                <button type="button" onClick={() => setOpen(false)} aria-label="Tutup pencarian" className="text-[var(--text-muted)] hover:text-[var(--foreground)]">
                  <X size={16} strokeWidth={1.8} aria-hidden="true" />
                </button>
              </div>

              <div className="max-h-80 overflow-y-auto p-2">
                {results.length === 0 ? (
                  <p className="px-3 py-6 text-center text-sm text-[var(--text-secondary)]">Menu tidak ditemukan.</p>
                ) : (
                  results.map((section) => (
                    <div key={section.title} className="mb-2">
                      <p className="px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-[var(--text-muted)]">
                        {section.title}
                      </p>
                      {section.items.map((item) => (
                        <button
                          key={item.href}
                          type="button"
                          onClick={() => {
                            setOpen(false);
                            setQuery("");
                            router.push(item.href);
                          }}
                          className="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-left transition hover:bg-[var(--surface-muted)]"
                        >
                          <span className="min-w-0">
                            <span className="block text-sm font-medium text-[var(--foreground)]">{item.label}</span>
                            {item.description ? (
                              <span className="block text-xs text-[var(--text-secondary)]">{item.description}</span>
                            ) : null}
                          </span>
                          <span className="text-xs text-[var(--text-muted)]">↵</span>
                        </button>
                      ))}
                    </div>
                  ))
                )}
              </div>
            </motion.div>
          </motion.div>
        ) : null}
      </AnimatePresence>
    </>
  );
}
