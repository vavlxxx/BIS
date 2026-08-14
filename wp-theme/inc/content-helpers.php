<?php

function bis_get_project_preview_image_url($post_id) {
    $preview = get_post_meta($post_id, 'bis_project_preview_image', true);
    if ($preview) {
        return bis_get_optimized_image_url($preview, 'bis-card');
    }

    $legacy = get_post_meta($post_id, 'bis_project_image', true);
    if ($legacy) {
        return bis_get_optimized_image_url($legacy, 'bis-card');
    }

    $banner = get_post_meta($post_id, 'bis_project_banner_image', true);
    if ($banner) {
        return bis_get_optimized_image_url($banner, 'bis-card');
    }

    return bis_get_post_thumbnail_optimized_url($post_id, 'bis-card');
}

function bis_get_project_image_url($post_id) {
    return bis_get_project_preview_image_url($post_id);
}

function bis_get_project_details($post_id) {
    return array(
        'address' => get_post_meta($post_id, 'bis_project_address', true),
        'area'    => get_post_meta($post_id, 'bis_project_area', true),
        'year'    => get_post_meta($post_id, 'bis_project_year', true),
    );
}

function bis_get_project_banner_image($post_id) {
    $banner = get_post_meta($post_id, 'bis_project_banner_image', true);
    if ($banner) {
        return bis_get_optimized_image_url($banner, 'bis-banner');
    }

    $preview = get_post_meta($post_id, 'bis_project_preview_image', true);
    if ($preview) {
        return bis_get_optimized_image_url($preview, 'bis-banner');
    }

    $legacy = get_post_meta($post_id, 'bis_project_image', true);
    if ($legacy) {
        return bis_get_optimized_image_url($legacy, 'bis-banner');
    }

    return bis_get_post_thumbnail_optimized_url($post_id, 'bis-banner');
}

function bis_get_project_banner_title($post_id) {
    return get_the_title($post_id);
}

function bis_get_project_banner_blocks($post_id) {
    $blocks = get_post_meta($post_id, 'bis_project_banner_blocks', true);
    return is_array($blocks) ? $blocks : array();
}

function bis_get_project_description($post_id) {
    $description = get_post_meta($post_id, 'bis_project_description', true);
    return is_string($description) ? trim($description) : '';
}

function bis_get_project_gallery($post_id) {
    $gallery = get_post_meta($post_id, 'bis_project_gallery', true);
    return is_array($gallery) ? $gallery : array();
}

function bis_get_gratitude_image_url($post_id) {
    $custom = get_post_meta($post_id, 'bis_gratitude_image', true);
    if ($custom) {
        return bis_get_optimized_image_url($custom, 'bis-card');
    }

    return bis_get_post_thumbnail_optimized_url($post_id, 'bis-card');
}

function bis_get_news_placeholder_image_url() {
    return get_template_directory_uri() . '/assets/img/placeholder600x400.png';
}

function bis_get_news_image_url($post_id) {
    $custom = get_post_meta($post_id, 'bis_news_image', true);
    if ($custom) {
        return bis_get_optimized_image_url($custom, 'full');
    }

    $thumb = bis_get_post_thumbnail_optimized_url($post_id, 'full');
    if ($thumb) {
        return $thumb;
    }

    return bis_get_news_placeholder_image_url();
}

function bis_get_news_banner_image_url($post_id) {
    $banner = get_post_meta($post_id, 'bis_news_banner_image', true);
    if ($banner) {
        return bis_get_optimized_image_url($banner, 'full');
    }

    $custom = get_post_meta($post_id, 'bis_news_image', true);
    if ($custom) {
        return bis_get_optimized_image_url($custom, 'full');
    }

    $thumb = bis_get_post_thumbnail_optimized_url($post_id, 'full');
    if ($thumb) {
        return $thumb;
    }

    return bis_get_news_placeholder_image_url();
}

function bis_get_service_preview_image_url($post_id) {
    $custom = get_post_meta($post_id, 'bis_service_preview_image', true);
    if ($custom) {
        return bis_get_optimized_image_url($custom, 'bis-card');
    }

    $legacy = get_post_meta($post_id, 'bis_service_image', true);
    if ($legacy) {
        return bis_get_optimized_image_url($legacy, 'bis-card');
    }

    return bis_get_post_thumbnail_optimized_url($post_id, 'bis-card');
}

function bis_get_service_banner_image_url($post_id) {
    $custom = get_post_meta($post_id, 'bis_service_banner_image', true);
    if ($custom) {
        return bis_get_optimized_image_url($custom, 'bis-banner');
    }

    $preview = get_post_meta($post_id, 'bis_service_preview_image', true);
    if ($preview) {
        return bis_get_optimized_image_url($preview, 'bis-banner');
    }

    $legacy = get_post_meta($post_id, 'bis_service_image', true);
    if ($legacy) {
        return bis_get_optimized_image_url($legacy, 'bis-banner');
    }

    return bis_get_post_thumbnail_optimized_url($post_id, 'bis-banner');
}

function bis_get_service_image_url($post_id) {
    return bis_get_service_preview_image_url($post_id);
}

function bis_get_service_description($post_id) {
    $description = trim((string) get_post_meta($post_id, 'bis_service_description', true));
    if ($description !== '') {
        return $description;
    }

    $excerpt = trim((string) get_the_excerpt($post_id));
    if ($excerpt !== '') {
        return $excerpt;
    }

    $post = get_post($post_id);
    if ($post instanceof WP_Post && '' !== trim((string) $post->post_content)) {
        return wp_trim_words(wp_strip_all_tags($post->post_content), 28);
    }

    return '';
}

function bis_service_should_show_in_catalog($post_id) {
    $post = get_post($post_id);
    if (!($post instanceof WP_Post) || 'bis_service' !== $post->post_type) {
        return false;
    }

    $show_in_catalog = get_post_meta($post_id, 'bis_service_show_in_catalog', true);
    if ($show_in_catalog === '1') {
        return true;
    }

    if ($show_in_catalog === '0') {
        return false;
    }

    return (int) $post->post_parent === 0;
}

function bis_get_catalog_services($args = array()) {
    $defaults = array(
        'post_type'      => 'bis_service',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => array('menu_order' => 'ASC', 'title' => 'ASC'),
    );

    $query_args = wp_parse_args($args, $defaults);
    $query_args['post_type'] = 'bis_service';
    $query_args['post_status'] = 'publish';
    $query_args['posts_per_page'] = -1;

    $posts = get_posts($query_args);
    return array_values(array_filter($posts, function ($post) {
        return $post instanceof WP_Post && bis_service_should_show_in_catalog($post->ID);
    }));
}

function bis_get_associated_services($service_id) {
    $service_id = (int) $service_id;
    if ($service_id <= 0) {
        return array();
    }

    return get_posts(array(
        'post_type'      => 'bis_service',
        'post_status'    => 'publish',
        'post_parent'    => $service_id,
        'posts_per_page' => -1,
        'orderby'        => array('menu_order' => 'ASC', 'title' => 'ASC'),
    ));
}

function bis_service_has_associated_services($service_id) {
    $service_id = (int) $service_id;
    if ($service_id <= 0) {
        return false;
    }

    $children = get_posts(array(
        'post_type'              => 'bis_service',
        'post_status'            => 'publish',
        'post_parent'            => $service_id,
        'posts_per_page'         => 1,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ));

    return !empty($children);
}

function bis_services_have_associated_services($services) {
    if (empty($services) || !is_array($services)) {
        return false;
    }

    foreach ($services as $service) {
        $service_id = $service instanceof WP_Post ? $service->ID : (int) $service;
        if (bis_service_has_associated_services($service_id)) {
            return true;
        }
    }

    return false;
}

function bis_get_service_sibling_services($service_id, $limit = 4) {
    $post = get_post($service_id);
    if (!($post instanceof WP_Post) || 'bis_service' !== $post->post_type || (int) $post->post_parent <= 0) {
        return array();
    }

    return get_posts(array(
        'post_type'      => 'bis_service',
        'post_status'    => 'publish',
        'post_parent'    => (int) $post->post_parent,
        'posts_per_page' => (int) $limit,
        'post__not_in'   => array((int) $service_id),
        'orderby'        => array('menu_order' => 'ASC', 'title' => 'ASC'),
    ));
}

function bis_render_service_card($service_id) {
    $service_id = (int) $service_id;
    if ($service_id <= 0) {
        return;
    }

    $image_url = bis_get_service_preview_image_url($service_id);
    $description = bis_get_service_description($service_id);
    $children = bis_get_associated_services($service_id);
    $has_children = !empty($children);
    $card_classes = 'service-card' . ($has_children ? ' service-card--has-children' : '');
    ?>
    <article class="<?php echo esc_attr($card_classes); ?>">
        <a class="service-card__main" href="<?php echo esc_url(get_permalink($service_id)); ?>">
            <div class="service-image">
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr(get_the_title($service_id)); ?>" loading="lazy" decoding="async">
            </div>
            <div class="service-content">
                <div class="service-content-main">
                    <h3><?php echo esc_html(get_the_title($service_id)); ?></h3>
                    <?php if ($description !== '') : ?>
                        <p class="experience-description"><?php echo esc_html($description); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </a>
        <?php if ($has_children) : ?>
            <nav class="service-card__submenu" aria-label="<?php echo esc_attr('Подуслуги: ' . get_the_title($service_id)); ?>">
                <?php foreach ($children as $child) : ?>
                    <a class="service-card__submenu-link" href="<?php echo esc_url(get_permalink($child)); ?>"><?php echo esc_html(get_the_title($child)); ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
    </article>
    <?php
}

function bis_get_equipment_image_url($post_id) {
    $custom = get_post_meta($post_id, 'bis_equipment_image', true);
    if ($custom) {
        return bis_get_optimized_image_url($custom, 'bis-card');
    }

    return bis_get_post_thumbnail_optimized_url($post_id, 'bis-card');
}

function bis_get_team_members() {
    $members = get_option('bis_team_members', array());
    if (!is_array($members)) {
        return array();
    }

    $filtered = array();
    foreach ($members as $member) {
        if (!is_array($member)) {
            continue;
        }
        $filtered[] = $member;
    }

    return $filtered;
}

function bis_get_team_member_photo_url($member, $size = 'bis-team') {
    if (!is_array($member)) {
        return '';
    }

    $photo = isset($member['photo']) ? $member['photo'] : '';
    return bis_get_optimized_image_url($photo, $size);
}

function bis_get_team_member_modal_photo_url($member, $size = 'bis-team-modal') {
    if (!is_array($member)) {
        return '';
    }

    $modal_photo = isset($member['modal_photo']) ? $member['modal_photo'] : '';
    if ($modal_photo) {
        return bis_get_optimized_image_url($modal_photo, $size);
    }

    $photo = isset($member['photo']) ? $member['photo'] : '';
    return bis_get_optimized_image_url($photo, $size);
}

function bis_get_phone_href($phone) {
    $phone = trim((string) $phone);
    if ($phone === '') {
        return '';
    }

    $digits = preg_replace('/\D+/', '', $phone);
    if ($digits === '') {
        return '';
    }

    if ('8' === substr($digits, 0, 1)) {
        $digits = '7' . substr($digits, 1);
    }

    if ('+' !== substr($phone, 0, 1)) {
        $digits = '+' . $digits;
    } else {
        $digits = '+' . ltrim($digits, '+');
    }

    return 'tel:' . $digits;
}
