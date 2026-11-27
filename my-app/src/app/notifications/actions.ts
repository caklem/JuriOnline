"use server";

import { cookies } from "next/headers";
import { API_BASE } from "@/lib/api";

export async function markAllReadAction(): Promise<void> {
  const token = (await cookies()).get("jl_token")?.value;

  await fetch(`${API_BASE}/notifications/read`, {
    method: "POST",
    headers: { Accept: "application/json", ...(token ? { Authorization: `Bearer ${token}` } : {}) },
    cache: "no-store",
  });
}
