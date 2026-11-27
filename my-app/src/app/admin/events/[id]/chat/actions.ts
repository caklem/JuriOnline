"use server";

import { revalidatePath } from "next/cache";
import { apiRequest, toActionResult, toFailureState, type ActionResult } from "../../../_lib/api-action";

export async function updateChatSettingsAction(eventId: number, formData: FormData): Promise<ActionResult> {
  try {
    const response = await apiRequest(`/admin/events/${eventId}/chat-settings`, "POST", {
      enabled: formData.get("enabled") === "1",
      participants_can_send: formData.get("participants_can_send") === "1",
      juries_can_send: formData.get("juries_can_send") === "1",
      whatsapp_enabled: formData.get("whatsapp_enabled") === "1",
      whatsapp_group_url: String(formData.get("whatsapp_group_url") ?? "").trim() || null,
      announcement: String(formData.get("announcement") ?? "").trim() || null,
    });
    const result = await toActionResult(response);

    if (result.ok) revalidatePath(`/admin/events/${eventId}/chat`);

    return result;
  } catch (error) {
    return toFailureState(error);
  }
}

export async function sendChatMessageAction(eventId: number, body: string): Promise<ActionResult> {
  try {
    const response = await apiRequest(`/admin/events/${eventId}/chat/messages`, "POST", { body });
    const result = await toActionResult(response);

    if (result.ok) revalidatePath(`/admin/events/${eventId}/chat`);

    return result;
  } catch (error) {
    return toFailureState(error);
  }
}

export async function setMessageHiddenAction(messageId: number, eventId: number, hidden: boolean): Promise<ActionResult> {
  try {
    const response = await apiRequest(`/admin/messages/${messageId}/${hidden ? "hide" : "unhide"}`, "POST");
    const result = await toActionResult(response);

    if (result.ok) revalidatePath(`/admin/events/${eventId}/chat`);

    return result;
  } catch (error) {
    return toFailureState(error);
  }
}
