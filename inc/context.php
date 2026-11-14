<?php
/**
 * Shared page context.
 *
 * The original landing page computed all of this at the top of index.php, where
 * header, body and footer were one file and shared a scope. Split into
 * header.php / front-page.php / footer.php they no longer do — WordPress loads
 * each in its own scope — so the derived values live here instead and every
 * template asks for them.
 *
 * Memoised, so the work happens once per request no matter how many templates
 * ask.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function mws_context(): array
{
    static $ctx = null;

    if ($ctx !== null) {
        return $ctx;
    }

    $config = mws_config();
    $brand  = $config['brand'];

    /* Navigation.
       The original page was a single scrolling document, so every nav item was
       an on-page anchor (#services, #work). Now that services and projects are
       real post types with their own pages, the nav is built from the database
       instead — anchors would strand anyone who is not already on the home page.

       inc/config.php remains the fallback, so the site still renders correctly
       before any content is seeded. */
    $services = function_exists('mws_services') ? mws_services() : [];

    if ($services !== []) {
        $serviceLinks = array_map(
            static fn(WP_Post $service): array => [
                'label' => get_the_title($service),
                'href'  => (string) get_permalink($service),
            ],
            $services
        );

        $pageLink = static function (string $slug, string $fallback): string {
            $page = get_page_by_path($slug);
            return $page ? (string) get_permalink($page) : $fallback;
        };

        $config['nav']['items'] = [
            ['label' => 'About us', 'href' => $pageLink('about-us', '#about')],
            [
                'label'    => 'Our services',
                'href'     => (string) get_post_type_archive_link('mws_service'),
                'children' => $serviceLinks,
            ],
            [
                'label'    => 'Areas we service',
                'href'     => $pageLink('areas-we-service', '#work'),
                'children' => array_map(
                    static fn(array $child): array => [
                        'label' => $child['label'],
                        'href'  => $pageLink('areas-we-service', '#work'),
                    ],
                    $config['nav']['items'][2]['children'] ?? []
                ),
            ],
            ['label' => 'Projects', 'href' => (string) get_post_type_archive_link('mws_project')],
            ['label' => 'Journal',  'href' => $pageLink('journal', '#journal')],
            /* Careers and Contact us are deliberately not in the header. Both
               are reachable from the footer's "Work with us" column, which keeps
               the main bar to the five items that carry the sales path. */
        ];

        $config['nav']['book'] = [
            'label' => 'Request an Estimate',
            'href'  => $pageLink('request-an-estimate', '#booking'),
        ];

        /* Both "Browse services" buttons pointed at on-page anchors, and the one
           inside the services section pointed at #work — the portfolio. Now that
           /services/ exists they both go there. */
        $servicesArchive = (string) get_post_type_archive_link('mws_service');
        $config['hero']['ghost']['href']        = $servicesArchive;
        $config['services']['ghost']['href']    = $servicesArchive;

        /* Every remaining on-page anchor gets a real destination. #booking was
           the estimate pop-up trigger in a dozen config entries; those become
           links to the estimate page so they work with JS off, are crawlable,
           and never read as dead anchors. The banner CTAs still open the modal —
           they are <button data-estimate-open>, not links. */
        $estimate = $pageLink('request-an-estimate', '#booking');
        $journal  = $pageLink('journal', '#journal');

        $config['hero']['primary']['href']          = $estimate;
        $config['hero']['card']['cta']['href']      = $pageLink('finance-calculator', $estimate);
        $config['about']['cta']['href']             = $estimate;
        $config['services']['primary']['href']      = $estimate;
        $config['work']['cta']['href']              = $estimate;
        $config['process']['cta']['href']           = $estimate;
        $config['testimonials']['cta']['href']      = $estimate;
        $config['cta']['button']['href']            = $estimate;
        $config['journal']['cta']['href']           = $journal;
        $config['footer']['cta']['primary']['href'] = $estimate;

        /* Service cards and the portfolio tiles link per-item in the templates;
           those are handled there. */

        $config['footer']['columns']['Services'] = $serviceLinks;

        /* The footer's "Quick links" column carries the same anchors; point them
           at the real pages too. */
        /* inc/config.php points these at #booking, which main.js turns into the
           estimate pop-up. Only "Request a quote" should do that — the other two
           are real pages. */
        $config['footer']['columns']['Work with us'] = [
            /* Real page rather than the #booking modal trigger: a footer link
               should be a link. The banner CTAs still open the modal. */
            ['label' => 'Request a quote', 'href' => $estimate],
            ['label' => 'Contact us',      'href' => $pageLink('contact-us', '#booking')],
            ['label' => 'Careers',         'href' => $pageLink('careers', '#booking')],
            ['label' => 'Financing',       'href' => $pageLink('finance-calculator', '#booking')],
        ];

        $config['footer']['columns']['Quick links'] = [
            ['label' => 'About us',    'href' => $pageLink('about-us', '#about')],
            ['label' => 'Our process', 'href' => home_url('/#process')],
            ['label' => 'Projects',    'href' => (string) get_post_type_archive_link('mws_project')],
            ['label' => 'Areas we service', 'href' => $pageLink('areas-we-service', '#work')],
            ['label' => 'Journal',     'href' => $pageLink('journal', '#journal')],
        ];
    } else {
        /* No content seeded yet — keep the original anchor-based nav. */
        $serviceLinks = array_map(
            static fn(array $item): array => ['label' => $item['title'], 'href' => '#services'],
            $config['services']['items']
        );

        foreach ($config['nav']['items'] as $i => $item) {
            if (($item['href'] ?? null) === '#services' && isset($item['children'])) {
                $config['nav']['items'][$i]['children'] = $serviceLinks;
            }
        }

        $config['footer']['columns']['Services'] = $serviceLinks;
    }

    /* Only banner slides with a usable image join the rotation, so half-filled
       configs never crossfade into blank panes. Once every slide has a URL this
       passes all of them through untouched. With none set we keep the full list
       so the banner still renders at the right size. */
    $heroSlides = array_values(array_filter(
        $config['hero']['slides'],
        static fn(array $slide): bool => mws_image_url($slide['image'] ?? '') !== null
    ));

    if ($heroSlides === []) {
        $heroSlides = $config['hero']['slides'];
    }

    /* Only the marquee reads $top now. The open/closed badge that used to live in
       the header bar is gone, so the timezone evaluation and the JSON payload it
       needed are no longer computed on every request. inc/config.php still
       carries topbar.hours, and mws_business_is_open() still exists, so the badge
       can be put back without rebuilding any of it. */
    $top = $config['topbar'];

    $ctx = [
        'config'     => $config,
        'brand'      => $brand,
        'tel'        => preg_replace('/\D+/', '', $brand['phone']),
        'year'       => wp_date('Y'),
        'top'        => $top,
        'heroSlides' => $heroSlides,

        /* Mosaic tile sizes for the portfolio grid (desktop only; 2-up below lg). */
        'spans' => [
            'a' => 'lg:col-span-2 lg:row-span-2',
            'b' => 'lg:col-span-2 lg:row-span-2',
            'c' => 'lg:col-span-1 lg:row-span-2',
            'd' => 'lg:col-span-2 lg:row-span-1',
            'e' => 'lg:col-span-1 lg:row-span-2',
            'f' => 'lg:col-span-2 lg:row-span-1',
        ],

        /* Every section — header and footer included — sits inside this container. */
        'wrap'    => 'mx-auto w-full max-w-[1440px] px-6',
        'section' => 'py-16 md:py-24 lg:py-28',

        /* Sizes come from the fluid `fontSize` scale in tailwind.config.js —
           each token carries its own line height, so no /[leading] suffix is
           needed. */
        'h2' => 'text-h2 text-ink',
        'h3' => 'text-h3',
    ];

    return $ctx;
}
