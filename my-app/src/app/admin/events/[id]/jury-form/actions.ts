"use server";

import { cookies } from "next/headers";
import { API_BASE } from "@/lib/api";

type SavePayload = {
  fields: { label: string; type: string; required: boolean; max?: number; description?: string }[];
  name?: string;
  useDefaultScore?: boolean;
  templateId?: number | null;
};

export async function saveJuryFormAction(eventId: number, payload: SavePayload) {
  const token = (await cookies()).get("jl_token")?.value;
  const response = await fetch(`${API_BASE}/admin/events/${eventId}/jury-forms`, {
    method: "POST",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: JSON.stringify({
      name: payload.name,
      form_template_id: payload.templateId ?? null,
      use_default_score: payload.useDefaultScore ?? true,
      criteria_fields: payload.fields.map((field) => ({
        label: field.label,
        type: field.type,
        required: field.required,
        max: field.type === "score" ? (field.max ?? 100) : null,
        description: field.description ?? "",
      })),
    }),
    cache: "no-store",
  });

  const body = (await response.json().catch(() => null)) as { message?: string; form_id?: number; errors?: Record<string, string[]> } | null;

  if (!response.ok) {
    const first = body?.errors ? Object.values(body.errors)[0]?.[0] : undefined;
    return { error: first ?? body?.message ?? "Form gagal disimpan." };
  }

  return { message: body?.message ?? "Form penilaian berhasil disimpan.", formId: body?.form_id };
}

export async function saveJuryTemplateAction(
  eventId: number,
  payload: { name: string; description: string; fields: { label: string; type: string; required: boolean; max?: number; description?: string; options?: string[] }[]; useDefaultScore: boolean },
) {
  const token = (await cookies()).get("jl_token")?.value;
  const response = await fetch(`${API_BASE}/admin/events/${eventId}/jury-forms/save-template`, {
    method: "POST",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: JSON.stringify({
      name: payload.name,
      description: payload.description,
      use_default_score: payload.useDefaultScore,
      criteria_fields: payload.fields.map((field) => ({
        label: field.label,
        type: field.type,
        required: field.required,
        max: field.type === "score" ? (field.max ?? 100) : null,
        description: field.description ?? "",
        options: field.options ?? [],
      })),
    }),
    cache: "no-store",
  });

  const body = (await response.json().catch(() => null)) as { message?: string; errors?: Record<string, string[]> } | null;

  if (!response.ok) {
    const first = body?.errors ? Object.values(body.errors)[0]?.[0] : undefined;
    return { error: first ?? body?.message ?? "Template gagal disimpan." };
  }

  return { message: body?.message ?? "Template berhasil disimpan." };
}
