<?php
/**
 * Services listing.
 *
 * @package MWS
 */

declare(strict_types=1);

get_header();

$ctx     = mws_context();
$wrap    = $ctx['wrap'];
$section = $ctx['section'];
$h2      = $ctx['h2'];

get_template_part('template-parts/banner', null, [
    'title'   => __('Landscape &amp; hardscape services', 'mws'),
    'eyebrow' => __('What we do', 'mws'),
    'lead'    => __('One crew, one schedule and one point of contact — from the first sketch through to the final walkthrough.', 'mws'),
    'image'   => 'assets/img/banner-services.jpg',
    'crumb'   => __('Services', 'mws'),
]);
?>

<main class="<?= $section ?> <?= mws_section_bg() ?>">
    <div class="<?= $wrap ?>">

        <?php if (have_posts()) : ?>

            <ul class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <?php $i = 0; ?>
                <?php while (have_posts()) : the_post(); ?>
                    <?php
                    $icon    = (string) mws_field('service_icon', null, 'leaf');
                    $summary = (string) mws_field('service_summary', null, get_the_excerpt());
                    ?>
                    <li <?= mws_aos('fade-up', $i++) ?> class="group flex">
                        <a href="<?php the_permalink(); ?>"
                            class="flex w-full flex-col overflow-hidden rounded-tl-4xl rounded-br-4xl border border-line bg-white transition duration-400 hover:-translate-y-1 hover:border-brand-200 hover:shadow-lift">

                            <?php if (has_post_thumbnail()) : ?>
                                <div class="aspect-[4/3] overflow-hidden">
                                    <?php the_post_thumbnail('large', [
                                        'class'   => 'h-full w-full object-cover transition duration-600 group-hover:scale-[1.04]',
                                        'loading' => 'lazy',
                                    ]); ?>
                                </div>
                            <?php else : ?>
                                <div <?= mws_media('', 'aspect-[4/3]') ?>></div>
                            <?php endif; ?>

                            <div class="flex flex-1 flex-col p-7">
                                <span class="mb-4 grid h-12 w-12 place-items-center rounded-full border border-brand-100 bg-brand-50 text-brand-600">
                                    <?= mws_icon($icon, 'h-6 w-6') ?>
                                </span>

                                <h2 class="mb-2 text-h4 text-ink transition group-hover:text-brand-600">
                                    <?php the_title(); ?>
                                </h2>

                                <p class="mb-5 text-body text-ink-muted"><?= mws_e($summary) ?></p>

                                <span class="mt-auto inline-flex items-center gap-2 text-btn font-extrabold uppercase text-brand-600">
                                    <?php esc_html_e('See details', 'mws'); ?>
                                    <?= mws_icon('arrow', 'h-[18px] w-[18px] transition group-hover:translate-x-1') ?>
                                </span>
                            </div>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>

        <?php else : ?>
            <p class="text-center text-body text-ink-muted"><?php esc_html_e('Services are being added shortly.', 'mws'); ?></p>
        <?php endif; ?>

    </div>
</main>

<?php
get_template_part('template-parts/estimate-band');
get_footer();
