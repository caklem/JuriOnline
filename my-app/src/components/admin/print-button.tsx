"use client";

import { Button } from "@/components/arc/button/button";

export function PrintButton({ label = "Cetak" }: { label?: string }) {
  return (
    <Button type="button" variant="primary" onClick={() => window.print()}>
      {label}
    </Button>
  );
}
