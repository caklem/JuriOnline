"use server";

import { cookies } from "next/headers";
import { revalidatePath } from "next/cache";
import { API_BASE } from "@/lib/api";

export type ChatState = { ok?: boolean; error?: string; message?: string };

async function request(path: string, body: Record<string, unknown>) {
  const token = (await cookies()).get("jl_token")?.value;
  return fetch(`${API_BASE}${path}`, {
    method: "POST",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: JSON.stringify(body),
    cache: "no-store",
  });
}

export async function sendMessageAction(eventId: number, _prev: ChatState, formData: FormData): Promise<ChatState> {
  const response = await request(`/events/${eventId}/chat`, { body: String(formData.get("body") ?? "").trim() });

  if (!response.ok) {
    const body = await response.json().catch(() => null);
    return { error: body?.message ?? "Pesan gagal dikirim." };
  }

  revalidatePath(`/events/${eventId}/chat`);
  return { ok: true };
}

export async function reportMessageAction(messageId: number, eventId: number, _prev: ChatState, formData: FormData): Promise<ChatState> {
  const response = await request(`/messages/${messageId}/report`, { reason: String(formData.get("reason") ?? "").trim() });

  if (!response.ok) {
    const body = await response.json().catch(() => null);
    return { error: body?.message ?? "Laporan gagal dikirim." };
  }

  revalidatePath(`/events/${eventId}/chat`);
  return { ok: true, message: "Pesan dilaporkan ke panitia." };
}
