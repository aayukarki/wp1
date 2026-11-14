<?php
/**
 * Template Name: Location (town or county)
 *
 * One landing page per township, borough or county.
 *
 * Everything on the page comes from that page's own fields. There is
 * deliberately no shared copy and no config fallback: twenty location pages
 * running the same paragraphs with the place name swapped is duplicate content,
 * and duplicate content is the specific way this kind of page fails. An editor
 * who has nothing particular to say about a section leaves it empty and the
 * section does not render — a shorter honest page beats a padded identical one.
 *
 * The five headline services come from mws_major_service_slugs(); every other
 * published service is listed underneath as a compact grid, so the page still
 * names the full range without giving eleven services equal weight.
 *
 * @package MWS
 */

declare(strict_types=1);

get_header();

$ctx     = mws_context();
$brand   = $ctx['brand'];
$wrap    = $ctx['wrap'];
$section = $ctx['section'];
$h2      = $ctx['h2'];
$h3      = $ctx['h3'];

while (have_posts()) :
    the_post();

    $place  = trim((string) mws_field('location_name', null, (string) get_the_title()));
    $type   = trim((string) mws_field('location_type', null, ''));
    $county = trim((string) mws_field('location_county', null, ''));

    /* The map. An editor can paste the src from Google Maps > Share > Embed a
       map, but the point of deriving it from the place name is that they do not
       have to: every location page gets a map of the right area the moment it
       is published, and a page that never gets that field filled still looks
       finished. The keyless maps.google.com embed is used rather than the
       Embed API so no key has to be provisioned or kept out of the repo. */
    $mapEmbed = trim((string) mws_field('location_map', null, ''));

    if ($mapEmbed === '') {
        $mapQuery = implode(', ', array_filter([$place, $county, 'PA']));
        $mapEmbed = 'https://maps.google.com/maps?q=' . rawurlencode($mapQuery) . '&z=11&output=embed';
    }

    get_template_part('template-parts/banner', null, [
        'title'   => get_the_title(),
        'eyebrow' => (string) mws_field('banner_eyebrow', null, __('Where we work', 'mws')),
        'lead'    => (string) mws_field('banner_lead', null, ''),
        'image'   => (string) mws_field('banner_image', null, 'assets/img/banner-areas.jpg'),
        'crumb'   => get_the_title(),
    ]);
    ?>

    <main>

        <?php /* Intro copy, with the facts panel and the ask alongside it —
                 the same two-column opening the service pages use. */ ?>
        <section class="<?= $section ?> <?= mws_section_bg() ?>">
            <div class="<?= $wrap ?> grid gap-12 lg:grid-cols-[1.15fr,1fr] lg:gap-20">

                <div <?= mws_aos() ?>>
                    <?= mws_eyebrow(sprintf(__('Serving %s', 'mws'), $place)) ?>
                    <h2 class="<?= $h2 ?> mb-6">
                        <?php
                        printf(
                            /* translators: %s: place name */
                            esc_html__('Landscaping and hardscaping in %s', 'mws'),
                            esc_html($place)
                        );
                        ?>
                    </h2>

                    <div class="<?= mws_prose() ?>">
                        <?php the_content(); ?>
                    </div>
                </div>

                <div <?= mws_aos() ?> class="lg:sticky lg:top-[160px] lg:self-start">

                    <?php /* Loaded lazily: it is a third-party iframe well down the
                             right-hand column, and it should not compete with the
                             page's own content for the first paint. */ ?>
                    <figure class="mb-8 overflow-hidden rounded-tl-4xl rounded-br-4xl border border-line shadow-soft">
                        <iframe src="<?= esc_url($mapEmbed) ?>"
                            title="<?php printf(esc_attr__('Map of %s', 'mws'), esc_attr($place)); ?>"
                            class="block h-[340px] w-full border-0 lg:h-[380px]"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen></iframe>

                        <?php if ($type !== '' || $county !== '') : ?>
                            <figcaption class="border-t border-line bg-white px-5 py-4 text-micro uppercase text-ink-muted">
                                <?= mws_e(trim(implode(' · ', array_filter([$place, $type, $county])), ' ·')) ?>
                            </figcaption>
                        <?php endif; ?>
                    </figure>

                    <div class="rounded-tl-4xl rounded-br-4xl border border-line bg-sand p-7">
                        <h3 class="<?= $h3 ?> mb-2 text-ink">
                            <?php
                            printf(
                                /* translators: %s: place name */
                                esc_html__('Booking work in %s', 'mws'),
                                esc_html($place)
                            );
                            ?>
                        </h3>
                        <p class="mb-5 text-body text-ink-muted">
                            <?php esc_html_e('Free on-site estimate and a fixed-price proposal, usually back with you within one business day.', 'mws'); ?>
                        </p>
                        <?= mws_estimate_button('primary', 'w-full') ?>
                        <p class="mt-4 text-center text-small text-ink-muted">
                            <?php esc_html_e('or call', 'mws'); ?>
                            <a class="font-semibold text-brand-600 underline" href="tel:<?= esc_attr($ctx['tel']) ?>"><?= mws_e($brand['phone']) ?></a>
                        </p>
                        <?= mws_trust_badges(false, 'mt-5') ?>
                    </div>
                </div>

            </div>
        </section>

        <?php
        /* Local conditions — the section that stops this being a template with
           a place name in it. */
        $ground      = (array) mws_field('location_ground', null, []);
        $groundItems = mws_lines((string) ($ground['items'] ?? ''));
        ?>
        <?php if (($ground['heading'] ?? '') !== '' && $groundItems !== []) : ?>
            <section class="<?= $section ?> <?= mws_section_bg() ?>">
                <div class="<?= $wrap ?>">

                    <div <?= mws_aos() ?> class="mx-auto mb-11 max-w-[760px] text-center">
                        <?php if (($ground['eyebrow'] ?? '') !== '') : ?>
                            <?= mws_eyebrow((string) $ground['eyebrow']) ?>
                        <?php endif; ?>
                        <h2 class="<?= $h2 ?>"><?= mws_heading((string) $ground['heading']) ?></h2>
                    </div>

                    <ul class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <?php foreach ($groundItems as $i => $line) : ?>
                            <?php [$label, $detail] = mws_split_line($line); ?>
                            <li <?= mws_aos('fade-up', $i) ?>
                                class="rounded-tl-4xl rounded-br-4xl border border-line bg-white p-7">
                                <span class="mb-4 grid h-11 w-11 place-items-center rounded-full bg-brand-50 text-brand-600">
                                    <?= mws_icon('pin', 'h-5 w-5') ?>
                                </span>
                                <strong class="mb-2 block text-h4 text-ink"><?= mws_e($label) ?></strong>
                                <p class="text-body text-ink-muted"><?= mws_e($detail) ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                </div>
            </section>
        <?php endif; ?>

        <?php
        /* The five headline services, alternating sides down the page. Each
           block only renders if this page wrote a heading for it, and $flip
           counts rendered sections rather than loop position — otherwise a
           skipped service would put two images down the same edge. */
        $flip = false;

        foreach (mws_major_services() as $i => $major) {
            $svc = (array) mws_field('location_svc_' . ($i + 1), null, []);

            if (trim((string) ($svc['heading'] ?? '')) === '') {
                continue;
            }

            /* A photo of work done in this town is better than a generic one,
               so the page's own field wins. But most location pages will never
               have one, and an empty grey pane down the middle of five sections
               looks broken rather than unfinished — so the service's own
               featured image stands in. Only if neither exists does the pane
               fall back to .is-empty. */
            $svcImage = trim((string) ($svc['image'] ?? ''));

            if ($svcImage === '' && $major['post']) {
                $svcImage = (string) (get_the_post_thumbnail_url($major['post'], 'large') ?: '');
            }

            get_template_part('template-parts/location-service', null, [
                'eyebrow' => $major['label'],
                'heading' => (string) $svc['heading'],
                'text'    => (string) ($svc['text'] ?? ''),
                'points'  => (string) ($svc['points'] ?? ''),
                'image'   => $svcImage,
                'link'    => $major['post'] ? (string) get_permalink($major['post']) : '',
                'label'   => $major['label'],
                'place'   => $place,
                'flip'    => $flip,
            ]);

            $flip = !$flip;
        }
        ?>

        <?php
        /* Everything else, as a compact grid. Pulled live from the CPT, so a
           new service appears on every location page without any of them
           being edited. */
        $otherHeading = trim((string) mws_field('location_other_heading', null, ''));
        $others       = mws_minor_services();
        ?>
        <?php if ($otherHeading !== '' && $others !== []) : ?>
            <section class="<?= $section ?> <?= mws_section_bg() ?>">
                <div class="<?= $wrap ?>">

                    <div <?= mws_aos() ?> class="mb-11 text-center">
                        <?= mws_eyebrow(__('Also available', 'mws')) ?>
                        <h2 class="<?= $h2 ?>"><?= mws_heading($otherHeading) ?></h2>
                    </div>

                    <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <?php foreach ($others as $i => $service) : ?>
                            <?php
                            $blurb = trim((string) mws_field('service_summary', $service->ID, ''));

                            if ($blurb === '') {
                                $blurb = trim((string) get_the_excerpt($service));
                            }
                            ?>
                            <li <?= mws_aos('fade-up', $i) ?>>
                                <a href="<?= esc_url((string) get_permalink($service)) ?>"
                                    class="group flex h-full flex-col rounded-tl-2xl rounded-br-2xl border border-line bg-white p-6 transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-soft">
                                    <span class="mb-4 grid h-10 w-10 place-items-center rounded-full bg-brand-50 text-brand-600">
                                        <?= mws_icon((string) mws_field('service_icon', $service->ID, 'leaf'), 'h-5 w-5') ?>
                                    </span>
                                    <strong class="mb-2 text-h4 text-ink transition group-hover:text-brand-600">
                                        <?= mws_e(get_the_title($service)) ?>
                                    </strong>
                                    <p class="line-clamp-2 text-body text-ink-muted"><?= mws_e($blurb) ?></p>
                                    <span class="mt-4 inline-flex items-center gap-2 text-btn font-semibold text-brand-600">
                                        <?php esc_html_e('Learn more', 'mws'); ?>
                                        <?= mws_icon('arrow', 'h-[18px] w-[18px] transition group-hover:translate-x-1') ?>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                </div>
            </section>
        <?php endif; ?>

        <?php
        /* Local proof. Matched on the project's own location field, so a town
           page shows work done in that town; nothing matching means the section
           is skipped rather than padded with projects from the far side of the
           service area. */
        $nearbyProjects = $place === '' ? [] : get_posts([
            'post_type'      => 'mws_project',
            'posts_per_page' => 3,
            'meta_query'     => [[
                'key'     => 'project_location',
                'value'   => $place,
                'compare' => 'LIKE',
            ]],
        ]);
        ?>
        <?php if ($nearbyProjects !== []) : ?>
            <section class="<?= $section ?> <?= mws_section_bg() ?>">
                <div class="<?= $wrap ?>">
                    <div <?= mws_aos() ?> class="mb-11 flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                        <div>
                            <?= mws_eyebrow(__('Proof', 'mws')) ?>
                            <h2 class="<?= $h2 ?>">
                                <?php
                                printf(
                                    /* translators: %s: place name */
                                    esc_html__('Work we have done in %s', 'mws'),
                                    esc_html($place)
                                );
                                ?>
                            </h2>
                        </div>
                        <a href="<?= esc_url((string) get_post_type_archive_link('mws_project')) ?>" class="<?= mws_btn('outline-dark', 'flex-none') ?>">
                            <?php esc_html_e('All projects', 'mws'); ?>
                        </a>
                    </div>

                    <ul class="grid gap-8 md:grid-cols-3">
                        <?php foreach ($nearbyProjects as $i => $project) : ?>
                            <li <?= mws_aos('fade-up', $i) ?>><?php mws_project_card($project); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
        <?php endif; ?>

        <?php /* Reviews sit straight after the local project proof: the work,
                 then what people said about it. The heading is built from the
                 place so the section reads as local rather than as the same
                 widget bolted onto twenty pages. */ ?>
        <?php
        get_template_part('template-parts/section-reviews', null, [
            'heading' => $place !== ''
                ? sprintf(__('What our %s clients say', 'mws'), $place)
                : null,
        ]);
        ?>

        <?php $faq = mws_lines((string) mws_field('location_faq', null, '')); ?>
        <?php if ($faq !== []) : ?>
            <section class="<?= $section ?> <?= mws_section_bg() ?>">
                <div class="<?= $wrap ?> mx-auto max-w-[860px]">
                    <div <?= mws_aos() ?> class="mb-10 text-center">
                        <?= mws_eyebrow(__('Good to know', 'mws')) ?>
                        <h2 class="<?= $h2 ?>">
                            <?php
                            printf(
                                /* translators: %s: place name */
                                esc_html__('Questions we get from %s', 'mws'),
                                esc_html($place)
                            );
                            ?>
                        </h2>
                    </div>

                    <div class="grid gap-4">
                        <?php foreach ($faq as $i => $line) : ?>
                            <?php [$question, $answer] = mws_split_line($line); ?>
                            <details <?= mws_aos('fade-up', $i) ?>
                                class="group rounded-tl-4xl rounded-br-4xl border border-line bg-white p-6 transition hover:border-brand-200 open:shadow-soft">
                                <summary class="flex cursor-pointer items-center justify-between gap-5 text-h4 text-ink [&::-webkit-details-marker]:hidden">
                                    <?= mws_e($question) ?>
                                    <?= mws_icon('chevron', 'h-5 w-5 flex-none text-brand-600 transition duration-300 group-open:rotate-180') ?>
                                </summary>
                                <p class="mt-4 text-body text-ink-muted"><?= mws_e($answer) ?></p>
                            </details>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php
        /* Neighbouring areas. These links are what turn a pile of separate
           location pages into a connected set rather than twenty orphans each
           reachable only from the hub. */
        $nearby = mws_lines((string) mws_field('location_nearby', null, ''));
        ?>
        <?php if ($nearby !== []) : ?>
            <section class="<?= $section ?> <?= mws_section_bg() ?>">
                <div class="<?= $wrap ?> text-center">

                    <div <?= mws_aos() ?> class="mb-9">
                        <?= mws_eyebrow(__('Nearby', 'mws')) ?>
                        <h2 class="<?= $h2 ?>"><?php esc_html_e('We also work just up the road', 'mws'); ?></h2>
                    </div>

                    <ul <?= mws_aos() ?> class="flex flex-wrap justify-center gap-3">
                        <?php foreach ($nearby as $line) : ?>
                            <?php
                            [$label, $url] = mws_split_line($line);

                            if ($label === '') {
                                continue;
                            }
                            ?>
                            <li>
                                <?php if ($url !== '') : ?>
                                    <a href="<?= esc_url($url) ?>"
                                        class="inline-flex items-center gap-2 rounded-full border border-line bg-white px-5 py-2.5 text-body text-ink transition hover:border-brand-200 hover:text-brand-600">
                                        <?= mws_icon('pin', 'h-4 w-4 text-brand-600') ?>
                                        <?= mws_e($label) ?>
                                    </a>
                                <?php else : ?>
                                    <span class="inline-flex items-center gap-2 rounded-full border border-line bg-white px-5 py-2.5 text-body text-ink-muted">
                                        <?= mws_icon('pin', 'h-4 w-4 text-brand-600') ?>
                                        <?= mws_e($label) ?>
                                    </span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                </div>
            </section>
        <?php endif; ?>

    </main>

    <?php
    get_template_part('template-parts/estimate-band');

endwhile;

get_footer();
