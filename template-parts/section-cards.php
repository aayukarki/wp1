<?php
/**
 * Icon card grid.
 *
 * Shared component — the "How we work" block from the About page, reused for
 * service benefits and project details.
 *
 * Args:
 *   eyebrow  string  optional
 *   heading  string  optional
 *   lead     string  optional
 *   items    string  one per line, "icon | Title | Description"
 *   align    string  'center' (default) or 'left'
 *
 * @package MWS
 */

declare(strict_types=1);

$ctx   = mws_context();
$wrap  = $ctx['wrap'];
$sec   = $ctx['section'];
$h2    = $ctx['h2'];
$items = mws_lines((string) ($args['items'] ?? ''));

if ($items === []) {
    return;
}

$eyebrow = (string) ($args['eyebrow'] ?? '');
$heading = (string) ($args['heading'] ?? '');
$lead    = (string) ($args['lead'] ?? '');
$centred = ($args['align'] ?? 'center') === 'center';
?>

<section class="<?= $sec ?> <?= mws_section_bg() ?>">
    <div class="<?= $wrap ?>">

        <?php if ($heading !== '' || $eyebrow !== '') : ?>
            <div <?= mws_aos() ?> class="mb-12 <?= $centred ? 'mx-auto max-w-[62ch] text-center' : 'max-w-[62ch]' ?>">
                <?php if ($eyebrow !== '') : ?>
                    <?= mws_eyebrow($eyebrow) ?>
                <?php endif; ?>
                <?php if ($heading !== '') : ?>
                    <h2 class="<?= $h2 ?>"><?= mws_heading($heading) ?></h2>
                <?php endif; ?>
                <?php if ($lead !== '') : ?>
                    <p class="mt-5 text-body text-ink-muted"><?= mws_e($lead) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <ul class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($items as $i => $line) : ?>
                <?php [$icon, $title, $text] = mws_split_parts($line, 3); ?>
                <li <?= mws_aos('fade-up', $i) ?>
                    class="rounded-tl-4xl rounded-br-4xl border border-line bg-white p-7 transition duration-400 hover:-translate-y-1 hover:border-brand-200 hover:shadow-lift">
                    <span class="mb-5 grid h-12 w-12 place-items-center rounded-full border border-brand-100 bg-brand-50 text-brand-600">
                        <?= mws_icon($icon !== '' ? $icon : 'check', 'h-6 w-6') ?>
                    </span>
                    <h3 class="mb-2 text-h4 text-ink"><?= mws_e($title) ?></h3>
                    <p class="text-body text-ink-muted"><?= mws_e($text) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>

    </div>
</section>
