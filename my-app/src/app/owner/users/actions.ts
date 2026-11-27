"use server";

import { revalidatePath } from "next/cache";
import { unstable_rethrow } from "next/navigation";
import { apiFetch } from "@/lib/api";

export type ActionState = { error?: string; success?: string };

type ApiMessage = { message?: string };

async function send(path: string, method: string, body: Record<string, unknown>): Promise<ActionState> {
  try {
    const response = await apiFetch<ApiMessage>(path, {
      method,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body),
    });

    revalidatePath("/owner/users");

    return { success: response.message ?? "Perubahan tersimpan." };
  } catch (error) {
    unstable_rethrow(error);
    return { error: error instanceof Error ? error.message : "Terjadi kesalahan. Coba lagi." };
  }
}

export async function createUserAction(_prev: ActionState, formData: FormData): Promise<ActionState> {
  return send("/owner/users", "POST", {
    name: String(formData.get("name") ?? "").trim(),
    email: String(formData.get("email") ?? "").trim(),
    password: String(formData.get("password") ?? ""),
    password_confirmation: String(formData.get("password_confirmation") ?? ""),
    role_id: Number(formData.get("role_id") ?? 0),
    event_scope: String(formData.get("event_scope") ?? "all"),
  });
}

export async function updateRoleAction(_prev: ActionState, formData: FormData): Promise<ActionState> {
  const userId = Number(formData.get("user_id") ?? 0);
  const eventScope = String(formData.get("event_scope") ?? "");

  const body: Record<string, unknown> = {
    role_id: Number(formData.get("role_id") ?? 0),
  };

  if (eventScope) {
    body.event_scope = eventScope;
  }

  return send(`/owner/users/${userId}/role`, "PATCH", body);
}

export async function updateScopeAction(_prev: ActionState, formData: FormData): Promise<ActionState> {
  const userId = Number(formData.get("user_id") ?? 0);

  return send(`/owner/users/${userId}/scope`, "PATCH", {
    event_scope: String(formData.get("event_scope") ?? "all"),
  });
}

export async function toggleStatusAction(_prev: ActionState, formData: FormData): Promise<ActionState> {
  const userId = Number(formData.get("user_id") ?? 0);

  return send(`/owner/users/${userId}/status`, "PATCH", {});
}
