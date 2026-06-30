<?php

remove_filter('use_block_editor_for_post_type', 'bis_disable_project_block_editor', 10);
remove_action('save_post', 'bis_save_news_image');

function bis_override_post_type_args($args, $post_type) {
    if ('bis_news' === $post_type) {
        $args['labels'] = array(
            'name'                  => 'Медиа',
            'singular_name'         => 'Запись медиа',
            'add_new'               => 'Добавить запись',
            'add_new_item'          => 'Добавить новую запись',
            'edit_item'             => 'Редактировать запись',
            'new_item'              => 'Новая запись',
            'view_item'             => 'Просмотр записи',
            'search_items'          => 'Поиск записей',
            'not_found'             => 'Записи не найдены',
            'not_found_in_trash'    => 'В корзине нет записей',
            'all_items'             => 'Все записи',
            'archives'              => 'Медиа компании',
            'attributes'            => 'Атрибуты записи',
            'insert_into_item'      => 'Вставить в запись',
            'uploaded_to_this_item' => 'Загружено для этой записи',
            'menu_name'             => 'Медиа',
            'filter_items_list'     => 'Фильтровать записи',
            'items_list_navigation' => 'Навигация по записям',
            'items_list'            => 'Список записей',
            'name_admin_bar'        => 'Запись медиа',
        );
    }

    if ('bis_project' === $post_type) {
        $args['hierarchical'] = true;
        $args['supports'] = array('title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes');
    }

    if ('bis_service' === $post_type) {
        $args['hierarchical'] = true;
        $args['rewrite'] = isset($args['rewrite']) && is_array($args['rewrite']) ? $args['rewrite'] : array('slug' => 'services', 'with_front' => false);
        $args['rewrite']['hierarchical'] = true;
        $args['supports'] = array('title', 'editor', 'thumbnail', 'excerpt', 'page-attributes');
    }

    return $args;
}
add_filter('register_post_type_args', 'bis_override_post_type_args', 20, 2);

function bis_register_override_meta() {
    $post_auth = function () {
        return current_user_can('edit_posts');
    };

    $page_auth = function () {
        return current_user_can('edit_pages');
    };

    register_post_meta('bis_news', 'bis_news_image', array(
        'single'            => true,
        'type'              => 'string',
        'show_in_rest'      => true,
        'sanitize_callback' => 'esc_url_raw',
        'auth_callback'     => $post_auth,
    ));

    register_post_meta('bis_news', 'bis_news_image_id', array(
        'single'            => true,
        'type'              => 'integer',
        'show_in_rest'      => true,
        'sanitize_callback' => 'absint',
        'auth_callback'     => $post_auth,
    ));

    register_post_meta('bis_news', 'bis_news_banner_image', array(
        'single'            => true,
        'type'              => 'string',
        'show_in_rest'      => true,
        'sanitize_callback' => 'esc_url_raw',
        'auth_callback'     => $post_auth,
    ));

    register_post_meta('bis_service', 'bis_service_image', array(
        'single'            => true,
        'type'              => 'string',
        'show_in_rest'      => true,
        'sanitize_callback' => 'esc_url_raw',
        'auth_callback'     => $post_auth,
    ));

    register_post_meta('bis_service', 'bis_service_preview_image', array(
        'single'            => true,
        'type'              => 'string',
        'show_in_rest'      => true,
        'sanitize_callback' => 'esc_url_raw',
        'auth_callback'     => $post_auth,
    ));

    register_post_meta('bis_service', 'bis_service_banner_image', array(
        'single'            => true,
        'type'              => 'string',
        'show_in_rest'      => true,
        'sanitize_callback' => 'esc_url_raw',
        'auth_callback'     => $post_auth,
    ));

    register_post_meta('bis_service', 'bis_service_description', array(
        'single'            => true,
        'type'              => 'string',
        'show_in_rest'      => true,
        'sanitize_callback' => 'sanitize_textarea_field',
        'auth_callback'     => $post_auth,
    ));

    register_post_meta('bis_service', 'bis_service_show_in_catalog', array(
        'single'            => true,
        'type'              => 'string',
        'show_in_rest'      => true,
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => $post_auth,
    ));

    register_post_meta('page', 'bis_page_banner_title', array(
        'single'            => true,
        'type'              => 'string',
        'show_in_rest'      => true,
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => $page_auth,
    ));

    register_post_meta('page', 'bis_page_banner_subtitle', array(
        'single'            => true,
        'type'              => 'string',
        'show_in_rest'      => true,
        'sanitize_callback' => 'sanitize_textarea_field',
        'auth_callback'     => $page_auth,
    ));

    register_post_meta('page', 'bis_page_banner_image', array(
        'single'            => true,
        'type'              => 'string',
        'show_in_rest'      => true,
        'sanitize_callback' => 'esc_url_raw',
        'auth_callback'     => $page_auth,
    ));
}
add_action('init', 'bis_register_override_meta');

function bis_replace_custom_meta_boxes() {
    remove_meta_box('bis_page_banner', 'page', 'normal');
    add_meta_box(
        'bis_page_banner',
        'Баннер страницы',
        'bis_render_page_banner_metabox_override',
        'page',
        'normal',
        'high'
    );

    remove_meta_box('bis_service_details', 'bis_service', 'normal');
    add_meta_box(
        'bis_service_details',
        'Карточка услуги',
        'bis_render_service_metabox_override',
        'bis_service',
        'normal',
        'high'
    );

    add_meta_box(
        'bis_service_children',
        'Дочерние услуги',
        'bis_render_service_children_metabox',
        'bis_service',
        'normal',
        'default'
    );

    add_meta_box(
        'bis_news_images',
        'Изображения записи',
        'bis_render_news_images_metabox',
        'bis_news',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'bis_replace_custom_meta_boxes', 20);

function bis_render_page_banner_metabox_override($post) {
    wp_nonce_field('bis_page_banner_override_nonce', 'bis_page_banner_override_nonce_field');

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
                <p>Задайте текст и изображение для баннера страницы. Если поле изображения пустое, используется «Изображение записи».</p>
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
                <input type="text" id="bis_page_banner_image" name="bis_page_banner_image" value="<?php echo esc_url($banner_image); ?>" placeholder="https://" data-image-input data-preview-target="bis_page_banner_image" data-meta-field="bis_page_banner_image">
                <div class="bis-project-media__buttons">
                    <button type="button" class="button button-primary bis-project-image-upload" data-target="bis_page_banner_image">Выбрать в медиабиблиотеке</button>
                    <button type="button" class="button bis-project-image-clear" data-target="bis_page_banner_image">Убрать фото</button>
                </div>
            </div>
        </div>

        <div class="bis-project-grid">
            <div class="bis-field">
                <label for="bis_page_banner_title">Заголовок баннера</label>
                <input type="text" id="bis_page_banner_title" name="bis_page_banner_title" value="<?php echo esc_attr($banner_title); ?>" placeholder="<?php echo esc_attr(get_the_title($post->ID)); ?>" data-meta-field="bis_page_banner_title">
            </div>
            <div class="bis-field">
                <label for="bis_page_banner_subtitle">Подзаголовок</label>
                <textarea id="bis_page_banner_subtitle" name="bis_page_banner_subtitle" rows="3" placeholder="Введите подзаголовок" data-meta-field="bis_page_banner_subtitle"><?php echo esc_textarea($banner_subtitle); ?></textarea>
            </div>
        </div>
    </div>
    <?php
}

function bis_render_service_metabox_override($post) {
    wp_nonce_field('bis_service_override_nonce', 'bis_service_override_nonce_field');

    $description = get_post_meta($post->ID, 'bis_service_description', true);
    $preview_image = get_post_meta($post->ID, 'bis_service_preview_image', true);
    $banner_image = get_post_meta($post->ID, 'bis_service_banner_image', true);
    $show_in_catalog = get_post_meta($post->ID, 'bis_service_show_in_catalog', true);
    $legacy_image = get_post_meta($post->ID, 'bis_service_image', true);
    $thumbnail = get_the_post_thumbnail_url($post->ID, 'full');
    $preview = $preview_image ? $preview_image : ($legacy_image ? $legacy_image : $thumbnail);
    $banner_preview = $banner_image ? $banner_image : ($preview ? $preview : $thumbnail);
    $is_visible = $show_in_catalog === '' ? ((int) $post->post_parent === 0) : $show_in_catalog === '1';
    ?>
    <div class="bis-project-box">
        <div class="bis-project-box__header">
            <div>
                <h3>Карточка услуги</h3>
                <p>Задайте отдельные изображения для превью карточки и баннера страницы услуги.</p>
            </div>
        </div>

        <div class="bis-project-media">
            <div class="bis-project-media__preview <?php echo $preview ? '' : 'is-empty'; ?>" data-image-preview="bis_service_image" style="background-image: url('<?php echo esc_url($preview); ?>');">
                <?php if (!$preview) : ?>
                    <span class="bis-project-media__placeholder">Нет изображения</span>
                <?php endif; ?>
            </div>
            <div class="bis-project-media__controls">
                <label for="bis_service_image">Изображение превью</label>
                <input type="text" id="bis_service_image" name="bis_service_image" value="<?php echo esc_url($preview); ?>" placeholder="https://" data-image-input data-preview-target="bis_service_image" data-meta-field="bis_service_image">
                <div class="bis-project-media__buttons">
                    <button type="button" class="button button-primary bis-project-image-upload" data-target="bis_service_image">Выбрать в медиабиблиотеке</button>
                    <button type="button" class="button bis-project-image-clear" data-target="bis_service_image">Убрать фото</button>
                </div>
            </div>
        </div>

        <div class="bis-project-media bis-project-media--banner">
            <div class="bis-project-media__preview <?php echo $banner_preview ? '' : 'is-empty'; ?>" data-image-preview="bis_service_banner_image" style="background-image: url('<?php echo esc_url($banner_preview); ?>');">
                <?php if (!$banner_preview) : ?>
                    <span class="bis-project-media__placeholder">Нет изображения</span>
                <?php endif; ?>
            </div>
            <div class="bis-project-media__controls">
                <label for="bis_service_banner_image">Главное изображение (баннер)</label>
                <input type="text" id="bis_service_banner_image" name="bis_service_banner_image" value="<?php echo esc_url($banner_image); ?>" placeholder="https://" data-image-input data-preview-target="bis_service_banner_image" data-meta-field="bis_service_banner_image">
                <div class="bis-project-media__buttons">
                    <button type="button" class="button button-primary bis-project-image-upload" data-target="bis_service_banner_image">Выбрать в медиабиблиотеке</button>
                    <button type="button" class="button bis-project-image-clear" data-target="bis_service_banner_image">Убрать фото</button>
                </div>
            </div>
        </div>

        <div class="bis-field">
            <label for="bis_service_description">Описание</label>
            <textarea id="bis_service_description" name="bis_service_description" rows="4" placeholder="Краткое описание услуги" data-meta-field="bis_service_description"><?php echo esc_textarea($description); ?></textarea>
        </div>

        <div class="bis-project-toggle">
            <label class="bis-switch">
                <input type="checkbox" name="bis_service_show_in_catalog" value="1" <?php checked($is_visible); ?> data-meta-field="bis_service_show_in_catalog">
                <span class="bis-switch__slider"></span>
                <span class="bis-switch__label">Показывать в общем списке услуг</span>
            </label>
            <p class="bis-field__hint">Подуслуги по умолчанию скрыты из общего каталога и слайдера, но остаются доступными как отдельные страницы и отображаются у родительской услуги.</p>
        </div>
    </div>
    <?php
}

function bis_service_has_child_services($service_id) {
    $service_id = (int) $service_id;
    if ($service_id <= 0) {
        return false;
    }

    $children = get_posts(array(
        'post_type'   => 'bis_service',
        'post_parent' => $service_id,
        'post_status' => array('publish', 'draft', 'pending', 'future', 'private'),
        'posts_per_page' => 1,
        'fields'      => 'ids',
    ));

    return !empty($children);
}

function bis_get_service_child_ids($service_id) {
    $service_id = (int) $service_id;
    if ($service_id <= 0) {
        return array();
    }

    $children = get_posts(array(
        'post_type'      => 'bis_service',
        'post_parent'    => $service_id,
        'post_status'    => array('publish', 'draft', 'pending', 'future', 'private'),
        'posts_per_page' => -1,
        'orderby'        => array('menu_order' => 'ASC', 'title' => 'ASC'),
        'fields'         => 'ids',
    ));

    return array_map('intval', array_values($children));
}

function bis_get_service_children_candidates($service_id) {
    $service_id = (int) $service_id;
    $services = get_posts(array(
        'post_type'      => 'bis_service',
        'post_status'    => array('publish', 'draft', 'pending', 'future', 'private'),
        'posts_per_page' => -1,
        'post__not_in'   => array($service_id),
        'orderby'        => array('menu_order' => 'ASC', 'title' => 'ASC'),
    ));

    return array_values(array_filter($services, function ($service) use ($service_id) {
        if (!($service instanceof WP_Post) || (int) $service->ID === $service_id) {
            return false;
        }

        return !bis_service_has_child_services($service->ID);
    }));
}

function bis_render_service_children_metabox($post) {
    wp_nonce_field('bis_service_children_nonce', 'bis_service_children_nonce_field');

    $child_ids = bis_get_service_child_ids($post->ID);
    $candidates = bis_get_service_children_candidates($post->ID);
    $parent_id = (int) $post->post_parent;
    ?>
    <div class="bis-project-box">
        <div class="bis-project-box__header">
            <div>
                <h3>Дочерние услуги</h3>
                <p>Выберите услуги, которые должны открываться отдельными страницами и отображаться как подпункты текущей услуги.</p>
            </div>
        </div>

        <?php if ($parent_id > 0) : ?>
            <p class="bis-field__hint">Эта услуга уже является дочерней для «<?php echo esc_html(get_the_title($parent_id)); ?>». Чтобы сделать её родительской, сначала уберите её из дочерних у текущего родителя.</p>
        <?php elseif (empty($candidates)) : ?>
            <p class="bis-field__hint">Нет доступных услуг для добавления. Текущая услуга и услуги, которые уже являются родительскими, в список не попадают.</p>
        <?php else : ?>
            <div class="bis-service-children-list">
                <?php foreach ($candidates as $candidate) : ?>
                    <?php
                    $candidate_id = (int) $candidate->ID;
                    $candidate_parent_id = (int) $candidate->post_parent;
                    $parent_label = $candidate_parent_id > 0 ? 'Сейчас дочерняя для: ' . get_the_title($candidate_parent_id) : 'Без родителя';
                    ?>
                    <label class="bis-service-child-option">
                        <input type="checkbox" name="bis_service_children[]" value="<?php echo esc_attr($candidate_id); ?>" <?php checked(in_array($candidate_id, $child_ids, true)); ?>>
                        <span>
                            <strong><?php echo esc_html(get_the_title($candidate_id)); ?></strong>
                            <em><?php echo esc_html($parent_label); ?></em>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
            <p class="bis-field__hint">При сохранении выбранные услуги станут дочерними для текущей и будут скрыты из общего списка услуг.</p>
        <?php endif; ?>
    </div>
    <?php
}

function bis_assign_service_child_ids($parent_id, $selected_ids) {
    static $is_updating = false;

    if ($is_updating) {
        return;
    }

    $parent_id = (int) $parent_id;
    if ($parent_id <= 0) {
        return;
    }

    $parent = get_post($parent_id);
    if (!($parent instanceof WP_Post) || 'bis_service' !== $parent->post_type || (int) $parent->post_parent > 0) {
        return;
    }

    $selected_ids = array_values(array_unique(array_filter(array_map('intval', (array) $selected_ids))));
    $valid_ids = array();

    foreach ($selected_ids as $candidate_id) {
        if ($candidate_id === $parent_id || bis_service_has_child_services($candidate_id)) {
            continue;
        }

        $candidate = get_post($candidate_id);
        if ($candidate instanceof WP_Post && 'bis_service' === $candidate->post_type) {
            $valid_ids[] = $candidate_id;
        }
    }

    $current_ids = bis_get_service_child_ids($parent_id);
    $to_unassign = array_diff($current_ids, $valid_ids);
    $to_assign = array_diff($valid_ids, $current_ids);

    $is_updating = true;

    foreach ($to_unassign as $child_id) {
        wp_update_post(array(
            'ID' => (int) $child_id,
            'post_parent' => 0,
        ));
    }

    foreach ($to_assign as $child_id) {
        wp_update_post(array(
            'ID' => (int) $child_id,
            'post_parent' => $parent_id,
        ));
        update_post_meta((int) $child_id, 'bis_service_show_in_catalog', '0');
    }

    $is_updating = false;
}

function bis_prevent_invalid_service_hierarchy($post_id) {
    static $is_fixing = false;

    if ($is_fixing || 'bis_service' !== get_post_type($post_id)) {
        return;
    }

    $post = get_post($post_id);
    if (!($post instanceof WP_Post) || (int) $post->post_parent <= 0) {
        return;
    }

    if ((int) $post->post_parent === (int) $post_id || bis_service_has_child_services($post_id)) {
        $is_fixing = true;
        wp_update_post(array(
            'ID' => (int) $post_id,
            'post_parent' => 0,
        ));
        $is_fixing = false;
    }
}
add_action('save_post_bis_service', 'bis_prevent_invalid_service_hierarchy', 25);

function bis_render_news_images_metabox($post) {
    wp_nonce_field('bis_news_override_nonce', 'bis_news_override_nonce_field');

    $news_image = get_post_meta($post->ID, 'bis_news_image', true);
    $news_image_id = (int) get_post_meta($post->ID, 'bis_news_image_id', true);
    $banner_image = get_post_meta($post->ID, 'bis_news_banner_image', true);
    $thumbnail = get_the_post_thumbnail_url($post->ID, 'full');
    $preview = $news_image ? $news_image : $thumbnail;
    $banner_preview = $banner_image ? $banner_image : ($preview ? $preview : $thumbnail);
    ?>
    <div class="bis-project-box">
        <div class="bis-project-box__header">
            <div>
                <h3>Изображения записи</h3>
                <p>Превью используется в списках медиа, баннер - в верхнем блоке страницы материала. Если баннер не заполнен, используется превью.</p>
            </div>
        </div>

        <div class="bis-project-media">
            <div class="bis-project-media__preview <?php echo $preview ? '' : 'is-empty'; ?>" data-image-preview="bis_news_image" style="background-image: url('<?php echo esc_url($preview); ?>');">
                <?php if (!$preview) : ?>
                    <span class="bis-project-media__placeholder">Нет изображения</span>
                <?php endif; ?>
            </div>
            <div class="bis-project-media__controls">
                <label for="bis_news_image">Изображение превью</label>
                <input type="text" id="bis_news_image" name="bis_news_image" value="<?php echo esc_url($news_image); ?>" placeholder="https://" data-image-input data-preview-target="bis_news_image" data-meta-field="bis_news_image" data-attachment-target="bis_news_image_id">
                <input type="hidden" id="bis_news_image_id" name="bis_news_image_id" value="<?php echo esc_attr($news_image_id); ?>" data-meta-field="bis_news_image_id">
                <div class="bis-project-media__buttons">
                    <button type="button" class="button button-primary bis-project-image-upload" data-target="bis_news_image" data-attachment-target="bis_news_image_id">Выбрать в медиабиблиотеке</button>
                    <button type="button" class="button bis-project-image-clear" data-target="bis_news_image">Убрать фото</button>
                </div>
            </div>
        </div>

        <div class="bis-project-media bis-project-media--banner">
            <div class="bis-project-media__preview <?php echo $banner_preview ? '' : 'is-empty'; ?>" data-image-preview="bis_news_banner_image" style="background-image: url('<?php echo esc_url($banner_preview); ?>');">
                <?php if (!$banner_preview) : ?>
                    <span class="bis-project-media__placeholder">Нет изображения</span>
                <?php endif; ?>
            </div>
            <div class="bis-project-media__controls">
                <label for="bis_news_banner_image">Изображение баннера</label>
                <input type="text" id="bis_news_banner_image" name="bis_news_banner_image" value="<?php echo esc_url($banner_image); ?>" placeholder="https://" data-image-input data-preview-target="bis_news_banner_image" data-meta-field="bis_news_banner_image">
                <div class="bis-project-media__buttons">
                    <button type="button" class="button button-primary bis-project-image-upload" data-target="bis_news_banner_image">Выбрать в медиабиблиотеке</button>
                    <button type="button" class="button bis-project-image-clear" data-target="bis_news_banner_image">Убрать фото</button>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function bis_save_page_banner_override($post_id) {
    if (!isset($_POST['bis_page_banner_override_nonce_field']) || !wp_verify_nonce($_POST['bis_page_banner_override_nonce_field'], 'bis_page_banner_override_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if ('page' !== get_post_type($post_id) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    update_post_meta($post_id, 'bis_page_banner_title', isset($_POST['bis_page_banner_title']) ? sanitize_text_field(wp_unslash($_POST['bis_page_banner_title'])) : '');
    update_post_meta($post_id, 'bis_page_banner_subtitle', isset($_POST['bis_page_banner_subtitle']) ? sanitize_textarea_field(wp_unslash($_POST['bis_page_banner_subtitle'])) : '');
    update_post_meta($post_id, 'bis_page_banner_image', isset($_POST['bis_page_banner_image']) ? esc_url_raw(wp_unslash($_POST['bis_page_banner_image'])) : '');
}
add_action('save_post', 'bis_save_page_banner_override', 20);

function bis_save_service_override($post_id) {
    if (!isset($_POST['bis_service_override_nonce_field']) || !wp_verify_nonce($_POST['bis_service_override_nonce_field'], 'bis_service_override_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if ('bis_service' !== get_post_type($post_id) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    $preview_image = isset($_POST['bis_service_image']) ? esc_url_raw(wp_unslash($_POST['bis_service_image'])) : '';
    $banner_image = isset($_POST['bis_service_banner_image']) ? esc_url_raw(wp_unslash($_POST['bis_service_banner_image'])) : '';
    $description = isset($_POST['bis_service_description']) ? sanitize_textarea_field(wp_unslash($_POST['bis_service_description'])) : '';
    $show_in_catalog = isset($_POST['bis_service_show_in_catalog']) ? '1' : '0';

    update_post_meta($post_id, 'bis_service_image', $preview_image);
    update_post_meta($post_id, 'bis_service_preview_image', $preview_image);
    update_post_meta($post_id, 'bis_service_banner_image', $banner_image);
    update_post_meta($post_id, 'bis_service_description', $description);
    update_post_meta($post_id, 'bis_service_show_in_catalog', $show_in_catalog);
}
add_action('save_post', 'bis_save_service_override', 20);

function bis_save_service_children($post_id) {
    if (!isset($_POST['bis_service_children_nonce_field']) || !wp_verify_nonce($_POST['bis_service_children_nonce_field'], 'bis_service_children_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if ('bis_service' !== get_post_type($post_id) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    $selected_ids = isset($_POST['bis_service_children']) && is_array($_POST['bis_service_children'])
        ? array_map('absint', wp_unslash($_POST['bis_service_children']))
        : array();

    bis_assign_service_child_ids($post_id, $selected_ids);
}
add_action('save_post', 'bis_save_service_children', 30);

function bis_resolve_or_import_attachment_id($image_url, $post_id = 0) {
    $image_url = trim((string) $image_url);
    $post_id = (int) $post_id;

    if ($image_url === '') {
        return 0;
    }

    // 1. Уже есть attachment в медиабиблиотеке
    $attachment_id = function_exists('bis_get_attachment_id_from_url')
        ? (int) bis_get_attachment_id_from_url($image_url)
        : (int) attachment_url_to_postid($image_url);

    if ($attachment_id > 0) {
        return $attachment_id;
    }

    // 2. Внешняя ссылка или URL не распознан — пробуем импортировать в медиабиблиотеку
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $attachment_id = media_sideload_image($image_url, $post_id, null, 'id');

    if (is_wp_error($attachment_id)) {
        return 0;
    }

    return (int) $attachment_id;
}

function bis_store_news_image_state($post_id, $news_image = '', $news_image_id = 0) {
    $post_id = (int) $post_id;
    $news_image = trim((string) $news_image);
    $news_image_id = (int) $news_image_id;

    if ($news_image === '' && $news_image_id === 0) {
        $news_image = bis_get_news_placeholder_image_url();
    }

    if ($news_image !== '') {
        update_post_meta($post_id, 'bis_news_image', $news_image);
    } else {
        delete_post_meta($post_id, 'bis_news_image');
    }

    if ($news_image_id > 0) {
        update_post_meta($post_id, 'bis_news_image_id', $news_image_id);
        set_post_thumbnail($post_id, $news_image_id);
        return;
    }

    delete_post_meta($post_id, 'bis_news_image_id');
    delete_post_thumbnail($post_id);
}

function bis_save_news_override($post_id) {
    if (!isset($_POST['bis_news_override_nonce_field']) || !wp_verify_nonce($_POST['bis_news_override_nonce_field'], 'bis_news_override_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if ('bis_news' !== get_post_type($post_id) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    $news_image = isset($_POST['bis_news_image']) ? esc_url_raw(wp_unslash($_POST['bis_news_image'])) : '';
    $news_image_id = isset($_POST['bis_news_image_id']) ? absint(wp_unslash($_POST['bis_news_image_id'])) : 0;
    $banner_image = isset($_POST['bis_news_banner_image']) ? esc_url_raw(wp_unslash($_POST['bis_news_banner_image'])) : '';

    // Полная очистка
    if ($news_image === '' && $news_image_id === 0) {
        bis_store_news_image_state($post_id);
        update_post_meta($post_id, 'bis_news_banner_image', $banner_image);
        return;
    }

    // Если ID есть, но URL пустой — восстанавливаем URL
    if ($news_image === '' && $news_image_id > 0) {
        $resolved_url = wp_get_attachment_url($news_image_id);
        if ($resolved_url) {
            $news_image = esc_url_raw($resolved_url);
        }
    }

    // Если URL есть, но ID нет — пробуем найти/импортировать attachment
    if ($news_image_id === 0 && $news_image !== '') {
        $news_image_id = bis_resolve_or_import_attachment_id($news_image, $post_id);
    }

    // Если после импорта получили attachment — нормализуем URL
    if ($news_image_id > 0) {
        $resolved_url = wp_get_attachment_url($news_image_id);
        if ($resolved_url) {
            $news_image = esc_url_raw($resolved_url);
        }
    }

    // Сохраняем fallback-мету
    bis_store_news_image_state($post_id, $news_image, $news_image_id);
    update_post_meta($post_id, 'bis_news_banner_image', $banner_image);
}
add_action('save_post', 'bis_save_news_override', 20);

function bis_sync_news_featured_image_state($post_id, $post) {
    if (!($post instanceof WP_Post) || 'bis_news' !== $post->post_type) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['bis_news_override_nonce_field']) && wp_verify_nonce($_POST['bis_news_override_nonce_field'], 'bis_news_override_nonce')) {
        return;
    }

    $thumbnail_id = (int) get_post_thumbnail_id($post_id);
    if ($thumbnail_id > 0) {
        $thumbnail_url = wp_get_attachment_url($thumbnail_id);
        bis_store_news_image_state($post_id, $thumbnail_url ? esc_url_raw($thumbnail_url) : '', $thumbnail_id);
        return;
    }

    bis_store_news_image_state($post_id);
}
add_action('save_post_bis_news', 'bis_sync_news_featured_image_state', 30, 2);
