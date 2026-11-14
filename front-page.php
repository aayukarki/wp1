<?php
/**
 * Front page — the GNT Landscaping landing page.
 *
 * Section order mirrors the reference design:
 * hero → about → awards → services → portfolio → process → testimonials →
 * before/after → CTA → journal.
 *
 * All copy and imagery comes from inc/config.php; this file is only markup.
 *
 * @package MWS
 */

declare(strict_types=1);

get_header();

$ctx = mws_context();
$config = $ctx['config'];
$brand = $ctx['brand'];
$tel = $ctx['tel'];
$wrap = $ctx['wrap'];
$section = $ctx['section'];
$h2 = $ctx['h2'];
$h3 = $ctx['h3'];
$spans = $ctx['spans'];
$heroSlides = $ctx['heroSlides'];

/* Service cards.
   The published services themselves, in their admin order, falling back to the
   inc/config.php list when none have been created yet — the same pattern
   inc/context.php already uses to build the nav dropdown, so the carousel, the
   menu and the footer all name the same services instead of the carousel
   quietly keeping its own hardcoded copy.

   Normalised to one shape here so the slide markup below is written once and
   the real and placeholder paths cannot drift apart. Real services link to
   themselves; placeholders link to the archive, because they are not posts. */
$serviceArchive = (string) get_post_type_archive_link('mws_service');
$services       = function_exists('mws_services') ? mws_services() : [];

if ($services !== []) {
    $serviceCards = array_map(
        static function (WP_Post $service): array {
            /* The carousel wants the featured image. service_photo is the
               "supporting photo" that sits beside "What is included" on the
               single view, and only some services carry one. */
            $summary = trim((string) mws_field('service_summary', $service->ID, ''));

            if ($summary === '') {
                $summary = trim((string) get_the_excerpt($service));
            }

            return [
                'title' => (string) get_the_title($service),
                'text'  => $summary,
                'image' => (string) (get_the_post_thumbnail_url($service, 'medium_large') ?: ''),
                'href'  => (string) get_permalink($service),
            ];
        },
        $services
    );
} else {
    $serviceCards = array_map(
        static fn(array $item): array => $item + ['href' => $serviceArchive],
        $config['services']['items']
    );
}

/* Journal cards.
   The two latest published posts, falling back to the inc/config.php entries
   when nothing has been published yet — the same pattern inc/context.php uses
   for services, so the section still renders on a fresh install.

   Both shapes are normalised into one array here so the card markup below is
   written once and the real and placeholder paths cannot drift apart. Real
   posts link to themselves; placeholders link to the journal index, because
   they are not posts and must not pretend to be. */
$journalUrl = $config['journal']['cta']['href'];

$journalPosts = get_posts([
    'post_type'           => 'post',
    'posts_per_page'      => 2,
    'post_status'         => 'publish',
    'ignore_sticky_posts' => true,
]);

if ($journalPosts !== []) {
    $journalCards = array_map(
        static function (WP_Post $post) use ($config): array {
            $categories = get_the_category($post->ID);

            /* get_the_excerpt() with no argument falls back to the global post,
               which on this template is the front page — so an empty excerpt is
               passed through as empty rather than through mws_excerpt(). */
            $excerpt = trim((string) get_the_excerpt($post));

            return [
                'title'    => (string) get_the_title($post),
                'excerpt'  => $excerpt !== '' ? mws_excerpt($excerpt) : '',
                'category' => $categories ? (string) $categories[0]->name : (string) $config['journal']['eyebrow'],
                'date'     => (string) get_the_date('', $post),
                'datetime' => (string) get_the_date('c', $post),
                'image'    => (string) (get_the_post_thumbnail_url($post, 'medium_large') ?: ''),
                'href'     => (string) get_permalink($post),
            ];
        },
        $journalPosts
    );
} else {
    $journalCards = array_map(
        static fn(array $item): array => $item + ['datetime' => '', 'href' => $journalUrl],
        array_slice($config['journal']['items'], 0, 2)
    );
}
?>

<main>

    <!-- ══ Hero ═════════════════════════════════════════════════════════ -->
    <section id="home" class="px-4 2xl:px-14">
        <div class="relative">
            <div data-hero class="relative flex min-h-[min(800px,85vh)] overflow-hidden rounded-b-[60px] bg-dark">

                <!-- Banner slideshow (Swiper, crossfade). Swiper's CSS puts .swiper on
                         z-index 1, so the overlays and content below carry explicit layers. -->
                <div data-hero-swiper class="swiper absolute inset-0">
                    <div class="swiper-wrapper">
                        <?php foreach ($heroSlides as $slide): ?>
                            <div class="swiper-slide">
                                <div <?= mws_media($slide['image'], 'h-full w-full') ?> role="img"
                                    aria-label="<?= mws_e($slide['alt']) ?>"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div
                    class="pointer-events-none absolute inset-0 z-10 bg-[linear-gradient(180deg,rgba(23,16,15,.42)_0%,rgba(23,16,15,.05)_32%,rgba(23,16,15,.72)_100%)]">
                </div>
                <div
                    class="pointer-events-none absolute inset-0 z-10 bg-[linear-gradient(90deg,rgba(23,16,15,.55),transparent_62%)]">
                </div>

                <?php /* Headline and actions left, proof right. Below lg they stack,
                        because two columns in a hero at phone width leaves neither
                        enough room. */ ?>
                <?php /* min-w-0: this is a flex item inside [data-hero], and a flex
                        item defaults to min-width:auto, so it refuses to shrink
                        below its content's intrinsic width. On a narrow phone the
                        button row forced it past the viewport and the hero — which
                        is overflow-hidden — simply clipped the right-hand side. */ ?>
                <div
                    class="<?= $wrap ?> relative z-20 flex w-full min-w-0 flex-col gap-8 py-8 md:py-12 lg:flex-row lg:items-end justify-end lg:justify-between lg:gap-12">

                    <div data-hero-enter class="min-w-0 max-w-[900px] text-white">
                        <h1 class="text-h1 text-white drop-shadow-[0_2px_30px_rgba(0,0,0,.28)]">
                            <?= mws_heading($config['hero']['title']) ?>
                        </h1>
                        <p class="mb-7 mt-5 max-w-[46ch] text-body text-white/85">
                            <?= mws_e($config['hero']['lead']) ?>
                        </p>

                        <?php /* `min-w-0 flex-1` used to let these shrink below their own
                                text, and because .btn-premium is overflow-hidden the label
                                was clipped rather than overflowing — which is why
                                "Request an Estimate" lost its leading R on narrower
                                screens. They are content-sized now and the row wraps
                                instead, which costs a line break and nothing else. */ ?>
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                            <?= mws_estimate_button('primary', 'flex-none !px-4 !text-[0.7rem] sm:!px-6 sm:!text-btn') ?>
                            <a href="<?= mws_e($config['hero']['ghost']['href']) ?>"
                                class="<?= mws_btn('ghost', 'flex-none !px-4 !text-[0.7rem] sm:!px-6 sm:!text-btn') ?>">
                                <?= mws_e($config['hero']['ghost']['label']) ?>
                                <?php /* The arrow is the first thing to go when space is tight. */ ?>
                                <?= mws_icon('arrow', 'hidden h-[18px] w-[18px] flex-none sm:block') ?>
                            </a>
                        </div>
                        <div data-hero-controls hidden
                            class="mt-8 flex items-center justify-center gap-2.5 sm:justify-start md:absolute md:right-12 md:top-12 md:z-30 md:mt-0">
                            <button type="button" data-hero-prev aria-label="Previous slide"
                                class="hidden h-10 w-10 place-items-center rounded-tl-2xl rounded-br-2xl border border-white/25 md:grid bg-white/10 text-white backdrop-blur-sm transition hover:border-white/60 hover:bg-white/20">
                                <?= mws_icon('arrow', 'h-[18px] w-[18px] rotate-180') ?>
                            </button>
                            <div data-hero-dots class="swiper-dots swiper-dots--light"></div>
                            <button type="button" data-hero-next aria-label="Next slide"
                                class="hidden h-10 w-10 place-items-center rounded-tl-2xl rounded-br-2xl border border-white/25 md:grid bg-white/10 text-white backdrop-blur-sm transition hover:border-white/60 hover:bg-white/20">
                                <?= mws_icon('arrow', 'h-[18px] w-[18px]') ?>
                            </button>
                        </div>
                    </div>

                    <?php /* Trust badges, stacked and right-aligned from lg so they
                            sit in the banner's bottom-right corner rather than
                            pushing the headline block taller. */ ?>
                    <div class="flex-none lg:pb-1">
                        <?= mws_trust_badges(true) ?>
                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- ══ About ════════════════════════════════════════════════════════ -->
    <section id="about" class="<?= $section ?> <?= mws_section_bg() ?>">
        <div class="<?= $wrap ?> grid items-center gap-12 lg:grid-cols-2 lg:gap-20">

            <?php /* The review card that used to sit over this photo is gone — the
                    testimonials section further down already carries proof, and a
                    quote here competed with the heading beside it. Replaced with
                    the founding-year badge, the same component the About page
                    uses so the two pages read as one design. */ ?>
            <div <?= mws_aos() ?> class="relative">
                <div <?= mws_media($config['about']['image'], 'aspect-[4/3] w-full rounded-tl-4xl rounded-br-4xl lg:aspect-[4/4.4]') ?> role="img" aria-label="A landscaper laying fresh turf in a finished garden">
                </div>

                <?= mws_stat_badge((string) $brand['founded'], __('Building here since', 'mws')) ?>
            </div>

            <div <?= mws_aos() ?>>
                <?= mws_eyebrow($config['about']['eyebrow']) ?>
                <h2 class="<?= $h2 ?> mb-4"><?= mws_heading($config['about']['title']) ?></h2>
                <p class="mb-8 max-w-[54ch] text-body text-ink-muted"><?= mws_e($config['about']['lead']) ?></p>

                <ul class="mb-9 grid gap-6">
                    <?php foreach ($config['about']['points'] as $point): ?>
                        <li class="flex items-start gap-4">
                            <span
                                class="grid h-12 w-12 flex-none place-items-center rounded-full border border-brand-100 bg-brand-50 text-brand-600">
                                <?= mws_icon($point['icon']) ?>
                            </span>
                            <span class="text-body text-ink-muted">
                                <strong class="mb-1 block text-h3 text-ink"><?= mws_e($point['title']) ?></strong>
                                <?= mws_e($point['text']) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <a href="<?= mws_e($config['about']['cta']['href']) ?>" class="<?= mws_btn('primary') ?>">
                    <?= mws_e($config['about']['cta']['label']) ?>
                </a>
            </div>

        </div>
    </section>

    <!-- ══ Awards ═══════════════════════════════════════════════════════ -->
    <?php $awards = $config['awards']; ?>
    <section id="awards" class="<?= $section ?> <?= mws_section_bg() ?>">
        <div class="<?= $wrap ?>">

            <div <?= mws_aos() ?> class="mb-10 flex flex-col items-center text-center md:mb-12">
                <h2 class="<?= $h2 ?>"><?= mws_heading($awards['title']) ?></h2>
            </div>

            <?php /* Contained, not cover-cropped: these are portrait documents whose
                    text has to stay readable, so each keeps its own aspect ratio. */ ?>
            <ul class="mx-auto grid max-w-[880px] gap-6 sm:grid-cols-2 sm:gap-8">
                <?php foreach ($awards['items'] as $i => $item): ?>
                    <?php $src = mws_image_url($item['image']); ?>
                    <?php if ($src !== null): ?>
                        <li <?= mws_aos('fade-up', $i) ?>>
                            <img src="<?= mws_e($src) ?>" alt="<?= mws_e($item['alt']) ?>" loading="lazy" decoding="async"
                                class="mx-auto h-auto w-full rounded-tl-4xl rounded-br-4xl shadow-soft">
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>

        </div>
    </section>

    <!-- ══ Services ═════════════════════════════════════════════════════ -->
    <section id="services"
        class="<?= $section ?> relative overflow-hidden rounded-5xl <?= mws_section_bg("dark") ?> text-white/70 py-12 sm:px-10 md:px-14 md:py-16">
        <div class="<?= $wrap ?>">

            <div class="relative">
                <div <?= mws_aos() ?>
                    class="mb-11 flex flex-col items-start justify-between gap-8 md:flex-row md:items-end">
                    <div>
                        <?= mws_eyebrow($config['services']['eyebrow'], true) ?>
                        <h2 class="<?= $h2 ?> !text-white"><?= mws_heading($config['services']['title']) ?></h2>
                    </div>
                    <div class="flex flex-none gap-3">
                        <a href="<?= mws_e($config['services']['primary']['href']) ?>"
                            class="<?= mws_btn('primary') ?>">
                            <?= mws_e($config['services']['primary']['label']) ?>
                        </a>
                        <a href="<?= mws_e($config['services']['ghost']['href']) ?>" class="<?= mws_btn('outline') ?>">
                            <?= mws_e($config['services']['ghost']['label']) ?>
                        </a>
                    </div>
                </div>

                <!-- Services carousel (Swiper): 1 → 2 → 3 cards per view. -->
                <div data-services-swiper class="swiper">
                    <ul class="swiper-wrapper">
                        <?php foreach ($serviceCards as $service): ?>
                            <li class="swiper-slide group">
                                <a href="<?= esc_url($service['href']) ?>" class="block">
                                    <div <?= mws_media($service['image'], 'mb-5 aspect-[4/3] rounded-tl-4xl rounded-br-4xl transition duration-500 group-hover:scale-[1.02]') ?> role="img"
                                        aria-label="<?= mws_e($service['title']) ?>">
                                    </div>
                                    <h3 class="<?= $h3 ?> mb-2 text-white transition group-hover:text-brand-200">
                                        <?= mws_e($service['title']) ?>
                                    </h3>
                                    <?php /* line-clamp-2: summaries are editor-written and vary in
                                            length, and the slides sit in a row whose footers have to
                                            line up. */ ?>
                                    <p class="mb-3.5 line-clamp-2 text-body text-white/65"><?= mws_e($service['text']) ?></p>
                                    <?php /* The whole slide is the link and its heading already names
                                            the destination, so a "Learn more" label under each one was
                                            the same two words rendered down the entire carousel. The
                                            arrow carries the affordance; the screen-reader name is the
                                            service, which is the part that actually differs. */ ?>
                                    <span class="inline-flex items-center gap-2 text-btn font-semibold text-brand-200 group-hover:text-white">
                                        <?= mws_icon('arrow', 'h-[18px] w-[18px] transition group-hover:translate-x-1') ?>
                                        <span class="sr-only">
                                            <?php
                                            printf(
                                                /* translators: %s: service name */
                                                esc_html__('Read more about %s', 'mws'),
                                                esc_html($service['title'])
                                            );
                                            ?>
                                        </span>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div data-services-dots class="swiper-dots swiper-dots--brand mt-9 justify-center"></div>
            </div>
        </div>
    </section>

    <!-- ══ Portfolio ════════════════════════════════════════════════════ -->
    <section id="work" class="<?= $section ?> <?= mws_section_bg() ?>">
        <div class="<?= $wrap ?>">

            <div <?= mws_aos() ?> class="mb-11 flex flex-col items-center text-center">
                <?= mws_eyebrow($config['work']['eyebrow']) ?>
                <h2 class="<?= $h2 ?>"><?= mws_heading($config['work']['title']) ?></h2>
            </div>

            <script defer async src='https://cdn.trustindex.io/loader-feed.js?1fcfe327940092455776bfaaf77'></script>

            <!-- <ul
                class="grid auto-rows-[minmax(150px,26vw)] grid-cols-2 gap-4 lg:auto-rows-[minmax(120px,15vw)] lg:grid-cols-4">
                <?php foreach ($config['work']['items'] as $i => $project): ?>
                    <li <?= mws_aos('fade-up', $i) ?> class="<?= mws_e($spans[$project['span']] ?? '') ?>">
                        <a href="<?= esc_url((string) get_post_type_archive_link('mws_project')) ?>"
                            <?= mws_media($project['image'], 'group relative block h-full overflow-hidden rounded-tl-4xl rounded-br-4xl transition duration-300 hover:-translate-y-1 hover:shadow-soft') ?>>
                            <span
                                class="absolute inset-0 bg-[linear-gradient(180deg,transparent_45%,rgba(23,16,15,.78))] opacity-0 transition group-hover:opacity-100 group-focus-visible:opacity-100"></span>
                            <span
                                class="absolute inset-x-5 bottom-4 z-10 translate-y-2 text-white opacity-0 transition duration-300 group-hover:translate-y-0 group-hover:opacity-100 group-focus-visible:translate-y-0 group-focus-visible:opacity-100">
                                <strong class="block text-h3"><?= mws_e($project['title']) ?></strong>
                                <small class="text-micro text-white/75"><?= mws_e($project['meta']) ?></small>
                            </span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul> -->

            <div class="mt-11 text-center">
                <a href="<?= mws_e($config['work']['cta']['href']) ?>" class="<?= mws_btn('primary') ?>">
                    <?= mws_e($config['work']['cta']['label']) ?>
                </a>
            </div>

        </div>
    </section>

    <!-- ══ Process ══════════════════════════════════════════════════════ -->
    <section id="process" class="<?= $section ?> <?= mws_section_bg() ?>">
        <div class="<?= $wrap ?>">

            <div <?= mws_aos() ?> class="mb-11 flex flex-col items-start justify-between gap-8 md:flex-row md:items-end">
                <div>
                    <?= mws_eyebrow($config['process']['eyebrow']) ?>
                    <h2 class="<?= $h2 ?>"><?= mws_heading($config['process']['title']) ?></h2>
                </div>
                <a href="<?= mws_e($config['process']['cta']['href']) ?>"
                    class="<?= mws_btn('primary', 'flex-none') ?>">
                    <?= mws_e($config['process']['cta']['label']) ?>
                </a>
            </div>

            <div <?= mws_aos() ?> class="relative">
                <div <?= mws_media($config['process']['image'], 'aspect-[16/10] min-h-[340px] w-full rounded-tl-4xl rounded-br-4xl lg:w-[76%]') ?> role="img"
                    aria-label="A finished garden walkway leading to a modern home">
                </div>

                <ol
                    class="relative -mt-16 mx-4 grid gap-6 rounded-tl-4xl rounded-br-4xl bg-white p-8 shadow-lift lg:absolute lg:right-0 lg:top-1/2 lg:m-0 lg:w-[min(430px,42%)] lg:-translate-y-1/2">
                    <?php foreach ($config['process']['steps'] as $i => $step): ?>
                        <li class="<?= $i > 0 ? 'border-t border-line pt-6' : '' ?>">
                            <span
                                class="mb-2.5 inline-block rounded-full bg-brand-50 px-3 py-1.5 text-eyebrow font-extrabold uppercase text-brand-600">
                                Step <?= sprintf('%02d', $i + 1) ?>
                            </span>
                            <h3 class="<?= $h3 ?> mb-1.5 text-ink"><?= mws_e($step['title']) ?></h3>
                            <p class="text-body text-ink-muted"><?= mws_e($step['text']) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>

        </div>
    </section>

    <?php /* Live Google reviews. Shared with the location pages — see
            template-parts/section-reviews.php. */ ?>
    <?php get_template_part('template-parts/section-reviews'); ?>

    <!-- ══ Before & after ═══════════════════════════════════════════════ -->
    <?php $ba = $config['beforeafter']; ?>
    <section id="before-after" class="<?= $section ?> <?= mws_section_bg() ?>">
        <div class="<?= $wrap ?>">

            <div <?= mws_aos() ?> class="mx-auto mb-11 flex max-w-[54rem] flex-col items-center text-center">
                <?= mws_eyebrow($ba['eyebrow']) ?>
                <h2 class="<?= $h2 ?>"><?= mws_heading($ba['title']) ?></h2>
                <p class="mt-5 max-w-[62ch] text-body text-ink-muted"><?= mws_e($ba['lead']) ?></p>
            </div>

            <!-- One pair per slide. After sits left and Before right, with the
                     label pinned to each card's outer bottom corner. -->
            <div data-ba-swiper class="swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($ba['items'] as $pair): ?>
                        <div class="swiper-slide">
                            <div class="grid gap-5 sm:grid-cols-2 sm:gap-6">
                                <?php foreach ([['before', 'left-5'], ['after', 'right-5']] as [$kind, $side]): ?>
                                    <figure class="relative">
                                        <div <?= mws_media($pair[$kind], 'aspect-[4/3] w-full rounded-tl-4xl rounded-br-4xl') ?>
                                            role="img" aria-label="<?= mws_e($pair[$kind . '_alt']) ?>"></div>
                                        <figcaption
                                            class="absolute bottom-5 <?= $side ?> rounded-full bg-white px-5 py-2.5 text-micro font-bold uppercase text-ink shadow-soft">
                                            <?= mws_e(ucfirst($kind)) ?>
                                        </figcaption>
                                    </figure>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div data-ba-dots class="swiper-dots swiper-dots--ink mt-9 justify-center"></div>
        </div>
    </section>

    <?php /* The closing call to action is the shared estimate band every
            other page ends on — one component, so the home page cannot
            drift away from the rest of the site. */ ?>
    <?php get_template_part('template-parts/estimate-band'); ?>

    <!-- ══ Journal ══════════════════════════════════════════════════════ -->
    <section id="journal" class="<?= $section ?> <?= mws_section_bg() ?>">
        <div class="<?= $wrap ?>">

            <div <?= mws_aos() ?> class="mb-11 flex flex-col items-start justify-between gap-8 md:flex-row md:items-end">
                <div>
                    <?= mws_eyebrow($config['journal']['eyebrow']) ?>
                    <h2 class="<?= $h2 ?>"><?= mws_heading($config['journal']['title']) ?></h2>
                </div>
                <a href="<?= mws_e($config['journal']['cta']['href']) ?>"
                    class="<?= mws_btn('outline-dark', 'flex-none') ?>">
                    <?= mws_e($config['journal']['cta']['label']) ?>
                </a>
            </div>

            <ul class="grid gap-8 md:grid-cols-2">
                <?php foreach ($journalCards as $i => $card): ?>
                    <li <?= mws_aos('fade-up', $i) ?>>
                        <a href="<?= esc_url($card['href']) ?>" class="group block">
                            <div <?= mws_media($card['image'], 'mb-5 flex aspect-[16/10] items-start justify-end overflow-hidden rounded-tl-4xl rounded-br-4xl p-4') ?> role="img"
                                aria-label="<?= mws_e($card['title']) ?>">
                                <?php if ($card['category'] !== ''): ?>
                                    <span
                                        class="rounded-full bg-white/95 px-3.5 py-1.5 text-micro font-bold text-ink shadow-sm"><?= mws_e($card['category']) ?></span>
                                <?php endif; ?>
                            </div>
                            <h3 class="mb-2 text-h4 text-ink transition group-hover:text-brand-600">
                                <?= mws_e($card['title']) ?>
                            </h3>
                            <?php /* line-clamp-2: a real excerpt is whatever length the
                                    editor left it, and two cards side by side have to
                                    keep their footers on the same line. */ ?>
                            <p class="line-clamp-2 text-body text-ink-muted"><?= mws_e($card['excerpt']) ?></p>
                            <div
                                class="mt-4 flex items-center justify-between border-t border-line pt-4 text-small text-ink-muted">
                                <time<?= $card['datetime'] !== '' ? ' datetime="' . esc_attr($card['datetime']) . '"' : '' ?>><?= mws_e($card['date']) ?></time>
                                <span class="inline-flex items-center gap-2 font-semibold text-brand-600">
                                    Learn more
                                    <?= mws_icon('arrow', 'h-[18px] w-[18px] transition group-hover:translate-x-1') ?>
                                </span>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

        </div>
    </section>

</main>

<?php get_footer(); ?>