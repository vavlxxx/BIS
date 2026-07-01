<?php

require_once get_template_directory() . '/inc/performance.php';
require_once get_template_directory() . '/inc/query-helpers.php';
require_once get_template_directory() . '/inc/content-helpers.php';
require_once get_template_directory() . '/inc/admin-tools.php';
require_once get_template_directory() . '/inc/request-handlers.php';
require_once get_template_directory() . '/inc/media.php';
require_once get_template_directory() . '/inc/content-models.php';
require_once get_template_directory() . '/inc/content-overrides.php';

function bis_theme_scripts() {
    // Enqueue Google Fonts
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap', array(), null);

    $css_files = array(
        'bis-base'       => 'assets/css/base.css',
        'bis-front-page' => 'assets/css/front-page.css',
        'bis-news'       => 'assets/css/news.css',
        'bis-team'       => 'assets/css/team.css',
        'bis-content'    => 'assets/css/content.css',
    );

    $style_deps = array();
    foreach ($css_files as $handle => $file) {
        wp_enqueue_style($handle, get_template_directory_uri() . '/' . $file, $style_deps, bis_get_asset_version($file));
        $style_deps[] = $handle;
    }

    wp_enqueue_script('bis-site-forms', get_template_directory_uri() . '/assets/js/site-forms.js', array(), bis_get_asset_version('assets/js/site-forms.js'), true);
    wp_localize_script('bis-site-forms', 'bisSiteConfig', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'locationCookieName' => 'bis_user_location',
        'locationFallback' => bis_get_location_placeholder(),
        'hcaptchaSiteKey' => bis_get_hcaptcha_settings()['site_key'],
    ));

    wp_enqueue_script('bis-site-navigation', get_template_directory_uri() . '/assets/js/site-navigation.js', array(), bis_get_asset_version('assets/js/site-navigation.js'), true);
    wp_enqueue_script('bis-site-home', get_template_directory_uri() . '/assets/js/site-home.js', array('bis-site-forms'), bis_get_asset_version('assets/js/site-home.js'), true);
    wp_enqueue_script('bis-site-team', get_template_directory_uri() . '/assets/js/site-team.js', array(), bis_get_asset_version('assets/js/site-team.js'), true);
    wp_enqueue_script('bis-site-project', get_template_directory_uri() . '/assets/js/site-project.js', array('bis-site-forms'), bis_get_asset_version('assets/js/site-project.js'), true);
    wp_enqueue_script('bis-site-app', get_template_directory_uri() . '/assets/js/site-app.js', array('bis-site-forms', 'bis-site-navigation', 'bis-site-home', 'bis-site-team', 'bis-site-project'), bis_get_asset_version('assets/js/site-app.js'), true);

    // Enqueue Slider Script
    if (is_front_page()) {
        wp_enqueue_script('bis-slider', get_template_directory_uri() . '/assets/js/slider.js', array(), bis_get_asset_version('assets/js/slider.js'), true);
        wp_enqueue_script('yandex-maps-api', 'https://api-maps.yandex.ru/2.1/?lang=ru_RU', array(), null, true);
        wp_enqueue_script('bis-objects-map', get_template_directory_uri() . '/assets/js/objects-map.js', array('yandex-maps-api'), bis_get_asset_version('assets/js/objects-map.js'), true);

        $revenue = bis_get_revenue_settings();
        $revenue_points = array();

        if (!empty($revenue['points']) && is_array($revenue['points'])) {
            foreach ($revenue['points'] as $point) {
                if (!isset($point['label'], $point['value'])) {
                    continue;
                }
                $label = sanitize_text_field($point['label']);
                $value = floatval($point['value']);
                if ($label === '') {
                    continue;
                }
                $revenue_points[] = array(
                    'label' => $label,
                    'value' => $value,
                );
            }
        }

        $revenue['points'] = $revenue_points;
        wp_localize_script('bis-site-home', 'bisRevenueData', $revenue);
    }
}
add_action('wp_enqueue_scripts', 'bis_theme_scripts');

function bis_get_runtime_setting($key, $default = '') {
    $env_value = getenv($key);
    if ($env_value !== false && $env_value !== '') {
        return $env_value;
    }

    if (defined($key)) {
        $constant_value = constant($key);
        if ($constant_value !== null && $constant_value !== '') {
            return $constant_value;
        }
    }

    return $default;
}

function bis_get_dadata_settings() {
    return array(
        'api_key' => trim((string) bis_get_runtime_setting('BIS_DADATA_API_KEY')),
        'secret_key' => trim((string) bis_get_runtime_setting('BIS_DADATA_SECRET_KEY')),
    );
}

function bis_get_hcaptcha_settings() {
    $plugin_settings = get_option('hcaptcha_settings', array());
    $plugin_site_key = is_array($plugin_settings) && !empty($plugin_settings['api_key'])
        ? trim((string) $plugin_settings['api_key'])
        : '';
    $plugin_secret_key = is_array($plugin_settings) && !empty($plugin_settings['secret_key'])
        ? trim((string) $plugin_settings['secret_key'])
        : '';

    return array(
        'site_key' => trim((string) bis_get_runtime_setting('BIS_HCAPTCHA_SITE_KEY', $plugin_site_key)),
        'secret_key' => trim((string) bis_get_runtime_setting('BIS_HCAPTCHA_SECRET_KEY', $plugin_secret_key)),
    );
}

function bis_is_hcaptcha_configured() {
    $settings = bis_get_hcaptcha_settings();

    return $settings['site_key'] !== '' && $settings['secret_key'] !== '';
}

function bis_get_smtp_settings() {
    return array(
        'host'      => trim((string) bis_get_runtime_setting('BIS_SMTP_HOST')),
        'port'      => (int) bis_get_runtime_setting('BIS_SMTP_PORT'),
        'user'      => trim((string) bis_get_runtime_setting('BIS_SMTP_USER')),
        'pass'      => trim((string) bis_get_runtime_setting('BIS_SMTP_PASS')),
        'secure'    => trim((string) bis_get_runtime_setting('BIS_SMTP_SECURE')),
        'from'      => trim((string) bis_get_runtime_setting('BIS_SMTP_FROM_EMAIL')),
        'from_name' => trim((string) bis_get_runtime_setting('BIS_SMTP_FROM_NAME')),
        'auth'      => strtolower(trim((string) bis_get_runtime_setting('BIS_SMTP_AUTH'))),
    );
}

function bis_is_wp_mail_smtp_active() {
    return defined('WPMS_PLUGIN_VER') || class_exists('\WPMailSMTP\WP');
}

function bis_configure_phpmailer($phpmailer) {
    if (bis_is_wp_mail_smtp_active()) {
        return;
    }

    $settings = bis_get_smtp_settings();

    if ($settings['host'] === '' || $settings['from'] === '') {
        return;
    }

    $phpmailer->isSMTP();
    $phpmailer->Host = $settings['host'];
    $phpmailer->Port = $settings['port'] > 0 ? $settings['port'] : 465;
    $phpmailer->SMTPAuth = $settings['auth'] !== 'false';
    $phpmailer->Username = $settings['user'] !== '' ? $settings['user'] : $settings['from'];
    $phpmailer->Password = $settings['pass'];
    $phpmailer->CharSet = 'UTF-8';

    if (in_array($settings['secure'], array('ssl', 'tls'), true)) {
        $phpmailer->SMTPSecure = $settings['secure'];
    } else {
        $phpmailer->SMTPSecure = '';
    }

    $phpmailer->From = $settings['from'];
    $phpmailer->FromName = $settings['from_name'] !== '' ? $settings['from_name'] : get_bloginfo('name');
}
add_action('phpmailer_init', 'bis_configure_phpmailer');

function bis_smtp_from_email($email) {
    if (bis_is_wp_mail_smtp_active()) {
        return $email;
    }

    $settings = bis_get_smtp_settings();
    return $settings['from'] !== '' ? $settings['from'] : $email;
}
add_filter('wp_mail_from', 'bis_smtp_from_email');

function bis_smtp_from_name($name) {
    if (bis_is_wp_mail_smtp_active()) {
        return $name;
    }

    $settings = bis_get_smtp_settings();
    return $settings['from_name'] !== '' ? $settings['from_name'] : $name;
}
add_filter('wp_mail_from_name', 'bis_smtp_from_name');

function bis_theme_setup() {
    // add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
}
add_action('after_setup_theme', 'bis_theme_setup');

function bis_get_seo_enabled_post_types() {
    return array('bis_project', 'bis_service');
}

function bis_get_post_seo_title($post_id) {
    if (!in_array(get_post_type($post_id), bis_get_seo_enabled_post_types(), true)) {
        return '';
    }

    return trim((string) get_post_meta($post_id, 'bis_seo_title', true));
}

function bis_get_post_seo_description($post_id) {
    if (!in_array(get_post_type($post_id), bis_get_seo_enabled_post_types(), true)) {
        return '';
    }

    return trim((string) get_post_meta($post_id, 'bis_seo_description', true));
}

function bis_filter_document_title($title) {
    if (!is_singular(bis_get_seo_enabled_post_types())) {
        return $title;
    }

    $seo_title = bis_get_post_seo_title(get_queried_object_id());
    return $seo_title !== '' ? $seo_title : $title;
}
add_filter('pre_get_document_title', 'bis_filter_document_title', 20);

function bis_is_project_single_page() {
    return is_singular('bis_project');
}

function bis_noindex_project_single_pages($robots) {
    if (!bis_is_project_single_page()) {
        return $robots;
    }

    $robots['noindex'] = false;
    $robots['follow'] = true;
    unset($robots['index'], $robots['nofollow']);

    return $robots;
}
add_filter('wp_robots', 'bis_noindex_project_single_pages');

function bis_get_current_meta_description() {
    $description = '';

    if (is_singular()) {
        $post = get_queried_object();

        if ($post instanceof WP_Post) {
            $description = bis_get_post_seo_description($post->ID);

            if ($description === '') {
                $excerpt = has_excerpt($post) ? get_the_excerpt($post) : wp_strip_all_tags(get_the_content(null, false, $post));
                if (!empty($excerpt)) {
                    $description = wp_trim_words($excerpt, 30, '...');
                }
            }
        }
    }

    if ($description === '') {
        $description = get_bloginfo('description');
    }

    if ($description === '') {
        $description = 'БИС: комплексные пусконаладочные работы, техническое обслуживание и сопровождение инженерных систем';
    }

    return $description;
}

function bis_get_social_share_image_url() {
    if (is_singular() && has_post_thumbnail()) {
        $thumbnail_url = get_the_post_thumbnail_url(get_queried_object_id(), 'large');
        if ($thumbnail_url) {
            return $thumbnail_url;
        }
    }

    return get_template_directory_uri() . '/assets/img/bis-black.png';
}

function bis_output_social_meta_tags() {
    if (is_admin() || is_feed() || is_robots() || is_trackback()) {
        return;
    }

    $title = wp_get_document_title();
    $description = bis_get_current_meta_description();

    $url = home_url(add_query_arg(array(), $GLOBALS['wp']->request ?? ''));
    if (is_singular()) {
        $url = get_permalink();
    }
    if (empty($url)) {
        $url = home_url('/');
    }

    $image_url = bis_get_social_share_image_url();
    $default_image_path = get_template_directory() . '/assets/img/bis-black.png';
    $default_image_size = file_exists($default_image_path) ? getimagesize($default_image_path) : false;

    echo "\n";
    echo '<meta property="og:locale" content="ru_RU">' . "\n";
    echo '<meta property="og:type" content="' . (is_singular() ? 'article' : 'website') . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url($image_url) . '">' . "\n";

    if ($default_image_size && !empty($default_image_size[0]) && !empty($default_image_size[1])) {
        echo '<meta property="og:image:width" content="' . intval($default_image_size[0]) . '">' . "\n";
        echo '<meta property="og:image:height" content="' . intval($default_image_size[1]) . '">' . "\n";
    }

    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta name="twitter:image" content="' . esc_url($image_url) . '">' . "\n";
}

