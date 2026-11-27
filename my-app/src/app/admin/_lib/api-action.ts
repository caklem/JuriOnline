import { cookies } from "next/headers";
import { API_BASE } from "@/lib/api";

export type ActionResult = {
  ok?: boolean;
  message?: string;
  error?: string;
  errors?: Record<string, string>;
  payload?: {
    message?: string;
    event?: { id: number };
    [key: string]: unknown;
  } | null;
};

/**
 * Panggilan API dari Server Action. Body boleh FormData (multipart) atau objek JSON.
 */
export async function apiRequest(
  path: string,
  method: string,
  body?: BodyInit | Record<string, unknown>,
): Promise<Response> {
  const token = (await cookies()).get("jl_token")?.value;
  const isJson = body !== undefined && !(body instanceof FormData);

  return fetch(`${API_BASE}${path}`, {
    method,
    headers: {
      Accept: "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...(isJson ? { "Content-Type": "application/json" } : {}),
    },
    body: body === undefined ? undefined : isJson ? JSON.stringify(body) : (body as BodyInit),
    cache: "no-store",
  });
}

/**
 * Terjemahkan respons API menjadi state form: pesan sukses, atau pesan galat per field.
 */
export async function toActionResult(response: Response): Promise<ActionResult> {
  const body = (await response.json().catch(() => null)) as
    | { message?: string; errors?: Record<string, string[]> }
    | null;

  if (!response.ok) {
    const errors: Record<string, string> = {};

    if (body?.errors) {
      for (const [field, messages] of Object.entries(body.errors)) {
        const first = messages?.[0];

        if (first) {
          errors[field] = first;
        }
      }
    }

    const firstError = Object.values(errors)[0];

    return {
      error:
        response.status === 403
          ? "Akses ditolak."
          : response.status === 404
            ? "Data tidak ditemukan."
            : firstError ?? body?.message ?? "Terjadi kesalahan. Coba lagi.",
      errors,
    };
  }

  return { ok: true, message: body?.message ?? "Berhasil disimpan.", payload: body };
}

/** Ubah galat tak terduga (jaringan, sesi kedaluwarsa) menjadi state form. */
export function toFailureState(error: unknown): ActionResult {
  if (error instanceof Error && error.message) {
    return { error: error.message };
  }

  return { error: "Tidak dapat menghubungi server. Coba lagi." };
}
