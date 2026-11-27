"use server";

import { revalidatePath } from "next/cache";
import { cookies } from "next/headers";
import { redirect } from "next/navigation";
import { API_BASE } from "@/lib/api";

export type UploadProofState = { error?: string; fieldError?: string };

type ValidationBody = {
  message?: string;
  errors?: Record<string, string[]>;
};

export async function uploadProof(
  registrationId: number,
  _previous: UploadProofState,
  formData: FormData,
): Promise<UploadProofState> {
  const token = (await cookies()).get("jl_token")?.value;

  if (!token) {
    redirect("/login");
  }

  const file = formData.get("proof_file");

  if (!(file instanceof File) || file.size === 0) {
    return { fieldError: "Bukti transfer wajib diunggah." };
  }

  const body = new FormData();
  body.append("proof_file", file, file.name);

  let response: Response;

  try {
    response = await fetch(`${API_BASE}/registrations/${registrationId}/payment`, {
      method: "POST",
      headers: {
        Accept: "application/json",
        Authorization: `Bearer ${token}`,
      },
      body,
      cache: "no-store",
    });
  } catch {
    return { error: "Tidak dapat menghubungi server. Coba lagi sebentar." };
  }

  if (response.status === 401) {
    redirect("/login");
  }

  if (response.ok) {
    revalidatePath("/my-registrations");
    revalidatePath(`/registrations/${registrationId}`);

    redirect(`/registrations/${registrationId}?uploaded=1`);
  }

  if (response.status === 403) {
    return { error: "Akses ditolak." };
  }

  if (response.status === 429) {
    return { error: "Terlalu banyak percobaan. Coba lagi sebentar." };
  }

  const data: ValidationBody | null = await response.json().catch(() => null);

  if (response.status === 409) {
    return { error: data?.message ?? "Pembayaran sudah diupload sebelumnya." };
  }

  if (response.status === 422) {
    const raw = data?.errors?.proof_file?.[0] ?? data?.message ?? "Bukti transfer tidak valid.";

    if (/required/i.test(raw)) {
      return { fieldError: "Bukti transfer wajib diunggah." };
    }

    if (/greater than|max/i.test(raw)) {
      return { fieldError: "Ukuran file maksimal 2MB." };
    }

    if (/type|mimes/i.test(raw)) {
      return { fieldError: "Format file harus JPG, PNG, atau PDF." };
    }

    if (/upload/i.test(raw)) {
      return {
        fieldError: "Gagal mengunggah bukti transfer. Ukuran file kemungkinan melebihi batas server.",
      };
    }

    return { fieldError: raw };
  }

  if (response.status >= 500) {
    return { error: "Gagal mengunggah bukti pembayaran. Coba lagi." };
  }

  return { error: data?.message ?? "Gagal mengunggah bukti pembayaran. Coba lagi." };
}
