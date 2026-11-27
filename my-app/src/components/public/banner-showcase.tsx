"use client";

/* eslint-disable @next/next/no-img-element */

import { useEffect, useRef } from "react";
import { ExternalLink, Maximize2, X } from "lucide-react";

/**
 * Menampilkan banner utuh (tanpa crop) di atas latar blur, dengan dialog
 * full-size agar detail poster tetap bisa dibaca peserta.
 */
export function BannerShowcase({ src, alt }: { src: string; alt: string }) {
  const dialogRef = useRef<HTMLDialogElement>(null);

  useEffect(() => {
    const dialog = dialogRef.current;

    if (!dialog) {
      return;
    }

    const unlock = () => {
      document.body.style.overflow = "";
    };

    dialog.addEventListener("close", unlock);

    return () => {
      dialog.removeEventListener("close", unlock);
      document.body.style.overflow = "";
    };
  }, []);

  function open() {
    document.body.style.overflow = "hidden";
    dialogRef.current?.showModal();
  }

  function close() {
    dialogRef.current?.close();
  }

  return (
    <div>
      <div className="relative overflow-hidden bg-[var(--brand-navy)]">
        <img
          src={src}
          alt=""
          aria-hidden="true"
          className="absolute inset-0 h-full w-full scale-110 object-cover opacity-40 blur-2xl"
        />
        <div aria-hidden="true" className="absolute inset-0 bg-[var(--brand-navy)]/25" />

        <div className="relative flex min-h-[18rem] items-center justify-center p-4 sm:min-h-[24rem] lg:min-h-[30rem]">
          <img
            src={src}
            alt={alt}
            decoding="async"
            className="max-h-[17rem] w-auto max-w-full rounded-xl object-contain shadow-2xl sm:max-h-[23rem] lg:max-h-[29rem]"
          />
        </div>
      </div>

      <div className="flex flex-wrap items-center justify-between gap-2 border-t border-white/10 bg-[var(--brand-navy)] px-4 py-2.5">
        <p className="text-[11px] font-medium text-slate-300">Banner event · klik untuk memperbesar</p>
        <button
          type="button"
          onClick={open}
          className="inline-flex items-center gap-2 rounded-full bg-white/95 px-3.5 py-2 text-xs font-semibold text-[var(--brand-navy)] shadow-sm ring-1 ring-inset ring-white/60 transition hover:bg-white"
        >
          <Maximize2 className="h-3.5 w-3.5" aria-hidden="true" />
          Lihat banner lengkap
        </button>
      </div>

      <dialog
        ref={dialogRef}
        className="banner-dialog m-auto max-h-[94vh] w-fit max-w-[94vw] rounded-3xl bg-transparent p-0"
        onClick={(event) => {
          if (event.target === dialogRef.current) {
            close();
          }
        }}
      >
        <div className="flex max-h-[94vh] w-fit min-w-[16rem] flex-col overflow-hidden rounded-3xl bg-[var(--brand-navy)]">
          <div className="flex items-center justify-between gap-3 border-b border-white/10 px-3 py-2 sm:px-4">
            <p className="text-xs font-medium text-slate-300">Banner event</p>
            <div className="flex items-center gap-1.5">
              <a
                href={src}
                target="_blank"
                rel="noopener"
                aria-label="Buka gambar asli di tab baru"
                className="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-white ring-1 ring-inset ring-white/20 transition hover:bg-white/20"
              >
                <ExternalLink className="h-3.5 w-3.5" aria-hidden="true" />
                <span className="hidden sm:inline">Buka gambar asli</span>
              </a>
              <button
                type="button"
                onClick={close}
                aria-label="Tutup banner"
                className="grid h-9 w-9 place-items-center rounded-full bg-white/95 text-[var(--brand-navy)] shadow-lg transition hover:bg-white"
              >
                <X className="h-4 w-4" aria-hidden="true" />
              </button>
            </div>
          </div>
          <div className="flex min-h-0 flex-1 items-center justify-center p-2 sm:p-3">
            <img
              src={src}
              alt={alt}
              className="max-h-[80vh] w-auto max-w-full rounded-xl object-contain"
            />
          </div>
        </div>
      </dialog>
    </div>
  );
}
