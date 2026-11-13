<?php
$options = array();
function get_option($key, $default = false) { global $options; return $options[$key] ?? $default; }
function sanitize_text_field($value) { return trim(strip_tags($value)); }
function sanitize_textarea_field($value) { return trim(strip_tags($value)); }
function esc_url_raw($value) { return preg_match('~^javascript:~i', $value) ? '' : trim($value); }
require __DIR__ . '/../wp-theme/inc/hero-slider.php';
function check($value, $message) { if (!$value) throw new RuntimeException($message); }
check(bis_get_hero_slides() === array(), 'No configuration preserves original homepage');
$options['bis_hero_slider_images'] = array('https://example.com/a.jpg', 'https://example.com/b.jpg');
$legacy = bis_get_hero_slides();
check(count($legacy) === 2 && $legacy[1]['image'] === 'https://example.com/b.jpg', 'Legacy images retain order');
check($legacy[0]['title'] === bis_hero_default_slide()['title'], 'Legacy images inherit current content');
$options['bis_hero_slides'] = array(array('title'=>'<b>New</b>','text'=>'Description','button'=>'Contact','url'=>'javascript:alert(1)'), array('title'=>''), false);
$slides = bis_get_hero_slides();
check(count($slides) === 1 && $slides[0]['title'] === 'New', 'Empty/malformed slides ignored and text sanitized');
check($slides[0]['url'] === '', 'Unsafe links removed');
check(count(bis_hero_normalize_slides(array_fill(0, 20, bis_hero_default_slide()))) === 12, 'Slide count capped');
$options['bis_hero_slides'] = array();
check(bis_get_hero_slides() === array(), 'Cleared new configuration does not revive legacy images');
$options['bis_hero_slides'] = array(
    array('title'=>'', 'text'=>'Description without title', 'hidden'=>'1', 'new_tab'=>'1'),
    array('title'=>'', 'image'=>'https://example.com/no-title.jpg'),
);
check(count(bis_get_hero_slides()) === 1, 'Hidden slide excluded from frontend');
$admin_slides = bis_get_hero_slides(true);
check(count($admin_slides) === 2 && $admin_slides[0]['hidden'] && $admin_slides[0]['new_tab'], 'Hidden and target settings retained for admin');
check(bis_get_hero_slides()[0]['title'] === '', 'Image-only slide retains optional title');
$options['bis_hero_interval'] = 12;
check(bis_hero_interval_seconds() === 12 && bis_hero_interval_seconds(1) === 3 && bis_hero_interval_seconds(100) === 60, 'Configured interval has safe bounds');
echo "Hero slider tests passed\n";
