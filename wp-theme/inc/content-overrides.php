<?php

remove_filter('use_block_editor_for_post_type', 'bis_disable_project_block_editor', 10);

function bis_override_post_type_args($args, $post_type) {
    if ('bis_news' === $post_type) {
        $args['labels'] = array(
            'name'                  => 'Новости',
            'singular_name'         => 'Новость',
            'add_new'               => 'Добавить новость',
            'add_new_item'          => 'Добавить новую новость',
            'edit_item'             => 'Редактировать новость',
            'new_item'              => 'Новая новость',
            'view_item'             => 'Просмотр новости',
            'search_items'          => 'Поиск новостей',
            'not_found'             => 'Новости не найдены',
            'not_found_in_trash'    => 'В корзине нет новостей',
            'all_items'             => 'Все новости',
            'archives'              => 'Архив новостей',
            'attributes'            => 'Атрибуты новости',
            'insert_into_item'      => 'Вставить в новость',
            'uploaded_to_this_item' => 'Загружено для этой новости',
            'menu_name'             => 'Новости',
            'filter_items_list'     => 'Фильтровать новости',
            'items_list_navigation' => 'Навигация по новостям',
            'items_list'            => 'Список новостей',
            'name_admin_bar'        => 'Новость',
        );
    }

    if ('bis_project' === $post_type) {
        $args['hierarchical'] = true;
        $args['supports'] = array('title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes');
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

    remove_meta_box('bis_news_image', 'bis_news', 'normal');
    add_meta_box(
        'bis_news_image',
        'Изображение новости',
        'bis_render_news_metabox_override',
        'bis_news',
        'normal',
        'high',
        array(
            '__block_editor_compatible_meta_box' => true,
        )
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
    $legacy_image = get_post_meta($post->ID, 'bis_service_image', true);
    $thumbnail = get_the_post_thumbnail_url($post->ID, 'full');
    $preview = $preview_image ? $preview_image : ($legacy_image ? $legacy_image : $thumbnail);
    $banner_preview = $banner_image ? $banner_image : ($preview ? $preview : $thumbnail);
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
    </div>
    <?php
}

function bis_render_news_metabox_override($post) {
    wp_nonce_field('bis_news_override_nonce', 'bis_news_override_nonce_field');

    $news_image = get_post_meta($post->ID, 'bis_news_image', true);
    $news_image_id = (int) get_post_meta($post->ID, 'bis_news_image_id', true);
    $thumbnail_url = get_the_post_thumbnail_url($post->ID, 'full');
    $preview = $news_image ? $news_image : $thumbnail_url;
    ?>
    <div class="bis-project-box">
        <div class="bis-project-box__header">
            <div>
                <h3>Изображение новости</h3>
                <p>Можно указать ссылку вручную или выбрать изображение из медиабиблиотеки. Если поле пустое, используется «Изображение записи».</p>
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
                <input type="text" id="bis_news_image" name="bis_news_image" value="<?php echo esc_url($news_image); ?>" placeholder="https://" data-image-input data-preview-target="bis_news_image" data-meta-field="bis_news_image" data-attachment-target="bis_news_image_id">
                <input type="hidden" id="bis_news_image_id" name="bis_news_image_id" value="<?php echo esc_attr($news_image_id); ?>" data-meta-field="bis_news_image_id">
                <div class="bis-project-media__buttons">
                    <button type="button" class="button button-primary bis-project-image-upload" data-target="bis_news_image" data-attachment-target="bis_news_image_id">Выбрать в медиабиблиотеке</button>
                    <button type="button" class="button bis-project-image-clear" data-target="bis_news_image">Убрать фото</button>
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

    update_post_meta($post_id, 'bis_service_image', $preview_image);
    update_post_meta($post_id, 'bis_service_preview_image', $preview_image);
    update_post_meta($post_id, 'bis_service_banner_image', $banner_image);
    update_post_meta($post_id, 'bis_service_description', $description);
}
add_action('save_post', 'bis_save_service_override', 20);

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

    if ($news_image === '' && $news_image_id > 0) {
        $resolved_url = wp_get_attachment_url($news_image_id);
        if ($resolved_url) {
            $news_image = esc_url_raw($resolved_url);
        }
    }

    if ($news_image_id === 0 && $news_image !== '') {
        $news_image_id = (int) attachment_url_to_postid($news_image);
    }

    update_post_meta($post_id, 'bis_news_image', $news_image);
    update_post_meta($post_id, 'bis_news_image_id', $news_image_id);
}
add_action('save_post', 'bis_save_news_override', 20);
