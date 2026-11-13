<?php
function bis_hero_default_slide() {
    return array('image'=>'', 'title'=>'БИС — Баланс Инженерных Систем', 'text'=>'Компания «БИС — Баланс Инженерных Систем» специализируется на комплексных пусконаладочных работах инженерных систем, техническом обслуживании и сопровождении', 'button'=>'Рассчитать смету и сроки', 'url'=>'', 'new_tab'=>false, 'hidden'=>false);
}
function bis_hero_normalize_slides($slides) {
    if (!is_array($slides)) return array();
    $result = array();
    foreach (array_slice($slides, 0, 12) as $slide) {
        if (!is_array($slide)) continue;
        $title = sanitize_text_field($slide['title'] ?? '');
        $normalized = array('image'=>esc_url_raw($slide['image'] ?? ''), 'title'=>$title, 'text'=>sanitize_textarea_field($slide['text'] ?? ''), 'button'=>sanitize_text_field($slide['button'] ?? ''), 'url'=>esc_url_raw($slide['url'] ?? ''), 'new_tab'=>!empty($slide['new_tab']), 'hidden'=>!empty($slide['hidden']));
        if ($normalized['image'] === '' && $normalized['title'] === '' && $normalized['text'] === '' && $normalized['button'] === '' && $normalized['url'] === '') continue;
        $result[] = $normalized;
    }
    return array_values($result);
}
function bis_get_hero_slides($include_hidden = false) {
    $slides = get_option('bis_hero_slides', null);
    if (is_array($slides)) {
        $slides = bis_hero_normalize_slides($slides);
        return $include_hidden ? $slides : array_values(array_filter($slides, function ($slide) { return !$slide['hidden']; }));
    }
    $images = get_option('bis_hero_slider_images', array());
    if (!is_array($images)) return array();
    return array_map(function ($image) { return array_merge(bis_hero_default_slide(), array('image'=>esc_url_raw($image))); }, array_values(array_filter($images, 'is_string')));
}
function bis_hero_interval_seconds($value = null) {
    if ($value === null) $value = get_option('bis_hero_interval', 7);
    return max(3, min(60, (int) $value));
}
function bis_render_hero_admin_slide($slide, $index) {
    $slide = array_merge(bis_hero_default_slide(), $slide);
    ?>
    <li class="hero-slider-item">
        <span class="dashicons dashicons-move handle" aria-label="Перетащить слайд"></span>
        <div class="image-preview" style="background-image:url('<?php echo esc_url($slide['image']); ?>')"></div>
        <input type="hidden" data-hero-field="image" name="hero_slides[<?php echo (int) $index; ?>][image]" value="<?php echo esc_attr($slide['image']); ?>">
        <button type="button" class="button choose-hero-image">Выбрать фон</button>
        <?php foreach (array('title'=>'Заголовок','text'=>'Описание','button'=>'Текст кнопки','url'=>'Ссылка кнопки') as $field=>$label) : ?>
            <label><?php echo esc_html($label); ?>
            <?php if ($field === 'text') : ?>
                <textarea data-hero-field="text" name="hero_slides[<?php echo (int) $index; ?>][text]" rows="4"><?php echo esc_textarea($slide[$field]); ?></textarea>
            <?php else : ?>
                <input type="text" data-hero-field="<?php echo esc_attr($field); ?>" name="hero_slides[<?php echo (int) $index; ?>][<?php echo esc_attr($field); ?>]" value="<?php echo esc_attr($slide[$field]); ?>">
            <?php endif; ?>
            </label>
        <?php endforeach; ?>
        <?php foreach (array('new_tab'=>'Открывать ссылку в новой вкладке', 'hidden'=>'Скрыть слайд из показа') as $field=>$label) : ?>
            <label><input type="checkbox" data-hero-field="<?php echo esc_attr($field); ?>" name="hero_slides[<?php echo (int) $index; ?>][<?php echo esc_attr($field); ?>]" value="1" <?php checked($slide[$field]); ?>> <?php echo esc_html($label); ?></label>
        <?php endforeach; ?>
        <p class="description">Пустая ссылка открывает расчёт сметы. Порядок меняется перетаскиванием. До 12 слайдов.</p>
        <button type="button" class="button remove-image">Удалить слайд</button>
    </li>
    <?php
}
