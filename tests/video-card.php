<?php
/**
 * Tests for Video Business Card (Видео визитка)
 */

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../wp-theme/');
}

$GLOBALS['options'] = [];
$GLOBALS['actions'] = [];
$GLOBALS['filters'] = [];
$GLOBALS['scripts'] = [];
$GLOBALS['is_front_page'] = true;
$GLOBALS['current_user_can'] = true;
$GLOBALS['attachments'] = [];

if (!function_exists('add_action')) {
    function add_action($tag, $callback, $priority = 10, $accepted_args = 1) {
        $GLOBALS['actions'][$tag][] = $callback;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($tag, $callback, $priority = 10, $accepted_args = 1) {
        $GLOBALS['filters'][$tag][] = $callback;
    }
}

if (!function_exists('get_option')) {
    function get_option($key, $default = false) {
        return $GLOBALS['options'][$key] ?? $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($key, $val) {
        $GLOBALS['options'][$key] = $val;
        return true;
    }
}

if (!function_exists('absint')) {
    function absint($v) {
        return abs((int) $v);
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($v) {
        return trim(strip_tags((string) $v));
    }
}

if (!function_exists('sanitize_textarea_field')) {
    function sanitize_textarea_field($v) {
        return trim(strip_tags((string) $v));
    }
}

if (!function_exists('is_front_page')) {
    function is_front_page() {
        return !empty($GLOBALS['is_front_page']);
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($cap) {
        return !empty($GLOBALS['current_user_can']);
    }
}

if (!function_exists('check_admin_referer')) {
    function check_admin_referer($action, $name) {
        return true;
    }
}

if (!function_exists('wp_nonce_field')) {
    function wp_nonce_field($action, $name) {
        echo '<input type="hidden" name="' . esc_attr($name) . '" value="mock_nonce" />';
    }
}

if (!function_exists('checked')) {
    function checked($checked, $current = true, $echo = true) {
        $res = ((string) $checked === (string) $current) ? ' checked="checked"' : '';
        if ($echo) {
            echo $res;
        }
        return $res;
    }
}

if (!function_exists('selected')) {
    function selected($selected, $current = true, $echo = true) {
        $res = ((string) $selected === (string) $current) ? ' selected="selected"' : '';
        if ($echo) {
            echo $res;
        }
        return $res;
    }
}

if (!function_exists('submit_button')) {
    function submit_button() {
        echo '<p class="submit"><input type="submit" name="submit" class="button button-primary" value="Сохранить изменения"></p>';
    }
}

if (!function_exists('wp_get_attachment_url')) {
    function wp_get_attachment_url($id) {
        return $GLOBALS['attachments'][$id] ?? false;
    }
}

if (!function_exists('esc_url')) {
    function esc_url($v) {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($v) {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_html')) {
    function esc_html($v) {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_textarea')) {
    function esc_textarea($v) {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('add_options_page')) {
    function add_options_page($page_title, $menu_title, $capability, $menu_slug, $callback = '') {
        $GLOBALS['options_pages'][$menu_slug] = [
            'page_title' => $page_title,
            'menu_title' => $menu_title,
            'capability' => $capability,
            'callback'   => $callback,
        ];
    }
}

if (!function_exists('wp_enqueue_media')) {
    function wp_enqueue_media() {
        $GLOBALS['media_enqueued'] = true;
    }
}

if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false) {
        $GLOBALS['scripts'][$handle] = [
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'in_footer' => $in_footer,
        ];
    }
}

if (!function_exists('get_template_directory_uri')) {
    function get_template_directory_uri() {
        return 'https://biscorp.ru/wp-content/themes/bis';
    }
}

require_once __DIR__ . '/../wp-theme/inc/video-card.php';

function test_assert($condition, $message) {
    if (!$condition) {
        throw new RuntimeException("Assertion failed: $message");
    }
}

// 1. Position normalization
test_assert(bis_video_card_normalize_position('bottom-right') === 'bottom-right', 'bottom-right is valid');
test_assert(bis_video_card_normalize_position('bottom-left') === 'bottom-left', 'bottom-left is valid');
test_assert(bis_video_card_normalize_position('top-right') === 'top-right', 'top-right is valid');
test_assert(bis_video_card_normalize_position('top-left') === 'top-left', 'top-left is valid');
test_assert(bis_video_card_normalize_position('invalid') === 'bottom-right', 'invalid defaults to bottom-right');
test_assert(bis_video_card_normalize_position('') === 'bottom-right', 'empty defaults to bottom-right');

// 2. Data retrieval and defaults
$GLOBALS['options'] = [];
test_assert(bis_get_video_card_data() === [], 'Empty options return empty array');

// 3. Fallback to legacy options
$GLOBALS['options']['bis_video_card_options'] = [
    'video_id' => '42',
    'title' => 'Тест визитки',
    'description' => 'Описание визитки',
    'position' => 'bottom-left',
    'enabled' => '1',
];
$data = bis_get_video_card_data();
test_assert($data['video_id'] === 42, 'Legacy video_id converted to int');
test_assert($data['title'] === 'Тест визитки', 'Legacy title read');
test_assert($data['position'] === 'bottom-left', 'Legacy position read');
test_assert($data['enabled'] === 1, 'Legacy enabled read');

// 4. Admin menu registration
bis_add_video_card_admin_menu();
test_assert(isset($GLOBALS['options_pages']['video-card']), 'video-card admin page registered');
test_assert($GLOBALS['options_pages']['video-card']['callback'] === 'bis_video_card_admin_page', 'Admin page callback matches');

// 5. Admin scripts enqueue
$GLOBALS['media_enqueued'] = false;
$GLOBALS['scripts'] = [];
bis_video_card_admin_scripts('some_other_page');
test_assert(!$GLOBALS['media_enqueued'], 'Media not enqueued on unrelated pages');
test_assert(!isset($GLOBALS['scripts']['bis-video-card-admin']), 'Script not enqueued on unrelated pages');

bis_video_card_admin_scripts('settings_page_video-card');
test_assert($GLOBALS['media_enqueued'], 'Media enqueued on settings_page_video-card');
test_assert(isset($GLOBALS['scripts']['bis-video-card-admin']), 'Script enqueued on settings_page_video-card');

// 6. Admin form submission
$_POST = [
    'submit' => '1',
    'bis_video_card_nonce' => 'mock_nonce',
    'enabled' => '1',
    'video_id' => '123',
    'title' => 'Визитка БИС',
    'description' => 'Инженерные решения',
    'position' => 'bottom-right',
];
ob_start();
bis_video_card_admin_page();
$admin_html = ob_get_clean();

test_assert(str_contains($admin_html, 'Настройки сохранены!'), 'Success notice shown after post');
$saved = get_option('video_card_data');
test_assert($saved['video_id'] === 123, 'Saved video_id matches');
test_assert($saved['title'] === 'Визитка БИС', 'Saved title matches');
test_assert($saved['description'] === 'Инженерные решения', 'Saved description matches');
test_assert($saved['position'] === 'bottom-right', 'Saved position matches');
test_assert($saved['enabled'] === 1, 'Saved enabled matches');

// 7. Frontend rendering
$GLOBALS['attachments'][123] = 'https://biscorp.ru/wp-content/uploads/video-card.mp4';

// A) Disabled
$GLOBALS['options']['video_card_data']['enabled'] = 0;
ob_start();
bis_display_video_card();
test_assert(ob_get_clean() === '', 'Does not render when disabled');

// B) Not front page
$GLOBALS['options']['video_card_data']['enabled'] = 1;
$GLOBALS['is_front_page'] = false;
ob_start();
bis_display_video_card();
test_assert(ob_get_clean() === '', 'Does not render when not front page');

// C) Front page and enabled
$GLOBALS['is_front_page'] = true;
ob_start();
bis_display_video_card();
$front_html = ob_get_clean();

test_assert(str_contains($front_html, 'id="videoCard"'), 'videoCard container rendered');
test_assert(str_contains($front_html, 'video-card-bottom-right'), 'position class added');
test_assert(str_contains($front_html, 'https://biscorp.ru/wp-content/uploads/video-card.mp4'), 'video src rendered in preview and modal');
test_assert(str_contains($front_html, 'id="videoModal"'), 'videoModal rendered');
test_assert(str_contains($front_html, 'video-modal__book-btn callback-btn'), 'Contact button with callback-btn rendered');
test_assert(str_contains($front_html, 'data-video-card-contact'), 'data-video-card-contact attribute present');
test_assert(str_contains($front_html, 'Связаться с нами'), 'Contact button label present');

// 8. Body class
$classes = bis_video_card_body_class(['home']);
test_assert(in_array('bis-has-video-card', $classes, true), 'bis-has-video-card body class added');
test_assert(in_array('bis-has-video-card-bottom-right', $classes, true), 'bis-has-video-card-bottom-right body class added');

// 9. Frontend script enqueue
$GLOBALS['scripts'] = [];
bis_video_card_enqueue_scripts();
test_assert(isset($GLOBALS['scripts']['bis-video-card-script']), 'bis-video-card-script enqueued on front page');

// 10. File integrity and styling
$functions_php = file_get_contents(__DIR__ . '/../wp-theme/functions.php');
test_assert(str_contains($functions_php, "require_once get_template_directory() . '/inc/video-card.php';"), 'functions.php includes video-card.php');

$content_css = file_get_contents(__DIR__ . '/../wp-theme/assets/css/content.css');
test_assert(str_contains($content_css, '.video-card-container'), 'content.css contains .video-card-container');
test_assert(str_contains($content_css, '.video-modal'), 'content.css contains .video-modal');
test_assert(str_contains($content_css, '.video-modal__book-btn'), 'content.css contains .video-modal__book-btn');
test_assert(str_contains($content_css, 'body.bis-has-video-card-bottom-right .floating-estimate-wrapper'), 'content.css contains floating widget offset');

$video_card_js = file_get_contents(__DIR__ . '/../wp-theme/assets/js/video-card.js');
test_assert(str_contains($video_card_js, 'videoModal'), 'video-card.js handles videoModal');
test_assert(str_contains($video_card_js, 'data-video-card-contact'), 'video-card.js handles data-video-card-contact');

$video_card_admin_js = file_get_contents(__DIR__ . '/../wp-theme/assets/js/video-card-admin.js');
test_assert(str_contains($video_card_admin_js, 'wp.media'), 'video-card-admin.js uses wp.media');

echo "Video card tests passed\n";
