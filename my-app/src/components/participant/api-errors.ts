/**
 * apiFetch melempar error biasa untuk respons non-ok, tetapi redirect() Next
 * juga berupa throw. Error redirect harus diteruskan agar halaman tetap
 * mengarahkan ke /login saat sesi berakhir.
 */
export function isNextRedirect(error: unknown): boolean {
  if (typeof error !== "object" || error === null || !("digest" in error)) {
    return false;
  }

  const { digest } = error;

  return typeof digest === "string" && digest.startsWith("NEXT_REDIRECT");
}

export function participantErrorMessage(error: unknown, fallback: string): string {
  const message = error instanceof Error ? error.message : "";

  if (!message) {
    return fallback;
  }

  if (message === "Unauthorized action.") {
    return "Akses ditolak.";
  }

  if (/no query results|not found/i.test(message)) {
    return "Pendaftaran tidak ditemukan.";
  }

  if (/^Permintaan gagal|^Gagal memuat/.test(message)) {
    return fallback;
  }

  return message;
}
