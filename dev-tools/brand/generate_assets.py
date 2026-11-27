"""Generate JURI.ON brand raster assets from the supplied logo.

Usage (repo root):
    python dev-tools/brand/generate_assets.py

Requires: pillow, numpy.
Outputs:
    public/images/brand/jurion-mark.png            transparent colour mark (1024)
    public/images/brand/jurion-mark-white.png      white monochrome mark (1024)
    public/images/brand/jurion-icon-{16..512}.png  rounded app icons
    public/images/brand/favicon.ico                16/32/48/64
    public/favicon.ico, public/favicon-*.png, public/apple-touch-icon.png
    my-app/public/brand/*                          copy for the Next.js app (if present)
"""

from __future__ import annotations

import shutil
from pathlib import Path

import numpy as np
from PIL import Image, ImageDraw, ImageFilter

ROOT = Path(__file__).resolve().parents[2]
SOURCE = ROOT / "dev-tools/brand/source/jurion-mark-source.png"
OUT = ROOT / "public/images/brand"
NEXT_OUT = ROOT / "my-app/public/brand"


def load_rgba() -> tuple[Image.Image, Image.Image]:
    """Return (colour mark, white silhouette), both 1024x1024 with alpha."""
    rgb = np.asarray(Image.open(SOURCE).convert("RGB")).astype(np.float64)
    bg = rgb[:8, :8].reshape(-1, 3).mean(axis=0)

    distance = np.sqrt(((rgb - bg) ** 2).sum(axis=2))
    ratio = np.clip(rgb / bg, 0, 1)
    alpha = np.clip(1.0 - ratio.min(axis=2), 0, 1)
    alpha = np.where(distance > 80, 1.0, np.clip(np.maximum(alpha, np.clip((distance - 10) / 55, 0, 1)), 0, 1))
    alpha[alpha < 0.02] = 0.0

    unblended = np.clip((rgb - (1.0 - alpha[..., None]) * bg) / np.maximum(alpha, 1e-4)[..., None], 0, 255)
    colour = Image.fromarray(np.dstack([unblended, alpha * 255]).astype(np.uint8), "RGBA")

    # tight crop, then pad to a square with 6% breathing room per side
    mask = alpha > 0.05
    ys, xs = np.where(mask)
    colour = colour.crop((xs.min(), ys.min(), xs.max() + 1, ys.max() + 1))
    w, h = colour.size
    side = int(max(w, h) * 1.12)
    canvas = Image.new("RGBA", (side, side), (0, 0, 0, 0))
    canvas.paste(colour, ((side - w) // 2, (side - h) // 2), colour)
    colour = canvas.resize((1024, 1024), Image.LANCZOS)

    # monochrome: hard silhouette from the colour alpha, softened 0.6px
    hard = (np.asarray(colour.getchannel("A")).astype(np.float32) / 255.0 > 0.55).astype(np.float32)
    soft = Image.fromarray((hard * 255).astype(np.uint8), "L").filter(ImageFilter.GaussianBlur(0.6))
    white = Image.new("RGBA", colour.size, (255, 255, 255, 0))
    white.putalpha(soft)

    return colour, white


def make_icon(mark: Image.Image, size: int, radius_ratio: float = 0.22, pad_ratio: float = 0.08) -> Image.Image:
    scale = 4
    side = size * scale
    tile = Image.new("RGBA", (side, side), (0, 0, 0, 0))
    ImageDraw.Draw(tile).rounded_rectangle([0, 0, side - 1, side - 1], radius=int(side * radius_ratio), fill=(255, 255, 255, 255))
    inner = int(side * (1 - pad_ratio * 2))
    tile.alpha_composite(mark.resize((inner, inner), Image.LANCZOS), ((side - inner) // 2, (side - inner) // 2))
    return tile.resize((size, size), Image.LANCZOS)


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)
    colour, white = load_rgba()
    colour.resize((512, 512), Image.LANCZOS).save(OUT / "jurion-mark.png", optimize=True)
    white.resize((512, 512), Image.LANCZOS).save(OUT / "jurion-mark-white.png", optimize=True)

    for size in (16, 32, 48, 64, 180, 192, 512):
        make_icon(colour, size).save(OUT / f"jurion-icon-{size}.png", optimize=True)

    make_icon(colour, 32).save(OUT / "favicon-32x32.png", optimize=True)
    make_icon(colour, 16).save(OUT / "favicon-16x16.png", optimize=True)
    make_icon(colour, 180).save(OUT / "apple-touch-icon.png", optimize=True)
    make_icon(colour, 256).save(OUT / "favicon.ico", sizes=[(16, 16), (32, 32), (48, 48), (64, 64)])

    for name in ("favicon.ico", "favicon-32x32.png", "favicon-16x16.png", "apple-touch-icon.png"):
        shutil.copyfile(OUT / name, ROOT / "public" / name)

    if NEXT_OUT.is_dir():
        for name in ("jurion-mark.png", "jurion-mark-white.png", "pattern.svg", "favicon.ico",
                     "favicon-16x16.png", "favicon-32x32.png", "apple-touch-icon.png",
                     "jurion-icon-192.png", "jurion-icon-512.png"):
            source = OUT / name
            if source.exists():
                shutil.copyfile(source, NEXT_OUT / name)
        shutil.copyfile(OUT / "favicon.ico", ROOT / "my-app/src/app/favicon.ico")

    print(f"Generated brand assets in {OUT}")


if __name__ == "__main__":
    main()
