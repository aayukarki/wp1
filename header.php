<?php
/**
 * Document head, marquee strip and the sticky header.
 *
 * @package MWS
 */

declare(strict_types=1);

$ctx       = mws_context();
$config    = $ctx['config'];
$brand     = $ctx['brand'];
$tel       = $ctx['tel'];
$top       = $ctx['top'];
$wrap      = $ctx['wrap'];

/* Financing destination for the header button. The settings screen wins so it
   can always be overridden, then the finance calculator page, then whatever
   inc/config.php carries, and the phone number as a last resort so the button is
   never a dead link. */
$financePage = get_page_by_path('finance-calculator');

$financeUrl = mws_setting('financing_url')
    ?: ($financePage ? (string) get_permalink($financePage) : '')
    ?: ($config['hero']['card']['cta']['href'] ?? '')
    ?: 'tel:' . $tel;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#C8102E">

    <?php /* The description and OpenGraph tags used to be hard-coded here, which
             meant one generic description on all ~80 URLs. Yoast now emits them
             per page from wp_head(), so these were removed rather than left in:
             two <meta name="description"> tags on a page is worse than either
             one alone, and the hard-coded pair printed first, so the generic
             text was the one search engines met. Yoast also covers og:type,
             og:url and og:site_name, which were never here. */ ?>

    <?php /* Favicon set built from the company logo. The 32px file is cropped to
             the logo block only — the square artwork the live site uses also packs
             the phone number and a tagline, and at tab size all three together are
             unreadable. Larger sizes carry the same crop so the mark is consistent
             wherever the browser shows it. */ ?>
    <link rel="icon" type="image/png" sizes="32x32" href="<?= mws_asset('assets/icons/favicon-32.png') ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= mws_asset('assets/icons/favicon-192.png') ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= mws_asset('assets/icons/favicon-512.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= mws_asset('assets/icons/apple-touch-icon.png') ?>">


    <?php wp_head(); ?>

    <?php if (is_front_page()) : ?>
        <script type="application/ld+json">
<?= wp_json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'LandscapingBusiness',
    'name' => $brand['name'],
    'description' => $brand['tagline'],
    'telephone' => $brand['phone'],
    'email' => $brand['email'],
    /* A real street address is published now, so it goes in PostalAddress
       properly. areaServed stays the counties — reading it off $brand['location']
       would have started emitting the street address as a served area the moment
       that field changed. */
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => $brand['address']['street'],
        'addressLocality' => $brand['address']['city'],
        'addressRegion' => $brand['address']['region'],
        'postalCode' => $brand['address']['postcode'],
        'addressCountry' => 'US',
    ],
    'areaServed' => array_map(
        static fn(string $place): array => ['@type' => 'AdministrativeArea', 'name' => $place],
        $brand['areas']
    ),
    'openingHours' => 'Mo-Sa 07:00-18:00',
    'foundingDate' => (string) $brand['founded'],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?>
</script>
    <?php endif; ?>
</head>

<body <?php body_class('bg-white font-sans text-body text-ink-soft antialiased'); ?>>
<?php wp_body_open(); ?>

    <a href="#home"
        class="sr-only focus:not-sr-only focus:absolute focus:left-1/2 focus:top-0 focus:z-[200] focus:-translate-x-1/2 focus:rounded-tl-xl focus:rounded-br-xl focus:bg-brand-600 focus:px-5 focus:py-2.5 focus:text-white">Skip
        to content</a>

    <!-- ══ Header ═══════════════════════════════════════════════════════ -->
    <?php
    /* Shared class sets so the desktop bar and the mobile sheet stay in step. */
    /* Normal weight, sentence case. The nav sat in bold uppercase, which
       competed with the headings for emphasis; letting it sit quietly puts the
       weight back on the display type and the two buttons beside it. */
    $navTop = 'inline-flex items-center gap-1.5 whitespace-nowrap text-nav font-normal '
        . 'text-ink transition hover:text-brand-600';
    $navSub = 'block rounded-xl px-3.5 py-2.5 text-btn font-medium text-ink-soft '
        . 'transition hover:bg-white hover:text-ink';
    $navPop = 'invisible absolute left-0 top-full z-50 w-[17rem] translate-y-1 pt-5 opacity-0 '
        . 'transition duration-200 group-hover:visible group-hover:translate-y-0 '
        . 'group-hover:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 '
        . 'group-focus-within:opacity-100';
    ?>
    <header id="top" data-header class="sticky top-0 z-50 bg-white">

        <!-- Marquee strip. The track repeats so the loop never runs dry: one copy
             is always scrolling off, so the rest must still cover the strip. Four
             copies of this list span ~3600px, past any realistic viewport. Only
             the first is exposed to assistive tech; the others are filler. -->
        <div class="marquee flex h-10 items-center overflow-hidden bg-ink text-eyebrow font-semibold uppercase text-white/85"
            role="region" aria-label="Why choose us">
            <?php for ($copy = 0; $copy < 4; $copy++): ?>
                <div class="marquee__track" <?= $copy ? 'aria-hidden="true"' : '' ?>>
                    <?php foreach ($top['marquee'] as $item): ?>
                        <span class="inline-flex flex-none items-center gap-4 whitespace-nowrap">
                            <?= mws_icon('leaf', 'h-4 w-4 flex-none text-brand-400') ?>        <?= mws_e($item) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Main bar: logo, navigation, then status and the two calls to action -->
        <div data-header-bar class="<?= $wrap ?> flex h-[88px] items-center gap-6 2xl:gap-8">

            <a href="<?= esc_url(home_url('/')) ?>#home" class="flex-none">
                <img src="<?= mws_asset($brand['logo']['src']) ?>" alt="<?= mws_e($brand['logo']['alt']) ?>" width="200"
                    height="98" class="w-[125px] md:w-auto">
            </a>

            <nav aria-label="Main" class="hidden xl:flex xl:items-center xl:gap-5 2xl:gap-7">
                <?php foreach ($config['nav']['items'] as $item): ?>
                    <?php if (empty($item['children'])): ?>
                        <a href="<?= mws_e($item['href']) ?>" class="<?= $navTop ?>"><?= mws_e($item['label']) ?></a>
                    <?php else: ?>
                        <div class="group relative">
                            <a href="<?= mws_e($item['href']) ?>" class="<?= $navTop ?>">
                                <?= mws_e($item['label']) ?>
                                <?= mws_icon('chevron', 'h-4 w-4 transition duration-200 group-hover:rotate-180') ?>
                            </a>
                            <div class="<?= $navPop ?>">
                                <ul class="rounded-2xl border border-line bg-sand p-2 shadow-lift">
                                    <?php foreach ($item['children'] as $child): ?>
                                        <li><a href="<?= mws_e($child['href']) ?>" class="<?= $navSub ?>"><?= mws_e($child['label']) ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>

            <div class="ml-auto flex flex-none items-center gap-3">

                <?php /* Financing. Used to be a card in the hero and then a band
                         further down the page; it is a header action now, so it is
                         reachable from every page rather than only the home page.
                         Green keeps it clearly separate from the red call action. */ ?>
                <a href="<?= esc_url($financeUrl) ?>"
                    class="<?= mws_btn('finance', 'mws-pulse hidden lg:inline-flex') ?>">
                    <?= mws_icon('calendar', 'h-[18px] w-[18px]') ?>
                    <?php esc_html_e('Financing Available', 'mws'); ?>
                </a>

                <?php /* Icon plus the number. The label is dropped below lg, where
                         the bar has no room for it — the icon and the tel: link
                         still work, and the number is spelled out in the mobile
                         sheet and the footer. */ ?>
                <a href="tel:<?= mws_e($tel) ?>"
                    aria-label="<?= esc_attr(sprintf(__('Call %s', 'mws'), $brand['phone'])) ?>"
                    class="btn-premium inline-flex h-11 flex-none items-center gap-2.5 rounded-tl-2xl rounded-br-2xl bg-brand-600 px-3 text-white shadow-glow transition duration-200 hover:-translate-y-0.5 hover:bg-brand-700 lg:px-5">
                    <?= mws_icon('phone', 'h-5 w-5') ?>
                    <span class="hidden whitespace-nowrap text-btn font-extrabold lg:inline"><?= mws_e($brand['phone']) ?></span>
                </a>

                <button type="button" data-nav-toggle aria-expanded="false" aria-controls="nav" aria-label="Open menu"
                    class="grid h-11 w-11 place-items-center rounded-tl-2xl rounded-br-2xl border border-line bg-white text-ink xl:hidden">
                    <?= mws_icon('menu', 'h-6 w-6 [.is-open_&]:hidden') ?>
                    <?= mws_icon('close', 'hidden h-6 w-6 [.is-open_&]:block') ?>
                </button>
            </div>
        </div>

        <!-- Mobile sheet: every header link in one scrollable panel -->
        <nav id="nav" aria-label="Mobile"
            class="invisible absolute inset-x-0 top-full z-40 max-h-[calc(100vh-8rem)] -translate-y-2 overflow-y-auto border-b border-line bg-sand px-6 pb-6 pt-2 opacity-0 shadow-lift transition duration-200 xl:hidden">
            <ul class="flex flex-col">
                <?php foreach ($config['nav']['items'] as $item): ?>
                    <li class="border-b border-line last:border-0">
                        <a href="<?= mws_e($item['href']) ?>"
                            class="block py-3 text-body font-normal text-ink"><?= mws_e($item['label']) ?></a>
                        <?php if (!empty($item['children'])): ?>
                            <ul class="mb-3 flex flex-col gap-0.5 pl-1">
                                <?php foreach ($item['children'] as $child): ?>
                                    <li><a href="<?= mws_e($child['href']) ?>" class="<?= $navSub ?>"><?= mws_e($child['label']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php /* Both header actions repeat here, spelled out — an icon-only
                     call button inside a menu sheet would be needlessly cryptic. */ ?>
            <a href="<?= esc_url($financeUrl) ?>" class="<?= mws_btn('finance', 'mt-4 w-full') ?>">
                <?= mws_icon('calendar', 'h-[18px] w-[18px]') ?>
                <?php esc_html_e('Financing Available', 'mws'); ?>
            </a>

            <a href="tel:<?= mws_e($tel) ?>" class="<?= mws_btn('primary', 'mt-3 w-full') ?>">
                <?= mws_icon('phone', 'h-[18px] w-[18px]') ?>
                Call <?= mws_e($brand['phone']) ?>
            </a>
        </nav>
    </header>
