export const PUBLIC_API_BASE = process.env.API_BASE_URL ?? "http://127.0.0.1:8082/api";

export type EventStatusTone = "done" | "open" | "soon" | "full" | "closed";
export type RegistrationState = "open" | "before" | "after" | "full" | "inactive";

export type PublicEvent = {
  id: number;
  name: string;
  slug?: string | null;
  public_url?: string | null;
  format?: string;
  timezone?: string;
  contact_name?: string | null;
  contact_email?: string | null;
  contact_phone?: string | null;
  description: string | null;
  category: string | null;
  organizer: string | null;
  location: string | null;
  event_date: string | null;
  event_start: string | null;
  event_end: string | null;
  registration_start: string | null;
  registration_end: string | null;
  quota: number;
  registered_count: number;
  available_slots: number;
  price: number;
  banner_url: string | null;
  juknis_name?: string | null;
  juknis_url?: string | null;
  status: string;
  status_label: string;
  status_tone: EventStatusTone;
  registration_open: boolean;
  registration_state: RegistrationState;
  require_official_email: boolean;
  allowed_email_domains: string | null;
};

type RequestOptions = { signal?: AbortSignal };

/** Fetch publik tanpa token: halaman publik tidak boleh membaca cookie `jl_token`. */
function publicJson(path: string, init: RequestOptions = {}): Promise<Response> {
  return fetch(`${PUBLIC_API_BASE}${path}`, {
    headers: { Accept: "application/json" },
    cache: "no-store",
    signal: init.signal,
  });
}

export async function fetchPublicEvents(): Promise<PublicEvent[]> {
  const response = await publicJson("/public/events");

  if (!response.ok) {
    throw new Error(`Gagal memuat event (HTTP ${response.status}).`);
  }

  const data = (await response.json()) as { events: PublicEvent[] };

  return data.events;
}

/** Mengembalikan null bila event tidak ada atau tidak aktif (API membalas 404). */
export async function fetchPublicEvent(idOrSlug: string): Promise<PublicEvent | null> {
  const path = /^\d+$/.test(idOrSlug)
    ? `/public/events/${encodeURIComponent(idOrSlug)}`
    : `/public/events/by-slug/${encodeURIComponent(idOrSlug)}`;
  const response = await publicJson(path);

  if (response.status === 404) {
    return null;
  }

  if (!response.ok) {
    throw new Error(`Gagal memuat detail event (HTTP ${response.status}).`);
  }

  const data = (await response.json()) as { event: PublicEvent };

  return data.event;
}

/** Terima ID numerik maupun slug; fallback ke daftar publik untuk link lama. */
export async function resolvePublicEvent(idOrSlug: string): Promise<PublicEvent | null> {
  const direct = await fetchPublicEvent(idOrSlug);

  if (direct) {
    return direct;
  }

  const events = await fetchPublicEvents();

  return events.find((event) => event.slug === idOrSlug) ?? null;
}
