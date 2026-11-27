"use server";

import { revalidatePath } from "next/cache";
import { apiRequest, toActionResult, toFailureState, type ActionResult } from "../../../_lib/api-action";

export async function reviewEvaluationAction(
  scoreId: number,
  eventId: number,
  status: "reviewed" | "needs_revision",
  notes: string,
): Promise<ActionResult> {
  try {
    const response = await apiRequest(`/admin/scores/${scoreId}/review`, "POST", { status, notes });
    const result = await toActionResult(response);

    if (result.ok) {
      revalidatePath(`/admin/events/${eventId}/evaluations`);
    }

    return result;
  } catch (error) {
    return toFailureState(error);
  }
}

export async function publishEvaluationAction(scoreId: number, eventId: number): Promise<ActionResult> {
  try {
    const response = await apiRequest(`/admin/scores/${scoreId}/publish`, "POST");
    const result = await toActionResult(response);

    if (result.ok) {
      revalidatePath(`/admin/events/${eventId}/evaluations`);
    }

    return result;
  } catch (error) {
    return toFailureState(error);
  }
}

export async function publishAllEvaluationsAction(eventId: number): Promise<ActionResult> {
  try {
    const response = await apiRequest(`/admin/events/${eventId}/evaluations/publish`, "POST");
    const result = await toActionResult(response);

    if (result.ok) {
      revalidatePath(`/admin/events/${eventId}/evaluations`);
    }

    return result;
  } catch (error) {
    return toFailureState(error);
  }
}
