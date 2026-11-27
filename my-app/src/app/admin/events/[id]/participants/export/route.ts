import { cookies } from "next/headers";
import { NextResponse } from "next/server";
import { API_BASE } from "@/lib/api";

export async function GET(
  _request: Request,
  { params }: { params: Promise<{ id: string }> },
) {
  const { id } = await params;
  const token = (await cookies()).get("jl_token")?.value;
  const response = await fetch(`${API_BASE}/admin/events/${id}/participants/export`, {
    headers: {
      Accept: "text/csv",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    cache: "no-store",
  });

  if (!response.ok) {
    return new NextResponse("Ekspor ditolak.", { status: response.status });
  }

  return new NextResponse(response.body, {
    status: 200,
    headers: {
      "Content-Type": "text/csv; charset=UTF-8",
      "Content-Disposition": response.headers.get("Content-Disposition") ?? `attachment; filename="peserta-${id}.csv"`,
      "Cache-Control": "private, no-store",
    },
  });
}
