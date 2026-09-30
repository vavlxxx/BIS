<?php

/**
 * Import a pasted list through one REST request instead of one request per tag.
 * Gutenberg's ordinary tag control creates each pasted term separately.
 */
function bis_parse_service_tag_list($raw) {
    if (!is_string($raw) || strlen($raw) > 20000) {
        return new WP_Error('bis_tags_invalid', 'Список меток слишком длинный или имеет неверный формат.', array('status' => 400));
    }

    $parts = preg_split('/[,\r\n]+/u', $raw);
    if ($parts === false || count($parts) > 101) {
        return new WP_Error('bis_tags_too_many', 'За один раз можно добавить не более 100 меток.', array('status' => 400));
    }

    $terms = array();
    $seen = array();
    foreach ($parts as $part) {
        $name = sanitize_text_field(trim($part));
        if ($name === '') {
            continue;
        }
        if (strlen($name) > 500) {
            return new WP_Error('bis_tag_too_long', 'Название метки слишком длинное.', array('status' => 400));
        }
        $key = function_exists('mb_strtolower') ? mb_strtolower($name, 'UTF-8') : strtolower($name);
        if (!isset($seen[$key])) {
            $seen[$key] = true;
            $terms[] = $name;
        }
    }

    if (!$terms) {
        return new WP_Error('bis_tags_empty', 'Вставьте хотя бы одну метку.', array('status' => 400));
    }
    if (count($terms) > 100) {
        return new WP_Error('bis_tags_too_many', 'За один раз можно добавить не более 100 меток.', array('status' => 400));
    }
    return $terms;
}

function bis_resolve_service_tags($terms) {
    $ids = array();
    foreach ($terms as $name) {
        $existing = term_exists($name, 'bis_service_tag');
        if ($existing) {
            $ids[] = (int) (is_array($existing) ? $existing['term_id'] : $existing);
            continue;
        }
        $inserted = wp_insert_term($name, 'bis_service_tag');
        if (is_wp_error($inserted)) {
            if ($inserted->get_error_code() === 'term_exists' && $inserted->get_error_data()) {
                $ids[] = (int) $inserted->get_error_data();
                continue;
            }
            return $inserted;
        }
        $ids[] = (int) $inserted['term_id'];
    }
    return array_values(array_unique($ids));
}

function bis_service_tag_import_permission($request) {
    $post_id = (int) $request->get_param('post_id');
    $taxonomy = get_taxonomy('bis_service_tag');
    return $post_id > 0
        && get_post_type($post_id) === 'bis_service'
        && $taxonomy
        && current_user_can('edit_post', $post_id)
        && current_user_can($taxonomy->cap->assign_terms)
        && current_user_can($taxonomy->cap->edit_terms);
}

function bis_service_tag_import_route($request) {
    $terms = bis_parse_service_tag_list($request->get_param('terms'));
    if (is_wp_error($terms)) {
        return $terms;
    }
    $ids = bis_resolve_service_tags($terms);
    if (is_wp_error($ids)) {
        return $ids;
    }
    return rest_ensure_response(array('ids' => $ids, 'count' => count($ids)));
}

function bis_register_service_tag_import_route() {
    register_rest_route('bis/v1', '/service-tags/import', array(
        'methods'             => 'POST',
        'callback'            => 'bis_service_tag_import_route',
        'permission_callback' => 'bis_service_tag_import_permission',
        'args'                => array(
            'post_id' => array('type' => 'integer', 'required' => true),
            'terms'   => array('type' => 'string', 'required' => true),
        ),
    ));
}

function bis_enqueue_service_tag_import() {
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'bis_service' || !$screen->is_block_editor()) {
        return;
    }
    $path = 'assets/js/admin-service-tag-import.js';
    wp_enqueue_script(
        'bis-service-tag-import',
        get_template_directory_uri() . '/' . $path,
        array('wp-api-fetch', 'wp-data', 'wp-dom-ready'),
        bis_get_asset_version($path),
        true
    );
}

add_action('rest_api_init', 'bis_register_service_tag_import_route');
add_action('enqueue_block_editor_assets', 'bis_enqueue_service_tag_import');
