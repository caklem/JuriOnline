"use server";

import { cookies } from "next/headers";
import { API_BASE } from "@/lib/api";

export type RegisterEventState = { ok?: boolean; error?: string; registrationId?: number };

export async function registerEventAction(
  eventId: number,
  _prev: RegisterEventState,
  formData: FormData,
): Promise<RegisterEventState> {
  const token = (await cookies()).get("jl_token")?.value;
  const response = await fetch(`${API_BASE}/events/${eventId}/register`, {
    method: "POST",
    headers: { Accept: "application/json", ...(token ? { Authorization: `Bearer ${token}` } : {}) },
    body: formData,
    cache: "no-store",
  });

  const body = (await response.json().catch(() => null)) as
    | { message?: string; errors?: Record<string, string[]>; registration_id?: number }
    | null;

  if (!response.ok) {
    const first = body?.errors ? Object.values(body.errors)[0]?.[0] : undefined;
    return { error: first ?? body?.message ?? "Pendaftaran gagal. Coba lagi." };
  }

  return { ok: true, registrationId: body?.registration_id };
}
