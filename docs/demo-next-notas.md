# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Landing page + registration form for the **LV Congreso Nacional de Pediatría 2027 – Edición Esmeralda** (Sociedad Panameña de Pediatría, 17–19 June 2027). Next.js 16 (App Router) / React 19 / Tailwind CSS 4 / framer-motion / react-three-fiber. All user-facing text is Spanish. Currently a **client demo** (placeholders for logos, photos, prices), not production.

The previous year's PHP registration platform is **not** in this repo; its notes were kept in [docs/sistema-registro-2026-php.md](docs/sistema-registro-2026-php.md) as a reference for the future admin panel.

## Commands

```bash
npm run dev          # dev server on :3000 (full mode, API route works)
npm run build        # normal server build
npm run lint
npx tsc --noEmit     # fastest correctness check; there is no test suite
```

[AGENTS.md](AGENTS.md) warns this Next version has breaking changes: read the relevant guide in `node_modules/next/dist/docs/` before using unfamiliar Next APIs. Example already hit: `export const runtime` in a route handler is rejected because `cacheComponents` is on.

## Two build modes (read before touching config or assets)

[next.config.ts](next.config.ts) switches on `STATIC_EXPORT=true`:

| | normal | `STATIC_EXPORT=true npx next build` → `out/` |
|---|---|---|
| `cacheComponents` / `partialPrefetching` | on | off (PPR is incompatible with `output: "export"`) |
| `basePath` | none | `/congreso` |
| `pageExtensions` | default | `tsx,jsx,js`, so `src/app/api/**/route.ts` is skipped (no server) |
| `NEXT_PUBLIC_DEMO_STATIC` | unset | `"true"`: `Registration.tsx` fakes the submit instead of calling the API |

Consequences:
- Files in `public/` referenced from plain `<img src>` or inline CSS **must** go through `asset()` from [src/lib/asset.ts](src/lib/asset.ts) so they get the `/congreso` prefix. `next/image`/`Link` do this themselves; raw tags and Tailwind `bg-[url(...)]` do not.
- `NEXT_PUBLIC_BASE_PATH`/`DEMO_STATIC` are set inside `next.config.ts` (`env`), **not** via shell. On Windows Git Bash, `VAR=/congreso cmd` is rewritten to `C:/Program Files/Git/congreso`, which silently broke every image once. After a static build, check `grep -oE 'src="[^"]*"' out/index.html` for the right prefix.
- Don't add anything to `src/app` with a `.ts` extension that must exist in the static build (route handlers are intentionally excluded that way).

## Deployment (demo)

Live at `https://it4nova.cloud/congreso/`, a static site on a shared **Plesk/AlmaLinux** VPS (`root@31.220.53.118`, key `~/.ssh/vps_innov4te`) that also hosts other clients' sites. No Node on PATH there, no process to manage.

1. `STATIC_EXPORT=true npx next build`
2. Replace the web folder with `out/`: empty `/var/www/vhosts/it4nova.cloud/httpdocs/congreso`, stream `tar -C out -cf - . | ssh ... "tar -xf - -C <dir>"` (use Git Bash, PowerShell corrupts binary pipes), then `chown -R it4nova:psacln`.
3. Verify the HTML's image/script URLs return 200 with `curl`; a 200 on the file alone doesn't prove the page references it correctly.

The source copy lives outside the webroot at `/var/www/vhosts/it4nova.cloud/congreso-src` (don't put source under `httpdocs`, it would be publicly served). Don't touch other vhosts or global nginx. The site root `index.html` is the user's ("en construcción") page; leave it alone.

## Architecture

- **Content is data**: [src/content/congreso.ts](src/content/congreso.ts) holds event name/dates/venue, menu anchors, `tarifas` (preventa/regular), payment methods, FAQ-adjacent copy, the Grand Tours URL, etc. Components read from it; change copy and prices there. Items still awaiting client input are tagged `PLACEHOLDER` (prices are `$000`, `grandToursUrl` is `#`, logos/photos are dashed boxes or gradient blocks).
- **Page assembly**: [src/app/page.tsx](src/app/page.tsx) just stacks section components in order (modeled on the Fexo theme: hero → about tabs → experience tabs → partners marquee → value block → steps → pricing → registration → international → awards → gallery → welcome → agenda → FAQ → contact). Menu anchors in `menu` (congreso.ts) must match section `id`s.
- **Layout primitives**: [src/components/Section.tsx](src/components/Section.tsx) exports `Section`/`SectionTitle`/`Reveal`, which encode the Fexo measurements (120px section padding, 20px uppercase eyebrow, 52px h2, 1320px container, 30px radius cards). Reuse them instead of re-deriving sizes.
- **Design tokens** in [src/app/globals.css](src/app/globals.css): palette taken from the official flyer: navy `esm-900`, emerald `esm-800/700`, light blues `esm-300/100/50`. The token named `gold` is actually a **mint green** accent (kept the name from an earlier gold palette). Fonts: Ubuntu (`font-display`) + DM Sans, loaded in `layout.tsx`.
- **3D**: [Gem3D.tsx](src/components/Gem3D.tsx) (react-three-fiber gem + particles) is loaded only on the client through `Gem3DLoader.tsx` (`dynamic`, `ssr: false`). It uses drei `<Environment preset="city">`, which fetches an HDR from a CDN at runtime. A previous 3D hero scene was rejected by the user and removed; check before adding more hero animation.
- **Registration** ([Registration.tsx](src/components/Registration.tsx) + [api/inscripcion/route.ts](src/app/api/inscripcion/route.ts)): the API validates against `tarifas`/`formasDePago` server-side, then appends a JSON line to `data/inscripciones.jsonl` (gitignored). `DEMO_MODE=true` skips the write (for hosts with no writable disk). There is no payment processing and no admin panel yet; both are planned for phase 2 (counting sold registrations by type, Grand Tours handles international bookings).
- **Time-dependent values** (which tariff applies: `periodoVigente()`, preventa ends 2027-03-15 23:59:59 Panama time) must be computed in the browser (`useEffect`). Calling `new Date()` during render fails the prerender under `cacheComponents` and would otherwise freeze the build date into the HTML.
- Site is `noindex` (set in `layout.tsx` metadata) while it is a demo; remove when going live.
