<?php

// Restore the PNR links that the previous child-sort handler detached on biscorp.ru.
function bis_repair_pnr_service_hierarchy() {
    $done_option = 'bis_pnr_hierarchy_repaired_20260927';
    $backup_option = 'bis_pnr_hierarchy_backup_20260927';
    if (get_option($done_option) === '1') {
        return;
    }

    $parent_id = 756;
    $expected = array(
        926 => 'Противодымная вентиляция',
        963 => 'Холодоснабжение',
        990 => 'Теплоснабжение',
        1020 => 'Отопление',
        759 => 'Общеобменная вентиляция',
    );
    $parent = get_post($parent_id);
    if (!($parent instanceof WP_Post)
        || 'bis_service' !== $parent->post_type
        || 'Комплекс пусконаладочных работ (ПНР)' !== $parent->post_title) {
        return;
    }

    $current_children = bis_get_service_child_ids($parent_id);
    if (array_diff($current_children, array_keys($expected))) {
        return;
    }

    $backup = array();
    foreach ($expected as $id => $title) {
        $post = get_post($id);
        if (!($post instanceof WP_Post)
            || 'bis_service' !== $post->post_type
            || $title !== $post->post_title
            || !in_array((int) $post->post_parent, array(0, $parent_id), true)) {
            return;
        }

        $backup[$id] = array(
            'post_parent' => (int) $post->post_parent,
            'menu_order' => (int) $post->menu_order,
            'show_in_catalog' => get_post_meta($id, 'bis_service_show_in_catalog', true),
        );
    }

    if (false === get_option($backup_option, false) && !add_option($backup_option, $backup, '', false)) {
        return;
    }

    bis_assign_service_child_ids($parent_id, array_keys($expected));

    foreach (array_keys($expected) as $order => $id) {
        $post = get_post($id);
        if (!($post instanceof WP_Post)
            || (int) $post->post_parent !== $parent_id
            || (int) $post->menu_order !== $order
            || get_post_meta($id, 'bis_service_show_in_catalog', true) !== '0') {
            return;
        }
    }

    update_option($done_option, '1', false);
}
add_action('init', 'bis_repair_pnr_service_hierarchy', 20);
