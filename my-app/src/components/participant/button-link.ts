export function buttonLinkClass(
  variant: "primary" | "secondary" = "secondary",
  size: "sm" | "md" = "md",
): string {
  return [
    "inline-flex items-center justify-center gap-2 rounded-lg border font-medium transition",
    variant === "primary"
      ? "border-[var(--foreground)] bg-[var(--foreground)] text-[var(--background)] hover:opacity-90"
      : "border-[var(--border)] bg-[var(--background)] text-[var(--foreground)] hover:bg-[var(--surface-muted)]",
    size === "sm" ? "px-3 py-1.5 text-xs" : "px-4 py-2 text-sm",
  ].join(" ");
}
