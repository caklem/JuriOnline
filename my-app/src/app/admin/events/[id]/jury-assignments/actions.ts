"use server";

import { apiRequest, toActionResult, toFailureState, type ActionResult } from "@/app/admin/_lib/api-action";

export async function assignJuryAction(
  eventId: number,
  _prev: ActionResult,
  formData: FormData,
): Promise<ActionResult> {
  const juryIds = formData
    .getAll("jury_ids")
    .map((value) => Number(value))
    .filter((value) => Number.isInteger(value) && value > 0);

  const templateId = Number(formData.get("jury_form_template_id"));
  const juryRole = String(formData.get("jury_role") ?? "").trim();

  if (juryIds.length === 0) {
    return { error: "Silakan pilih minimal 1 juri untuk di-assign!" };
  }

  try {
    const response = await apiRequest(`/admin/events/${eventId}/jury-assignments`, "POST", {
      jury_ids: juryIds,
      jury_form_template_id: templateId,
      jury_role: juryRole === "" ? null : juryRole,
    });

    return await toActionResult(response);
  } catch (error) {
    return toFailureState(error);
  }
}

export async function deleteAssignmentAction(eventId: number, assignmentId: number): Promise<ActionResult> {
  try {
    const response = await apiRequest(`/admin/events/${eventId}/jury-assignments/${assignmentId}`, "DELETE");

    return await toActionResult(response);
  } catch (error) {
    return toFailureState(error);
  }
}

export type Credentials = { name: string; email: string; code: string; custom?: boolean };

export async function createJuryAccountAction(
  eventId: number,
  _prev: ActionResult & { credentials?: Credentials },
  formData: FormData,
): Promise<ActionResult & { credentials?: Credentials }> {
  try {
    const response = await apiRequest(`/admin/events/${eventId}/jury-accounts`, "POST", {
      name: String(formData.get("name") ?? "").trim(),
      email: String(formData.get("email") ?? "").trim(),
      jury_form_template_id: Number(formData.get("jury_form_template_id")),
      jury_role: String(formData.get("jury_role") ?? "").trim() || null,
      password: String(formData.get("password") ?? "").trim() || null,
    });

    const body = (await response.json().catch(() => null)) as
      | { message?: string; errors?: Record<string, string[]>; credentials?: Credentials }
      | null;

    if (!response.ok) {
      const first = body?.errors ? Object.values(body.errors)[0]?.[0] : undefined;
      return { error: first ?? body?.message ?? "Gagal membuat akun juri." };
    }

    return { message: body?.message ?? "Akun juri berhasil dibuat.", credentials: body?.credentials };
  } catch (error) {
    return toFailureState(error);
  }
}

export async function resetJuryPasswordAction(
  eventId: number,
  assignmentId: number,
  _prev: ActionResult & { credentials?: Credentials },
  formData: FormData,
): Promise<ActionResult & { credentials?: Credentials }> {
  try {
    const response = await apiRequest(
      `/admin/events/${eventId}/jury-assignments/${assignmentId}/reset-password`,
      "POST",
      { password: String(formData.get("password") ?? "").trim() || null },
    );

    const body = (await response.json().catch(() => null)) as
      | { message?: string; errors?: Record<string, string[]>; credentials?: Credentials }
      | null;

    if (!response.ok) {
      const first = body?.errors ? Object.values(body.errors)[0]?.[0] : undefined;
      return { error: first ?? body?.message ?? "Gagal mereset password juri." };
    }

    return { message: body?.message ?? "Password juri berhasil diperbarui.", credentials: body?.credentials };
  } catch (error) {
    return toFailureState(error);
  }
}
