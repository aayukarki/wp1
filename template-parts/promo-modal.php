<?php
/**
 * Promotion pop-up.
 *
 * A single image that opens over the page shortly after load, configured at
 * Appearance → Jobber & Trust. Deliberately kept separate from the estimate
 * modal: they can both exist on a page, and only one should ever be open.
 *
 * Shown once per browser session — main.js records the dismissal in
 * sessionStorage, so moving between pages does not reopen it, but a fresh visit
 * tomorrow will. An interstitial on every page view is the fastest way to make
 * people leave.
 *
 * The <img> carries the real dimensions so the browser reserves the right box
 * before it decodes, and it is `loading="eager"` because it is about to be shown
 * on purpose.
 *
 * @package MWS
 */

declare(strict_types=1);

if (!mws_setting('promo_enabled')) {
    return;
}

$image = (string) mws_setting('promo_image');

if ($image === '') {
    $rel = 'assets/img/snow-and-ice-offer.jpg';
    if (!is_file(get_template_directory() . '/' . $rel)) {
        return;
    }
    $image = mws_asset_url($rel);
}

$alt   = (string) mws_setting('promo_alt');
$link  = (string) mws_setting('promo_url');
$delay = (int) mws_setting('promo_delay', 1200);
?>

<div data-promo-modal data-promo-delay="<?= esc_attr((string) $delay) ?>" hidden
    class="fixed inset-0 z-[130] flex items-center justify-center p-4 sm:p-6">

    <div data-promo-close aria-hidden="true"
        class="fixed inset-0 bg-ink/75 opacity-0 backdrop-blur-sm transition-opacity duration-400 [[data-promo-modal].is-open_&]:opacity-100"></div>

    <div role="dialog" aria-modal="true" aria-label="<?= esc_attr($alt !== '' ? $alt : __('Special offer', 'mws')) ?>"
        class="relative z-10 w-full max-w-[860px] scale-95 opacity-0 transition duration-400 [[data-promo-modal].is-open_&]:scale-100 [[data-promo-modal].is-open_&]:opacity-100">

        <?php /* Close sits outside the artwork on larger screens so it never
                 covers the offer, and tucks into the corner on small ones. */ ?>
        <button type="button" data-promo-close
            aria-label="<?= esc_attr__('Close', 'mws') ?>"
            class="absolute right-2 top-2 z-20 grid h-11 w-11 place-items-center rounded-full bg-white text-ink shadow-lift transition hover:bg-sand sm:-right-4 sm:-top-4">
            <?= mws_icon('close', 'h-5 w-5') ?>
        </button>

        <?php
        $img = sprintf(
            '<img src="%s" alt="%s" width="1200" height="686" loading="eager" decoding="async" '
            . 'class="block h-auto w-full rounded-tl-4xl rounded-br-4xl shadow-lift">',
            esc_url($image),
            esc_attr($alt)
        );
        ?>

        <?php if ($link !== '') : ?>
            <a href="<?= esc_url($link) ?>" class="block"><?= $img ?></a>
        <?php else : ?>
            <?php /* No link configured, so the image opens the estimate form —
                     the point of showing the offer in the first place. */ ?>
            <button type="button" data-estimate-open class="block w-full text-left"><?= $img ?></button>
        <?php endif; ?>

        <?php /* Just the one action. Dismissing is still covered by the close
                 button above, Escape, and clicking the backdrop — so removing the
                 opt-out never traps anyone in the dialog. */ ?>
        <div class="mt-5 flex justify-center">
            <?= mws_estimate_button('primary', '', __('Request an Estimate', 'mws')) ?>
        </div>
    </div>
</div>
