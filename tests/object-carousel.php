<?php

// Render the actual homepage section and verify every referenced asset.
function get_template_directory_uri() { return '/theme'; }
function esc_url($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_html($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_attr($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function check($condition, $message) {
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$theme = __DIR__ . '/../wp-theme';
$source = file_get_contents($theme . '/front-page.php');
$start = strpos($source, '<!-- Objects Section -->');
$end = strpos($source, '<section class="services"', $start);
ob_start();
eval('?>' . substr($source, $start, $end - $start));
$html = ob_get_clean();

$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
$xpath = new DOMXPath($dom);
$cards = $xpath->query('//article[contains(@class, "object-card")]');
check($cards->length === 12, 'All 12 object types must render.');
check($xpath->query('//h2[@id="objects-title"]')->item(0)->textContent === 'Объекты, на которых мы работаем', 'Keep the original heading.');
check($xpath->query('//*[@id="objects-track"]')->length === 1, 'The carousel track needs a unique ID.');

$hashes = array();
foreach ($cards as $card) {
    check($xpath->query('.//h3', $card)->length === 1, 'Each card has a title.');
    $image = $xpath->query('.//img', $card)->item(0);
    check($image !== null, 'Each card has a photograph.');
    check($image->getAttribute('loading') === 'lazy', 'Images outside the viewport should load lazily.');
    $file = $theme . substr($image->getAttribute('src'), strlen('/theme'));
    check(is_file($file), 'Missing image: ' . $file);
    $size = getimagesize($file);
    check($size[0] === 960 && $size[1] === 540 && $size['mime'] === 'image/webp', 'Main image must be 960x540 WebP.');
    $small = str_replace('.webp', '-480.webp', $file);
    check(is_file($small), 'Missing small image: ' . $small);
    $small_size = getimagesize($small);
    check($small_size[0] === 480 && $small_size[1] === 270, 'Small image must be 480x270.');
    check(strpos($image->getAttribute('srcset'), basename($small)) !== false, 'Responsive image must reference its small variant.');
    $hashes[] = hash_file('sha256', $file);
}
check(count(array_unique($hashes)) === 12, 'Every type should have its own image.');
echo "Object carousel content and asset checks passed.\n";
