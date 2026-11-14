<?php
/**
 * Live Google reviews, as a hosted Trustindex widget.
 *
 * Shared by the home page and the location pages. The widget id in particular
 * is the reason this is a component rather than copied markup: it is an opaque
 * string that means nothing on sight, so a second copy of it somewhere else in
 * the theme is a copy nobody would think to update when the Trustindex account
 * changes.
 *
 * On sand rather than white, so the review cards sit on a tint and read as
 * their own section between the two white ones either side.
 *
 * Args:
 *   eyebrow  string  defaults to the config testimonials eyebrow
 *   heading  string  defaults to the config testimonials title
 *   cta      bool    show the button underneath (default true)
 *
 * @package MWS
 */

declare(strict_types=1);

$ctx     = mws_context();
$config  = $ctx['config'];
$section = $ctx['section'];
$h2      = $ctx['h2'];

$reviews = $config['testimonials'] ?? [];

$eyebrow = (string) ($args['eyebrow'] ?? ($reviews['eyebrow'] ?? ''));
$heading = (string) ($args['heading'] ?? ($reviews['title'] ?? ''));
$cta     = (bool) ($args['cta'] ?? true);
?>

<section id="testimonials" class="<?= $section ?> <?= mws_section_bg() ?>">

    <?php if ($eyebrow !== '' || $heading !== '') : ?>
        <div <?= mws_aos() ?> class="mb-8 flex flex-col items-center text-center md:mb-12">
            <?php if ($eyebrow !== '') : ?>
                <?= mws_eyebrow($eyebrow) ?>
            <?php endif; ?>
            <?php if ($heading !== '') : ?>
                <h2 class="<?= $h2 ?>"><?= mws_heading($heading) ?></h2>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php /* Trustindex is a hosted review widget, not a library — it has no npm
            package and must be served from its own origin to fetch the live
            Google reviews. It is the one remaining third-party request.

            .mws-reviews scales the widget up — see the premium layer in
            main.css. Its own markup is out of our hands, so the size is set
            from the wrapper rather than by styling its internals. */ ?>
    <div class="mws-reviews px-4">
        <script defer async src="https://cdn.trustindex.io/loader.js?44722597944d021a5186af6485d"></script>
    </div>

    <?php if ($cta && ($reviews['cta']['href'] ?? '') !== '') : ?>
        <div class="mt-8 text-center md:mt-12">
            <a href="<?= mws_e($reviews['cta']['href']) ?>" class="<?= mws_btn('primary') ?>">
                <?= mws_e($reviews['cta']['label'] ?? '') ?>
            </a>
        </div>
    <?php endif; ?>

</section>
