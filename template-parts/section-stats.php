<?php
/**
 * Numbers band — dark, full width.
 *
 * Shared component. Introduced on the About page and now used by services and
 * projects too, so a number band looks identical wherever it appears.
 *
 * Args:
 *   heading  string  optional
 *   items    string  one per line, "Value | Label"
 *
 * @package MWS
 */

declare(strict_types=1);

$ctx   = mws_context();
$wrap  = $ctx['wrap'];
$items = mws_lines((string) ($args['items'] ?? ''));

if ($items === []) {
    return;
}

$heading = (string) ($args['heading'] ?? '');
/* Literal class strings: Tailwind cannot see a class name assembled at
   runtime, so the generated sheet would be missing it. */
$colClasses = [
    2 => 'lg:grid-cols-2',
    3 => 'lg:grid-cols-3',
    4 => 'lg:grid-cols-4',
];
$cols = $colClasses[min(4, max(2, count($items)))] ?? 'lg:grid-cols-4';
?>

<section class="relative overflow-hidden <?= mws_section_bg('dark') ?> py-16 md:py-20">
    <div class="<?= $wrap ?> relative z-10">
        <?php if ($heading !== '') : ?>
            <h2 <?= mws_aos() ?> class="mb-12 max-w-[24ch] text-h2 !text-white">
                <?= mws_heading($heading) ?>
            </h2>
        <?php endif; ?>

        <dl class="grid gap-10 sm:grid-cols-2 <?= $cols ?>">
            <?php foreach ($items as $i => $line) : ?>
                <?php [$value, $label] = mws_split_line($line); ?>
                <div <?= mws_aos('fade-up', $i) ?> class="border-t border-white/15 pt-6">
                    <dt class="mb-2 text-h1 leading-none text-white"><?= mws_e($value) ?></dt>
                    <dd class="text-small text-white/65"><?= mws_e($label) ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </div>
</section>
