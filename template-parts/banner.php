<?php
/**
 * Inner-page banner.
 *
 * Every page except the front page uses this. It carries the same three things
 * on every view, because the estimate request is the point of the site:
 * the trust badges, the estimate button, and a single clear heading.
 *
 * Args (via get_template_part's $args):
 *   title     string  required
 *   eyebrow   string  small label above the title
 *   lead      string  one-line supporting sentence
 *   image     string  path relative to the theme, or an absolute URL
 *   crumb     string  label of the current page for the breadcrumb trail
 *
 * @package MWS
 */

declare(strict_types=1);

$ctx  = mws_context();
$wrap = $ctx['wrap'];

$title   = (string) ($args['title'] ?? get_the_title());
$eyebrow = (string) ($args['eyebrow'] ?? '');
$lead    = (string) ($args['lead'] ?? '');
$image   = (string) ($args['image'] ?? 'assets/img/banner-services.jpg');
$crumb   = (string) ($args['crumb'] ?? $title);

/* Blog posts and the journal listing carry no description under the heading —
   the title alone does the work there, so the lead is dropped whatever the
   caller passes. */
if (is_singular('post') || is_home()) {
    $lead = '';
}
?>

<section class="px-4 2xl:px-14">
    <div class="relative flex min-h-[380px] overflow-hidden rounded-b-[60px] bg-dark md:min-h-[460px]">

        <div <?= mws_media($image, 'absolute inset-0') ?> role="img" aria-label="<?= mws_e($title) ?>"></div>

        <div class="pointer-events-none absolute inset-0 z-10 bg-[linear-gradient(180deg,rgba(23,16,15,.55)_0%,rgba(23,16,15,.25)_40%,rgba(23,16,15,.80)_100%)]"></div>
        <div class="pointer-events-none absolute inset-0 z-10 bg-[linear-gradient(90deg,rgba(23,16,15,.60),transparent_68%)]"></div>

        <div class="<?= $wrap ?> relative z-20 flex w-full flex-col justify-end py-10 md:py-14">

            <nav aria-label="Breadcrumb" class="mb-5">
                <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-micro text-white/70">
                    <li><a href="<?= esc_url(home_url('/')) ?>" class="transition hover:text-white"><?php esc_html_e('Home', 'mws'); ?></a></li>
                    <li aria-hidden="true">/</li>
                    <li><span class="font-semibold text-white"><?= mws_e($crumb) ?></span></li>
                </ol>
            </nav>

            <div class="max-w-[820px] text-white">
                <?php /* The eyebrow <p> that used to sit above the heading is gone —
                         the breadcrumb already says where you are, so it was a third
                         line of small text before the page even names itself. The
                         $eyebrow arg is still accepted so callers need no changes. */ ?>
                <h1 class="text-h1 text-white drop-shadow-[0_2px_30px_rgba(0,0,0,.28)]">
                    <?= mws_heading($title) ?>
                </h1>

                <?php if ($lead !== '') : ?>
                    <p class="mt-5 max-w-[54ch] text-body text-white/85"><?= mws_e($lead) ?></p>
                <?php endif; ?>
            </div>

            <?php /* The conversion row: estimate button first, proof beside it. */ ?>
            <div class="mt-8 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <?= mws_estimate_button('primary') ?>
                    <a href="tel:<?= mws_e($ctx['tel']) ?>" class="<?= mws_btn('outline') ?>">
                        <?= mws_icon('phone', 'h-[18px] w-[18px]') ?>
                        <?= mws_e($ctx['brand']['phone']) ?>
                    </a>
                </div>

                <?= mws_trust_badges(true) ?>
            </div>

        </div>
    </div>
</section>
