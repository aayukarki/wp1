# MWS theme

WordPress theme for GNT Landscaping — a landscape and hardscape contractor in
Cranberry Township, PA.

## Build

```bash
npm install
npm run build     # one-off
npm run dev       # watch
```

Tailwind + gulp compile `src/` into `dist/`.

**`dist/` is committed on purpose.** Production pulls this repo and has no node
toolchain, so the compiled CSS and JS must arrive with the code. Run
`npm run build` before committing anything under `src/` — a stale `dist/` ships
markup and styles that disagree, which is harder to spot than no styles at all.

## Layout

| Path | Contents |
|---|---|
| `inc/` | Config, helpers, post types, ACF fields, forms |
| `template-parts/` | Shared sections (banner, split, cards, reviews…) |
| `template-*.php` | Page templates, selected per page in the editor |
| `assets/` · `dist/` | Source images and icons · build output |
| `docs/` | SEO copy and deployment artifacts |

Content lives in ACF fields and `inc/config.php`, not in the templates.

## Deploy

```bash
cd wp-content/themes/mws && git pull
```

Pages, services and SEO meta are database content and do not travel through
git — see `docs/deploy/` for the import files and instructions.
