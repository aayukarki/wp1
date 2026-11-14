<?php
/**
 * Single journal post.
 *
 * Two columns from lg up: a table of contents that tracks the reader on the
 * left, the article on the right. The contents is derived from the post's own
 * h2s by mws_toc(), which also hands back the content with an id on each of
 * those headings — so it is that returned HTML, not the_content(), that gets
 * printed here; otherwise the links point at anchors nothing ever emitted.
 *
 * @package MWS
 */

declare(strict_types=1);

get_header();

$ctx     = mws_context();
$wrap    = $ctx['wrap'];
$section = $ctx['section'];

while (have_posts()) :
    the_post();

    get_template_part('template-parts/banner', null, [
        'title'   => get_the_title(),
        'eyebrow' => __('Journal', 'mws'),
        'image'   => (string) mws_field('banner_image', null,
            (string) (get_the_post_thumbnail_url(null, 'full') ?: 'assets/img/banner-journal.jpg')),
        'crumb'   => get_the_title(),
    ]);

    $toc     = mws_toc(apply_filters('the_content', get_the_content()));
    $hasToc  = count($toc['items']) > 1;

    /* Without a contents column the article keeps the centred single-column
       measure it had before, rather than sitting off to one side of an empty
       grid track. */
    $layout  = $hasToc
        ? 'grid gap-10 lg:grid-cols-[minmax(0,260px)_minmax(0,1fr)] lg:gap-14 xl:gap-16'
        : '';
    ?>

    <main class="<?= $section ?> <?= mws_section_bg() ?>">
        <div class="<?= $wrap ?>">
            <div class="<?= $layout ?>">

                <?php if ($hasToc) : ?>
                    <?php /* No self-start here: the column has to stretch to the row's
                             height or the sticky nav inside it has no travel and never
                             actually sticks. */ ?>
                    <aside>
                        <?php get_template_part('template-parts/post-toc', null, ['items' => $toc['items']]); ?>
                    </aside>
                <?php endif; ?>

                <article>

                    <p class="mb-8 flex items-center gap-3 text-small text-ink-muted">
                        <time datetime="<?= esc_attr(get_the_date('c')) ?>"><?php echo esc_html(get_the_date()); ?></time>
                        <?php $cats = get_the_category_list(', '); ?>
                        <?php if ($cats !== '') : ?>
                            <span aria-hidden="true">·</span>
                            <span class="[&_a]:font-semibold [&_a]:text-brand-600"><?php echo wp_kses_post($cats); ?></span>
                        <?php endif; ?>
                    </p>

                    <div class="<?= mws_prose() ?>">
                        <?php
                        /* Already through the_content's filters in the mws_toc() call above,
                           so this echoes raw exactly as the_content() would — escaping it a
                           second time would strip embeds and block markup. The str_replace
                           is the one other thing the_content() does after filtering: it
                           stops a stray CDATA close in the copy from ending the block. */
                        echo str_replace(']]>', ']]&gt;', $toc['content']); // phpcs:ignore WordPress.Security.EscapeOutput
                        ?>
                    </div>

                    <?php
                    wp_link_pages([
                        'before' => '<nav class="mt-8 flex gap-2 text-small">',
                        'after'  => '</nav>',
                    ]);
                    ?>
                </article>

            </div>
        </div>
    </main>

    <?php
    get_template_part('template-parts/estimate-band');

endwhile;

get_footer();
