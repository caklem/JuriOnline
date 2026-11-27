"use server";

import { API_BASE } from "@/lib/api";

export type ResetState = { ok?: boolean; error?: string; message?: string };

export async function resetPasswordAction(_prev: ResetState, formData: FormData): Promise<ResetState> {
  const response = await fetch(`${API_BASE}/reset-password`, {
    method: "POST",
    headers: { Accept: "application/json", "Content-Type": "application/json" },
    body: JSON.stringify({
      token: String(formData.get("token") ?? ""),
      email: String(formData.get("email") ?? ""),
      password: String(formData.get("password") ?? ""),
      password_confirmation: String(formData.get("password_confirmation") ?? ""),
    }),
    cache: "no-store",
  });

  const body = (await response.json().catch(() => null)) as
    | { message?: string; errors?: Record<string, string[]> }
    | null;

  if (!response.ok) {
    const first = body?.errors ? Object.values(body.errors)[0]?.[0] : undefined;
    return { error: first ?? body?.message ?? "Gagal menyimpan password baru." };
  }

  return { ok: true, message: body?.message ?? "Password berhasil diperbarui." };
}
