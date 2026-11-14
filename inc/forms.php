<?php
/**
 * Contact Form 7 integration.
 *
 * CF7 is the interim form engine while the Jobber embed is outstanding: where a
 * Jobber URL is configured the iframe still wins, and CF7 fills every other
 * slot. That keeps both routes live rather than replacing one with the other.
 *
 * Two things CF7 does not do on its own and are handled here:
 *
 *   1. Redirect after a successful send. CF7 deliberately dropped on_sent_ok,
 *      so the supported route is the `wpcf7mailsent` DOM event — see the
 *      listener in src/js/main.js. The destination is printed as a data
 *      attribute on the wrapper so the JS never has to know any URLs.
 *   2. Styling. CF7 emits its own markup, so the theme's field styles are
 *      applied from src/css/main.css rather than by rewriting its output.
 *
 * @package MWS
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** Is Contact Form 7 available? */
function mws_cf7_active(): bool
{
    return defined('WPCF7_VERSION') || class_exists('WPCF7_ContactForm');
}

/**
 * Render one of the theme's forms.
 *
 * @param string $slug 'estimate' | 'contact' | 'careers'
 */
function mws_form(string $slug): void
{
    $formId = 'mws-' . $slug;

    if (!mws_cf7_active()) {
        if (current_user_can('manage_options')) {
            printf(
                '<p class="rounded-tl-2xl rounded-br-2xl border border-brand-200 bg-brand-50 p-4 text-small text-ink-soft">%s</p>',
                esc_html__('Contact Form 7 is not active, so this form cannot render. Activate it under Plugins.', 'mws')
            );
        }
        return;
    }

    /* CF7 resolves the shortcode's `id` against the numeric post ID (or its own
       hash) — a post slug silently renders nothing, which is exactly what
       happened first time round. So the slug is looked up here and the real ID
       is what goes into the shortcode. */
    $form = get_posts([
        'post_type'      => 'wpcf7_contact_form',
        'name'           => $formId,
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
    ]);

    if (!$form) {
        if (current_user_can('manage_options')) {
            printf(
                '<p class="rounded-tl-2xl rounded-br-2xl border border-brand-200 bg-brand-50 p-4 text-small text-ink-soft">%s</p>',
                esc_html(sprintf(
                    /* translators: %s: form slug */
                    __('Contact form "%s" is missing. Re-create it under Contact → Contact Forms.', 'mws'),
                    $formId
                ))
            );
        }
        return;
    }

    printf(
        '<div class="mws-cf7" data-cf7-redirect="%s">%s</div>',
        esc_url(mws_thank_you_url($slug)),
        do_shortcode(sprintf('[contact-form-7 id="%d"]', (int) $form[0]))
    );
}

/** Where a given form sends people once it has sent successfully. */
function mws_thank_you_url(string $slug): string
{
    $page = get_page_by_path('thank-you');

    $base = $page ? (string) get_permalink($page) : home_url('/');

    return add_query_arg('form', $slug, $base);
}

/**
 * CF7 loads its CSS and JS on every page by default. The forms only appear on a
 * handful of views plus the estimate modal, so everywhere else that is two
 * requests for nothing.
 *
 * The modal is rendered in the footer of every page, so the check is "does this
 * view have a form at all" rather than a page whitelist.
 */
function mws_cf7_conditional_assets(): void
{
    if (!mws_cf7_active()) {
        return;
    }

    /* The estimate modal carries a form on every page unless Jobber is wired
       up, in which case only the dedicated pages need CF7. */
    $needed = !mws_jobber_ready()
        || is_page_template(['template-contact.php', 'template-careers.php'])
        || is_page(['contact-us', 'careers', 'request-an-estimate']);

    if ($needed) {
        return;
    }

    wp_dequeue_style('contact-form-7');
    wp_dequeue_script('contact-form-7');
}
add_action('wp_enqueue_scripts', 'mws_cf7_conditional_assets', 100);
