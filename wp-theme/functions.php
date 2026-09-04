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
        'bis-base'        => 'assets/css/base.css',
        'bis-front-page'  => 'assets/css/front-page.css',
        'bis-news'        => 'assets/css/news.css',
        'bis-team'        => 'assets/css/team.css',
        'bis-content'     => 'assets/css/content.css',
        'bis-vacancies'   => 'assets/css/vacancies.css',
    );

    if (is_user_logged_in()) {
        wp_enqueue_style('bis-katex', get_template_directory_uri() . '/assets/vendor/katex/katex.min.css', array(), '0.16.9');
        $css_files['bis-calculators'] = 'assets/css/calculators.css';
    }

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
    wp_enqueue_script('bis-site-news-ajax', get_template_directory_uri() . '/assets/js/site-news-ajax.js', array(), bis_get_asset_version('assets/js/site-news-ajax.js'), true);

    $app_deps = array('bis-site-forms', 'bis-site-navigation', 'bis-site-home', 'bis-site-team', 'bis-site-project', 'bis-site-news-ajax');
    if (is_user_logged_in()) {
        wp_enqueue_script('bis-katex', get_template_directory_uri() . '/assets/vendor/katex/katex.min.js', array(), '0.16.9', false);
        wp_enqueue_script('bis-site-calculators', get_template_directory_uri() . '/assets/js/site-calculators.js', array('bis-katex'), bis_get_asset_version('assets/js/site-calculators.js'), true);
        $app_deps[] = 'bis-site-calculators';
    }
    wp_enqueue_script('bis-site-app', get_template_directory_uri() . '/assets/js/site-app.js', $app_deps, bis_get_asset_version('assets/js/site-app.js'), true);

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

function bis_get_current_meta_title() {
    $site_name = get_bloginfo('name');
    $document_title = $site_name;

    if (is_front_page()) {
        $tagline = get_bloginfo('description');
        if (!empty($tagline)) {
            $document_title = $site_name . ' — ' . $tagline;
        }
    } else {
        $queried_object = get_queried_object();

        if (is_home()) {
            $posts_page_id = (int) get_option('page_for_posts');
            if ($posts_page_id > 0) {
                $posts_page = get_post($posts_page_id);
                if ($posts_page instanceof WP_Post && !empty($posts_page->post_title)) {
                    $document_title = $posts_page->post_title . ' — ' . $site_name;
                }
            }
        } elseif ($queried_object instanceof WP_Post && !empty($queried_object->post_title)) {
            $document_title = $queried_object->post_title . ' — ' . $site_name;
        } elseif (is_category() || is_tag() || is_tax()) {
            $term_title = single_term_title('', false);
            $document_title = ($term_title ? $term_title . ' — ' : '') . $site_name;
        } elseif (is_post_type_archive()) {
            $archive_title = post_type_archive_title('', false);
            $document_title = ($archive_title ? $archive_title . ' — ' : '') . $site_name;
        } elseif (is_search()) {
            $document_title = 'Поиск — ' . $site_name;
        } elseif (is_404()) {
            $document_title = 'Страница не найдена — ' . $site_name;
        }
    }

    if (is_singular(bis_get_seo_enabled_post_types())) {
        $seo_title = bis_get_post_seo_title(get_queried_object_id());
        if (!empty($seo_title)) {
            $document_title = $seo_title;
        }
    }

    return $document_title;
}

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

function bis_get_social_share_image_data() {
    $image_url = '';
    $width = 1200;
    $height = 630;
    $type = 'image/png';

    if (is_singular() && has_post_thumbnail()) {
        $thumb_id = get_post_thumbnail_id(get_queried_object_id());
        $img_data = wp_get_attachment_image_src($thumb_id, 'large');
        if ($img_data) {
            $image_url = $img_data[0];
            if (!empty($img_data[1])) {
                $width = $img_data[1];
            }
            if (!empty($img_data[2])) {
                $height = $img_data[2];
            }
            $file_path = get_attached_file($thumb_id);
            if ($file_path && file_exists($file_path)) {
                $mime = wp_check_filetype($file_path);
                if (!empty($mime['type'])) {
                    $type = $mime['type'];
                }
            }
        }
    }

    if (empty($image_url)) {
        $default_img_relative = '/assets/img/bis-black.png';
        $image_url = get_template_directory_uri() . $default_img_relative;
        $file_path = get_template_directory() . $default_img_relative;
        if (file_exists($file_path)) {
            $size = @getimagesize($file_path);
            if ($size) {
                $width = $size[0];
                $height = $size[1];
                if (!empty($size['mime'])) {
                    $type = $size['mime'];
                }
            }
        }
    }

    return array(
        'url' => $image_url,
        'width' => $width,
        'height' => $height,
        'type' => $type,
    );
}

function bis_get_social_share_image_url() {
    $image = bis_get_social_share_image_data();
    return $image['url'];
}

function bis_output_social_meta_tags() {
    if (is_admin() || is_feed() || is_robots() || is_trackback()) {
        return;
    }

    $title = bis_get_current_meta_title();
    $description = bis_get_current_meta_description();

    $url = home_url(add_query_arg(array(), $GLOBALS['wp']->request ?? ''));
    if (is_singular()) {
        $url = get_permalink();
    }
    if (empty($url)) {
        $url = home_url('/');
    }

    $image = bis_get_social_share_image_data();
    $image_url = $image['url'];

    echo "\n" . '  <!-- Open Graph / Rich Social Preview -->' . "\n";
    echo '  <meta property="og:locale" content="ru_RU">' . "\n";
    echo '  <meta property="og:type" content="' . (is_singular() ? 'article' : 'website') . '">' . "\n";
    echo '  <meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
    echo '  <meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '  <meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '  <meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '  <meta property="og:image" content="' . esc_url($image_url) . '">' . "\n";
    if (is_ssl() || strpos($image_url, 'https://') === 0) {
        echo '  <meta property="og:image:secure_url" content="' . esc_url($image_url) . '">' . "\n";
    }
    if (!empty($image['type'])) {
        echo '  <meta property="og:image:type" content="' . esc_attr($image['type']) . '">' . "\n";
    }
    if (!empty($image['width']) && !empty($image['height'])) {
        echo '  <meta property="og:image:width" content="' . intval($image['width']) . '">' . "\n";
        echo '  <meta property="og:image:height" content="' . intval($image['height']) . '">' . "\n";
    }
    echo '  <meta property="og:image:alt" content="' . esc_attr($title) . '">' . "\n";

    echo '  <!-- Twitter Card -->' . "\n";
    echo '  <meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '  <meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '  <meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    echo '  <meta name="twitter:image" content="' . esc_url($image_url) . '">' . "\n";
}
add_action('wp_head', 'bis_output_social_meta_tags', 1);

function bis_admin_enqueue_scripts($hook) {
    global $current_screen;
    if ($current_screen && $current_screen->taxonomy === 'bis_news_category') {
        wp_enqueue_script('bis-admin-auto-slug', get_template_directory_uri() . '/assets/js/admin-auto-slug.js', array(), bis_get_asset_version('assets/js/admin-auto-slug.js'), true);
    }
}
add_action('admin_enqueue_scripts', 'bis_admin_enqueue_scripts');

function bis_calculators_rewrite_rules() {
    add_rewrite_rule('^calculators/?$', 'index.php?bis_calculators=1', 'top');
}
add_action('init', 'bis_calculators_rewrite_rules');

function bis_calculators_query_vars($vars) {
    $vars[] = 'bis_calculators';
    return $vars;
}
add_filter('query_vars', 'bis_calculators_query_vars');

function bis_calculators_pre_handle_404($preempt, $wp_query) {
    $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    if ($path === 'calculators') {
        return true;
    }
    return $preempt;
}
add_filter('pre_handle_404', 'bis_calculators_pre_handle_404', 10, 2);

function bis_protect_calculators_page() {
    $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    if ($path === 'calculators' || get_query_var('bis_calculators') || is_page('calculators')) {
        if (!is_user_logged_in()) {
            auth_redirect();
            exit;
        }
    }
}
add_action('template_redirect', 'bis_protect_calculators_page');

function bis_calculators_template_include($template) {
    $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    if ($path === 'calculators' || get_query_var('bis_calculators') || is_page('calculators')) {
        if (!is_user_logged_in()) {
            auth_redirect();
            exit;
        }
        $calc_template = get_template_directory() . '/page-calculators.php';
        if (file_exists($calc_template)) {
            global $wp_query;
            $wp_query->is_404 = false;
            status_header(200);
            return $calc_template;
        }
    }
    return $template;
}
add_filter('template_include', 'bis_calculators_template_include');

function bis_ensure_calculators_page() {
    if (get_option('bis_calculators_page_created_v2')) {
        return;
    }

    $existing = get_page_by_path('calculators');
    if (!$existing) {
        $existing_by_tpl = get_pages(array(
            'meta_key'   => '_wp_page_template',
            'meta_value' => 'page-calculators.php',
            'number'     => 1,
        ));
        if (empty($existing_by_tpl)) {
            $page_id = wp_insert_post(array(
                'post_title'     => 'Калькуляторы',
                'post_name'      => 'calculators',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed',
            ));
            if ($page_id && !is_wp_error($page_id)) {
                update_post_meta($page_id, '_wp_page_template', 'page-calculators.php');
            }
        }
    }

    update_option('bis_calculators_page_created_v2', 1);
}
add_action('init', 'bis_ensure_calculators_page');


