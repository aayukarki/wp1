<?php
/**
 * Theme settings — Appearance → Jobber & Trust.
 *
 * ACF's free tier has no options page (that is a PRO feature), so this uses the
 * WordPress Settings API directly. It has no plugin dependency at all, which
 * also means the Jobber embed survives ACF being deactivated.
 *
 * The one field that matters is the Jobber work request URL. Until it is
 * filled in, the estimate modal falls back to capturing the lead locally so
 * nothing is lost — see inc/estimate.php.
 *
 * @package MWS
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const MWS_SETTINGS = 'mws_settings';

/** All settings, with defaults applied. */
function mws_settings(): array
{
    static $settings = null;

    if ($settings === null) {
        $settings = wp_parse_args((array) get_option(MWS_SETTINGS, []), [
            'jobber_url'      => '',
            'jobber_height'   => 900,
            'google_rating'   => '5-Star Rated on Google',
            'google_url'      => '',
            'bbb_rating'      => 'BBB Rating A+',
            'bbb_url'         => '',
            'financing_url'   => '',
            'promo_enabled'   => '1',
            'promo_image'     => '',
            'promo_url'       => '',
            'promo_alt'       => 'Accepting new commercial snow & ice removal clients for the 2026/2027 seasons',
            'promo_delay'     => 1200,
        ]);
    }

    return $settings;
}

/** A single setting. */
function mws_setting(string $key, $default = '')
{
    $settings = mws_settings();

    return $settings[$key] ?? $default;
}

/** Is the Jobber form configured? */
function mws_jobber_ready(): bool
{
    return mws_setting('jobber_url') !== '';
}

function mws_register_settings(): void
{
    register_setting(MWS_SETTINGS, MWS_SETTINGS, [
        'type'              => 'array',
        'sanitize_callback' => 'mws_sanitize_settings',
        'default'           => [],
    ]);

    add_settings_section(
        'mws_jobber',
        __('Jobber request form', 'mws'),
        static function (): void {
            echo '<p>' . esc_html__(
                'Paste the public Jobber work request URL. It is embedded in the "Request an Estimate" modal on every banner. Until it is set, the modal captures the lead on this site instead, so no enquiry is lost.',
                'mws'
            ) . '</p>';
            echo '<p><code>https://clienthub.getjobber.com/client_hubs/&lt;id&gt;/public/work_request/new</code></p>';
        },
        MWS_SETTINGS
    );

    add_settings_field('jobber_url', __('Work request URL', 'mws'), 'mws_field_text', MWS_SETTINGS, 'mws_jobber', [
        'key'         => 'jobber_url',
        'type'        => 'url',
        'class'       => 'regular-text code',
        'placeholder' => 'https://clienthub.getjobber.com/client_hubs/…/public/work_request/new',
        'description' => __('Leave empty to keep using the built-in fallback form.', 'mws'),
    ]);

    add_settings_field('jobber_height', __('Embed height (px)', 'mws'), 'mws_field_text', MWS_SETTINGS, 'mws_jobber', [
        'key'         => 'jobber_height',
        'type'        => 'number',
        'class'       => 'small-text',
        'description' => __('How tall the embedded form iframe should be. 900 suits most Jobber forms.', 'mws'),
    ]);

    add_settings_section(
        'mws_trust',
        __('Trust badges', 'mws'),
        static function (): void {
            echo '<p>' . esc_html__('Shown in every page banner beside the estimate button. Clear a label to hide that badge.', 'mws') . '</p>';
        },
        MWS_SETTINGS
    );

    add_settings_field('google_rating', __('Google badge', 'mws'), 'mws_field_text', MWS_SETTINGS, 'mws_trust', [
        'key'   => 'google_rating',
        'class' => 'regular-text',
    ]);

    add_settings_field('google_url', __('Google reviews link', 'mws'), 'mws_field_text', MWS_SETTINGS, 'mws_trust', [
        'key'   => 'google_url',
        'type'  => 'url',
        'class' => 'regular-text code',
    ]);

    add_settings_field('bbb_rating', __('BBB badge', 'mws'), 'mws_field_text', MWS_SETTINGS, 'mws_trust', [
        'key'   => 'bbb_rating',
        'class' => 'regular-text',
    ]);

    add_settings_field('bbb_url', __('BBB profile link', 'mws'), 'mws_field_text', MWS_SETTINGS, 'mws_trust', [
        'key'   => 'bbb_url',
        'type'  => 'url',
        'class' => 'regular-text code',
    ]);

    add_settings_section(
        'mws_promo',
        __('Promotion pop-up', 'mws'),
        static function (): void {
            echo '<p>' . esc_html__(
                'A single image that opens over the page shortly after it loads. It shows once per browser session — closing it, or moving to another page, does not bring it back until the visitor returns fresh.',
                'mws'
            ) . '</p>';
        },
        MWS_SETTINGS
    );

    add_settings_field('promo_enabled', __('Show the pop-up', 'mws'), 'mws_field_checkbox', MWS_SETTINGS, 'mws_promo', [
        'key'        => 'promo_enabled',
        'label_text' => __('Enabled', 'mws'),
    ]);

    add_settings_field('promo_image', __('Image URL', 'mws'), 'mws_field_text', MWS_SETTINGS, 'mws_promo', [
        'key'         => 'promo_image',
        'type'        => 'url',
        'class'       => 'regular-text code',
        'description' => __('Leave empty to use the snow &amp; ice offer bundled with the theme.', 'mws'),
    ]);

    add_settings_field('promo_url', __('Links to', 'mws'), 'mws_field_text', MWS_SETTINGS, 'mws_promo', [
        'key'         => 'promo_url',
        'type'        => 'url',
        'class'       => 'regular-text code',
        'description' => __('Optional. Where clicking the image takes the visitor. Empty means the estimate form opens instead.', 'mws'),
    ]);

    add_settings_field('promo_alt', __('Image description', 'mws'), 'mws_field_text', MWS_SETTINGS, 'mws_promo', [
        'key'         => 'promo_alt',
        'class'       => 'large-text',
        'description' => __('Read aloud by screen readers — describe the offer in words.', 'mws'),
    ]);

    add_settings_field('promo_delay', __('Delay (ms)', 'mws'), 'mws_field_text', MWS_SETTINGS, 'mws_promo', [
        'key'         => 'promo_delay',
        'type'        => 'number',
        'class'       => 'small-text',
        'description' => __('How long after load before it opens. 1200 gives the page time to paint first.', 'mws'),
    ]);

    add_settings_field('financing_url', __('Financing link', 'mws'), 'mws_field_text', MWS_SETTINGS, 'mws_trust', [
        'key'         => 'financing_url',
        'type'        => 'url',
        'class'       => 'regular-text code',
        'description' => __('Used by the financing band further down the home page. Falls back to the phone number.', 'mws'),
    ]);
}
add_action('admin_init', 'mws_register_settings');

/** Checkbox renderer. */
function mws_field_checkbox(array $args): void
{
    $settings = mws_settings();
    $key      = $args['key'];

    printf(
        '<label><input type="checkbox" id="%s" name="%s[%s]" value="1" %s> %s</label>',
        esc_attr($key),
        esc_attr(MWS_SETTINGS),
        esc_attr($key),
        checked(!empty($settings[$key]), true, false),
        esc_html($args['label_text'] ?? '')
    );

    if (!empty($args['description'])) {
        printf('<p class="description">%s</p>', esc_html($args['description']));
    }
}

/** Generic text/url/number field renderer. */
function mws_field_text(array $args): void
{
    $settings = mws_settings();
    $key      = $args['key'];
    $type     = $args['type'] ?? 'text';

    printf(
        '<input type="%s" id="%s" name="%s[%s]" value="%s" class="%s" placeholder="%s">',
        esc_attr($type),
        esc_attr($key),
        esc_attr(MWS_SETTINGS),
        esc_attr($key),
        esc_attr((string) ($settings[$key] ?? '')),
        esc_attr($args['class'] ?? 'regular-text'),
        esc_attr($args['placeholder'] ?? '')
    );

    if (!empty($args['description'])) {
        printf('<p class="description">%s</p>', esc_html($args['description']));
    }
}

/** Sanitise on save. */
function mws_sanitize_settings($input): array
{
    $input = (array) $input;

    return [
        'jobber_url'    => esc_url_raw(trim((string) ($input['jobber_url'] ?? ''))),
        'jobber_height' => max(400, min(2000, (int) ($input['jobber_height'] ?? 900))),
        'google_rating' => sanitize_text_field((string) ($input['google_rating'] ?? '')),
        'google_url'    => esc_url_raw(trim((string) ($input['google_url'] ?? ''))),
        'bbb_rating'    => sanitize_text_field((string) ($input['bbb_rating'] ?? '')),
        'bbb_url'       => esc_url_raw(trim((string) ($input['bbb_url'] ?? ''))),
        'financing_url' => esc_url_raw(trim((string) ($input['financing_url'] ?? ''))),
        'promo_enabled' => empty($input['promo_enabled']) ? '' : '1',
        'promo_image'   => esc_url_raw(trim((string) ($input['promo_image'] ?? ''))),
        'promo_url'     => esc_url_raw(trim((string) ($input['promo_url'] ?? ''))),
        'promo_alt'     => sanitize_text_field((string) ($input['promo_alt'] ?? '')),
        'promo_delay'   => max(0, min(15000, (int) ($input['promo_delay'] ?? 1200))),
    ];
}

function mws_settings_page(): void
{
    add_theme_page(
        __('Jobber & Trust', 'mws'),
        __('Jobber & Trust', 'mws'),
        'manage_options',
        MWS_SETTINGS,
        'mws_render_settings_page'
    );
}
add_action('admin_menu', 'mws_settings_page');

function mws_render_settings_page(): void
{
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <?php if (!mws_jobber_ready()) : ?>
            <div class="notice notice-warning inline">
                <p><strong><?php esc_html_e('Jobber is not connected yet.', 'mws'); ?></strong>
                <?php esc_html_e('The estimate modal is currently capturing leads on this site. Paste the work request URL below to switch it over.', 'mws'); ?></p>
            </div>
        <?php else : ?>
            <div class="notice notice-success inline">
                <p><strong><?php esc_html_e('Jobber is connected.', 'mws'); ?></strong>
                <?php esc_html_e('The estimate modal is embedding your Jobber request form.', 'mws'); ?></p>
            </div>
        <?php endif; ?>

        <form action="options.php" method="post">
            <?php
            settings_fields(MWS_SETTINGS);
            do_settings_sections(MWS_SETTINGS);
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
