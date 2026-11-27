const dateFormatter = new Intl.DateTimeFormat("id-ID", {
  day: "2-digit",
  month: "short",
  year: "numeric",
});

const priceFormatter = new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 });

/** Format tanggal ISO (YYYY-MM-DD) menjadi "01 Des 2026". */
export function formatDate(value: string | null | undefined): string {
  if (!value) {
    return "-";
  }

  const date = new Date(`${value}T00:00:00`);

  return Number.isNaN(date.getTime()) ? value : dateFormatter.format(date);
}

/** Harga event dalam rupiah; harga 0 ditampilkan sebagai "Gratis". */
export function formatPrice(price: number): string {
  return price > 0 ? `Rp ${priceFormatter.format(price)}` : "Gratis";
}

/** Potong deskripsi panjang seperti Str::limit pada Blade. */
export function limit(text: string | null | undefined, length = 120): string {
  const value = (text ?? "").trim();

  return value.length <= length ? value : `${value.slice(0, length).trimEnd()}...`;
}
