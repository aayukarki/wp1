<?php
/**
 * Fallback template — everything that is not the front page.
 *
 * The site is a single landing page, so this exists to keep WordPress happy
 * (index.php is a required template) and to give posts, archives, search and
 * 404s a presentable page in the same design rather than an unstyled one.
 *
 * @package MWS
 */

declare(strict_types=1);

get_header();

$ctx     = mws_context();
$wrap    = $ctx['wrap'];
$section = $ctx['section'];
$h2      = $ctx['h2'];

/* One banner for every fallback view, so posts, archives, search and 404 all
   carry the estimate call to action like the rest of the site. */
if (is_search()) {
    $bannerTitle = sprintf(__('Results for “%s”', 'mws'), get_search_query());
    $bannerEye   = __('Search', 'mws');
} elseif (is_404()) {
    $bannerTitle = __('Page not found', 'mws');
    $bannerEye   = __('404', 'mws');
} elseif (is_home()) {
    $bannerTitle = (string) (get_the_title(get_option('page_for_posts')) ?: __('Journal', 'mws'));
    $bannerEye   = __('News &amp; advice', 'mws');
} elseif (is_archive()) {
    $bannerTitle = wp_strip_all_tags(get_the_archive_title());
    $bannerEye   = __('Archive', 'mws');
} else {
    $bannerTitle = get_the_title();
    $bannerEye   = '';
}

get_template_part('template-parts/banner', null, [
    'title'   => $bannerTitle,
    'eyebrow' => $bannerEye,
    'image'   => 'assets/img/banner-journal.jpg',
    'crumb'   => $bannerTitle,
]);
?>

    <main class="<?= $section ?> <?= mws_section_bg() ?>">
        <div class="<?= $wrap ?>">

            <?php if (have_posts()) : ?>

                <?php if (is_singular()) : ?>

                    <?php while (have_posts()) : the_post(); ?>
                        <article <?php post_class('mws-prose'); ?>>
                            <header class="mb-8">
                                <h1 class="text-h2 text-ink mb-4"><?php the_title(); ?></h1>
                                <p class="text-small text-ink-muted">
                                    <time datetime="<?= esc_attr(get_the_date('c')) ?>"><?php echo esc_html(get_the_date()); ?></time>
                                </p>
                            </header>

                            <?php if (has_post_thumbnail()) : ?>
                                <div class="mb-9 overflow-hidden rounded-tl-4xl rounded-br-4xl shadow-soft">
                                    <?php the_post_thumbnail('large', ['class' => 'h-auto w-full']); ?>
                                </div>
                            <?php endif; ?>

                            <div class="text-body text-ink-muted [&_a]:font-semibold [&_a]:text-brand-600 [&_h2]:mb-4 [&_h2]:mt-10 [&_h2]:text-h3 [&_h2]:text-ink [&_h3]:mb-3 [&_h3]:mt-8 [&_h3]:text-h4 [&_h3]:text-ink [&_li]:mb-2 [&_ol]:mb-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mb-5 [&_ul]:mb-6 [&_ul]:list-disc [&_ul]:pl-6">
                                <?php the_content(); ?>
                            </div>
                        </article>
                    <?php endwhile; ?>

                <?php else : ?>

                    <ul class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                        <?php while (have_posts()) : the_post(); ?>
                            <li <?php post_class(); ?>>
                                <a href="<?php the_permalink(); ?>" class="group block">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="h-[200px] md:h-[250px] mb-5 overflow-hidden rounded-tl-4xl rounded-br-4xl">
                                            <?php the_post_thumbnail('medium_large', [
                                                'class' => 'h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]',
                                            ]); ?>
                                        </div>
                                    <?php endif; ?>
                                    <h2 class="mb-2 text-h3 text-ink transition group-hover:text-brand-600">
                                        <?php the_title(); ?>
                                    </h2>
                                    <p class="line-clamp-2 text-body text-ink-muted"><?php echo esc_html(mws_excerpt()); ?></p>
                                    <div class="mt-4 flex items-center justify-between border-t border-line pt-4 text-small text-ink-muted">
                                        <time datetime="<?= esc_attr(get_the_date('c')) ?>"><?php echo esc_html(get_the_date()); ?></time>
                                        <span class="inline-flex items-center gap-2 font-semibold text-brand-600">
                                            Read more
                                            <?= mws_icon('arrow', 'h-[18px] w-[18px] transition group-hover:translate-x-1') ?>
                                        </span>
                                    </div>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>

                    <?php
                    the_posts_pagination([
                        'class'     => 'mt-12 flex justify-center gap-2 text-small',
                        'mid_size'  => 2,
                        'prev_text' => 'Previous',
                        'next_text' => 'Next',
                    ]);
                    ?>

                <?php endif; ?>

            <?php else : ?>

                <div class="py-10 text-center">
                    <?= mws_eyebrow(is_404() ? 'Page not found' : 'Nothing here') ?>
                    <h1 class="<?= $h2 ?> mb-5">
                        <?= is_404() ? 'That page has moved or never existed' : 'Nothing found' ?>
                    </h1>
                    <p class="mx-auto mb-8 max-w-[52ch] text-body text-ink-muted">
                        <?= is_404()
                            ? 'The link may be out of date. Head back to the landing page to find what you need.'
                            : 'No posts matched. Try a different search.' ?>
                    </p>
                    <a href="<?= esc_url(home_url('/')) ?>" class="<?= mws_btn('primary') ?>">Back to home</a>
                </div>

            <?php endif; ?>

        </div>
    </main>

<?php
get_template_part('template-parts/estimate-band');
get_footer();

