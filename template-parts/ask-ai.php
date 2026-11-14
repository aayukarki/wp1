<?php
/**
 * "Ask an AI about us" — sits in the footer, under the menu columns.
 *
 * Each link opens an assistant with the question already typed. The prompt is
 * written once in inc/config.php and URL-encoded here, so the five links can
 * never drift apart from one another.
 *
 * Links open in a new tab deliberately: this is the footer of a page someone is
 * still reading, and sending them away from it to ask a question about the
 * business is the opposite of what the block is for.
 *
 * Renders nothing if the config block is missing or has no providers, so the
 * footer survives the section being removed from config.
 *
 * @package MWS
 */

declare(strict_types=1);

$ctx    = mws_context();
$config = $ctx['config'];

$askAi = $config['ask_ai'] ?? [];
$items = $askAi['items'] ?? [];

if ($items === [] || trim((string) ($askAi['title'] ?? '')) === '') {
    return;
}

$prompt = rawurlencode((string) ($askAi['prompt'] ?? ''));
?>

<div class="mt-14 border-t border-white/10 pt-14">
    <div <?= mws_aos() ?> class="mx-auto max-w-[680px] text-center">

        <h3 class="text-h2 text-white"><?= mws_heading((string) $askAi['title']) ?></h3>

        <?php if (($askAi['lead'] ?? '') !== '') : ?>
            <p class="mx-auto mt-4 max-w-[58ch] text-body text-white/70">
                <?= mws_e((string) $askAi['lead']) ?>
            </p>
        <?php endif; ?>

        <ul class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <?php foreach ($items as $item) : ?>
                <?php
                $label = trim((string) ($item['label'] ?? ''));
                $url   = trim((string) ($item['url'] ?? ''));

                if ($label === '' || $url === '') {
                    continue;
                }
                ?>
                <li>
                    <?php /* The icon is a decorative mask with no text, so the link
                             needs the sr-only name — a row of five unlabelled links
                             is unusable with a screen reader. */ ?>
                    <a href="<?= esc_url($url . $prompt) ?>"
                        target="_blank" rel="noopener noreferrer"
                        title="<?= mws_e($label) ?>"
                        class="grid h-12 w-12 place-items-center rounded-tl-2xl rounded-br-2xl border border-white/20 text-white transition duration-300 hover:-translate-y-0.5 hover:border-brand-600 hover:bg-brand-600">
                        <?= mws_icon((string) ($item['icon'] ?? 'arrow-ne'), 'h-6 w-6') ?>
                        <span class="sr-only">
                            <?php
                            printf(
                                /* translators: %s: assistant name, e.g. ChatGPT */
                                esc_html__('Ask %s about us (opens in a new tab)', 'mws'),
                                esc_html($label)
                            );
                            ?>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

    </div>
</div>
