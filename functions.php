<?php
/**
 * MWS theme — setup, asset loading and template wiring.
 *
 * All CSS and JS is compiled from src/ into dist/ by gulp (see gulpfile.js).
 * Nothing is loaded from a CDN: Tailwind is compiled, Swiper and AOS are
 * bundled out of node_modules, and both font families are self-hosted.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('MWS_VERSION', '1.0.0');

require get_template_directory() . '/inc/helpers.php';
require get_template_directory() . '/inc/context.php';
require get_template_directory() . '/inc/post-types.php';
require get_template_directory() . '/inc/settings.php';
require get_template_directory() . '/inc/estimate.php';
require get_template_directory() . '/inc/forms.php';
require get_template_directory() . '/inc/acf-fields.php';

/**
 * Theme supports.
 */
function mws_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('customize-selective-refresh-widgets');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);
    add_theme_support('responsive-embeds');

    /* The design's own palette, exposed to the block editor so any content
       added later can only reach for colours that belong to the brand. */
    add_theme_support('editor-color-palette', [
        ['name' => 'Brand',      'slug' => 'brand',      'color' => '#C8102E'],
        ['name' => 'Brand dark', 'slug' => 'brand-dark', 'color' => '#A00C24'],
        ['name' => 'Ink',        'slug' => 'ink',        'color' => '#17100F'],
        ['name' => 'Ink soft',   'slug' => 'ink-soft',   'color' => '#4A3E3C'],
        ['name' => 'Sand',       'slug' => 'sand',       'color' => '#FBF6F4'],
        ['name' => 'White',      'slug' => 'white',      'color' => '#FFFFFF'],
    ]);

    register_nav_menus([
        'primary' => __('Primary navigation', 'mws'),
    ]);
}
add_action('after_setup_theme', 'mws_setup');

/**
 * Front-end styles and scripts.
 *
 * Four files, vendor separated from application code so an edit to our own CSS
 * or JS does not invalidate the cached vendor bundle. `main` depends on
 * `vendor` in both cases, which is what guarantees Swiper and AOS have defined
 * their globals before main.js looks for them.
 *
 * Versions come from filemtime, so a rebuilt asset busts its own cache without
 * anyone remembering to bump a number.
 */
function mws_assets(): void
{
    $dir = get_template_directory();
    $uri = get_template_directory_uri();

    $version = static function (string $rel) use ($dir): string {
        $file = $dir . '/' . $rel;
        return is_file($file) ? (string) filemtime($file) : MWS_VERSION;
    };

    wp_enqueue_style('mws-vendor', $uri . '/dist/css/vendor.min.css', [], $version('dist/css/vendor.min.css'));
    wp_enqueue_style('mws-main', $uri . '/dist/css/main.min.css', ['mws-vendor'], $version('dist/css/main.min.css'));

    /* style.css itself carries only the theme header, so it is not enqueued —
       the compiled sheet above is the stylesheet. */

    wp_enqueue_script('mws-vendor', $uri . '/dist/js/vendor.min.js', [], $version('dist/js/vendor.min.js'), [
        'in_footer' => true,
        'strategy'  => 'defer',
    ]);

    wp_enqueue_script('mws-main', $uri . '/dist/js/main.min.js', ['mws-vendor'], $version('dist/js/main.min.js'), [
        'in_footer' => true,
        'strategy'  => 'defer',
    ]);

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'mws_assets');

/**
 * Preload the two font files that appear above the fold.
 *
 * Self-hosting removed the render-blocking Google Fonts request, but it also
 * removed the browser's early hint — without this the display face is only
 * discovered once the stylesheet has parsed, and the hero headline swaps in
 * late.
 */
function mws_preload_fonts(): void
{
    $fonts = [
        'dist/fonts/anton-latin-400-normal.woff2',
        'dist/fonts/corben-latin-400-normal.woff2',
    ];

    foreach ($fonts as $rel) {
        if (!is_file(get_template_directory() . '/' . $rel)) {
            continue;
        }

        printf(
            '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
            esc_url(get_template_directory_uri() . '/' . $rel)
        );
    }
}
add_action('wp_head', 'mws_preload_fonts', 1);

/**
 * The landing page is a single scrolling document with its own <h1>, so the
 * site title is only appended on other views.
 */
function mws_document_title(array $parts): array
{
    if (is_front_page()) {
        $brand = mws_config()['brand'];
        $parts['title'] = $brand['name'] . ' — Landscape & Hardscape Services in Butler & Allegheny County, PA';
        unset($parts['tagline']);
    }

    return $parts;
}
add_filter('document_title_parts', 'mws_document_title');

/**
 * Body classes used by the premium layer.
 */
function mws_body_class(array $classes): array
{
    if (is_front_page()) {
        $classes[] = 'mws-landing';
    }

    return $classes;
}
add_filter('body_class', 'mws_body_class');

/**
 * Drop WordPress's emoji polyfill.
 *
 * It injects an inline detection script plus a settings object that points at
 * s.w.org — the one request in the page that neither we nor the client control.
 * Every browser this theme targets renders emoji natively, and the site's copy
 * uses none, so the whole feature is removed rather than proxied.
 */
function mws_disable_emojis(): void
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

    add_filter('tiny_mce_plugins', static function ($plugins) {
        return is_array($plugins) ? array_diff($plugins, ['wpemoji']) : [];
    });

    /* Also drops the dns-prefetch hint to s.w.org that core adds for it. */
    add_filter('wp_resource_hints', static function (array $urls, string $relation): array {
        if ($relation === 'dns-prefetch') {
            $urls = array_filter($urls, static fn($url) => !str_contains((string) $url, 's.w.org'));
        }
        return $urls;
    }, 10, 2);
}
add_action('init', 'mws_disable_emojis');

/**
 * Strip the block library's default front-end CSS.
 *
 * The landing page uses none of it, and it otherwise ships ~30KB of styles that
 * fight the theme's own resets. Removed only on the front page, so ordinary
 * content pages keep working blocks.
 */
function mws_dequeue_block_styles(): void
{
    if (is_front_page()) {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('global-styles');
        wp_dequeue_style('classic-theme-styles');
    }
}
add_action('wp_enqueue_scripts', 'mws_dequeue_block_styles', 100);
