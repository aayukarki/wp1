<?php
/**
 * Custom post types and taxonomies.
 *
 * Projects were the explicit requirement. Services are a post type too, because
 * the header's "Our services" dropdown needs a real page per service — hard
 * coding twelve near-identical page templates would be worse in every way, and
 * this lets the client add a thirteenth from the admin without a developer.
 *
 * Both are registered in code rather than through ACF's post type UI so they
 * travel with the theme and cannot be edited away by accident.
 *
 * @package MWS
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function mws_register_post_types(): void
{
    register_post_type('mws_project', [
        'labels' => [
            'name'               => __('Projects', 'mws'),
            'singular_name'      => __('Project', 'mws'),
            'add_new_item'       => __('Add New Project', 'mws'),
            'edit_item'          => __('Edit Project', 'mws'),
            'new_item'           => __('New Project', 'mws'),
            'view_item'          => __('View Project', 'mws'),
            'search_items'       => __('Search Projects', 'mws'),
            'not_found'          => __('No projects yet', 'mws'),
            'all_items'          => __('All Projects', 'mws'),
            'menu_name'          => __('Projects', 'mws'),
        ],
        'public'        => true,
        'has_archive'   => 'projects',
        'rewrite'       => ['slug' => 'projects', 'with_front' => false],
        'menu_icon'     => 'dashicons-images-alt2',
        'menu_position' => 20,
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'],
        'show_in_rest'  => true,
        'taxonomies'    => ['mws_project_type'],
    ]);

    register_post_type('mws_service', [
        'labels' => [
            'name'          => __('Services', 'mws'),
            'singular_name' => __('Service', 'mws'),
            'add_new_item'  => __('Add New Service', 'mws'),
            'edit_item'     => __('Edit Service', 'mws'),
            'all_items'     => __('All Services', 'mws'),
            'menu_name'     => __('Services', 'mws'),
        ],
        'public'        => true,
        'has_archive'   => 'services',
        'rewrite'       => ['slug' => 'services', 'with_front' => false],
        'menu_icon'     => 'dashicons-hammer',
        'menu_position' => 21,
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'],
        'show_in_rest'  => true,
    ]);

    register_taxonomy('mws_project_type', ['mws_project'], [
        'labels' => [
            'name'          => __('Project Types', 'mws'),
            'singular_name' => __('Project Type', 'mws'),
            'menu_name'     => __('Project Types', 'mws'),
        ],
        'public'            => true,
        'hierarchical'      => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'project-type', 'with_front' => false],
    ]);
}
add_action('init', 'mws_register_post_types');

/**
 * Services and projects are ordered by hand in the admin (menu_order), so the
 * archives have to respect that rather than falling back to newest-first.
 */
function mws_archive_order(WP_Query $query): void
{
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->is_post_type_archive(['mws_service', 'mws_project'])
        || $query->is_tax('mws_project_type')) {
        $query->set('orderby', ['menu_order' => 'ASC', 'date' => 'DESC']);
        $query->set('posts_per_page', 24);
    }
}
add_action('pre_get_posts', 'mws_archive_order');

/**
 * Flush rewrite rules once after the post types first appear.
 *
 * Without this the /services/ and /projects/ archives 404 until someone visits
 * Settings → Permalinks. Guarded by an option so it runs exactly once rather
 * than on every load, which would be expensive.
 */
function mws_maybe_flush_rewrites(): void
{
    if (get_option('mws_rewrites_flushed') === MWS_VERSION) {
        return;
    }

    flush_rewrite_rules(false);
    update_option('mws_rewrites_flushed', MWS_VERSION);
}
add_action('init', 'mws_maybe_flush_rewrites', 99);

/**
 * Project card.
 *
 * Used by the projects archive, the related-projects strip on a service, and
 * the "more projects" row on a single project — so the three cannot drift.
 */
function mws_project_card(WP_Post $project): void
{
    $location = (string) mws_field('project_location', $project->ID);
    $done     = (string) mws_field('project_completed', $project->ID);
    $meta     = implode(' · ', array_filter([$location, $done]));
    $thumb    = get_the_post_thumbnail_url($project, 'large')
              ?: (string) mws_field('project_after', $project->ID);
    ?>
    <a href="<?= esc_url(get_permalink($project)) ?>" class="group block">
        <div <?= mws_media($thumb, 'mb-5 aspect-[4/3] overflow-hidden rounded-tl-4xl rounded-br-4xl transition duration-400 group-hover:-translate-y-1 group-hover:shadow-lift') ?>
            role="img" aria-label="<?= mws_e(get_the_title($project)) ?>"></div>

        <h3 class="mb-1.5 text-h4 text-ink transition group-hover:text-brand-600">
            <?= mws_e(get_the_title($project)) ?>
        </h3>

        <?php if ($meta !== '') : ?>
            <p class="text-micro uppercase text-ink-muted"><?= mws_e($meta) ?></p>
        <?php endif; ?>
    </a>
    <?php
}

/**
 * All services, ordered as in the admin — used by the header dropdown, the
 * footer column and the estimate form's service select.
 *
 * @return WP_Post[]
 */
function mws_services(): array
{
    static $services = null;

    if ($services === null) {
        $services = get_posts([
            'post_type'      => 'mws_service',
            'posts_per_page' => -1,
            'orderby'        => ['menu_order' => 'ASC', 'title' => 'ASC'],
            'post_status'    => 'publish',
        ]);
    }

    return $services;
}

/**
 * The five services every location page leads with.
 *
 * Keyed by service slug, so one edit here moves a service between its own
 * section on the location pages and the secondary "everything else" grid —
 * the template, the ACF fields and the grid all read this same list.
 *
 * The label is what the location pages call the service, which is not always
 * the service post's own title: people search "sod installation", and the site
 * files that work under "Lawn & Sod Expert". The location pages use the words
 * searchers use and link through to the page that carries the detail.
 *
 * @return array<string,string> slug => label
 */
function mws_major_service_slugs(): array
{
    return [
        'landscape-design-and-installation' => __('Landscaping', 'mws'),
        'hardscapes'                        => __('Hardscaping', 'mws'),
        'landscape-lighting'                => __('Lights Installation', 'mws'),
        'lawn-and-sod'                      => __('SOD Installation', 'mws'),
        'snow-and-ice-management'           => __('Snow & Ice Removal', 'mws'),
    ];
}

/**
 * The five majors in order, each with the service post behind it.
 *
 * 'post' is null when a slug has no published service — a renamed or unpublished
 * service then costs the location pages their link rather than fatal-erroring,
 * and the section still renders from its own copy.
 *
 * @return array<int,array{slug:string,label:string,post:?WP_Post}>
 */
function mws_major_services(): array
{
    static $rows = null;

    if ($rows === null) {
        $bySlug = [];

        foreach (mws_services() as $service) {
            $bySlug[$service->post_name] = $service;
        }

        $rows = [];

        foreach (mws_major_service_slugs() as $slug => $label) {
            $rows[] = [
                'slug'  => $slug,
                'label' => $label,
                'post'  => $bySlug[$slug] ?? null,
            ];
        }
    }

    return $rows;
}

/**
 * Every service that is not one of the five majors.
 *
 * The location pages list these as a compact grid, so a town page still names
 * the full range without giving eleven services equal weight.
 *
 * @return WP_Post[]
 */
function mws_minor_services(): array
{
    $major = mws_major_service_slugs();

    return array_values(array_filter(
        mws_services(),
        static fn(WP_Post $service): bool => !isset($major[$service->post_name])
    ));
}
