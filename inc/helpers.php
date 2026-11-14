<?php
/**
 * Rendering helpers + form handling for the MWS theme.
 *
 * Every function is prefixed `mws_`. The original landing page used bare names
 * (e, icon, media, btn …) which was fine for a standalone file, but a theme
 * shares a global function namespace with WordPress core and every active
 * plugin — a second declaration of `e()` anywhere would be a fatal error.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Escape for HTML output.
 *
 * Decodes before escaping so the result is idempotent. WordPress hands back
 * already-encoded text — get_the_title() runs the title through wptexturize,
 * which turns "&" into "&#038;" — and escaping that again produced a literal
 * "&#038;" on screen. Decoding first means the same call is safe whether the
 * value came from inc/config.php as raw text or from the database pre-encoded.
 */
function mws_e($value): string
{
    $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Escape a headline and turn the config's newlines into real <br> breaks.
 *
 * The headings deliberately do NOT use `whitespace-pre-line`. That class honours
 * the config's newlines, but it equally honours the newline an editor leaves
 * between `<h1 ...>` and the echo, which renders as a blank first line and a
 * stray line box worth of dead space. Emitting <br> here keeps the intended
 * breaks and lets ordinary whitespace collapsing ignore the template's own
 * formatting — so re-wrapping the markup can never change the output.
 */
function mws_heading(string $value): string
{
    return str_replace(["\r\n", "\r", "\n"], '<br>', mws_e($value));
}

/**
 * Cache-busted asset URL (raw — escape it before printing).
 *
 * Paths are relative to the theme root, so 'assets/img/logo.png' resolves to
 * the file inside the theme and returns a full URL through the theme URI.
 */
function mws_asset_url(string $path): string
{
    $rel  = ltrim($path, '/');
    $file = get_template_directory() . '/' . $rel;

    return get_template_directory_uri() . '/' . $rel
         . '?v=' . (is_file($file) ? filemtime($file) : '1');
}

/** Cache-busted asset URL, escaped for HTML output. */
function mws_asset(string $path): string
{
    return mws_e(mws_asset_url($path));
}

/**
 * Resolve an image reference to a usable URL.
 *
 * Accepts an absolute URL (https://…, //…, data:…) or a path relative to the
 * theme root. Returns null when nothing usable is configured, so callers can
 * render an empty pane rather than a broken image.
 */
function mws_image_url(?string $src): ?string
{
    $src = trim((string) $src);

    if ($src === '') {
        return null;
    }

    if (preg_match('~^(?:https?:)?//~i', $src) || stripos($src, 'data:') === 0) {
        return $src;
    }

    $file = get_template_directory() . '/' . ltrim($src, '/');

    return is_file($file) ? mws_asset_url($src) : null;
}

/**
 * Media pane attributes.
 *
 * Prints the resolved photo as a background image. With no image configured the
 * pane keeps its size and renders as a plain neutral surface (.is-empty), so the
 * layout holds until a URL is dropped into inc/config.php.
 */
function mws_media(?string $src, string $classes = ''): string
{
    $class = trim('media ' . $classes);
    $url   = mws_image_url($src);

    if ($url === null) {
        return 'class="' . mws_e($class . ' is-empty') . '"';
    }

    // Single-quoted CSS url(): backslash-escape quotes first, then escape the
    // whole declaration for the HTML attribute.
    $css = "background-image:url('" . addcslashes($url, "\\'\"\r\n") . "')";

    return 'class="' . mws_e($class) . '" style="' . mws_e($css) . '"';
}

/**
 * Tailwind class sets for buttons — keeps the markup readable and consistent.
 * Variants: primary | ghost (on photos) | outline (on dark) | outline-dark
 *
 * `btn-premium` carries the specular sheen defined in the premium layer of
 * src/css/main.css; it is inert on variants without a fill.
 */
function mws_btn(string $variant = 'primary', string $extra = ''): string
{
    $base = 'btn-premium inline-flex items-center justify-center gap-2 rounded-tl-2xl rounded-br-2xl px-6 py-3.5 text-btn '
          . 'font-extrabold uppercase leading-none whitespace-nowrap transition duration-200 '
          . 'hover:-translate-y-0.5 focus-visible:outline focus-visible:outline-2 '
          . 'focus-visible:outline-offset-2 focus-visible:outline-brand-600';

    $variants = [
        'primary'      => 'bg-brand-600 text-white shadow-glow hover:bg-brand-700',
        'ghost'        => 'bg-white text-ink hover:bg-brand-50',
        'outline'      => 'border border-white/30 text-white hover:border-white/60 hover:bg-white/10',
        'outline-dark' => 'border border-line text-ink hover:border-brand-200 hover:bg-sand',
        'dark'         => 'bg-ink text-white hover:bg-dark-soft',
        /* Financing only. Green so it never reads as a second brand action
           sitting next to the red call button. */
        'finance'      => 'bg-finance text-white hover:bg-finance-dark',
    ];

    return mws_e(trim($base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . $extra));
}

/**
 * Alternating section background.
 *
 * Every page is a stack of full-width sections, and two of the same colour
 * running together read as one long block — the reason for doing this at all.
 *
 * Hard-coding `bg-white` / `bg-sand` down a template looks fine until a section
 * is conditional: hide the sand one in the middle and the two white ones either
 * side merge. So the colour is decided at render time from whatever the previous
 * *rendered* section used, which means a skipped section never leaves two
 * matching neighbours.
 *
 * Call with no argument for the next colour in the alternation. Pass a colour to
 * set one explicitly — used by the dark panels, which need to both render dark
 * and tell the next section what it is following.
 *
 * State starts at `dark` because every page opens with the hero or the banner.
 */
function mws_section_bg(string $force = ''): string
{
    static $last = 'dark';

    if ($force !== '') {
        $last = $force;

        return 'bg-' . $force;
    }

    $last = ($last === 'white') ? 'sand' : 'white';

    return 'bg-' . $last;
}

/**
 * Typography for editor-managed content.
 *
 * Tailwind's preflight strips heading sizes and list markers, so anything
 * coming out of the WordPress editor needs them put back. Kept as one string
 * rather than a plugin so the styles match the hand-built sections exactly.
 */
function mws_prose(): string
{
    return 'text-body text-ink-muted '
         . '[&_h2]:mb-4 [&_h2]:mt-10 [&_h2]:text-h3 [&_h2]:text-ink '
         . '[&_h3]:mb-3 [&_h3]:mt-8 [&_h3]:text-h4 [&_h3]:text-ink '
         . '[&_h4]:mb-2 [&_h4]:mt-6 [&_h4]:text-h4 [&_h4]:text-ink '
         . '[&_p]:mb-5 '
         . '[&_ul]:mb-6 [&_ul]:list-disc [&_ul]:pl-6 '
         . '[&_ol]:mb-6 [&_ol]:list-decimal [&_ol]:pl-6 '
         . '[&_li]:mb-2 '
         . '[&_a]:font-semibold [&_a]:text-brand-600 [&_a:hover]:underline '
         . '[&_blockquote]:my-7 [&_blockquote]:border-l-2 [&_blockquote]:border-brand-600 '
         . '[&_blockquote]:pl-5 '
         . '[&_img]:rounded-tl-4xl [&_img]:rounded-br-4xl '
         . '[&_figure]:my-8';
}

/**
 * A post excerpt with the "TL;DR" label taken off the front.
 *
 * Editors open a summary with it out of habit, but a card in the journal grid
 * already reads as a summary — the label only spends one of the two lines the
 * card gives the text. The separator that follows it goes too, so the sentence
 * does not start on a stray colon or dash.
 *
 * Only the first occurrence is dropped, and only where it is punctuated like a
 * label rather than discussed as a term, so a post that is actually about the
 * abbreviation keeps its words.
 *
 * Returns raw text — escape it at the point of output.
 */
function mws_excerpt(string $text = ''): string
{
    if ($text === '') {
        $text = (string) get_the_excerpt();
    }

    $text = (string) preg_replace(
        '/^\s*TL\s*[;:]?\s*DR\b\s*[:;.\x{2014}\x{2013}-]*\s*/iu',
        '',
        $text,
        1
    );

    return trim($text);
}

/**
 * Build a table of contents out of rendered post content.
 *
 * The list is derived from the content itself rather than a field an editor has
 * to keep in sync: write an h2, get a row. Every heading it lists also gets an
 * id, so the returned HTML — not the original — is what the template must print,
 * otherwise the links point at anchors that were never emitted.
 *
 * An id the editor set by hand wins, because an existing link may already point
 * at it. Generated ids are de-duplicated, since two sections can legitimately
 * carry the same words.
 *
 * Returns ['items' => [['id' => string, 'text' => string], ...], 'content' => string].
 */
function mws_toc(string $html): array
{
    $items = [];
    $used  = [];

    $content = (string) preg_replace_callback(
        '#<h2\b([^>]*)>(.*?)</h2>#is',
        static function (array $match) use (&$items, &$used): string {
            $attrs = $match[1];
            $text  = trim(wp_strip_all_tags($match[2]));

            /* A heading holding only an image or an empty one has nothing to
               label a row with, so it is left exactly as the editor wrote it. */
            if ($text === '') {
                return $match[0];
            }

            if (preg_match('#\bid=["\']([^"\']+)["\']#i', $attrs, $existing)) {
                $id = $existing[1];
            } else {
                $base = sanitize_title($text) ?: 'section';
                $id   = $base;
                $n    = 2;

                while (isset($used[$id])) {
                    $id = $base . '-' . $n++;
                }

                $attrs .= ' id="' . esc_attr($id) . '"';
            }

            $used[$id] = true;
            $items[]   = ['id' => $id, 'text' => $text];

            return '<h2' . $attrs . '>' . $match[2] . '</h2>';
        },
        $html
    );

    return ['items' => $items, 'content' => $content];
}

/**
 * The figure-on-photo badge — a big number over a small caption.
 *
 * Used by the About page's split sections and by the home page's "Why choose
 * us?" block. It lives here rather than being written out twice so the two
 * cannot drift apart.
 *
 * Positioned absolutely, so the caller's wrapper needs `relative`.
 */
function mws_stat_badge(string $value, string $label, string $align = 'left'): string
{
    if (trim($value) === '') {
        return '';
    }

    $side = $align === 'right' ? 'right-6' : 'left-6';

    return '<div class="absolute -bottom-6 ' . $side . ' rounded-tl-4xl rounded-br-4xl bg-brand-600 '
         . 'px-7 py-5 text-white shadow-lift">'
         . '<strong class="block text-h2 leading-none">' . mws_e($value) . '</strong>'
         . '<span class="text-micro uppercase text-white/90">' . mws_e($label) . '</span>'
         . '</div>';
}

/** Small uppercase label with a leading dot, as used above every section title. */
function mws_eyebrow(string $text, bool $onDark = false): string
{
    $color = $onDark ? 'text-brand-200' : 'text-brand-600';

    return '<p class="mb-4 inline-flex items-center gap-2.5 text-eyebrow font-bold uppercase '
         . ' ' . $color . '">'
         . '<span class="h-1.5 w-1.5 rounded-full bg-current"></span>' . mws_e($text) . '</p>';
}

/**
 * Icon.
 *
 * The icons used to be inline `<svg>` markup. They are now real .svg files in
 * assets/icons/, applied as a CSS mask on an empty <span> — see the generated
 * src/css/icons.css. The mask is what lets a flat file still take its colour
 * from `currentColor`; an <img> would render a fixed black glyph and ignore the
 * `text-brand-400` style classes the markup relies on.
 *
 * Purely decorative, so it stays hidden from assistive tech — every caller
 * pairs it with a real label.
 */
function mws_icon(string $name, string $classes = 'icon'): string
{
    $name = in_array($name, mws_icon_names(), true) ? $name : 'arrow';

    return '<span class="mws-icon mws-icon--' . mws_e($name) . ' ' . mws_e($classes) . '" aria-hidden="true"></span>';
}

/** The available icon names, read from the files themselves so the two cannot drift. */
function mws_icon_names(): array
{
    static $names = null;

    if ($names === null) {
        $files = glob(get_template_directory() . '/assets/icons/*.svg') ?: [];
        $names = array_map(
            static fn(string $file): string => basename($file, '.svg'),
            $files
        );
    }

    return $names;
}

/**
 * Scroll-reveal attributes — now deliberately empty.
 *
 * This used to return `data-aos="fade-up"` with a staggered delay, and the home
 * page carried twenty-one of them: the same entrance on every section, card and
 * row. One identical reveal repeated down a page is not a motion design, it is
 * the absence of one — and it costs the reader, who meets each block as a
 * delay rather than as content that is simply there.
 *
 * The page now moves in one authored place instead: the hero settles once on
 * load (see `[data-hero-enter]` in src/css/main.css), and the hover layer
 * answers every link. Nothing animates on scroll.
 *
 * The function stays, returning nothing, because it is called from well over a
 * hundred places across every template. Keeping the call sites intact means the
 * decision lives in exactly one spot and can be reversed in exactly one spot.
 *
 * @param string $effect Ignored.
 * @param int    $index  Ignored.
 * @param int    $step   Ignored.
 * @param int    $cap    Ignored.
 */
function mws_aos(string $effect = 'fade-up', int $index = 0, int $step = 100, int $cap = 400): string
{
    return '';
}

/** "07:30" → minutes since midnight. */
function mws_hm_to_minutes(string $time): int
{
    [$h, $m] = array_pad(explode(':', $time, 2), 2, '0');
    return ((int) $h) * 60 + (int) $m;
}

/**
 * Is the business open at $now?
 *
 * $hours uses ISO weekdays (1 = Monday) and 24-hour "HH:MM" strings. The header
 * strip renders this server-side, then main.js re-evaluates it every second in
 * the business's own timezone so the badge stays truthful without a reload.
 */
function mws_business_is_open(DateTimeImmutable $now, array $hours): bool
{
    if (!empty($hours['always'])) {
        return true;
    }

    if (!in_array((int) $now->format('N'), $hours['days'] ?? [], true)) {
        return false;
    }

    $minutes = ((int) $now->format('G')) * 60 + (int) $now->format('i');

    return $minutes >= mws_hm_to_minutes($hours['from']) && $minutes < mws_hm_to_minutes($hours['to']);
}

/** Initials for the small avatar chips on testimonials. */
function mws_initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $out   = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $out .= mb_strtoupper(mb_substr($part, 0, 1));
    }
    return $out !== '' ? $out : '?';
}

/**
 * Handle a submitted form.
 *
 * Both the hero booking card and the footer newsletter post back to this page.
 * Returns the render state for whichever form was submitted.
 *
 * The nonce check is new: a theme template is a public endpoint, so without it
 * the lead log is writable by anyone who can POST to the front page.
 */
function mws_handle_submission(array $post, array $config): array
{
    $state = ['form' => null, 'ok' => false, 'error' => '', 'email' => '', 'name' => ''];

    $form = $post['form'] ?? null;
    if (!in_array($form, ['booking', 'newsletter'], true)) {
        return $state;
    }

    $state['form']  = $form;
    $state['email'] = trim((string) ($post['email'] ?? ''));
    $state['name']  = trim((string) ($post['name'] ?? ''));

    if (!isset($post['mws_nonce']) || !wp_verify_nonce($post['mws_nonce'], 'mws_lead')) {
        $state['error'] = 'Your session expired. Please try again.';
        return $state;
    }

    // Honeypot: real people never fill this in.
    if (trim((string) ($post['website'] ?? '')) !== '') {
        $state['ok'] = true;                 // Silently accept, store nothing.
        return $state;
    }

    if ($state['email'] === '') {
        $state['error'] = 'Please enter your email address.';
        return $state;
    }

    if (!is_email($state['email'])) {
        $state['error'] = 'That email address does not look right.';
        return $state;
    }

    mws_store_lead($form, $state, $config['leads'] ?? []);
    $state['ok'] = true;

    return $state;
}

/** Append the lead to a log file and optionally email it. */
function mws_store_lead(string $form, array $state, array $leads): void
{
    $line = sprintf(
        "%s\t%s\t%s\t%s\t%s\n",
        date('c'),
        $form,
        $state['email'],
        $state['name'] !== '' ? $state['name'] : '-',
        $_SERVER['REMOTE_ADDR'] ?? '-'
    );

    $logFile = $leads['log_file'] ?? null;
    if ($logFile) {
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }
        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }

    if (!empty($leads['send_mail']) && !empty($leads['notify_email'])) {
        wp_mail(
            $leads['notify_email'],
            'New website enquiry (' . $form . ')',
            "A new enquiry came in from the website:\n\n" . $line,
            ['Content-Type: text/plain; charset=UTF-8']
        );
    }
}

/**
 * The page config, loaded once and shared.
 *
 * Wrapped in a filter so a child theme or a plugin can adjust content without
 * editing inc/config.php.
 */
function mws_config(): array
{
    static $config = null;

    if ($config === null) {
        $config = apply_filters('mws_config', require get_template_directory() . '/inc/config.php');
    }

    return $config;
}
