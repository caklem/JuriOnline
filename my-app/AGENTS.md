<!-- BEGIN:nextjs-agent-rules -->

## This is NOT the Next.js you know

This version has breaking changes — APIs, conventions, and file structure may all differ from your training data. Read the relevant guide in `node_modules/next/dist/docs/` (resolved from this file's directory; in monorepos the `next` package may not be visible from the repo root) before writing any code. Heed deprecation notices.

This block is written and re-added by `next dev` — verify at `node_modules/next/dist/server/lib/generate-agent-files.js`. Removing it from a diff only re-creates the uncommitted change; committing it with your work keeps the tree clean.

<!-- END:nextjs-agent-rules -->

## JURI.ON brand rules (read before editing UI)

Brand system: `BRAND_GUIDELINES.md` at the repo root, PRD in `resources/brand/`.

- Official spelling is `JURI.ON` — never `JuriOnline` in UI, metadata, or copy. Descriptor: `Juri Online`.
- Use `<BrandLockup href="/…" variant="light|dark" tile descriptor />` from `@/components/brand/lockup` for any header, sidebar, footer, or auth hero. Do not hand-draw the gavel/J mark.
- Brand layer: `src/components/arc/brand.css` (imported in `app/layout.tsx`), assets in `public/brand/`, favicon `src/app/favicon.ico`.
- Colours: navy `#0B1F46`, cobalt `#2F80FF`, cobalt-strong `#1E6BE6` (AA text), light blue `#E8F2FF`, slate `#667085`, border `#DCE4EF`, surface `#F7FAFF`. The `indigo-*` and `slate-950` utilities are mapped to this palette in `brand.css` — use tokens, not raw hex.
- Fonts: Montserrat (headings, `h1`–`h3`, wordmark), Inter (body/UI). Do not add a third family.
- Keep `data-accent="brand"` on `<html>`; `--accent` is the cobalt ramp.
- Public scroll motion: every section heading, card, list item, step, and footer block must be wrapped in `Reveal` (or an equivalent `whileInView`). Keep the bottom viewport margin at `-24px` so bottom-of-page blocks trigger; under `prefers-reduced-motion` use a fade-only reveal (no translate/blur).
