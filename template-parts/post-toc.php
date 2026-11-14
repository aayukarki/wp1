<?php
/**
 * Journal post table of contents.
 *
 * Built by mws_toc() from the post's own h2s, so it needs no editor upkeep.
 * Sticky beside the article from lg up; above it on narrower screens, where a
 * column beside 820px of body copy would leave neither enough room.
 *
 * Args (via get_template_part's $args):
 *   items array of ['id' => string, 'text' => string]
 *
 * @package MWS
 */

declare(strict_types=1);

$items = (array) ($args['items'] ?? []);

/* A post with a single heading has nothing to navigate — the list would only
   repeat the title back at the reader. */
if (count($items) < 2) {
    return;
}
?>

<nav class="mws-toc rounded-tl-4xl rounded-br-4xl border border-line bg-white p-6 shadow-soft lg:sticky lg:top-[120px]"
     aria-labelledby="mws-toc-title" data-toc>

    <p id="mws-toc-title" class="mb-4 text-micro uppercase text-ink-muted">
        <?php esc_html_e('On this page', 'mws'); ?>
    </p>

    <ol class="mws-toc__list">
        <?php foreach ($items as $index => $item) : ?>
            <li>
                <a href="#<?= esc_attr($item['id']) ?>" class="mws-toc__link" data-toc-link>
                    <span class="mws-toc__num" aria-hidden="true"><?= esc_html(sprintf('%02d', $index + 1)) ?></span>
                    <span><?= mws_e($item['text']) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
