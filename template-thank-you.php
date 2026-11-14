<?php
/**
 * Template Name: Thank you
 *
 * Where every Contact Form 7 submission lands. The `?form=` parameter tells us
 * which one, so the copy can match what the visitor actually just sent — a job
 * applicant and someone asking for a patio quote need different next steps.
 *
 * @package MWS
 */

declare(strict_types=1);

get_header();

$ctx   = mws_context();
$brand = $ctx['brand'];
$wrap  = $ctx['wrap'];
$sec   = $ctx['section'];
$h2    = $ctx['h2'];

$which = isset($_GET['form']) ? sanitize_key((string) $_GET['form']) : 'estimate';

$copy = [
    'estimate' => [
        'eyebrow' => __('Request received', 'mws'),
        'title'   => __('Thanks — we have your request', 'mws'),
        'lead'    => __('We reply within one business day with next steps and a time for your free on-site estimate.', 'mws'),
    ],
    'contact'  => [
        'eyebrow' => __('Message sent', 'mws'),
        'title'   => __('Thanks — your message is with us', 'mws'),
        'lead'    => __('Someone from the office will come back to you within one business day.', 'mws'),
    ],
    'careers'  => [
        'eyebrow' => __('Application received', 'mws'),
        'title'   => __('Thanks for applying', 'mws'),
        'lead'    => __('We read every application. If it looks like a fit we will call you within two business days to arrange a phone chat.', 'mws'),
    ],
][$which] ?? null;

$copy ??= [
    'eyebrow' => __('Received', 'mws'),
    'title'   => __('Thanks — we have it', 'mws'),
    'lead'    => __('We will be in touch shortly.', 'mws'),
];

while (have_posts()) :
    the_post();

    get_template_part('template-parts/banner', null, [
        'title'   => $copy['title'],
        'eyebrow' => $copy['eyebrow'],
        'lead'    => $copy['lead'],
        'image'   => (string) mws_field('banner_image', null, 'assets/img/banner-about.jpg'),
        'crumb'   => get_the_title(),
    ]);
    ?>

    <main class="<?= $sec ?> <?= mws_section_bg() ?>">
        <div class="<?= $wrap ?> mx-auto max-w-[760px] text-center">

            <span <?= mws_aos() ?> class="mx-auto mb-7 grid h-16 w-16 place-items-center rounded-full bg-brand-600 text-white shadow-glow">
                <?= mws_icon('check', 'h-8 w-8') ?>
            </span>

            <div <?= mws_aos() ?>>
                <h2 class="<?= $h2 ?> mb-5"><?php esc_html_e('What happens next', 'mws'); ?></h2>

                <ol class="mx-auto mb-10 grid max-w-[520px] gap-4 text-left">
                    <?php
                    $steps = $which === 'careers'
                        ? [
                            __('We read your application.', 'mws'),
                            __('If it fits, we call for a short phone chat.', 'mws'),
                            __('A paid day on site, so you see the work and we see how you work.', 'mws'),
                        ]
                        : [
                            __('We call you back within one business day.', 'mws'),
                            __('We visit, measure up and talk through the options.', 'mws'),
                            __('You get a fixed-price proposal in writing.', 'mws'),
                        ];
                    ?>
                    <?php foreach ($steps as $i => $step) : ?>
                        <li class="flex items-start gap-3.5">
                            <span class="grid h-7 w-7 flex-none place-items-center rounded-full bg-brand-50 text-btn font-bold text-brand-600"><?= (int) ($i + 1) ?></span>
                            <span class="text-body text-ink-muted"><?= mws_e($step) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>

                <p class="mb-8 text-body text-ink-muted">
                    <?php
                    printf(
                        /* translators: %s: phone number link */
                        esc_html__('Need us sooner? Call %s.', 'mws'),
                        '<a class="font-semibold text-brand-600 underline" href="tel:' . esc_attr($ctx['tel']) . '">' . esc_html($brand['phone']) . '</a>'
                    );
                    ?>
                </p>

                <div class="flex flex-wrap items-center justify-center gap-3">
                    <a href="<?= esc_url(home_url('/')) ?>" class="<?= mws_btn('primary') ?>"><?php esc_html_e('Back to home', 'mws'); ?></a>
                    <a href="<?= esc_url((string) get_post_type_archive_link('mws_project')) ?>" class="<?= mws_btn('outline-dark') ?>"><?php esc_html_e('See our work', 'mws'); ?></a>
                </div>
            </div>

        </div>
    </main>

<?php
endwhile;

get_footer();
