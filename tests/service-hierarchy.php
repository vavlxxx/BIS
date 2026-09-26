<?php

class WP_Post {
    public $ID;
    public $post_type = 'bis_service';
    public $post_parent;
    public $post_status;
    public $menu_order = 0;

    public function __construct($id, $parent = 0, $status = 'publish') {
        $this->ID = $id;
        $this->post_parent = $parent;
        $this->post_status = $status;
    }
}

$posts = array(
    1 => new WP_Post(1),
    2 => new WP_Post(2, 1),
    3 => new WP_Post(3, 0, 'auto-draft'),
);
$meta = array(2 => array('bis_service_show_in_catalog' => '0'));

function add_action(...$args) {}
function add_filter(...$args) {}
function remove_action(...$args) {}
function remove_filter(...$args) {}
function wp_nonce_field(...$args) {}
function wp_verify_nonce($nonce, $action) { return $nonce === 'valid'; }
function current_user_can(...$args) { return true; }
function get_post($id) { global $posts; return $posts[$id] ?? null; }
function get_post_type($id) { return get_post($id)->post_type; }
function get_post_meta($id, $key, $single = true) { global $meta; return $meta[$id][$key] ?? ''; }
function update_post_meta($id, $key, $value) { global $meta; $meta[$id][$key] = $value; }
function delete_post_meta($id, $key) { global $meta; unset($meta[$id][$key]); }
function get_the_post_thumbnail_url(...$args) { return ''; }
function esc_url($value) { return $value; }
function esc_attr($value) { return $value; }
function esc_textarea($value) { return $value; }
function checked($actual, $echo = true) { if ($actual) echo 'checked="checked"'; }
function wp_unslash($value) { return $value; }
function sanitize_textarea_field($value) { return $value; }
function get_posts($args) {
    global $posts;
    $matches = array_filter($posts, function ($post) use ($args) {
        return $post->post_parent === (int) ($args['post_parent'] ?? -1)
            && $post->post_status !== 'auto-draft';
    });
    if (($args['fields'] ?? '') === 'ids') {
        return array_map(function ($post) { return $post->ID; }, array_values($matches));
    }
    return array_values($matches);
}
function wp_update_post($args) {
    global $posts;
    $id = $args['ID'];
    foreach ($args as $key => $value) {
        if ($key !== 'ID') $posts[$id]->$key = $value;
    }
    bis_save_service_override($id);
    bis_save_service_faq($id);
    bis_save_service_children($id);
}

require __DIR__ . '/../wp-theme/inc/content-helpers.php';
require __DIR__ . '/../wp-theme/inc/content-overrides.php';

function check($condition, $message) {
    if (!$condition) {
        fwrite(STDERR, "FAIL: $message\n");
        exit(1);
    }
}

// A child update made while saving its parent must not inherit the parent's form fields.
$_POST = array(
    'post_ID' => '1',
    'bis_service_override_nonce_field' => 'valid',
    'bis_service_show_in_catalog' => '1',
);
bis_save_service_override(2);
check(get_post_meta(2, 'bis_service_show_in_catalog') === '0', 'child inherited parent visibility');

update_post_meta(2, 'bis_service_faq', array(array('question' => 'Child question', 'answer' => 'Child answer')));
$_POST['bis_service_faq_nonce_field'] = 'valid';
bis_save_service_faq(2);
check(get_post_meta(2, 'bis_service_faq') !== '', 'child FAQ was removed by parent save');

// A stale flag must never place a child in the root catalog.
update_post_meta(2, 'bis_service_show_in_catalog', '1');
check(!bis_service_should_show_in_catalog(2), 'child appeared in root catalog');
bis_assign_service_child_ids(1, array(2));
check(get_post_meta(2, 'bis_service_show_in_catalog') === '0', 'sorting did not reset stale child visibility');
ob_start();
bis_render_service_metabox_override($posts[2]);
$child_html = ob_get_clean();
check(!preg_match('/<input[^>]*name="bis_service_show_in_catalog"[^>]*checked/', $child_html), 'child switch is on');

// The new-service editor starts with the switch off.
ob_start();
bis_render_service_metabox_override($posts[3]);
$html = ob_get_clean();
check(!preg_match('/<input[^>]*name="bis_service_show_in_catalog"[^>]*checked/', $html), 'new service switch is on');

$_POST = array('post_ID' => '3', 'bis_service_override_nonce_field' => 'valid');
bis_save_service_override(3);
check(get_post_meta(3, 'bis_service_show_in_catalog') === '0', 'new service was saved as visible');
check(!bis_service_should_show_in_catalog(3), 'new service appeared in catalog');

echo "Service hierarchy regression checks passed.\n";
