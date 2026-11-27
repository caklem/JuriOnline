import Link from "next/link";
import { notFound, redirect } from "next/navigation";
import type { Metadata } from "next";
import type { ReactNode } from "react";
import {
  ArrowLeft,
  CalendarDays,
  Check,
  CircleAlert,
  FileText,
  LockKeyhole,
  Mail,
  ShieldCheck,
  Sparkles,
  Upload,
  UserRoundCheck,
  UsersRound,
  Wallet,
} from "lucide-react";
import { apiFetch } from "@/lib/api";
import { safeNextPath } from "@/lib/next-path";
import { getOptionalViewer } from "@/lib/viewer";
import { resolvePublicEvent, type PublicEvent } from "@/components/public/api";
import { CtaLink } from "@/components/public/cta-link";
import { formatDate, formatPrice } from "@/components/public/format";
import { PublicShell } from "@/components/public/public-shell";
import { Reveal } from "@/components/public/reveal";
import { PageHero } from "@/components/public/section";
import { isNextRedirect, participantErrorMessage } from "@/components/participant/api-errors";
import { EventRegisterForm, type EventFormField } from "./register-form";

export const instant = false;

type RegistrationFormResponse = {
  event: {
    id: number;
    name: string;
    price: number;
    quota: number;
    registered_count: number;
    event_date: string | null;
    registration_open: boolean;
    participation_mode: "individual" | "team";
    team_min_members: number;
    team_max_members: number;
    require_official_email: boolean;
    allowed_email_domains: string[];
    email_domain_allowed: boolean;
  };
  fields: EventFormField[];
  registered: boolean;
  registration_id: number | null;
};

type PageProps = { params: Promise<{ id: string }> };

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { id } = await params;
  const event = await resolvePublicEvent(id);

  if (!event) {
    return { title: "Event tidak ditemukan — JURI.ON" };
  }

  return {
    title: `Pendaftaran ${event.name} — JURI.ON`,
    description: `Formulir pendaftaran peserta untuk ${event.name}.`,
  };
}

function allowedDomains(event: PublicEvent): string[] {
  return (event.allowed_email_domains ?? "")
    .split(",")
    .map((domain) => domain.trim())
    .filter(Boolean);
}

function closureNotes(event: PublicEvent): string[] {
  const notes: string[] = [];

  if (event.registration_state === "before") {
    notes.push(`Pendaftaran dibuka pada ${formatDate(event.registration_start)}.`);
  } else if (event.registration_state === "after") {
    notes.push(`Pendaftaran sudah ditutup pada ${formatDate(event.registration_end)}.`);
  } else if (event.registration_state === "full") {
    notes.push(`Kuota sudah penuh (${event.registered_count}/${event.quota} peserta).`);
  } else if (event.registration_state === "inactive") {
    notes.push(`Event sedang tidak aktif (status: ${event.status_label}).`);
  } else {
    notes.push("Pendaftaran event ini sedang tidak dibuka.");
  }

  return notes;
}

function SummaryRow({ label, children }: { label: string; children: ReactNode }) {
  return (
    <div className="py-3">
      <dt className="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{label}</dt>
      <dd className="mt-1 text-sm font-medium text-slate-800">{children}</dd>
    </div>
  );
}

export default async function RegisterEventPage({ params }: PageProps) {
  const { id } = await params;
  const [event, viewer] = await Promise.all([resolvePublicEvent(id), getOptionalViewer()]);

  if (!event) {
    notFound();
  }

  const nextPath = safeNextPath(`/events/${event.id}/register`, "/dashboard");
  const loginHref = `/login?next=${encodeURIComponent(nextPath)}`;
  const registerHref = `/register?next=${encodeURIComponent(nextPath)}`;
  const isParticipant = viewer?.role === "participant";
  const domains = allowedDomains(event);

  let formData: RegistrationFormResponse | null = null;
  let loadError: string | null = null;

  if (isParticipant) {
    try {
      formData = await apiFetch<RegistrationFormResponse>(`/events/${event.id}/registration-form`);

      if (formData.registered && formData.registration_id) {
        redirect(`/registrations/${formData.registration_id}`);
      }
    } catch (error) {
      if (isNextRedirect(error)) {
        throw error;
      }

      loadError = participantErrorMessage(error, "Formulir pendaftaran gagal dimuat. Coba muat ulang halaman ini.");
    }
  }

  const emailAllowed = formData?.event.email_domain_allowed ?? true;
  const canFillForm = Boolean(viewer && isParticipant && formData && emailAllowed && !loadError);
  const eventDate = event.event_date ?? event.event_start;
  const filledPercent =
    event.quota > 0
      ? Math.round(Math.min(100, Math.max(0, ((event.quota - event.available_slots) / event.quota) * 100)))
      : 100;

  const heroDescription = !event.registration_open
    ? "Pendaftaran event ini sedang tidak tersedia. Periksa jadwal dan informasi di bawah."
    : canFillForm
      ? "Lengkapi data berikut untuk mendaftar. Setelah formulir terkirim, unggah bukti pembayaran agar pendaftaran diverifikasi panitia."
      : viewer && !isParticipant
        ? "Pendaftaran event hanya dapat dilakukan melalui akun peserta. Buka dashboard untuk mengelola akun Anda."
        : isParticipant
          ? "Siapkan data diri dan dokumen yang diminta, lalu lengkapi formulir pendaftaran."
          : "Masuk atau buat akun peserta terlebih dahulu. Setelah itu Anda akan kembali ke halaman ini untuk mengisi formulir.";

  const steps = viewer
    ? [
        { title: "Lengkapi formulir", description: "Isi data peserta, tim, dan dokumen yang diminta." },
        {
          title: event.price > 0 ? "Unggah bukti pembayaran" : "Konfirmasi pendaftaran",
          description:
            event.price > 0
              ? `Bayar ${formatPrice(event.price)} lalu unggah bukti transfer.`
              : "Event ini gratis; tidak perlu mengunggah bukti pembayaran.",
        },
        { title: "Tunggu verifikasi", description: "Panitia memverifikasi pendaftaran dan pembayaran Anda." },
      ]
    : [
        { title: "Masuk atau buat akun", description: "Gunakan email aktif sebagai akun peserta." },
        { title: "Lengkapi formulir", description: "Isi data peserta, tim, dan dokumen yang diminta." },
        {
          title: event.price > 0 ? "Unggah bukti pembayaran" : "Konfirmasi pendaftaran",
          description:
            event.price > 0
              ? `Bayar ${formatPrice(event.price)} lalu unggah bukti transfer.`
              : "Event ini gratis; tidak perlu mengunggah bukti pembayaran.",
        },
      ];

  return (
    <PublicShell viewer={viewer}>
      <PageHero eyebrow="Pendaftaran peserta" title={event.name} description={heroDescription}>
        <div className="flex flex-wrap items-center gap-2.5">
          <span className="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/6 px-3.5 py-2 text-xs font-medium text-slate-200 backdrop-blur">
            <CalendarDays className="h-3.5 w-3.5 text-emerald-400" aria-hidden="true" />
            {formatDate(eventDate)}
          </span>
          <span className="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/6 px-3.5 py-2 text-xs font-medium text-slate-200 backdrop-blur">
            <Wallet className="h-3.5 w-3.5 text-amber-300" aria-hidden="true" />
            {formatPrice(event.price)}
          </span>
          <span className="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/6 px-3.5 py-2 text-xs font-medium text-slate-200 backdrop-blur">
            <UsersRound className="h-3.5 w-3.5 text-sky-300" aria-hidden="true" />
            {event.available_slots} dari {event.quota} slot tersisa
          </span>
          <span className="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/6 px-3.5 py-2 text-xs font-medium text-slate-200 backdrop-blur">
            <Sparkles className="h-3.5 w-3.5 text-[var(--brand-cobalt)]" aria-hidden="true" />
            {event.status_label}
          </span>
        </div>
      </PageHero>

      <section className="bg-[var(--brand-surface)] py-10 sm:py-14">
        <div className="mx-auto max-w-7xl px-6 lg:px-8">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <nav className="flex min-w-0 items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
              <Link href="/" className="transition hover:text-slate-900">Beranda</Link>
              <span aria-hidden="true" className="text-slate-300">/</span>
              <Link href="/events" className="transition hover:text-slate-900">Semua Event</Link>
              <span aria-hidden="true" className="text-slate-300">/</span>
              <Link href={`/events/${event.slug ?? event.id}`} className="truncate font-medium text-slate-900">
                {event.name}
              </Link>
              <span aria-hidden="true" className="text-slate-300">/</span>
              <span className="font-medium text-slate-900">Pendaftaran</span>
            </nav>

            <Link
              href={`/events/${event.slug ?? event.id}`}
              className="inline-flex items-center gap-1.5 text-sm font-medium text-slate-600 transition hover:text-slate-950"
            >
              <ArrowLeft className="h-4 w-4" aria-hidden="true" />
              Kembali ke detail event
            </Link>
          </div>

          <div className="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1.65fr)_minmax(19rem,1fr)] lg:items-start">
            <div className="space-y-6">
              <Reveal>
                <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_24px_60px_-48px_rgba(11,31,70,0.45)]">
                  <div className="border-b border-slate-100 px-6 py-5 sm:px-8">
                    <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-[var(--brand-cobalt-strong)]">
                      Formulir pendaftaran
                    </p>
                    <h2 className="mt-2 text-xl font-semibold tracking-tight text-[var(--brand-navy)]">
                      {!event.registration_open
                        ? "Pendaftaran tidak tersedia"
                        : canFillForm
                          ? "Data pendaftaran"
                          : isParticipant
                            ? "Formulir pendaftaran"
                            : "Masuk untuk mendaftar"}
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                      {!event.registration_open
                        ? "Anda belum dapat mengisi formulir karena pendaftaran event ini sedang ditutup."
                        : canFillForm
                          ? `Kuota ${event.registered_count}/${event.quota} peserta terisi. Pastikan data yang Anda isi sudah benar.`
                          : isParticipant
                            ? "Periksa status akun dan persyaratan event sebelum mengisi formulir."
                            : "Masuk dengan akun peserta atau buat akun baru. Setelah masuk, Anda akan kembali ke halaman ini dengan formulir yang sudah terbuka."}
                    </p>
                  </div>

                  <div className="px-6 py-6 sm:px-8">
                    {!event.registration_open ? (
                      <div className="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                        <p className="flex items-center gap-2 font-semibold">
                          <CircleAlert className="h-4 w-4" aria-hidden="true" />
                          Pendaftaran belum bisa dilakukan
                        </p>
                        <ul className="mt-3 list-disc space-y-1.5 pl-5">
                          {closureNotes(event).map((note) => (
                            <li key={note}>{note}</li>
                          ))}
                        </ul>
                        <div className="mt-5">
                          <CtaLink href={`/events/${event.slug ?? event.id}`} variant="secondary" size="sm">
                            Lihat detail event
                          </CtaLink>
                        </div>
                      </div>
                    ) : loadError ? (
                      <div className="rounded-xl border border-rose-200 bg-rose-50 p-5 text-sm text-rose-900">
                        <p className="flex items-center gap-2 font-semibold">
                          <CircleAlert className="h-4 w-4" aria-hidden="true" />
                          Formulir tidak dapat dimuat
                        </p>
                        <p className="mt-2 leading-6">{loadError}</p>
                      </div>
                    ) : !viewer ? (
                      <div>
                        <div className="flex items-start gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                          <span className="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white text-[var(--brand-cobalt-strong)] ring-1 ring-inset ring-slate-200">
                            <LockKeyhole className="h-5 w-5" aria-hidden="true" />
                          </span>
                          <div>
                            <h3 className="text-sm font-semibold text-slate-950">Akun peserta diperlukan</h3>
                            <p className="mt-1 text-sm leading-6 text-slate-600">
                              Satu akun untuk mengisi formulir, memantau verifikasi, dan melihat hasil evaluasi event.
                            </p>
                          </div>
                        </div>

                        <div className="mt-5 flex flex-col gap-3 sm:flex-row">
                          <CtaLink href={registerHref} size="md" className="w-full justify-center sm:w-auto">
                            Buat akun peserta
                          </CtaLink>
                          <CtaLink href={loginHref} variant="secondary" size="md" className="w-full justify-center sm:w-auto">
                            Sudah punya akun? Masuk
                          </CtaLink>
                        </div>

                        {event.require_official_email && domains.length > 0 ? (
                          <div className="mt-6 rounded-xl border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-900">
                            <p className="flex items-center gap-2 font-semibold">
                              <Mail className="h-4 w-4" aria-hidden="true" />
                              Wajib email resmi
                            </p>
                            <p className="mt-2 leading-6">
                              Pendaftaran ini hanya menerima email dengan domain:
                            </p>
                            <div className="mt-3 flex flex-wrap gap-2">
                              {domains.map((domain) => (
                                <span
                                  key={domain}
                                  className="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-indigo-800 ring-1 ring-inset ring-indigo-200"
                                >
                                  <Check className="h-3.5 w-3.5" aria-hidden="true" />
                                  {domain}
                                </span>
                              ))}
                            </div>
                          </div>
                        ) : null}

                        <div className="mt-6 border-t border-slate-100 pt-5">
                          <p className="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                            Yang perlu Anda siapkan
                          </p>
                          <ul className="mt-3 grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                            {[
                              "Data diri dan kontak aktif",
                              "Nama tim dan anggota (jika event tim)",
                              "Dokumen pendukung jika diminta",
                              "Bukti transfer setelah mendaftar",
                            ].map((item) => (
                              <li key={item} className="flex items-start gap-2">
                                <Check className="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" aria-hidden="true" />
                                {item}
                              </li>
                            ))}
                          </ul>
                        </div>
                      </div>
                    ) : !isParticipant ? (
                      <div className="rounded-xl border border-slate-200 bg-slate-50 p-5">
                        <p className="flex items-center gap-2 text-sm font-semibold text-slate-950">
                          <ShieldCheck className="h-4 w-4" aria-hidden="true" />
                          Akun ini bukan akun peserta
                        </p>
                        <p className="mt-2 text-sm leading-6 text-slate-600">
                          Anda masuk sebagai <strong>{viewer.roleLabel}</strong>. Pendaftaran event hanya dapat dilakukan
                          melalui akun peserta. Keluar lalu masuk dengan akun peserta untuk melanjutkan.
                        </p>
                        <div className="mt-5">
                          <CtaLink href="/dashboard" variant="secondary" size="sm">
                            Buka dashboard
                          </CtaLink>
                        </div>
                      </div>
                    ) : !emailAllowed ? (
                      <div className="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                        <p className="flex items-center gap-2 font-semibold">
                          <Mail className="h-4 w-4" aria-hidden="true" />
                          Email Anda belum memenuhi persyaratan
                        </p>
                        <p className="mt-2 leading-6">
                          Event ini hanya menerima email resmi dengan domain{" "}
                          <strong>{(formData?.event.allowed_email_domains ?? domains).join(", ")}</strong>. Akun{" "}
                          <strong>{viewer.email}</strong> belum dapat mendaftar.
                        </p>
                      </div>
                    ) : formData ? (
                      <div>
                        <div className="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm">
                          <span className="flex items-center gap-2 font-medium text-emerald-900">
                            <UserRoundCheck className="h-4 w-4" aria-hidden="true" />
                            Masuk sebagai {viewer.name}
                          </span>
                          <Link href="/my-registrations" className="font-semibold text-emerald-900 underline underline-offset-2">
                            Pendaftaran Saya
                          </Link>
                        </div>

                        <EventRegisterForm
                          eventId={formData.event.id}
                          fields={formData.fields}
                          participationMode={formData.event.participation_mode}
                          teamMin={formData.event.team_min_members}
                          teamMax={formData.event.team_max_members}
                          submitLabel="Kirim Pendaftaran"
                        />

                        <p className="mt-4 text-xs leading-5 text-slate-500">
                          Setelah pendaftaran terkirim, Anda akan diarahkan ke halaman status untuk{" "}
                          {event.price > 0
                            ? "mengunggah bukti pembayaran dan memantau verifikasi."
                            : "memantau verifikasi panitia."}
                        </p>
                      </div>
                    ) : null}
                  </div>
                </div>
              </Reveal>

              <Reveal delay={0.06}>
                <div className="rounded-2xl border border-slate-200 bg-white p-6 sm:p-7">
                  <div className="flex items-center justify-between gap-4">
                    <h2 className="text-sm font-semibold text-[var(--brand-navy)]">Alur pendaftaran</h2>
                    <span className="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                      {steps.length} langkah
                    </span>
                  </div>
                  <ol className="mt-5 grid gap-4 sm:grid-cols-3">
                    {steps.map((step, index) => (
                      <li key={step.title} className="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                        <span className="grid h-8 w-8 place-items-center rounded-lg bg-[var(--brand-navy)] text-xs font-semibold text-white">
                          {index + 1}
                        </span>
                        <p className="mt-3 text-sm font-semibold text-slate-900">{step.title}</p>
                        <p className="mt-1 text-xs leading-5 text-slate-600">{step.description}</p>
                      </li>
                    ))}
                  </ol>
                </div>
              </Reveal>
            </div>

            <aside className="space-y-6 lg:sticky lg:top-24">
              <Reveal delay={0.04}>
                <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_1px_2px_rgba(11,31,70,0.04)]">
                  <div className="flex items-center justify-between gap-3">
                    <h2 className="text-sm font-semibold text-[var(--brand-navy)]">Ringkasan Event</h2>
                    <span
                      className={`inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-semibold ${
                        event.registration_open
                          ? "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-100"
                          : "bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200"
                      }`}
                    >
                      {event.registration_open ? "Pendaftaran dibuka" : "Pendaftaran ditutup"}
                    </span>
                  </div>

                  <dl className="mt-3 divide-y divide-slate-100">
                    <SummaryRow label="Penyelenggara">{event.organizer ?? "-"}</SummaryRow>
                    <SummaryRow label="Lokasi">{event.location || "Online"}</SummaryRow>
                    <SummaryRow label="Tanggal event">{formatDate(eventDate)}</SummaryRow>
                    <SummaryRow label="Biaya">
                      <span className="font-semibold text-slate-900">{formatPrice(event.price)}</span>
                    </SummaryRow>
                    <SummaryRow label="Periode pendaftaran">
                      {formatDate(event.registration_start)} – {formatDate(event.registration_end)}
                    </SummaryRow>
                    <SummaryRow label="Kuota">
                      <span>
                        {event.available_slots > 0
                          ? `${event.available_slots} dari ${event.quota} tersisa`
                          : `Penuh (${event.registered_count}/${event.quota})`}
                      </span>
                      <span className="mt-2 block h-1.5 overflow-hidden rounded-full bg-slate-100">
                        <span
                          className={`block h-full rounded-full ${filledPercent >= 100 ? "bg-rose-500" : "bg-[var(--brand-cobalt)]"}`}
                          style={{ width: `${filledPercent}%` }}
                        />
                      </span>
                    </SummaryRow>
                  </dl>

                  <CtaLink
                    href={`/events/${event.slug ?? event.id}`}
                    variant="secondary"
                    size="sm"
                    className="mt-5 w-full justify-center"
                  >
                    Lihat detail event
                  </CtaLink>
                </div>
              </Reveal>

              {event.juknis_url ? (
                <Reveal delay={0.08}>
                  <div className="rounded-2xl border border-slate-200 bg-white p-5">
                    <div className="flex items-start gap-3">
                      <span className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-100">
                        <FileText className="h-5 w-5" aria-hidden="true" />
                      </span>
                      <div className="min-w-0">
                        <h2 className="text-sm font-semibold text-slate-950">Juknis event</h2>
                        <p className="mt-1 text-xs leading-5 text-slate-600">
                          Baca petunjuk teknis sebelum mengirim pendaftaran.
                        </p>
                      </div>
                    </div>
                    <a
                      href={event.juknis_url}
                      target="_blank"
                      rel="noopener"
                      className="mt-4 inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100"
                    >
                      Buka juknis
                    </a>
                  </div>
                </Reveal>
              ) : null}

              <Reveal delay={0.1}>
                <div className="rounded-2xl border border-slate-200 bg-white p-5">
                  <h2 className="text-sm font-semibold text-slate-950">Butuh bantuan?</h2>
                  <p className="mt-1 text-xs leading-5 text-slate-600">
                    Hubungi penyelenggara jika ada kendala saat mendaftar.
                  </p>
                  <div className="mt-4 space-y-2 text-sm">
                    {event.contact_name ? (
                      <p className="font-medium text-slate-800">{event.contact_name}</p>
                    ) : null}
                    {event.contact_email ? (
                      <a href={`mailto:${event.contact_email}`} className="block break-all text-[var(--brand-cobalt-strong)] underline underline-offset-2">
                        {event.contact_email}
                      </a>
                    ) : null}
                    {event.contact_phone ? (
                      <a href={`tel:${event.contact_phone}`} className="block text-[var(--brand-cobalt-strong)] underline underline-offset-2">
                        {event.contact_phone}
                      </a>
                    ) : null}
                    {!event.contact_name && !event.contact_email && !event.contact_phone ? (
                      <p className="text-slate-500">Kontak penyelenggara belum dicantumkan.</p>
                    ) : null}
                  </div>
                </div>
              </Reveal>

              {viewer ? (
                <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                  <p className="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                    <Upload className="h-3.5 w-3.5" aria-hidden="true" />
                    Setelah mendaftar
                  </p>
                  <p className="mt-2 text-sm leading-6 text-slate-600">
                    Pantau status pendaftaran dan pembayaran dari menu{" "}
                    <Link href="/my-registrations" className="font-semibold text-[var(--brand-cobalt-strong)] underline underline-offset-2">
                      Pendaftaran Saya
                    </Link>
                    .
                  </p>
                </div>
              ) : null}
            </aside>
          </div>
        </div>
      </section>
    </PublicShell>
  );
}
