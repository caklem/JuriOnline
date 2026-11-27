"use server";

import { cookies } from "next/headers";

const API = process.env.API_BASE_URL ?? "http://127.0.0.1:8081/api";

export type LoginState = { error?: string; ok?: boolean };

export async function loginAction(_prev: LoginState, formData: FormData): Promise<LoginState> {
  const email = String(formData.get("email") ?? "").trim();
  const password = String(formData.get("password") ?? "");
  const remember = formData.get("remember") === "on";

  if (!email || !password) {
    return { error: "Email dan password wajib diisi." };
  }

  let response: Response;

  try {
    response = await fetch(`${API}/login`, {
      method: "POST",
      headers: { Accept: "application/json", "Content-Type": "application/json" },
      body: JSON.stringify({ email, password, device_name: "juri-online-web" }),
      cache: "no-store",
    });
  } catch {
    return { error: "Tidak dapat menghubungi server. Pastikan aplikasi Laravel berjalan." };
  }

  if (response.status === 422) {
    const body = (await response.json().catch(() => null)) as { message?: string; errors?: Record<string, string[]> } | null;
    return { error: body?.errors?.email?.[0] ?? body?.message ?? "Email atau password salah." };
  }

  if (!response.ok) {
    return { error: "Gagal masuk. Coba lagi sebentar." };
  }

  const data = (await response.json()) as { token?: string };

  if (!data.token) {
    return { error: "Respons server tidak valid." };
  }

  (await cookies()).set("jl_token", data.token, {
    httpOnly: true,
    sameSite: "lax",
    path: "/",
    secure: process.env.NODE_ENV === "production",
    ...(remember ? { maxAge: 60 * 60 * 24 * 30 } : {}),
  });

  return { ok: true };
}

export async function logoutAction(): Promise<void> {
  const store = await cookies();
  const token = store.get("jl_token")?.value;

  if (token) {
    await fetch(`${API}/logout`, {
      method: "POST",
      headers: { Accept: "application/json", Authorization: `Bearer ${token}` },
      cache: "no-store",
    }).catch(() => null);
  }

  store.delete("jl_token");
}
