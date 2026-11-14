<?php
/**
 * Template Name: Careers
 *
 * Assembled from the same shared section components as About, services and
 * projects, plus an open-roles list and a slot for the application form.
 *
 * The form slot behaves like the estimate slot elsewhere: paste a Jobber (or
 * any other) application form URL into the Careers fields and it embeds;
 * otherwise the page falls back to a mailto link so an applicant is never
 * left without a way to apply.
 *
 * @package MWS
 */

declare(strict_types=1);

get_header();

$ctx   = mws_context();
$brand = $ctx['brand'];
$wrap  = $ctx['wrap'];
$sec   = $ctx['section'];
$h2    = $ctx['h2'];
$h3    = $ctx['h3'];

while (have_posts()) :
    the_post();

    get_template_part('template-parts/banner', null, [
        'title'   => (string) (get_the_title() ?: __('Careers', 'mws')),
        'eyebrow' => (string) mws_field('banner_eyebrow', null, __('Join the crew', 'mws')),
        'lead'    => (string) mws_field('banner_lead', null, __('We hire for attitude and train for skill. Year-round work, proper kit and a crew that finishes what it starts.', 'mws')),
        'image'   => (string) mws_field('banner_image', null, 'assets/img/work-mulch.jpg'),
        'crumb'   => get_the_title(),
    ]);

    $intro  = (array) mws_field('careers_intro', null, []);
    $stats  = (array) mws_field('careers_stats', null, []);
    $perks  = (array) mws_field('careers_perks', null, []);
    $steps  = (array) mws_field('careers_steps', null, []);
    $roles  = mws_lines((string) mws_field('careers_roles'));
    $apply  = (array) mws_field('careers_apply', null, []);
    ?>

    <main>

        <?php
        /* Why work here */
        get_template_part('template-parts/section-split', null, [
            'eyebrow' => (string) ($intro['eyebrow'] ?? ''),
            'heading' => (string) ($intro['heading'] ?? ''),
            'text'    => (string) ($intro['text'] ?? ''),
            'image'   => (string) ($intro['image'] ?? 'assets/img/work-paver-install.jpg'),
            'points'  => (string) ($intro['points'] ?? ''),
            'flip'    => true,
        ]);

        /* The numbers */
        get_template_part('template-parts/section-stats', null, [
            'heading' => (string) ($stats['heading'] ?? ''),
            'items'   => (string) ($stats['items'] ?? ''),
        ]);

        /* What you get */
        get_template_part('template-parts/section-cards', null, [
            'eyebrow' => (string) ($perks['eyebrow'] ?? ''),
            'heading' => (string) ($perks['heading'] ?? ''),
            'lead'    => (string) ($perks['lead'] ?? ''),
            'items'   => (string) ($perks['items'] ?? ''),
        ]);
        ?>

        <?php /* ── Open roles ──────────────────────────────────────────── */ ?>
        <?php if ($roles !== []) : ?>
            <section id="roles" class="<?= $sec ?> <?= mws_section_bg() ?>">
                <div class="<?= $wrap ?>">

                    <div <?= mws_aos() ?> class="mb-11 flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                        <div>
                            <?= mws_eyebrow(__('Open roles', 'mws')) ?>
                            <h2 class="<?= $h2 ?>"><?php esc_html_e('Who we are hiring', 'mws'); ?></h2>
                        </div>
                        <p class="text-small text-ink-muted">
                            <?php printf(esc_html__('%d positions open', 'mws'), count($roles)); ?>
                        </p>
                    </div>

                    <ul class="grid gap-4">
                        <?php foreach ($roles as $i => $line) : ?>
                            <?php [$title, $type, $detail] = mws_split_parts($line, 3); ?>
                            <li <?= mws_aos('fade-up', $i) ?>
                                class="group flex flex-col gap-5 rounded-tl-4xl rounded-br-4xl border border-line bg-white p-7 transition duration-400 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-lift md:flex-row md:items-center md:justify-between">
                                <div class="flex items-start gap-5">
                                    <span class="grid h-12 w-12 flex-none place-items-center rounded-full border border-brand-100 bg-brand-50 text-brand-600">
                                        <?= mws_icon('truck', 'h-6 w-6') ?>
                                    </span>
                                    <div>
                                        <h3 class="mb-1 text-h4 text-ink"><?= mws_e($title) ?></h3>
                                        <p class="text-body text-ink-muted"><?= mws_e($detail) ?></p>
                                    </div>
                                </div>

                                <div class="flex flex-none items-center gap-4 md:pl-6">
                                    <?php if ($type !== '') : ?>
                                        <span class="inline-flex items-center rounded-full bg-sand px-4 py-2 text-micro font-bold uppercase text-ink-soft">
                                            <?= mws_e($type) ?>
                                        </span>
                                    <?php endif; ?>
                                    <a href="#apply" class="<?= mws_btn('outline-dark') ?>">
                                        <?php esc_html_e('Apply', 'mws'); ?>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                </div>
            </section>
        <?php endif; ?>

        <?php
        /* How hiring runs */
        get_template_part('template-parts/section-timeline', null, [
            'eyebrow' => (string) ($steps['eyebrow'] ?? ''),
            'heading' => (string) ($steps['heading'] ?? ''),
            'items'   => (string) ($steps['items'] ?? ''),
        ]);
        ?>

        <?php /* ── Application form ────────────────────────────────────────
                 Same pattern as the estimate slot: an embedded form when a URL
                 is configured, a real fallback when it is not. */ ?>
        <section id="apply" class="<?= $sec ?> <?= mws_section_bg() ?>">
            <div class="<?= $wrap ?> mx-auto max-w-[820px]">

                <div <?= mws_aos() ?> class="mb-10 text-center">
                    <?= mws_eyebrow(__('Apply', 'mws')) ?>
                    <h2 class="<?= $h2 ?> mb-4"><?php esc_html_e('Send us your details', 'mws'); ?></h2>
                    <p class="mx-auto max-w-[58ch] text-body text-ink-muted">
                        <?php esc_html_e('No CV needed to start the conversation — tell us what you have worked on and we will call you back.', 'mws'); ?>
                    </p>
                </div>

                <div <?= mws_aos() ?> class="rounded-tl-4xl rounded-br-4xl border border-line bg-white p-6 shadow-soft sm:p-8">
                    <?php $applyUrl = trim((string) ($apply['url'] ?? '')); ?>

                    <?php if ($applyUrl !== '') : ?>
                        <iframe src="<?= esc_url($applyUrl) ?>"
                            title="<?php esc_attr_e('Job application form', 'mws'); ?>"
                            loading="lazy"
                            style="height: <?= esc_attr((string) max(400, (int) ($apply['height'] ?? 900))) ?>px"
                            class="w-full rounded-tl-2xl rounded-br-2xl border-0"></iframe>
                    <?php else : ?>
                        <?php mws_form('careers'); ?>
                    <?php endif; ?>
                </div>

            </div>
        </section>

    </main>

<?php
endwhile;

get_footer();
