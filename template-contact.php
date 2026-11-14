<?php
/**
 * Template Name: Contact / Request an Estimate
 *
 * The full-size version of the estimate request: the same Jobber slot the modal
 * uses, but inline and full width, with the contact details beside it.
 *
 * When Jobber is not yet configured this renders the built-in fallback form, so
 * the page is never a dead end.
 *
 * @package MWS
 */

declare(strict_types=1);

get_header();

$ctx     = mws_context();
$brand   = $ctx['brand'];
$tel     = $ctx['tel'];
$wrap    = $ctx['wrap'];
$section = $ctx['section'];
$h2      = $ctx['h2'];
$h3      = $ctx['h3'];

while (have_posts()) :
    the_post();

    get_template_part('template-parts/banner', null, [
        'title'   => (string) (get_the_title() ?: __('Request an estimate', 'mws')),
        'eyebrow' => (string) mws_field('banner_eyebrow', null, __('Free · No obligation', 'mws')),
        'lead'    => (string) mws_field('banner_lead', null, __('Tell us about the project and we will come out, measure up and put a fixed price against it.', 'mws')),
        'image'   => (string) mws_field('banner_image', null, 'assets/img/banner-contact.jpg'),
        'crumb'   => get_the_title(),
    ]);
    ?>

    <main class="<?= $section ?> <?= mws_section_bg() ?>">
        <div class="<?= $wrap ?> grid gap-12 lg:grid-cols-[1.4fr,1fr] lg:gap-16">

            <?php /* ── The form ─────────────────────────────────────────────── */ ?>
            <div <?= mws_aos() ?>>
                <?= mws_eyebrow(__('Step one', 'mws')) ?>
                <h2 class="<?= $h2 ?> mb-4"><?php esc_html_e('Tell us what you need', 'mws'); ?></h2>
                <p class="mb-8 max-w-[60ch] text-body text-ink-muted">
                    <?php esc_html_e('The more detail you give us up front, the more accurate the number we come back with. Photos help.', 'mws'); ?>
                </p>

                <?php the_content(); ?>

                <div class="rounded-tl-4xl rounded-br-4xl border border-line bg-white p-6 shadow-soft sm:p-8">
                    <?php if (mws_jobber_ready()) : ?>
                        <?php /* ── Jobber form slot ───────────────────────────────
                                 Configured in Appearance → Jobber & Trust. */ ?>
                        <iframe src="<?= esc_url(mws_setting('jobber_url')) ?>"
                            title="<?php esc_attr_e('Request an estimate', 'mws'); ?>"
                            loading="lazy"
                            style="height: <?= esc_attr((string) (int) mws_setting('jobber_height', 900)) ?>px"
                            class="w-full rounded-tl-2xl rounded-br-2xl border-0"></iframe>
                    <?php else : ?>
                        <?php mws_form(is_page('contact-us') ? 'contact' : 'estimate'); ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php /* ── Contact details ──────────────────────────────────────── */ ?>
            <aside <?= mws_aos() ?> class="lg:sticky lg:top-[160px] lg:self-start">

                <div class="rounded-tl-4xl rounded-br-4xl bg-dark p-8 text-white/75">
                    <h3 class="<?= $h3 ?> mb-6 !text-white"><?php esc_html_e('Or reach us directly', 'mws'); ?></h3>

                    <ul class="grid gap-6">
                        <li class="flex items-start gap-4">
                            <span class="grid h-11 w-11 flex-none place-items-center rounded-full border border-white/20 text-brand-400">
                                <?= mws_icon('phone', 'h-5 w-5') ?>
                            </span>
                            <span>
                                <strong class="mb-0.5 block text-micro uppercase text-white/60"><?php esc_html_e('Phone', 'mws'); ?></strong>
                                <a class="text-h4 font-semibold text-white transition hover:text-brand-200" href="tel:<?= mws_e($tel) ?>"><?= mws_e($brand['phone']) ?></a>
                            </span>
                        </li>

                        <li class="flex items-start gap-4">
                            <span class="grid h-11 w-11 flex-none place-items-center rounded-full border border-white/20 text-brand-400">
                                <?= mws_icon('mail', 'h-5 w-5') ?>
                            </span>
                            <span class="min-w-0">
                                <strong class="mb-0.5 block text-micro uppercase text-white/60"><?php esc_html_e('Email', 'mws'); ?></strong>
                                <a class="break-words font-semibold text-white transition hover:text-brand-200" href="mailto:<?= mws_e($brand['email']) ?>"><?= mws_e($brand['email']) ?></a>
                            </span>
                        </li>

                        <li class="flex items-start gap-4">
                            <span class="grid h-11 w-11 flex-none place-items-center rounded-full border border-white/20 text-brand-400">
                                <?= mws_icon('clock', 'h-5 w-5') ?>
                            </span>
                            <span>
                                <strong class="mb-0.5 block text-micro uppercase text-white/60"><?php esc_html_e('Hours', 'mws'); ?></strong>
                                <?= mws_e($brand['hours']) ?>
                            </span>
                        </li>

                        <li class="flex items-start gap-4">
                            <span class="grid h-11 w-11 flex-none place-items-center rounded-full border border-white/20 text-brand-400">
                                <?= mws_icon('pin', 'h-5 w-5') ?>
                            </span>
                            <span>
                                <strong class="mb-0.5 block text-micro uppercase text-white/60"><?php esc_html_e('Address', 'mws'); ?></strong>
                                <?= implode('<br>', array_map('mws_e', $brand['location'])) ?>
                            </span>
                        </li>
                    </ul>

                    <?= mws_trust_badges(true, 'mt-8') ?>
                </div>

                <div class="mt-6 rounded-tl-4xl rounded-br-4xl border border-line bg-sand p-7">
                    <h3 class="mb-3 text-h4 text-ink"><?php esc_html_e('What happens next', 'mws'); ?></h3>
                    <ol class="grid gap-3 text-body text-ink-muted">
                        <?php
                        $steps = [
                            __('We call you back within one business day.', 'mws'),
                            __('We visit, measure up and talk through options.', 'mws'),
                            __('You get a fixed-price proposal in writing.', 'mws'),
                        ];
                        ?>
                        <?php foreach ($steps as $i => $step) : ?>
                            <li class="flex items-start gap-3">
                                <span class="grid h-6 w-6 flex-none place-items-center rounded-full bg-brand-600 text-micro font-bold text-white"><?= (int) ($i + 1) ?></span>
                                <?= mws_e($step) ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>

            </aside>

        </div>
    </main>

<?php
endwhile;

get_footer();
