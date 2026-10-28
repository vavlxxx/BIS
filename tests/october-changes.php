<?php
function add_action(...$args) {}
function add_filter(...$args) {}
function get_option($key,$default='') { return $GLOBALS['options'][$key] ?? $default; }
function get_post_meta($id,$key,$single=true) { return $GLOBALS['meta'][$id][$key] ?? ''; }
function update_post_meta($id,$key,$val) { $GLOBALS['meta'][$id][$key] = $val; }
function delete_post_meta($id,$key) { unset($GLOBALS['meta'][$id][$key]); }
function get_post_type($id) { return $GLOBALS['types'][$id] ?? 'bis_service'; }
function get_the_terms($id,$tax) { return $GLOBALS['terms'][$id][$tax] ?? false; }
function is_wp_error($v) { return $v instanceof WP_Error; }
function sanitize_text_field($v) { return trim(strip_tags($v)); }
function sanitize_textarea_field($v) { return trim(strip_tags($v)); }
function wp_strip_all_tags($v) { return strip_tags($v); }
function wp_unslash($v) { return $v; }
function wp_get_post_parent_id($id) { return $GLOBALS['parents'][$id] ?? 0; }
function is_singular($type = '') { return ($GLOBALS['is_singular'] ?? false) && (!$type || ($GLOBALS['singular_type'] ?? '') === $type); }
function get_queried_object_id() { return $GLOBALS['queried_object_id'] ?? 0; }
function esc_url($v) { return htmlspecialchars((string) $v, ENT_QUOTES); }
function esc_attr($v) { return htmlspecialchars((string) $v, ENT_QUOTES); }
function esc_html($v) { return htmlspecialchars((string) $v, ENT_QUOTES); }
function esc_textarea($v) { return htmlspecialchars((string) $v, ENT_QUOTES); }
function get_template_directory_uri() { return 'https://example.com/wp-content/themes/bis'; }
function current_user_can($cap, $id) { return true; }
function wp_is_post_revision($id) { return false; }
function wp_is_post_autosave($id) { return false; }
function wp_verify_nonce($nonce, $action) { return true; }
function get_posts($args) {
    $parent = (int) ($args['post_parent'] ?? 0);
    $res = [];
    foreach (($GLOBALS['parents'] ?? []) as $cid => $pid) {
        if ($pid === $parent) $res[] = $cid;
    }
    return $res;
}
class WP_Error {}
require __DIR__.'/../wp-theme/inc/client-settings.php';
function check($condition,$message) { if (!$condition) throw new RuntimeException($message); }
$GLOBALS['options']=[];
check(!bis_captcha_enabled(),'Captcha disabled by default');
$GLOBALS['options']['bis_captcha_enabled']='1';
check(bis_captcha_enabled(),'Captcha can be restored');
$GLOBALS['meta'][12]['bis_seo_keywords']='  вентиляция, наладка, вентиляция ';
check(bis_get_post_keywords(12)===['вентиляция','наладка'],'Manual keywords take precedence and deduplicate');
$GLOBALS['meta'][12]['bis_seo_keywords']='';
$GLOBALS['terms'][12]['bis_service_tag']=[(object)['name'=>'отопление'],(object)['name'=>'вентиляция']];
check(bis_get_post_keywords(12)===['отопление','вентиляция'],'Tag fallback');
check(bis_get_post_keywords(55)===[],'No invented keywords');

// Service message scoping tests
$parent_id = 100;
$child_id = 101;
$unrelated_id = 102;
$GLOBALS['parents'] = [$child_id => $parent_id];
$GLOBALS['types'] = [$parent_id => 'bis_service', $child_id => 'bis_service', $unrelated_id => 'bis_service'];

// 1. Parent has message, child has none
$GLOBALS['meta'][$parent_id]['bis_service_message'] = 'Предложение для ПНР';
unset($GLOBALS['meta'][$child_id]['bis_service_message']);
check(bis_get_service_message($parent_id) === 'Предложение для ПНР', 'Parent returns configured message');
check(bis_get_service_message($child_id) === '', 'Child returns empty when it has no message');

// 2. Child erroneously has the exact same message as parent (cascaded by previous bug)
$GLOBALS['meta'][$child_id]['bis_service_message'] = 'Предложение для ПНР';
check(bis_get_service_message($child_id) === '', 'Child suppresses inherited parent message');

// 3. Child has its own distinct message
$GLOBALS['meta'][$child_id]['bis_service_message'] = 'Предложение для Общеобменной вентиляции';
check(bis_get_service_message($child_id) === 'Предложение для Общеобменной вентиляции', 'Child returns its own distinct message');

// 4. Save isolation: saving parent ignores child post_ID
$_POST['post_ID'] = (string) $parent_id;
$_POST['bis_service_message_nonce'] = 'dummy';
$_POST['bis_service_message'] = 'Обновленное предложение';
// Simulating WordPress firing save_post_bis_service for child during wp_update_post
bis_save_service_message($child_id);
check($GLOBALS['meta'][$child_id]['bis_service_message'] === 'Предложение для Общеобменной вентиляции', 'Child was NOT overwritten during parent save');

// 5. Saving parent cleans up child if child still had old parent message
$GLOBALS['meta'][$child_id]['bis_service_message'] = 'Обновленное предложение';
bis_save_service_message($parent_id);
check($GLOBALS['meta'][$parent_id]['bis_service_message'] === 'Обновленное предложение', 'Parent updated');
check(!isset($GLOBALS['meta'][$child_id]['bis_service_message']), 'Child with identical cloned message was cleaned up');

// 6. Rendering test: buttons Telegram and Max are rendered
$GLOBALS['is_singular'] = true;
$GLOBALS['singular_type'] = 'bis_service';
$GLOBALS['queried_object_id'] = $parent_id;
ob_start();
bis_render_service_message();
$output = ob_get_clean();

check(str_contains($output, 'class="bis-service-message"'), 'Modal aside rendered');
check(str_contains($output, 'class="bis-service-message__actions"'), 'Actions wrapper rendered');
check(str_contains($output, 'callback-btn'), 'Callback button rendered');
check(str_contains($output, 'bis-service-message__social--telegram'), 'Telegram button rendered');
check(str_contains($output, 'https://t.me/+79264380770'), 'Telegram link rendered');
check(str_contains($output, 'telegram-white-32x32.png'), 'Telegram icon rendered');
check(str_contains($output, 'bis-service-message__social--max'), 'Max button rendered');
check(str_contains($output, 'https://max.ru/u/f9LHodD0cOIYdHZd-s9_nqTN9t76kGjdQxmIoxXSFGhqRnW3d4TLAMEFfVs'), 'Max link rendered');
check(str_contains($output, 'MAX-white-32x32.png'), 'Max icon rendered');

// 7. Child rendering test: child does not render modal when it has no message
$GLOBALS['queried_object_id'] = $child_id;
ob_start();
bis_render_service_message();
$child_output = ob_get_clean();
check($child_output === '', 'Child without its own message renders nothing');

// 8. Breadcrumbs on resource pages (gallery, faq, generic page)
$gallery_code = file_get_contents(__DIR__ . '/../wp-theme/page-gallery.php');
check(str_contains($gallery_code, '<section class="breadcrumbs-section">'), 'Gallery has breadcrumbs section');
check(str_contains($gallery_code, 'class="project-breadcrumbs mw-1400px"'), 'Gallery has standard project breadcrumbs markup');
check(str_contains($gallery_code, 'home_url(\'/\')'), 'Gallery breadcrumb links to home');

$faq_code = file_get_contents(__DIR__ . '/../wp-theme/page-faq.php');
check(str_contains($faq_code, '<section class="breadcrumbs-section">'), 'FAQ has breadcrumbs section');
check(str_contains($faq_code, 'class="project-breadcrumbs mw-1400px"'), 'FAQ has standard project breadcrumbs markup');
check(str_contains($faq_code, 'home_url(\'/\')'), 'FAQ breadcrumb links to home');

$page_code = file_get_contents(__DIR__ . '/../wp-theme/page.php');
check(str_contains($page_code, '<section class="breadcrumbs-section">'), 'Generic page has breadcrumbs section');
check(str_contains($page_code, 'class="project-breadcrumbs mw-1400px"'), 'Generic page has standard project breadcrumbs markup');

$content_css = file_get_contents(__DIR__ . '/../wp-theme/assets/css/content.css');
check(str_contains($content_css, '.bis-resource-page .breadcrumbs-section'), 'CSS styles breadcrumbs inside resource pages without padding conflict');
check(str_contains($content_css, '.team-slider > .team-slider__controls') && str_contains($content_css, 'justify-content: space-between'), 'Team navigation buttons are separated to both sides on mobile');

$front_css = file_get_contents(__DIR__ . '/../wp-theme/assets/css/front-page.css');
check(str_contains($front_css, '.objects-slider-nav .slider-prev') && str_contains($front_css, 'border-radius: 50%'), 'Objects slider navigation buttons are circular');
check(str_contains($front_css, '.objects-slider-dots') && str_contains($front_css, 'border-radius: 999px'), 'Objects slider dots have rounded capsule container');
check(str_contains($front_css, '.objects-slider-dots .slider-dot') && str_contains($front_css, 'border-radius: 50%'), 'Objects slider dots are circular');
check(str_contains($front_css, 'left: -21px;') && str_contains($front_css, 'right: -21px;'), 'Desktop slider arrows are centered on block borders');

$base_css = file_get_contents(__DIR__ . '/../wp-theme/assets/css/base.css');
check(str_contains($base_css, 'min-height: 100dvh;'), 'Hero has dynamic viewport height support');
check(str_contains($base_css, '@media (max-width: 900px)') && str_contains($base_css, 'flex-direction: column;'), 'Hero switches to vertical flex column on mobile/tablet');
check(!preg_match('/^\s*bottom:\s*18px;/m', $base_css), 'Hero navigation rail is no longer absolutely pinned to bottom on mobile');

$about_php = file_get_contents(__DIR__ . '/../wp-theme/page-about.php');
check(str_contains($about_php, '<span class="about-econ-card__num">1 год</span>'), 'About page displays payback period 1 year');
check(str_contains($about_php, 'Срок окупаемости ПНР — 1 год. Дальше — чистая экономия каждый год всего срока службы здания.'), 'About page description updated with 1 year payback');
check(!str_contains($about_php, '1 500 руб/м²'), 'Cost per square meter removed from about page economics');

check(str_contains($front_css, '#objects-yandex-map [class*="-ad-"]') && str_contains($front_css, 'display: none !important;'), 'CSS suppresses Yandex Maps ad banners');

$map_js = file_get_contents(__DIR__ . '/../wp-theme/assets/js/objects-map.js');
check(str_contains($map_js, 'removeAds') && str_contains($map_js, 'MutationObserver'), 'Map JS includes ad suppressor with MutationObserver');

$functions_php = file_get_contents(__DIR__ . '/../wp-theme/functions.php');
check(str_contains($functions_php, 'BIS_YANDEX_MAPS_API_KEY') && str_contains($functions_php, '&apikey='), 'Functions supports custom Yandex Maps API key');

echo "Client settings tests passed\n";
