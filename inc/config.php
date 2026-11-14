<?php
/**
 * Site content & configuration.
 *
 * Section order mirrors the reference design:
 * hero → about → services → portfolio → process → testimonials → CTA → journal → footer.
 *
 * Everything the page renders lives here; index.php is only markup.
 *
 * IMAGES — every 'image' key below is empty and waiting for a source. Paste an
 * absolute URL (https://…) or a path relative to the project root
 * (assets/img/hero.jpg) and it is picked up automatically. An empty value
 * renders a plain neutral pane, so the layout never breaks.
 */

return [

    'brand' => [
        /* Header logo. 'alt' should read the way the artwork does, since it
           replaces the wordmark rather than sitting beside it. */
        'logo'    => ['src' => 'assets/img/logo.png', 'alt' => 'GNT Landscaping'],
        'name'    => 'GNT Landscaping',
        'tagline' => 'Landscape & hardscape construction',
        'phone'   => '(724) 712-3619',
        'email'   => 'chris@gntlandscaping.com',
        /* The registered address. Kept as parts as well as display lines: the
           structured data needs streetAddress / addressLocality / postalCode
           separately, and guessing them back out of a formatted string is how
           schema errors happen. */
        'address' => [
            'street'   => '106 Kingston Ave',
            'city'     => 'Cranberry Twp',
            'region'   => 'PA',
            'postcode' => '16066',
        ],

        /* What prints under "Our location". */
        'location' => [
            '106 Kingston Ave',
            'Cranberry Twp, PA 16066',
        ],

        /* Counties served. Separate from the address on purpose — these feed
           areaServed in the structured data, where a street address would be
           wrong and a county would be wrong in streetAddress. */
        'areas' => [
            'Butler County, PA, US',
            'Allegheny County, PA, US',
        ],
        'hours'   => 'Mon – Sat · 7:00am – 6:00pm',
        'area'    => 'Serving Butler & Allegheny counties, PA',
        'founded' => 2009,
    ],

    /* Where form submissions go. Leads are always appended to log_file;
       set 'send_mail' => true once a working mailer is configured
       (MAMP has none by default, so mail() would silently fail). */
    'leads' => [
        'notify_email' => 'chris@gntlandscaping.com',
        'send_mail'    => false,
        'log_file'     => __DIR__ . '/../storage/leads.log',
    ],

    /* The header's marquee strip, plus the open/closed badge in the main bar.
       'hours' drives the badge: days are ISO weekdays (1 = Mon), times 24-hour.
       Set 'always' => true for a round-the-clock business. */
    'topbar' => [
        'timezone' => 'America/New_York',

        /* Scrolling ticker across the top of the header. Each entry is separated
           by a leaf mark; the list is rendered twice so the loop is seamless, so
           keep it short enough to stay legible in motion. */
        'marquee'  => [
            'Customer-oriented',
            'Fully Licensed & Insured',
            'We Are Experts',
            'Free Quotes',
            'Financing Available',
            /* The Google rating lives in the banner badge now, not here — it was
               saying the same thing twice on the same screen. */
            '10+ Years in Business',
        ],
        'hours'        => [
            'always'       => false,
            'days'         => [1, 2, 3, 4, 5, 6],
            'from'         => '07:00',
            'to'           => '18:00',
            'open_label'   => "We're open",
            'closed_label' => 'Currently closed',
        ],
    ],

    /* Header navigation: a single group sitting to the right of the logo. Any
       item may carry 'children' to render a dropdown. The bar also shows a
       tel: button built from brand.phone plus the 'book' CTA below. */
    'nav' => [
        'items' => [
            ['label' => 'About us', 'href' => '#about'],
            [
                /* 'children' is filled from services.items by index.php, so the
                   service list only ever needs editing in one place. */
                'label'    => 'Our services',
                'href'     => '#services',
                'children' => [],
            ],
            [
                'label'    => 'Areas we service',
                'href'     => '#work',
                'children' => [
                    ['label' => 'Cranberry Township', 'href' => '#work'],
                    ['label' => 'Butler',             'href' => '#work'],
                    ['label' => 'Zelienople',         'href' => '#work'],
                    ['label' => 'Mars',               'href' => '#work'],
                    ['label' => 'Wexford',            'href' => '#work'],
                    ['label' => 'Gibsonia',           'href' => '#work'],
                    ['label' => 'Sewickley',          'href' => '#work'],
                    ['label' => 'Pittsburgh',         'href' => '#work'],
                ],
            ],
            ['label' => 'Projects', 'href' => '#work'],
            ['label' => 'Journal',  'href' => '#journal'],
        ],
        'book' => ['label' => 'Book Now', 'href' => '#booking'],
    ],

    'hero' => [
        'title'   => "Let us bring life to your landscape",
        'lead'    => 'Design, stonework and outdoor living spaces built by a licensed crew '
                   . 'that treats your yard like its own.',
        'primary' => ['label' => 'Get in touch',    'href' => '#booking'],
        'ghost'   => ['label' => 'Browse services', 'href' => '#services'],

        /* Banner slideshow. Slides crossfade in this order; the headline, the
           buttons and the booking card stay put. Add or remove entries freely —
           the controls adapt, and a single slide hides them altogether. */
        'slides'  => [
            [
                'image' => 'assets/img/banner-1.jpg',
                'alt'   => 'A landscaped backyard at golden hour with a broad flagstone patio '
                         . 'and a built-in stone fire pit ringed by planting beds',
            ],
            [
                'image' => 'assets/img/banner-2.jpg',
                'alt'   => 'The front of a modern home with a striped lawn, a natural stone '
                         . 'walkway and layered planting beds',
            ],
            [
                'image' => 'assets/img/banner-3.jpg',
                'alt'   => 'A wide flagstone terrace with stone steps, a dry-stacked retaining '
                         . 'wall and a cedar pergola over a seating area',
            ],
            [
                'image' => 'assets/img/banner-4.jpg',
                'alt'   => 'A garden at dusk lit by low-voltage uplights and path lights tracing '
                         . 'a curved stone walkway',
            ],
        ],

        /* The card sitting on the banner. Point cta.href at your financing
           provider's application; while it is empty the button falls back to
           the phone number so it is never a dead end. */
        'card'    => [
            'title' => 'Financing Available Now',
            'text'  => 'This takes two minutes and does not affect your credit score.',
            'cta'   => ['label' => 'Check your options', 'href' => ''],
        ],
    ],

    'about' => [
        'eyebrow' => 'About us',
        'title'   => 'Why choose us?',
        'lead'    => 'We handle the whole yard — grading, planting, patios, walls and lighting — so you get '
                   . 'one crew, one schedule and one point of contact from first sketch to final walkthrough.',
        'image'   => 'assets/img/about.jpg',
        'points'  => [
            [
                'icon'  => 'leaf',
                'title' => 'Built for this climate',
                'text'  => 'Hardy native planting plans chosen to come through a western PA winter.',
            ],
            [
                'icon'  => 'heart',
                'title' => 'Done with care',
                'text'  => 'Proper base depth, drainage and compaction — patios that stay flat for decades.',
            ],
        ],
        'cta'     => ['label' => 'Get in touch', 'href' => '#booking'],
        'quote'   => [
            'text'   => 'Reliable and meticulous work — they made the renovation process easy.',
            'author' => 'Sophie Moore',
            'role'   => 'Cranberry Township, PA',
        ],
    ],

    /* Awards / recognition below "Why choose us". These are portrait documents
       with readable text, so the section renders them as contained <img> rather
       than cover-cropped panes — a 4:3 crop would cut the wording off. Hosted on
       the client's own site; swap in local paths if you'd rather self-host. */
    'awards' => [
        'title' => 'Best Landscaper - 2026 Cranberry Twp',
        'items' => [
            /* Local copies rather than hotlinks to gntlandscaping.com — the old
               URLs meant this section broke the moment that site moved a file,
               and every visitor fetched it from a server we do not control. */
            [
                'image' => 'assets/img/award-1.jpg',
                'alt'   => 'BusinessRate Best of 2026 award winner plaque for GNT in the '
                         . 'Landscaper category, Cranberry Twp, Pennsylvania',
            ],
            [
                'image' => 'assets/img/award-2.jpg',
                'alt'   => 'Letter confirming GNT as a BusinessRate Best of 2026 Cranberry Twp '
                         . 'award winner in the Landscaper category, based on Google Reviews',
            ],
        ],
    ],

    'services' => [
        'eyebrow' => 'Services',
        'title'   => "Explore our wide\nrange of services",
        'primary' => ['label' => 'Get in touch',    'href' => '#booking'],
        'ghost'   => ['label' => 'Browse services', 'href' => '#work'],
        'items'   => [
            [
                'title' => 'Junk Removal',
                'text'  => 'Yard waste, old decking, broken concrete and storm debris hauled away and disposed of properly.',
                'image' => 'assets/img/service-junk-removal.jpg',
            ],
            [
                'title' => 'Landscape Lighting Experts',
                'text'  => 'Low-voltage path, uplighting and facade schemes — zoned, transformer-sized and installed to code.',
                'image' => 'assets/img/service-landscape-lighting.jpg',
            ],
            [
                'title' => 'Lawn & Sod Expert',
                'text'  => 'Grading, soil prep and fresh sod or seed, with a watering plan that gets it properly established.',
                'image' => 'assets/img/service-lawn-sod.jpg',
            ],
            [
                'title' => 'Tropical Back Yard Oasis',
                'text'  => 'Layered palms, bold foliage and water features that turn a plain yard into a private retreat.',
                'image' => 'assets/img/service-tropical-oasis.jpg',
            ],
            [
                'title' => 'Landscape Installation And Design',
                'text'  => 'Full concept plans, 3D renderings and planting schedules tailored to your lot and light.',
                'image' => 'assets/img/service-landscape-design.jpg',
            ],
            [
                'title' => 'Hardscapes Design & Installation',
                'text'  => 'Flagstone, pavers and poured concrete set on an engineered base with real drainage.',
                'image' => 'assets/img/service-hardscapes.jpg',
            ],
            [
                'title' => 'Mulch Installation',
                'text'  => 'Bed edging, weed barrier and fresh hardwood or cedar mulch laid at the right depth.',
                'image' => 'assets/img/service-mulch.jpg',
            ],
            [
                'title' => 'Drainage And French Drains Installation',
                'text'  => 'French drains, dry creek beds and regrading that move water away from your foundation.',
                'image' => 'assets/img/service-drainage.jpg',
            ],
            [
                'title' => 'Backyard Hardscape And Renovations',
                'text'  => 'Patios, fire pits, pergolas and built-in seating that extend the season outdoors.',
                'image' => 'assets/img/service-backyard-renovation.jpg',
            ],
            [
                'title' => 'Commercial Maintenance Services',
                'text'  => 'Scheduled mowing, pruning, bed care and clean-ups for offices, retail sites and stratas.',
                'image' => 'assets/img/service-commercial-maintenance.jpg',
            ],
            [
                'title' => 'Commercial Snow & Ice Management',
                'text'  => 'Seasonal contracts for plowing, salting and sidewalk clearing, with 24-hour call-out.',
                'image' => 'assets/img/service-snow-ice.jpg',
            ],
        ],
    ],

    'work' => [
        'eyebrow' => 'Portfolio',
        'title'   => "Our past projects of\nlandscaping work",
        'cta'     => ['label' => 'Get in touch', 'href' => '#booking'],
        'items'   => [
            /* Titles and metas describe what is actually in each photograph.
               The spans are a mosaic — a and b are 2×2, c and e are tall, d and
               f are wide — so the one portrait shot sits on a tall tile (c)
               rather than being cropped to a letterbox. */
            ['title' => 'Fire pit patio',        'meta' => 'Paving · Seating walls',      'image' => 'assets/img/work-1.jpg', 'span' => 'a'],
            ['title' => 'Covered patio',         'meta' => 'Concrete · Planting beds',    'image' => 'assets/img/work-2.jpg', 'span' => 'b'],
            ['title' => 'Poolside pavilion',     'meta' => 'Outdoor living · Pool surround', 'image' => 'assets/img/work-3.jpg', 'span' => 'c'],
            ['title' => 'Patio after dark',      'meta' => 'Lighting · Outdoor living',   'image' => 'assets/img/work-4.jpg', 'span' => 'd'],
            ['title' => 'Walkway &amp; steps',   'meta' => 'Hardscape · Drainage',        'image' => 'assets/img/work-5.jpg', 'span' => 'e'],
            ['title' => 'Under-deck patio',      'meta' => 'Paving · Fire table',         'image' => 'assets/img/work-6.jpg', 'span' => 'f'],
        ],
    ],

    'process' => [
        'eyebrow' => 'Our process',
        'title'   => "Our process is\nsimple and effective",
        'cta'     => ['label' => 'Get in touch', 'href' => '#booking'],
        'image'   => 'assets/img/process.jpg',
        'steps'   => [
            ['title' => 'Contact us',              'text' => 'Tell us about the property and your budget range. We reply within one business day.'],
            ['title' => 'Schedule an appointment', 'text' => 'We walk the yard, take measurements, then send a fixed-price proposal with every line itemised.'],
            ['title' => 'Get your project done',   'text' => 'Our crew builds it, cleans up daily and hands over a care guide plus your written warranty.'],
        ],
    ],

    'testimonials' => [
        'eyebrow' => 'Testimonials',
        'title'   => "What our clients say\nabout our work",
        'cta'     => ['label' => 'Get in touch', 'href' => '#booking'],
        'items'   => [
            ['name' => 'Lily Woods',    'role' => 'Cranberry Twp',     'text' => 'Highly professional team delivering top-notch landscaping every single time.'],
            ['name' => 'John Carter',   'role' => 'Wexford',           'text' => 'Fantastic landscaping service with remarkable results. Highly recommended.'],
            ['name' => 'Sophie Moore',  'role' => 'Sewickley',   'text' => 'Transformed our yard into a beautiful outdoor oasis. Absolutely stunning.'],
            ['name' => 'Matt Cannon',   'role' => 'Mars',          'text' => 'Exceptional service and breathtaking results. Our garden looks amazing.'],
            ['name' => 'Sandy Houston', 'role' => 'Fox Chapel',         'text' => 'Impressive transformation of our outdoor space. Could not be happier.'],
            ['name' => 'Patrick Meyer', 'role' => 'Butler',          'text' => 'Expert landscapers who deliver incredible results. Our yard is gorgeous.'],
            ['name' => 'Katrina Curl',  'role' => 'Gibsonia',        'text' => 'Unmatched expertise and dedication to landscaping perfection every time.'],
            ['name' => 'Andy Smith',    'role' => 'Zelienople', 'text' => 'Exceeded all our expectations with their landscaping skills and creativity.'],
            ['name' => 'Nina Alvarez',  'role' => 'Pittsburgh',      'text' => 'The drainage fix alone was worth it. Three winters on, the patio is perfect.'],
            ['name' => 'Tom Beckett',   'role' => 'Saxonburg',       'text' => 'Clear pricing, no surprises, and the crew cleaned the site every evening.'],
        ],
    ],

    /* Before & after. Each pair is the same yard shot from the same viewpoint,
       so keep them matched if you swap the photos — the whole point is that the
       two frames read as one place. Cards read left to right: Before, then
       After. */
    'beforeafter' => [
        'eyebrow' => 'Where before meets after',
        'title'   => "Discover how we can make your\noutdoor dreams come true.",
        'lead'    => 'Find out how we bring outdoor places to life, from plain patches to lush green '
                   . 'retreats. Our before and after photos show the attention to detail, ingenuity '
                   . 'and skill that go into every project we take on.',
        'items'   => [
            [
                'after'      => 'assets/img/ba-after-1.jpg',
                'before'     => 'assets/img/ba-before-1.jpg',
                'after_alt'  => 'The same backyard rebuilt with a flagstone patio, a striped lawn and '
                              . 'deep planting beds along the fence',
                'before_alt' => 'A neglected backyard of bare dirt, weeds and a pile of cut logs '
                              . 'against the same boundary fence',
            ],
            [
                'after'      => 'assets/img/ba-after-2.jpg',
                'before'     => 'assets/img/ba-before-2.jpg',
                'after_alt'  => 'The same front yard with a new stone walkway, fresh lawn and clipped '
                              . 'shrubs in mulched beds',
                'before_alt' => 'The same front yard with a cracked concrete path, patchy weedy grass '
                              . 'and straggly overgrown shrubs',
            ],
            [
                'after'      => 'assets/img/ba-after-3.jpg',
                'before'     => 'assets/img/ba-before-3.jpg',
                'after_alt'  => 'The same side yard held by a two-tier dry-stacked stone retaining '
                              . 'wall with planted terraces and stone steps',
                'before_alt' => 'The same side yard as a raw eroding earth bank with exposed roots '
                              . 'and loose rubble',
            ],
        ],
    ],

    'cta' => [
        'eyebrow' => 'Get in touch',
        'title'   => "Get in touch to design a\nbeautiful landscape today",
        'button'  => ['label' => 'Get in touch', 'href' => '#booking'],
    ],

    'journal' => [
        'eyebrow' => 'Our journal',
        'title'   => 'Our latest articles',
        'cta'     => ['label' => 'Browse all articles', 'href' => '#journal'],
        'items'   => [
            [
                'title'    => 'Landscape design 101: basics for a beautiful yard',
                'excerpt'  => 'Where to start when the yard is a blank slate — zones, sightlines and the order to build in.',
                'category' => 'Guides',
                'date'     => 'Aug 19, 2025',
                'image'    => 'assets/img/post-1.jpg',
            ],
            [
                'title'    => 'How to choose plants that suit your soil: a step-by-step guide',
                'excerpt'  => 'Picking natives that come through a western PA winter without constant replanting.',
                'category' => 'Planting',
                'date'     => 'Aug 04, 2025',
                'image'    => 'assets/img/post-2.jpg',
            ],
        ],
    ],

    'footer' => [
        'cta' => [
            'title'   => "Get in touch and\nreceive a free quote!",
            'primary' => ['label' => 'Get in touch', 'href' => '#booking'],
            /* Empty href falls back to brand.phone, so the number lives in one place. */
            'ghost'   => ['label' => 'Give us a call', 'href' => ''],
        ],
        'blurb'   => 'Design-build landscaping and stonework for homeowners across Butler and Allegheny counties since 2009.',
        'newsletter' => ['title' => 'Subscribe to our newsletter'],

        /* Footer link groups, each rendered as its own column. */
        'columns' => [
            'Quick links' => [
                ['label' => 'About us',     'href' => '#about'],
                ['label' => 'Our process',  'href' => '#process'],
                ['label' => 'Projects',     'href' => '#work'],
                ['label' => 'Testimonials', 'href' => '#testimonials'],
                ['label' => 'Journal',      'href' => '#journal'],
            ],
            /* Filled from services.items by index.php — one link per service. */
            'Services' => [],
            'Work with us' => [
                ['label' => 'Request a quote', 'href' => '#booking'],
                ['label' => 'Contact us',      'href' => '#booking'],
                ['label' => 'Careers',         'href' => '#booking'],
            ],
        ],
        /* Rendered beside the footer's call-to-action buttons. */
        'socials' => [
            ['label' => 'Facebook',  'href' => '#', 'icon' => 'facebook'],
            ['label' => 'Instagram', 'href' => '#', 'icon' => 'instagram'],
        ],
    ],
];
