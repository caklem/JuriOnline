const MONTHS = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

/**
 * Format tanggal mengikuti Carbon `d M Y` milik Blade lama, dalam UTC
 * (timezone aplikasi Laravel) agar hasil server dan klien selalu sama.
 */
export function formatDate(value: string | null): string {
  if (!value) return "-";

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) return "-";

  const day = String(date.getUTCDate()).padStart(2, "0");

  return `${day} ${MONTHS[date.getUTCMonth()]} ${date.getUTCFullYear()}`;
}

/**
 * Format tanggal + jam mengikuti Carbon `d M Y H:i` milik Blade lama.
 */
export function formatDateTime(value: string | null): string {
  if (!value) return "-";

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) return "-";

  const hours = String(date.getUTCHours()).padStart(2, "0");
  const minutes = String(date.getUTCMinutes()).padStart(2, "0");

  return `${formatDate(value)} ${hours}:${minutes}`;
}
