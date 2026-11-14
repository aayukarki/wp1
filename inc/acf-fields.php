<?php
/**
 * ACF field groups, registered in code.
 *
 * Defined with acf_add_local_field_group() rather than through the admin UI so
 * the fields live in version control and deploy with the theme — a field group
 * created in the database on one machine does not exist on any other.
 *
 * IMPORTANT: this site runs ACF free (6.8.x). Repeater, Flexible Content,
 * Gallery, Clone and Options Pages are PRO-only and are NOT available. Where a
 * repeating list is genuinely needed the pattern here is a textarea parsed one
 * item per line — see mws_lines(). Theme-wide settings live on a native
 * WordPress settings screen instead: inc/settings.php.
 *
 * @package MWS
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Split a "one per line" textarea into a clean array.
 *
 * Blank lines are dropped so a stray return at the end of the box never renders
 * an empty list item.
 *
 * @return string[]
 */
function mws_lines(?string $value): array
{
    if (!is_string($value) || trim($value) === '') {
        return [];
    }

    $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];
    $lines = array_map('trim', $lines);

    return array_values(array_filter($lines, static fn(string $line): bool => $line !== ''));
}

/**
 * Split a "Label | detail" line into its two halves.
 *
 * @return array{0:string,1:string}
 */
function mws_split_line(string $line): array
{
    $parts = array_map('trim', explode('|', $line, 2));

    return [$parts[0] ?? '', $parts[1] ?? ''];
}

/**
 * Split a pipe-delimited line into exactly $count parts, padding with ''.
 *
 * Used by the About page's three-part lines (icon | title | text and
 * year | title | text), so a line missing its last field still renders.
 *
 * @return string[]
 */
function mws_split_parts(string $line, int $count): array
{
    $parts = array_map('trim', explode('|', $line, $count));

    return array_pad($parts, $count, '');
}

/**
 * The filled images from a group of numbered image sub-fields.
 *
 * ACF free has no Gallery field, so galleries are stored as image_1…image_n
 * inside a group. This flattens that back into a plain list.
 *
 * @return string[] URLs
 */
function mws_gallery(string $group, $post_id = null): array
{
    $value = mws_field($group, $post_id, []);

    if (!is_array($value)) {
        return [];
    }

    ksort($value);

    return array_values(array_filter(
        array_map(static fn($url): string => is_string($url) ? trim($url) : '', $value),
        static fn(string $url): bool => $url !== ''
    ));
}

/** A field value, safe when ACF is not active. */
function mws_field(string $name, $post_id = null, $default = '')
{
    if (!function_exists('get_field')) {
        return $default;
    }

    $value = get_field($name, $post_id);

    return ($value === null || $value === false || $value === '') ? $default : $value;
}

function mws_register_acf_fields(): void
{
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    /* ── Page banner ──────────────────────────────────────────────────────
       Applies to every page and to both post types, so any view can override
       the banner artwork and copy without touching a template. */
    acf_add_local_field_group([
        'key'    => 'group_mws_banner',
        'title'  => __('Banner', 'mws'),
        'fields' => [
            [
                'key'           => 'field_mws_banner_image',
                'label'         => __('Banner image', 'mws'),
                'name'          => 'banner_image',
                'type'          => 'image',
                'return_format' => 'url',
                'preview_size'  => 'medium',
                'instructions'  => __('Wide shot, at least 1600px across. Leave empty to use the default for this section.', 'mws'),
            ],
            [
                'key'          => 'field_mws_banner_eyebrow',
                'label'        => __('Eyebrow', 'mws'),
                'name'         => 'banner_eyebrow',
                'type'         => 'text',
                'instructions' => __('Small label above the heading.', 'mws'),
            ],
            [
                'key'          => 'field_mws_banner_lead',
                'label'        => __('Intro line', 'mws'),
                'name'         => 'banner_lead',
                'type'         => 'textarea',
                'rows'         => 2,
                'instructions' => __('One supporting sentence under the heading.', 'mws'),
            ],
        ],
        'location' => [
            [['param' => 'post_type', 'operator' => '==', 'value' => 'page']],
            [['param' => 'post_type', 'operator' => '==', 'value' => 'mws_service']],
            [['param' => 'post_type', 'operator' => '==', 'value' => 'mws_project']],
        ],
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'hide_on_screen'        => ['discussion', 'comments', 'trackbacks'],
        'active'                => true,
        'show_in_rest'          => false,
        'description'           => __('Artwork and copy for the banner at the top of this page.', 'mws'),
    ]);

    /* ── About page sections ──────────────────────────────────────────────
       Five fixed sections rather than flexible content, which is PRO-only. Each
       is a `group` so the admin screen stays organised, and every repeating list
       is a one-per-line textarea. Leave a section's heading empty to hide the
       whole section. */
    acf_add_local_field_group([
        'key'    => 'group_mws_about',
        'title'  => __('About page sections', 'mws'),
        'fields' => [

            /* 1 ── Story */
            [
                'key'        => 'field_mws_about_story',
                'label'      => __('1. Story', 'mws'),
                'name'       => 'about_story',
                'type'       => 'group',
                'layout'     => 'block',
                'sub_fields' => [
                    ['key' => 'field_mws_about_story_eyebrow', 'label' => __('Eyebrow', 'mws'), 'name' => 'eyebrow', 'type' => 'text'],
                    ['key' => 'field_mws_about_story_heading', 'label' => __('Heading', 'mws'), 'name' => 'heading', 'type' => 'text'],
                    ['key' => 'field_mws_about_story_text', 'label' => __('Body', 'mws'), 'name' => 'text', 'type' => 'wysiwyg', 'media_upload' => 0, 'toolbar' => 'basic'],
                    ['key' => 'field_mws_about_story_image', 'label' => __('Image', 'mws'), 'name' => 'image', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'medium'],
                ],
            ],

            /* 2 ── Stats */
            [
                'key'        => 'field_mws_about_stats',
                'label'      => __('2. Numbers band', 'mws'),
                'name'       => 'about_stats',
                'type'       => 'group',
                'layout'     => 'block',
                'sub_fields' => [
                    ['key' => 'field_mws_about_stats_heading', 'label' => __('Heading', 'mws'), 'name' => 'heading', 'type' => 'text'],
                    [
                        'key'          => 'field_mws_about_stats_items',
                        'label'        => __('Numbers', 'mws'),
                        'name'         => 'items',
                        'type'         => 'textarea',
                        'rows'         => 5,
                        'instructions' => __('One per line. Format: <code>Value | Label</code> — e.g. <code>2009 | Building here since</code>.', 'mws'),
                    ],
                ],
            ],

            /* 3 ── Values */
            [
                'key'        => 'field_mws_about_values',
                'label'      => __('3. How we work', 'mws'),
                'name'       => 'about_values',
                'type'       => 'group',
                'layout'     => 'block',
                'sub_fields' => [
                    ['key' => 'field_mws_about_values_eyebrow', 'label' => __('Eyebrow', 'mws'), 'name' => 'eyebrow', 'type' => 'text'],
                    ['key' => 'field_mws_about_values_heading', 'label' => __('Heading', 'mws'), 'name' => 'heading', 'type' => 'text'],
                    ['key' => 'field_mws_about_values_lead', 'label' => __('Intro', 'mws'), 'name' => 'lead', 'type' => 'textarea', 'rows' => 2],
                    [
                        'key'          => 'field_mws_about_values_items',
                        'label'        => __('Points', 'mws'),
                        'name'         => 'items',
                        'type'         => 'textarea',
                        'rows'         => 8,
                        'instructions' => __('One per line. Format: <code>icon | Title | Description</code>. Icon names: leaf, shield, check, ruler, truck, calendar, clock, heart, star, sun, quote, pin.', 'mws'),
                    ],
                ],
            ],

            /* 4 ── Milestones */
            [
                'key'        => 'field_mws_about_timeline',
                'label'      => __('4. Milestones', 'mws'),
                'name'       => 'about_timeline',
                'type'       => 'group',
                'layout'     => 'block',
                'sub_fields' => [
                    ['key' => 'field_mws_about_timeline_eyebrow', 'label' => __('Eyebrow', 'mws'), 'name' => 'eyebrow', 'type' => 'text'],
                    ['key' => 'field_mws_about_timeline_heading', 'label' => __('Heading', 'mws'), 'name' => 'heading', 'type' => 'text'],
                    [
                        'key'          => 'field_mws_about_timeline_items',
                        'label'        => __('Milestones', 'mws'),
                        'name'         => 'items',
                        'type'         => 'textarea',
                        'rows'         => 8,
                        'instructions' => __('One per line. Format: <code>Year | Title | Description</code>.', 'mws'),
                    ],
                ],
            ],

            /* 5 ── Crew */
            [
                'key'        => 'field_mws_about_crew',
                'label'      => __('5. The crew', 'mws'),
                'name'       => 'about_crew',
                'type'       => 'group',
                'layout'     => 'block',
                'sub_fields' => [
                    ['key' => 'field_mws_about_crew_eyebrow', 'label' => __('Eyebrow', 'mws'), 'name' => 'eyebrow', 'type' => 'text'],
                    ['key' => 'field_mws_about_crew_heading', 'label' => __('Heading', 'mws'), 'name' => 'heading', 'type' => 'text'],
                    ['key' => 'field_mws_about_crew_text', 'label' => __('Body', 'mws'), 'name' => 'text', 'type' => 'wysiwyg', 'media_upload' => 0, 'toolbar' => 'basic'],
                    ['key' => 'field_mws_about_crew_image', 'label' => __('Image', 'mws'), 'name' => 'image', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'medium'],
                    [
                        'key'          => 'field_mws_about_crew_points',
                        'label'        => __('Checklist', 'mws'),
                        'name'         => 'points',
                        'type'         => 'textarea',
                        'rows'         => 6,
                        'instructions' => __('One per line. Plain text — rendered as a ticked list.', 'mws'),
                    ],
                ],
            ],
        ],
        'location' => [[
            ['param' => 'page_template', 'operator' => '==', 'value' => 'template-about.php'],
        ]],
        'menu_order'   => 1,
        'active'       => true,
        'show_in_rest' => false,
        'description'  => __('The About page is built from these sections rather than the editor.', 'mws'),
    ]);

    /* ── Extra sections, shared shape ─────────────────────────────────────
       Services and projects get the same four building blocks the About page
       uses — numbers band, card grid, timeline, split. Same field names, same
       one-per-line formats, so an editor who has filled in one page already
       knows how the next one works. */

    $sectionGroups = static function (string $prefix, array $labels): array {
        return [
            [
                'key'        => "field_mws_{$prefix}_stats",
                'label'      => $labels['stats'],
                'name'       => "{$prefix}_stats",
                'type'       => 'group',
                'layout'     => 'block',
                'sub_fields' => [
                    ['key' => "field_mws_{$prefix}_stats_heading", 'label' => __('Heading', 'mws'), 'name' => 'heading', 'type' => 'text'],
                    [
                        'key'          => "field_mws_{$prefix}_stats_items",
                        'label'        => __('Numbers', 'mws'),
                        'name'         => 'items',
                        'type'         => 'textarea',
                        'rows'         => 5,
                        'instructions' => __('One per line. Format: <code>Value | Label</code>.', 'mws'),
                    ],
                ],
            ],
            [
                'key'        => "field_mws_{$prefix}_cards",
                'label'      => $labels['cards'],
                'name'       => "{$prefix}_cards",
                'type'       => 'group',
                'layout'     => 'block',
                'sub_fields' => [
                    ['key' => "field_mws_{$prefix}_cards_eyebrow", 'label' => __('Eyebrow', 'mws'), 'name' => 'eyebrow', 'type' => 'text'],
                    ['key' => "field_mws_{$prefix}_cards_heading", 'label' => __('Heading', 'mws'), 'name' => 'heading', 'type' => 'text'],
                    ['key' => "field_mws_{$prefix}_cards_lead", 'label' => __('Intro', 'mws'), 'name' => 'lead', 'type' => 'textarea', 'rows' => 2],
                    [
                        'key'          => "field_mws_{$prefix}_cards_items",
                        'label'        => __('Cards', 'mws'),
                        'name'         => 'items',
                        'type'         => 'textarea',
                        'rows'         => 8,
                        'instructions' => __('One per line. Format: <code>icon | Title | Description</code>. Icons: leaf, shield, check, ruler, truck, calendar, clock, heart, star, sun, quote, pin.', 'mws'),
                    ],
                ],
            ],
            [
                'key'        => "field_mws_{$prefix}_timeline",
                'label'      => $labels['timeline'],
                'name'       => "{$prefix}_timeline",
                'type'       => 'group',
                'layout'     => 'block',
                'sub_fields' => [
                    ['key' => "field_mws_{$prefix}_timeline_eyebrow", 'label' => __('Eyebrow', 'mws'), 'name' => 'eyebrow', 'type' => 'text'],
                    ['key' => "field_mws_{$prefix}_timeline_heading", 'label' => __('Heading', 'mws'), 'name' => 'heading', 'type' => 'text'],
                    [
                        'key'          => "field_mws_{$prefix}_timeline_items",
                        'label'        => __('Steps', 'mws'),
                        'name'         => 'items',
                        'type'         => 'textarea',
                        'rows'         => 8,
                        'instructions' => __('One per line. Format: <code>Label | Title | Description</code>. The label is the step number, phase or year.', 'mws'),
                    ],
                ],
            ],
            [
                'key'        => "field_mws_{$prefix}_split",
                'label'      => $labels['split'],
                'name'       => "{$prefix}_split",
                'type'       => 'group',
                'layout'     => 'block',
                'sub_fields' => [
                    ['key' => "field_mws_{$prefix}_split_eyebrow", 'label' => __('Eyebrow', 'mws'), 'name' => 'eyebrow', 'type' => 'text'],
                    ['key' => "field_mws_{$prefix}_split_heading", 'label' => __('Heading', 'mws'), 'name' => 'heading', 'type' => 'text'],
                    ['key' => "field_mws_{$prefix}_split_text", 'label' => __('Body', 'mws'), 'name' => 'text', 'type' => 'wysiwyg', 'media_upload' => 0, 'toolbar' => 'basic'],
                    ['key' => "field_mws_{$prefix}_split_image", 'label' => __('Image', 'mws'), 'name' => 'image', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'medium'],
                    [
                        'key'          => "field_mws_{$prefix}_split_points",
                        'label'        => __('Checklist', 'mws'),
                        'name'         => 'points',
                        'type'         => 'textarea',
                        'rows'         => 6,
                        'instructions' => __('One per line. Plain text.', 'mws'),
                    ],
                ],
            ],
        ];
    };

    acf_add_local_field_group([
        'key'      => 'group_mws_service_sections',
        'title'    => __('Service page sections', 'mws'),
        'fields'   => $sectionGroups('service', [
            'stats'    => __('A. Numbers band', 'mws'),
            'cards'    => __('B. Why us', 'mws'),
            'timeline' => __('C. How the job runs', 'mws'),
            'split'    => __('D. Our guarantee', 'mws'),
        ]),
        'location'     => [[['param' => 'post_type', 'operator' => '==', 'value' => 'mws_service']]],
        'menu_order'   => 3,
        'active'       => true,
        'show_in_rest' => false,
        'description'  => __('Optional extra sections. Leave a group empty to hide that section.', 'mws'),
    ]);

    acf_add_local_field_group([
        'key'      => 'group_mws_project_sections',
        'title'    => __('Project page sections', 'mws'),
        'fields'   => $sectionGroups('project', [
            'stats'    => __('A. Numbers band', 'mws'),
            'cards'    => __('B. Details', 'mws'),
            'timeline' => __('C. How it was built', 'mws'),
            'split'    => __('D. The brief', 'mws'),
        ]),
        'location'     => [[['param' => 'post_type', 'operator' => '==', 'value' => 'mws_project']]],
        'menu_order'   => 3,
        'active'       => true,
        'show_in_rest' => false,
        'description'  => __('Optional extra sections. Leave a group empty to hide that section.', 'mws'),
    ]);

    /* ── Careers page ─────────────────────────────────────────────────────
       Same four components again, plus the roles list and the application
       form slot. */
    $careers = $sectionGroups('careers', [
        'stats'    => __('B. Numbers band', 'mws'),
        'cards'    => __('C. What you get', 'mws'),
        'timeline' => __('E. How hiring runs', 'mws'),
        'split'    => __('A. Why work here', 'mws'),
    ]);

    /* Rename the split group so it reads as the intro it is on this page. */
    foreach ($careers as $i => $group) {
        if ($group['name'] === 'careers_split') {
            $careers[$i]['key']  = 'field_mws_careers_intro';
            $careers[$i]['name'] = 'careers_intro';
            foreach ($careers[$i]['sub_fields'] as $j => $sub) {
                $careers[$i]['sub_fields'][$j]['key'] = str_replace('careers_split', 'careers_intro', $sub['key']);
            }
        }
        if ($group['name'] === 'careers_cards') {
            $careers[$i]['key']  = 'field_mws_careers_perks';
            $careers[$i]['name'] = 'careers_perks';
        }
        if ($group['name'] === 'careers_timeline') {
            $careers[$i]['key']  = 'field_mws_careers_steps';
            $careers[$i]['name'] = 'careers_steps';
        }
    }

    $careers[] = [
        'key'          => 'field_mws_careers_roles',
        'label'        => __('D. Open roles', 'mws'),
        'name'         => 'careers_roles',
        'type'         => 'textarea',
        'rows'         => 8,
        'instructions' => __('One per line. Format: <code>Job title | Type | Short description</code> — e.g. <code>Hardscape Crew Lead | Full time · Year round | Running two-man crews on patios and walls.</code>', 'mws'),
    ];

    $careers[] = [
        'key'        => 'field_mws_careers_apply',
        'label'      => __('F. Application form', 'mws'),
        'name'       => 'careers_apply',
        'type'       => 'group',
        'layout'     => 'block',
        'sub_fields' => [
            [
                'key'          => 'field_mws_careers_apply_url',
                'label'        => __('Form URL', 'mws'),
                'name'         => 'url',
                'type'         => 'url',
                'instructions' => __('Embedded in an iframe. Leave empty to show the email fallback instead.', 'mws'),
            ],
            [
                'key'           => 'field_mws_careers_apply_height',
                'label'         => __('Embed height (px)', 'mws'),
                'name'          => 'height',
                'type'          => 'number',
                'default_value' => 900,
                'min'           => 400,
                'max'           => 2000,
            ],
        ],
    ];

    acf_add_local_field_group([
        'key'          => 'group_mws_careers',
        'title'        => __('Careers page sections', 'mws'),
        'fields'       => $careers,
        'location'     => [[['param' => 'page_template', 'operator' => '==', 'value' => 'template-careers.php']]],
        'menu_order'   => 1,
        'active'       => true,
        'show_in_rest' => false,
    ]);

    /* ── Project gallery ──────────────────────────────────────────────────
       ACF free has no Gallery field, so this is six discrete image fields.
       Whatever is filled renders; the rest are ignored. */
    acf_add_local_field_group([
        'key'    => 'group_mws_project_gallery',
        'title'  => __('Project gallery', 'mws'),
        'fields' => [[
            'key'          => 'field_mws_project_gallery',
            'label'        => __('Gallery', 'mws'),
            'name'         => 'project_gallery',
            'type'         => 'group',
            'layout'       => 'block',
            'instructions' => __('Add at least three. Empty slots are skipped.', 'mws'),
            'sub_fields'   => array_map(
                static fn(int $i): array => [
                    'key'           => 'field_mws_project_gallery_' . $i,
                    'label'         => sprintf(__('Image %d', 'mws'), $i),
                    'name'          => 'image_' . $i,
                    'type'          => 'image',
                    'return_format' => 'url',
                    'preview_size'  => 'medium',
                    'wrapper'       => ['width' => '33'],
                ],
                range(1, 6)
            ),
        ]],
        'location'     => [[['param' => 'post_type', 'operator' => '==', 'value' => 'mws_project']]],
        'menu_order'   => 2,
        'active'       => true,
        'show_in_rest' => false,
    ]);

    /* ── Service ──────────────────────────────────────────────────────── */
    acf_add_local_field_group([
        'key'    => 'group_mws_service',
        'title'  => __('Service details', 'mws'),
        'fields' => [
            [
                'key'          => 'field_mws_service_summary',
                'label'        => __('Card summary', 'mws'),
                'name'         => 'service_summary',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => __('One or two sentences. Used on the services carousel and the services listing.', 'mws'),
            ],
            [
                'key'           => 'field_mws_service_icon',
                'label'         => __('Icon', 'mws'),
                'name'          => 'service_icon',
                'type'          => 'select',
                'choices'       => mws_icon_choices(),
                'default_value' => 'leaf',
                'ui'            => 1,
            ],
            [
                'key'          => 'field_mws_service_includes',
                'label'        => __('What is included', 'mws'),
                'name'         => 'service_includes',
                'type'         => 'textarea',
                'rows'         => 7,
                'instructions' => __('One per line. Format: <code>Heading | short description</code>. The description is optional.', 'mws'),
            ],
            [
                'key'          => 'field_mws_service_faq',
                'label'        => __('Common questions', 'mws'),
                'name'         => 'service_faq',
                'type'         => 'textarea',
                'rows'         => 7,
                'instructions' => __('One per line. Format: <code>Question | answer</code>.', 'mws'),
            ],
            [
                'key'           => 'field_mws_service_gallery',
                'label'         => __('Supporting photo', 'mws'),
                'name'          => 'service_photo',
                'type'          => 'image',
                'return_format' => 'url',
                'preview_size'  => 'medium',
                'instructions'  => __('Shown beside "What is included". A photo of the work itself reads better than a finished-garden shot here.', 'mws'),
            ],
        ],
        'location'       => [[['param' => 'post_type', 'operator' => '==', 'value' => 'mws_service']]],
        'menu_order'     => 1,
        'active'         => true,
        'show_in_rest'   => false,
    ]);

    /* ── Project ──────────────────────────────────────────────────────── */
    acf_add_local_field_group([
        'key'    => 'group_mws_project',
        'title'  => __('Project details', 'mws'),
        'fields' => [
            [
                'key'          => 'field_mws_project_location',
                'label'        => __('Location', 'mws'),
                'name'         => 'project_location',
                'type'         => 'text',
                'placeholder'  => 'Cranberry Township, PA',
            ],
            [
                'key'          => 'field_mws_project_completed',
                'label'        => __('Completed', 'mws'),
                'name'         => 'project_completed',
                'type'         => 'date_picker',
                'display_format' => 'F Y',
                'return_format'  => 'F Y',
            ],
            [
                'key'          => 'field_mws_project_duration',
                'label'        => __('Time on site', 'mws'),
                'name'         => 'project_duration',
                'type'         => 'text',
                'placeholder'  => '3 weeks',
            ],
            [
                'key'          => 'field_mws_project_scope',
                'label'        => __('Scope of work', 'mws'),
                'name'         => 'project_scope',
                'type'         => 'textarea',
                'rows'         => 6,
                'instructions' => __('One item per line.', 'mws'),
            ],
            [
                'key'           => 'field_mws_project_services',
                'label'         => __('Services used', 'mws'),
                'name'          => 'project_services',
                'type'          => 'relationship',
                'post_type'     => ['mws_service'],
                'filters'       => ['search'],
                'return_format' => 'id',
                'instructions'  => __('Links this project to the service pages it belongs to.', 'mws'),
            ],
            [
                'key'           => 'field_mws_project_before',
                'label'         => __('Before photo', 'mws'),
                'name'          => 'project_before',
                'type'          => 'image',
                'return_format' => 'url',
                'preview_size'  => 'medium',
            ],
            [
                'key'           => 'field_mws_project_after',
                'label'         => __('After photo', 'mws'),
                'name'          => 'project_after',
                'type'          => 'image',
                'return_format' => 'url',
                'preview_size'  => 'medium',
            ],
            [
                'key'          => 'field_mws_project_quote',
                'label'        => __('Client quote', 'mws'),
                'name'         => 'project_quote',
                'type'         => 'textarea',
                'rows'         => 3,
            ],
            [
                'key'          => 'field_mws_project_quote_author',
                'label'        => __('Quote attributed to', 'mws'),
                'name'         => 'project_quote_author',
                'type'         => 'text',
            ],
        ],
        'location'     => [[['param' => 'post_type', 'operator' => '==', 'value' => 'mws_project']]],
        'menu_order'   => 1,
        'active'       => true,
        'show_in_rest' => false,
    ]);

    /* -- Location pages -------------------------------------------------
       Every field here is per-page on purpose. A township page that recycles
       another township's copy is duplicate content with the place name swapped,
       which is the failure mode these pages exist to avoid - so nothing is
       defaulted from config, and an empty section simply does not render.

       The five service blocks are generated from mws_major_service_slugs(), so
       the admin screen, the template and the "everything else" grid can never
       disagree about which five are the majors. */
    $locationServices = [];
    $index            = 0;

    foreach (mws_major_service_slugs() as $slug => $label) {
        $index++;

        $locationServices[] = [
            'key'          => 'field_mws_location_svc_' . $index,
            'label'        => sprintf('%d. %s', $index, $label),
            'name'         => 'location_svc_' . $index,
            'type'         => 'group',
            'layout'       => 'block',
            'instructions' => sprintf(
                /* translators: %s: service name */
                __('Leave the heading empty to skip this section on this page. Links through to the "%s" service page.', 'mws'),
                $label
            ),
            'sub_fields'   => [
                [
                    'key'          => 'field_mws_location_svc_' . $index . '_heading',
                    'label'        => __('Heading', 'mws'),
                    'name'         => 'heading',
                    'type'         => 'text',
                    'instructions' => __('Name the place. "Paver patios built for Jackson Township clay" beats "Hardscaping".', 'mws'),
                ],
                [
                    'key'          => 'field_mws_location_svc_' . $index . '_text',
                    'label'        => __('Copy', 'mws'),
                    'name'         => 'text',
                    'type'         => 'wysiwyg',
                    'tabs'         => 'visual',
                    'toolbar'      => 'basic',
                    'media_upload' => 0,
                    'instructions' => __('Two short paragraphs on how this service plays out on properties here specifically.', 'mws'),
                ],
                [
                    'key'          => 'field_mws_location_svc_' . $index . '_points',
                    'label'        => __('Ticked points', 'mws'),
                    'name'         => 'points',
                    'type'         => 'textarea',
                    'rows'         => 4,
                    'instructions' => __('One per line. Three or four is plenty.', 'mws'),
                ],
                [
                    'key'           => 'field_mws_location_svc_' . $index . '_image',
                    'label'         => __('Photo', 'mws'),
                    'name'          => 'image',
                    'type'          => 'image',
                    'return_format' => 'url',
                    'preview_size'  => 'medium',
                    'instructions'  => __('Ideally work done in or near this area.', 'mws'),
                ],
            ],
        ];
    }

    acf_add_local_field_group([
        'key'    => 'group_mws_location',
        'title'  => __('Location page', 'mws'),
        'fields' => array_merge(
            [
                /* 1 - What this place is.
                   Kept as discrete fields rather than parsed out of the page
                   title: the template needs "Township" and "Butler County" as
                   values it can print and compare, and guessing them back out
                   of "Landscaping in Jackson Township" is how that breaks. */
                [
                    'key'          => 'field_mws_location_name',
                    'label'        => __('Place name', 'mws'),
                    'name'         => 'location_name',
                    'type'         => 'text',
                    'placeholder'  => 'Jackson Township',
                    'instructions' => __('Just the place, no service words. Used in headings and to find nearby projects.', 'mws'),
                    'wrapper'      => ['width' => '40'],
                ],
                [
                    'key'           => 'field_mws_location_type',
                    'label'         => __('Type', 'mws'),
                    'name'          => 'location_type',
                    'type'          => 'select',
                    'choices'       => [
                        'Township'  => __('Township', 'mws'),
                        'Borough'   => __('Borough', 'mws'),
                        'City'      => __('City', 'mws'),
                        'Community' => __('Community', 'mws'),
                        'County'    => __('County', 'mws'),
                    ],
                    'default_value' => 'Township',
                    'wrapper'       => ['width' => '30'],
                ],
                [
                    'key'          => 'field_mws_location_county',
                    'label'        => __('County', 'mws'),
                    'name'         => 'location_county',
                    'type'         => 'text',
                    'placeholder'  => 'Butler County',
                    'instructions' => __('Leave empty on a county page.', 'mws'),
                    'wrapper'      => ['width' => '30'],
                ],
                [
                    'key'          => 'field_mws_location_map',
                    'label'        => __('Map embed URL', 'mws'),
                    'name'         => 'location_map',
                    'type'         => 'url',
                    'instructions' => __('Optional. Google Maps &rarr; Share &rarr; Embed a map, then paste just the src URL. Leave empty and the map is built from the place name and county above.', 'mws'),
                ],

                /* 2 - Why this place is different */
                [
                    'key'          => 'field_mws_location_ground',
                    'label'        => __('Local conditions', 'mws'),
                    'name'         => 'location_ground',
                    'type'         => 'group',
                    'layout'       => 'block',
                    'instructions' => __('What the ground, the lots or the housing stock here demand. Empty heading hides it.', 'mws'),
                    'sub_fields'   => [
                        [
                            'key'           => 'field_mws_location_ground_eyebrow',
                            'label'         => __('Eyebrow', 'mws'),
                            'name'          => 'eyebrow',
                            'type'          => 'text',
                            'default_value' => 'On the ground',
                            'wrapper'       => ['width' => '40'],
                        ],
                        [
                            'key'         => 'field_mws_location_ground_heading',
                            'label'       => __('Heading', 'mws'),
                            'name'        => 'heading',
                            'type'        => 'text',
                            'placeholder' => 'What building here actually involves',
                            'wrapper'     => ['width' => '60'],
                        ],
                        [
                            'key'          => 'field_mws_location_ground_items',
                            'label'        => __('Points', 'mws'),
                            'name'         => 'items',
                            'type'         => 'textarea',
                            'rows'         => 6,
                            'instructions' => __('One per line, "Label | detail". Example: Heavy clay | Holds water, so drainage comes before planting', 'mws'),
                        ],
                    ],
                ],
            ],

            /* 3 - The five headline services */
            $locationServices,

            [
                /* 4 - Everything else, plus the local wrap-up */
                [
                    'key'           => 'field_mws_location_other_heading',
                    'label'         => __('Other services heading', 'mws'),
                    'name'          => 'location_other_heading',
                    'type'          => 'text',
                    'default_value' => 'Everything else we do here',
                    'instructions'  => __('Heading for the grid of remaining services. Empty hides the grid.', 'mws'),
                ],
                [
                    'key'          => 'field_mws_location_faq',
                    'label'        => __('Local questions', 'mws'),
                    'name'         => 'location_faq',
                    'type'         => 'textarea',
                    'rows'         => 8,
                    'instructions' => __('One per line, "Question | Answer". Answer them for this place rather than in general - that is what makes the page worth indexing.', 'mws'),
                ],
                [
                    'key'          => 'field_mws_location_nearby',
                    'label'        => __('Nearby areas', 'mws'),
                    'name'         => 'location_nearby',
                    'type'         => 'textarea',
                    'rows'         => 6,
                    'instructions' => __('One per line, "Name | /areas-we-service/slug/". Links the location pages to each other.', 'mws'),
                ],
            ]
        ),
        'location'        => [[['param' => 'page_template', 'operator' => '==', 'value' => 'template-location.php']]],
        'menu_order'      => 1,
        'position'        => 'normal',
        'label_placement' => 'top',
        'hide_on_screen'  => ['discussion', 'comments', 'trackbacks'],
        'active'          => true,
        'show_in_rest'    => false,
        'description'     => __('Content for one town or county landing page.', 'mws'),
    ]);
}
add_action('acf/init', 'mws_register_acf_fields');

/** Icon names as an ACF select list. */
function mws_icon_choices(): array
{
    $choices = [];

    foreach (mws_icon_names() as $name) {
        $choices[$name] = ucwords(str_replace('-', ' ', $name));
    }

    return $choices;
}
