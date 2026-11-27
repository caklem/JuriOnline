"use server";

import { cookies } from "next/headers";
import { API_BASE } from "@/lib/api";

export type ActionState = { ok?: boolean; message?: string; error?: string };

async function request(path: string, method: string, body?: BodyInit | Record<string, unknown>) {
  const token = (await cookies()).get("jl_token")?.value;
  const isJson = body && !(body instanceof FormData);

  return fetch(`${API_BASE}${path}`, {
    method,
    headers: {
      Accept: "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...(isJson ? { "Content-Type": "application/json" } : {}),
    },
    body: isJson ? JSON.stringify(body) : (body as BodyInit | undefined),
    cache: "no-store",
  });
}

async function toState(response: Response): Promise<ActionState> {
  const body = (await response.json().catch(() => null)) as
    | { message?: string; errors?: Record<string, string[]> }
    | null;

  if (!response.ok) {
    const firstError = body?.errors ? Object.values(body.errors)[0]?.[0] : undefined;
    return { error: firstError ?? body?.message ?? "Terjadi kesalahan. Coba lagi." };
  }

  return { ok: true, message: body?.message ?? "Berhasil disimpan." };
}

export async function updateProfileAction(_prev: ActionState, formData: FormData): Promise<ActionState> {
  const response = await request("/profile", "PATCH", {
    name: String(formData.get("name") ?? "").trim(),
    email: String(formData.get("email") ?? "").trim(),
  });

  return toState(response);
}

export async function updatePasswordAction(_prev: ActionState, formData: FormData): Promise<ActionState> {
  const response = await request("/profile/password", "PUT", {
    current_password: String(formData.get("current_password") ?? ""),
    password: String(formData.get("password") ?? ""),
    password_confirmation: String(formData.get("password_confirmation") ?? ""),
  });

  return toState(response);
}

export async function deleteAccountAction(_prev: ActionState, formData: FormData): Promise<ActionState> {
  const response = await request("/profile", "DELETE", {
    password: String(formData.get("password") ?? ""),
  });

  const state = await toState(response);

  if (state.ok) {
    (await cookies()).delete("jl_token");
  }

  return state;
}

export async function updateJuryProfileAction(_prev: ActionState, formData: FormData): Promise<ActionState> {
  const response = await request("/profile/jury", "PUT", {
    phone: String(formData.get("phone") ?? "").trim() || null,
    organization: String(formData.get("organization") ?? "").trim() || null,
    expertise: String(formData.get("expertise") ?? "").trim() || null,
    bio: String(formData.get("bio") ?? "").trim() || null,
    whatsapp_number: String(formData.get("whatsapp_number") ?? "").trim() || null,
    whatsapp_consent: formData.get("whatsapp_consent") === "1",
  });

  return toState(response);
}
