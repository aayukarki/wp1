# MWS theme

The GNT Landscaping landing page, converted from the standalone `ai-testing`
PHP page into a WordPress theme.

Nothing is loaded from a CDN. Tailwind is compiled, Swiper and AOS are bundled
out of `node_modules`, and both font families are self-hosted.

---

## Build

```bash
cd wp-content/themes/mws
npm install
npm run build        # one-off production build (minified, no sourcemaps)
npm run dev          # build once, then watch src/ and the PHP templates
```

`npm run dev` watches the templates as well as `src/`, because Tailwind only
emits the utility classes it can actually find in the markup — a class used in a
`.php` file for the first time needs a CSS rebuild to appear.

### Outputs

Everything under `dist/` is generated. Do not edit it by hand.

| File | Built from |
| --- | --- |
| `dist/css/vendor.min.css` | `swiper-bundle.min.css` + `aos.css` |
| `dist/css/main.min.css` | `src/css/main.css` → Tailwind → autoprefixer → cssnano |
| `dist/js/vendor.min.js` | `swiper-bundle.min.js` + `aos.js` |
| `dist/js/main.min.js` | `src/js/main.js` → terser |
| `dist/fonts/*` | `@fontsource/anton-sc`, `@fontsource/open-sans` |

Vendor is kept separate from application code so editing our own CSS or JS does
not invalidate the cached vendor bundle. `main` is enqueued with `vendor` as a
dependency in both cases, which is what guarantees `Swiper` and `AOS` are
defined as globals before `main.js` looks for them.

Cache busting is `filemtime`-based, so a rebuilt file busts its own URL without
anyone bumping a version number.

---

## Where things live

| Path | What |
| --- | --- |
| `inc/config.php` | Home page copy, brand details, opening hours, socials. |
| `inc/helpers.php` | Rendering helpers. All prefixed `mws_`. |
| `inc/context.php` | Derived values + navigation, shared by the templates. |
| `inc/post-types.php` | Services and Projects post types, taxonomy, project card. |
| `inc/acf-fields.php` | ACF field groups, defined in PHP. |
| `inc/settings.php` | Appearance → Jobber & Trust settings screen. |
| `inc/estimate.php` | Estimate button, trust badges, modal, fallback form. |
| `front-page.php` | The landing page. |
| `template-parts/banner.php` | Inner-page banner — used by every non-home view. |
| `template-parts/estimate-band.php` | Closing call to action. |
| `template-contact.php` / `template-areas.php` | Selectable page templates. |
| `archive-*` / `single-*` | Services and Projects listings and detail pages. |
| `index.php` | Fallback for archives, search and 404. |
| `tailwind.config.js` | Design tokens — colours, fluid type scale, shadows. |
| `src/css/main.css` | Theme styles, including the premium layer (see below). |
| `src/css/icons.css` | **Generated** icon masks — do not edit by hand. |

### Editing content

Brand details, opening hours, the marquee and the home page's own copy live in
`inc/config.php`. Services, projects and pages are edited in the admin.

Navigation is built from the database in `inc/context.php` — adding a service
adds it to the header dropdown and the footer column automatically. The
anchor-based nav in `inc/config.php` is kept as a fallback for a site with no
content seeded yet.

Image paths are relative to the theme root (`assets/img/hero.jpg`) or absolute
URLs. An empty value renders a plain neutral pane rather than a broken image, so
the layout never collapses while artwork is outstanding.

---

## The premium layer

`src/css/main.css` ends with a clearly marked `PREMIUM LAYER` block. It is
purely additive — deleting that block returns the site to the original design
exactly. It does not touch brand colours, layout or copy.

What it changes:

1. **Type finish** — `text-wrap: balance` on headings so the ragged edge evens
   out instead of leaving a lonely last word; `pretty` on body copy; kerning and
   optical sizing on.

   > **No letter-spacing anywhere**, by request. `* { letter-spacing: normal }`
   > enforces it, and there are no `tracking-*` classes left in any template.
   > Kerning stays on — that is the font correcting individual awkward pairs from
   > its own metrics, not tracking applied across a line. If uppercase labels
   > ever need loosening again, that rule is the single place to change.
2. **Edge quality** — a 1px inset hairline on every photo pane, so a large flat
   photo meeting the page background does not read as a print misregistration.
3. **Depth** — `shadow-soft` and `shadow-lift` upgraded from a single blur to
   layered contact + ambient shadows. This is most of the "expensive" feel, and
   because it upgrades the two shared tokens it lifts every card at once.
4. **Motion timing** — one easing curve (`cubic-bezier(.22,.61,.36,1)`) across
   every hover, replacing Tailwind's ubiquitous default.
5. **Buttons** — a specular sheen that sweeps once on hover, and a glow that
   deepens as the button lifts so the two read as a single movement.
6. **Focus** — a two-tone ring that stays visible on both light sections and
   dark panels.
7. **Sticky header** — frosts, compresses and grows a hairline once scrolled
   (`.is-stuck`, toggled in `main.js` with a hysteretic threshold so it cannot
   flicker on the boundary).
8. **Dark panels** — a faint grain that stops the wide gradients banding on
   8-bit displays, plus a lit top edge.
9. **Gilt accents** — one warm metallic, used only on eyebrow dots over dark
   panels.
10. **Reveal polish** — AOS travel halved from 100px to 40px so reveals read as
    deliberate rather than busy.

Every one of these respects `prefers-reduced-motion`.

---

## Section backgrounds

Every full-width section alternates white / sand so no two neighbours share a
background — two of the same colour running together read as one long block.

Use `mws_section_bg()` on **every** section, rather than writing `bg-white` or
`bg-sand` by hand:

```php
<section class="<?= $section ?> <?= mws_section_bg() ?>">
```

It returns the opposite of whatever the previous *rendered* section used. That
matters because many sections are conditional — a project with no before/after
photos skips that block entirely, and hard-coded colours would leave the two
sections either side of it matching. Deciding at render time means a skipped
section can never produce a seam.

Dark panels pass their colour explicitly, so they both render dark and tell the
next section what it is following:

```php
<?= mws_section_bg('dark') ?>
```

State starts at `dark`, because every page opens with the hero or a banner.

---

## Content model

| Type | Where | Notes |
| --- | --- | --- |
| **Services** (`mws_service`) | Services → All Services | Archive at `/services/`, one page each. Ordered by the Order field. |
| **Projects** (`mws_project`) | Projects → All Projects | Archive at `/projects/`, filterable by Project Type. |
| **Project Types** | Projects → Project Types | Taxonomy driving the filter chips. |
| **Pages** | Pages | About, Areas We Service, Request an Estimate. |

Registered in `inc/post-types.php` in code, not through ACF's UI, so they deploy
with the theme.

### Page templates

`Contact / Request an Estimate` and `Areas We Service` are selectable from the
Page Attributes box. Everything else uses `page.php`.

### ACF

Field groups are defined in PHP (`inc/acf-fields.php`), so they are in version
control rather than in one machine's database.

> **This site runs ACF free.** Repeater, Flexible Content, Gallery, Clone and
> Options Pages are PRO-only and are **not** available. Repeating content uses a
> textarea parsed one item per line — `Heading | description` — via
> `mws_lines()`, `mws_split_line()` and `mws_split_parts()`. If ACF PRO is ever
> licensed, those are the fields worth converting first.

**About page** uses the `About us` page template and is built from five ACF
section groups, not the editor. Backgrounds alternate white → dark → sand →
white → sand so the sections read as separate chapters. Leave a section's
heading empty to hide it entirely.

| Section | Repeating format |
| --- | --- |
| 1. Story | — (heading, body, image) |
| 2. Numbers band | `Value \| Label` |
| 3. How we work | `icon \| Title \| Description` |
| 4. Milestones | `Year \| Title \| Description` |
| 5. The crew | checklist, one line each |

**Project gallery** is six discrete image fields in a group (`image_1`…
`image_6`) since there is no Gallery field. Fill at least three; empty slots are
skipped. `mws_gallery()` flattens them back into a list, and the first image
runs double-width so the set does not read as a flat contact sheet.

**Service FAQs** carry ten questions each, one per line as `Question | Answer`,
rendered as native `<details>` accordions.

---

## The estimate flow

The whole site points at one action. Every banner on every page carries a
**Request an Estimate** button and the trust badges, and the closing band
repeats them.

The button opens a modal containing a single slot:

- **Jobber URL set** → your Jobber work request form is embedded in an iframe.
- **Not set** → a built-in fallback form captures the lead to
  `storage/leads.log`, so nothing is lost while the integration is outstanding.

Configure it at **Appearance → Jobber & Trust**:

| Setting | What it does |
| --- | --- |
| Work request URL | The Jobber public form. Setting this switches the modal over. |
| Embed height | Iframe height in px (900 suits most Jobber forms). |
| Google / BBB badge | Badge text. Clear a label to hide that badge. |
| Google / BBB link | Optional — makes the badge a link to the real profile. |
| Financing link | Used by the financing band on the home page. |

The iframe carries its URL in `data-src`, not `src` — it is only fetched the
first time a visitor opens the modal, so a third-party form on every page costs
nothing on load.

`/request-an-estimate/` renders the same slot full width, with contact details
beside it.

Any remaining `#booking` link is intercepted in `main.js` and opens the modal,
so the dozen anchors in `inc/config.php` did not each need rewriting.

### Trust badges

Each badge carries its own brand treatment rather than adapting to the section
behind it — Google on a white chip with dark text, BBB reversed out of BBB blue
(`#005A78`, in the Tailwind palette as `bbb`). Because both are self-contained
they read correctly on the dark banner and on a light sidebar without needing a
variant for each.

The two marks are the **only** `<img>` icons on the site. Every other icon is a
CSS mask, which is a single alpha channel and would flatten the four-colour
Google G to one colour.

Badges appear in page banners only. They are deliberately **not** in the closing
estimate band or the marquee strip — the same claim three times on one screen
dilutes it.

---

## No SVG markup

There is no `<svg>` element and no `data:image/svg+xml` URI anywhere in the
rendered page — verified across every template.

- **Icons** are real files in `assets/icons/`, applied as a CSS **mask** on an
  empty `<span>`. The mask is what lets a flat file still take its colour from
  `currentColor`; an `<img>` would render a fixed black glyph and ignore the
  `text-brand-400` classes the markup relies on.
- `src/css/icons.css` is **generated** — one `--mws-icon` custom property per
  file. Regenerate it after adding an icon.
- The favicon and the dark-panel grain texture are `.svg` files too.

Adding an icon: drop `name.svg` into `assets/icons/`, add a matching
`.mws-icon--name { --mws-icon: url("../icons/name.svg") }` rule to
`src/css/icons.css`, rebuild. `mws_icon('name')` then works, and it appears in
the service icon picker automatically (the list is read from the directory).

---

## Imagery

Photography was generated with Higgsfield (`soul_location`). Two rules were
applied and checked on every frame:

- **No faces.** People appear only from behind or as hands. One frame came back
  showing a profile and was regenerated.
- **Workers only where it earns its place** — the trade shots are used on
  service pages where seeing the work being done is the point. Everything else
  is the finished landscape.

## Notes

- **Contact Form 7** was removed at your request — the estimate flow uses Jobber (see above) with a native fallback, so no form plugin is involved.
- **Trustindex** (the Google reviews widget in the testimonials section) is the
  one remaining third-party request. It is a hosted service, not a library —
  there is no npm package, and it has to be served from its own origin to fetch
  live reviews.
- WordPress's emoji polyfill is removed in `functions.php`; it was the only
  other external request (`s.w.org`).
- The block editor's front-end CSS is dequeued **on the front page only**, so
  ordinary content pages keep working blocks.
- Lead submissions are logged to `storage/leads.log`. `send_mail` is off in
  `inc/config.php` because MAMP has no mailer configured — turn it on once one
  is. Submissions are nonce-checked.
