import { cookies } from "next/headers";
import { redirect } from "next/navigation";

export const API_BASE = process.env.API_BASE_URL ?? "http://127.0.0.1:8082/api";

export async function apiFetch<T>(path: string, init: RequestInit = {}): Promise<T> {
  const token = (await cookies()).get("jl_token")?.value;

  const response = await fetch(`${API_BASE}${path}`, {
    ...init,
    headers: {
      Accept: "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...(init.headers ?? {}),
    },
    cache: "no-store",
  });

  if (response.status === 401) {
    redirect("/login");
  }

  if (response.status === 403 && init.method && init.method !== "GET") {
    throw new Error("Akses ditolak.");
  }

  if (!response.ok) {
    const body = await response.json().catch(() => null);
    throw new Error(body?.message ?? `Permintaan gagal (HTTP ${response.status}).`);
  }

  return (await response.json()) as T;
}
