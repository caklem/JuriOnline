"use client";

import { useTransition } from "react";
import { useRouter } from "next/navigation";
import { DropdownMenu } from "@/components/arc/dropdown-menu/dropdown-menu";
import { logoutAction } from "@/app/login/actions";

export function UserMenu({ name, role }: { name: string; role: string }) {
  const [pending, startTransition] = useTransition();
  const router = useRouter();

  return (
    <DropdownMenu
      label={name}
      items={[
        { label: role, disabled: true },
        {
          label: pending ? "Keluar…" : "Keluar",
          destructive: true,
          separatorBefore: true,
          onSelect: () => {
            startTransition(async () => {
              await logoutAction();
              router.replace("/login");
              router.refresh();
            });
          },
        },
      ]}
    />
  );
}
