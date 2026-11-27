"use server";

import { cookies } from "next/headers";
import { API_BASE } from "@/lib/api";

export type RegisterState = { ok?: boolean; error?: string; message?: string };

async function post(path: string, payload: Record<string, unknown>): Promise<Response> {
  return fetch(`${API_BASE}${path}`, {
    method: "POST",
    headers: { Accept: "application/json", "Content-Type": "application/json" },
    body: JSON.stringify(payload),
    cache: "no-store",
  });
}

async function readError(response: Response): Promise<RegisterState> {
  const body = (await response.json().catch(() => null)) as
    | { message?: string; errors?: Record<string, string[]> }
    | null;
  const first = body?.errors ? Object.values(body.errors)[0]?.[0] : undefined;

  return { error: first ?? body?.message ?? "Terjadi kesalahan. Coba lagi." };
}

export async function registerAction(_prev: RegisterState, formData: FormData): Promise<RegisterState> {
  const role = String(formData.get("role") ?? "participant");
  const path = role === "jury" ? "/register/jury" : "/register";

  const response = await post(path, {
    name: String(formData.get("name") ?? "").trim(),
    email: String(formData.get("email") ?? "").trim(),
    password: String(formData.get("password") ?? ""),
    password_confirmation: String(formData.get("password_confirmation") ?? ""),
  });

  if (!response.ok) return readError(response);

  const data = (await response.json()) as { token?: string };
  if (data.token) {
    (await cookies()).set("jl_token", data.token, {
      httpOnly: true,
      sameSite: "lax",
      path: "/",
      secure: process.env.NODE_ENV === "production",
    });
  }

  return { ok: true };
}

export async function forgotPasswordAction(_prev: RegisterState, formData: FormData): Promise<RegisterState> {
  const response = await post("/forgot-password", {
    email: String(formData.get("email") ?? "").trim(),
  });

  if (!response.ok) return readError(response);

  const body = (await response.json()) as { message?: string };

  return { message: body.message };
}
