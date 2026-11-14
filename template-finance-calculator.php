<?php
/**
 * Template Name: Finance Calculator
 *
 * Hosts the Hearth financing widget.
 *
 * The widget is a third-party script served from gethearth.com. Like the
 * Trustindex reviews widget it is a hosted service rather than a library —
 * there is no npm package, and it has to run from Hearth's own origin to reach
 * their rate data. It is loaded only on this page, not sitewide.
 *
 * @package MWS
 */

declare(strict_types=1);

get_header();

$ctx = mws_context();
$brand = $ctx['brand'];
$wrap = $ctx['wrap'];
$sec = $ctx['section'];
$h2 = $ctx['h2'];
$h3 = $ctx['h3'];

while (have_posts()):
    the_post();

    get_template_part('template-parts/banner', null, [
        'title' => (string) (get_the_title() ?: __('Finance calculator', 'mws')),
        'eyebrow' => (string) mws_field('banner_eyebrow', null, __('Financing available', 'mws')),
        'lead' => (string) mws_field('banner_lead', null, __('See what your project costs per month before you commit. Checking your options takes two minutes and does not affect your credit score.', 'mws')),
        'image' => (string) mws_field('banner_image', null, 'assets/img/banner-projects.jpg'),
        'crumb' => get_the_title(),
    ]);
    ?>

    <main>

        <?php /* ── The calculator ──────────────────────────────────────────── */ ?>
        <section class="<?= $sec ?> <?= mws_section_bg() ?>">
            <div class="<?= $wrap ?>">

                <div <?= mws_aos() ?> class="mx-auto mb-10 max-w-[64ch] text-center">
                    <?= mws_eyebrow(__('Work out the monthly cost', 'mws')) ?>
                    <h2 class="<?= $h2 ?> mb-5"><?php esc_html_e('What will it cost per month?', 'mws'); ?></h2>
                    <p class="text-body text-ink-muted">
                        <?php esc_html_e('Enter the amount you are budgeting and the calculator shows the monthly payment across the terms available to you. Pre-qualifying is a soft credit check, so it leaves no mark.', 'mws'); ?>
                    </p>
                </div>

                <?php the_content(); ?>

                <?php /* ── Hearth widget ───────────────────────────────────────
                        The script renders itself into this container. Kept in a
                        card so the widget's own styling sits on a deliberate
                        surface rather than floating on the page background. */ ?>
                <div <?= mws_aos() ?>
                    class="mx-auto max-w-[900px] rounded-tl-4xl rounded-br-4xl border border-line bg-white p-6 shadow-soft sm:p-8">
                    <div id="mws-hearth-widget" class="min-h-[320px]">
                        <script src="https://widget.gethearth.com/script.js" id="hearth-script" data-orgid="51337"
                            data-partner="gnt-landscaping-llc"></script>
                        <iframe id="hearth-widget_calculator_v1" class="mx-auto"></iframe>
                        <noscript>
                            <p class="text-body text-ink-muted">
                                <?php
                                printf(
                                    /* translators: %s: phone number link */
                                    esc_html__('The payment calculator needs JavaScript. Call us on %s and we will talk you through the options.', 'mws'),
                                    '<a class="font-semibold text-brand-600 underline" href="tel:' . esc_attr($ctx['tel']) . '">' . esc_html($brand['phone']) . '</a>'
                                );
                                ?>
                            </p>
                        </noscript>
                    </div>
                </div>

                <p class="mx-auto mt-6 max-w-[70ch] text-center text-micro text-ink-muted">
                    <?php esc_html_e('Financing is provided by Hearth’s lending partners, not by GNT Landscaping. Rates and terms depend on your credit and are set by the lender.', 'mws'); ?>
                </p>

            </div>
        </section>

        <?php
        /* How it works — the shared card grid, same component the other pages use. */
        get_template_part('template-parts/section-cards', null, [
            'eyebrow' => __('How it works', 'mws'),
            'heading' => __('Three steps, no obligation', 'mws'),
            'lead' => __('Pre-qualifying tells you what you can borrow before we price the work, so the proposal arrives against a budget you already know is realistic.', 'mws'),
            'items' => "calendar | Check your options | Two minutes, and a soft credit check that does not affect your score.\n"
                . "ruler | Get the estimate | We come out, measure up and put a fixed price against the work.\n"
                . "check | Choose a term | Pick the monthly payment that suits you and we schedule the build.",
        ]);

        /* Reassurance — the split component. */
        get_template_part('template-parts/section-split', null, [
            'eyebrow' => __('Good to know', 'mws'),
            'heading' => __("Spread the cost,\nnot the timeline", 'mws'),
            'text' => '<p>Financing does not change how we run the job. The work is still priced fixed against drawings, still scheduled the same way, and still carries the same two-year workmanship guarantee.</p><p>What it changes is when you pay for it — which is often the difference between doing the whole project now and doing half of it this year.</p>',
            'image' => 'assets/img/work-paver-install.jpg',
            'points' => "Soft credit check to pre-qualify — no mark on your score\n"
                . "Terms and rates set by the lender, shown up front\n"
                . "No prepayment penalty with Hearth's partners\n"
                . "The build is priced and guaranteed exactly as it would be otherwise",
            'flip' => true,
            'cta' => true,
        ]);
        ?>

    </main>

    <?php
    get_template_part('template-parts/estimate-band');

endwhile;

get_footer();
