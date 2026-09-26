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
    4 => new WP_Post(4),
    5 => new WP_Post(5),
);
$meta = array(
    2 => array('bis_service_show_in_catalog' => '0'),
    5 => array('bis_service_show_in_catalog' => '1'),
);
$insert_post_callbacks = array();

function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
    global $insert_post_callbacks;
    if ($hook === 'wp_insert_post') {
        $insert_post_callbacks[] = array($callback, $accepted_args);
    }
}
function do_action($hook, ...$args) {
    global $insert_post_callbacks;
    if ($hook === 'wp_insert_post') {
        foreach ($insert_post_callbacks as $entry) {
            call_user_func_array($entry[0], array_slice($args, 0, $entry[1]));
        }
    }
}
function add_filter(...$args) {}
function remove_action(...$args) {}
function remove_filter(...$args) {}
function wp_nonce_field(...$args) {}
function wp_verify_nonce($nonce, $action) { return $nonce === 'valid'; }
function current_user_can(...$args) { return true; }
function get_post($id) { global $posts; return $posts[$id] ?? null; }
function get_post_type($id) { return get_post($id)->post_type; }
function get_post_meta($id, $key, $single = true) { global $meta; return $meta[$id][$key] ?? ''; }
function metadata_exists($type, $id, $key) { global $meta; return array_key_exists($key, $meta[$id] ?? array()); }
function update_post_meta($id, $key, $value) { global $meta; $meta[$id][$key] = $value; }
function delete_post_meta($id, $key) { global $meta; unset($meta[$id][$key]); }
function get_the_post_thumbnail_url(...$args) { return ''; }
function esc_url($value) { return $value; }
function esc_attr($value) { return $value; }
function esc_textarea($value) { return $value; }
function checked($actual, $echo = true) { if ($actual) echo 'checked="checked"'; }
function wp_unslash($value) { return $value; }
function absint($value) { return abs((int) $value); }
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
class FakeWpdb {
    public $posts = 'wp_posts';

    public function update($table, $values, $where, $formats = array(), $where_formats = array()) {
        global $posts;
        $post = $posts[$where['ID']] ?? null;
        if (!$post || $table !== $this->posts) return false;
        foreach ($values as $key => $value) $post->$key = $value;
        return 1;
    }
}
$wpdb = new FakeWpdb();
function check_ajax_referer(...$args) {}
function clean_post_cache(...$args) {}
function wp_send_json_success($data) { global $json_response; $json_response = $data; }
function wp_send_json_error($data) { throw new RuntimeException($data['message']); }
function check_admin_referer(...$args) {}
function sanitize_key($value) { return $value; }
function add_query_arg($args, $url) { return $url; }
function admin_url($path) { return $path; }
function wp_safe_redirect($url) { global $redirect_url; $redirect_url = $url; }

require __DIR__ . '/../wp-theme/inc/content-helpers.php';
require __DIR__ . '/../wp-theme/inc/content-overrides.php';
require __DIR__ . '/../wp-theme/inc/content-models.php';

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

// REST/block editor creation can publish without submitting the metabox form.
do_action('wp_insert_post', 4, $posts[4], false);
check(get_post_meta(4, 'bis_service_show_in_catalog') === '0', 'new REST service has no hidden default');
check(!bis_service_should_show_in_catalog(4), 'new REST service appeared in catalog');
check(bis_service_should_show_in_catalog(1), 'legacy root service disappeared');
do_action('wp_insert_post', 5, $posts[5], false);
check(get_post_meta(5, 'bis_service_show_in_catalog') === '1', 'explicit catalog opt-in was overwritten');

// Save a parent with an existing child and a newly selected child, then reorder both.
$_POST = array(
    'post_ID' => '1',
    'bis_service_override_nonce_field' => 'valid',
    'bis_service_children_nonce_field' => 'valid',
    'bis_service_faq_nonce_field' => 'valid',
    'bis_service_show_in_catalog' => '1',
    'bis_service_children' => array('2', '4'),
);
bis_save_service_override(1);
bis_save_service_children(1);
check($posts[2]->post_parent === 1 && $posts[4]->post_parent === 1, 'parent save broke child links');
check(get_post_meta(2, 'bis_service_show_in_catalog') === '0'
    && get_post_meta(4, 'bis_service_show_in_catalog') === '0', 'parent save enabled a child switch');
check(bis_service_should_show_in_catalog(1) && !bis_service_should_show_in_catalog(2)
    && !bis_service_should_show_in_catalog(4), 'catalog shows a child after parent save');
check(get_post_meta(2, 'bis_service_faq') !== '', 'parent save changed a child FAQ');

$_POST['bis_service_children'] = array('4', '2');
bis_save_service_children(1);
check($posts[4]->menu_order === 0 && $posts[2]->menu_order === 1, 'child sort order was not saved');
check($posts[2]->post_parent === 1 && $posts[4]->post_parent === 1, 'sorting broke child links');
check(get_post_meta(2, 'bis_service_show_in_catalog') === '0'
    && get_post_meta(4, 'bis_service_show_in_catalog') === '0', 'sorting enabled a child switch');

// The services table has separate AJAX handlers for drag sorting and numeric order.
$_POST = array('orders' => array('2' => '7', '4' => '3'));
bis_ajax_reorder_services();
check($posts[2]->menu_order === 7 && $posts[4]->menu_order === 3, 'table drag order was not saved');
check($posts[2]->post_parent === 1 && $posts[4]->post_parent === 1, 'table drag broke child links');
check(get_post_meta(2, 'bis_service_show_in_catalog') === '0'
    && get_post_meta(4, 'bis_service_show_in_catalog') === '0', 'table drag enabled a child switch');

$_POST = array('post_id' => '2', 'menu_order' => '5');
bis_ajax_update_service_order();
check($posts[2]->menu_order === 5, 'numeric order was not saved');
check($posts[2]->post_parent === 1 && get_post_meta(2, 'bis_service_show_in_catalog') === '0',
    'numeric order changed hierarchy or visibility');

// The separate "Порядок услуг" page exits after saving, so verify its result at shutdown.
$_POST = array(
    'bis_service_order' => array('4', '2'),
    'tab' => 'children',
    'parent_id' => '1',
);
register_shutdown_function(function () use (&$posts) {
    check($posts[4]->menu_order === 10 && $posts[2]->menu_order === 20, 'order page did not save sorting');
    check($posts[2]->post_parent === 1 && $posts[4]->post_parent === 1, 'order page broke child links');
    check(get_post_meta(2, 'bis_service_show_in_catalog') === '0'
        && get_post_meta(4, 'bis_service_show_in_catalog') === '0', 'order page enabled a child switch');
    echo "Service hierarchy regression checks passed.\n";
});
bis_handle_save_service_order();
