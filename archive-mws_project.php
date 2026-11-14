<?php
/**
 * Projects listing, including the project-type taxonomy archives.
 *
 * @package MWS
 */

declare(strict_types=1);

get_header();

$ctx = mws_context();
$wrap = $ctx['wrap'];
$section = $ctx['section'];
$h2 = $ctx['h2'];

$isTax = is_tax('mws_project_type');
$term = $isTax ? get_queried_object() : null;
$title = $isTax && $term instanceof WP_Term ? $term->name : __('Recent projects', 'mws');
$lead = $isTax && $term instanceof WP_Term && $term->description !== ''
    ? $term->description
    : __('Patios, walls, plantings and full backyard rebuilds across Butler and Allegheny counties.', 'mws');

get_template_part('template-parts/banner', null, [
    'title' => $title,
    'eyebrow' => __('Our work', 'mws'),
    'lead' => $lead,
    'image' => 'assets/img/banner-projects.jpg',
    'crumb' => $isTax ? $title : __('Projects', 'mws'),
]);

$types = get_terms(['taxonomy' => 'mws_project_type', 'hide_empty' => true]);
?>

<main class="<?= $section ?> <?= mws_section_bg() ?>">
    <div class="<?= $wrap ?>">

        <script defer async src='https://cdn.trustindex.io/loader-feed.js?543d4097948c92427c86a2561e0'></script>

        <!-- <?php if (is_array($types) && count($types) > 1): ?>
            <nav aria-label="<?php esc_attr_e('Filter projects', 'mws'); ?>" class="mb-11 flex flex-wrap justify-center gap-2.5">
                <?php
                $chip = 'inline-flex items-center rounded-full border px-5 py-2.5 text-micro font-bold '
                    . 'uppercase transition';
                ?>
                <a href="<?= esc_url(get_post_type_archive_link('mws_project')) ?>"
                    class="<?= $chip ?> <?= !$isTax ? 'border-brand-600 bg-brand-600 text-white' : 'border-line bg-white text-ink hover:border-brand-200 hover:bg-sand' ?>">
                    <?php esc_html_e('All', 'mws'); ?>
                </a>
                <?php foreach ($types as $type): ?>
                    <?php $current = $isTax && $term instanceof WP_Term && $term->term_id === $type->term_id; ?>
                    <a href="<?= esc_url(get_term_link($type)) ?>"
                        class="<?= $chip ?> <?= $current ? 'border-brand-600 bg-brand-600 text-white' : 'border-line bg-white text-ink hover:border-brand-200 hover:bg-sand' ?>">
                        <?= mws_e($type->name) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if (have_posts()): ?>

            <ul class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <?php $i = 0; ?>
                <?php while (have_posts()):
                    the_post(); ?>
                    <li <?= mws_aos('fade-up', $i++) ?>>
                        <?php mws_project_card(get_post()); ?>
                    </li>
                <?php endwhile; ?>
            </ul>

            <?php
            the_posts_pagination([
                'class' => 'mt-14 flex justify-center gap-2 text-small',
                'mid_size' => 2,
                'prev_text' => __('Previous', 'mws'),
                'next_text' => __('Next', 'mws'),
            ]);
            ?>

        <?php else: ?>
            <p class="text-center text-body text-ink-muted"><?php esc_html_e('Projects are being added shortly.', 'mws'); ?></p>
        <?php endif; ?> -->

    </div>
</main>

<?php
get_template_part('template-parts/estimate-band');
get_footer();
