import { Badge } from "@/components/kit/ui";

export function RegistrationStatusBadge({
  status,
  variant = "list",
}: {
  status: string;
  variant?: "list" | "detail";
}) {
  if (status === "verified") {
    return <Badge tone="success">Terverifikasi</Badge>;
  }

  if (status === "pending") {
    return <Badge tone="warning">{variant === "detail" ? "Menunggu" : "Pending"}</Badge>;
  }

  if (status === "rejected") {
    return <Badge tone="danger">Ditolak</Badge>;
  }

  return <Badge tone="info">{variant === "detail" ? status : `${status.charAt(0).toUpperCase()}${status.slice(1)}`}</Badge>;
}

export function PaymentStatusBadge({
  payment,
  variant = "list",
}: {
  payment: { status: string } | null;
  variant?: "list" | "detail";
}) {
  if (!payment) {
    return <Badge tone="danger">Belum Upload</Badge>;
  }

  if (payment.status === "verified") {
    return <Badge tone="success">Terverifikasi</Badge>;
  }

  if (payment.status === "pending") {
    return <Badge tone="warning">{variant === "detail" ? "Menunggu Verifikasi" : "Menunggu"}</Badge>;
  }

  return <Badge tone="danger">Ditolak</Badge>;
}
