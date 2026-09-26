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

    register_post_meta('bis_news', 'bis_news_description', array(
        'single'            => true,
        'type'              => 'string',
        'show_in_rest'      => true,
        'sanitize_callback' => 'sanitize_textarea_field',
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

    register_post_meta('bis_service', 'bis_service_faq_dirty', array(
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

function bis_initialize_service_catalog_visibility($post_id, $post, $update) {
    if ($update || !($post instanceof WP_Post) || 'bis_service' !== $post->post_type) {
        return;
    }

    if (!metadata_exists('post', $post_id, 'bis_service_show_in_catalog')) {
        update_post_meta($post_id, 'bis_service_show_in_catalog', '0');
    }
}
add_action('wp_insert_post', 'bis_initialize_service_catalog_visibility', 10, 3);

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
        'bis_service_faq',
        'FAQ (Вопросы и ответы)',
        'bis_render_service_faq_metabox',
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

    add_meta_box(
        'bis_news_description',
        'Краткое описание',
        'bis_render_news_description_metabox',
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

        <div class="bis-project-media bis-project-media--banner bis-project-media--column" style="max-width: 480px;">
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
    $is_visible = (int) $post->post_parent === 0
        && $post->post_status !== 'auto-draft'
        && ($show_in_catalog === '1' || $show_in_catalog === '');
    ?>
    <div class="bis-project-box">
        <div class="bis-project-box__header">
            <div>
                <h3>Карточка услуги</h3>
                <p>Задайте отдельные изображения для превью карточки и баннера страницы услуги.</p>
            </div>
        </div>

        <div class="bis-project-media-grid">
            <div class="bis-project-media bis-project-media--column">
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

            <div class="bis-project-media bis-project-media--banner bis-project-media--column">
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

    // Group candidates by category
    $candidates_by_cat = array();
    foreach ($candidates as $candidate) {
        $cid = (int) $candidate->ID;
        $terms = get_the_terms($cid, 'bis_service_category');
        $cat_slug = 'catalog';
        $cat_name = 'Рубрики каталога';

        if (!empty($terms) && !is_wp_error($terms)) {
            $cat_slug = $terms[0]->slug;
            $cat_name = $terms[0]->name;
        }

        if (!isset($candidates_by_cat[$cat_slug])) {
            $candidates_by_cat[$cat_slug] = array(
                'name'  => $cat_name,
                'items' => array(),
            );
        }

        $c_parent_id = (int) $candidate->post_parent;
        $parent_label = ($c_parent_id > 0 && $c_parent_id !== (int) $post->ID)
            ? 'Сейчас дочерняя для: ' . get_the_title($c_parent_id)
            : '';

        $is_selected = in_array($cid, $child_ids, true);

        $candidates_by_cat[$cat_slug]['items'][] = array(
            'id'           => $cid,
            'title'        => get_the_title($cid),
            'category'     => $cat_name,
            'parent_label' => $parent_label,
            'is_selected'  => $is_selected,
        );
    }
    ?>
    <div class="bis-project-box bis-children-box">
        <div class="bis-project-box__header">
            <div>
                <h3>Дочерние услуги</h3>
                <p>Выберите услуги, которые должны открываться отдельными страницами и отображаться как подпункты текущей услуги.</p>
            </div>
        </div>

        <?php if ($parent_id > 0) : ?>
            <div class="bis-field__hint" style="margin-bottom: 12px; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; padding: 8px 12px; border-radius: 6px;">
                Внимание: эта услуга сейчас сама является дочерней для: <strong><?php echo esc_html(get_the_title($parent_id)); ?></strong>.
            </div>
        <?php endif; ?>

        <?php if (empty($candidates)) : ?>
            <p class="bis-field__hint">Нет доступных услуг для добавления. Текущая услуга и услуги, которые уже являются родительскими, в список не попадают.</p>
        <?php else : ?>
            <div class="bis-search-select" id="bis-children-search-select">
                <label class="bis-search-select__label" for="bis-children-search-input">
                    <span class="bis-search-select__label-icon">&#9889;</span>
                    <span>Выбрать дочерние услуги:</span>
                </label>

                <div class="bis-search-select__control-wrap">
                    <div class="bis-search-select__control" id="bis-children-select-trigger">
                        <input type="text"
                               class="bis-search-select__input"
                               id="bis-children-search-input"
                               placeholder="-- Выберите услугу (или начните вводить для поиска) --"
                               autocomplete="off">
                        <span class="bis-search-select__arrow" aria-hidden="true">&#9660;</span>
                    </div>

                    <div class="bis-search-select__dropdown" id="bis-children-dropdown" style="display: none;">
                        <div class="bis-search-select__option bis-search-select__option--reset" data-action="reset">
                            <span class="bis-search-select__option-title">— Очистить список дочерних услуг —</span>
                            <span class="bis-search-select__badge bis-search-select__badge--danger">Отключено</span>
                        </div>

                        <div class="bis-search-select__options-list">
                            <?php foreach ($candidates_by_cat as $cat_slug => $cat_data) : ?>
                                <div class="bis-search-select__group" data-cat="<?php echo esc_attr($cat_slug); ?>">
                                    <div class="bis-search-select__group-header">
                                        <span class="bis-search-select__group-title">
                                            &#128193; <?php echo esc_html(mb_strtoupper($cat_data['name'], 'UTF-8')); ?>
                                        </span>
                                        <button type="button" class="bis-search-select__group-btn" data-action="toggle-cat" data-cat="<?php echo esc_attr($cat_slug); ?>" title="Выбрать все услуги раздела">Выбрать раздел</button>
                                    </div>
                                    <div class="bis-search-select__group-items">
                                        <?php foreach ($cat_data['items'] as $item) : ?>
                                            <div class="bis-search-select__option<?php echo $item['is_selected'] ? ' is-selected' : ''; ?>"
                                                 data-id="<?php echo esc_attr($item['id']); ?>"
                                                 data-title="<?php echo esc_attr($item['title']); ?>"
                                                 data-badge="<?php echo esc_attr($item['category']); ?>">
                                                <div class="bis-search-select__option-left">
                                                    <span class="bis-search-select__check"><?php echo $item['is_selected'] ? '&#10003;' : ''; ?></span>
                                                    <span class="bis-search-select__option-title"><?php echo esc_html($item['title']); ?></span>
                                                    <?php if ($item['parent_label']) : ?>
                                                        <span class="bis-search-select__option-sub"><?php echo esc_html($item['parent_label']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="bis-search-select__badge"><?php echo esc_html($item['category']); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="bis-search-select__no-results" style="display: none;">
                                Ничего не найдено
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Выбранные дочерние услуги -->
                <div class="bis-selected-tags-panel">
                    <div class="bis-selected-tags-header">
                        <strong>Выбранные дочерние услуги (<span id="bis-children-count"><?php echo count($child_ids); ?></span>):</strong>
                    </div>
                    <div class="bis-selected-tags-list" id="bis-children-selected-list">
                        <?php foreach ($child_ids as $cid) : ?>
                            <?php
                            $c_title = get_the_title($cid);
                            $c_terms = get_the_terms($cid, 'bis_service_category');
                            $c_badge = (!empty($c_terms) && !is_wp_error($c_terms)) ? $c_terms[0]->name : 'Рубрика каталога';
                            ?>
                            <div class="bis-child-tag" data-id="<?php echo esc_attr($cid); ?>">
                                <span class="dashicons dashicons-menu bis-child-tag__handle" title="Перетащите для изменения порядка"></span>
                                <span class="bis-child-tag__title"><?php echo esc_html($c_title); ?></span>
                                <span class="bis-child-tag__badge"><?php echo esc_html($c_badge); ?></span>
                                <button type="button" class="bis-child-tag__remove" title="Удалить" aria-label="Удалить">&times;</button>
                                <input type="hidden" name="bis_service_children[]" value="<?php echo esc_attr($cid); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="bis-selected-tags-empty" id="bis-children-empty" style="<?php echo empty($child_ids) ? '' : 'display: none;'; ?>">
                        Дочерние услуги не выбраны. Выберите услугу или раздел в выпадающем меню выше.
                    </div>
                </div>

                <p class="bis-field__hint">При сохранении выбранные услуги станут дочерними для текущей и будут скрыты из общего списка услуг.</p>
            </div>
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
    if (!($parent instanceof WP_Post) || 'bis_service' !== $parent->post_type) {
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
    }

    foreach ($valid_ids as $order_index => $child_id) {
        wp_update_post(array(
            'ID'         => (int) $child_id,
            'menu_order' => $order_index,
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
// add_action('save_post_bis_service', 'bis_prevent_invalid_service_hierarchy', 25);

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

        <div class="bis-project-media-grid">
            <div class="bis-project-media bis-project-media--column">
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

            <div class="bis-project-media bis-project-media--banner bis-project-media--column">
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
    </div>
    <?php
}

function bis_render_news_description_metabox($post) {
    wp_nonce_field('bis_news_description_nonce', 'bis_news_description_nonce_field');

    $description = get_post_meta($post->ID, 'bis_news_description', true);
    if ($description === '') {
        $description = $post->post_excerpt;
    }
    ?>
    <div class="bis-project-box">
        <div class="bis-field">
            <label for="bis_news_description">Описание</label>
            <textarea id="bis_news_description" name="bis_news_description" rows="4" placeholder="Краткое описание материала" data-meta-field="bis_news_description"><?php echo esc_textarea($description); ?></textarea>
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
    // wp_update_post() for a child fires save_post again with the parent's form in $_POST.
    if (!isset($_POST['post_ID']) || (int) $_POST['post_ID'] !== (int) $post_id) {
        return;
    }

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
    if (!isset($_POST['post_ID']) || (int) $_POST['post_ID'] !== (int) $post_id) {
        return;
    }

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

function bis_save_news_description($post_id) {
    if (!isset($_POST['bis_news_description_nonce_field']) || !wp_verify_nonce($_POST['bis_news_description_nonce_field'], 'bis_news_description_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if ('bis_news' !== get_post_type($post_id) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    $description = isset($_POST['bis_news_description']) ? sanitize_textarea_field(wp_unslash($_POST['bis_news_description'])) : '';
    update_post_meta($post_id, 'bis_news_description', $description);

    $post = get_post($post_id);
    if ($post instanceof WP_Post && $post->post_excerpt !== $description) {
        remove_action('save_post', 'bis_save_news_description', 25);
        wp_update_post(array(
            'ID'           => $post_id,
            'post_excerpt' => $description,
        ));
        add_action('save_post', 'bis_save_news_description', 25);
    }
}
add_action('save_post', 'bis_save_news_description', 25);

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

function bis_get_service_faq($post_id = 0) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $post_id = (int) $post_id;
    if ($post_id <= 0) {
        return array();
    }

    $raw = get_post_meta($post_id, 'bis_service_faq', true);
    if (!is_array($raw)) {
        return array();
    }

    $items = array();
    foreach ($raw as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $q = isset($entry['question']) ? trim((string) $entry['question']) : '';
        $a = isset($entry['answer']) ? trim((string) $entry['answer']) : '';
        if ($q !== '' && $a !== '') {
            $items[] = array(
                'question' => $q,
                'answer'   => $a,
            );
        }
    }
    return $items;
}

function bis_render_service_faq_metabox($post) {
    wp_nonce_field('bis_service_faq_nonce', 'bis_service_faq_nonce_field');
    $raw_faq = get_post_meta($post->ID, 'bis_service_faq', true);
    $faq_items = is_array($raw_faq) ? $raw_faq : array();
    ?>
    <div class="bis-project-box">
        <div class="bis-project-box__header">
            <div>
                <h3>FAQ (Вопросы и ответы)</h3>
                <p>Добавьте вопросы и ответы, которые будут отображаться на странице услуги после текста перед кнопкой расчета.</p>
            </div>
            <button type="button" class="button button-primary" id="bis-service-faq-add">
                <span class="dashicons dashicons-plus-alt2"></span> Добавить вопрос
            </button>
        </div>

        <div id="bis-service-faq-list" class="bis-faq-list">
            <?php if (!empty($faq_items)) : ?>
                <?php foreach ($faq_items as $index => $item) : ?>
                    <?php
                    $q = isset($item['question']) ? $item['question'] : '';
                    $a = isset($item['answer']) ? $item['answer'] : '';
                    ?>
                    <div class="bis-faq-item" data-faq-index="<?php echo esc_attr($index); ?>">
                        <div class="bis-faq-item__header">
                            <div class="bis-faq-item__title">
                                <span class="dashicons dashicons-menu bis-faq-item__handle" title="Перетащите для изменения порядка"></span>
                                <strong>Вопрос <span class="bis-faq-item__num"><?php echo (int) ($index + 1); ?></span></strong>
                            </div>
                            <button type="button" class="button button-link-delete bis-faq-item__remove" title="Удалить вопрос">Удалить</button>
                        </div>
                        <div class="bis-faq-item__body">
                            <div class="bis-field">
                                <label>Вопрос</label>
                                <input type="text" name="bis_service_faq[<?php echo esc_attr($index); ?>][question]" value="<?php echo esc_attr($q); ?>" placeholder="Например: Сколько времени занимает наладка?" data-faq-field="question">
                            </div>
                            <div class="bis-field">
                                <label>Ответ</label>
                                <textarea name="bis_service_faq[<?php echo esc_attr($index); ?>][answer]" rows="6" placeholder="Введите подробный ответ. Поддерживается разметка HTML (абзацы <p>, списки <ul>, ссылки <a>, таблицы <table>, жирный текст <strong> и т.д.)." data-faq-field="answer"><?php echo esc_textarea($a); ?></textarea>
                                <span class="description" style="font-size: 11.5px; color: #64748b; margin-top: 4px; display: block;">Поддерживается разметка HTML (абзацы, списки, ссылки, таблицы). Скрипты и опасные теги автоматически блокируются (защита от XSS).</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="bis-service-faq-empty" class="bis-faq-empty" style="<?php echo !empty($faq_items) ? 'display: none;' : ''; ?>">
            <p>Вопросы и ответы пока не добавлены. Нажмите кнопку «Добавить вопрос», чтобы создать первый пункт FAQ.</p>
        </div>

        <template id="bis-service-faq-template">
            <div class="bis-faq-item" data-faq-index="__INDEX__">
                <div class="bis-faq-item__header">
                    <div class="bis-faq-item__title">
                        <span class="dashicons dashicons-menu bis-faq-item__handle" title="Перетащите для изменения порядка"></span>
                        <strong>Вопрос <span class="bis-faq-item__num">__NUM__</span></strong>
                    </div>
                    <button type="button" class="button button-link-delete bis-faq-item__remove" title="Удалить вопрос">Удалить</button>
                </div>
                <div class="bis-faq-item__body">
                    <div class="bis-field">
                        <label>Вопрос</label>
                        <input type="text" name="bis_service_faq[__INDEX__][question]" value="" placeholder="Например: Сколько времени занимает наладка?" data-faq-field="question">
                    </div>
                    <div class="bis-field">
                        <label>Ответ</label>
                        <textarea name="bis_service_faq[__INDEX__][answer]" rows="6" placeholder="Введите подробный ответ. Поддерживается разметка HTML (абзацы <p>, списки <ul>, ссылки <a>, таблицы <table>, жирный текст <strong> и т.д.)." data-faq-field="answer"></textarea>
                        <span class="description" style="font-size: 11.5px; color: #64748b; margin-top: 4px; display: block;">Поддерживается разметка HTML (абзацы, списки, ссылки, таблицы). Скрипты и опасные теги автоматически блокируются (защита от XSS).</span>
                    </div>
                </div>
            </div>
        </template>
    </div>
    <?php
}

function bis_save_service_faq($post_id) {
    if (!isset($_POST['post_ID']) || (int) $_POST['post_ID'] !== (int) $post_id) {
        return;
    }

    if (!isset($_POST['bis_service_faq_nonce_field']) || !wp_verify_nonce($_POST['bis_service_faq_nonce_field'], 'bis_service_faq_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if ('bis_service' !== get_post_type($post_id) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    $raw_faq = isset($_POST['bis_service_faq']) && is_array($_POST['bis_service_faq']) ? $_POST['bis_service_faq'] : array();
    $cleaned_faq = array();

    foreach ($raw_faq as $item) {
        if (!is_array($item)) {
            continue;
        }
        $question = isset($item['question']) ? sanitize_text_field(wp_unslash($item['question'])) : '';
        // wp_kses_post allows safe rich HTML formatting (p, br, a, strong, em, ul, ol, li, table, blockquote, etc.)
        // while strictly stripping malicious scripts, iframes, and on* event handlers to prevent XSS.
        $answer   = isset($item['answer']) ? wp_kses_post(wp_unslash($item['answer'])) : '';

        if ($question !== '' || $answer !== '') {
            $cleaned_faq[] = array(
                'question' => $question,
                'answer'   => $answer,
            );
        }
    }

    if (!empty($cleaned_faq)) {
        update_post_meta($post_id, 'bis_service_faq', $cleaned_faq);
    } else {
        delete_post_meta($post_id, 'bis_service_faq');
    }
}
add_action('save_post', 'bis_save_service_faq', 25);
