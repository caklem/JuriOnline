"use server";

import { apiRequest, toActionResult, toFailureState, type ActionResult } from "@/app/admin/_lib/api-action";

export async function verifyPaymentAction(
  paymentId: number,
  _prev: ActionResult,
  formData: FormData,
): Promise<ActionResult> {
  const status = String(formData.get("status") ?? "");
  const notes = String(formData.get("verification_notes") ?? "").trim();

  try {
    const response = await apiRequest(`/admin/payments/${paymentId}/verify`, "POST", {
      status,
      verification_notes: notes === "" ? null : notes,
    });

    return await toActionResult(response);
  } catch (error) {
    return toFailureState(error);
  }
}
