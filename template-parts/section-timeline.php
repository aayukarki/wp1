<?php
/**
 * Horizontal timeline.
 *
 * Shared component — the About page's milestones block, reused for a service's
 * process and a project's phases. The label column takes a year, a step number
 * or a phase name; the markup does not care which.
 *
 * Args:
 *   eyebrow  string  optional
 *   heading  string  optional
 *   items    string  one per line, "Label | Title | Description"
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
/* Literal class strings — see the note above the <ol> below. */
$colClasses = [
    2 => 'md:grid-cols-2',
    3 => 'md:grid-cols-3',
    4 => 'md:grid-cols-4',
];
$cols = $colClasses[min(4, max(2, count($items)))] ?? 'md:grid-cols-4';
?>

<section class="<?= $sec ?> <?= mws_section_bg() ?>">
    <div class="<?= $wrap ?>">

        <?php if ($heading !== '' || $eyebrow !== '') : ?>
            <div <?= mws_aos() ?> class="mb-12 text-center">
                <?php if ($eyebrow !== '') : ?>
                    <?= mws_eyebrow($eyebrow) ?>
                <?php endif; ?>
                <?php if ($heading !== '') : ?>
                    <h2 class="<?= $h2 ?>"><?= mws_heading($heading) ?></h2>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php /* A single rule runs behind the row from md up; each marker sits on
                 it, so the steps read as one line rather than unrelated cards. */ ?>
        <?php /* Tailwind's scanner only ever sees literal strings in the source,
                 so a class name built at runtime — "md:grid-cols-" . $cols —
                 never gets generated. That is exactly what broke this section:
                 four steps asked for md:grid-cols-4, which was not in the
                 compiled sheet, so the row collapsed to a single column with the
                 connecting rule still drawn across the top. The literals below
                 are what the scanner needs to find. */ ?>
        <ol class="relative grid gap-10 <?= $cols ?> md:gap-6">
            <span aria-hidden="true" class="pointer-events-none absolute left-0 right-0 top-[13px] hidden h-px bg-line md:block"></span>

            <?php foreach ($items as $i => $line) : ?>
                <?php [$label, $title, $text] = mws_split_parts($line, 3); ?>
                <li <?= mws_aos('fade-up', $i) ?> class="relative">
                    <span aria-hidden="true" class="mb-6 block h-[27px]">
                        <span class="grid h-[27px] w-[27px] place-items-center rounded-full border-2 border-brand-600 bg-white">
                            <span class="h-2.5 w-2.5 rounded-full bg-brand-600"></span>
                        </span>
                    </span>
                    <strong class="mb-1.5 block text-h3 text-brand-600"><?= mws_e($label) ?></strong>
                    <h3 class="mb-2 text-h4 text-ink"><?= mws_e($title) ?></h3>
                    <p class="text-body text-ink-muted"><?= mws_e($text) ?></p>
                </li>
            <?php endforeach; ?>
        </ol>

    </div>
</section>
