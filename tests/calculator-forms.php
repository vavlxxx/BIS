<?php

// Render the actual WordPress template with only the page data it consumes.
function is_user_logged_in() { return true; }
function get_header() {}
function get_footer() {}
function get_the_ID() { return 1; }
function get_post_type($id) { return 'page'; }
function get_post_meta($id, $key, $single) { return ''; }
function get_the_title($id) { return 'Калькуляторы'; }
function bis_get_page_banner_image_url($id) { return ''; }
function home_url($path) { return $path; }
function current_time($format) { return date($format); }
function esc_html($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function esc_attr($value) { return esc_html($value); }
function esc_url($value) { return esc_html($value); }

ob_start();
require __DIR__ . '/../wp-theme/page-calculators.php';
$html = ob_get_clean();

$document = new DOMDocument();
libxml_use_internal_errors(true);
$document->loadHTML('<?xml encoding="UTF-8">' . $html);
libxml_clear_errors();
$xpath = new DOMXPath($document);

// These inputs come directly from the client's written review, not from old formulas.
$required = [
    'du4_1' => [
        'du4_1_distance', 'du4_1_receivers', 'du4_1_shaft_width', 'du4_1_shaft_height',
        'du4_1_indoor_temp', 'du4_1_outdoor_temp', 'du4_1_wind_speed',
        'du4_1_upper_floor_elevation', 'du4_1_exhaust_elevation',
        'du4_1_building_type', 'du4_1_corridor_shape',
    ],
    'pd4_1' => [
        'pd4_1_stair_type', 'pd4_1_has_airlock', 'pd4_1_outdoor_doors',
        'pd4_1_outdoor_door_sizes', 'pd4_1_indoor_temp', 'pd4_1_outdoor_temp',
        'pd4_1_stair_width', 'pd4_1_stair_length',
        'pd4_1_lower_floor_elevation', 'pd4_1_upper_floor_elevation',
    ],
    'pd4_2' => [
        'pd4_2_elevator_type', 'pd4_2_portal_width', 'pd4_2_portal_height',
        'pd4_2_lower_floor_elevation', 'pd4_2_intake_elevation',
        'pd4_2_indoor_temp', 'pd4_2_outdoor_temp',
    ],
];
foreach (['pd4_7', 'pd4_8', 'pd7_a'] as $system) {
    $required[$system] = [
        "{$system}_w", "{$system}_h", "{$system}_damper_width", "{$system}_damper_height",
        "{$system}_shaft_width", "{$system}_shaft_height",
        "{$system}_lower_floor_elevation", "{$system}_intake_elevation",
    ];
}

foreach ($required as $system => $fields) {
    foreach ($fields as $id) {
        $nodes = $xpath->query("//*[@id='avok-{$system}']//*[@id='{$id}']");
        if ($nodes->length !== 1) {
            throw new RuntimeException("Missing input {$id} in {$system}");
        }
        $field = $nodes->item(0);
        if ($field->hasAttribute('value') && trim($field->getAttribute('value')) !== '') {
            throw new RuntimeException("Invented default value in {$id}");
        }
    }
}

echo "AVOK source-data form checks passed.\n";
