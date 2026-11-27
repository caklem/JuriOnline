import { cookies } from "next/headers";
import { cache } from "react";
import { API_BASE } from "./api";

export type OptionalViewer = {
  name: string;
  email: string;
  role: string;
  roleLabel: string;
};

type MePayload = {
  user?: {
    name?: string;
    email?: string;
    role?: string;
    role_label?: string;
  };
};

/**
 * Sesi opsional untuk halaman publik: beda dengan apiFetch(), token yang
 * kedaluwarsa tidak melempar redirect. Halaman tetap tampil sebagai guest.
 */
export const getOptionalViewer = cache(async (): Promise<OptionalViewer | null> => {
  const token = (await cookies()).get("jl_token")?.value;

  if (!token) {
    return null;
  }

  try {
    const response = await fetch(`${API_BASE}/me`, {
      headers: {
        Accept: "application/json",
        Authorization: `Bearer ${token}`,
      },
      cache: "no-store",
    });

    if (!response.ok) {
      return null;
    }

    const data = (await response.json()) as MePayload;
    const user = data.user;

    if (!user?.name) {
      return null;
    }

    return {
      name: user.name,
      email: user.email ?? "",
      role: user.role ?? "",
      roleLabel: user.role_label ?? user.role ?? "Pengguna",
    };
  } catch {
    return null;
  }
});
