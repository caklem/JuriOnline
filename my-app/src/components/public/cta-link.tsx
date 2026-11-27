import Link from "next/link";
import type { ReactNode } from "react";
import styles from "@/components/arc/button/button.module.css";

type CtaVariant = "primary" | "secondary" | "ghost";
type CtaSize = "sm" | "md" | "lg";

/**
 * Tautan navigasi dengan tampilan Arc Button.
 * Arc `Button` merender <button>, sehingga tautan memakai stylesheet Arc yang sama
 * agar semantik <a> (buka tab baru, prefetch) tetap utuh.
 */
export function CtaLink({
  href,
  children,
  variant = "primary",
  size = "md",
  className = "",
}: {
  href: string;
  children: ReactNode;
  variant?: CtaVariant;
  size?: CtaSize;
  className?: string;
}) {
  return (
    <Link
      href={href}
      className={[styles.button, styles[variant], styles[size], className].filter(Boolean).join(" ")}
    >
      {children}
    </Link>
  );
}
