# Photos

Every image on the page is set in `inc/config.php` under an `image` key. Each
one accepts either:

* an **absolute URL** — `https://cdn.example.com/hero.jpg` (or `//host/…`), or
* a **path relative to the project root** — `assets/img/hero.jpg`, for files
  dropped into this folder.

An empty value renders a plain neutral pane (`.media.is-empty`), so the layout
holds until a source is added. There are no gradient placeholders any more.

## Where each image goes

Every slot below is currently filled. The aspect ratio matters more than the
exact pixel size — panes are `background-size: cover`, so a mismatched ratio is
cropped rather than squashed.

| Config key                 | Used by               | Ratio | Shipped size  |
|----------------------------|-----------------------|-------|---------------|
| `brand.logo.src`           | Header logo           | —     | 200 × 98 png  |
| `hero.slides[0…3].image`   | Banner slideshow (4)  | 16:9  | 1600 × 893    |
| `about.image`              | "Why choose us"       | 1:1   | 1000 × 1000   |
| `services.items[…].image`  | Services carousel (11)| 4:3   | 1100 × 821    |
| `work.items[…].image`      | Portfolio mosaic (6)  | mixed | see below     |
| `process.image`            | Process section       | 3:2   | 1264 × 848    |
| `journal.items[…].image`   | Journal cards (2)     | 3:2   | 1100 × 738    |

The portfolio mosaic tiles are different shapes on desktop, set by the `span`
key against `$spans` in `index.php`, so their images are cut to match:

| Span | Tile        | Ratio | Files                     |
|------|-------------|-------|---------------------------|
| a, b | 2 × 2 cells | 1:1   | `work-1.jpg`, `work-2.jpg` |
| c, e | 1 × 2 cells | 2:3   | `work-3.jpg`, `work-5.jpg` |
| d, f | 2 × 1 cells | 16:9  | `work-4.jpg`, `work-6.jpg` |

## Banner slideshow

`hero.slides` holds four entries, each with an `image` and an `alt` description
used by screen readers. Slides crossfade every 6 seconds, pause while the
visitor hovers, types or switches tabs, and can be stepped through with the
arrows and dots in the banner's top-right corner.

Only slides with a usable image join the rotation (`index.php` filters the
list), so a half-filled config never fades to a blank pane. With a single slide
the controls hide themselves entirely.

Because the slides stack on top of each other, use images with a similar crop
and exposure — mismatched brightness makes the crossfade obvious.

## Weight

Keep each image under ~300 KB. They load as CSS background images, so there is
no lazy-loading fallback and **all four banner slides are fetched up front** —
about 1.3 MB before the hero settles. If that matters, cut the banner to two or
three slides, or serve the images as WebP/AVIF from a CDN.
