"use server";

import { revalidatePath } from "next/cache";
import { cookies } from "next/headers";
import { redirect } from "next/navigation";
import { API_BASE } from "@/lib/api";

export type ScoreFormState = {
  message?: string;
  errors?: Record<string, string>;
};

export type InvitationResult = {
  ok: boolean;
  message?: string;
  eventId?: number;
};

async function authedFetch(path: string, init: RequestInit = {}): Promise<Response> {
  const token = (await cookies()).get("jl_token")?.value;

  if (!token) {
    redirect("/login");
  }

  return fetch(`${API_BASE}${path}`, {
    ...init,
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
      ...(init.headers ?? {}),
    },
    cache: "no-store",
  });
}

async function readJson(response: Response): Promise<Record<string, unknown> | null> {
  return (await response.json().catch(() => null)) as Record<string, unknown> | null;
}

function firstErrors(body: Record<string, unknown> | null): Record<string, string> | undefined {
  const errors = body?.errors;

  if (!errors || typeof errors !== "object") {
    return undefined;
  }

  const mapped: Record<string, string> = {};
  for (const [field, messages] of Object.entries(errors as Record<string, unknown>)) {
    if (Array.isArray(messages) && typeof messages[0] === "string") {
      mapped[field] = messages[0];
    }
  }

  return Object.keys(mapped).length > 0 ? mapped : undefined;
}

export async function respondInvitation(
  assignmentId: number,
  action: "accept" | "decline",
): Promise<InvitationResult> {
  const response = await authedFetch(`/jury/invitations/${assignmentId}/${action}`, {
    method: "POST",
    body: JSON.stringify({}),
  });
  const body = await readJson(response);

  if (response.status === 401) {
    redirect("/login");
  }

  if (!response.ok) {
    return {
      ok: false,
      message: typeof body?.message === "string" ? body.message : "Undangan gagal diproses. Coba lagi.",
    };
  }

  revalidatePath("/jury/invitations");
  revalidatePath("/jury/events");

  return {
    ok: true,
    message: typeof body?.message === "string" ? body.message : undefined,
    eventId: typeof body?.event_id === "number" ? body.event_id : undefined,
  };
}

export async function saveScoreAction(
  _previous: ScoreFormState,
  formData: FormData,
): Promise<ScoreFormState> {
  const registrationId = Number(formData.get("registration_id"));
  const mode = String(formData.get("mode") ?? "create");
  const scoreId = Number(formData.get("score_id"));
  const eventId = Number(formData.get("event_id"));

  const payload: Record<string, string> = {};

  for (const [key, value] of formData.entries()) {
    if (typeof value !== "string") {
      continue;
    }

    if (key === "registration_id" || key === "mode" || key === "score_id" || key === "event_id") {
      continue;
    }

    payload[key] = value;
  }

  const endpoint =
    mode === "edit" ? `/jury/scores/${scoreId}` : `/jury/registrations/${registrationId}/score`;
  const method = mode === "edit" ? "PATCH" : "POST";

  let response: Response;

  try {
    response = await authedFetch(endpoint, { method, body: JSON.stringify(payload) });
  } catch (error) {
    if (
      error &&
      typeof error === "object" &&
      "digest" in error &&
      typeof error.digest === "string" &&
      error.digest.startsWith("NEXT_REDIRECT")
    ) {
      throw error;
    }

    return { message: "Tidak dapat menghubungi server. Coba lagi sebentar." };
  }

  if (response.status === 401) {
    redirect("/login");
  }

  const body = await readJson(response);

  if (response.status === 422) {
    return {
      message: typeof body?.message === "string" ? body.message : "Periksa kembali isian Anda.",
      errors: firstErrors(body),
    };
  }

  if (!response.ok) {
    return {
      message: typeof body?.message === "string" ? body.message : "Penilaian gagal disimpan. Coba lagi.",
    };
  }

  revalidatePath("/jury/events");
  revalidatePath(`/jury/events/${eventId}/participants`);

  const message = typeof body?.message === "string" ? `?notice=${encodeURIComponent(body.message)}` : "";

  if (mode === "edit") {
    redirect(`/jury/events/${eventId}/participants${message}`);
  }

  const nextId = typeof body?.next_registration_id === "number" ? body.next_registration_id : null;

  redirect(
    nextId
      ? `/jury/registrations/${nextId}/score${message}`
      : `/jury/events/${eventId}/participants${message}`,
  );
}
