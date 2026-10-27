<?php

function bis_register_media_sizes() {
    add_image_size('bis-banner', 1600, 900, true);
    add_image_size('bis-card', 960, 720, true);
    add_image_size('bis-thumbnail', 640, 480, true);
    add_image_size('bis-team', 800, 1000, true);
    add_image_size('bis-team-modal', 1200, 1500, true);
}
add_action('after_setup_theme', 'bis_register_media_sizes');

function bis_get_attachment_id_from_url($url) {
    static $cache = array();

    $url = trim((string) $url);
    if ($url === '') {
        return 0;
    }

    if (isset($cache[$url])) {
        return $cache[$url];
    }

    $cache[$url] = (int) attachment_url_to_postid($url);

    return $cache[$url];
}

function bis_get_optimized_image_url($image, $size = 'large') {
    if (is_numeric($image) && (int) $image > 0) {
        $resolved = wp_get_attachment_image_url((int) $image, $size);
        return $resolved ? $resolved : '';
    }

    $image = trim((string) $image);
    if ($image === '') {
        return '';
    }

    $attachment_id = bis_get_attachment_id_from_url($image);
    if ($attachment_id) {
        $resolved = wp_get_attachment_image_url($attachment_id, $size);
        if ($resolved) {
            return $resolved;
        }
    }

    return esc_url($image);
}

function bis_get_post_thumbnail_optimized_url($post_id, $size = 'large') {
    $post_id = (int) $post_id;
    if ($post_id <= 0 || !has_post_thumbnail($post_id)) {
        return '';
    }

    $image = get_the_post_thumbnail_url($post_id, $size);
    return $image ? $image : '';
}

function bis_get_page_banner_image_url($page_id, $size = 'bis-banner') {
    $banner_image = get_post_meta($page_id, 'bis_page_banner_image', true);
    if (!empty($banner_image)) {
        return bis_get_optimized_image_url($banner_image, $size);
    }

    return bis_get_post_thumbnail_optimized_url($page_id, $size);
}

function bis_replace_image_extension($url, $extension) {
    $url = trim((string) $url);
    $extension = ltrim(trim((string) $extension), '.');

    if ($url === '' || $extension === '') {
        return $url;
    }

    $parts = wp_parse_url($url);
    if (empty($parts['path'])) {
        return $url;
    }

    $path = $parts['path'];
    $query = isset($parts['query']) ? '?' . $parts['query'] : '';
    $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

    $new_path = preg_replace('/\.[A-Za-z0-9]+$/', '.' . $extension, $path);
    if (!$new_path || $new_path === $path && str_ends_with(strtolower($path), '.' . strtolower($extension))) {
        return $url;
    }

    $rebuilt = '';
    if (!empty($parts['scheme'])) {
        $rebuilt .= $parts['scheme'] . '://';
    }
    if (!empty($parts['user'])) {
        $rebuilt .= $parts['user'];
        if (!empty($parts['pass'])) {
            $rebuilt .= ':' . $parts['pass'];
        }
        $rebuilt .= '@';
    }
    if (!empty($parts['host'])) {
        $rebuilt .= $parts['host'];
    }
    if (!empty($parts['port'])) {
        $rebuilt .= ':' . $parts['port'];
    }

    return $rebuilt . $new_path . $query . $fragment;
}

function bis_get_webp_image_url($url) {
    return bis_replace_image_extension($url, 'webp');
}

/**
 * Site Icon and Favicon handling with automatic cache-busting.
 * Ensures any updated site icon in General Settings updates for all users immediately.
 */
function bis_get_site_icon_version() {
    $site_icon_id = (int) get_option('site_icon');
    if ($site_icon_id) {
        $mod_time = function_exists('get_post_modified_time') ? get_post_modified_time('U', true, $site_icon_id) : 0;
        return $mod_time ? (string) $mod_time : (string) $site_icon_id;
    }
    return function_exists('bis_get_asset_version') ? bis_get_asset_version('assets/img/LOGOLOGO11.ico') : '1.0';
}

function bis_render_site_favicon() {
    if (function_exists('has_site_icon') && has_site_icon()) {
        $ver = bis_get_site_icon_version();
        $icon_32 = get_site_icon_url(32);
        if ($icon_32) {
            $icon_32_ver = add_query_arg('v', $ver, $icon_32);
            echo '<link rel="icon" href="' . esc_url($icon_32_ver) . '" sizes="32x32" />' . "\n";
            echo '<link rel="shortcut icon" href="' . esc_url($icon_32_ver) . '" />' . "\n";
        }
        return;
    }

    $fallback_url = get_template_directory_uri() . '/assets/img/LOGOLOGO11.ico';
    $ver = function_exists('bis_get_asset_version') ? bis_get_asset_version('assets/img/LOGOLOGO11.ico') : '1.0';
    $fallback_url = add_query_arg('v', $ver, $fallback_url);
    echo '<link rel="icon" type="image/x-icon" href="' . esc_url($fallback_url) . '" />' . "\n";
    echo '<link rel="shortcut icon" type="image/x-icon" href="' . esc_url($fallback_url) . '" />' . "\n";
}

function bis_filter_site_icon_url($url, $size = 512, $blog_id = 0) {
    if (!$url) {
        return $url;
    }
    if (strpos($url, 'v=') !== false) {
        return $url;
    }
    $ver = bis_get_site_icon_version();
    if ($ver) {
        return add_query_arg('v', $ver, $url);
    }
    return $url;
}
add_filter('get_site_icon_url', 'bis_filter_site_icon_url', 10, 3);

function bis_filter_site_icon_meta_tags($meta_tags) {
    if (!is_array($meta_tags) || empty($meta_tags)) {
        return $meta_tags;
    }
    $ver = bis_get_site_icon_version();
    if (!$ver) {
        return $meta_tags;
    }
    foreach ($meta_tags as &$tag) {
        $tag = preg_replace_callback('/(href|content)=([\'"])(.*?)\2/', function($m) use ($ver) {
            $attr = $m[1];
            $quote = $m[2];
            $url = $m[3];
            if (strpos($url, 'v=') === false) {
                $url = add_query_arg('v', $ver, $url);
            }
            return $attr . '=' . $quote . esc_url($url) . $quote;
        }, $tag);
    }
    return $meta_tags;
}
add_filter('site_icon_meta_tags', 'bis_filter_site_icon_meta_tags', 10, 1);

