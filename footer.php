<?php
/**
 * Site footer and document close.
 *
 * @package MWS
 */

declare(strict_types=1);

$ctx = mws_context();
$config = $ctx['config'];
$brand = $ctx['brand'];
$tel = $ctx['tel'];
$year = $ctx['year'];
$wrap = $ctx['wrap'];
$h2 = $ctx['h2'];
?>

<!-- ══ Footer ═══════════════════════════════════════════════════════ -->
<footer class="relative overflow-hidden bg-dark pb-7 pt-14 text-white/70 md:pt-20">
    <div class="<?= $wrap ?> relative z-10">

        <div
            class="mb-12 flex flex-col items-start justify-between gap-8 border-b border-white/10 pb-12 md:flex-row md:items-end">
            <h2 class="<?= $h2 ?> !text-white"><?= mws_heading($config['footer']['cta']['title']) ?></h2>
            <div class="flex flex-none flex-wrap items-center gap-3">
                <a href="<?= mws_e($config['footer']['cta']['primary']['href']) ?>" class="<?= mws_btn('primary') ?>">
                    <?= mws_e($config['footer']['cta']['primary']['label']) ?>
                </a>
                <a href="<?= mws_e($config['footer']['cta']['ghost']['href'] ?: 'tel:' . $tel) ?>"
                    class="<?= mws_btn('outline') ?>">
                    <?= mws_e($config['footer']['cta']['ghost']['label']) ?>
                </a>

                <?php /* Socials with no URL yet are skipped rather than rendered as
href="#" — a dead icon is worse than no icon. Add the real
profile URLs in inc/config.php and they reappear. */ ?>
                <ul class="ml-1 flex gap-2.5">
                    <?php foreach ($config['footer']['socials'] as $social): ?>
                        <?php if (trim((string) $social['href']) === '' || $social['href'] === '#')
                            continue; ?>
                        <li>
                            <a href="<?= mws_e($social['href']) ?>" aria-label="<?= mws_e($social['label']) ?>"
                                class="grid h-11 w-11 place-items-center rounded-full border border-white/20 transition hover:border-brand-600 hover:bg-brand-600 hover:text-white">
                                <?= mws_icon($social['icon'], 'h-5 w-5') ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <?php /* A defined grid rather than flex-wrap + justify-between: with
four groups of very different widths that left a lopsided row
and pushed the contact block onto a line of its own. Services
gets the extra span for its long labels. */ ?>
        <div class="grid gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-12">

            <?php $spanFor = ['Services' => 'lg:col-span-5', 'Quick links' => 'lg:col-span-2', 'Work with us' => 'lg:col-span-2']; ?>
            <?php foreach ($config['footer']['columns'] as $title => $links): ?>
                <nav aria-label="<?= mws_e($title) ?>" class="<?= mws_e($spanFor[$title] ?? 'lg:col-span-2') ?>">
                    <h3 class="mb-6 text-h3 text-white"><?= mws_e($title) ?></h3>
                    <?php /* The services list is long, so it runs in two columns from sm up. */ ?>
                    <ul class="<?= $title === 'Services' ? 'sm:columns-2 sm:gap-x-6' : '' ?>">
                        <?php foreach ($links as $link): ?>
                            <li class="mb-3 break-inside-avoid">
                                <a href="<?= mws_e($link['href']) ?>" class="text-small transition hover:text-white">
                                    <?= mws_e($link['label']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            <?php endforeach; ?>

            <!-- Contact details and location -->
            <div class="lg:col-span-3">
                <h3 class="mb-5 flex items-center gap-2.5 text-h3 text-white">
                    <?= mws_icon('mail', 'h-6 w-6 flex-none text-brand-400') ?>Contact us
                </h3>
                <ul class="grid gap-2 text-small">
                    <li>Email:
                        <a class="font-semibold text-white transition hover:text-brand-200"
                            href="mailto:<?= mws_e($brand['email']) ?>"><?= mws_e($brand['email']) ?></a>
                    </li>
                    <li>Phone:
                        <a class="font-semibold text-white transition hover:text-brand-200"
                            href="tel:<?= mws_e($tel) ?>"><?= mws_e($brand['phone']) ?></a>
                    </li>
                </ul>

                <h3 class="mb-5 mt-9 flex items-center gap-2.5 text-h3 text-white">
                    <?= mws_icon('pin', 'h-6 w-6 flex-none text-brand-400') ?>Our location
                </h3>
                <p class="text-small mb-4">
                    <a href="https://maps.app.goo.gl/xwvkkig6uk4VZVq7A" target="_blank" class="hover:text-brand-200"><?= implode(
                        '<br>',
                        array_map('mws_e', $brand['location'])
                    ) ?></a>
                </p>
                <div class="rounded-2xl overflow-hidden">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3023.080761643875!2d-80.13783629999999!3d40.73824839999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x88347d31f76e88fd%3A0x74f36c393c275597!2sGNT!5e0!3m2!1sen!2sau!4v1789178699739!5m2!1sen!2sau"
                        width="100%" height="150" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"></iframe>
                </div>
            </div>

        </div>

        <?php /* Ask an AI about us — under the menu columns, above the
                 copyright row. */ ?>
        <?php get_template_part('template-parts/ask-ai'); ?>

        <?php /* The wordmark is decorative and belongs behind everything. It
used to come after the copyright row in the same stacking
context, so it painted straight over that text. z-0 here plus
z-10 on the row below keeps it where it belongs, and it is a
size smaller now that the type scale grew. */ ?>
        <p aria-hidden="true"
            class="pointer-events-none absolute inset-x-6 -bottom-[0.46em] z-0 select-none overflow-hidden whitespace-nowrap text-[clamp(3.5rem,13vw,8rem)] font-extrabold leading-[0.8] text-white/[0.035]">
            <?= mws_e($brand['name']) ?>
        </p>

        <div
            class="relative z-10 mt-14 flex flex-col gap-5 border-t border-white/10 pt-6 text-small sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; <?= mws_e($year) ?> <?= mws_e($brand['name']) ?>. All rights reserved.</p>
            <p>Powered By: <a href="https://magneticwebsolution.com/" target="_blank"
                    class="font-semibold text-white transition hover:text-brand-200">MWS</a></p>
        </div>

    </div>
</footer>

<a href="#top" data-to-top aria-label="Back to top"
    class="invisible fixed bottom-6 right-6 z-40 grid h-11 w-11 translate-y-3 place-items-center rounded-tl-2xl rounded-br-2xl bg-brand-600 text-white opacity-0 shadow-soft transition duration-300 hover:bg-brand-700">
    <?= mws_icon('arrow', 'h-6 w-6 -rotate-90') ?>
</a>

<?php
/* The estimate modal — one per page, opened by every banner button and by
   any remaining #booking link. */
mws_estimate_modal();

/* The promotion pop-up, which opens itself shortly after load. */
get_template_part('template-parts/promo-modal');
?>

<?php wp_footer(); ?>
</body>

</html>