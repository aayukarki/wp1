<?php
/**
 * Single service.
 *
 * @package MWS
 */

declare(strict_types=1);

get_header();

$ctx     = mws_context();
$wrap    = $ctx['wrap'];
$section = $ctx['section'];
$h2      = $ctx['h2'];
$h3      = $ctx['h3'];

while (have_posts()) :
    the_post();

    $summary  = (string) mws_field('service_summary', null, get_the_excerpt());
    $includes = mws_lines((string) mws_field('service_includes'));
    $faq      = mws_lines((string) mws_field('service_faq'));
    $photo    = (string) mws_field('service_photo', null, 'assets/img/work-planting.jpg');
    $banner   = (string) mws_field('banner_image', null, (string) (get_the_post_thumbnail_url(null, 'full') ?: 'assets/img/banner-services.jpg'));

    get_template_part('template-parts/banner', null, [
        'title'   => get_the_title(),
        'eyebrow' => __('Our services', 'mws'),
        'lead'    => $summary,
        'image'   => $banner,
        'crumb'   => get_the_title(),
    ]);
    ?>

    <main>

        <?php /* Body copy + what's included */ ?>
        <section class="<?= $section ?> <?= mws_section_bg() ?>">
            <div class="<?= $wrap ?> grid gap-12 lg:grid-cols-[1.15fr,1fr] lg:gap-20">

                <div <?= mws_aos() ?>>
                    <?= mws_eyebrow(__('The work', 'mws')) ?>
                    <h2 class="<?= $h2 ?> mb-6"><?php esc_html_e('What this covers', 'mws'); ?></h2>

                    <div class="<?= mws_prose() ?>">
                        <?php the_content(); ?>
                    </div>

                    <?php if ($includes !== []) : ?>
                        <ul class="mt-9 grid gap-5">
                            <?php foreach ($includes as $line) : ?>
                                <?php [$title, $detail] = mws_split_line($line); ?>
                                <li class="flex items-start gap-4">
                                    <span class="mt-0.5 grid h-8 w-8 flex-none place-items-center rounded-full bg-brand-50 text-brand-600">
                                        <?= mws_icon('check', 'h-4 w-4') ?>
                                    </span>
                                    <span class="text-body text-ink-muted">
                                        <strong class="block text-h4 text-ink"><?= mws_e($title) ?></strong>
                                        <?= mws_e($detail) ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div <?= mws_aos() ?> class="lg:sticky lg:top-[160px] lg:self-start">
                    <div <?= mws_media($photo, 'mb-8 aspect-[4/3] w-full rounded-tl-4xl rounded-br-4xl shadow-soft') ?>
                        role="img" aria-label="<?= mws_e(get_the_title()) ?>"></div>

                    <?php /* Ask, right where the visitor has just read what they get. */ ?>
                    <div class="rounded-tl-4xl rounded-br-4xl border border-line bg-sand p-7">
                        <h3 class="<?= $h3 ?> mb-2 text-ink"><?php esc_html_e('Want a price for this?', 'mws'); ?></h3>
                        <p class="mb-5 text-body text-ink-muted">
                            <?php esc_html_e('Free on-site estimate and a fixed-price proposal — usually back with you within one business day.', 'mws'); ?>
                        </p>
                        <?= mws_estimate_button('primary', 'w-full') ?>
                        <?= mws_trust_badges(false, 'mt-5') ?>
                    </div>
                </div>

            </div>
        </section>

        <?php
        /* Optional extra sections, built from the shared components the About
           page uses. Each renders only when its ACF group has content. */
        $sStats    = (array) mws_field('service_stats', null, []);
        $sCards    = (array) mws_field('service_cards', null, []);
        $sTimeline = (array) mws_field('service_timeline', null, []);
        $sSplit    = (array) mws_field('service_split', null, []);

        get_template_part('template-parts/section-stats', null, [
            'heading' => (string) ($sStats['heading'] ?? ''),
            'items'   => (string) ($sStats['items'] ?? ''),
        ]);

        get_template_part('template-parts/section-cards', null, [
            'eyebrow' => (string) ($sCards['eyebrow'] ?? ''),
            'heading' => (string) ($sCards['heading'] ?? ''),
            'lead'    => (string) ($sCards['lead'] ?? ''),
            'items'   => (string) ($sCards['items'] ?? ''),
        ]);

        get_template_part('template-parts/section-timeline', null, [
            'eyebrow' => (string) ($sTimeline['eyebrow'] ?? ''),
            'heading' => (string) ($sTimeline['heading'] ?? ''),
            'items'   => (string) ($sTimeline['items'] ?? ''),
        ]);

        get_template_part('template-parts/section-split', null, [
            'eyebrow' => (string) ($sSplit['eyebrow'] ?? ''),
            'heading' => (string) ($sSplit['heading'] ?? ''),
            'text'    => (string) ($sSplit['text'] ?? ''),
            'image'   => (string) ($sSplit['image'] ?? 'assets/img/work-paver-install.jpg'),
            'points'  => (string) ($sSplit['points'] ?? ''),
            'flip'    => true,
            'cta'     => true,
        ]);
        ?>

        <?php /* Related projects — proof this service has been delivered before. */ ?>
        <?php
        $related = get_posts([
            'post_type'      => 'mws_project',
            'posts_per_page' => 3,
            'meta_query'     => [[
                'key'     => 'project_services',
                'value'   => '"' . get_the_ID() . '"',
                'compare' => 'LIKE',
            ]],
        ]);
        ?>
        <?php if ($related !== []) : ?>
            <section class="<?= $section ?> <?= mws_section_bg() ?>">
                <div class="<?= $wrap ?>">
                    <div <?= mws_aos() ?> class="mb-11 flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                        <div>
                            <?= mws_eyebrow(__('Proof', 'mws')) ?>
                            <h2 class="<?= $h2 ?>"><?php esc_html_e('Projects using this service', 'mws'); ?></h2>
                        </div>
                        <a href="<?= esc_url(get_post_type_archive_link('mws_project')) ?>" class="<?= mws_btn('outline-dark', 'flex-none') ?>">
                            <?php esc_html_e('All projects', 'mws'); ?>
                        </a>
                    </div>

                    <ul class="grid gap-8 md:grid-cols-3">
                        <?php foreach ($related as $i => $project) : ?>
                            <li <?= mws_aos('fade-up', $i) ?>>
                                <?php mws_project_card($project); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
        <?php endif; ?>

        <?php /* FAQ */ ?>
        <?php if ($faq !== []) : ?>
            <section class="<?= $section ?> <?= mws_section_bg() ?>">
                <div class="<?= $wrap ?> mx-auto max-w-[860px]">
                    <div <?= mws_aos() ?> class="mb-10 text-center">
                        <?= mws_eyebrow(__('Good to know', 'mws')) ?>
                        <h2 class="<?= $h2 ?>"><?php esc_html_e('Common questions', 'mws'); ?></h2>
                    </div>

                    <div class="grid gap-4">
                        <?php foreach ($faq as $i => $line) : ?>
                            <?php [$question, $answer] = mws_split_line($line); ?>
                            <details <?= mws_aos('fade-up', $i) ?>
                                class="group rounded-tl-4xl rounded-br-4xl border border-line bg-white p-6 transition hover:border-brand-200 open:shadow-soft">
                                <summary class="flex cursor-pointer items-center justify-between gap-5 text-h4 text-ink [&::-webkit-details-marker]:hidden">
                                    <?= mws_e($question) ?>
                                    <?= mws_icon('chevron', 'h-5 w-5 flex-none text-brand-600 transition duration-300 group-open:rotate-180') ?>
                                </summary>
                                <p class="mt-4 text-body text-ink-muted"><?= mws_e($answer) ?></p>
                            </details>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

    </main>

    <?php
    get_template_part('template-parts/estimate-band');

endwhile;

get_footer();
