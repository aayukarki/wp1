<?php
/**
 * Template Name: Areas We Service
 *
 * The footprint is written out below rather than read from the header
 * navigation. The nav dropdown only has room for a handful of towns, while this
 * page is the one page that has to carry the whole service area — driving both
 * off the same short list meant the page could only ever list what fitted in a
 * menu. The two are now independent: adding a town here does not add it to the
 * nav, and vice versa.
 *
 * @package MWS
 */

declare(strict_types=1);

get_header();

$ctx     = mws_context();
$brand   = $ctx['brand'];
$wrap    = $ctx['wrap'];
$section = $ctx['section'];
$h2      = $ctx['h2'];
$h3      = $ctx['h3'];

/**
 * The service area, grouped by what each place actually is.
 *
 * The three tiers are kept apart because they are not interchangeable:
 * Zelienople and Mars are boroughs, Wexford and Gibsonia are unincorporated
 * communities sitting inside other townships, and Butler and Pittsburgh are
 * cities. Filing any of them under "township" is the kind of error a local
 * reader catches immediately — and locals are exactly who reads this page.
 *
 * 'note' carries the parent county, so a visitor scanning for their own
 * township can tell Marion Township, Beaver County from the Butler County one
 * of the same name.
 */
$area_groups = [
    [
        'eyebrow' => __('Counties', 'mws'),
        'title'   => __('Three counties, six days a week', 'mws'),
        'items'   => [
            ['name' => __('Butler County', 'mws'),    'note' => __('Home county — based in Cranberry Twp', 'mws')],
            ['name' => __('Allegheny County', 'mws'), 'note' => __('North Hills & Pittsburgh suburbs', 'mws')],
            ['name' => __('Beaver County', 'mws'),    'note' => __('Eastern Beaver County', 'mws')],
        ],
    ],
    [
        'eyebrow' => __('Townships', 'mws'),
        'title'   => __('Townships we work in', 'mws'),
        'items'   => [
            ['name' => __('Cranberry Township', 'mws'),      'note' => __('Butler County', 'mws')],
            ['name' => __('Adams Township', 'mws'),          'note' => __('Butler County', 'mws')],
            ['name' => __('Jackson Township', 'mws'),        'note' => __('Butler County', 'mws')],
            ['name' => __('Forward Township', 'mws'),        'note' => __('Butler County', 'mws')],
            ['name' => __('Penn Township', 'mws'),           'note' => __('Butler County', 'mws')],
            ['name' => __('Middlesex Township', 'mws'),      'note' => __('Butler County', 'mws')],
            ['name' => __('Buffalo Township', 'mws'),        'note' => __('Butler County', 'mws')],
            ['name' => __('Clinton Township', 'mws'),        'note' => __('Butler County', 'mws')],
            ['name' => __('Connoquenessing Township', 'mws'), 'note' => __('Butler County', 'mws')],
            ['name' => __('Butler Township', 'mws'),         'note' => __('Butler County', 'mws')],
            ['name' => __('Pine Township', 'mws'),           'note' => __('Allegheny County', 'mws')],
            ['name' => __('Marshall Township', 'mws'),       'note' => __('Allegheny County', 'mws')],
            ['name' => __('McCandless Township', 'mws'),     'note' => __('Allegheny County', 'mws')],
            ['name' => __('Richland Township', 'mws'),       'note' => __('Allegheny County', 'mws')],
            ['name' => __('Hampton Township', 'mws'),        'note' => __('Allegheny County', 'mws')],
            ['name' => __('Ohio Township', 'mws'),           'note' => __('Allegheny County', 'mws')],
            ['name' => __('Indiana Township', 'mws'),        'note' => __('Allegheny County', 'mws')],
            ['name' => __('Daugherty Township', 'mws'),      'note' => __('Beaver County', 'mws')],
            ['name' => __('Marion Township', 'mws'),         'note' => __('Beaver County', 'mws')],
            ['name' => __('New Sewickley Township', 'mws'),  'note' => __('Beaver County', 'mws')],
            ['name' => __('North Sewickley Township', 'mws'), 'note' => __('Beaver County', 'mws')],
        ],
    ],
    [
        'eyebrow' => __('Boroughs & communities', 'mws'),
        'title'   => __('Boroughs, towns and communities', 'mws'),
        'items'   => [
            ['name' => __('Zelienople', 'mws'),     'note' => __('Borough · Butler County', 'mws')],
            ['name' => __('Harmony', 'mws'),        'note' => __('Borough · Butler County', 'mws')],
            ['name' => __('Mars', 'mws'),           'note' => __('Borough · Butler County', 'mws')],
            ['name' => __('Evans City', 'mws'),     'note' => __('Borough · Butler County', 'mws')],
            ['name' => __('Seven Fields', 'mws'),   'note' => __('Borough · Butler County', 'mws')],
            ['name' => __('Saxonburg', 'mws'),      'note' => __('Borough · Butler County', 'mws')],
            ['name' => __('Callery', 'mws'),        'note' => __('Borough · Butler County', 'mws')],
            ['name' => __('Valencia', 'mws'),       'note' => __('Borough · Butler County', 'mws')],
            ['name' => __('Butler', 'mws'),         'note' => __('City · Butler County', 'mws')],
            ['name' => __('Wexford', 'mws'),        'note' => __('Community · Allegheny County', 'mws')],
            ['name' => __('Gibsonia', 'mws'),       'note' => __('Community · Allegheny County', 'mws')],
            ['name' => __('Sewickley', 'mws'),      'note' => __('Borough · Allegheny County', 'mws')],
            ['name' => __('Fox Chapel', 'mws'),     'note' => __('Borough · Allegheny County', 'mws')],
            ['name' => __('Franklin Park', 'mws'),  'note' => __('Borough · Allegheny County', 'mws')],
            ['name' => __('Bradford Woods', 'mws'), 'note' => __('Borough · Allegheny County', 'mws')],
            ['name' => __('Pittsburgh', 'mws'),     'note' => __('City · Allegheny County', 'mws')],
            ['name' => __('New Brighton', 'mws'),   'note' => __('Borough · Beaver County', 'mws')],
            ['name' => __('Beaver', 'mws'),         'note' => __('Borough · Beaver County', 'mws')],
            ['name' => __('Economy', 'mws'),        'note' => __('Borough · Beaver County', 'mws')],
        ],
    ],
];

/**
 * Published location pages, keyed by slug.
 *
 * Built in one query rather than a get_page_by_path() per card: there are over
 * forty cards and this page renders every one of them on every load.
 *
 * A card becomes a link only when a child page exists for it, so the hub
 * upgrades itself as location pages are published and there is no second list
 * to keep in sync with the one above.
 */
$location_pages = [];
$areas_hub      = get_page_by_path('areas-we-service');

if ($areas_hub) {
    foreach (get_posts([
        'post_type'      => 'page',
        'post_parent'    => $areas_hub->ID,
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order',
    ]) as $location_page) {
        $location_pages[$location_page->post_name] = (string) get_permalink($location_page);
    }
}

while (have_posts()) :
    the_post();

    get_template_part('template-parts/banner', null, [
        'title'   => (string) (get_the_title() ?: __('Areas we service', 'mws')),
        'eyebrow' => (string) mws_field('banner_eyebrow', null, __('Where we work', 'mws')),
        'lead'    => (string) mws_field('banner_lead', null, __('Crews out of Cranberry Township covering Butler, Allegheny and Beaver counties six days a week.', 'mws')),
        'image'   => (string) mws_field('banner_image', null, 'assets/img/banner-areas.jpg'),
        'crumb'   => get_the_title(),
    ]);
    ?>

    <main>

        <section class="<?= $section ?> <?= mws_section_bg() ?>">
            <div class="<?= $wrap ?>">

                <?php if (get_the_content() !== '') : ?>
                    <div <?= mws_aos() ?> class="mx-auto mb-14 max-w-[820px] <?= mws_prose() ?>">
                        <?php the_content(); ?>
                    </div>
                <?php endif; ?>

                <div <?= mws_aos() ?> class="mb-14 text-center">
                    <?= mws_eyebrow(__('Where we work', 'mws')) ?>
                    <h2 class="<?= $h2 ?>"><?php esc_html_e('Landscaping across Butler, Allegheny & Beaver County', 'mws'); ?></h2>
                </div>

                <?php foreach ($area_groups as $g => $group) : ?>
                    <div class="<?= $g > 0 ? 'mt-16' : '' ?>">

                        <div <?= mws_aos() ?> class="mb-8">
                            <?= mws_eyebrow($group['eyebrow']) ?>
                            <h3 class="<?= $h3 ?> text-ink"><?= mws_e($group['title']) ?></h3>
                        </div>

                        <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <?php foreach ($group['items'] as $i => $area) : ?>
                                <?php
                                /* sanitize_title() is what WordPress used to build the
                                   child page's slug from its own title, so matching on it
                                   keeps the two in step without storing a URL per area. */
                                $area_url = $location_pages[sanitize_title($area['name'])] ?? '';

                                /* The hover lift is reserved for cards that actually go
                                   somewhere. Putting it on all forty-plus would promise a
                                   click that most of them cannot honour. */
                                $area_card = $area_url !== ''
                                    ? 'group flex items-center gap-3 rounded-tl-2xl rounded-br-2xl border border-line bg-white p-5 transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-soft'
                                    : 'flex items-center gap-3 rounded-tl-2xl rounded-br-2xl border border-line bg-white p-5';
                                ?>
                                <li <?= mws_aos('fade-up', $i) ?>>
                                    <?php if ($area_url !== '') : ?>
                                        <a href="<?= esc_url($area_url) ?>" class="<?= $area_card ?>">
                                    <?php else : ?>
                                        <div class="<?= $area_card ?>">
                                    <?php endif; ?>

                                        <span class="grid h-10 w-10 flex-none place-items-center rounded-full bg-brand-50 text-brand-600">
                                            <?= mws_icon('pin', 'h-5 w-5') ?>
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-h4 text-ink <?= $area_url !== '' ? 'transition group-hover:text-brand-600' : '' ?>">
                                                <?= mws_e($area['name']) ?>
                                            </span>
                                            <span class="block truncate text-micro uppercase text-ink-muted"><?= mws_e($area['note']) ?></span>
                                        </span>

                                        <?php if ($area_url !== '') : ?>
                                            <?= mws_icon('arrow', 'h-[18px] w-[18px] flex-none text-brand-600 transition group-hover:translate-x-1') ?>
                                        <?php endif; ?>

                                    <?php if ($area_url !== '') : ?>
                                        </a>
                                    <?php else : ?>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                    </div>
                <?php endforeach; ?>

                <p class="mt-12 text-center text-body text-ink-muted">
                    <?php
                    printf(
                        /* translators: %s: phone number link */
                        esc_html__('Not on the list? We travel further for larger builds — %s and ask.', 'mws'),
                        '<a class="font-semibold text-brand-600 underline" href="tel:' . esc_attr($ctx['tel']) . '">' . esc_html($brand['phone']) . '</a>'
                    );
                    ?>
                </p>

            </div>
        </section>

        <?php /* Recent work, as local proof. */ ?>
        <?php $projects = get_posts(['post_type' => 'mws_project', 'posts_per_page' => 3]); ?>
        <?php if ($projects !== []) : ?>
            <section class="<?= $section ?> <?= mws_section_bg() ?>">
                <div class="<?= $wrap ?>">
                    <div <?= mws_aos() ?> class="mb-11 flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                        <div>
                            <?= mws_eyebrow(__('Nearby', 'mws')) ?>
                            <h2 class="<?= $h2 ?>"><?php esc_html_e('Recent work in the area', 'mws'); ?></h2>
                        </div>
                        <a href="<?= esc_url(get_post_type_archive_link('mws_project')) ?>" class="<?= mws_btn('outline-dark', 'flex-none') ?>">
                            <?php esc_html_e('All projects', 'mws'); ?>
                        </a>
                    </div>
                    <ul class="grid gap-8 md:grid-cols-3">
                        <?php foreach ($projects as $i => $project) : ?>
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
