"use server";

import { apiRequest, toActionResult, toFailureState, type ActionResult } from "@/app/admin/_lib/api-action";

export async function updateRegistrationStatusAction(
  registrationId: number,
  status: string,
): Promise<ActionResult> {
  try {
    const response = await apiRequest(`/admin/registrations/${registrationId}/status`, "PATCH", { status });

    return await toActionResult(response);
  } catch (error) {
    return toFailureState(error);
  }
}
