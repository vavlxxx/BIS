<?php

// Hero Slider Settings
function bis_hero_slider_menu() {
    add_menu_page(
        'Слайдер Hero',
        'Слайдер Hero',
        'manage_options',
        'bis-hero-slider',
        'bis_hero_slider_page',
        'dashicons-images-alt2',
        20
    );
}
add_action('admin_menu', 'bis_hero_slider_menu');

function bis_team_menu() {
    add_menu_page(
        'Команда',
        'Команда',
        'manage_options',
        'bis-team',
        'bis_team_page',
        'dashicons-groups',
        22
    );
}
add_action('admin_menu', 'bis_team_menu');

function bis_admin_scripts($hook) {
    if ('toplevel_page_bis-hero-slider' === $hook) {
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('bis-admin-script', get_template_directory_uri() . '/assets/js/admin-script.js', array('jquery', 'jquery-ui-sortable'), '1.0', true);
        wp_enqueue_style('bis-admin-style', get_template_directory_uri() . '/assets/css/admin-style.css', array(), '1.0');
        return;
    }

    if ('toplevel_page_bis-team' === $hook) {
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('bis-team-admin', get_template_directory_uri() . '/assets/js/admin-team.js', array('jquery', 'jquery-ui-sortable'), '1.0', true);
        wp_enqueue_style('bis-team-admin', get_template_directory_uri() . '/assets/css/admin-team.css', array(), '1.0');
        return;
    }

    if ('toplevel_page_bis-floating-buttons' === $hook) {
        wp_enqueue_media();
        wp_enqueue_script('bis-floating-buttons-admin', get_template_directory_uri() . '/assets/js/admin-floating-buttons.js', array('jquery'), '1.0', true);
        wp_enqueue_style('bis-floating-buttons-admin', get_template_directory_uri() . '/assets/css/admin-floating-buttons.css', array(), '1.0');
        return;
    }

    if (in_array($hook, array('post-new.php', 'post.php'), true)) {
        $screen = get_current_screen();
        if ($screen && in_array($screen->post_type, array('bis_project', 'page', 'bis_gratitude', 'bis_service', 'bis_equipment', 'bis_news'), true)) {
            wp_enqueue_media();
            wp_enqueue_script('jquery-ui-sortable');
            $admin_script_path = get_template_directory() . '/assets/js/admin-projects.js';
            $admin_style_path = get_template_directory() . '/assets/css/admin-projects.css';
            $admin_script_version = file_exists($admin_script_path) ? (string) filemtime($admin_script_path) : '1.0';
            $admin_style_version = file_exists($admin_style_path) ? (string) filemtime($admin_style_path) : '1.0';

            wp_enqueue_script('bis-projects-admin', get_template_directory_uri() . '/assets/js/admin-projects.js', array('jquery', 'jquery-ui-sortable'), $admin_script_version, true);
            wp_enqueue_style('bis-projects-admin', get_template_directory_uri() . '/assets/css/admin-projects.css', array(), $admin_style_version);
            wp_add_inline_style('bis-projects-admin', '
                .bis-service-children-list{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:10px;align-items:stretch}
                .bis-service-child-option{display:flex;align-items:flex-start;gap:10px;padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;box-sizing:border-box}
                .bis-service-child-option input{flex:0 0 auto;margin-top:3px}
                .bis-service-child-option span{display:flex;min-width:0;flex-direction:column;gap:4px}
                .bis-service-child-option strong{display:block;color:#111827;line-height:1.35}
                .bis-service-child-option em{display:block;color:#6b7280;font-size:12px;font-style:normal;line-height:1.35}
            ');
        }
    }

    if ('edit.php' === $hook) {
        $screen = get_current_screen();
        if ($screen && 'bis_service' === $screen->post_type) {
            wp_enqueue_script('jquery-ui-sortable');

            $admin_services_js = get_template_directory() . '/assets/js/admin-services.js';
            $admin_services_css = get_template_directory() . '/assets/css/admin-services.css';
            $js_ver = file_exists($admin_services_js) ? (string) filemtime($admin_services_js) : '1.0';
            $css_ver = file_exists($admin_services_css) ? (string) filemtime($admin_services_css) : '1.0';

            wp_enqueue_style('bis-admin-services', get_template_directory_uri() . '/assets/css/admin-services.css', array(), $css_ver);
            wp_enqueue_script('bis-admin-services', get_template_directory_uri() . '/assets/js/admin-services.js', array('jquery', 'jquery-ui-sortable'), $js_ver, true);
            wp_localize_script('bis-admin-services', 'bisServiceOrderConfig', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('bis_service_order_nonce'),
            ));
        }
    }
}
add_action('admin_enqueue_scripts', 'bis_admin_scripts');

function bis_hero_slider_page() {
    if (isset($_POST['bis_hero_slider_save']) && check_admin_referer('bis_hero_slider_nonce')) {
        $images = isset($_POST['hero_images']) ? array_map('esc_url_raw', $_POST['hero_images']) : array();
        update_option('bis_hero_slider_images', $images);
        echo '<div class="updated"><p>Настройки сохранены.</p></div>';
    }

    $images = get_option('bis_hero_slider_images', array());
    ?>
    <div class="wrap">
        <h1>Настройки слайдера Hero</h1>
        <form method="post">
            <?php wp_nonce_field('bis_hero_slider_nonce'); ?>
            <div id="hero-slider-images-container">
                <ul id="hero-slider-list" class="hero-slider-list">
                    <?php if (!empty($images)) : ?>
                        <?php foreach ($images as $image) : ?>
                            <li class="hero-slider-item">
                                <div class="image-preview" style="background-image: url('<?php echo esc_url($image); ?>');"></div>
                                <input type="hidden" name="hero_images[]" value="<?php echo esc_url($image); ?>">
                                <button type="button" class="button remove-image">Удалить</button>
                                <span class="dashicons dashicons-move handle"></span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            <p>
                <button type="button" class="button" id="add-hero-image">Добавить изображение</button>
            </p>
            <p class="submit">
                <input type="submit" name="bis_hero_slider_save" class="button button-primary" value="Сохранить изменения">
            </p>
        </form>
    </div>
    <?php
}

function bis_team_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['bis_team_save']) && check_admin_referer('bis_team_nonce')) {
        $members = array();
        $raw_members = isset($_POST['team_members']) && is_array($_POST['team_members']) ? $_POST['team_members'] : array();

        foreach ($raw_members as $member) {
            $name = isset($member['name']) ? sanitize_text_field($member['name']) : '';
            $role = isset($member['role']) ? sanitize_text_field($member['role']) : '';
            $since = isset($member['since']) ? sanitize_text_field($member['since']) : '';
            $short = isset($member['short']) ? wp_kses_post($member['short']) : '';
            $long = isset($member['long']) ? wp_kses_post($member['long']) : '';
            $photo = isset($member['photo']) ? esc_url_raw($member['photo']) : '';
            $modal_photo = isset($member['modal_photo']) ? esc_url_raw($member['modal_photo']) : '';
            $qr_code = isset($member['qr_code']) ? esc_url_raw($member['qr_code']) : '';
            $contact_phone = isset($member['contact_phone']) ? sanitize_text_field($member['contact_phone']) : '';
            $contact_email = isset($member['contact_email']) ? sanitize_email($member['contact_email']) : '';

            if ($name === '' && $role === '' && $since === '' && $short === '' && $long === '' && $photo === '' && $modal_photo === '' && $qr_code === '' && $contact_phone === '' && $contact_email === '') {
                continue;
            }

            $members[] = array(
                'name' => $name,
                'role' => $role,
                'since' => $since,
                'short' => $short,
                'long' => $long,
                'photo' => $photo,
                'modal_photo' => $modal_photo,
                'qr_code' => $qr_code,
                'contact_phone' => $contact_phone,
                'contact_email' => $contact_email,
            );
        }

        update_option('bis_team_members', $members);
        echo '<div class="updated"><p>Команда сохранена.</p></div>';
    }

    $members = get_option('bis_team_members', array());
    if (!is_array($members)) {
        $members = array();
    }
    ?>
    <div class="wrap bis-team-admin">
        <h1>Команда</h1>
        <p class="description">Добавляйте сотрудников для слайдера блока "Команда". Изменения сразу отражаются на главной странице.</p>

        <form method="post">
            <?php wp_nonce_field('bis_team_nonce'); ?>

            <ul id="team-members-list" class="team-members-list">
                <?php foreach ($members as $index => $member) :
                    $name = isset($member['name']) ? $member['name'] : '';
                    $role = isset($member['role']) ? $member['role'] : '';
                    $since = isset($member['since']) ? $member['since'] : '';
                    $short = isset($member['short']) ? $member['short'] : '';
                    $long = isset($member['long']) ? $member['long'] : '';
                    $photo = isset($member['photo']) ? $member['photo'] : '';
                    $modal_photo = isset($member['modal_photo']) ? $member['modal_photo'] : '';
                    $qr_code = isset($member['qr_code']) ? $member['qr_code'] : '';
                    $contact_phone = isset($member['contact_phone']) ? $member['contact_phone'] : '';
                    $contact_email = isset($member['contact_email']) ? $member['contact_email'] : '';
                    ?>
                    <li class="team-member-item" data-index="<?php echo esc_attr($index); ?>">
                        <div class="team-member-card">
                            <div class="team-member-media">
                                <div class="team-member-preview <?php echo $photo ? '' : 'is-empty'; ?>" data-preview="photo" style="background-image: url('<?php echo esc_url($photo); ?>');">
                                    <?php if (!$photo) : ?>
                                        <span class="team-member-placeholder">Нет фото</span>
                                    <?php endif; ?>
                                </div>
                                <div class="team-member-controls">
                                    <label>Фото для слайда</label>
                                    <input type="text" value="<?php echo esc_url($photo); ?>" data-field="photo" name="team_members[<?php echo esc_attr($index); ?>][photo]" placeholder="https://">
                                    <div class="team-member-buttons">
                                        <button type="button" class="button team-photo-upload" data-photo-type="photo">Выбрать</button>
                                        <button type="button" class="button team-photo-clear" data-photo-type="photo">Убрать</button>
                                    </div>
                                </div>
                            </div>

                            <div class="team-member-media">
                                <div class="team-member-preview <?php echo $modal_photo ? '' : 'is-empty'; ?>" data-preview="modal_photo" style="background-image: url('<?php echo esc_url($modal_photo); ?>');">
                                    <?php if (!$modal_photo) : ?>
                                        <span class="team-member-placeholder">Нет фото</span>
                                    <?php endif; ?>
                                </div>
                                <div class="team-member-controls">
                                    <label>Фото для модального окна</label>
                                    <input type="text" value="<?php echo esc_url($modal_photo); ?>" data-field="modal_photo" name="team_members[<?php echo esc_attr($index); ?>][modal_photo]" placeholder="https://">
                                    <div class="team-member-buttons">
                                        <button type="button" class="button team-photo-upload" data-photo-type="modal_photo">Выбрать</button>
                                        <button type="button" class="button team-photo-clear" data-photo-type="modal_photo">Убрать</button>
                                    </div>
                                </div>
                            </div>

                            <div class="team-member-fields">
                                <div class="team-field">
                                    <label>ФИО</label>
                                    <input type="text" value="<?php echo esc_attr($name); ?>" data-field="name" name="team_members[<?php echo esc_attr($index); ?>][name]" placeholder="Иванов Иван Иванович">
                                </div>
                                <div class="team-field">
                                    <label>Должность</label>
                                    <input type="text" value="<?php echo esc_attr($role); ?>" data-field="role" name="team_members[<?php echo esc_attr($index); ?>][role]" placeholder="Директор">
                                </div>
                                <div class="team-field">
                                    <label>В команде с</label>
                                    <input type="text" value="<?php echo esc_attr($since); ?>" data-field="since" name="team_members[<?php echo esc_attr($index); ?>][since]" placeholder="2021">
                                </div>
                                <div class="team-member-contact-grid">
                                    <div class="team-member-media team-member-media--qr">
                                        <div class="team-member-preview team-member-preview--qr <?php echo $qr_code ? '' : 'is-empty'; ?>" data-preview="qr_code" data-placeholder="Нет QR-кода" style="background-image: url('<?php echo esc_url($qr_code); ?>');">
                                            <?php if (!$qr_code) : ?>
                                                <span class="team-member-placeholder">Нет QR-кода</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="team-member-controls">
                                            <label>QR-код контакта</label>
                                            <input type="text" value="<?php echo esc_url($qr_code); ?>" data-field="qr_code" name="team_members[<?php echo esc_attr($index); ?>][qr_code]" placeholder="https://">
                                            <div class="team-member-buttons">
                                                <button type="button" class="button team-photo-upload" data-photo-type="qr_code">Выбрать</button>
                                                <button type="button" class="button team-photo-clear" data-photo-type="qr_code">Убрать</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="team-member-contact-fields">
                                        <div class="team-field">
                                            <label>Телефон</label>
                                            <input type="text" value="<?php echo esc_attr($contact_phone); ?>" data-field="contact_phone" name="team_members[<?php echo esc_attr($index); ?>][contact_phone]" placeholder="+7 (926) 438-07-70">
                                        </div>
                                        <div class="team-field">
                                            <label>Email</label>
                                            <input type="email" value="<?php echo esc_attr($contact_email); ?>" data-field="contact_email" name="team_members[<?php echo esc_attr($index); ?>][contact_email]" placeholder="office@bis-rf.ru">
                                        </div>
                                    </div>
                                </div>
                                <div class="team-field team-field--full">
                                    <label>Короткое описание для слайда</label>
                                    <textarea rows="4" data-field="short" name="team_members[<?php echo esc_attr($index); ?>][short]" placeholder="Короткая история/резюме"><?php echo esc_textarea($short); ?></textarea>
                                </div>
                                <div class="team-field team-field--full">
                                    <label>Подробное описание для модального окна</label>
                                    <textarea rows="6" data-field="long" name="team_members[<?php echo esc_attr($index); ?>][long]" placeholder="Подробный текст"><?php echo esc_textarea($long); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="team-member-actions">
                            <div class="team-member-actions__buttons">
                                <button type="submit" name="bis_team_save" class="button button-primary team-member-save">Сохранить</button>
                                <button type="button" class="button link-delete team-member-remove">Удалить сотрудника</button>
                            </div>
                            <span class="dashicons dashicons-move handle" aria-hidden="true"></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

            <p>
                <button type="button" class="button" id="add-team-member">Добавить сотрудника</button>
            </p>

            <p class="submit">
                <input type="submit" name="bis_team_save" class="button button-primary" value="Сохранить изменения">
            </p>
        </form>

        <script type="text/template" id="team-member-template">
            <li class="team-member-item" data-index="">
                <div class="team-member-card">
                    <div class="team-member-media">
                        <div class="team-member-preview is-empty" data-preview="photo">
                            <span class="team-member-placeholder">Нет фото</span>
                        </div>
                        <div class="team-member-controls">
                            <label>Фото для слайда</label>
                            <input type="text" value="" data-field="photo" placeholder="https://">
                            <div class="team-member-buttons">
                                <button type="button" class="button team-photo-upload" data-photo-type="photo">Выбрать</button>
                                <button type="button" class="button team-photo-clear" data-photo-type="photo">Убрать</button>
                            </div>
                        </div>
                    </div>

                    <div class="team-member-media">
                        <div class="team-member-preview is-empty" data-preview="modal_photo">
                            <span class="team-member-placeholder">Нет фото</span>
                        </div>
                        <div class="team-member-controls">
                            <label>Фото для модального окна</label>
                            <input type="text" value="" data-field="modal_photo" placeholder="https://">
                            <div class="team-member-buttons">
                                <button type="button" class="button team-photo-upload" data-photo-type="modal_photo">Выбрать</button>
                                <button type="button" class="button team-photo-clear" data-photo-type="modal_photo">Убрать</button>
                            </div>
                        </div>
                    </div>

                    <div class="team-member-fields">
                        <div class="team-field">
                            <label>ФИО</label>
                            <input type="text" value="" data-field="name" placeholder="Иванов Иван Иванович">
                        </div>
                        <div class="team-field">
                            <label>Должность</label>
                            <input type="text" value="" data-field="role" placeholder="Директор">
                        </div>
                        <div class="team-field">
                            <label>В команде с</label>
                            <input type="text" value="" data-field="since" placeholder="2021">
                        </div>
                        <div class="team-member-contact-grid">
                            <div class="team-member-media team-member-media--qr">
                                <div class="team-member-preview team-member-preview--qr is-empty" data-preview="qr_code" data-placeholder="Нет QR-кода">
                                    <span class="team-member-placeholder">Нет QR-кода</span>
                                </div>
                                <div class="team-member-controls">
                                    <label>QR-код контакта</label>
                                    <input type="text" value="" data-field="qr_code" placeholder="https://">
                                    <div class="team-member-buttons">
                                        <button type="button" class="button team-photo-upload" data-photo-type="qr_code">Выбрать</button>
                                        <button type="button" class="button team-photo-clear" data-photo-type="qr_code">Убрать</button>
                                    </div>
                                </div>
                            </div>
                            <div class="team-member-contact-fields">
                                <div class="team-field">
                                    <label>Телефон</label>
                                    <input type="text" value="" data-field="contact_phone" placeholder="+7 (926) 438-07-70">
                                </div>
                                <div class="team-field">
                                    <label>Email</label>
                                    <input type="email" value="" data-field="contact_email" placeholder="office@bis-rf.ru">
                                </div>
                            </div>
                        </div>
                        <div class="team-field team-field--full">
                            <label>Короткое описание для слайда</label>
                            <textarea rows="4" data-field="short" placeholder="Короткая история/резюме"></textarea>
                        </div>
                        <div class="team-field team-field--full">
                            <label>Подробное описание для модального окна</label>
                            <textarea rows="6" data-field="long" placeholder="Подробный текст"></textarea>
                        </div>
                    </div>
                </div>

                <div class="team-member-actions">
                    <div class="team-member-actions__buttons">
                        <button type="submit" name="bis_team_save" class="button button-primary team-member-save">Сохранить</button>
                        <button type="button" class="button link-delete team-member-remove">Удалить сотрудника</button>
                    </div>
                    <span class="dashicons dashicons-move handle" aria-hidden="true"></span>
                </div>
            </li>
        </script>
    </div>
    <?php
}

// Floating social buttons settings
function bis_social_buttons_menu() {
    add_menu_page(
        'Плавающие кнопки',
        'Плавающие кнопки',
        'manage_options',
        'bis-floating-buttons',
        'bis_social_buttons_page',
        'dashicons-share',
        21
    );
}
add_action('admin_menu', 'bis_social_buttons_menu');

function bis_social_buttons_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['bis_social_buttons_save']) && check_admin_referer('bis_social_buttons_nonce')) {
        $images = isset($_POST['bis_social_buttons_image']) ? (array) $_POST['bis_social_buttons_image'] : array();
        $links = isset($_POST['bis_social_buttons_link']) ? (array) $_POST['bis_social_buttons_link'] : array();

        $buttons = array();
        foreach ($images as $index => $image) {
            $image_url = esc_url_raw($image);
            $link_url = isset($links[$index]) ? esc_url_raw($links[$index]) : '';

            if (empty($image_url) || empty($link_url)) {
                continue;
            }

            $buttons[] = array(
                'image' => $image_url,
                'link'  => $link_url,
            );
        }

        update_option('bis_social_buttons', $buttons);
        echo '<div class="updated"><p>Настройки сохранены.</p></div>';
    }

    $buttons = get_option('bis_social_buttons', array());
    ?>
    <div class="wrap">
        <h1>Плавающие кнопки социальных сетей</h1>
        <p class="description bis-floating-buttons-description">
            Добавьте любое количество кнопок с изображениями и ссылками. Они будут закреплены поверх сайта и помогут посетителям быстро переходить в нужные социальные сети.
        </p>
        <form method="post">
            <?php wp_nonce_field('bis_social_buttons_nonce'); ?>
            <ul id="bis-floating-buttons-list" class="bis-floating-buttons-list">
                <?php if (!empty($buttons)) : ?>
                    <?php foreach ($buttons as $button) : ?>
                        <li class="bis-floating-buttons-item<?php echo !empty($button['image']) ? ' has-image' : ''; ?>">
                            <div class="bis-floating-buttons-preview" <?php echo !empty($button['image']) ? 'style="background-image: url(' . esc_url($button['image']) . ');"' : ''; ?>></div>
                            <div class="bis-floating-buttons-fields">
                                <input type="hidden" class="bis-floating-buttons-image" name="bis_social_buttons_image[]" value="<?php echo esc_url($button['image']); ?>">
                                <button type="button" class="button bis-select-floating-image">Выбрать изображение</button>
                                <label>
                                    <span>Ссылка</span>
                                    <input type="url" class="regular-text" name="bis_social_buttons_link[]" value="<?php echo esc_url($button['link']); ?>" placeholder="https://example.com" required>
                                </label>
                            </div>
                            <button type="button" class="button button-link-delete bis-remove-floating-button">Удалить</button>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            <p class="bis-floating-buttons-empty<?php echo empty($buttons) ? '' : ' hidden'; ?>">Пока нет ни одной кнопки — добавьте первую.</p>
            <p>
                <button type="button" class="button" id="bis-add-floating-button">Добавить кнопку</button>
            </p>
            <p class="submit">
                <input type="submit" name="bis_social_buttons_save" class="button button-primary" value="Сохранить изменения">
            </p>
        </form>
    </div>
    <script type="text/template" id="bis-floating-button-template">
        <li class="bis-floating-buttons-item">
            <div class="bis-floating-buttons-preview"></div>
            <div class="bis-floating-buttons-fields">
                <input type="hidden" class="bis-floating-buttons-image" name="bis_social_buttons_image[]" value="">
                <button type="button" class="button bis-select-floating-image">Выбрать изображение</button>
                <label>
                    <span>Ссылка</span>
                    <input type="url" class="regular-text" name="bis_social_buttons_link[]" value="" placeholder="https://example.com" required>
                </label>
            </div>
            <button type="button" class="button button-link-delete bis-remove-floating-button">Удалить</button>
        </li>
    </script>
    <?php
}

function bis_render_floating_social_buttons() {
    $buttons = get_option('bis_social_buttons', array());

    if (empty($buttons) || !is_array($buttons)) {
        return;
    }

    $items = array();
    foreach ($buttons as $button) {
        $image = isset($button['image']) ? esc_url($button['image']) : '';
        $link = isset($button['link']) ? esc_url($button['link']) : '';

        if (!$image || !$link) {
            continue;
        }

        $items[] = sprintf(
            '<a class="floating-social-buttons__link" href="%1$s" target="_blank" rel="noopener noreferrer"><img src="%2$s" alt="%3$s" loading="lazy"></a>',
            $link,
            $image,
            esc_attr__('Социальная сеть', 'bis')
        );
    }

    if (empty($items)) {
        return;
    }

    echo '<div class="floating-social-buttons" aria-label="Социальные сети">';
    echo implode('', $items);
    echo '</div>';
}
add_action('wp_footer', 'bis_render_floating_social_buttons');

/**
 * Revenue chart settings
 */
function bis_get_revenue_settings() {
    $defaults = array(
        'title'          => 'Динамика выручки за 10 лет',
        'currency_label' => 'млрд ₽',
        'cta_label'      => 'Узнать больше',
        'cta_link'       => '#contact',
        'points'         => array(
            array('label' => '2014', 'value' => 1.1),
            array('label' => '2015', 'value' => 3.8),
            array('label' => '2016', 'value' => 5.2),
            array('label' => '2017', 'value' => 8.0),
            array('label' => '2018', 'value' => 10.2),
            array('label' => '2019', 'value' => 11.4),
            array('label' => '2020', 'value' => 18.0),
            array('label' => '2021', 'value' => 19.1),
            array('label' => '2022', 'value' => 54.5),
            array('label' => '2023', 'value' => 51.7),
        ),
    );

    $settings = get_option('bis_revenue_chart', array());
    if (empty($settings) || !is_array($settings)) {
        return $defaults;
    }

    return wp_parse_args($settings, $defaults);
}

function bis_revenue_menu() {
    add_menu_page(
        'Динамика выручки',
        'Динамика выручки',
        'manage_options',
        'bis-revenue',
        'bis_revenue_page',
        'dashicons-chart-line',
        22
    );
}
add_action('admin_menu', 'bis_revenue_menu');

function bis_revenue_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['bis_revenue_save']) && check_admin_referer('bis_revenue_nonce')) {
        $years  = isset($_POST['bis_revenue_year']) ? (array) $_POST['bis_revenue_year'] : array();
        $values = isset($_POST['bis_revenue_value']) ? (array) $_POST['bis_revenue_value'] : array();

        $points = array();
        foreach ($years as $index => $year) {
            $year_label = sanitize_text_field(wp_unslash($year));
            $value_raw  = isset($values[$index]) ? wp_unslash($values[$index]) : '';
            if ($year_label === '' && $value_raw === '') {
                continue;
            }
            $value = floatval(str_replace(',', '.', $value_raw));
            $points[] = array(
                'label' => $year_label,
                'value' => $value,
            );
        }

        $settings = bis_get_revenue_settings();
        $settings['points'] = $points;

        update_option('bis_revenue_chart', $settings);
        echo '<div class="updated"><p>Настройки сохранены.</p></div>';
    }

    $settings = bis_get_revenue_settings();
    $points   = !empty($settings['points']) && is_array($settings['points']) ? $settings['points'] : array();
    ?>
    <div class="wrap">
        <h1>Динамика выручки</h1>
        <p class="description">Управляйте точками графика на главной странице. Укажите подписи (например, годы) и значения.</p>

        <form method="post">
            <?php wp_nonce_field('bis_revenue_nonce'); ?>
            <h2 style="margin-top:30px;">Точки графика</h2>
            <p class="description">Укажите подпись (обычно год) и значение. Для дробных значений можно использовать запятую или точку.</p>

            <table class="widefat fixed striped" id="bis-revenue-table" style="max-width:800px; margin-top:10px;">
                <thead>
                    <tr>
                        <th style="width:40%;">Подпись</th>
                        <th style="width:40%;">Значение</th>
                        <th style="width:20%;">Действие</th>
                    </tr>
                </thead>
                <tbody id="bis-revenue-rows">
                    <?php if (!empty($points)) : ?>
                        <?php foreach ($points as $point) : ?>
                            <tr>
                                <td><input type="text" name="bis_revenue_year[]" value="<?php echo esc_attr($point['label']); ?>" placeholder="Год" class="widefat"></td>
                                <td><input type="text" name="bis_revenue_value[]" value="<?php echo esc_attr($point['value']); ?>" placeholder="Значение" class="widefat"></td>
                                <td><button type="button" class="button bis-revenue-remove">Удалить</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td><input type="text" name="bis_revenue_year[]" value="" placeholder="Год" class="widefat"></td>
                            <td><input type="text" name="bis_revenue_value[]" value="" placeholder="Значение" class="widefat"></td>
                            <td><button type="button" class="button bis-revenue-remove">Удалить</button></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <p style="margin-top:12px;">
                <button type="button" class="button" id="bis-revenue-add">Добавить точку</button>
            </p>

            <p class="submit" style="margin-top:20px;">
                <input type="submit" name="bis_revenue_save" class="button button-primary" value="Сохранить изменения">
            </p>
        </form>
    </div>
    <script type="text/html" id="bis-revenue-row-template">
        <tr>
            <td><input type="text" name="bis_revenue_year[]" value="" placeholder="Год" class="widefat"></td>
            <td><input type="text" name="bis_revenue_value[]" value="" placeholder="Значение" class="widefat"></td>
            <td><button type="button" class="button bis-revenue-remove">Удалить</button></td>
        </tr>
    </script>
    <script>
        (function() {
            const addBtn = document.getElementById('bis-revenue-add');
            const rows = document.getElementById('bis-revenue-rows');
            const template = document.getElementById('bis-revenue-row-template').textContent.trim();

            if (addBtn && rows) {
                addBtn.addEventListener('click', function() {
                    rows.insertAdjacentHTML('beforeend', template);
                });

                rows.addEventListener('click', function(e) {
                    if (e.target.classList.contains('bis-revenue-remove')) {
                        const tr = e.target.closest('tr');
                        if (tr && rows.children.length > 1) {
                            tr.remove();
                        } else if (tr) {
                            tr.querySelectorAll('input').forEach(input => input.value = '');
                        }
                    }
                });
            }
        })();
    </script>
    <?php
}

/**
 * Maintenance mode settings
 */
function bis_get_maintenance_settings() {
    $defaults = array(
        'enabled' => '0',
        'badge'   => 'Технические работы',
        'title'   => 'Сайт временно недоступен',
        'message' => 'Сейчас на сайте идут технические работы. Мы скоро вернёмся. Спасибо за понимание!',
        'phone'   => '+7 (926) 438-07-70',
        'email'   => 'office@bis-rf.ru',
    );

    $settings = get_option('bis_maintenance_settings', array());
    if (!is_array($settings)) {
        return $defaults;
    }

    return wp_parse_args($settings, $defaults);
}

function bis_maintenance_menu() {
    add_menu_page(
        'Технические работы',
        'Тех. работы',
        'manage_options',
        'bis-maintenance',
        'bis_maintenance_page',
        'dashicons-shield-alt',
        23
    );
}
add_action('admin_menu', 'bis_maintenance_menu');

function bis_maintenance_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['bis_maintenance_save']) && check_admin_referer('bis_maintenance_nonce')) {
        $settings = array(
            'enabled' => isset($_POST['bis_maintenance_enabled']) ? '1' : '0',
            'badge'   => isset($_POST['bis_maintenance_badge']) ? sanitize_text_field(wp_unslash($_POST['bis_maintenance_badge'])) : '',
            'title'   => isset($_POST['bis_maintenance_title']) ? sanitize_text_field(wp_unslash($_POST['bis_maintenance_title'])) : '',
            'message' => isset($_POST['bis_maintenance_message']) ? sanitize_textarea_field(wp_unslash($_POST['bis_maintenance_message'])) : '',
            'phone'   => isset($_POST['bis_maintenance_phone']) ? sanitize_text_field(wp_unslash($_POST['bis_maintenance_phone'])) : '',
            'email'   => isset($_POST['bis_maintenance_email']) ? sanitize_email(wp_unslash($_POST['bis_maintenance_email'])) : '',
        );

        update_option('bis_maintenance_settings', $settings);
        echo '<div class="updated"><p>Настройки сохранены.</p></div>';
    }

    $settings = bis_get_maintenance_settings();
    ?>
    <div class="wrap">
        <h1>Технические работы</h1>
        <p class="description">Включите заглушку, чтобы скрыть сайт для гостей. Администраторы продолжают видеть сайт.</p>
        <form method="post">
            <?php wp_nonce_field('bis_maintenance_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Статус</th>
                    <td>
                        <label>
                            <input type="checkbox" name="bis_maintenance_enabled" value="1" <?php checked($settings['enabled'], '1'); ?>>
                            Включить заглушку
                        </label>
                        <p class="description">Незалогиненные посетители увидят страницу технических работ.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="bis_maintenance_badge">Бейдж</label></th>
                    <td><input type="text" id="bis_maintenance_badge" name="bis_maintenance_badge" class="regular-text" value="<?php echo esc_attr($settings['badge']); ?>" placeholder="Технические работы"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="bis_maintenance_title">Заголовок</label></th>
                    <td><input type="text" id="bis_maintenance_title" name="bis_maintenance_title" class="regular-text" value="<?php echo esc_attr($settings['title']); ?>" placeholder="Сайт временно недоступен"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="bis_maintenance_message">Сообщение</label></th>
                    <td><textarea id="bis_maintenance_message" name="bis_maintenance_message" rows="3" class="large-text" placeholder="Сейчас на сайте идут технические работы."><?php echo esc_textarea($settings['message']); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="bis_maintenance_phone">Телефон</label></th>
                    <td><input type="text" id="bis_maintenance_phone" name="bis_maintenance_phone" class="regular-text" value="<?php echo esc_attr($settings['phone']); ?>" placeholder="+7 (000) 000-00-00"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="bis_maintenance_email">Email</label></th>
                    <td><input type="email" id="bis_maintenance_email" name="bis_maintenance_email" class="regular-text" value="<?php echo esc_attr($settings['email']); ?>" placeholder="office@example.com"></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="bis_maintenance_save" class="button button-primary" value="Сохранить изменения">
            </p>
        </form>
    </div>
    <?php
}

function bis_handle_maintenance_mode() {
    $settings = bis_get_maintenance_settings();
    $enabled  = isset($settings['enabled']) && '1' === $settings['enabled'];

    if (!$enabled) {
        return;
    }

    if (is_user_logged_in() && current_user_can('manage_options')) {
        return;
    }

    if (is_admin() || wp_doing_ajax() || wp_doing_cron() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }

    status_header(503);
    nocache_headers();
    include get_template_directory() . '/maintenance.php';
    exit;
}
add_action('template_redirect', 'bis_handle_maintenance_mode');

