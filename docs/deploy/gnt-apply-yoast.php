<?php
/**
 * Apply Yoast SEO titles and meta descriptions from a slug-keyed JSON map.
 *
 *   wp eval-file gnt-apply-yoast.php gnt-yoast-existing.json
 *
 * Everything is resolved by slug. Post IDs are deliberately never used: they
 * differ between the local build and production, and keying on them would write
 * meta onto whatever unrelated post happened to hold that ID.
 *
 * Safe to re-run. Reports anything it could not find rather than failing quietly.
 */

if (!defined('WP_CLI') || !WP_CLI) { exit('Run via wp eval-file.'); }

$file = $args[0] ?? '';
if (!is_readable($file)) { WP_CLI::error('Cannot read ' . $file); }

$map = json_decode(file_get_contents($file), true);
if (!is_array($map)) { WP_CLI::error('Bad JSON: ' . json_last_error_msg()); }

$hub   = get_page_by_path('areas-we-service');
$hubId = $hub ? $hub->ID : 0;

/** Find one post by slug: location page first, then page, service, project. */
function gnt_resolve(string $slug, int $hubId): ?WP_Post {
    if ($hubId) {
        $child = get_posts([
            'post_type' => 'page', 'name' => $slug, 'post_parent' => $hubId,
            'posts_per_page' => 1, 'post_status' => 'publish',
        ]);
        if ($child) { return $child[0]; }
    }

    foreach (['page', 'mws_service', 'mws_project'] as $type) {
        $hit = get_posts([
            'post_type' => $type, 'name' => $slug,
            'posts_per_page' => 1, 'post_status' => 'publish',
        ]);
        if ($hit) { return $hit[0]; }
    }

    return null;
}

$done = 0; $missing = [];

foreach ($map as $slug => $v) {
    $post = gnt_resolve($slug, $hubId);

    if (!$post) { $missing[] = $slug; continue; }

    update_post_meta($post->ID, '_yoast_wpseo_title', $v['title']);
    update_post_meta($post->ID, '_yoast_wpseo_metadesc', $v['desc']);
    $done++;
}

/* Utility pages must never rank: a thank-you page in search results means
   people landing on it without submitting, which corrupts the conversion
   numbers it exists to record. */
foreach (['thank-you' => 'Thank You | GNT Landscaping',
          'privacy-policy' => 'Privacy Policy | GNT Landscaping'] as $slug => $title) {
    $p = get_page_by_path($slug);
    if (!$p) { continue; }
    update_post_meta($p->ID, '_yoast_wpseo_title', $title);
    update_post_meta($p->ID, '_yoast_wpseo_meta-robots-noindex', '1');
    $done++;
}

WP_CLI::success(sprintf('%d posts updated.%s', $done,
    $missing ? ' NOT FOUND: ' . implode(', ', $missing) : ''));
