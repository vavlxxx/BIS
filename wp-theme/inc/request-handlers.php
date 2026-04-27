<?php

// Register Custom Post Type for Requests
function bis_register_requests_cpt() {
    register_post_type('bis_request', array(
        'labels' => array(
            'name' => 'Заявки',
            'singular_name' => 'Заявка',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => false, // Hidden from main menu, accessed via custom page
        'supports' => array('title', 'custom-fields'),
        'capabilities' => array(
            'create_posts' => 'do_not_allow', // Users can't create via admin
        ),
        'map_meta_cap' => true,
    ));
}
add_action('init', 'bis_register_requests_cpt');

function bis_parse_size_to_bytes($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return 0;
    }

    $unit = strtolower(substr($value, -1));
    $bytes = (float) $value;

    switch ($unit) {
        case 'g':
            $bytes *= 1024;
        case 'm':
            $bytes *= 1024;
        case 'k':
            $bytes *= 1024;
    }

    return (int) $bytes;
}

function bis_is_request_larger_than_post_max_size() {
    $content_length = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
    $post_max_size = bis_parse_size_to_bytes(ini_get('post_max_size'));

    return $content_length > 0 && $post_max_size > 0 && $content_length > $post_max_size;
}

function bis_get_upload_error_message($error_code) {
    switch ((int) $error_code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'Файл слишком большой для загрузки на сервер.';
        case UPLOAD_ERR_PARTIAL:
            return 'Файл загрузился не полностью. Повторите попытку.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'На сервере не настроена временная директория для загрузки файлов.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Сервер не смог сохранить файл.';
        case UPLOAD_ERR_EXTENSION:
            return 'Загрузка файла остановлена расширением PHP.';
        default:
            return 'Не удалось загрузить файл.';
    }
}

function bis_normalize_request_messenger($messenger) {
    $messenger = trim((string) $messenger);
    $normalized = strtolower($messenger);

    if (in_array($normalized, array('phone', 'by phone', 'telephone', 'tel', 'по телефону'), true)) {
        return 'По телефону';
    }

    if ('telegram' === $normalized) {
        return 'Telegram';
    }

    if ('max' === $normalized) {
        return 'MAX';
    }

    return $messenger;
}

function bis_is_valid_request_phone($phone) {
    $digits = preg_replace('/\D+/', '', (string) $phone);

    return 11 === strlen($digits) && '7' === substr($digits, 0, 1);
}

function bis_get_location_placeholder() {
    return 'Не определено';
}

function bis_get_location_cookie_name() {
    return 'bis_user_location';
}

function bis_get_request_remote_ip() {
    $candidate_keys = array(
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
        'REMOTE_ADDR',
    );

    foreach ($candidate_keys as $key) {
        if (empty($_SERVER[$key])) {
            continue;
        }

        $raw_value = sanitize_text_field(wp_unslash($_SERVER[$key]));
        $parts = array_map('trim', explode(',', $raw_value));

        foreach ($parts as $part) {
            if ($part !== '' && false !== filter_var($part, FILTER_VALIDATE_IP)) {
                return $part;
            }
        }
    }

    return '';
}

function bis_get_location_fallback_data() {
    $placeholder = bis_get_location_placeholder();

    return array(
        'city' => $placeholder,
        'region' => '',
        'label' => $placeholder,
        'source' => 'fallback',
        'resolved' => false,
    );
}

function bis_is_public_ip_address($ip) {
    $ip = trim((string) $ip);
    if ($ip === '') {
        return false;
    }

    return false !== filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    );
}

function bis_normalize_location_response($location, $source = 'ip') {
    if (!is_array($location)) {
        return bis_get_location_fallback_data();
    }

    if (!empty($location['data']) && is_array($location['data'])) {
        $location = $location['data'];
    }

    $city = '';
    $region = '';

    $city_candidates = array(
        $location['city'] ?? '',
        $location['settlement'] ?? '',
        $location['area'] ?? '',
        $location['city_with_type'] ?? '',
        $location['settlement_with_type'] ?? '',
        $location['area_with_type'] ?? '',
    );

    foreach ($city_candidates as $candidate) {
        $candidate = trim((string) $candidate);
        if ($candidate !== '') {
            $city = $candidate;
            break;
        }
    }

    $region_candidates = array(
        $location['region_with_type'] ?? '',
        $location['region'] ?? '',
    );

    foreach ($region_candidates as $candidate) {
        $candidate = trim((string) $candidate);
        if ($candidate !== '' && $candidate !== $city) {
            $region = $candidate;
            break;
        }
    }

    $normalized_city = function_exists('mb_strtolower') ? mb_strtolower($city, 'UTF-8') : strtolower($city);
    $normalized_region = function_exists('mb_strtolower') ? mb_strtolower($region, 'UTF-8') : strtolower($region);
    $normalized_region = preg_replace('/^(г\.?|город)\s+/u', '', (string) $normalized_region);
    if ($normalized_region === $normalized_city) {
        $region = '';
    }

    if ($city === '') {
        return bis_get_location_fallback_data();
    }

    $label_parts = array_values(array_unique(array_filter(array($city, $region))));

    return array(
        'city' => $city,
        'region' => $region,
        'label' => !empty($label_parts) ? implode(', ', $label_parts) : $city,
        'source' => $source,
        'resolved' => true,
    );
}

function bis_get_dadata_request_headers() {
    $settings = bis_get_dadata_settings();
    if (empty($settings['api_key'])) {
        return array();
    }

    $headers = array(
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'Authorization' => 'Token ' . $settings['api_key'],
    );

    if (!empty($settings['secret_key'])) {
        $headers['X-Secret'] = $settings['secret_key'];
    }

    return $headers;
}

function bis_verify_hcaptcha_response() {
    if (!bis_is_hcaptcha_configured()) {
        return true;
    }

    $token = isset($_POST['h-captcha-response']) ? trim((string) wp_unslash($_POST['h-captcha-response'])) : '';
    if ('' === $token) {
        return new WP_Error('bis_hcaptcha_missing', 'Подтвердите, что вы не робот.');
    }

    $settings = bis_get_hcaptcha_settings();
    $response = wp_remote_post(
        'https://hcaptcha.com/siteverify',
        array(
            'timeout' => 8,
            'body'    => array(
                'secret'   => $settings['secret_key'],
                'response' => $token,
                'remoteip' => bis_get_request_remote_ip(),
            ),
        )
    );

    if (is_wp_error($response)) {
        return new WP_Error('bis_hcaptcha_request_failed', 'Не удалось проверить капчу. Попробуйте ещё раз.');
    }

    $payload = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($payload) || empty($payload['success'])) {
        return new WP_Error('bis_hcaptcha_invalid', 'Капча не пройдена. Попробуйте ещё раз.');
    }

    return true;
}

function bis_dadata_request($endpoint, array $payload) {
    $headers = bis_get_dadata_request_headers();
    if (empty($headers)) {
        return new WP_Error('bis_dadata_missing_key', 'Не настроен API-ключ Dadata.');
    }

    $response = wp_remote_post(
        'https://suggestions.dadata.ru/suggestions/api/4_1/rs/' . ltrim($endpoint, '/'),
        array(
            'headers' => $headers,
            'body' => wp_json_encode($payload),
            'timeout' => 8,
        )
    );

    if (is_wp_error($response)) {
        return $response;
    }

    $status_code = (int) wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $decoded = json_decode((string) $body, true);

    if ($status_code < 200 || $status_code >= 300 || !is_array($decoded)) {
        return new WP_Error('bis_dadata_invalid_response', 'Dadata вернул некорректный ответ.');
    }

    return $decoded;
}

function bis_get_client_ip_address() {
    $server_keys = array(
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_REAL_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR',
    );

    foreach ($server_keys as $server_key) {
        if (empty($_SERVER[$server_key])) {
            continue;
        }

        $raw_value = wp_unslash($_SERVER[$server_key]);
        $candidates = 'HTTP_X_FORWARDED_FOR' === $server_key ? explode(',', $raw_value) : array($raw_value);

        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_IP)) {
                return $candidate;
            }
        }
    }

    return '';
}

function bis_detect_location_by_ip($ip = '') {
    $ip = trim((string) $ip);
    if ($ip === '') {
        return new WP_Error('bis_empty_ip', 'IP-адрес не определён.');
    }

    if (!bis_is_public_ip_address($ip)) {
        return new WP_Error('bis_private_ip', 'Локальный или приватный IP нельзя использовать для геоопределения.');
    }

    $response = bis_dadata_request('iplocate/address', array('ip' => $ip));
    if (is_wp_error($response)) {
        return $response;
    }

    if (!empty($response['location']['data']) && is_array($response['location']['data'])) {
        return bis_normalize_location_response($response['location']['data'], 'ip');
    }

    if (!empty($response['location']) && is_array($response['location'])) {
        return bis_normalize_location_response($response['location'], 'ip');
    }

    if (!empty($response['suggestions'][0]['data']) && is_array($response['suggestions'][0]['data'])) {
        return bis_normalize_location_response($response['suggestions'][0]['data'], 'ip');
    }

    return bis_get_location_fallback_data();
}

function bis_resolve_location_by_query($query) {
    $query = trim((string) $query);
    if ($query === '') {
        return new WP_Error('bis_empty_location_query', 'Укажите город.');
    }

    $response = bis_dadata_request('suggest/address', array(
        'query' => $query,
        'count' => 5,
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    if (empty($response['suggestions']) || !is_array($response['suggestions'])) {
        return new WP_Error('bis_location_not_found', 'Не удалось найти такой город.');
    }

    foreach ($response['suggestions'] as $suggestion) {
        if (empty($suggestion['data']) || !is_array($suggestion['data'])) {
            continue;
        }

        $normalized = bis_normalize_location_response($suggestion['data'], 'manual');
        if (!empty($normalized['resolved'])) {
            return $normalized;
        }
    }

    return new WP_Error('bis_location_not_found', 'Не удалось найти такой город.');
}

function bis_get_request_location_value() {
    $location_meta = bis_get_request_location_meta_input();

    return isset($location_meta['bis_location_region']) ? $location_meta['bis_location_region'] : bis_get_location_placeholder();
}

function bis_get_request_location_cookie_data() {
    if (empty($_COOKIE[bis_get_location_cookie_name()])) {
        return array();
    }

    $cookie_value = json_decode(rawurldecode(wp_unslash($_COOKIE[bis_get_location_cookie_name()])), true);

    return is_array($cookie_value) ? $cookie_value : array();
}

function bis_get_request_location_meta_input() {
    $cookie_value = bis_get_request_location_cookie_data();
    $placeholder = bis_get_location_placeholder();
    $region = '';

    if (!empty($_POST['location_region'])) {
        $posted_value = sanitize_text_field(wp_unslash($_POST['location_region']));
        if ($posted_value !== '') {
            $region = $posted_value;
        }
    }

    if ($region === '' && !empty($cookie_value['label'])) {
        $label = sanitize_text_field((string) $cookie_value['label']);
        if ($label !== '') {
            $region = $label;
        }
    }

    $city = '';
    if (!empty($_POST['location_city'])) {
        $posted_city = sanitize_text_field(wp_unslash($_POST['location_city']));
        if ($posted_city !== '') {
            $city = $posted_city;
        }
    }

    if ($city === '' && !empty($cookie_value['city'])) {
        $saved_city = sanitize_text_field((string) $cookie_value['city']);
        if ($saved_city !== '') {
            $city = $saved_city;
        }
    }

    $source = 'fallback';
    if (!empty($_POST['location_source'])) {
        $posted_source = sanitize_key(wp_unslash($_POST['location_source']));
        if ($posted_source !== '') {
            $source = $posted_source;
        }
    } elseif (!empty($cookie_value['source'])) {
        $saved_source = sanitize_key((string) $cookie_value['source']);
        if ($saved_source !== '') {
            $source = $saved_source;
        }
    }

    return array(
        'bis_location_region' => $region !== '' ? $region : $placeholder,
        'bis_location_city'   => $city !== '' ? $city : $placeholder,
        'bis_location_source' => $source,
    );
}

function bis_request_has_hcaptcha_token() {
    $token = isset($_POST['h-captcha-response']) ? trim((string) wp_unslash($_POST['h-captcha-response'])) : '';

    return '' !== $token;
}

function bis_maybe_verify_hcaptcha_response($required = false) {
    if (!$required && !bis_request_has_hcaptcha_token()) {
        return true;
    }

    return bis_verify_hcaptcha_response();
}

function bis_detect_location() {
    $location = bis_get_location_fallback_data();
    $requested_ip = isset($_POST['ip']) ? sanitize_text_field(wp_unslash($_POST['ip'])) : '';
    $ip_address = bis_is_public_ip_address($requested_ip) ? $requested_ip : bis_get_client_ip_address();

    if ($ip_address !== '') {
        $detected_location = bis_detect_location_by_ip($ip_address);
        if (!is_wp_error($detected_location)) {
            $location = $detected_location;
        }
    }

    wp_send_json_success($location);
}
add_action('wp_ajax_bis_detect_location', 'bis_detect_location');
add_action('wp_ajax_nopriv_bis_detect_location', 'bis_detect_location');

function bis_resolve_location() {
    $query = isset($_POST['query']) ? sanitize_text_field(wp_unslash($_POST['query'])) : '';
    if ($query === '') {
        wp_send_json_error(array('message' => 'Укажите город.'));
    }

    $location = bis_resolve_location_by_query($query);
    if (is_wp_error($location)) {
        wp_send_json_error(array('message' => $location->get_error_message()));
    }

    wp_send_json_success($location);
}
add_action('wp_ajax_bis_resolve_location', 'bis_resolve_location');
add_action('wp_ajax_nopriv_bis_resolve_location', 'bis_resolve_location');

function bis_get_private_request_upload_subdir() {
    return 'bis-private';
}

function bis_private_request_upload_dir($dirs) {
    $subdir = '/' . trim(bis_get_private_request_upload_subdir(), '/');
    $dirs['subdir'] = $subdir;
    $dirs['path'] = $dirs['basedir'] . $subdir;
    $dirs['url'] = $dirs['baseurl'] . $subdir;

    return $dirs;
}

function bis_ensure_private_request_upload_dir() {
    $upload_dir = wp_upload_dir();
    $private_dir = trailingslashit($upload_dir['basedir']) . bis_get_private_request_upload_subdir();

    if (!wp_mkdir_p($private_dir)) {
        return new WP_Error('bis_private_upload_dir', 'Не удалось подготовить директорию для приватных файлов.');
    }

    $index_file = trailingslashit($private_dir) . 'index.html';
    if (!file_exists($index_file)) {
        file_put_contents($index_file, '');
    }

    $htaccess_file = trailingslashit($private_dir) . '.htaccess';
    if (!file_exists($htaccess_file)) {
        file_put_contents($htaccess_file, "Order Allow,Deny\nDeny from all\n");
    }

    $web_config_file = trailingslashit($private_dir) . 'web.config';
    if (!file_exists($web_config_file)) {
        file_put_contents($web_config_file, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration>\n  <system.webServer>\n    <authorization>\n      <remove users=\"*\" roles=\"\" verbs=\"\" />\n      <add accessType=\"Deny\" users=\"*\" />\n    </authorization>\n  </system.webServer>\n</configuration>\n");
    }

    return $private_dir;
}

function bis_handle_private_request_upload($field_name) {
    if (empty($_FILES[$field_name]['name'])) {
        return array(
            'path' => '',
            'relative_path' => '',
            'name' => '',
        );
    }

    $prepared_dir = bis_ensure_private_request_upload_dir();
    if (is_wp_error($prepared_dir)) {
        return $prepared_dir;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';

    add_filter('upload_dir', 'bis_private_request_upload_dir');
    $uploaded = wp_handle_upload($_FILES[$field_name], array('test_form' => false));
    remove_filter('upload_dir', 'bis_private_request_upload_dir');

    if (!empty($uploaded['error'])) {
        return new WP_Error('bis_private_upload', $uploaded['error']);
    }

    $upload_dir = wp_upload_dir();
    $normalized_base_dir = trailingslashit(wp_normalize_path($upload_dir['basedir']));
    $normalized_file_path = isset($uploaded['file']) ? wp_normalize_path((string) $uploaded['file']) : '';
    $relative_path = '';

    if ($normalized_file_path && 0 === strpos($normalized_file_path, $normalized_base_dir)) {
        $relative_path = ltrim(substr($normalized_file_path, strlen($normalized_base_dir)), '/');
    }

    return array(
        'path' => isset($uploaded['file']) ? (string) $uploaded['file'] : '',
        'relative_path' => $relative_path,
        'name' => sanitize_file_name(wp_unslash($_FILES[$field_name]['name'])),
    );
}

function bis_get_request_type_label($request_type) {
    $type_labels = array(
        'consultation' => 'Консультация по проекту',
        'estimate'     => 'Смета и сроки',
        'contact'      => 'Форма контактов',
        'order'        => 'Заявка на услугу',
        'callback'     => 'Обратный звонок',
        'exit_intent'  => 'Лид-магнит при выходе',
    );

    return isset($type_labels[$request_type]) ? $type_labels[$request_type] : 'Заявка с сайта';
}

function bis_normalize_request_notification_recipients($value) {
    if (is_array($value)) {
        $raw_items = $value;
    } else {
        $raw_items = preg_split('/[\r\n,;]+/', (string) $value);
    }

    $emails = array();
    foreach ($raw_items as $item) {
        $email = sanitize_email(trim((string) $item));
        if ($email !== '' && is_email($email)) {
            $emails[] = $email;
        }
    }

    return array_values(array_unique($emails));
}

function bis_get_request_notification_recipients() {
    $stored = get_option('bis_request_notification_emails', array());
    $emails = bis_normalize_request_notification_recipients($stored);

    if (empty($emails)) {
        return array();
    }

    return $emails;
}

function bis_resolve_request_private_file_path($absolute_path = '', $relative_path = '') {
    $candidates = array();

    if ($absolute_path !== '') {
        $candidates[] = wp_normalize_path($absolute_path);
    }

    if ($relative_path !== '') {
        $upload_dir = wp_upload_dir();
        $candidates[] = wp_normalize_path(trailingslashit($upload_dir['basedir']) . ltrim($relative_path, '/\\'));
    }

    foreach (array_unique(array_filter($candidates)) as $candidate) {
        if (file_exists($candidate)) {
            return $candidate;
        }
    }

    return '';
}

function bis_get_request_file_data($post_id) {
    $private_file_path = (string) get_post_meta($post_id, 'bis_private_file_path', true);
    $private_file_relative_path = (string) get_post_meta($post_id, 'bis_private_file_relative_path', true);
    $private_file_name = (string) get_post_meta($post_id, 'bis_private_file_name', true);

    if ($private_file_path && '' === $private_file_relative_path) {
        $normalized_private_path = wp_normalize_path($private_file_path);
        $marker = '/' . trim(bis_get_private_request_upload_subdir(), '/') . '/';
        $marker_position = strpos($normalized_private_path, $marker);

        if (false !== $marker_position) {
            $private_file_relative_path = ltrim(substr($normalized_private_path, $marker_position + 1), '/');
            update_post_meta($post_id, 'bis_private_file_relative_path', $private_file_relative_path);
        }
    }

    $resolved_private_file_path = bis_resolve_request_private_file_path($private_file_path, $private_file_relative_path);

    if ($resolved_private_file_path) {
        if ($resolved_private_file_path !== $private_file_path) {
            update_post_meta($post_id, 'bis_private_file_path', $resolved_private_file_path);
        }

        return array(
            'path' => $resolved_private_file_path,
            'name' => $private_file_name ? $private_file_name : basename($resolved_private_file_path),
            'download_url' => wp_nonce_url(
                admin_url('admin-ajax.php?action=bis_download_request_file&id=' . absint($post_id)),
                'bis_download_request_file_' . absint($post_id),
                'nonce'
            ),
            'is_private' => true,
            'is_missing' => false,
        );
    }

    if ($private_file_name || $private_file_path || $private_file_relative_path) {
        return array(
            'path' => '',
            'name' => $private_file_name ? $private_file_name : basename($private_file_relative_path ? $private_file_relative_path : $private_file_path),
            'download_url' => '',
            'is_private' => true,
            'is_missing' => true,
        );
    }

    $file_id = (int) get_post_meta($post_id, 'bis_file_id', true);
    if ($file_id) {
        $attachment_path = get_attached_file($file_id);
        if ($attachment_path && file_exists($attachment_path)) {
            return array(
                'path' => $attachment_path,
                'name' => basename($attachment_path),
                'download_url' => wp_nonce_url(
                    admin_url('admin-ajax.php?action=bis_download_request_file&id=' . absint($post_id)),
                    'bis_download_request_file_' . absint($post_id),
                    'nonce'
                ),
                'is_private' => false,
                'is_missing' => false,
            );
        }
    }

    return array(
        'path' => '',
        'name' => '',
        'download_url' => '',
        'is_private' => false,
        'is_missing' => false,
    );
}

function bis_render_mail_template($template_name, array $context = array()) {
    $template_path = trailingslashit(get_template_directory()) . 'mail-templates/' . sanitize_file_name($template_name) . '.php';

    if (!file_exists($template_path)) {
        return '';
    }

    ob_start();
    extract($context, EXTR_SKIP);
    include $template_path;

    return (string) ob_get_clean();
}

function bis_get_request_notification_context($post_id) {
    $request_type = get_post_meta($post_id, 'bis_request_type', true);
    $type_label = bis_get_request_type_label($request_type);

    if ('consultation' === $request_type) {
        $type_label = 'Консультация по проекту';
    } elseif ('estimate' === $request_type) {
        $type_label = 'Смета и сроки';
    }

    $name = (string) get_post_meta($post_id, 'bis_name', true);
    $phone = (string) get_post_meta($post_id, 'bis_phone', true);
    $email = (string) get_post_meta($post_id, 'bis_email', true);
    $messenger = bis_normalize_request_messenger(get_post_meta($post_id, 'bis_messenger', true));
    $comment = (string) get_post_meta($post_id, 'bis_comment', true);
    $company = (string) get_post_meta($post_id, 'bis_company', true);
    $position = (string) get_post_meta($post_id, 'bis_position', true);
    $topic = (string) get_post_meta($post_id, 'bis_topic', true);
    $details = (string) get_post_meta($post_id, 'bis_details', true);
    $project = (string) get_post_meta($post_id, 'bis_project_title', true);
    $location = (string) get_post_meta($post_id, 'bis_location_region', true);
    $date = (string) get_post_meta($post_id, 'bis_date', true);
    $file = bis_get_request_file_data($post_id);
    $file_path = $file['path'];

    $detail_rows = array();
    $plain_lines = array(
        'Новая заявка с сайта БИС',
        '',
        'Тип: ' . $type_label,
        'Дата: ' . $date,
    );

    $add_detail = static function ($label, $value, $multiline = false) use (&$detail_rows, &$plain_lines) {
        $value = trim((string) $value);
        if ('' === $value) {
            return;
        }

        $detail_rows[] = array(
            'label' => $label,
            'value' => $value,
            'multiline' => (bool) $multiline,
        );

        $plain_lines[] = $label . ': ' . $value;
    };

    $add_detail('Имя', $name);
    $add_detail('Телефон', $phone);
    $add_detail('Email', $email);
    $add_detail('Предпочтительный контакт', $messenger);
    $add_detail('Проект', $project);
    $add_detail('Город-регион', $location);
    $add_detail('Компания', $company);
    $add_detail('Должность', $position);
    $add_detail('Тема', $topic);
    $add_detail('Комментарий', $comment, true);

    if ('' !== $details && $details !== $comment) {
        $add_detail('Подробности', $details, true);
    }

    if ($file_path && file_exists($file_path)) {
        $add_detail('Вложение', $file['name']);
    }

    return array(
        'post_id' => absint($post_id),
        'type_label' => $type_label,
        'request_owner' => '' !== $name ? $name : 'коллеги',
        'accent_value' => '' !== $phone ? $phone : ('' !== $email ? $email : $type_label),
        'site_name' => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
        'site_url' => home_url('/'),
        'admin_request_url' => admin_url('post.php?post=' . absint($post_id) . '&action=edit'),
        'detail_rows' => $detail_rows,
        'plain_lines' => $plain_lines,
        'footer_note' => 'Письмо сформировано автоматически на основе новой заявки с сайта.',
        'file_path' => $file_path,
    );
}

function bis_send_request_notification($post_id) {
    $recipients = bis_get_request_notification_recipients();
    if (empty($recipients)) {
        return false;
    }

    $context = bis_get_request_notification_context($post_id);
    $subject = sprintf('[%s] Новая заявка: %s', $context['site_name'], $context['type_label']);
    $message = bis_render_mail_template('request-notification', $context);

    if ('' === trim(wp_strip_all_tags($message))) {
        $message = nl2br(esc_html(implode("\n", $context['plain_lines'])));
    }

    $headers = array('Content-Type: text/html; charset=UTF-8');
    $attachments = array();

    if (!empty($context['file_path']) && file_exists($context['file_path'])) {
        $attachments[] = $context['file_path'];
    }

    return wp_mail($recipients, $subject, $message, $headers, $attachments);
}

function bis_submit_general_request() {
    $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
    $service = isset($_POST['service']) ? sanitize_text_field(wp_unslash($_POST['service'])) : '';
    $request_type = isset($_POST['request_type']) ? sanitize_key(wp_unslash($_POST['request_type'])) : 'contact';
    $location_meta = bis_get_request_location_meta_input();

    if (!in_array($request_type, array('contact', 'order', 'callback', 'exit_intent'), true)) {
        $request_type = 'contact';
    }

    $hcaptcha_check = bis_maybe_verify_hcaptcha_response(in_array($request_type, array('contact', 'exit_intent'), true));
    if (is_wp_error($hcaptcha_check)) {
        wp_send_json_error(array('message' => $hcaptcha_check->get_error_message()));
    }

    if ('exit_intent' === $request_type && $name === '') {
        $name = 'Посетитель сайта';
    }

    if (($name === '' && 'exit_intent' !== $request_type) || $phone === '') {
        wp_send_json_error(array('message' => 'Заполните обязательные поля: имя и телефон.'));
    }

    if (!bis_is_valid_request_phone($phone)) {
        wp_send_json_error(array('message' => 'Укажите корректный номер телефона.'));
    }
    if ('contact' === $request_type && $message === '') {
        wp_send_json_error(array('message' => 'Заполните поле сообщения.'));
    }

    $post_title = $name . ' - ' . $phone;
    if ($service !== '') {
        $post_title .= ' - ' . $service;
    }

    $post_id = wp_insert_post(array(
        'post_title' => $post_title,
        'post_type' => 'bis_request',
        'post_status' => 'publish',
        'meta_input' => array_merge($location_meta, array(
            'bis_name' => $name,
            'bis_phone' => $phone,
            'bis_comment' => $message,
            'bis_topic' => $service,
            'bis_request_type' => $request_type,
            'bis_status' => 'new',
            'bis_date' => current_time('mysql'),
        )),
    ));

    if (!$post_id || is_wp_error($post_id)) {
        wp_send_json_error(array('message' => 'Не удалось сохранить заявку.'));
    }

    $mail_sent = bis_send_request_notification($post_id);
    if (!$mail_sent) {
        wp_send_json_error(array('message' => 'Заявка сохранена, но письмо не отправилось. Проверьте SMTP.'));
    }

    wp_send_json_success(array('message' => 'Заявка отправлена.'));
}
add_action('wp_ajax_bis_submit_general_request', 'bis_submit_general_request');
add_action('wp_ajax_nopriv_bis_submit_general_request', 'bis_submit_general_request');

// AJAX Handler for Estimate Submission
function bis_submit_estimate() {
    if (bis_is_request_larger_than_post_max_size()) {
        wp_send_json_error(array('message' => 'Размер данных формы превышает лимит сервера. Уменьшите файл или увеличьте `post_max_size` и `upload_max_filesize`.'));
    }

    $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $raw_email = isset($_POST['email']) ? wp_unslash($_POST['email']) : '';
    $email = sanitize_email($raw_email);
    $messenger = isset($_POST['messenger']) ? bis_normalize_request_messenger(sanitize_text_field(wp_unslash($_POST['messenger']))) : '';
    $comment = isset($_POST['comment']) ? sanitize_textarea_field(wp_unslash($_POST['comment'])) : '';
    $location_meta = bis_get_request_location_meta_input();
    $hcaptcha_check = bis_maybe_verify_hcaptcha_response(true);

    if (is_wp_error($hcaptcha_check)) {
        wp_send_json_error(array('message' => $hcaptcha_check->get_error_message()));
    }

    if ($name === '' || $phone === '' || trim((string) $raw_email) === '') {
        wp_send_json_error(array('message' => 'Заполните обязательные поля: имя, телефон и email.'));
    }

    if (!bis_is_valid_request_phone($phone)) {
        wp_send_json_error(array('message' => 'Укажите корректный номер телефона.'));
    }
    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'Укажите корректный email.'));
    }

    if (!empty($_FILES['project_doc']['name']) && isset($_FILES['project_doc']['error']) && (int) $_FILES['project_doc']['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error(array('message' => bis_get_upload_error_message($_FILES['project_doc']['error'])));
    }

    $post_id = wp_insert_post(array(
        'post_title' => $name . ' - ' . $phone,
        'post_type' => 'bis_request',
        'post_status' => 'publish',
        'meta_input' => array_merge($location_meta, array(
            'bis_name' => $name,
            'bis_phone' => $phone,
            'bis_email' => $email,
            'bis_messenger' => $messenger,
            'bis_comment' => $comment,
            'bis_request_type' => 'estimate',
            'bis_status' => 'new',
            'bis_date' => current_time('mysql'),
        )),
    ));

    if ($post_id) {
        $upload_error = '';
        // Handle File Upload
        if (!empty($_FILES['project_doc']['name'])) {
            $uploaded_file = bis_handle_private_request_upload('project_doc');

            if (is_wp_error($uploaded_file)) {
                $upload_error = $uploaded_file->get_error_message();
            } else {
                update_post_meta($post_id, 'bis_private_file_path', $uploaded_file['path']);
                update_post_meta($post_id, 'bis_private_file_relative_path', $uploaded_file['relative_path']);
                update_post_meta($post_id, 'bis_private_file_name', $uploaded_file['name']);
                delete_post_meta($post_id, 'bis_upload_error');
            }
        }

        if ($upload_error !== '') {
            update_post_meta($post_id, 'bis_upload_error', $upload_error);
        }

        $mail_sent = bis_send_request_notification($post_id);
        if (!$mail_sent) {
            wp_send_json_error(array(
                'message' => 'Заявка сохранена, но письмо не отправилось. Проверьте SMTP.',
                'upload_error' => $upload_error,
            ));
        }

        wp_send_json_success(array('message' => 'Request saved', 'upload_error' => $upload_error));
    } else {
        wp_send_json_error(array('message' => 'Error saving request'));
    }
}
add_action('wp_ajax_bis_submit_estimate', 'bis_submit_estimate');
add_action('wp_ajax_nopriv_bis_submit_estimate', 'bis_submit_estimate');

// AJAX Handler for Project Consultation Form
function bis_submit_project_consultation() {
    $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $company = isset($_POST['company']) ? sanitize_text_field(wp_unslash($_POST['company'])) : '';
    $position = isset($_POST['position']) ? sanitize_text_field(wp_unslash($_POST['position'])) : '';
    $topic = isset($_POST['topic']) ? sanitize_text_field(wp_unslash($_POST['topic'])) : '';
    $details = isset($_POST['details']) ? sanitize_textarea_field(wp_unslash($_POST['details'])) : '';
    $project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;
    $privacy = isset($_POST['privacy']) ? '1' : '0';
    $marketing = isset($_POST['marketing']) ? '1' : '0';
    $location_meta = bis_get_request_location_meta_input();
    $hcaptcha_check = bis_maybe_verify_hcaptcha_response(true);

    if (is_wp_error($hcaptcha_check)) {
        wp_send_json_error(array('message' => $hcaptcha_check->get_error_message()));
    }

    if (empty($name) || empty($phone) || empty($email)) {
        wp_send_json_error(array('message' => 'Required fields missing'));
    }

    if (!bis_is_valid_request_phone($phone)) {
        wp_send_json_error(array('message' => 'Укажите корректный номер телефона.'));
    }
    $project_title = $project_id ? get_the_title($project_id) : '';
    $title_suffix = $project_title ? ' - ' . $project_title : '';

    $post_id = wp_insert_post(array(
        'post_title' => $name . $title_suffix,
        'post_type' => 'bis_request',
        'post_status' => 'publish',
        'meta_input' => array_merge($location_meta, array(
            'bis_name' => $name,
            'bis_phone' => $phone,
            'bis_email' => $email,
            'bis_company' => $company,
            'bis_position' => $position,
            'bis_topic' => $topic,
            'bis_details' => $details,
            'bis_project_id' => $project_id,
            'bis_project_title' => $project_title,
            'bis_request_type' => 'consultation',
            'bis_comment' => $details,
            'bis_privacy' => $privacy,
            'bis_marketing' => $marketing,
            'bis_status' => 'new',
            'bis_date' => current_time('mysql'),
        )),
    ));

    if ($post_id) {
        bis_send_request_notification($post_id);
        wp_send_json_success(array('message' => 'Request saved'));
    }

    wp_send_json_error(array('message' => 'Error saving request'));
}
add_action('wp_ajax_bis_submit_project_consultation', 'bis_submit_project_consultation');
add_action('wp_ajax_nopriv_bis_submit_project_consultation', 'bis_submit_project_consultation');

function bis_get_new_requests_count() {
    $args = array(
        'post_type' => 'bis_request',
        'post_status' => 'publish',
        'meta_key' => 'bis_status',
        'meta_value' => 'new',
        'posts_per_page' => -1,
        'fields' => 'ids',
    );
    $query = new WP_Query($args);
    return $query->found_posts;
}

// Admin Page for Requests
function bis_requests_menu() {
    $count = bis_get_new_requests_count();
    $menu_title = 'Заявки';
    
    if ($count > 0) {
        $menu_title .= sprintf(
            ' <span class="awaiting-mod count-%1$d"><span class="pending-count" aria-hidden="true">%1$d</span></span>',
            $count
        );
    }

    add_menu_page(
        'Заявки',
        $menu_title,
        'manage_options',
        'bis-requests',
        'bis_requests_page',
        'dashicons-email',
        6
    );
}
add_action('admin_menu', 'bis_requests_menu');

function bis_requests_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['bis_request_notification_save']) && check_admin_referer('bis_request_notification_nonce')) {
        $raw_emails = isset($_POST['bis_request_notification_emails']) ? wp_unslash($_POST['bis_request_notification_emails']) : '';
        $emails = bis_normalize_request_notification_recipients($raw_emails);
        update_option('bis_request_notification_emails', $emails);
        echo '<div class="updated"><p>Получатели уведомлений по заявкам сохранены.</p></div>';
    }

    $notification_emails = implode("\n", bis_get_request_notification_recipients());
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Заявки</h1>
        <form class="bis-requests-settings" method="post">
            <?php wp_nonce_field('bis_request_notification_nonce'); ?>
            <h2>Получатели email-уведомлений</h2>
            <p class="description">Укажите email-адреса, на которые нужно отправлять копии новых заявок. По одному адресу на строку.</p>
            <textarea name="bis_request_notification_emails" rows="4" class="large-text code"><?php echo esc_textarea($notification_emails); ?></textarea>
            <p class="submit">
                <button type="submit" name="bis_request_notification_save" class="button button-primary">Сохранить получателей</button>
            </p>
        </form>
        <div id="bis-requests-app">
            <div class="bis-requests-overview" id="bis-requests-overview"></div>
            <div class="bis-requests-toolbar" id="bis-requests-toolbar"></div>
            <div class="bis-requests-list" id="bis-requests-list">
                <!-- Requests will be loaded here via JS -->
                <div class="bis-loading">Загрузка...</div>
            </div>
        </div>
    </div>
    <?php
}

// AJAX Handler to Get Requests
function bis_get_requests() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error();
    }

    check_ajax_referer('bis_requests_admin', 'nonce');

    $args = array(
        'post_type' => 'bis_request',
        'posts_per_page' => 50,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    );

    $query = new WP_Query($args);
    $requests = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $file = bis_get_request_file_data(get_the_ID());
            $file_url = $file['download_url'];
            $file_name = $file['name'];
            $upload_error = (string) get_post_meta(get_the_ID(), 'bis_upload_error', true);
            $timestamp = strtotime((string) get_post_meta(get_the_ID(), 'bis_date', true));

            $comment = get_post_meta(get_the_ID(), 'bis_comment', true);
            if (!$comment) {
                $comment = get_post_meta(get_the_ID(), 'bis_details', true);
            }

            $requests[] = array(
                'id' => get_the_ID(),
                'name' => get_post_meta(get_the_ID(), 'bis_name', true),
                'phone' => get_post_meta(get_the_ID(), 'bis_phone', true),
                'email' => get_post_meta(get_the_ID(), 'bis_email', true),
                'messenger' => bis_normalize_request_messenger(get_post_meta(get_the_ID(), 'bis_messenger', true)),
                'comment' => $comment,
                'company' => get_post_meta(get_the_ID(), 'bis_company', true),
                'position' => get_post_meta(get_the_ID(), 'bis_position', true),
                'topic' => get_post_meta(get_the_ID(), 'bis_topic', true),
                'details' => get_post_meta(get_the_ID(), 'bis_details', true),
                'project' => get_post_meta(get_the_ID(), 'bis_project_title', true),
                'location' => get_post_meta(get_the_ID(), 'bis_location_region', true),
                'type' => get_post_meta(get_the_ID(), 'bis_request_type', true),
                'file_url' => $file_url,
                'file_name' => $file_name,
                'file_error' => $upload_error,
                'has_file' => !empty($file_name),
                'file_missing' => !empty($file['is_missing']),
                'can_download_file' => !empty($file_url),
                'status' => get_post_meta(get_the_ID(), 'bis_status', true),
                'date' => get_post_meta(get_the_ID(), 'bis_date', true),
                'date_label' => $timestamp ? wp_date('d.m.Y H:i', $timestamp) : '',
                'time_ago' => human_time_diff(strtotime(get_post_meta(get_the_ID(), 'bis_date', true)), current_time('timestamp')) . ' назад',
            );
        }
        wp_reset_postdata();
    }

    wp_send_json_success($requests);
}
add_action('wp_ajax_bis_get_requests', 'bis_get_requests');

// AJAX Handler to Mark Request as Read
function bis_mark_read() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error();
    }

    check_ajax_referer('bis_requests_admin', 'nonce');

    $post_id = intval($_POST['id']);
    if (!$post_id) {
        wp_send_json_error();
    }

    update_post_meta($post_id, 'bis_status', 'read');
    wp_send_json_success(array('count' => bis_get_new_requests_count()));
}
add_action('wp_ajax_bis_mark_read', 'bis_mark_read');

function bis_delete_request() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error();
    }

    check_ajax_referer('bis_requests_admin', 'nonce');

    $post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if (!$post_id || 'bis_request' !== get_post_type($post_id)) {
        wp_send_json_error();
    }

    $private_file_path = (string) get_post_meta($post_id, 'bis_private_file_path', true);
    $private_file_relative_path = (string) get_post_meta($post_id, 'bis_private_file_relative_path', true);
    $resolved_private_file_path = bis_resolve_request_private_file_path($private_file_path, $private_file_relative_path);
    if ($resolved_private_file_path) {
        wp_delete_file($resolved_private_file_path);
    }

    $file_id = (int) get_post_meta($post_id, 'bis_file_id', true);
    if ($file_id) {
        wp_delete_attachment($file_id, true);
    }

    $deleted = wp_delete_post($post_id, true);
    if (!$deleted) {
        wp_send_json_error();
    }

    wp_send_json_success(array('count' => bis_get_new_requests_count()));
}
add_action('wp_ajax_bis_delete_request', 'bis_delete_request');

function bis_download_request_file() {
    if (!current_user_can('manage_options')) {
        wp_die('Forbidden', 403);
    }

    $post_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
    if (!$post_id || 'bis_request' !== get_post_type($post_id)) {
        wp_die('File not found', 404);
    }

    check_admin_referer('bis_download_request_file_' . $post_id, 'nonce');

    $file = bis_get_request_file_data($post_id);
    $file_path = $file['path'];
    $file_name = $file['name'];

    if (!$file_path || !file_exists($file_path)) {
        wp_die('File not found', 404);
    }

    if (ob_get_length()) {
        ob_end_clean();
    }

    nocache_headers();
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    $download_name = sanitize_file_name($file_name);
    header('Content-Disposition: attachment; filename="' . $download_name . '"; filename*=UTF-8\'\'' . rawurlencode($file_name));
    header('Content-Length: ' . filesize($file_path));
    header('X-Content-Type-Options: nosniff');

    readfile($file_path);
    exit;
}
add_action('wp_ajax_bis_download_request_file', 'bis_download_request_file');

// Enqueue Admin Scripts for Requests Page
function bis_requests_admin_scripts($hook) {
    if ('toplevel_page_bis-requests' !== $hook) {
        return;
    }
    $script_version = filemtime(get_template_directory() . '/assets/js/admin-requests.js');
    $style_version = filemtime(get_template_directory() . '/assets/css/admin-requests.css');

    wp_enqueue_script('bis-requests-script', get_template_directory_uri() . '/assets/js/admin-requests.js', array('jquery'), $script_version, true);
    wp_enqueue_style('bis-requests-style', get_template_directory_uri() . '/assets/css/admin-requests.css', array(), $style_version);
    wp_localize_script('bis-requests-script', 'bisRequestsData', array(
        'nonce' => wp_create_nonce('bis_requests_admin'),
        'icons' => array(
            'telegram' => get_template_directory_uri() . '/assets/img/telegram-32x32.png',
            'max' => get_template_directory_uri() . '/assets/img/MAX-32x32.png',
            'whatsapp' => get_template_directory_uri() . '/assets/img/free-icon-whatsapp-5968841.png',
        ),
        'strings' => array(
            'empty' => 'Нет заявок',
            'delete_confirm' => 'Удалить заявку без возможности восстановления?',
            'delete_error' => 'Не удалось удалить заявку. Попробуйте ещё раз.',
        ),
    ));
}
add_action('admin_enqueue_scripts', 'bis_requests_admin_scripts');

