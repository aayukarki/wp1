<?php
/**
 * Template Name: About us
 *
 * Built from the ACF sections on the page rather than the editor, and assembled
 * from the shared section components in template-parts/ — the same four blocks
 * services and projects use, so the whole site speaks one visual vocabulary.
 *
 * Backgrounds alternate automatically via mws_section_bg(), so hiding a section
 * never leaves two matching neighbours.
 *
 * @package MWS
 */

declare(strict_types=1);

get_header();

$ctx   = mws_context();
$brand = $ctx['brand'];

while (have_posts()) :
    the_post();

    get_template_part('template-parts/banner', null, [
        'title'   => (string) (get_the_title() ?: __('About us', 'mws')),
        'eyebrow' => (string) mws_field('banner_eyebrow', null, __('Since 2009', 'mws')),
        'lead'    => (string) mws_field('banner_lead'),
        'image'   => (string) mws_field('banner_image', null, 'assets/img/banner-about.jpg'),
        'crumb'   => get_the_title(),
    ]);

    $story    = (array) mws_field('about_story', null, []);
    $stats    = (array) mws_field('about_stats', null, []);
    $values   = (array) mws_field('about_values', null, []);
    $timeline = (array) mws_field('about_timeline', null, []);
    $crew     = (array) mws_field('about_crew', null, []);
    ?>

    <main>

        <?php
        /* 1 — Story */
        get_template_part('template-parts/section-split', null, [
            'eyebrow' => (string) ($story['eyebrow'] ?? ''),
            'heading' => (string) ($story['heading'] ?? ''),
            'text'    => (string) ($story['text'] ?? ''),
            'image'   => (string) ($story['image'] ?? 'assets/img/about.jpg'),
            'flip'    => true,
            'badge'   => $brand['founded'] . ' | ' . __('Building here since', 'mws'),
        ]);

        /* 2 — Numbers */
        get_template_part('template-parts/section-stats', null, [
            'heading' => (string) ($stats['heading'] ?? ''),
            'items'   => (string) ($stats['items'] ?? ''),
        ]);

        /* 3 — How we work */
        get_template_part('template-parts/section-cards', null, [
            'eyebrow' => (string) ($values['eyebrow'] ?? ''),
            'heading' => (string) ($values['heading'] ?? ''),
            'lead'    => (string) ($values['lead'] ?? ''),
            'items'   => (string) ($values['items'] ?? ''),
        ]);

        /* 4 — Milestones */
        get_template_part('template-parts/section-timeline', null, [
            'eyebrow' => (string) ($timeline['eyebrow'] ?? ''),
            'heading' => (string) ($timeline['heading'] ?? ''),
            'items'   => (string) ($timeline['items'] ?? ''),
        ]);

        /* 5 — The crew */
        get_template_part('template-parts/section-split', null, [
            'eyebrow' => (string) ($crew['eyebrow'] ?? ''),
            'heading' => (string) ($crew['heading'] ?? ''),
            'text'    => (string) ($crew['text'] ?? ''),
            'image'   => (string) ($crew['image'] ?? 'assets/img/work-planting.jpg'),
            'points'  => (string) ($crew['points'] ?? ''),
            'cta'     => true,
        ]);
        ?>

    </main>

    <?php
    get_template_part('template-parts/estimate-band');

endwhile;

get_footer();
