<?php
/**
 * Video Business Card (Видео визитка)
 * Matches the reference implementation from ECG
 */

if (!defined('ABSPATH')) {
    exit;
}

function bis_get_video_card_data() {
    $video_card_data = get_option('video_card_data', array());
    if (!is_array($video_card_data) || empty($video_card_data)) {
        $legacy = get_option('bis_video_card_options', array());
        if (empty($legacy) || !is_array($legacy)) {
            $legacy = get_option('ecg_video_card_options', array());
        }
        if (is_array($legacy) && !empty($legacy)) {
            $video_card_data = array(
                'video_id'    => absint($legacy['video_id'] ?? 0),
                'title'       => sanitize_text_field($legacy['title'] ?? ''),
                'description' => sanitize_textarea_field($legacy['description'] ?? ''),
                'position'    => bis_video_card_normalize_position($legacy['position'] ?? 'bottom-right'),
                'enabled'     => !empty($legacy['enabled']) ? 1 : 0,
            );
        }
    }

    if (!is_array($video_card_data)) {
        $video_card_data = array();
    }

    return $video_card_data;
}

function bis_video_card_normalize_position($position) {
    $allowed = array('bottom-right', 'bottom-left', 'top-right', 'top-left');
    $position = sanitize_text_field((string) $position);
    return in_array($position, $allowed, true) ? $position : 'bottom-right';
}

function bis_add_video_card_admin_menu() {
    add_options_page(
        'Видео визитка',
        'Видео визитка',
        'manage_options',
        'video-card',
        'bis_video_card_admin_page'
    );
}
add_action('admin_menu', 'bis_add_video_card_admin_menu');

function bis_video_card_admin_scripts($hook) {
    if ('settings_page_video-card' !== $hook) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script(
        'bis-video-card-admin',
        get_template_directory_uri() . '/assets/js/video-card-admin.js',
        array('jquery'),
        function_exists('bis_get_asset_version') ? bis_get_asset_version('assets/js/video-card-admin.js') : '1.0.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'bis_video_card_admin_scripts');

function bis_video_card_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['submit'])) {
        if (isset($_POST['bis_video_card_nonce'])) {
            check_admin_referer('bis_video_card_settings_save', 'bis_video_card_nonce');
        } elseif (isset($_POST['ecg_video_card_nonce'])) {
            check_admin_referer('ecg_video_card_settings_save', 'ecg_video_card_nonce');
        }

        $video_card_data = array();

        if (!empty($_POST['video_id'])) {
            $title = sanitize_text_field($_POST['title'] ?? '');
            if ($title === '') {
                $title = 'Видео визитка';
            }
            $video_card_data = array(
                'video_id'    => absint($_POST['video_id']),
                'title'       => $title,
                'description' => sanitize_textarea_field($_POST['description'] ?? ''),
                'position'    => bis_video_card_normalize_position($_POST['position'] ?? 'bottom-right'),
                'enabled'     => isset($_POST['enabled']) ? 1 : 0,
            );
        }

        update_option('video_card_data', $video_card_data);
        echo '<div class="notice notice-success"><p>Настройки сохранены!</p></div>';
    }

    $video_card_data = bis_get_video_card_data();
    $video_url = !empty($video_card_data['video_id']) ? wp_get_attachment_url((int) $video_card_data['video_id']) : '';
    ?>
    <div class="wrap">
        <h1>Настройки видео визитки</h1>
        <p>Видео визитка будет отображаться на главной странице и странице «О компании»</p>

        <form method="post" action="">
            <?php wp_nonce_field('bis_video_card_settings_save', 'bis_video_card_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th>Включить видео визитку</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enabled" value="1" <?php checked(!empty($video_card_data['enabled']), true); ?> />
                            Показывать видео визитку на главной странице и странице «О компании»
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>Видео файл</th>
                    <td>
                        <div class="video-upload-container">
                            <video class="preview-video" src="<?php echo esc_url($video_url); ?>" style="max-width:300px;max-height:200px;<?php echo empty($video_url) ? 'display:none;' : ''; ?>" controls></video>
                            <input type="hidden" name="video_id" value="<?php echo esc_attr(!empty($video_card_data['video_id']) ? $video_card_data['video_id'] : ''); ?>" class="video-id" />
                            <br><br>
                            <button type="button" class="button upload-video-button">Выбрать видео</button>
                            <button type="button" class="button remove-video-button" style="<?php echo empty($video_url) ? 'display:none;' : ''; ?>">Удалить видео</button>
                        </div>
                        <p class="description">Рекомендуется использовать видео в формате MP4 для лучшей совместимости</p>
                    </td>
                </tr>
                <tr>
                    <th>Заголовок</th>
                    <td>
                        <input type="text" name="title" value="<?php echo esc_attr(!empty($video_card_data['title']) ? $video_card_data['title'] : ''); ?>" style="width:100%;" placeholder="Например: Видео визитка компании" />
                        <p class="description">Будет показан при наведении на видео</p>
                    </td>
                </tr>
                <tr>
                    <th>Описание</th>
                    <td>
                        <textarea name="description" rows="3" style="width:100%;" placeholder="Краткое описание видео"><?php echo esc_textarea(!empty($video_card_data['description']) ? $video_card_data['description'] : ''); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th>Позиция на странице</th>
                    <td>
                        <select name="position">
                            <option value="bottom-right" <?php selected(!empty($video_card_data['position']) ? $video_card_data['position'] : 'bottom-right', 'bottom-right'); ?>>Внизу справа</option>
                            <option value="bottom-left" <?php selected(!empty($video_card_data['position']) ? $video_card_data['position'] : '', 'bottom-left'); ?>>Внизу слева</option>
                            <option value="top-right" <?php selected(!empty($video_card_data['position']) ? $video_card_data['position'] : '', 'top-right'); ?>>Вверху справа</option>
                            <option value="top-left" <?php selected(!empty($video_card_data['position']) ? $video_card_data['position'] : '', 'top-left'); ?>>Вверху слева</option>
                        </select>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function bis_video_card_is_allowed_page() {
    return is_front_page() || is_page('about') || is_page_template('page-about.php');
}

function bis_display_video_card() {
    if (!bis_video_card_is_allowed_page()) {
        return;
    }

    $video_card_data = bis_get_video_card_data();
    if (empty($video_card_data['enabled']) || empty($video_card_data['video_id'])) {
        return;
    }

    $video_url = wp_get_attachment_url((int) $video_card_data['video_id']);
    if (!$video_url) {
        return;
    }

    $position = bis_video_card_normalize_position($video_card_data['position'] ?? 'bottom-right');
    $title = !empty($video_card_data['title']) ? $video_card_data['title'] : 'Видео визитка';
    $description = !empty($video_card_data['description']) ? $video_card_data['description'] : '';
    ?>
    <div class="video-card-container video-card-<?php echo esc_attr($position); ?>" id="videoCard">
        <div class="video-card-preview">
            <video muted loop autoplay playsinline preload="metadata">
                <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                Ваш браузер не поддерживает видео.
            </video>
            <div class="video-card-overlay">
                <div class="play-button">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" aria-hidden="true" focusable="false">
                        <circle cx="20" cy="20" r="20" fill="rgba(255,255,255,0.9)"/>
                        <path d="M16 12L28 20L16 28V12Z" fill="#333"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="video-card-info">
            <h3><?php echo esc_html($title); ?></h3>
            <?php if ($description !== ''): ?>
                <p><?php echo esc_html($description); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="video-modal" id="videoModal" aria-hidden="true" role="dialog" aria-modal="true">
        <div class="video-modal-content">
            <button type="button" class="video-modal-close" aria-label="Закрыть">&times;</button>
            <video id="modalVideo" controls playsinline preload="metadata">
                <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                Ваш браузер не поддерживает видео.
            </video>
            <div class="video-modal-actions">
                <button type="button" class="video-modal__book-btn callback-btn" data-open-contact data-video-card-contact>
                    <span>Связаться с нами</span>
                </button>
            </div>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'bis_display_video_card');

function bis_video_card_enqueue_scripts() {
    if (!bis_video_card_is_allowed_page()) {
        return;
    }

    $video_card_data = bis_get_video_card_data();
    if (empty($video_card_data['enabled']) || empty($video_card_data['video_id'])) {
        return;
    }

    wp_enqueue_script(
        'bis-video-card-script',
        get_template_directory_uri() . '/assets/js/video-card.js',
        array(),
        function_exists('bis_get_asset_version') ? bis_get_asset_version('assets/js/video-card.js') : '1.0.1',
        true
    );
}
add_action('wp_enqueue_scripts', 'bis_video_card_enqueue_scripts');

function bis_video_card_body_class($classes) {
    if (!bis_video_card_is_allowed_page()) {
        return $classes;
    }

    $video_card_data = bis_get_video_card_data();
    if (!empty($video_card_data['enabled']) && !empty($video_card_data['video_id'])) {
        $position = bis_video_card_normalize_position($video_card_data['position'] ?? 'bottom-right');
        $classes[] = 'bis-has-video-card';
        $classes[] = 'bis-has-video-card-' . $position;
    }

    return $classes;
}
add_filter('body_class', 'bis_video_card_body_class');
