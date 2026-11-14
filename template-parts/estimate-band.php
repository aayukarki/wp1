<?php
/**
 * Closing estimate band.
 *
 * Sits at the foot of every inner page, so a visitor who has read to the end
 * never has to scroll back up to act. Same dark treatment as the home page's
 * CTA band, so the two read as one system.
 *
 * @package MWS
 */

declare(strict_types=1);

$ctx  = mws_context();
$wrap = $ctx['wrap'];

$heading = (string) ($args['title'] ?? __("Ready for a straight answer\non what your project costs?", 'mws'));
$lead    = (string) ($args['lead'] ?? __('Free on-site estimate, fixed-price proposal, no pressure. Most quotes are back with you within one business day.', 'mws'));
?>

<section class="<?= $ctx["section"] ?> <?= mws_section_bg() ?>">
    <div class="<?= $wrap ?>">
        <div <?= mws_aos() ?> class="relative overflow-hidden rounded-5xl bg-dark px-6 py-16 text-center md:py-20">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(60%_80%_at_100%_0%,rgba(200,16,46,.32),transparent_60%)]"></div>
            <div class="pointer-events-none absolute inset-0 opacity-50 [background-image:radial-gradient(rgba(255,255,255,.12)_1px,transparent_1px)] [background-size:22px_22px] [mask-image:radial-gradient(70%_70%_at_50%_50%,#000,transparent)]"></div>

            <div class="relative flex flex-col items-center">
                <?= mws_eyebrow(__('Free · No obligation', 'mws'), true) ?>

                <h2 class="mb-5 max-w-[24ch] text-h2 !text-white"><?= mws_heading($heading) ?></h2>

                <p class="mb-8 max-w-[58ch] text-body text-white/75"><?= mws_e($lead) ?></p>

                <div class="flex flex-wrap items-center justify-center gap-3">
                    <?= mws_estimate_button('primary') ?>
                    <a href="tel:<?= mws_e($ctx['tel']) ?>" class="<?= mws_btn('outline') ?>">
                        <?= mws_icon('phone', 'h-[18px] w-[18px]') ?>
                        <?= mws_e($ctx['brand']['phone']) ?>
                    </a>
                </div>

                <?php /* No trust badges here. They carry the banner at the top of
                         every page; repeating them at the foot of the same page
                         dilutes them and pushes the buttons off centre. */ ?>
            </div>
        </div>
    </div>
</section>
