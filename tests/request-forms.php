<?php
function add_action(...$a) {} function add_filter(...$a) {}
function get_option($k,$d='') { return $k==='bis_request_notification_emails' ? array('test@example.invalid') : $d; }
function sanitize_text_field($v) { return trim(strip_tags($v)); }
function sanitize_textarea_field($v) { return trim(strip_tags($v)); }
function sanitize_key($v) { return preg_replace('/[^a-z0-9_-]/','',strtolower($v)); }
function sanitize_email($v) { return trim($v); }
function sanitize_file_name($v) { return basename($v); }
function wp_unslash($v) { return $v; }
function is_email($v) { return filter_var($v,FILTER_VALIDATE_EMAIL)!==false; }
function is_wp_error($v) { return false; }
function wp_insert_post($p) { $GLOBALS['request']=$p; return 123; }
function get_post_meta($id,$key,$single) { return $GLOBALS['request']['meta_input'][$key] ?? ''; }
function wp_mail(...$a) { $GLOBALS['mail']=$a; return true; }
function wp_specialchars_decode($v,$f) { return html_entity_decode($v,$f); }
function get_bloginfo($key) { return 'БИС'; }
function home_url($v) { return 'https://example.invalid'.$v; }
function admin_url($v) { return 'https://example.invalid/wp-admin/'.$v; }
function absint($v) { return abs((int)$v); }
function get_template_directory() { return '/private/tmp/bis-mail-fixture-none'; }
function trailingslashit($v) { return rtrim($v,'/').'/'; }
function wp_strip_all_tags($v) { return strip_tags($v); }
function esc_html($v) { return htmlspecialchars($v); }
function current_time($v) { return '2026-10-14 12:00:00'; }
function get_the_title($id) { return 'Проект'; }
class JsonResponse extends Exception { public $success; public $data; function __construct($s,$d) { $this->success=$s;$this->data=$d; } }
function wp_send_json_error($d) { throw new JsonResponse(false,$d); }
function wp_send_json_success($d) { throw new JsonResponse(true,$d); }
function bis_captcha_enabled() { return false; }
require __DIR__.'/../wp-theme/inc/request-handlers.php';
function submit($handler,$data) { $_POST=$data; unset($GLOBALS['request'],$GLOBALS['mail']);try {$handler();}catch(JsonResponse $e){return $e;}throw new Exception('No response'); }
$r=submit('bis_submit_general_request',array('request_type'=>'callback','name'=>'Проверка','phone'=>'+7 (926) 438-07-70'));
if (!$r->success || $GLOBALS['request']['post_type']!=='bis_request' || $GLOBALS['request']['meta_input']['bis_request_type']!=='callback' || count($GLOBALS['mail'])!==5) throw new Exception('Callback must save and notify');
foreach (array('','7926438077','792643807700') as $phone) {
 $r=submit('bis_submit_general_request',array('request_type'=>'callback','name'=>'Проверка','phone'=>$phone));
 if ($r->success || isset($GLOBALS['request']) || isset($GLOBALS['mail'])) throw new Exception('Invalid phone must not save or notify');
}
$r=submit('bis_submit_general_request',array('request_type'=>'callback','name'=>'','phone'=>'79264380770'));
if ($r->success || isset($GLOBALS['request'])) throw new Exception('Missing name accepted');
$r=submit('bis_submit_project_consultation',array('name'=>'Проверка','phone'=>'79264380770','email'=>'test@example.invalid','privacy'=>'1'));
if (!$r->success || $GLOBALS['request']['meta_input']['bis_company']!=='' || $GLOBALS['request']['meta_input']['bis_position']!=='' || $GLOBALS['request']['meta_input']['bis_topic']!=='') throw new Exception('Optional project fields rejected');
echo "Request form validation, persistence and mail tests passed\n";
