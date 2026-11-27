const FALLBACK = "/dashboard";
const AUTH_PATHS = ["/login", "/register", "/forgot-password", "/reset-password"];

/**
 * Batasi `?next=` ke path internal agar tidak menjadi open redirect.
 * Nilai dari searchParams bisa berupa array (query ganda).
 */
export function safeNextPath(value: string | string[] | undefined | null, fallback = FALLBACK): string {
  const candidate = Array.isArray(value) ? value[0] : value;

  if (
    !candidate ||
    candidate.length > 2048 ||
    !candidate.startsWith("/") ||
    candidate.startsWith("//") ||
    candidate.includes("\\")
  ) {
    return fallback;
  }

  const pathOnly = candidate.split(/[?#]/, 1)[0];

  if (AUTH_PATHS.some((path) => pathOnly === path)) {
    return fallback;
  }

  return candidate;
}
