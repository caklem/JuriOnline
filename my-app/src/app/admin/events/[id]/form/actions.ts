"use server";

import { cookies } from "next/headers";
import { API_BASE } from "@/lib/api";

type SavePayload = { fields: { label: string; type: string; required: boolean; options?: string[] }[] };

export async function saveRegistrationFormAction(eventId: number, payload: SavePayload) {
  const token = (await cookies()).get("jl_token")?.value;
  const response = await fetch(`${API_BASE}/admin/events/${eventId}/registration-form`, {
    method: "PUT",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: JSON.stringify({ fields: payload.fields }),
    cache: "no-store",
  });

  const body = (await response.json().catch(() => null)) as { message?: string; errors?: Record<string, string[]> } | null;

  if (!response.ok) {
    const first = body?.errors ? Object.values(body.errors)[0]?.[0] : undefined;
    return { error: first ?? body?.message ?? "Form gagal disimpan." };
  }

  return { message: body?.message ?? "Form pendaftaran berhasil disimpan." };
}
