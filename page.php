<?php
/**
 * Generic page.
 *
 * Banner, then the editor content in a readable measure, then the estimate
 * band. Pages that need bespoke layout get their own template — this is the
 * one every ordinary page falls back to.
 *
 * @package MWS
 */

declare(strict_types=1);

get_header();

$ctx     = mws_context();
$wrap    = $ctx['wrap'];
$section = $ctx['section'];

while (have_posts()) :
    the_post();

    get_template_part('template-parts/banner', null, [
        'title'   => get_the_title(),
        'eyebrow' => (string) mws_field('banner_eyebrow'),
        'lead'    => (string) mws_field('banner_lead'),
        'image'   => (string) mws_field('banner_image', null, 'assets/img/banner-services.jpg'),
        'crumb'   => get_the_title(),
    ]);
    ?>

    <main class="<?= $section ?> <?= mws_section_bg() ?>">
        <div class="<?= $wrap ?>">
            <div class="mx-auto max-w-[820px] <?= mws_prose() ?>">
                <?php the_content(); ?>
            </div>
        </div>
    </main>

    <?php
    get_template_part('template-parts/estimate-band');

endwhile;

get_footer();
