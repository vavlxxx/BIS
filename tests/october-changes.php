<?php
function add_action(...$args) {}
function add_filter(...$args) {}
function get_option($key,$default='') { return $GLOBALS['options'][$key] ?? $default; }
function get_post_meta($id,$key,$single=true) { return $GLOBALS['meta'][$id][$key] ?? ''; }
function get_post_type($id) { return $GLOBALS['types'][$id] ?? 'bis_service'; }
function get_the_terms($id,$tax) { return $GLOBALS['terms'][$id][$tax] ?? false; }
function is_wp_error($v) { return $v instanceof WP_Error; }
function sanitize_text_field($v) { return trim(strip_tags($v)); }
function wp_strip_all_tags($v) { return strip_tags($v); }
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
echo "Client settings tests passed\n";
