"use server";

import { cookies } from "next/headers";
import { revalidatePath } from "next/cache";
import { API_BASE } from "@/lib/api";

export type RevisionActionState = { ok?: boolean; error?: string };

export async function rejectRevisionAction(
  revisionId: number,
  _prev: RevisionActionState,
  formData: FormData,
): Promise<RevisionActionState> {
  const token = (await cookies()).get("jl_token")?.value;
  const response = await fetch(`${API_BASE}/jury/revisions/${revisionId}/reject`, {
    method: "POST",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: JSON.stringify({ notes: String(formData.get("notes") ?? "").trim() }),
    cache: "no-store",
  });

  const body = (await response.json().catch(() => null)) as
    | { message?: string; errors?: Record<string, string[]> }
    | null;

  if (!response.ok) {
    const first = body?.errors ? Object.values(body.errors)[0]?.[0] : undefined;
    return { error: first ?? body?.message ?? "Gagal menolak permintaan revisi." };
  }

  revalidatePath("/jury/revisions");
  return { ok: true };
}
