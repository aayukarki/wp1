<?php
/**
 * Single project — the case study.
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

    $location  = (string) mws_field('project_location');
    $completed = (string) mws_field('project_completed');
    $duration  = (string) mws_field('project_duration');
    $scope     = mws_lines((string) mws_field('project_scope'));
    $before    = (string) mws_field('project_before');
    $after     = (string) mws_field('project_after');
    $quote     = (string) mws_field('project_quote');
    $author    = (string) mws_field('project_quote_author');
    $serviceIds = (array) mws_field('project_services', null, []);

    $banner = (string) mws_field('banner_image', null,
        (string) (get_the_post_thumbnail_url(null, 'full') ?: $after ?: 'assets/img/banner-projects.jpg'));

    get_template_part('template-parts/banner', null, [
        'title'   => get_the_title(),
        'eyebrow' => __('Project', 'mws'),
        'lead'    => (string) get_the_excerpt(),
        'image'   => $banner,
        'crumb'   => get_the_title(),
    ]);
    ?>

    <main>

        <?php /* Facts strip */ ?>
        <?php $facts = array_filter([
            __('Location', 'mws')  => $location,
            __('Completed', 'mws') => $completed,
            __('On site', 'mws')   => $duration,
        ]); ?>
        <?php if ($facts !== []) : ?>
            <section class="border-b border-line py-8 <?= mws_section_bg() ?>">
                <div class="<?= $wrap ?>">
                    <dl class="grid gap-6 sm:grid-cols-3">
                        <?php foreach ($facts as $label => $value) : ?>
                            <div>
                                <dt class="mb-1 text-eyebrow font-bold uppercase text-brand-600"><?= mws_e($label) ?></dt>
                                <dd class="text-h4 text-ink"><?= mws_e($value) ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                </div>
            </section>
        <?php endif; ?>

        <?php /* Story + scope */ ?>
        <section class="<?= $section ?> <?= mws_section_bg() ?>">
            <div class="<?= $wrap ?> grid gap-12 lg:grid-cols-[1.15fr,1fr] lg:gap-20">

                <div <?= mws_aos() ?>>
                    <?= mws_eyebrow(__('The brief', 'mws')) ?>
                    <h2 class="<?= $h2 ?> mb-6"><?php esc_html_e('What we did', 'mws'); ?></h2>
                    <div class="<?= mws_prose() ?>"><?php the_content(); ?></div>
                </div>

                <div <?= mws_aos() ?> class="lg:sticky lg:top-[160px] lg:self-start">
                    <?php if ($scope !== []) : ?>
                        <div class="mb-8 rounded-tl-4xl rounded-br-4xl border border-line bg-white p-7 shadow-soft">
                            <h3 class="<?= $h3 ?> mb-4 text-ink"><?php esc_html_e('Scope of work', 'mws'); ?></h3>
                            <ul class="grid gap-3">
                                <?php foreach ($scope as $item) : ?>
                                    <li class="flex items-start gap-3 text-body text-ink-muted">
                                        <span class="mt-1 grid h-5 w-5 flex-none place-items-center rounded-full bg-brand-50 text-brand-600">
                                            <?= mws_icon('check', 'h-3 w-3') ?>
                                        </span>
                                        <?= mws_e($item) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="rounded-tl-4xl rounded-br-4xl border border-line bg-sand p-7">
                        <h3 class="<?= $h3 ?> mb-2 text-ink"><?php esc_html_e('Want something like this?', 'mws'); ?></h3>
                        <p class="mb-5 text-body text-ink-muted">
                            <?php esc_html_e('Tell us about your yard and we will put a fixed price against it.', 'mws'); ?>
                        </p>
                        <?= mws_estimate_button('primary', 'w-full') ?>
                    </div>
                </div>

            </div>
        </section>

        <?php /* Services used — sits directly under "What we did", so the reader
                 goes straight from what was built to which services built it. */ ?>
        <?php $services = $serviceIds ? get_posts(['post_type' => 'mws_service', 'include' => $serviceIds, 'posts_per_page' => -1]) : []; ?>
        <?php if ($services !== []) : ?>
            <section class="<?= $section ?> <?= mws_section_bg() ?>">
                <div class="<?= $wrap ?>">
                    <div <?= mws_aos() ?> class="mb-8">
                        <?= mws_eyebrow(__('Services used', 'mws')) ?>
                        <h2 class="<?= $h2 ?>"><?php esc_html_e('What went into it', 'mws'); ?></h2>
                    </div>
                    <ul class="flex flex-wrap gap-3">
                        <?php foreach ($services as $service) : ?>
                            <li>
                                <a href="<?= esc_url(get_permalink($service)) ?>"
                                    class="inline-flex items-center gap-2 rounded-full border border-line bg-white px-5 py-2.5 text-micro font-bold uppercase text-ink transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-soft">
                                    <?= mws_icon((string) mws_field('service_icon', $service->ID, 'leaf'), 'h-4 w-4 text-brand-600') ?>
                                    <?= mws_e(get_the_title($service)) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
        <?php endif; ?>

        <?php
        /* Optional extra sections, from the same shared components the About
           page uses. Each renders only when its ACF group has content. */
        $pStats    = (array) mws_field('project_stats', null, []);
        $pCards    = (array) mws_field('project_cards', null, []);
        $pTimeline = (array) mws_field('project_timeline', null, []);
        $pSplit    = (array) mws_field('project_split', null, []);

        get_template_part('template-parts/section-stats', null, [
            'heading' => (string) ($pStats['heading'] ?? ''),
            'items'   => (string) ($pStats['items'] ?? ''),
        ]);

        get_template_part('template-parts/section-split', null, [
            'eyebrow' => (string) ($pSplit['eyebrow'] ?? ''),
            'heading' => (string) ($pSplit['heading'] ?? ''),
            'text'    => (string) ($pSplit['text'] ?? ''),
            'image'   => (string) ($pSplit['image'] ?? $after ?: 'assets/img/banner-projects.jpg'),
            'points'  => (string) ($pSplit['points'] ?? ''),
            'flip'    => true,
        ]);

        get_template_part('template-parts/section-timeline', null, [
            'eyebrow' => (string) ($pTimeline['eyebrow'] ?? ''),
            'heading' => (string) ($pTimeline['heading'] ?? ''),
            'items'   => (string) ($pTimeline['items'] ?? ''),
        ]);

        get_template_part('template-parts/section-cards', null, [
            'eyebrow' => (string) ($pCards['eyebrow'] ?? ''),
            'heading' => (string) ($pCards['heading'] ?? ''),
            'lead'    => (string) ($pCards['lead'] ?? ''),
            'items'   => (string) ($pCards['items'] ?? ''),
        ]);
        ?>

        <?php /* Gallery */ ?>
        <?php $gallery = mws_gallery('project_gallery'); ?>
        <?php if ($gallery !== []) : ?>
            <section class="<?= $section ?> <?= mws_section_bg() ?>">
                <div class="<?= $wrap ?>">
                    <div <?= mws_aos() ?> class="mb-10 flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                        <div>
                            <?= mws_eyebrow(__('Gallery', 'mws')) ?>
                            <h2 class="<?= $h2 ?>"><?php esc_html_e('The finished job', 'mws'); ?></h2>
                        </div>
                        <p class="text-small text-ink-muted">
                            <?php printf(esc_html__('%d photographs', 'mws'), count($gallery)); ?>
                        </p>
                    </div>

                    <?php /* First image runs full width on md+, the rest tile beneath it —
                             a plain equal grid makes every shot look equally important. */ ?>
                    <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <?php foreach ($gallery as $i => $src) : ?>
                            <li <?= mws_aos('fade-up', $i) ?> class="<?= $i === 0 ? 'sm:col-span-2 lg:col-span-2 lg:row-span-2' : '' ?>">
                                <a href="<?= esc_url($src) ?>" target="_blank" rel="noopener noreferrer"
                                    class="group block h-full overflow-hidden rounded-tl-4xl rounded-br-4xl">
                                    <div <?= mws_media($src, ($i === 0 ? 'aspect-[4/3] lg:h-full' : 'aspect-[4/3]') . ' w-full transition duration-600 group-hover:scale-[1.03]') ?>
                                        role="img" aria-label="<?= mws_e(sprintf(__('%1$s — photo %2$d', 'mws'), get_the_title(), $i + 1)) ?>"></div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
        <?php endif; ?>

        <?php /* Before & after */ ?>
        <?php if ($before !== '' && $after !== '') : ?>
            <section class="<?= $section ?> <?= mws_section_bg() ?>">
                <div class="<?= $wrap ?>">
                    <div <?= mws_aos() ?> class="mb-10 text-center">
                        <?= mws_eyebrow(__('Where it started', 'mws')) ?>
                        <h2 class="<?= $h2 ?>"><?php esc_html_e('Before and after', 'mws'); ?></h2>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2 sm:gap-6">
                        <?php foreach ([['before', $before, 'left-5'], ['after', $after, 'right-5']] as [$kind, $src, $side]) : ?>
                            <figure <?= mws_aos() ?> class="relative">
                                <div <?= mws_media($src, 'aspect-[4/3] w-full rounded-tl-4xl rounded-br-4xl') ?>
                                    role="img" aria-label="<?= mws_e(ucfirst($kind)) ?>"></div>
                                <figcaption class="absolute bottom-5 <?= $side ?> rounded-full bg-white px-5 py-2.5 text-micro font-bold uppercase text-ink shadow-soft">
                                    <?= mws_e(ucfirst($kind)) ?>
                                </figcaption>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php /* Client quote */ ?>
        <?php if ($quote !== '') : ?>
            <section class="<?= $section ?> <?= mws_section_bg() ?>">
                <div class="<?= $wrap ?>">
                    <figure <?= mws_aos() ?> class="mx-auto max-w-[860px] rounded-tl-4xl rounded-br-4xl border border-line bg-sand p-9 text-center md:p-12">
                        <span class="mx-auto mb-5 grid h-12 w-12 place-items-center rounded-full bg-brand-600 text-white">
                            <?= mws_icon('quote', 'h-6 w-6') ?>
                        </span>
                        <blockquote class="text-h3 text-ink">&ldquo;<?= mws_e($quote) ?>&rdquo;</blockquote>
                        <?php if ($author !== '') : ?>
                            <figcaption class="mt-5 text-small font-semibold text-ink-muted"><?= mws_e($author) ?></figcaption>
                        <?php endif; ?>
                    </figure>
                </div>
            </section>
        <?php endif; ?>

        <?php /* More projects */ ?>
        <?php
        $more = get_posts([
            'post_type'      => 'mws_project',
            'posts_per_page' => 3,
            'post__not_in'   => [get_the_ID()],
            'orderby'        => 'rand',
        ]);
        ?>
        <?php if ($more !== []) : ?>
            <section class="<?= $section ?> <?= mws_section_bg() ?>">
                <div class="<?= $wrap ?>">
                    <div <?= mws_aos() ?> class="mb-11 flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                        <h2 class="<?= $h2 ?>"><?php esc_html_e('More of our work', 'mws'); ?></h2>
                        <a href="<?= esc_url(get_post_type_archive_link('mws_project')) ?>" class="<?= mws_btn('outline-dark', 'flex-none') ?>">
                            <?php esc_html_e('All projects', 'mws'); ?>
                        </a>
                    </div>
                    <ul class="grid gap-8 md:grid-cols-3">
                        <?php foreach ($more as $i => $project) : ?>
                            <li <?= mws_aos('fade-up', $i) ?>><?php mws_project_card($project); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
        <?php endif; ?>

    </main>

    <?php
    get_template_part('template-parts/estimate-band');

endwhile;

get_footer();
