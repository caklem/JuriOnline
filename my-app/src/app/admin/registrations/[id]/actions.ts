"use server";

import { apiRequest, toActionResult, toFailureState, type ActionResult } from "../../_lib/api-action";

export async function requestScoreRevisionAction(
  scoreId: number,
  _prev: ActionResult,
  formData: FormData,
): Promise<ActionResult> {
  try {
    const response = await apiRequest(`/admin/scores/${scoreId}/revision-request`, "POST", {
      reason: String(formData.get("reason") ?? "").trim(),
    });
    return await toActionResult(response);
  } catch (error) {
    return toFailureState(error);
  }
}
