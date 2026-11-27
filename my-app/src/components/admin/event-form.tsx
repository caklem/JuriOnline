"use client";

import { useActionState, useState } from "react";
import Link from "next/link";
import { Button } from "@/components/arc/button/button";
import { Input } from "@/components/arc/input/input";
import type { ActionResult } from "@/app/admin/_lib/api-action";
import type { CategoryOption, EventSummary } from "./types";

const CONTROL =
  "w-full rounded-lg border border-[var(--border)] bg-[var(--background)] px-3 py-2 text-sm text-[var(--foreground)] outline-none transition focus:border-[var(--foreground)] focus:ring-2 focus:ring-[var(--border)]";

const LABEL = "block text-sm font-medium text-[var(--foreground)]";

const INITIAL_STATE: ActionResult = {};

function FieldError({ message }: { message?: string }) {
  if (!message) return null;

  return (
    <p role="alert" className="mt-1 text-xs font-medium text-rose-600">
      {message}
    </p>
  );
}


function SectionTitle({ title, description }: { title: string; description?: string }) {
  return (
    <div className="mt-2 border-t border-[var(--border)] pt-5">
      <h2 className="text-sm font-semibold text-[var(--foreground)]">{title}</h2>
      {description ? <p className="mt-1 text-xs text-[var(--text-secondary)]">{description}</p> : null}
    </div>
  );
}

function packageHint(value: string): string {
  if (!value) return "";

  const base = new Date(`${value}T00:00:00`);

  if (Number.isNaN(base.getTime())) return "";

  const until = new Date(base.getFullYear(), base.getMonth() + 1, base.getDate());
  const label = until.toLocaleDateString("id-ID", { day: "2-digit", month: "short", year: "numeric" });

  return `Paket aktif sampai ${label} (1 bulan sejak hari H).`;
}

export function EventForm({
  mode,
  event,
  categories,
  action,
}: {
  mode: "create" | "edit";
  event?: EventSummary;
  categories: CategoryOption[];
  action: (prev: ActionResult, formData: FormData) => Promise<ActionResult>;
}) {
  const [state, formAction, pending] = useActionState(action, INITIAL_STATE);
  const [eventDate, setEventDate] = useState(event?.event_date ?? "");
  const [eventStart, setEventStart] = useState(event?.event_start ?? "");
  const [eventEnd, setEventEnd] = useState(event?.event_end ?? "");
  const errors = state.errors ?? {};
  const hint = packageHint(eventDate);

  const dateBlock = (
    <div className="rounded-xl border border-indigo-100 bg-indigo-50/50 p-4">
      <Input
        label="Tanggal Hari H *"
        name="event_date"
        type="date"
        value={eventDate}
        onChange={(changeEvent) => {
          const value = changeEvent.target.value;
          setEventDate(value);

          if (value) {
            if (!eventStart) setEventStart(value);
            if (!eventEnd) setEventEnd(value);
          }
        }}
        description="Tanggal pelaksanaan utama yang tampil ke juri. Paket event aktif 1 bulan sejak hari H."
        error={errors.event_date}
      />
      {hint ? <p className="mt-1 text-xs font-semibold text-indigo-700">{hint}</p> : null}
    </div>
  );

  const quotaHelp =
    mode === "edit" && event
      ? `Peserta terdaftar: ${event.registered_count} (kuota minimal harus ${event.registered_count})`
      : undefined;

  return (
    <form action={formAction} className="flex flex-col gap-5">
      {state.error ? (
        <p role="alert" className="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
          {state.error}
        </p>
      ) : null}

      {mode === "create" ? (
        <>
          <SectionTitle title="Informasi Event" description="Nama, kategori, dan deskripsi yang akan tampil ke peserta." />

          <Input label="Nama Event *" name="name" type="text" required error={errors.name} />

          <div>
            <label className={LABEL} htmlFor="category_id">
              Kategori
            </label>
            <select id="category_id" name="category_id" className={`${CONTROL} mt-1.5`} defaultValue="">
              <option value="">-- Pilih Kategori --</option>
              {categories.map((category) => (
                <option key={category.id} value={category.id}>
                  {category.name}
                </option>
              ))}
            </select>
            <FieldError message={errors.category_id} />
          </div>

          <div>
            <label className={LABEL} htmlFor="description">
              Deskripsi *
            </label>
            <textarea id="description" name="description" rows={5} required className={`${CONTROL} mt-1.5`} />
            <FieldError message={errors.description} />
          </div>

          <SectionTitle title="Identitas Publik" description="URL, format pelaksanaan, zona waktu, dan kontak resmi event." />

          <div className="grid gap-4 sm:grid-cols-2">
            <Input label="Slug URL" name="slug" type="text" placeholder="otomatis-dari-nama" error={errors.slug} description="Huruf kecil, angka, dan tanda hubung. Kosongkan untuk dibuat otomatis." />
            <div>
              <label className={LABEL} htmlFor="format">Format Pelaksanaan</label>
              <select id="format" name="format" className={`${CONTROL} mt-1.5`} defaultValue="offline">
                <option value="offline">Offline</option>
                <option value="online">Online</option>
                <option value="hybrid">Hybrid</option>
              </select>
              <FieldError message={errors.format} />
            </div>
          </div>

          <div className="grid gap-4 sm:grid-cols-3">
            <div>
              <label className={LABEL} htmlFor="participation_mode">Mode Partisipasi</label>
              <select id="participation_mode" name="participation_mode" className={`${CONTROL} mt-1.5`} defaultValue="individual">
                <option value="individual">Individu</option>
                <option value="team">Tim</option>
              </select>
              <FieldError message={errors.participation_mode} />
            </div>
            <div>
              <label className={LABEL} htmlFor="timezone">Zona Waktu</label>
              <select id="timezone" name="timezone" className={`${CONTROL} mt-1.5`} defaultValue="Asia/Jakarta">
                <option value="Asia/Jakarta">WIB (Asia/Jakarta)</option>
                <option value="Asia/Makassar">WITA (Asia/Makassar)</option>
                <option value="Asia/Jayapura">WIT (Asia/Jayapura)</option>
                <option value="UTC">UTC</option>
              </select>
              <FieldError message={errors.timezone} />
            </div>
            <Input label="Nama Kontak" name="contact_name" type="text" error={errors.contact_name} />
            <Input label="Email Kontak" name="contact_email" type="email" error={errors.contact_email} />
          </div>
          <Input label="Telepon/WhatsApp Kontak" name="contact_phone" type="text" error={errors.contact_phone} />

          <div className="grid gap-4 sm:grid-cols-2">
            <Input label="Anggota Tim Minimal" name="team_min_members" type="number" min={1} max={20} defaultValue={2} error={errors.team_min_members} description="Dipakai jika mode partisipasi Tim." />
            <Input label="Anggota Tim Maksimal" name="team_max_members" type="number" min={1} max={20} defaultValue={5} error={errors.team_max_members} />
          </div>

          <SectionTitle title="Harga & Kuota" description="Harga tiket dan batas jumlah peserta." />

          <div className="grid gap-4 sm:grid-cols-2">
            <Input label="Harga (Rp) *" name="price" type="number" min={0} step={1000} defaultValue={0} required error={errors.price} />
            <Input label="Kuota Peserta *" name="quota" type="number" min={1} required error={errors.quota} />
          </div>

          <SectionTitle title="Jadwal Pendaftaran & Hari H" description="Tentukan periode pendaftaran dan tanggal pelaksanaan utama." />

          <div className="grid gap-4 sm:grid-cols-2">
            <Input label="Mulai Pendaftaran *" name="registration_start" type="date" required error={errors.registration_start} />
            <Input label="Akhir Pendaftaran *" name="registration_end" type="date" required error={errors.registration_end} />
          </div>

          {dateBlock}

          <div className="grid gap-4 sm:grid-cols-2">
            <Input
              label="Mulai Event *"
              name="event_start"
              type="date"
              value={eventStart}
              onChange={(changeEvent) => setEventStart(changeEvent.target.value)}
              required
              error={errors.event_start}
            />
            <Input
              label="Akhir Event *"
              name="event_end"
              type="date"
              value={eventEnd}
              onChange={(changeEvent) => setEventEnd(changeEvent.target.value)}
              required
              error={errors.event_end}
            />
          </div>

          <SectionTitle title="Lokasi & Banner" description="Lokasi acara dan gambar banner event (opsional)." />

          <Input
            label="Lokasi"
            name="location"
            type="text"
            placeholder="Contoh: Hotel Bintang 5 Jakarta atau Online via Zoom"
            error={errors.location}
          />

          <div>
            <label className={LABEL} htmlFor="banner">
              Banner Event (JPG, PNG - Max 2MB)
            </label>
            <input
              id="banner"
              name="banner"
              type="file"
              accept="image/jpeg,image/png"
              className="mt-1.5 block w-full text-sm text-[var(--text-secondary)] file:mr-3 file:rounded-lg file:border-0 file:bg-[var(--surface-muted)] file:px-3 file:py-2 file:text-sm file:font-medium file:text-[var(--foreground)]"
            />
            <FieldError message={errors.banner} />
          </div>

          <div>
            <label className={LABEL} htmlFor="juknis">
              Dokumen Juknis (PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX - Max 10MB)
            </label>
            <input
              id="juknis"
              name="juknis"
              type="file"
              accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
              className="mt-1.5 block w-full text-sm text-[var(--text-secondary)] file:mr-3 file:rounded-lg file:border-0 file:bg-[var(--surface-muted)] file:px-3 file:py-2 file:text-sm file:font-medium file:text-[var(--foreground)]"
            />
            <p className="mt-1 text-xs text-[var(--text-secondary)]">
              Peserta dapat melihat dokumen ini sebelum mendaftar.
            </p>
            <FieldError message={errors.juknis} />
          </div>
        </>
      ) : (
        <div className="grid gap-x-8 gap-y-5 lg:grid-cols-2">
          <div className="flex flex-col gap-5">
            <Input label="Nama Event *" name="name" type="text" defaultValue={event?.name} required error={errors.name} />

            <div>
              <label className={LABEL} htmlFor="category_id">
                Kategori
              </label>
              <select
                id="category_id"
                name="category_id"
                className={`${CONTROL} mt-1.5`}
                defaultValue={event?.category?.id ? String(event.category.id) : ""}
              >
                <option value="">-- Pilih Kategori --</option>
                {categories.map((category) => (
                  <option key={category.id} value={category.id}>
                    {category.name}
                  </option>
                ))}
              </select>
              <FieldError message={errors.category_id} />
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
              <Input label="Slug URL" name="slug" type="text" defaultValue={event?.slug ?? ""} error={errors.slug} description="Kosongkan untuk mempertahankan slug saat ini." />
              <div>
                <label className={LABEL} htmlFor="format">Format Pelaksanaan</label>
                <select id="format" name="format" className={`${CONTROL} mt-1.5`} defaultValue={event?.format ?? "offline"}>
                  <option value="offline">Offline</option>
                  <option value="online">Online</option>
                  <option value="hybrid">Hybrid</option>
                </select>
                <FieldError message={errors.format} />
              </div>
            </div>

            <div className="grid gap-4 sm:grid-cols-3">
              <div>
                <label className={LABEL} htmlFor="participation_mode">Mode Partisipasi</label>
                <select id="participation_mode" name="participation_mode" className={`${CONTROL} mt-1.5`} defaultValue={event?.participation_mode ?? "individual"}>
                  <option value="individual">Individu</option>
                  <option value="team">Tim</option>
                </select>
                <FieldError message={errors.participation_mode} />
              </div>
              <div>
                <label className={LABEL} htmlFor="timezone">Zona Waktu</label>
                <select id="timezone" name="timezone" className={`${CONTROL} mt-1.5`} defaultValue={event?.timezone ?? "Asia/Jakarta"}>
                  <option value="Asia/Jakarta">WIB (Asia/Jakarta)</option>
                  <option value="Asia/Makassar">WITA (Asia/Makassar)</option>
                  <option value="Asia/Jayapura">WIT (Asia/Jayapura)</option>
                  <option value="UTC">UTC</option>
                </select>
                <FieldError message={errors.timezone} />
              </div>
              <Input label="Nama Kontak" name="contact_name" type="text" defaultValue={event?.contact_name ?? ""} error={errors.contact_name} />
              <Input label="Email Kontak" name="contact_email" type="email" defaultValue={event?.contact_email ?? ""} error={errors.contact_email} />
            </div>
            <Input label="Telepon/WhatsApp Kontak" name="contact_phone" type="text" defaultValue={event?.contact_phone ?? ""} error={errors.contact_phone} />

            <div className="grid gap-4 sm:grid-cols-2">
              <Input label="Anggota Tim Minimal" name="team_min_members" type="number" min={1} max={20} defaultValue={event?.team_min_members ?? 2} error={errors.team_min_members} />
              <Input label="Anggota Tim Maksimal" name="team_max_members" type="number" min={1} max={20} defaultValue={event?.team_max_members ?? 5} error={errors.team_max_members} />
            </div>

            <div>
              <label className={LABEL} htmlFor="description">
                Deskripsi *
              </label>
              <textarea
                id="description"
                name="description"
                rows={5}
                required
                defaultValue={event?.description ?? ""}
                className={`${CONTROL} mt-1.5`}
              />
              <FieldError message={errors.description} />
            </div>

            <Input
              label="Lokasi"
              name="location"
              type="text"
              defaultValue={event?.location ?? ""}
              placeholder="Contoh: Jakarta Convention Center atau Online"
              error={errors.location}
            />

            <div>
              <label className={LABEL} htmlFor="status">
                Status *
              </label>
              <select id="status" name="status" className={`${CONTROL} mt-1.5`} defaultValue={event?.status ?? "draft"} required>
                <option value="draft">Draft</option>
                <option value="active">Active</option>
                <option value="closed">Closed</option>
                <option value="completed">Completed</option>
                <option value="archived">Diarsipkan</option>
              </select>
              <FieldError message={errors.status} />
            </div>

            <div>
              <label className={LABEL} htmlFor="banner">
                Banner Event (JPG/PNG, Max 2MB)
              </label>
              {event?.banner_url ? (
                <div className="mb-3 mt-1.5 rounded-lg border border-[var(--border)] bg-[var(--surface-muted)] p-3">
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img src={event.banner_url} alt="Banner saat ini" className="max-w-[200px] rounded-md" />
                  <p className="mt-2 text-xs text-[var(--text-secondary)]">Banner saat ini (akan diganti jika upload baru)</p>
                </div>
              ) : null}
              <input
                id="banner"
                name="banner"
                type="file"
                accept="image/jpeg,image/png"
                className="mt-1.5 block w-full text-sm text-[var(--text-secondary)] file:mr-3 file:rounded-lg file:border-0 file:bg-[var(--surface-muted)] file:px-3 file:py-2 file:text-sm file:font-medium file:text-[var(--foreground)]"
              />
              <FieldError message={errors.banner} />
            </div>

            <div>
              <label className={LABEL} htmlFor="juknis">
                Dokumen Juknis (PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX - Max 10MB)
              </label>
              {event?.juknis_url ? (
                <div className="mb-3 mt-1.5 rounded-lg border border-[var(--border)] bg-[var(--surface-muted)] p-3">
                  <p className="text-sm font-medium text-[var(--foreground)]">{event.juknis_name ?? "Juknis saat ini"}</p>
                  <a href={event.juknis_url} target="_blank" rel="noopener" className="mt-1 inline-block text-xs font-semibold underline underline-offset-2">
                    Lihat juknis saat ini
                  </a>
                  <label className="mt-2 flex items-center gap-2 text-xs text-[var(--text-secondary)]">
                    <input type="checkbox" name="remove_juknis" value="1" />
                    Hapus juknis saat ini
                  </label>
                </div>
              ) : null}
              <input
                id="juknis"
                name="juknis"
                type="file"
                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                className="mt-1.5 block w-full text-sm text-[var(--text-secondary)] file:mr-3 file:rounded-lg file:border-0 file:bg-[var(--surface-muted)] file:px-3 file:py-2 file:text-sm file:font-medium file:text-[var(--foreground)]"
              />
              <p className="mt-1 text-xs text-[var(--text-secondary)]">
                Peserta dapat melihat dokumen ini sebelum mendaftar.
              </p>
              <FieldError message={errors.juknis} />
            </div>
          </div>

          <div className="flex flex-col gap-5">
            <Input
              label="Harga (Rp) *"
              name="price"
              type="number"
              min={0}
              step={1000}
              defaultValue={event?.price ?? 0}
              required
              description="Masukkan 0 untuk event gratis"
              error={errors.price}
            />

            <Input
              label="Kuota Peserta *"
              name="quota"
              type="number"
              min={event?.registered_count ?? 1}
              defaultValue={event?.quota}
              required
              description={quotaHelp}
              error={errors.quota}
            />

            <h3 className="border-t border-[var(--border)] pt-5 text-sm font-semibold uppercase tracking-wide text-[var(--text-muted)]">
              Periode Pendaftaran
            </h3>

            <Input
              label="Tanggal Mulai Pendaftaran *"
              name="registration_start"
              type="date"
              defaultValue={event?.registration_start ?? ""}
              required
              error={errors.registration_start}
            />
            <Input
              label="Tanggal Akhir Pendaftaran *"
              name="registration_end"
              type="date"
              defaultValue={event?.registration_end ?? ""}
              required
              error={errors.registration_end}
            />

            <h3 className="border-t border-[var(--border)] pt-5 text-sm font-semibold uppercase tracking-wide text-[var(--text-muted)]">
              Pelaksanaan (Hari H)
            </h3>

            {dateBlock}

            <Input
              label="Tanggal Mulai Event *"
              name="event_start"
              type="date"
              value={eventStart}
              onChange={(changeEvent) => setEventStart(changeEvent.target.value)}
              required
              error={errors.event_start}
            />
            <Input
              label="Tanggal Akhir Event *"
              name="event_end"
              type="date"
              value={eventEnd}
              onChange={(changeEvent) => setEventEnd(changeEvent.target.value)}
              required
              error={errors.event_end}
            />
          </div>
        </div>
      )}

      <div className="mt-1 flex flex-wrap items-center gap-3 border-t border-[var(--border)] pt-5">
        <Button type="submit" variant="primary" loading={pending}>
          {mode === "create" ? "Buat Event" : "Update Event"}
        </Button>
        <Link
          href={mode === "create" ? "/admin/events" : `/admin/events/${event?.id ?? ""}`}
          className="inline-flex items-center justify-center rounded-lg border border-[var(--border)] px-3 py-2 text-sm font-medium text-[var(--text-secondary)] transition hover:bg-[var(--surface-muted)]"
        >
          Batal
        </Link>
      </div>
    </form>
  );
}
