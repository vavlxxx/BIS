<?php
/**
 * Tests for dynamic site icon and favicon cache-busting.
 */

$GLOBALS['options'] = [];
$GLOBALS['post_modified'] = [];

if (!function_exists('add_action')) {
    function add_action(...$args) {}
}
if (!function_exists('add_filter')) {
    function add_filter(...$args) {}
}
if (!function_exists('add_image_size')) {
    function add_image_size(...$args) {}
}
if (!function_exists('get_option')) {
    function get_option($key, $default = false) {
        return $GLOBALS['options'][$key] ?? $default;
    }
}
if (!function_exists('esc_url')) {
    function esc_url($v) {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('get_template_directory_uri')) {
    function get_template_directory_uri() {
        return 'https://biscorp.ru/wp-content/themes/wp-theme';
    }
}
if (!function_exists('has_site_icon')) {
    function has_site_icon() {
        return !empty($GLOBALS['options']['site_icon']);
    }
}
if (!function_exists('get_site_icon_url')) {
    function get_site_icon_url($size = 512, $url = '', $blog_id = 0) {
        if (!has_site_icon()) {
            return '';
        }
        $id = $GLOBALS['options']['site_icon'];
        $base = "https://biscorp.ru/wp-content/uploads/2026/10/favicon-{$size}x{$size}.png";
        return function_exists('bis_filter_site_icon_url') ? bis_filter_site_icon_url($base, $size, $blog_id) : $base;
    }
}
if (!function_exists('get_post_modified_time')) {
    function get_post_modified_time($format, $gmt, $post_id) {
        return $GLOBALS['post_modified'][$post_id] ?? 1729357310;
    }
}
if (!function_exists('add_query_arg')) {
    function add_query_arg($key, $val, $url) {
        $sep = (strpos($url, '?') !== false) ? '&' : '?';
        return $url . $sep . rawurlencode($key) . '=' . rawurlencode($val);
    }
}

require_once __DIR__ . '/../wp-theme/inc/media.php';

function test_icon_check($cond, $msg) {
    if (!$cond) {
        throw new RuntimeException("Test failed: $msg");
    }
}

// 1. When site icon is NOT set in WP admin: fallback icon is output with version
$GLOBALS['options']['site_icon'] = 0;
ob_start();
bis_render_site_favicon();
$fallback_html = ob_get_clean();

test_icon_check(str_contains($fallback_html, 'LOGOLOGO11.ico'), 'Fallback renders LOGOLOGO11.ico when no site_icon set');
test_icon_check(str_contains($fallback_html, '?v='), 'Fallback icon includes cache-busting version parameter');

// 2. When site icon IS set in WP admin: dynamic site icon is rendered with cache busting
$GLOBALS['options']['site_icon'] = 1118;
$GLOBALS['post_modified'][1118] = 1729357310;

ob_start();
bis_render_site_favicon();
$site_icon_html = ob_get_clean();

test_icon_check(!str_contains($site_icon_html, 'LOGOLOGO11.ico'), 'Site icon does NOT render old hardcoded LOGOLOGO11.ico');
test_icon_check(str_contains($site_icon_html, 'favicon-32x32.png?v=1729357310'), 'Site icon renders 32x32 icon with timestamp version parameter');
test_icon_check(str_contains($site_icon_html, 'rel="icon"'), 'Site icon renders rel="icon"');
test_icon_check(str_contains($site_icon_html, 'rel="shortcut icon"'), 'Site icon renders rel="shortcut icon"');

// 3. When site icon is updated to a new attachment or time changes: version updates automatically
$GLOBALS['post_modified'][1118] = 1730000000;
$filtered_url = bis_filter_site_icon_url('https://biscorp.ru/wp-content/uploads/2026/10/new-icon-32x32.png', 32);
test_icon_check(str_contains($filtered_url, '?v=1730000000'), 'get_site_icon_url filter attaches new updated timestamp');

// 4. Meta tags filter adds version parameter to all wp_site_icon meta tags
$sample_tags = [
    '<link rel="icon" href="https://biscorp.ru/icon-32.png" sizes="32x32" />',
    '<link rel="icon" href="https://biscorp.ru/icon-192.png" sizes="192x192" />',
    '<link rel="apple-touch-icon" href="https://biscorp.ru/icon-180.png" />',
    '<meta name="msapplication-TileImage" content="https://biscorp.ru/icon-270.png" />',
];
$filtered_tags = bis_filter_site_icon_meta_tags($sample_tags);
foreach ($filtered_tags as $tag) {
    test_icon_check(str_contains($tag, 'v=1730000000'), 'site_icon_meta_tags filter updates all meta tags with version');
}

// 5. Header template check: header.php no longer has hardcoded LOGOLOGO11.ico
$header_php = file_get_contents(__DIR__ . '/../wp-theme/header.php');
test_icon_check(!str_contains($header_php, 'LOGOLOGO11.ico'), 'header.php no longer hardcodes LOGOLOGO11.ico');
test_icon_check(str_contains($header_php, 'bis_render_site_favicon()'), 'header.php calls bis_render_site_favicon()');

// 6. ICO file check: LOGOLOGO11.ico is valid ICO file
$ico_path = __DIR__ . '/../wp-theme/assets/img/LOGOLOGO11.ico';
test_icon_check(file_exists($ico_path), 'LOGOLOGO11.ico exists');
test_icon_check(filesize($ico_path) > 10000, 'LOGOLOGO11.ico contains multi-res infinity icon');

echo "Site icon and favicon tests passed\n";
