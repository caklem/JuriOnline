import Link from "next/link";

type BrandLockupProps = {
  href?: string;
  variant?: "light" | "dark";
  tile?: boolean;
  descriptor?: boolean;
};

export function BrandLockup({
  href = "/dashboard",
  variant = "light",
  tile = false,
  descriptor = false,
}: BrandLockupProps) {
  return (
    <Link
      href={href}
      className={[
        "jurion-lockup",
        variant === "dark" ? "jurion-lockup--dark" : "",
        tile ? "jurion-lockup--tile" : "",
      ]
        .filter(Boolean)
        .join(" ")}
    >
      {/* eslint-disable-next-line @next/next/no-img-element */}
      <img
        src="/brand/jurion-mark.png"
        alt=""
        width={512}
        height={512}
        className="jurion-lockup__mark"
      />
      <span className="jurion-lockup__text">
        <span className="jurion-wordmark">
          JURI<span className="jurion-dot">.</span>ON
        </span>
        {descriptor ? <span className="jurion-descriptor">Juri Online</span> : null}
      </span>
    </Link>
  );
}
