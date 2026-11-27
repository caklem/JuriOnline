"use server";

import { apiRequest, toActionResult, toFailureState, type ActionResult } from "../_lib/api-action";

function pruneEmptyFiles(formData: FormData): FormData {
  for (const [key, value] of [...formData.entries()]) {
    if (value instanceof File && (value.size === 0 || value.name === "")) {
      formData.delete(key);
    }
  }

  return formData;
}

export async function saveOrganizerProfileAction(
  _prev: ActionResult,
  formData: FormData,
): Promise<ActionResult> {
  try {
    const response = await apiRequest("/admin/organizer-profile", "POST", pruneEmptyFiles(formData));
    return await toActionResult(response);
  } catch (error) {
    return toFailureState(error);
  }
}
