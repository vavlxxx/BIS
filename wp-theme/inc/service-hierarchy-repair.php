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

// The old picker omitted service 763 because it has children, leaving it hidden at the root.
function bis_repair_ventilation_service_hierarchy() {
    $done_option = 'bis_ventilation_hierarchy_repaired_20260927';
    $backup_option = 'bis_ventilation_hierarchy_backup_20260927';
    if (get_option($done_option) === '1') {
        return;
    }

    $parent_id = 759;
    $child_id = 763;
    $parent = get_post($parent_id);
    $child = get_post($child_id);
    if (!($parent instanceof WP_Post)
        || 'bis_service' !== $parent->post_type
        || 'Общеобменная вентиляция' !== $parent->post_title
        || !($child instanceof WP_Post)
        || 'bis_service' !== $child->post_type
        || '1 Подготовительный этап и проверка монтажа общеобменной вентиляции' !== $child->post_title
        || !in_array((int) $child->post_parent, array(0, $parent_id), true)
        || bis_service_would_create_cycle($parent_id, $child_id)) {
        return;
    }

    $backup = array(
        'post_parent' => (int) $child->post_parent,
        'menu_order' => (int) $child->menu_order,
        'show_in_catalog' => get_post_meta($child_id, 'bis_service_show_in_catalog', true),
    );
    if (false === get_option($backup_option, false) && !add_option($backup_option, $backup, '', false)) {
        return;
    }

    if ((int) $child->post_parent === 0) {
        wp_update_post(array('ID' => $child_id, 'post_parent' => $parent_id));
    }
    update_post_meta($child_id, 'bis_service_show_in_catalog', '0');

    $child = get_post($child_id);
    if ($child instanceof WP_Post
        && (int) $child->post_parent === $parent_id
        && get_post_meta($child_id, 'bis_service_show_in_catalog', true) === '0') {
        update_option($done_option, '1', false);
    }
}
add_action('init', 'bis_repair_ventilation_service_hierarchy', 25);
