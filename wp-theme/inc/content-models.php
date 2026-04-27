<?php

function bis_register_news_cpt() {
    $labels = array(
        'name'                     => 'Новости',
        'singular_name'            => 'Новость',
        'add_new'                  => 'Добавить новость',
        'add_new_item'             => 'Добавить новую новость',
        'edit_item'                => 'Редактировать новость',
        'new_item'                 => 'Новая новость',
        'view_item'                => 'Просмотр новости',
        'search_items'             => 'Поиск новостей',
        'not_found'                => 'Новости не найдены',
        'not_found_in_trash'       => 'В корзине нет новостей',
        'all_items'                => 'Все новости',
        'archives'                 => 'Архив новостей',
        'attributes'               => 'Атрибуты новости',
        'insert_into_item'         => 'Вставить в новость',
        'uploaded_to_this_item'    => 'Загружено для этой новости',
        'menu_name'                => 'Новости',
        'filter_items_list'        => 'Фильтровать новости',
        'items_list_navigation'    => 'Навигация по новостям',
        'items_list'               => 'Список новостей',
        'name_admin_bar'           => 'Новость',
    );

    register_post_type('bis_news', array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => array('slug' => 'news'),
        'menu_icon'          => 'dashicons-media-document',
        'show_in_rest'       => true, // Enables the block editor
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions'),
    ));
}
add_action('init', 'bis_register_news_cpt');

/**
 * Registers projects custom post type to manage portfolio objects.
 */
function bis_register_projects_cpt() {
    $labels = array(
        'name'               => 'Проекты',
        'singular_name'      => 'Проект',
        'add_new'            => 'Добавить проект',
        'add_new_item'       => 'Добавить новый проект',
        'edit_item'          => 'Редактировать проект',
        'new_item'           => 'Новый проект',
        'view_item'          => 'Просмотр проекта',
        'search_items'       => 'Искать проекты',
        'not_found'          => 'Проекты не найдены',
        'not_found_in_trash' => 'В корзине нет проектов',
        'all_items'          => 'Все проекты',
        'menu_name'          => 'Проекты',
        'name_admin_bar'     => 'Проект',
    );

    register_post_type('bis_project', array(
        'labels'        => $labels,
        'public'        => true,
        'has_archive'   => false,
        'rewrite'       => array('slug' => 'projects', 'with_front' => false),
        'menu_icon'     => 'dashicons-portfolio',
        'show_in_rest'  => true,
        'supports'      => array('title'),
    ));
}
add_action('init', 'bis_register_projects_cpt');

function bis_get_latin_slug_post_types() {
    return array('bis_news', 'bis_project', 'bis_service');
}

function bis_should_force_latin_slug($post_type) {
    return in_array($post_type, bis_get_latin_slug_post_types(), true);
}

function bis_transliterate_to_latin_slug($value) {
    $value = html_entity_decode(wp_strip_all_tags((string) $value), ENT_QUOTES, 'UTF-8');

    if ($value === '') {
        return '';
    }

    $map = array(
        'А' => 'A',   'Б' => 'B',   'В' => 'V',   'Г' => 'G',   'Д' => 'D',
        'Е' => 'E',   'Ё' => 'Yo',  'Ж' => 'Zh',  'З' => 'Z',   'И' => 'I',
        'Й' => 'Y',   'К' => 'K',   'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',   'С' => 'S',   'Т' => 'T',
        'У' => 'U',   'Ф' => 'F',   'Х' => 'Kh',  'Ц' => 'Ts',  'Ч' => 'Ch',
        'Ш' => 'Sh',  'Щ' => 'Shch','Ъ' => '',    'Ы' => 'Y',   'Ь' => '',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
        'а' => 'a',   'б' => 'b',   'в' => 'v',   'г' => 'g',   'д' => 'd',
        'е' => 'e',   'ё' => 'yo',  'ж' => 'zh',  'з' => 'z',   'и' => 'i',
        'й' => 'y',   'к' => 'k',   'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',   'с' => 's',   'т' => 't',
        'у' => 'u',   'ф' => 'f',   'х' => 'kh',  'ц' => 'ts',  'ч' => 'ch',
        'ш' => 'sh',  'щ' => 'shch','ъ' => '',    'ы' => 'y',   'ь' => '',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
    );

    $value = strtr($value, $map);
    $value = remove_accents($value);

    if (function_exists('iconv')) {
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($transliterated !== false) {
            $value = $transliterated;
        }
    }

    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);
    $value = trim((string) $value, '-');

    return sanitize_title($value);
}

function bis_build_forced_latin_slug($post_type, $title, $post_id = 0, $post_status = 'publish', $post_parent = 0) {
    $base_slug = bis_transliterate_to_latin_slug($title);

    if ($base_slug === '') {
        $fallback_map = array(
            'bis_news' => 'news-item',
            'bis_project' => 'project',
            'bis_service' => 'service',
        );
        $base_slug = isset($fallback_map[$post_type]) ? $fallback_map[$post_type] : 'entry';
    }

    return wp_unique_post_slug($base_slug, $post_id, $post_status, $post_type, $post_parent);
}

function bis_force_latin_slug_on_save($data, $postarr) {
    if (empty($data['post_type']) || !bis_should_force_latin_slug($data['post_type'])) {
        return $data;
    }

    if (in_array($data['post_status'], array('auto-draft', 'trash'), true)) {
        return $data;
    }

    $title = isset($data['post_title']) ? trim((string) $data['post_title']) : '';
    if ($title === '') {
        return $data;
    }

    $post_id = !empty($postarr['ID']) ? (int) $postarr['ID'] : 0;
    $post_parent = isset($data['post_parent']) ? (int) $data['post_parent'] : 0;

    $data['post_name'] = bis_build_forced_latin_slug(
        $data['post_type'],
        $title,
        $post_id,
        $data['post_status'],
        $post_parent
    );

    return $data;
}
add_filter('wp_insert_post_data', 'bis_force_latin_slug_on_save', 20, 2);

function bis_migrate_existing_latin_slugs() {
    $current_version = (int) get_option('bis_slug_migration_version', 0);
    $target_version = 2;

    if ($current_version >= $target_version) {
        return;
    }

    $post_ids = get_posts(array(
        'post_type'      => bis_get_latin_slug_post_types(),
        'post_status'    => array('publish', 'future', 'draft', 'pending', 'private'),
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'orderby'        => 'ID',
        'order'          => 'ASC',
    ));

    if (!empty($post_ids)) {
        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);
            if (!($post instanceof WP_Post) || !bis_should_force_latin_slug($post->post_type)) {
                continue;
            }

            $new_slug = bis_build_forced_latin_slug(
                $post->post_type,
                $post->post_title,
                $post->ID,
                $post->post_status,
                $post->post_parent
            );

            if ($new_slug === '' || $new_slug === $post->post_name) {
                continue;
            }

            wp_update_post(array(
                'ID' => $post->ID,
                'post_name' => $new_slug,
            ));
        }
    }

    update_option('bis_slug_migration_version', $target_version, false);
    flush_rewrite_rules(false);
}
add_action('init', 'bis_migrate_existing_latin_slugs', 99);

function bis_register_news_meta() {
    register_post_meta('bis_news', 'bis_news_image', array(
        'single'            => true,
        'type'              => 'string',
        'show_in_rest'      => true,
        'sanitize_callback' => 'esc_url_raw',
        'auth_callback'     => function () {
            return current_user_can('edit_posts');
        },
    ));
}
add_action('init', 'bis_register_news_meta');

function bis_register_project_taxonomies() {
    $type_labels = array(
        'name'              => 'Типы проектов',
        'singular_name'     => 'Тип проекта',
        'search_items'      => 'Искать типы проектов',
        'all_items'         => 'Все типы проектов',
        'edit_item'         => 'Редактировать тип проекта',
        'update_item'       => 'Обновить тип проекта',
        'add_new_item'      => 'Добавить тип проекта',
        'new_item_name'     => 'Новый тип проекта',
        'menu_name'         => 'Типы проектов',
    );

    register_taxonomy('bis_project_type', array('bis_project'), array(
        'hierarchical'      => true,
        'labels'            => $type_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'show_in_rest'      => true,
        'rewrite'           => array('slug' => 'project-type'),
    ));

    $service_labels = array(
        'name'              => 'Услуги проекта',
        'singular_name'     => 'Услуга проекта',
        'search_items'      => 'Искать услуги проекта',
        'all_items'         => 'Все услуги проекта',
        'edit_item'         => 'Редактировать услугу проекта',
        'update_item'       => 'Обновить услугу проекта',
        'add_new_item'      => 'Добавить услугу проекта',
        'new_item_name'     => 'Новая услуга проекта',
        'menu_name'         => 'Услуги проекта',
    );

    register_taxonomy('bis_project_service', array('bis_project'), array(
        'hierarchical'      => true,
        'labels'            => $service_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'show_in_rest'      => true,
        'rewrite'           => array('slug' => 'project-service'),
    ));
}
add_action('init', 'bis_register_project_taxonomies');

function bis_normalize_project_type_label($value) {
    $value = trim(wp_strip_all_tags((string) $value));
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($value, 'UTF-8');
    }

    return strtolower($value);
}

function bis_get_project_type_default_order_map() {
    return array(
        'промышленные'         => 10,
        'промышленные объекты' => 10,
        'promyshlennye'        => 10,
        'industrial'           => 10,
        'административные'     => 20,
        'administrativnye'     => 20,
        'administrative'       => 20,
        'жилые'        => 30,
        'zhilye'               => 30,
        'residential'          => 30,
    );
}

function bis_get_project_type_order_value($term) {
    if (!($term instanceof WP_Term)) {
        return 1000;
    }

    $custom_order = get_term_meta($term->term_id, 'bis_project_type_order', true);
    if ($custom_order !== '' && is_numeric($custom_order)) {
        return max(0, (int) $custom_order);
    }

    $fallback_map = bis_get_project_type_default_order_map();
    $candidates = array(
        bis_normalize_project_type_label($term->name),
        bis_normalize_project_type_label($term->slug),
        bis_normalize_project_type_label(sanitize_title($term->name)),
    );

    foreach ($candidates as $candidate) {
        if (isset($fallback_map[$candidate])) {
            return $fallback_map[$candidate];
        }
    }

    return 1000;
}

function bis_sort_project_type_terms($terms) {
    if (!is_array($terms) || empty($terms)) {
        return $terms;
    }

    usort($terms, function ($left, $right) {
        $left_order = bis_get_project_type_order_value($left);
        $right_order = bis_get_project_type_order_value($right);

        if ($left_order === $right_order) {
            return strcmp(
                bis_normalize_project_type_label($left->name),
                bis_normalize_project_type_label($right->name)
            );
        }

        return $left_order <=> $right_order;
    });

    return $terms;
}

function bis_get_project_type_sort_order_for_post($post_id) {
    $terms = get_the_terms($post_id, 'bis_project_type');
    if (!is_array($terms) || empty($terms) || is_wp_error($terms)) {
        return 1000;
    }

    $terms = bis_sort_project_type_terms($terms);
    $primary_term = reset($terms);

    return bis_get_project_type_order_value($primary_term);
}

function bis_sort_project_posts($posts) {
    if (!is_array($posts) || count($posts) < 2) {
        return $posts;
    }

    usort($posts, function ($left, $right) {
        $left_order = bis_get_project_type_sort_order_for_post($left->ID);
        $right_order = bis_get_project_type_sort_order_for_post($right->ID);

        if ($left_order !== $right_order) {
            return $left_order <=> $right_order;
        }

        $left_menu_order = (int) get_post_field('menu_order', $left->ID);
        $right_menu_order = (int) get_post_field('menu_order', $right->ID);
        if ($left_menu_order !== $right_menu_order) {
            return $left_menu_order <=> $right_menu_order;
        }

        return strcmp(
            bis_normalize_project_type_label(get_the_title($left->ID)),
            bis_normalize_project_type_label(get_the_title($right->ID))
        );
    });

    return $posts;
}

function bis_project_type_add_order_field() {
    ?>
    <div class="form-field term-order-wrap">
        <label for="bis-project-type-order">Порядок вывода</label>
        <input type="number" min="0" step="1" name="bis_project_type_order" id="bis-project-type-order" value="">
        <p>Меньшее значение выводится раньше. По умолчанию: промышленные → административные → жилые.</p>
    </div>
    <?php
}
add_action('bis_project_type_add_form_fields', 'bis_project_type_add_order_field');

function bis_project_type_edit_order_field($term) {
    $order = get_term_meta($term->term_id, 'bis_project_type_order', true);
    ?>
    <tr class="form-field term-order-wrap">
        <th scope="row"><label for="bis-project-type-order">Порядок вывода</label></th>
        <td>
            <input type="number" min="0" step="1" name="bis_project_type_order" id="bis-project-type-order" value="<?php echo esc_attr($order); ?>">
            <p class="description">Меньшее значение выводится раньше. Если поле пустое, используется базовый порядок типов.</p>
        </td>
    </tr>
    <?php
}
add_action('bis_project_type_edit_form_fields', 'bis_project_type_edit_order_field');

function bis_save_project_type_order($term_id) {
    if (!current_user_can('manage_categories')) {
        return;
    }

    if (!isset($_POST['bis_project_type_order'])) {
        return;
    }

    $order = sanitize_text_field(wp_unslash($_POST['bis_project_type_order']));

    if ($order === '') {
        delete_term_meta($term_id, 'bis_project_type_order');
        return;
    }

    update_term_meta($term_id, 'bis_project_type_order', max(0, (int) $order));
}
add_action('created_bis_project_type', 'bis_save_project_type_order');
add_action('edited_bis_project_type', 'bis_save_project_type_order');

function bis_project_type_columns($columns) {
    $columns['bis_project_type_order'] = 'Порядок';
    return $columns;
}
add_filter('manage_edit-bis_project_type_columns', 'bis_project_type_columns');

function bis_project_type_custom_column($content, $column_name, $term_id) {
    if ('bis_project_type_order' !== $column_name) {
        return $content;
    }

    $term = get_term($term_id, 'bis_project_type');
    if (!($term instanceof WP_Term)) {
        return '';
    }

    return (string) bis_get_project_type_order_value($term);
}
add_filter('manage_bis_project_type_custom_column', 'bis_project_type_custom_column', 10, 3);


function bis_projects_filter_request($query_vars) {
    if (isset($query_vars['pagename']) && 'projects' === $query_vars['pagename']) {
        if (isset($query_vars['year'])) {
            $query_vars['project_year'] = $query_vars['year'];
            unset($query_vars['year']);
        }
        if (isset($query_vars['area'])) {
            $query_vars['project_area'] = $query_vars['area'];
            unset($query_vars['area']);
        }
    }

    return $query_vars;
}
add_filter('request', 'bis_projects_filter_request');

function bis_register_services_cpt() {
    $labels = array(
        'name'               => 'Услуги',
        'singular_name'      => 'Услуга',
        'menu_name'          => 'Услуги',
        'name_admin_bar'     => 'Услуга',
        'add_new'            => 'Добавить',
        'add_new_item'       => 'Добавить услугу',
        'edit_item'          => 'Редактировать',
        'new_item'           => 'Новая услуга',
        'view_item'          => 'Просмотр',
        'search_items'       => 'Искать услуги',
        'not_found'          => 'Не найдено',
        'not_found_in_trash' => 'В корзине нет услуг',
        'all_items'          => 'Все услуги',
    );

    register_post_type('bis_service', array(
        'labels'       => $labels,
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => array('slug' => 'services', 'with_front' => false),
        'menu_icon'    => 'dashicons-admin-tools',
        'show_in_rest' => true,
        'supports'     => array('title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'),
    ));
}
add_action('init', 'bis_register_services_cpt');

function bis_register_equipment_cpt() {
    $labels = array(
        'name'               => 'Оборудование',
        'singular_name'      => 'Оборудование',
        'menu_name'          => 'Оборудование',
        'name_admin_bar'     => 'Оборудование',
        'add_new'            => 'Добавить',
        'add_new_item'       => 'Добавить оборудование',
        'edit_item'          => 'Редактировать',
        'new_item'           => 'Новое оборудование',
        'view_item'          => 'Просмотр',
        'search_items'       => 'Искать оборудование',
        'not_found'          => 'Не найдено',
        'not_found_in_trash' => 'В корзине нет оборудования',
        'all_items'          => 'Все оборудование',
    );

    register_post_type('bis_equipment', array(
        'labels'       => $labels,
        'public'       => true,
        'has_archive'  => false,
        'menu_icon'    => 'dashicons-hammer',
        'show_in_rest' => true,
        'supports'     => array('title', 'thumbnail', 'page-attributes'),
    ));
}
add_action('init', 'bis_register_equipment_cpt');

function bis_disable_project_block_editor($use_block_editor, $post_type) {
    if ('bis_project' === $post_type) {
        return false;
    }

    return $use_block_editor;
}
add_filter('use_block_editor_for_post_type', 'bis_disable_project_block_editor', 10, 2);

/**
 * Meta box with project details (address, area, year, featured flag, image).
 */
function bis_add_project_meta_boxes() {
    add_meta_box(
        'bis_project_details',
        'Детали проекта',
        'bis_project_details_metabox',
        'bis_project',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'bis_add_project_meta_boxes');

function bis_add_page_banner_meta_boxes() {
    add_meta_box(
        'bis_page_banner',
        'Баннер страницы',
        'bis_page_banner_metabox',
        'page',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'bis_add_page_banner_meta_boxes');

function bis_add_service_meta_boxes() {
    add_meta_box(
        'bis_service_details',
        'Карточка услуги',
        'bis_service_details_metabox',
        'bis_service',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'bis_add_service_meta_boxes');

function bis_add_equipment_meta_boxes() {
    add_meta_box(
        'bis_equipment_details',
        'Карточка оборудования',
        'bis_equipment_details_metabox',
        'bis_equipment',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'bis_add_equipment_meta_boxes');

function bis_add_gratitude_meta_boxes() {
    add_meta_box(
        'bis_gratitude_image',
        'Изображение благодарности',
        'bis_gratitude_image_metabox',
        'bis_gratitude',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'bis_add_gratitude_meta_boxes');

function bis_add_news_meta_boxes() {
    add_meta_box(
        'bis_news_image',
        'Изображение новости',
        'bis_news_image_metabox',
        'bis_news',
        'normal',
        'high',
        array(
            '__block_editor_compatible_meta_box' => true,
        )
    );
}
add_action('add_meta_boxes', 'bis_add_news_meta_boxes');

function bis_page_banner_metabox($post) {
    wp_nonce_field('bis_page_banner_nonce', 'bis_page_banner_nonce_field');

    $banner_title = get_post_meta($post->ID, 'bis_page_banner_title', true);
    $banner_subtitle = get_post_meta($post->ID, 'bis_page_banner_subtitle', true);
    $banner_image = get_post_meta($post->ID, 'bis_page_banner_image', true);
    $thumbnail_url = get_the_post_thumbnail_url($post->ID, 'full');
    $banner_preview = $banner_image ? $banner_image : $thumbnail_url;
    ?>
    <div class="bis-project-box">
        <div class="bis-project-box__header">
            <div>
                <h3>Текст баннера</h3>
                <p>Задайте текст для баннера страницы. Фоновое изображение берется из «Изображения записи».</p>
            </div>
        </div>

        <div class="bis-project-media bis-project-media--banner">
            <div class="bis-project-media__preview <?php echo $banner_preview ? '' : 'is-empty'; ?>" data-image-preview="bis_page_banner_image" style="background-image: url('<?php echo esc_url($banner_preview); ?>');">
                <?php if (!$banner_preview) : ?>
                    <span class="bis-project-media__placeholder">Нет изображения</span>
                <?php endif; ?>
            </div>
            <div class="bis-project-media__controls">
                <label for="bis_page_banner_image">Изображение баннера</label>
                <input type="text" id="bis_page_banner_image" name="bis_page_banner_image" value="<?php echo esc_url($banner_image); ?>" placeholder="https://" data-image-input data-preview-target="bis_page_banner_image">
                <div class="bis-project-media__buttons">
                    <button type="button" class="button button-primary bis-project-image-upload" data-target="bis_page_banner_image">Выбрать в медиабиблиотеке</button>
                    <button type="button" class="button bis-project-image-clear" data-target="bis_page_banner_image">Убрать фото</button>
                </div>
                <p class="bis-field__hint">Можно указать ссылку вручную или выбрать файл. Если поле пустое, будет использовано «Изображение записи».</p>
            </div>
        </div>

        <div class="bis-project-grid">
            <div class="bis-field">
                <label for="bis_page_banner_title">Заголовок баннера</label>
                <input type="text" id="bis_page_banner_title" name="bis_page_banner_title" value="<?php echo esc_attr($banner_title); ?>" placeholder="<?php echo esc_attr(get_the_title($post->ID)); ?>">
            </div>
            <div class="bis-field">
                <label for="bis_page_banner_subtitle">Подзаголовок</label>
                <textarea id="bis_page_banner_subtitle" name="bis_page_banner_subtitle" rows="3" placeholder="Введите подзаголовок"><?php echo esc_textarea($banner_subtitle); ?></textarea>
            </div>
        </div>
    </div>
    <?php
}

function bis_service_details_metabox($post) {
    wp_nonce_field('bis_service_details_nonce', 'bis_service_details_nonce_field');

    $description = get_post_meta($post->ID, 'bis_service_description', true);
    $image = get_post_meta($post->ID, 'bis_service_image', true);
    $thumbnail = get_the_post_thumbnail_url($post->ID, 'full');
    $preview = $image ? $image : $thumbnail;
    ?>
    <div class="bis-project-box">
        <div class="bis-project-box__header">
            <div>
                <h3>Карточка услуги</h3>
                <p>Настройте изображение и описание. Заголовок берется из названия записи.</p>
            </div>
        </div>

        <div class="bis-project-media bis-project-media--banner">
            <div class="bis-project-media__preview <?php echo $preview ? '' : 'is-empty'; ?>" data-image-preview="bis_service_image" style="background-image: url('<?php echo esc_url($preview); ?>');">
                <?php if (!$preview) : ?>
                    <span class="bis-project-media__placeholder">Нет изображения</span>
                <?php endif; ?>
            </div>
            <div class="bis-project-media__controls">
                <label for="bis_service_image">Изображение</label>
                <input type="text" id="bis_service_image" name="bis_service_image" value="<?php echo esc_url($image); ?>" placeholder="https://" data-image-input data-preview-target="bis_service_image">
                <div class="bis-project-media__buttons">
                    <button type="button" class="button button-primary bis-project-image-upload" data-target="bis_service_image">Выбрать в медиабиблиотеке</button>
                    <button type="button" class="button bis-project-image-clear" data-target="bis_service_image">Убрать фото</button>
                </div>
                <p class="bis-field__hint">Можно указать ссылку вручную или выбрать файл. Если поле пустое, используется «Изображение записи».</p>
            </div>
        </div>

        <div class="bis-field">
            <label for="bis_service_description">Описание</label>
            <textarea id="bis_service_description" name="bis_service_description" rows="4" placeholder="Краткое описание услуги"><?php echo esc_textarea($description); ?></textarea>
        </div>
    </div>
    <?php
}

function bis_equipment_details_metabox($post) {
    wp_nonce_field('bis_equipment_details_nonce', 'bis_equipment_details_nonce_field');

    $description = get_post_meta($post->ID, 'bis_equipment_description', true);
    $image = get_post_meta($post->ID, 'bis_equipment_image', true);
    $thumbnail = get_the_post_thumbnail_url($post->ID, 'full');
    $preview = $image ? $image : $thumbnail;
    ?>
    <div class="bis-project-box">
        <div class="bis-project-box__header">
            <div>
                <h3>Карточка оборудования</h3>
                <p>Настройте изображение и описание. Заголовок берется из названия записи.</p>
            </div>
        </div>

        <div class="bis-project-media bis-project-media--banner">
            <div class="bis-project-media__preview <?php echo $preview ? '' : 'is-empty'; ?>" data-image-preview="bis_equipment_image" style="background-image: url('<?php echo esc_url($preview); ?>');">
                <?php if (!$preview) : ?>
                    <span class="bis-project-media__placeholder">Нет изображения</span>
                <?php endif; ?>
            </div>
            <div class="bis-project-media__controls">
                <label for="bis_equipment_image">Изображение</label>
                <input type="text" id="bis_equipment_image" name="bis_equipment_image" value="<?php echo esc_url($image); ?>" placeholder="https://" data-image-input data-preview-target="bis_equipment_image">
                <div class="bis-project-media__buttons">
                    <button type="button" class="button button-primary bis-project-image-upload" data-target="bis_equipment_image">Выбрать в медиабиблиотеке</button>
                    <button type="button" class="button bis-project-image-clear" data-target="bis_equipment_image">Убрать фото</button>
                </div>
                <p class="bis-field__hint">Можно указать ссылку вручную или выбрать файл. Если поле пустое, используется «Изображение записи».</p>
            </div>
        </div>

        <div class="bis-field">
            <label for="bis_equipment_description">Описание</label>
            <textarea id="bis_equipment_description" name="bis_equipment_description" rows="4" placeholder="Краткое описание оборудования"><?php echo esc_textarea($description); ?></textarea>
        </div>
    </div>
    <?php
}

function bis_gratitude_image_metabox($post) {
    wp_nonce_field('bis_gratitude_image_nonce', 'bis_gratitude_image_nonce_field');

    $gratitude_image = get_post_meta($post->ID, 'bis_gratitude_image', true);
    $thumbnail_url = get_the_post_thumbnail_url($post->ID, 'full');
    $preview = $gratitude_image ? $gratitude_image : $thumbnail_url;
    ?>
    <div class="bis-project-box">
        <div class="bis-project-box__header">
            <div>
                <h3>Изображение благодарности</h3>
                <p>Можно указать ссылку или выбрать изображение из медиабиблиотеки.</p>
            </div>
        </div>

        <div class="bis-project-media bis-project-media--banner">
            <div class="bis-project-media__preview <?php echo $preview ? '' : 'is-empty'; ?>" data-image-preview="bis_gratitude_image" style="background-image: url('<?php echo esc_url($preview); ?>');">
                <?php if (!$preview) : ?>
                    <span class="bis-project-media__placeholder">Нет изображения</span>
                <?php endif; ?>
            </div>
            <div class="bis-project-media__controls">
                <label for="bis_gratitude_image">Изображение</label>
                <input type="text" id="bis_gratitude_image" name="bis_gratitude_image" value="<?php echo esc_url($gratitude_image); ?>" placeholder="https://" data-image-input data-preview-target="bis_gratitude_image">
                <div class="bis-project-media__buttons">
                    <button type="button" class="button button-primary bis-project-image-upload" data-target="bis_gratitude_image">Выбрать в медиабиблиотеке</button>
                    <button type="button" class="button bis-project-image-clear" data-target="bis_gratitude_image">Убрать фото</button>
                </div>
                <p class="bis-field__hint">Если поле пустое, будет использовано «Изображение записи».</p>
            </div>
        </div>
    </div>
    <?php
}

function bis_news_image_metabox($post) {
    wp_nonce_field('bis_news_image_nonce', 'bis_news_image_nonce_field');

    $news_image = get_post_meta($post->ID, 'bis_news_image', true);
    $thumbnail_url = get_the_post_thumbnail_url($post->ID, 'full');
    $preview = $news_image ? $news_image : $thumbnail_url;
    ?>
    <div class="bis-project-box">
        <div class="bis-project-box__header">
            <div>
                <h3>Изображение новости</h3>
                <p>Можно указать ссылку или выбрать изображение из медиабиблиотеки.</p>
            </div>
        </div>

        <div class="bis-project-media bis-project-media--banner">
            <div class="bis-project-media__preview <?php echo $preview ? '' : 'is-empty'; ?>" data-image-preview="bis_news_image" style="background-image: url('<?php echo esc_url($preview); ?>');">
                <?php if (!$preview) : ?>
                    <span class="bis-project-media__placeholder">Нет изображения</span>
                <?php endif; ?>
            </div>
            <div class="bis-project-media__controls">
                <label for="bis_news_image">Изображение</label>
                <input type="text" id="bis_news_image" name="bis_news_image" value="<?php echo esc_url($news_image); ?>" placeholder="https://" data-image-input data-preview-target="bis_news_image">
                <div class="bis-project-media__buttons">
                    <button type="button" class="button button-primary bis-project-image-upload" data-target="bis_news_image">Выбрать в медиабиблиотеке</button>
                    <button type="button" class="button bis-project-image-clear" data-target="bis_news_image">Убрать фото</button>
                </div>
                <p class="bis-field__hint">Если поле пустое, будет использовано «Изображение записи».</p>
            </div>
        </div>
    </div>
    <?php
}

function bis_project_details_metabox_legacy($post) {
    wp_nonce_field('bis_project_details_nonce', 'bis_project_details_nonce_field');

    $address   = get_post_meta($post->ID, 'bis_project_address', true);
    $area      = get_post_meta($post->ID, 'bis_project_area', true);
    $year      = get_post_meta($post->ID, 'bis_project_year', true);
    $is_key    = get_post_meta($post->ID, 'bis_project_is_featured', true);
    $image_url = get_post_meta($post->ID, 'bis_project_image', true);
    $banner_image = get_post_meta($post->ID, 'bis_project_banner_image', true);
    $banner_layers = get_post_meta($post->ID, 'bis_project_banner_layers', true);
    $gallery = get_post_meta($post->ID, 'bis_project_gallery', true);

    if (!is_array($banner_layers)) {
        $banner_layers = array();
    }

    if (!is_array($gallery)) {
        $gallery = array();
    }

    $thumbnail_url = get_the_post_thumbnail_url($post->ID, 'full');
    $banner_preview = $banner_image ? $banner_image : ($image_url ? $image_url : $thumbnail_url);
    ?>
    <div class="bis-project-box">
        <div class="bis-project-box__header">
            <div>
                <h3>Детали проекта</h3>
                <p>Заполните основные параметры и выберите фото. Эти данные попадут на главную и в модальное окно проектов.</p>
            </div>
            <div class="bis-project-box__status <?php echo $is_key ? 'is-featured' : ''; ?>" data-featured-badge>
                <?php echo $is_key ? 'Ключевой проект' : 'Обычный проект'; ?>
            </div>
        </div>

        <div class="bis-project-grid">
            <div class="bis-field">
                <label for="bis_project_address">Адрес / локация</label>
                <input type="text" id="bis_project_address" name="bis_project_address" value="<?php echo esc_attr($address); ?>" placeholder="Москва, Кутузовский проспект">
                <p class="bis-field__hint">Город, бизнес-центр или точная площадка.</p>
            </div>

            <div class="bis-field">
                <label for="bis_project_area">Площадь (м²)</label>
                <input type="text" id="bis_project_area" name="bis_project_area" value="<?php echo esc_attr($area); ?>" placeholder="45 000">
                <p class="bis-field__hint">Укажите цифрой, без м² — мы добавим автоматически.</p>
            </div>

            <div class="bis-field">
                <label for="bis_project_year">Год</label>
                <input type="text" id="bis_project_year" name="bis_project_year" value="<?php echo esc_attr($year); ?>" placeholder="2024">
                <p class="bis-field__hint">Год завершения или активной фазы.</p>
            </div>
        </div>

        <div class="bis-project-media">
            <div class="bis-project-media__preview" data-image-preview="bis_project_image" style="background-image: url('<?php echo esc_url($image_url); ?>');">
                <?php if (!$image_url) : ?>
                    <span class="bis-project-media__placeholder">Нет изображения</span>
                <?php endif; ?>
            </div>
            <div class="bis-project-media__controls">
                <label for="bis_project_image">Фото проекта</label>
                <input type="text" id="bis_project_image" name="bis_project_image" value="<?php echo esc_url($image_url); ?>" placeholder="https://" data-image-input data-preview-target="bis_project_image">
                <div class="bis-project-media__buttons">
                    <button type="button" class="button button-primary bis-project-image-upload" data-target="bis_project_image" data-preview="project">Выбрать в медиабиблиотеке</button>
                    <button type="button" class="button bis-project-image-clear" data-target="bis_project_image" data-preview="project">Убрать фото</button>
                </div>
                <p class="bis-field__hint">Лучше использовать горизонтальные изображения 1200px+ для четкой обложки.</p>
            </div>
        </div>

        <div class="bis-project-section">
            <div class="bis-project-section__header">
                <h4>Страница проекта</h4>
                <p class="bis-field__hint">Задайте баннер и тексты, которые будут показаны на странице проекта. Перетаскивайте текст прямо в превью.</p>
            </div>

            <div class="bis-project-media bis-project-media--banner">
                <div class="bis-project-media__preview <?php echo $banner_preview ? '' : 'is-empty'; ?>" data-image-preview="bis_project_banner_image" style="background-image: url('<?php echo esc_url($banner_preview); ?>');">
                    <?php if (!$banner_preview) : ?>
                        <span class="bis-project-media__placeholder">Нет изображения</span>
                    <?php endif; ?>
                </div>
                <div class="bis-project-media__controls">
                    <label for="bis_project_banner_image">Баннер проекта</label>
                    <input type="text" id="bis_project_banner_image" name="bis_project_banner_image" value="<?php echo esc_url($banner_image); ?>" placeholder="https://" data-image-input data-preview-target="bis_project_banner_image">
                    <div class="bis-project-media__buttons">
                        <button type="button" class="button button-primary bis-project-image-upload" data-target="bis_project_banner_image" data-preview="banner">Выбрать баннер</button>
                        <button type="button" class="button bis-project-image-clear" data-target="bis_project_banner_image" data-preview="banner">Убрать фото</button>
                    </div>
                    <p class="bis-field__hint">Если баннер не выбран, используется фото проекта или миниатюра записи.</p>
                </div>
            </div>

            <div class="bis-project-banner-builder">
                <div class="bis-project-banner-previews">
                    <div class="bis-banner-preview" data-banner-preview="desktop" style="background-image: url('<?php echo esc_url($banner_preview); ?>');">
                        <?php foreach ($banner_layers as $index => $layer) :
                            $text = isset($layer['text']) ? $layer['text'] : '';
                            $size = isset($layer['size']) ? $layer['size'] : 'md';
                            $align = isset($layer['align']) ? $layer['align'] : 'left';
                            $dx = isset($layer['desktop_x']) ? $layer['desktop_x'] : 50;
                            $dy = isset($layer['desktop_y']) ? $layer['desktop_y'] : 50;
                            $mx = isset($layer['mobile_x']) ? $layer['mobile_x'] : $dx;
                            $my = isset($layer['mobile_y']) ? $layer['mobile_y'] : $dy;
                            ?>
                            <div class="bis-banner-preview__layer is-<?php echo esc_attr($size); ?> is-align-<?php echo esc_attr($align); ?>" data-layer-index="<?php echo esc_attr($index); ?>" style="left: <?php echo esc_attr($dx); ?>%; top: <?php echo esc_attr($dy); ?>%;">
                                <?php echo esc_html($text); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="bis-banner-preview bis-banner-preview--mobile" data-banner-preview="mobile" style="background-image: url('<?php echo esc_url($banner_preview); ?>');">
                        <?php foreach ($banner_layers as $index => $layer) :
                            $text = isset($layer['text']) ? $layer['text'] : '';
                            $size = isset($layer['size']) ? $layer['size'] : 'md';
                            $align = isset($layer['align']) ? $layer['align'] : 'left';
                            $dx = isset($layer['desktop_x']) ? $layer['desktop_x'] : 50;
                            $dy = isset($layer['desktop_y']) ? $layer['desktop_y'] : 50;
                            $mx = isset($layer['mobile_x']) ? $layer['mobile_x'] : $dx;
                            $my = isset($layer['mobile_y']) ? $layer['mobile_y'] : $dy;
                            ?>
                            <div class="bis-banner-preview__layer is-<?php echo esc_attr($size); ?> is-align-<?php echo esc_attr($align); ?>" data-layer-index="<?php echo esc_attr($index); ?>" style="left: <?php echo esc_attr($mx); ?>%; top: <?php echo esc_attr($my); ?>%;">
                                <?php echo esc_html($text); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bis-project-banner-list">
                    <ul id="bis-project-banner-layers" class="bis-banner-layer-list">
                        <?php foreach ($banner_layers as $index => $layer) :
                            $text = isset($layer['text']) ? $layer['text'] : '';
                            $size = isset($layer['size']) ? $layer['size'] : 'md';
                            $align = isset($layer['align']) ? $layer['align'] : 'left';
                            $dx = isset($layer['desktop_x']) ? $layer['desktop_x'] : 50;
                            $dy = isset($layer['desktop_y']) ? $layer['desktop_y'] : 50;
                            $mx = isset($layer['mobile_x']) ? $layer['mobile_x'] : $dx;
                            $my = isset($layer['mobile_y']) ? $layer['mobile_y'] : $dy;
                            ?>
                            <li class="bis-banner-layer-item" data-index="<?php echo esc_attr($index); ?>">
                                <div class="bis-banner-layer-item__header">
                                    <span class="dashicons dashicons-move handle" aria-hidden="true"></span>
                                    <strong>Текст <?php echo esc_html($index + 1); ?></strong>
                                    <button type="button" class="button-link-delete bis-banner-layer-remove">Удалить</button>
                                </div>
                                <div class="bis-banner-layer-fields">
                                    <label>Текст</label>
                                    <textarea rows="2" data-field="text" name="bis_project_banner_layers[<?php echo esc_attr($index); ?>][text]" placeholder="Введите текст"><?php echo esc_textarea($text); ?></textarea>
                                    <div class="bis-banner-layer-row">
                                        <div class="bis-banner-layer-field">
                                            <label>Размер</label>
                                            <select data-field="size" name="bis_project_banner_layers[<?php echo esc_attr($index); ?>][size]">
                                                <option value="xl" <?php selected($size, 'xl'); ?>>XL</option>
                                                <option value="lg" <?php selected($size, 'lg'); ?>>L</option>
                                                <option value="md" <?php selected($size, 'md'); ?>>M</option>
                                                <option value="sm" <?php selected($size, 'sm'); ?>>S</option>
                                            </select>
                                        </div>
                                        <div class="bis-banner-layer-field">
                                            <label>Выравнивание</label>
                                            <select data-field="align" name="bis_project_banner_layers[<?php echo esc_attr($index); ?>][align]">
                                                <option value="left" <?php selected($align, 'left'); ?>>Слева</option>
                                                <option value="center" <?php selected($align, 'center'); ?>>По центру</option>
                                                <option value="right" <?php selected($align, 'right'); ?>>Справа</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="bis-banner-layer-row">
                                        <div class="bis-banner-layer-field">
                                            <label>Desktop X (%)</label>
                                            <input type="number" step="0.1" min="0" max="100" data-field="desktop_x" name="bis_project_banner_layers[<?php echo esc_attr($index); ?>][desktop_x]" value="<?php echo esc_attr($dx); ?>">
                                        </div>
                                        <div class="bis-banner-layer-field">
                                            <label>Desktop Y (%)</label>
                                            <input type="number" step="0.1" min="0" max="100" data-field="desktop_y" name="bis_project_banner_layers[<?php echo esc_attr($index); ?>][desktop_y]" value="<?php echo esc_attr($dy); ?>">
                                        </div>
                                        <div class="bis-banner-layer-field">
                                            <label>Mobile X (%)</label>
                                            <input type="number" step="0.1" min="0" max="100" data-field="mobile_x" name="bis_project_banner_layers[<?php echo esc_attr($index); ?>][mobile_x]" value="<?php echo esc_attr($mx); ?>">
                                        </div>
                                        <div class="bis-banner-layer-field">
                                            <label>Mobile Y (%)</label>
                                            <input type="number" step="0.1" min="0" max="100" data-field="mobile_y" name="bis_project_banner_layers[<?php echo esc_attr($index); ?>][mobile_y]" value="<?php echo esc_attr($my); ?>">
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="button" id="bis-add-banner-layer">Добавить текст</button>
                </div>
            </div>
        </div>

        <div class="bis-project-section">
            <div class="bis-project-section__header">
                <h4>Галерея проекта</h4>
                <p class="bis-field__hint">Фото для слайдера на странице проекта. Можно менять порядок перетаскиванием.</p>
            </div>
            <div class="bis-project-gallery-admin">
                <ul id="bis-project-gallery-list" class="bis-project-gallery-list">
                    <?php foreach ($gallery as $image) : ?>
                        <li class="bis-project-gallery-item">
                            <div class="bis-project-gallery-thumb" style="background-image: url('<?php echo esc_url($image); ?>');"></div>
                            <input type="hidden" name="bis_project_gallery[]" value="<?php echo esc_url($image); ?>">
                            <button type="button" class="button-link-delete bis-project-gallery-remove">Удалить</button>
                            <span class="dashicons dashicons-move handle" aria-hidden="true"></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="button" id="bis-project-gallery-add">Добавить фото</button>
            </div>
        </div>

        <script type="text/template" id="bis-project-banner-layer-template">
            <li class="bis-banner-layer-item" data-index="">
                <div class="bis-banner-layer-item__header">
                    <span class="dashicons dashicons-move handle" aria-hidden="true"></span>
                    <strong>Текст</strong>
                    <button type="button" class="button-link-delete bis-banner-layer-remove">Удалить</button>
                </div>
                <div class="bis-banner-layer-fields">
                    <label>Текст</label>
                    <textarea rows="2" data-field="text" placeholder="Введите текст"></textarea>
                    <div class="bis-banner-layer-row">
                        <div class="bis-banner-layer-field">
                            <label>Размер</label>
                            <select data-field="size">
                                <option value="xl">XL</option>
                                <option value="lg">L</option>
                                <option value="md" selected>M</option>
                                <option value="sm">S</option>
                            </select>
                        </div>
                        <div class="bis-banner-layer-field">
                            <label>Выравнивание</label>
                            <select data-field="align">
                                <option value="left" selected>Слева</option>
                                <option value="center">По центру</option>
                                <option value="right">Справа</option>
                            </select>
                        </div>
                    </div>
                    <div class="bis-banner-layer-row">
                        <div class="bis-banner-layer-field">
                            <label>Desktop X (%)</label>
                            <input type="number" step="0.1" min="0" max="100" data-field="desktop_x" value="20">
                        </div>
                        <div class="bis-banner-layer-field">
                            <label>Desktop Y (%)</label>
                            <input type="number" step="0.1" min="0" max="100" data-field="desktop_y" value="30">
                        </div>
                        <div class="bis-banner-layer-field">
                            <label>Mobile X (%)</label>
                            <input type="number" step="0.1" min="0" max="100" data-field="mobile_x" value="20">
                        </div>
                        <div class="bis-banner-layer-field">
                            <label>Mobile Y (%)</label>
                            <input type="number" step="0.1" min="0" max="100" data-field="mobile_y" value="30">
                        </div>
                    </div>
                </div>
            </li>
        </script>
        <script type="text/template" id="bis-project-gallery-item-template">
            <li class="bis-project-gallery-item">
                <div class="bis-project-gallery-thumb"></div>
                <input type="hidden" value="">
                <button type="button" class="button-link-delete bis-project-gallery-remove">Удалить</button>
                <span class="dashicons dashicons-move handle" aria-hidden="true"></span>
            </li>
        </script>

        <div class="bis-project-toggle">
            <label class="bis-switch">
                <input type="checkbox" name="bis_project_is_featured" value="1" <?php checked($is_key, '1'); ?> data-featured-toggle>
                <span class="bis-switch__slider"></span>
                <span class="bis-switch__label">Показать в блоке «Ключевые проекты»</span>
            </label>
            <p class="bis-field__hint">Ключевые проекты отображаются на главной в верхнем списке, остальные — в блоке «Все проекты».</p>
        </div>
    </div>
    <?php
}

function bis_project_details_metabox($post) {
    wp_nonce_field('bis_project_details_nonce', 'bis_project_details_nonce_field');

    $is_key = get_post_meta($post->ID, 'bis_project_is_featured', true);
    $preview_image = get_post_meta($post->ID, 'bis_project_preview_image', true);
    $banner_image = get_post_meta($post->ID, 'bis_project_banner_image', true);
    $banner_title = get_post_meta($post->ID, 'bis_project_banner_title', true);
    $banner_blocks = get_post_meta($post->ID, 'bis_project_banner_blocks', true);
    $project_description = get_post_meta($post->ID, 'bis_project_description', true);
    $gallery = get_post_meta($post->ID, 'bis_project_gallery', true);

    if (!is_array($banner_blocks)) {
        $banner_blocks = array();
    }

    if (!is_array($gallery)) {
        $gallery = array();
    }

    $default_blocks = array(
        'top_left' => array('label' => '', 'value' => ''),
        'bottom_left' => array('label' => '', 'value' => ''),
        'top_right' => array('label' => '', 'value' => ''),
        'bottom_right' => array('label' => '', 'value' => ''),
    );
    $banner_blocks = wp_parse_args($banner_blocks, $default_blocks);

    $legacy_year = get_post_meta($post->ID, 'bis_project_year', true);
    $legacy_area = get_post_meta($post->ID, 'bis_project_area', true);
    $legacy_address = get_post_meta($post->ID, 'bis_project_address', true);

    $has_blocks = false;
    foreach ($banner_blocks as $block) {
        if (!empty($block['label']) || !empty($block['value'])) {
            $has_blocks = true;
            break;
        }
    }

    if (!$has_blocks) {
        if ($legacy_year) {
            $banner_blocks['top_left'] = array('label' => 'Год реализации', 'value' => $legacy_year);
        }
        if ($legacy_address) {
            $banner_blocks['bottom_left'] = array('label' => 'Адрес', 'value' => $legacy_address);
        }
        if ($legacy_area) {
            $area_value = $legacy_area;
            if (!preg_match('/\b(м2|м²|m2|m²)\b/iu', $area_value)) {
                $area_value .= ' м²';
            }
            $banner_blocks['top_right'] = array('label' => 'Площадь', 'value' => $area_value);
        }
    }

    $legacy_image = get_post_meta($post->ID, 'bis_project_image', true);
    $thumbnail_url = get_the_post_thumbnail_url($post->ID, 'full');
    $preview_fallback = $preview_image ? $preview_image : ($legacy_image ? $legacy_image : ($banner_image ? $banner_image : $thumbnail_url));
    $banner_preview = $banner_image ? $banner_image : ($legacy_image ? $legacy_image : $thumbnail_url);
    ?>
    <div class="bis-project-box">
        <div class="bis-project-box__header">
            <div>
                <h3>Страница проекта</h3>
                <p>Настройте баннер и галерею проекта. Тексты блоков можно задавать вручную.</p>
            </div>
            <div class="bis-project-box__status <?php echo $is_key ? 'is-featured' : ''; ?>" data-featured-badge>
                <?php echo $is_key ? 'Ключевой проект' : 'Обычный проект'; ?>
            </div>
        </div>

        <div class="bis-project-media">
            <div class="bis-project-media__preview <?php echo $preview_fallback ? '' : 'is-empty'; ?>" data-image-preview="bis_project_preview_image" style="background-image: url('<?php echo esc_url($preview_fallback); ?>');">
                <?php if (!$preview_fallback) : ?>
                    <span class="bis-project-media__placeholder">Нет изображения</span>
                <?php endif; ?>
            </div>
            <div class="bis-project-media__controls">
                <label for="bis_project_preview_image">Превью проекта</label>
                <input type="text" id="bis_project_preview_image" name="bis_project_preview_image" value="<?php echo esc_url($preview_image); ?>" placeholder="https://" data-image-input data-preview-target="bis_project_preview_image">
                <div class="bis-project-media__buttons">
                    <button type="button" class="button button-primary bis-project-image-upload" data-target="bis_project_preview_image" data-preview="preview">Выбрать в медиабиблиотеке</button>
                    <button type="button" class="button bis-project-image-clear" data-target="bis_project_preview_image" data-preview="preview">Убрать фото</button>
                </div>
                <p class="bis-field__hint">Используется в карточках проекта. Если не задано, берется старое превью, баннер или миниатюра записи.</p>
            </div>
        </div>

        <div class="bis-project-media">
            <div class="bis-project-media__preview <?php echo $banner_preview ? '' : 'is-empty'; ?>" data-image-preview="bis_project_banner_image" style="background-image: url('<?php echo esc_url($banner_preview); ?>');">
                <?php if (!$banner_preview) : ?>
                    <span class="bis-project-media__placeholder">Нет изображения</span>
                <?php endif; ?>
            </div>
            <div class="bis-project-media__controls">
                <label for="bis_project_banner_image">Главное изображение (баннер)</label>
                <input type="text" id="bis_project_banner_image" name="bis_project_banner_image" value="<?php echo esc_url($banner_image); ?>" placeholder="https://" data-image-input data-preview-target="bis_project_banner_image">
                <div class="bis-project-media__buttons">
                    <button type="button" class="button button-primary bis-project-image-upload" data-target="bis_project_banner_image" data-preview="banner">Выбрать в медиабиблиотеке</button>
                    <button type="button" class="button bis-project-image-clear" data-target="bis_project_banner_image" data-preview="banner">Убрать фото</button>
                </div>
                <p class="bis-field__hint">Изображение используется только в баннере страницы проекта.</p>
            </div>
        </div>

        <div class="bis-project-section">
            <div class="bis-project-section__header">
                <h4>Параметры проекта</h4>
                <p class="bis-field__hint">Поля требуются для фильтров на странице проектов.</p>
            </div>
            <div class="bis-project-grid">
                <div class="bis-field">
                    <label for="bis_project_area">Площадь (м²)</label>
                    <input type="text" id="bis_project_area" name="bis_project_area" value="<?php echo esc_attr($legacy_area); ?>" placeholder="45 000">
                </div>
                <div class="bis-field">
                    <label for="bis_project_year">Год</label>
                    <input type="text" id="bis_project_year" name="bis_project_year" value="<?php echo esc_attr($legacy_year); ?>" placeholder="2024">
                </div>
            </div>
        </div>

        <div class="bis-project-section">
            <div class="bis-project-section__header">
                <h4>Текст на баннере</h4>
                <p class="bis-field__hint">Заполните подписи и значения для четырех блоков. Если подпись и значение пустые — блок не отображается.</p>
            </div>

            <div class="bis-project-grid">
                <div class="bis-field">
                    <label for="bis_project_banner_title">Заголовок баннера</label>
                    <input type="text" id="bis_project_banner_title" name="bis_project_banner_title" value="<?php echo esc_attr($banner_title); ?>" placeholder="<?php echo esc_attr(get_the_title($post->ID)); ?>">
                    <p class="bis-field__hint">Если оставить пустым, будет использовано название записи.</p>
                </div>
                <div class="bis-field">
                    <label for="bis_project_description">Описание проекта</label>
                    <textarea id="bis_project_description" name="bis_project_description" rows="4" placeholder="Краткое описание проекта для карточек и страницы"><?php echo esc_textarea($project_description); ?></textarea>
                    <p class="bis-field__hint">Краткий текст для карточек и страницы проекта. Если оставить пустым, блоки описания не отображаются.</p>
                </div>
            </div>

            <div class="bis-project-banner-blocks">
                <?php
                $positions = array(
                    'top_left' => 'Левый верхний блок',
                    'bottom_left' => 'Левый нижний блок',
                    'top_right' => 'Правый верхний блок',
                    'bottom_right' => 'Правый нижний блок',
                );
                foreach ($positions as $key => $label) :
                    $block = isset($banner_blocks[$key]) ? $banner_blocks[$key] : array('label' => '', 'value' => '');
                    $block_label = isset($block['label']) ? $block['label'] : '';
                    $block_value = isset($block['value']) ? $block['value'] : '';
                    ?>
                    <div class="bis-banner-block">
                        <h4><?php echo esc_html($label); ?></h4>
                        <div class="bis-field">
                            <label>Подпись</label>
                            <input type="text" name="bis_project_banner_blocks[<?php echo esc_attr($key); ?>][label]" value="<?php echo esc_attr($block_label); ?>" placeholder="Например: Год реализации">
                        </div>
                        <div class="bis-field">
                            <label>Значение</label>
                            <textarea rows="2" name="bis_project_banner_blocks[<?php echo esc_attr($key); ?>][value]" placeholder="Например: 2024"><?php echo esc_textarea($block_value); ?></textarea>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bis-project-section">
            <div class="bis-project-section__header">
                <h4>Галерея проекта</h4>
                <p class="bis-field__hint">Фото для слайдера на странице проекта. Можно менять порядок перетаскиванием.</p>
            </div>
            <div class="bis-project-gallery-admin">
                <ul id="bis-project-gallery-list" class="bis-project-gallery-list">
                    <?php foreach ($gallery as $image) : ?>
                        <li class="bis-project-gallery-item">
                            <div class="bis-project-gallery-thumb" style="background-image: url('<?php echo esc_url($image); ?>');"></div>
                            <input type="hidden" name="bis_project_gallery[]" value="<?php echo esc_url($image); ?>">
                            <button type="button" class="button-link-delete bis-project-gallery-remove">Удалить</button>
                            <span class="dashicons dashicons-move handle" aria-hidden="true"></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="bis-project-gallery-actions">
                    <button type="button" class="button" id="bis-project-gallery-add">Добавить фото</button>
                    <div class="bis-project-gallery-url">
                        <input type="text" id="bis-project-gallery-url" placeholder="https://">
                        <button type="button" class="button" id="bis-project-gallery-add-url">Добавить по ссылке</button>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/template" id="bis-project-gallery-item-template">
            <li class="bis-project-gallery-item">
                <div class="bis-project-gallery-thumb"></div>
                <input type="hidden" value="">
                <button type="button" class="button-link-delete bis-project-gallery-remove">Удалить</button>
                <span class="dashicons dashicons-move handle" aria-hidden="true"></span>
            </li>
        </script>

        <div class="bis-project-toggle">
            <label class="bis-switch">
                <input type="checkbox" name="bis_project_is_featured" value="1" <?php checked($is_key, '1'); ?> data-featured-toggle>
                <span class="bis-switch__slider"></span>
                <span class="bis-switch__label">Показать в блоке <Ключевые проекты></span>
            </label>
            <p class="bis-field__hint">Ключевые проекты отображаются на главной в верхнем списке, остальные - в блоке <Все проекты>.</p>
        </div>
    </div>
    <?php
}

function bis_save_project_details($post_id) {
    if (!isset($_POST['bis_project_details_nonce_field']) || !wp_verify_nonce($_POST['bis_project_details_nonce_field'], 'bis_project_details_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!isset($_POST['post_type']) || 'bis_project' !== $_POST['post_type'] || !current_user_can('edit_post', $post_id)) {
        return;
    }

    $preview_image = isset($_POST['bis_project_preview_image']) ? esc_url_raw(wp_unslash($_POST['bis_project_preview_image'])) : '';
    $banner_image = isset($_POST['bis_project_banner_image']) ? esc_url_raw(wp_unslash($_POST['bis_project_banner_image'])) : '';
    $banner_title = isset($_POST['bis_project_banner_title']) ? sanitize_text_field(wp_unslash($_POST['bis_project_banner_title'])) : '';
    $project_description = isset($_POST['bis_project_description']) ? sanitize_textarea_field(wp_unslash($_POST['bis_project_description'])) : '';
    $is_key = isset($_POST['bis_project_is_featured']) ? '1' : '0';

    $positions = array('top_left', 'bottom_left', 'top_right', 'bottom_right');
    $banner_blocks = array();
    if (isset($_POST['bis_project_banner_blocks']) && is_array($_POST['bis_project_banner_blocks'])) {
        $raw_blocks = $_POST['bis_project_banner_blocks'];
    } else {
        $raw_blocks = array();
    }

    foreach ($positions as $key) {
        $block = isset($raw_blocks[$key]) && is_array($raw_blocks[$key]) ? $raw_blocks[$key] : array();
        $label = isset($block['label']) ? sanitize_text_field(wp_unslash($block['label'])) : '';
        $value = isset($block['value']) ? sanitize_textarea_field(wp_unslash($block['value'])) : '';
        $banner_blocks[$key] = array(
            'label' => $label,
            'value' => $value,
        );
    }

    $gallery = array();
    if (isset($_POST['bis_project_gallery']) && is_array($_POST['bis_project_gallery'])) {
        foreach ($_POST['bis_project_gallery'] as $image) {
            $url = esc_url_raw(wp_unslash($image));
            if ($url) {
                $gallery[] = $url;
            }
        }
    }

    $year_input = isset($_POST['bis_project_year']) ? sanitize_text_field(wp_unslash($_POST['bis_project_year'])) : '';
    $area_input = isset($_POST['bis_project_area']) ? sanitize_text_field(wp_unslash($_POST['bis_project_area'])) : '';
    $address_input = isset($_POST['bis_project_address']) ? sanitize_text_field(wp_unslash($_POST['bis_project_address'])) : '';

    $legacy_year = isset($banner_blocks['top_left']['value']) ? $banner_blocks['top_left']['value'] : '';
    $legacy_address = isset($banner_blocks['bottom_left']['value']) ? $banner_blocks['bottom_left']['value'] : '';
    $legacy_area_raw = isset($banner_blocks['top_right']['value']) ? $banner_blocks['top_right']['value'] : '';
    $legacy_area = trim(preg_replace('/\\s*(м2|м²|m2|m²)\\s*/iu', '', $legacy_area_raw));

    $area_input = trim(preg_replace('/\\s*(м2|м²|m2|m²)\\s*/iu', '', $area_input));
    $project_year = $year_input !== '' ? $year_input : $legacy_year;
    $project_address = $address_input !== '' ? $address_input : $legacy_address;
    $project_area = $area_input !== '' ? $area_input : $legacy_area;

    update_post_meta($post_id, 'bis_project_address', $project_address);
    update_post_meta($post_id, 'bis_project_area', $project_area);
    update_post_meta($post_id, 'bis_project_year', $project_year);
    update_post_meta($post_id, 'bis_project_preview_image', $preview_image);
    update_post_meta($post_id, 'bis_project_image', $preview_image);
    update_post_meta($post_id, 'bis_project_banner_image', $banner_image);
    update_post_meta($post_id, 'bis_project_banner_title', $banner_title);
    update_post_meta($post_id, 'bis_project_banner_blocks', $banner_blocks);
    update_post_meta($post_id, 'bis_project_gallery', $gallery);
    update_post_meta($post_id, 'bis_project_is_featured', $is_key);
    update_post_meta($post_id, 'bis_project_description', $project_description);
}
add_action('save_post', 'bis_save_project_details');

function bis_save_page_banner($post_id) {
    if (!isset($_POST['bis_page_banner_nonce_field']) || !wp_verify_nonce($_POST['bis_page_banner_nonce_field'], 'bis_page_banner_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!isset($_POST['post_type']) || 'page' !== $_POST['post_type'] || !current_user_can('edit_post', $post_id)) {
        return;
    }

    $banner_title = isset($_POST['bis_page_banner_title']) ? sanitize_text_field(wp_unslash($_POST['bis_page_banner_title'])) : '';
    $banner_subtitle = isset($_POST['bis_page_banner_subtitle']) ? sanitize_textarea_field(wp_unslash($_POST['bis_page_banner_subtitle'])) : '';
    $banner_image = isset($_POST['bis_page_banner_image']) ? esc_url_raw(wp_unslash($_POST['bis_page_banner_image'])) : '';

    update_post_meta($post_id, 'bis_page_banner_title', $banner_title);
    update_post_meta($post_id, 'bis_page_banner_subtitle', $banner_subtitle);
    update_post_meta($post_id, 'bis_page_banner_image', $banner_image);
}
add_action('save_post', 'bis_save_page_banner');

function bis_save_service_details($post_id) {
    if (!isset($_POST['bis_service_details_nonce_field']) || !wp_verify_nonce($_POST['bis_service_details_nonce_field'], 'bis_service_details_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!isset($_POST['post_type']) || 'bis_service' !== $_POST['post_type'] || !current_user_can('edit_post', $post_id)) {
        return;
    }

    $image = isset($_POST['bis_service_image']) ? esc_url_raw(wp_unslash($_POST['bis_service_image'])) : '';
    $description = isset($_POST['bis_service_description']) ? sanitize_textarea_field(wp_unslash($_POST['bis_service_description'])) : '';

    update_post_meta($post_id, 'bis_service_image', $image);
    update_post_meta($post_id, 'bis_service_description', $description);
}
add_action('save_post', 'bis_save_service_details');

function bis_save_equipment_details($post_id) {
    if (!isset($_POST['bis_equipment_details_nonce_field']) || !wp_verify_nonce($_POST['bis_equipment_details_nonce_field'], 'bis_equipment_details_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!isset($_POST['post_type']) || 'bis_equipment' !== $_POST['post_type'] || !current_user_can('edit_post', $post_id)) {
        return;
    }

    $image = isset($_POST['bis_equipment_image']) ? esc_url_raw(wp_unslash($_POST['bis_equipment_image'])) : '';
    $description = isset($_POST['bis_equipment_description']) ? sanitize_textarea_field(wp_unslash($_POST['bis_equipment_description'])) : '';

    update_post_meta($post_id, 'bis_equipment_image', $image);
    update_post_meta($post_id, 'bis_equipment_description', $description);
}
add_action('save_post', 'bis_save_equipment_details');

function bis_save_gratitude_image($post_id) {
    if (!isset($_POST['bis_gratitude_image_nonce_field']) || !wp_verify_nonce($_POST['bis_gratitude_image_nonce_field'], 'bis_gratitude_image_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!isset($_POST['post_type']) || 'bis_gratitude' !== $_POST['post_type'] || !current_user_can('edit_post', $post_id)) {
        return;
    }

    $gratitude_image = isset($_POST['bis_gratitude_image']) ? esc_url_raw(wp_unslash($_POST['bis_gratitude_image'])) : '';
    update_post_meta($post_id, 'bis_gratitude_image', $gratitude_image);
}
add_action('save_post', 'bis_save_gratitude_image');

function bis_save_news_image($post_id) {
    if (!isset($_POST['bis_news_image_nonce_field']) || !wp_verify_nonce($_POST['bis_news_image_nonce_field'], 'bis_news_image_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if ('bis_news' !== get_post_type($post_id) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    $news_image = isset($_POST['bis_news_image']) ? esc_url_raw(wp_unslash($_POST['bis_news_image'])) : '';
    update_post_meta($post_id, 'bis_news_image', $news_image);
}
add_action('save_post', 'bis_save_news_image');

/**
 * Custom columns for projects.
 */
function bis_project_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $label) {
        if ('title' === $key) {
            $new_columns['bis_project_address'] = 'Адрес';
            $new_columns['bis_project_year'] = 'Год';
            $new_columns['bis_project_featured'] = 'Ключевой';
        }
        $new_columns[$key] = $label;
    }
    return $new_columns;
}
add_filter('manage_bis_project_posts_columns', 'bis_project_columns');

function bis_project_custom_column($column, $post_id) {
    if ('bis_project_address' === $column) {
        echo esc_html(get_post_meta($post_id, 'bis_project_address', true));
    }

    if ('bis_project_year' === $column) {
        echo esc_html(get_post_meta($post_id, 'bis_project_year', true));
    }

    if ('bis_project_featured' === $column) {
        $is_key = get_post_meta($post_id, 'bis_project_is_featured', true);
        echo $is_key ? '✓' : '—';
    }
}
add_action('manage_bis_project_posts_custom_column', 'bis_project_custom_column', 10, 2);

/**
 * Seed initial projects from current layout once.
 */
function bis_seed_projects_from_layout() {
    if (get_option('bis_projects_seeded')) {
        return;
    }

    $base = get_template_directory_uri() . '/assets/img/';
    $projects = array(
        array(
            'title'    => 'Футбольный стадион «ЦСКА» (Москва)',
            'address'  => 'Москва',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k1.jpg',
            'featured' => true,
        ),
        array(
            'title'    => 'Штаб-квартира «Ростелеком»',
            'address'  => 'Москва',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k2.jpg',
            'featured' => true,
        ),
        array(
            'title'    => 'Офисы Яндекс (Москва)',
            'address'  => 'БЦ «Аврора», Москва',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k1.png',
            'featured' => true,
        ),
        array(
            'title'    => 'Яндекс — БЦ «Аврора», Москва',
            'address'  => 'Москва',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k2.png',
            'featured' => false,
        ),
        array(
            'title'    => 'Яндекс — БЦ «Красная Роза», Москва',
            'address'  => 'Москва',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k1.png',
            'featured' => false,
        ),
        array(
            'title'    => 'Центральный офис Россельхозбанка (Москва-Сити)',
            'address'  => 'Москва-Сити',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k7.webp',
            'featured' => false,
        ),
        array(
            'title'    => 'Штаб-квартира ПАО «Сбербанк»',
            'address'  => 'Кутузовский проспект, Москва',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k3.png',
            'featured' => false,
        ),
        array(
            'title'    => 'Офис Avon (БЦ «Большевик», Москва)',
            'address'  => 'Москва, БЦ «Большевик»',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k4.png',
            'featured' => false,
        ),
        array(
            'title'    => 'Центральный офис Металлинвестбанка',
            'address'  => 'Москва',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k8.png',
            'featured' => false,
        ),
        array(
            'title'    => 'ЗАО «Канонфарма Продакшн» (Щёлково)',
            'address'  => 'Щёлково',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k4.webp',
            'featured' => false,
        ),
        array(
            'title'    => 'АО «Валента Фарм» (Щёлково)',
            'address'  => 'Щёлково',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k11.webp',
            'featured' => false,
        ),
        array(
            'title'    => 'НПК «Генериум» (Владимирская область)',
            'address'  => 'Владимирская область',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k5.webp',
            'featured' => false,
        ),
        array(
            'title'    => 'АО «Красногорсклекарства»',
            'address'  => 'Красногорск',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k11.png',
            'featured' => false,
        ),
        array(
            'title'    => 'ООО «ССТинвест»',
            'address'  => 'Москва',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k13.png',
            'featured' => false,
        ),
        array(
            'title'    => 'Клинический госпиталь «Мать и Дитя» (Тюмень)',
            'address'  => 'Тюмень',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k6.webp',
            'featured' => false,
        ),
        array(
            'title'    => 'Клинический госпиталь «Мать и Дитя» (Самара)',
            'address'  => 'Самара',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k13.webp',
            'featured' => false,
        ),
        array(
            'title'    => 'Мать и Дитя (Рязань)',
            'address'  => 'Рязань',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k12.png',
            'featured' => false,
        ),
        array(
            'title'    => 'Онкоцентр, Балашиха',
            'address'  => 'Балашиха',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k9.webp',
            'featured' => false,
        ),
        array(
            'title'    => 'Онкоцентр, Подольск',
            'address'  => 'Подольск',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k10.webp',
            'featured' => false,
        ),
        array(
            'title'    => 'Онкоцентр, Кострома',
            'address'  => 'Кострома',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k12.webp',
            'featured' => false,
        ),
        array(
            'title'    => 'Медицинский центр «МЕДСИ» (Москва)',
            'address'  => 'Москва',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k6.png',
            'featured' => false,
        ),
        array(
            'title'    => 'Пассажирский терминал «Домодедово-2»',
            'address'  => 'Домодедово',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k8.webp',
            'featured' => false,
        ),
        array(
            'title'    => 'СКА Арена (Санкт-Петербург)',
            'address'  => 'Санкт-Петербург',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k14.webp',
            'featured' => false,
        ),
        array(
            'title'    => 'Футбольный стадион «Открытие Арена» (Москва)',
            'address'  => 'Москва',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k18.jpg',
            'featured' => false,
        ),
        array(
            'title'    => 'ЖК «Триколор» (Москва)',
            'address'  => 'Москва',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k15.jpg',
            'featured' => false,
        ),
        array(
            'title'    => 'ЖК «LIFE Ботанический сад» (Москва)',
            'address'  => 'Москва',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k23.png',
            'featured' => false,
        ),
        array(
            'title'    => 'ЖК «Маяк» (Химки)',
            'address'  => 'Химки',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k24.jpg',
            'featured' => false,
        ),
        array(
            'title'    => 'ПИК — Белая Дача парк, Одинцово-1',
            'address'  => 'Московская область',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k5.png',
            'featured' => false,
        ),
        array(
            'title'    => 'ТРЦ «Эльград»',
            'address'  => 'Москва',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k9.png',
            'featured' => false,
        ),
        array(
            'title'    => 'ТРЦ «Мега»',
            'address'  => 'Москва',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k10.png',
            'featured' => false,
        ),
        array(
            'title'    => 'ПАО «Гостиничный комплекс «Космос»',
            'address'  => 'Москва',
            'area'     => '',
            'year'     => '',
            'image'    => $base . 'k14.png',
            'featured' => false,
        ),
    );

    foreach ($projects as $project) {
        $existing = get_page_by_title($project['title'], OBJECT, 'bis_project');
        $post_id = $existing ? $existing->ID : 0;

        if (!$post_id) {
            $post_id = wp_insert_post(array(
                'post_title'  => $project['title'],
                'post_status' => 'publish',
                'post_type'   => 'bis_project',
            ));
        }

        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, 'bis_project_address', $project['address']);
            update_post_meta($post_id, 'bis_project_area', $project['area']);
            update_post_meta($post_id, 'bis_project_year', $project['year']);
            update_post_meta($post_id, 'bis_project_preview_image', $project['image']);
            update_post_meta($post_id, 'bis_project_image', $project['image']);
            update_post_meta($post_id, 'bis_project_banner_image', $project['image']);
            update_post_meta($post_id, 'bis_project_is_featured', $project['featured'] ? '1' : '0');
        }
    }

    update_option('bis_projects_seeded', 1);
}
add_action('init', 'bis_seed_projects_from_layout', 20);

/**
 * Helpers for projects.
 */

function bis_register_gratitude_cpt() {
    $labels = array(
        'name'                  => 'Благодарности',
        'singular_name'         => 'Благодарность',
        'menu_name'             => 'Благодарности',
        'name_admin_bar'        => 'Благодарность',
        'add_new'               => 'Добавить',
        'add_new_item'          => 'Добавить благодарность',
        'edit_item'             => 'Редактировать',
        'new_item'              => 'Новая благодарность',
        'view_item'             => 'Просмотр',
        'search_items'          => 'Искать благодарности',
        'not_found'             => 'Не найдено',
        'not_found_in_trash'    => 'В корзине пусто',
        'all_items'             => 'Все благодарности',
    );

    register_post_type('bis_gratitude', array(
        'labels'             => $labels,
        'public'             => true,
        'show_in_menu'       => true,
        'has_archive'        => false,
        'menu_icon'          => 'dashicons-format-image',
        'supports'           => array('title', 'thumbnail', 'page-attributes'),
        'hierarchical'       => true,
        'rewrite'            => array('slug' => 'gratitude'),
        'show_in_rest'       => true,
    ));
}
add_action('init', 'bis_register_gratitude_cpt');

/**
 * Sort gratitude posts by manual order in admin by default.
 */
function bis_gratitude_admin_order($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ('bis_gratitude' === $query->get('post_type') && !$query->get('orderby')) {
        $query->set('orderby', 'menu_order');
        $query->set('order', 'ASC');
    }
}
add_action('pre_get_posts', 'bis_gratitude_admin_order');

/**
 * Adds thumbnail and order columns for gratitude admin list.
 */
function bis_gratitude_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $label) {
        if ('title' === $key) {
            $new_columns['thumbnail'] = 'Изображение';
        }
        $new_columns[$key] = $label;
    }
    $new_columns['menu_order'] = 'Порядок';

    return $new_columns;
}
add_filter('manage_bis_gratitude_posts_columns', 'bis_gratitude_columns');

function bis_gratitude_custom_column($column, $post_id) {
    if ('thumbnail' === $column) {
        $thumb = get_the_post_thumbnail($post_id, array(80, 80));
        echo $thumb ? $thumb : '—';
    }

    if ('menu_order' === $column) {
        echo intval(get_post_field('menu_order', $post_id));
    }
}
add_action('manage_bis_gratitude_posts_custom_column', 'bis_gratitude_custom_column', 10, 2);


