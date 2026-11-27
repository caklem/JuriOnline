// Sama dengan format('M') Carbon pada Blade lama: Jan .. Dec.
const MONTHS_SHORT = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

function toDate(value: string | null | undefined): Date | null {
  if (!value) return null;

  const date = new Date(value.length === 10 ? `${value}T00:00:00` : value);

  return Number.isNaN(date.getTime()) ? null : date;
}

/** 25 Nov 2026 */
export function formatDate(value: string | null | undefined): string {
  const date = toDate(value);

  if (!date) return "-";

  return `${String(date.getDate()).padStart(2, "0")} ${MONTHS_SHORT[date.getMonth()]} ${date.getFullYear()}`;
}

/** 25 Nov */
export function formatShortDate(value: string | null | undefined): string {
  const date = toDate(value);

  if (!date) return "-";

  return `${String(date.getDate()).padStart(2, "0")} ${MONTHS_SHORT[date.getMonth()]}`;
}

/** 25 Nov 2026 14:30 */
export function formatDateTime(value: string | null | undefined): string {
  const date = toDate(value);

  if (!date) return "-";

  const hours = String(date.getHours()).padStart(2, "0");
  const minutes = String(date.getMinutes()).padStart(2, "0");

  return `${formatDate(value)} ${hours}:${minutes}`;
}

/** Rp 150.000 */
export function formatRupiah(value: number | string | null | undefined): string {
  const amount = Number(value ?? 0);
  const formatted = new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(
    Number.isFinite(amount) ? amount : 0,
  );

  return `Rp ${formatted}`;
}

/** 85,50 */
export function formatScore(value: number | string | null | undefined): string {
  const score = Number(value ?? 0);

  return new Intl.NumberFormat("id-ID", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number.isFinite(score) ? score : 0);
}
