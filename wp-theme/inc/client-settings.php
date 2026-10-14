<?php
/** Client-managed form and keyword settings. */
function bis_captcha_enabled() {
    return get_option('bis_captcha_enabled', '0') === '1';
}
function bis_render_captcha() {
    return bis_captcha_enabled() ? do_shortcode('[hcaptcha auto="true" force="true"]') : '';
}
function bis_split_keywords($value) {
    $parts = preg_split('/[,;\r\n]+/u', wp_strip_all_tags((string) $value));
    return array_values(array_unique(array_filter(array_map('trim', $parts))));
}
function bis_get_post_keywords($id) {
    $manual = bis_split_keywords(get_post_meta($id, 'bis_seo_keywords', true));
    if ($manual) return $manual;
    $tax = array('bis_service'=>'bis_service_tag', 'popular_page'=>'popular_tag', 'bis_project'=>'bis_project_service', 'bis_news'=>'bis_news_tag')[get_post_type($id)] ?? '';
    $terms = $tax ? get_the_terms($id, $tax) : false;
    return is_array($terms) ? array_values(array_unique(array_map(function($t) { return $t->name; }, $terms))) : array();
}
function bis_render_keywords($id) {
    $words = bis_get_post_keywords($id);
    if (!$words) return;
    echo '<section class="service-tags-section"><div class="service-tags mw-1400px" aria-label="Ключевые слова">';
    foreach ($words as $word) echo '<span class="service-tag">'.esc_html($word).'</span>';
    echo '</div></section>';
}
function bis_privacy_url() {
    $url = get_privacy_policy_url();
    return $url ?: home_url('/privacy-policy/');
}
function bis_render_form_consent() {
    echo '<p class="form-consent">Нажимая на кнопку, вы даете согласие на обработку своих персональных данных и соглашаетесь с <a href="'.esc_url(bis_privacy_url()).'" target="_blank" rel="noopener">Политикой конфиденциальности сайта</a></p>';
}
function bis_client_general_settings() {
    register_setting('general', 'bis_home_keywords', array('sanitize_callback'=>'sanitize_textarea_field'));
    register_setting('general', 'bis_captcha_enabled', array('sanitize_callback'=>function($v) { return $v === '1' ? '1' : '0'; }));
    add_settings_field('bis_home_keywords', 'Ключевые слова главной БИС', function() {
        echo '<textarea class="large-text" rows="3" name="bis_home_keywords">'.esc_textarea(get_option('bis_home_keywords','')).'</textarea><p class="description">Только для meta keywords главной страницы. Разделяйте запятыми.</p>';
    }, 'general');
    add_settings_field('bis_captcha_enabled', 'Капча в формах БИС', function() {
        echo '<input type="hidden" name="bis_captcha_enabled" value="0"><label><input type="checkbox" name="bis_captcha_enabled" value="1" '.checked(bis_captcha_enabled(),true,false).'> Включить hCaptcha во всех формах</label><p class="description">Ключи hCaptcha сохраняются в прежних настройках. По умолчанию проверка отключена.</p>';
    }, 'general');
}
add_action('admin_init','bis_client_general_settings');
function bis_output_keywords_meta() {
    $words = is_front_page() ? bis_split_keywords(get_option('bis_home_keywords','')) : (is_singular() ? bis_get_post_keywords(get_queried_object_id()) : array());
    if ($words) echo '<meta name="keywords" content="'.esc_attr(implode(', ', $words)).'">' . "\n";
}
add_action('wp_head','bis_output_keywords_meta',2);
add_filter('the_generator','__return_empty_string');
function bis_remove_generator() { remove_action('wp_head','wp_generator'); }
add_action('init','bis_remove_generator');
function bis_service_message_box() {
    add_meta_box('bis_service_message','Выдвижное предложение','bis_service_message_fields','bis_service','normal','default');
}
add_action('add_meta_boxes','bis_service_message_box');
function bis_service_message_fields($post) {
    wp_nonce_field('bis_service_message','bis_service_message_nonce');
    echo '<p>Пустое поле отключает окно. Показ запоминается на сессию отдельно для каждой услуги.</p><textarea class="widefat" rows="5" name="bis_service_message">'.esc_textarea(get_post_meta($post->ID,'bis_service_message',true)).'</textarea>';
}
function bis_save_service_message($id) {
    if (wp_is_post_revision($id) || wp_is_post_autosave($id) || !current_user_can('edit_post',$id) || !isset($_POST['bis_service_message_nonce']) || !wp_verify_nonce($_POST['bis_service_message_nonce'],'bis_service_message')) return;
    update_post_meta($id,'bis_service_message',sanitize_textarea_field(wp_unslash($_POST['bis_service_message'] ?? '')));
}
add_action('save_post_bis_service','bis_save_service_message');
function bis_render_service_message() {
    if (!is_singular('bis_service')) return;
    $id=get_queried_object_id(); $message=trim((string)get_post_meta($id,'bis_service_message',true));
    if ($message==='') return;
    echo '<aside class="bis-service-message" data-service-message="'.esc_attr($id).'" hidden aria-label="Предложение по услуге"><button type="button" data-service-message-close aria-label="Закрыть предложение">×</button><p>'.nl2br(esc_html($message)).'</p><button class="btn btn-primary callback-btn" type="button">Оставить заявку</button></aside>';
}
