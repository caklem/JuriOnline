import { cookies } from "next/headers";
import { API_BASE } from "@/lib/api";

/**
 * Proksi bukti pembayaran dari API Laravel (disk privat).
 * Token sesi tetap di server; browser hanya menerima berkasnya.
 */
export async function GET(
  _request: Request,
  context: { params: Promise<{ id: string }> },
): Promise<Response> {
  const { id } = await context.params;
  const token = (await cookies()).get("jl_token")?.value;

  if (!token) {
    return new Response("Silakan masuk terlebih dahulu.", { status: 401 });
  }

  const upstream = await fetch(`${API_BASE}/admin/payments/${encodeURIComponent(id)}/proof`, {
    headers: {
      Accept: "*/*",
      Authorization: `Bearer ${token}`,
    },
    cache: "no-store",
  });

  if (!upstream.ok || !upstream.body) {
    const status = upstream.status === 403 ? 403 : upstream.status === 404 ? 404 : 502;
    const message =
      status === 403
        ? "Akses ditolak."
        : status === 404
          ? "Bukti pembayaran tidak ditemukan."
          : "Gagal memuat bukti pembayaran.";

    return new Response(message, { status, headers: { "Content-Type": "text/plain; charset=utf-8" } });
  }

  return new Response(upstream.body, {
    status: 200,
    headers: {
      "Content-Type": upstream.headers.get("content-type") ?? "application/octet-stream",
      "Content-Disposition": upstream.headers.get("content-disposition") ?? "inline",
      "Cache-Control": "private, no-store",
    },
  });
}
