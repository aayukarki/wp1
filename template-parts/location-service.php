<?php
/**
 * One of the five headline services, written for a specific town or county.
 *
 * Modelled on template-parts/section-split, but with the one thing that part
 * cannot do: a link through to the service post. That link is the entire point
 * of these blocks — a location page that talks about hardscaping without
 * linking to the hardscaping page gives the service page nothing, and these are
 * the pages meant to be feeding it.
 *
 * The heading gates the section. An editor who has nothing specific to say
 * about snow removal in a given township leaves it empty and the section is
 * skipped, rather than every location page carrying the same filler.
 *
 * Args:
 *   eyebrow  string  the service name as the location pages call it
 *   heading  string  location-specific headline — empty hides the section
 *   text     string  HTML (runs through wp_kses_post)
 *   points   string  one per line, rendered as a ticked list
 *   image    string  theme-relative path or absolute URL
 *   link     string  permalink of the service post, '' to omit the link
 *   label    string  service name used in the link text
 *   place    string  town or county name, appended to the heading when missing
 *   flip     bool    image on the right instead of the left
 *
 * @package MWS
 */

declare(strict_types=1);

$heading = (string) ($args['heading'] ?? '');

if ($heading === '') {
    return;
}

$ctx  = mws_context();
$wrap = $ctx['wrap'];
$sec  = $ctx['section'];
$h2   = $ctx['h2'];

$eyebrow = (string) ($args['eyebrow'] ?? '');
$text    = (string) ($args['text'] ?? '');
$points  = mws_lines((string) ($args['points'] ?? ''));
$image   = (string) ($args['image'] ?? '');
$link    = (string) ($args['link'] ?? '');
$label   = (string) ($args['label'] ?? '');
$place   = (string) ($args['place'] ?? '');
$flip    = (bool) ($args['flip'] ?? false);

/* The place name belongs in every one of these headings. They are the
   location-targeting h2s on the page, and a heading that would sit just as
   happily on another town's page is a wasted one.
   Appended only when the editor has not already worked it in, so a heading
   written naturally around the place is left exactly as it was typed. The
   check is on the stripped text because the field carries newlines that
   mws_heading() turns into <br>. */
if ($place !== '' && stripos(wp_strip_all_tags($heading), $place) === false) {
    $heading = rtrim($heading) . ' in ' . $place;
}
?>

<section class="<?= $sec ?> <?= mws_section_bg() ?>">
    <div class="<?= $wrap ?> grid items-center gap-12 lg:grid-cols-2 lg:gap-20">

        <div <?= mws_aos() ?> class="relative <?= $flip ? 'order-2' : 'order-2 lg:order-1' ?>">
            <div <?= mws_media($image, 'aspect-[4/3] w-full rounded-tl-4xl rounded-br-4xl shadow-soft lg:aspect-[4/4.2]') ?>
                role="img" aria-label="<?= mws_e($heading) ?>"></div>
        </div>

        <div <?= mws_aos() ?> class="<?= $flip ? 'order-1' : 'order-1 lg:order-2' ?>">
            <?php if ($eyebrow !== '') : ?>
                <?= mws_eyebrow($eyebrow) ?>
            <?php endif; ?>

            <h2 class="<?= $h2 ?> mb-6"><?= mws_heading($heading) ?></h2>

            <?php if ($text !== '') : ?>
                <div class="<?= mws_prose() ?>"><?= wp_kses_post($text) ?></div>
            <?php endif; ?>

            <?php if ($points !== []) : ?>
                <ul class="mt-8 grid gap-4">
                    <?php foreach ($points as $point) : ?>
                        <li class="flex items-start gap-3.5">
                            <span class="mt-0.5 grid h-7 w-7 flex-none place-items-center rounded-full bg-brand-600 text-white">
                                <?= mws_icon('check', 'h-4 w-4') ?>
                            </span>
                            <span class="text-body text-ink-muted"><?= mws_e($point) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ($link !== '') : ?>
                <div class="mt-9">
                    <a href="<?= esc_url($link) ?>" class="<?= mws_btn('outline-dark') ?>">
                        <?php
                        printf(
                            /* translators: %s: service name, e.g. "Hardscaping" */
                            esc_html__('More on %s', 'mws'),
                            esc_html($label !== '' ? $label : $eyebrow)
                        );
                        ?>
                        <?= mws_icon('arrow', 'h-[18px] w-[18px]') ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>
