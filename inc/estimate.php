<?php
/**
 * "Request an Estimate" — the site's primary conversion path.
 *
 * Every banner carries the button; the modal it opens is rendered once per page
 * in the footer. Inside the modal is a single slot:
 *
 *   • Jobber URL set (Appearance → Jobber & Trust) → the Jobber work request
 *     form is embedded in an iframe.
 *   • Not set → the built-in fallback form captures the lead on this site, so
 *     enquiries are never dropped while the integration is outstanding.
 *
 * The iframe is deliberately NOT in the initial HTML. Its src is swapped in the
 * first time the modal opens, so a third-party form on every page costs nothing
 * until a visitor actually asks for it.
 *
 * @package MWS
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * The estimate call to action.
 *
 * Rendered as a <button> rather than a link: it opens the modal, and if JS is
 * unavailable it falls back to the contact page via the form-action attribute
 * handled in main.js.
 */
function mws_estimate_button(string $variant = 'primary', string $extra = '', string $label = ''): string
{
    $label = $label !== '' ? $label : __('Request an Estimate', 'mws');

    return sprintf(
        '<button type="button" data-estimate-open class="%s">%s%s</button>',
        mws_btn($variant, $extra),
        mws_e($label),
        mws_icon('arrow', 'h-[18px] w-[18px]')
    );
}

/**
 * Trust badges — Google rating and BBB rating.
 *
 * Each carries its own brand treatment rather than adapting to the surrounding
 * section: Google on a light chip with dark text, BBB reversed out of its own
 * blue. Because both are self-contained they read correctly on the dark banner
 * and on a light sidebar without a variant for each.
 *
 * The marks are <img> rather than the usual CSS-mask icon. A mask is a single
 * alpha channel, which would flatten the four-colour Google G to one colour —
 * these are the two icons on the site that must keep their own palette.
 */
function mws_trust_badges(bool $onDark = true, string $extra = ''): string
{
    $badges = [
        [
            'label' => mws_setting('google_rating'),
            'url'   => mws_setting('google_url'),
            'file'  => 'assets/icons/brand-google.svg',
            'alt'   => 'Google',
            /* Light chip, dark text. */
            'shell' => 'border-black/5 bg-white text-ink shadow-soft hover:bg-sand',
        ],
        [
            'label' => mws_setting('bbb_rating'),
            'url'   => mws_setting('bbb_url'),
            'file'  => 'assets/icons/brand-bbb.svg',
            'alt'   => 'Better Business Bureau',
            /* BBB blue, light text. */
            'shell' => 'border-transparent bg-bbb text-white shadow-soft hover:bg-bbb-dark',
        ],
    ];

    $badges = array_filter($badges, static fn(array $b): bool => trim((string) $b['label']) !== '');

    if ($badges === []) {
        return '';
    }

    /* Stacked below sm. Side by side the pair is wider than a phone viewport,
       and inside the hero — which is overflow-hidden — the second chip was
       being clipped rather than wrapping. */
    $base = 'flex flex-col items-start gap-2.5 sm:flex-row sm:flex-wrap sm:items-center';

    $out = '<ul class="' . mws_e(trim($base . ' ' . $extra)) . '">';

    foreach ($badges as $badge) {
        /* <img> rather than the usual CSS-mask icon: a mask is a single alpha
           channel, which would flatten the four-colour Google G to one colour.
           These two are the only icons on the site that keep their own palette. */
        $inner = '<img src="' . mws_asset($badge['file']) . '" alt="' . mws_e($badge['alt']) . '" '
               . 'width="20" height="20" loading="lazy" decoding="async" class="h-5 w-5 flex-none">'
               . '<span>' . mws_e($badge['label']) . '</span>';

        $classes = 'inline-flex items-center gap-2.5 rounded-full border px-4 py-2.5 text-micro '
                 . 'font-bold uppercase transition ' . $badge['shell'];

        $out .= '<li>';
        $out .= $badge['url'] !== ''
            ? '<a href="' . esc_url($badge['url']) . '" target="_blank" rel="noopener noreferrer" class="' . mws_e($classes) . '">' . $inner . '</a>'
            : '<span class="' . mws_e($classes) . '">' . $inner . '</span>';
        $out .= '</li>';
    }

    return $out . '</ul>';
}

/**
 * The modal itself — printed once per page, just before wp_footer().
 */
function mws_estimate_modal(): void
{
    $ready  = mws_jobber_ready();
    $height = (int) mws_setting('jobber_height', 900);
    $sent   = isset($_GET['estimate']) && $_GET['estimate'] === 'sent';
    ?>
    <div data-estimate-modal hidden
        class="fixed inset-0 z-[120] flex items-start justify-center overflow-y-auto p-4 py-10 sm:p-6 sm:py-12">

        <div data-estimate-close aria-hidden="true"
            class="fixed inset-0 bg-ink/70 opacity-0 backdrop-blur-sm transition-opacity duration-400 [[data-estimate-modal].is-open_&]:opacity-100"></div>

        <div role="dialog" aria-modal="true" aria-labelledby="mws-estimate-title"
            class="relative z-10 w-full max-w-[680px] translate-y-4 scale-[.98] rounded-tl-4xl rounded-br-4xl bg-white opacity-0 shadow-lift transition duration-400 [[data-estimate-modal].is-open_&]:translate-y-0 [[data-estimate-modal].is-open_&]:scale-100 [[data-estimate-modal].is-open_&]:opacity-100">

            <div class="flex items-start justify-between gap-6 border-b border-line px-7 pb-5 pt-7 sm:px-9">
                <div>
                    <?= mws_eyebrow(__('Free · No obligation', 'mws')) ?>
                    <h2 id="mws-estimate-title" class="text-h3 text-ink">
                        <?php esc_html_e('Request an Estimate', 'mws'); ?>
                    </h2>
                </div>
                <button type="button" data-estimate-close aria-label="<?php esc_attr_e('Close', 'mws'); ?>"
                    class="grid h-11 w-11 flex-none place-items-center rounded-tl-2xl rounded-br-2xl border border-line text-ink transition hover:border-brand-200 hover:bg-sand">
                    <?= mws_icon('close', 'h-5 w-5') ?>
                </button>
            </div>

            <div class="px-7 py-7 sm:px-9">
                <?php if ($sent) : ?>
                    <div class="rounded-tl-2xl rounded-br-2xl border border-brand-100 bg-brand-50 p-6 text-center">
                        <span class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-full bg-brand-600 text-white">
                            <?= mws_icon('check', 'h-6 w-6') ?>
                        </span>
                        <h3 class="mb-2 text-h4 text-ink"><?php esc_html_e('Thanks — we have your request.', 'mws'); ?></h3>
                        <p class="text-body text-ink-muted">
                            <?php esc_html_e('We reply within one business day with next steps and a time for your free on-site estimate.', 'mws'); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if ($ready) : ?>
                    <?php /* ── Jobber slot ─────────────────────────────────────────
                             src is empty on purpose: main.js copies data-src into it
                             the first time the modal opens, so the third-party form
                             is not fetched on page load. */ ?>
                    <iframe data-estimate-frame
                        data-src="<?= esc_url(mws_setting('jobber_url')) ?>"
                        title="<?php esc_attr_e('Request an estimate', 'mws'); ?>"
                        loading="lazy"
                        style="height: <?= esc_attr((string) $height) ?>px"
                        class="w-full rounded-tl-2xl rounded-br-2xl border-0"></iframe>
                <?php else : ?>
                    <?php /* Jobber not wired up yet, so Contact Form 7 takes the slot. */ ?>
                    <?php mws_form('estimate'); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Fallback lead form, used until the Jobber URL is configured.
 *
 * Posts to admin-post.php rather than the current URL so the handler runs
 * before any output and can redirect cleanly (no "resubmit form?" prompt on
 * refresh).
 */
function mws_estimate_fallback_form(): void
{
    $field = 'w-full rounded-tl-2xl rounded-br-2xl border-line bg-sand text-body text-ink '
           . 'placeholder:text-ink-muted/70 focus:border-brand-600 focus:ring-brand-600';
    $label = 'mb-1.5 block text-micro font-bold uppercase text-ink';
    ?>
    <?php if (current_user_can('manage_options')) : ?>
        <p class="mb-6 rounded-tl-2xl rounded-br-2xl border border-brand-200 bg-brand-50 p-4 text-small text-ink-soft">
            <strong><?php esc_html_e('Admin note:', 'mws'); ?></strong>
            <?php
            printf(
                /* translators: %s: link to the theme settings screen */
                esc_html__('Jobber is not connected yet, so this fallback form is showing. Add the work request URL in %s and it will be replaced by your Jobber form.', 'mws'),
                '<a class="font-semibold text-brand-600 underline" href="' . esc_url(admin_url('themes.php?page=' . MWS_SETTINGS)) . '">' . esc_html__('Appearance → Jobber &amp; Trust', 'mws') . '</a>'
            );
            ?>
        </p>
    <?php endif; ?>

    <form action="<?= esc_url(admin_url('admin-post.php')) ?>" method="post" class="grid gap-5">
        <input type="hidden" name="action" value="mws_estimate">
        <input type="hidden" name="redirect_to" value="<?= esc_url(mws_current_url()) ?>">
        <?php wp_nonce_field('mws_estimate', 'mws_nonce'); ?>

        <?php /* Honeypot: off-screen rather than display:none, which some bots skip. */ ?>
        <div class="absolute left-[-9999px]" aria-hidden="true">
            <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="<?= $label ?>" for="mws-name"><?php esc_html_e('Name', 'mws'); ?></label>
                <input class="<?= $field ?>" type="text" id="mws-name" name="name" required autocomplete="name">
            </div>
            <div>
                <label class="<?= $label ?>" for="mws-phone"><?php esc_html_e('Phone', 'mws'); ?></label>
                <input class="<?= $field ?>" type="tel" id="mws-phone" name="phone" required autocomplete="tel">
            </div>
        </div>

        <div>
            <label class="<?= $label ?>" for="mws-email"><?php esc_html_e('Email', 'mws'); ?></label>
            <input class="<?= $field ?>" type="email" id="mws-email" name="email" required autocomplete="email">
        </div>

        <div>
            <label class="<?= $label ?>" for="mws-service"><?php esc_html_e('What do you need?', 'mws'); ?></label>
            <select class="<?= $field ?>" id="mws-service" name="service">
                <option value=""><?php esc_html_e('Select a service…', 'mws'); ?></option>
                <?php foreach (mws_services() as $service) : ?>
                    <option value="<?= esc_attr($service->post_title) ?>"><?= esc_html($service->post_title) ?></option>
                <?php endforeach; ?>
                <option value="Something else"><?php esc_html_e('Something else', 'mws'); ?></option>
            </select>
        </div>

        <div>
            <label class="<?= $label ?>" for="mws-message"><?php esc_html_e('Tell us about the project', 'mws'); ?></label>
            <textarea class="<?= $field ?>" id="mws-message" name="message" rows="4"></textarea>
        </div>

        <button type="submit" class="<?= mws_btn('primary', 'w-full') ?>">
            <?php esc_html_e('Send my request', 'mws'); ?>
        </button>

        <p class="text-micro text-ink-muted">
            <?php esc_html_e('We reply within one business day. Your details are never shared.', 'mws'); ?>
        </p>
    </form>
    <?php
}

/** Current front-end URL, used to send the visitor back after posting. */
function mws_current_url(): string
{
    $home = home_url('/');
    $path = (string) ($_SERVER['REQUEST_URI'] ?? '');

    return $path !== '' ? home_url($path) : $home;
}

/**
 * Handle the fallback form.
 *
 * Registered for both logged-in and logged-out visitors — admin-post.php only
 * runs the _nopriv_ variant for the latter.
 */
function mws_handle_estimate(): void
{
    $redirect = wp_validate_redirect((string) ($_POST['redirect_to'] ?? ''), home_url('/'));

    if (!isset($_POST['mws_nonce']) || !wp_verify_nonce($_POST['mws_nonce'], 'mws_estimate')) {
        wp_safe_redirect(add_query_arg('estimate', 'error', $redirect));
        exit;
    }

    // Honeypot: accept silently, store nothing.
    if (trim((string) ($_POST['website'] ?? '')) !== '') {
        wp_safe_redirect(add_query_arg('estimate', 'sent', $redirect));
        exit;
    }

    $lead = [
        'name'    => sanitize_text_field((string) ($_POST['name'] ?? '')),
        'phone'   => sanitize_text_field((string) ($_POST['phone'] ?? '')),
        'email'   => sanitize_email((string) ($_POST['email'] ?? '')),
        'service' => sanitize_text_field((string) ($_POST['service'] ?? '')),
        'message' => sanitize_textarea_field((string) ($_POST['message'] ?? '')),
    ];

    if ($lead['email'] === '' || !is_email($lead['email'])) {
        wp_safe_redirect(add_query_arg('estimate', 'error', $redirect));
        exit;
    }

    mws_store_estimate($lead);

    wp_safe_redirect(add_query_arg('estimate', 'sent', $redirect));
    exit;
}
add_action('admin_post_mws_estimate', 'mws_handle_estimate');
add_action('admin_post_nopriv_mws_estimate', 'mws_handle_estimate');

/** Append the lead to the log and notify by email when a mailer is configured. */
function mws_store_estimate(array $lead): void
{
    $config = mws_config();
    $leads  = $config['leads'] ?? [];

    $line = sprintf(
        "%s\testimate\t%s\t%s\t%s\t%s\t%s\t%s\n",
        date('c'),
        $lead['name'] !== '' ? $lead['name'] : '-',
        $lead['email'],
        $lead['phone'] !== '' ? $lead['phone'] : '-',
        $lead['service'] !== '' ? $lead['service'] : '-',
        str_replace(["\r", "\n", "\t"], ' ', $lead['message']) ?: '-',
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
        $body = sprintf(
            "New estimate request from the website:\n\nName: %s\nPhone: %s\nEmail: %s\nService: %s\n\n%s\n",
            $lead['name'],
            $lead['phone'],
            $lead['email'],
            $lead['service'],
            $lead['message']
        );

        wp_mail(
            $leads['notify_email'],
            'New estimate request — ' . ($lead['name'] !== '' ? $lead['name'] : $lead['email']),
            $body,
            ['Content-Type: text/plain; charset=UTF-8']
        );
    }
}
