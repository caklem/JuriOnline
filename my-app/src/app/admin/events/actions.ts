"use server";

import { redirect } from "next/navigation";
import { apiRequest, toActionResult, toFailureState, type ActionResult } from "../_lib/api-action";

/** Buang file kosong dari form (input file yang tidak dipilih tetap terkirim sebagai File 0 byte). */
function pruneEmptyFiles(formData: FormData): FormData {
  for (const [key, value] of [...formData.entries()]) {
    if (value instanceof File && (value.size === 0 || value.name === "")) {
      formData.delete(key);
    }
  }

  return formData;
}


export async function createEventAction(_prev: ActionResult, formData: FormData): Promise<ActionResult> {
  let state: ActionResult;

  try {
    const response = await apiRequest("/admin/events", "POST", pruneEmptyFiles(formData));
    state = await toActionResult(response);
  } catch (error) {
    return toFailureState(error);
  }

  if (!state.ok) {
    return state;
  }

  const eventId = state.payload?.event?.id;

  redirect(eventId ? `/admin/events/${eventId}` : "/admin/events");
}

export async function updateEventAction(
  eventId: number,
  _prev: ActionResult,
  formData: FormData,
): Promise<ActionResult> {
  let state: ActionResult;

  try {
    const response = await apiRequest(`/admin/events/${eventId}`, "POST", pruneEmptyFiles(formData));
    state = await toActionResult(response);
  } catch (error) {
    return toFailureState(error);
  }

  if (!state.ok) {
    return state;
  }

  redirect(`/admin/events/${eventId}`);
}

export async function updateEventStatusAction(eventId: number, status: string): Promise<ActionResult> {
  try {
    const response = await apiRequest(`/admin/events/${eventId}/status`, "POST", { status });

    return await toActionResult(response);
  } catch (error) {
    return toFailureState(error);
  }
}

export async function updateScoringAction(
  eventId: number,
  mode: "open" | "closed" | "auto",
): Promise<ActionResult> {
  try {
    const response = await apiRequest(`/admin/events/${eventId}/scoring`, "POST", { mode });

    return await toActionResult(response);
  } catch (error) {
    return toFailureState(error);
  }
}

export async function duplicateEventAction(eventId: number): Promise<ActionResult> {
  try {
    const response = await apiRequest(`/admin/events/${eventId}/duplicate`, "POST");
    return await toActionResult(response);
  } catch (error) {
    return toFailureState(error);
  }
}

export async function archiveEventAction(eventId: number): Promise<ActionResult> {
  return updateEventStatusAction(eventId, "archived");
}
