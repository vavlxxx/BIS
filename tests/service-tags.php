<?php

class WP_Error {
    private $code;
    private $message;
    private $data;
    public function __construct($code, $message, $data = null) {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }
    public function get_error_code() { return $this->code; }
    public function get_error_message() { return $this->message; }
    public function get_error_data() { return $this->data; }
}
function is_wp_error($value) { return $value instanceof WP_Error; }
function sanitize_text_field($value) { return trim(strip_tags($value)); }
function wp_unslash($value) { return $value; }
function add_action(...$args) {}
$allow_access = true;
function get_post_type($id) { return $id === 1045 ? 'bis_service' : 'post'; }
function get_taxonomy($name) {
    return (object) array('cap' => (object) array('assign_terms' => 'assign_terms', 'edit_terms' => 'edit_terms'));
}
function current_user_can($cap, ...$args) { global $allow_access; return $allow_access; }
class TestRequest {
    private $params;
    public function __construct($params) { $this->params = $params; }
    public function get_param($name) { return $this->params[$name] ?? null; }
}

$stored_terms = array();
$next_term_id = 1;
function term_exists($name, $taxonomy) {
    global $stored_terms;
    return $stored_terms[$name] ?? null;
}
function wp_insert_term($name, $taxonomy) {
    global $stored_terms, $next_term_id;
    if ($name === 'Ошибка') {
        return new WP_Error('insert_failed', 'Ошибка вставки');
    }
    $stored_terms[$name] = $next_term_id++;
    return array('term_id' => $stored_terms[$name]);
}

require __DIR__ . '/../wp-theme/inc/service-tag-import.php';

function check($condition, $message) {
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$customer_terms = 'проектирование вентиляции, проектирование вентиляции бассейна, проектирование вентиляции в котельной, проектирование вентиляции жилых зданий, проектирование вентиляции зданий, проектирование вентиляции и кондиционирования, проектирование вентиляции и кондиционирования помещений, проектирование вентиляции компании, проектирование вентиляции кондиционирования воздуха, проектирование вентиляции лабораторий, проектирование вентиляции общественного питания, проектирование вентиляции общественных зданий, проектирование вентиляции организации, проектирование вентиляции помещений, проектирование вентиляции предприятий, проектирование вентиляции предприятий общественного питания, проектирование вентиляции производства, проектирование вентиляции промышленных, проектирование вентиляции ресторана, проектирование вентиляции цеха, проектирование вентиляция чистых помещений, проектирование вытяжной вентиляции, проектирование вытяжной системы вентиляции, проектирование инженерных систем вентиляции, проектирование общеобменной вентиляции, проектирование отопления вентиляции кондиционирования воздуха, проектирование приточно вытяжной вентиляции, проектирование приточной вентиляции, проектирование приточной системы вентиляции, проектирование производственной вентиляции, проектирование расчет систем вентиляции, проектирование сетей вентиляции, проектирование систем вентиляции, проектирование систем вентиляции воздуха, проектирование систем вентиляции и кондиционирования воздуха, проектирование систем вентиляции компания, проектирование систем вентиляции общественных зданий, проектирование систем вентиляции сооружений, проектирование системы вентиляции в помещении, проектирование системы вентиляции здания, проектирование системы приточно вытяжной вентиляции, проектирование складов вентиляция, проектирование технических вентиляции, проектирование технологической вентиляции, проектирование школы вентиляция, промышленные здания проектирование вентиляции, услуги проектирования вентиляции,';

$terms = bis_parse_service_tag_list($customer_terms);
check(!is_wp_error($terms), 'Список заказчика должен приниматься');
check(count($terms) === 47, 'Все 47 меток должны быть разобраны');
check($terms[0] === 'проектирование вентиляции', 'Первая метка должна сохраняться');
check(end($terms) === 'услуги проектирования вентиляции', 'Последняя метка должна сохраняться');

$ids = bis_resolve_service_tags($terms);
check(!is_wp_error($ids) && count($ids) === 47, 'Все метки должны получить ID');
check(bis_resolve_service_tags($terms) === $ids, 'Повторная вставка не должна создавать дубликаты');
check($next_term_id === 48, 'После повторной вставки новых терминов нет');

$duplicates = bis_parse_service_tag_list(" Первая, первая, Вторая\nТретья,, ");
check($duplicates === array('Первая', 'Вторая', 'Третья'), 'Дубликаты и пустые элементы удалены');
check(is_wp_error(bis_parse_service_tag_list(str_repeat('x,', 101))), 'Слишком длинный список отклонён до записи');
check(is_wp_error(bis_parse_service_tag_list(' , , ')), 'Пустой список отклонён');
check(is_wp_error(bis_resolve_service_tags(array('Ошибка'))), 'Ошибка создания термина передаётся вызывающему коду');

check(bis_service_tag_import_permission(new TestRequest(array('post_id' => 1045))), 'Редактор услуги может импортировать метки');
check(!bis_service_tag_import_permission(new TestRequest(array('post_id' => 999))), 'Нельзя импортировать метки для другой записи');
$allow_access = false;
check(!bis_service_tag_import_permission(new TestRequest(array('post_id' => 1045))), 'Без прав импорт запрещён');

echo "Service tag import tests passed\n";
