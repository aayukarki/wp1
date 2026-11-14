<?php
/**
 * Image beside text, with an optional ticked checklist and CTA.
 *
 * Shared component — the About page's story and crew blocks. Reused for a
 * service's guarantee and a project's brief.
 *
 * Args:
 *   eyebrow  string  optional
 *   heading  string  optional
 *   text     string  HTML (runs through wp_kses_post)
 *   image    string  path relative to the theme, or an absolute URL
 *   points   string  one per line, plain text — rendered as a ticked list
 *   flip     bool    image on the right instead of the left
 *   cta      bool    append the estimate button
 *   badge    string  optional "Value | Label" chip set into the image corner
 *
 * @package MWS
 */

declare(strict_types=1);

$ctx  = mws_context();
$wrap = $ctx['wrap'];
$sec  = $ctx['section'];
$h2   = $ctx['h2'];

$heading = (string) ($args['heading'] ?? '');
$image   = (string) ($args['image'] ?? '');

if ($heading === '' && $image === '') {
    return;
}

$eyebrow = (string) ($args['eyebrow'] ?? '');
$text    = (string) ($args['text'] ?? '');
$points  = mws_lines((string) ($args['points'] ?? ''));
$flip    = (bool) ($args['flip'] ?? false);
$cta     = (bool) ($args['cta'] ?? false);
$badge   = (string) ($args['badge'] ?? '');
?>

<section class="<?= $sec ?> <?= mws_section_bg() ?>">
    <div class="<?= $wrap ?> grid items-center gap-12 lg:grid-cols-2 lg:gap-20">

        <div <?= mws_aos() ?> class="relative <?= $flip ? 'order-2' : 'order-2 lg:order-1' ?>">
            <div <?= mws_media($image, 'aspect-[4/3] w-full rounded-tl-4xl rounded-br-4xl shadow-soft lg:aspect-[4/4.2]') ?>
                role="img" aria-label="<?= mws_e($heading) ?>"></div>

            <?php if ($badge !== '') : ?>
                <?php [$badgeValue, $badgeLabel] = mws_split_line($badge); ?>
                <?= mws_stat_badge($badgeValue, $badgeLabel, $flip ? 'right' : 'left') ?>
            <?php endif; ?>
        </div>

        <div <?= mws_aos() ?> class="<?= $flip ? 'order-1' : 'order-1 lg:order-2' ?>">
            <?php if ($eyebrow !== '') : ?>
                <?= mws_eyebrow($eyebrow) ?>
            <?php endif; ?>

            <?php if ($heading !== '') : ?>
                <h2 class="<?= $h2 ?> mb-6"><?= mws_heading($heading) ?></h2>
            <?php endif; ?>

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

            <?php if ($cta) : ?>
                <div class="mt-9"><?= mws_estimate_button('primary') ?></div>
            <?php endif; ?>
        </div>

    </div>
</section>
