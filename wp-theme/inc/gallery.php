<?php
function bis_register_gallery() {
    register_post_type('bis_gallery',array('labels'=>array('name'=>'Фото / Видео','singular_name'=>'Материал галереи','add_new_item'=>'Добавить фото или видео','edit_item'=>'Редактировать материал'),'public'=>false,'show_ui'=>true,'show_in_rest'=>true,'menu_icon'=>'dashicons-format-gallery','supports'=>array('title','editor')));
}
add_action('init','bis_register_gallery');
function bis_gallery_box() { add_meta_box('bis_gallery_media','Фото / Видео в S3','bis_gallery_fields','bis_gallery','normal','high'); }
add_action('add_meta_boxes','bis_gallery_box');
function bis_gallery_fields($post) {
    wp_nonce_field('bis_gallery_fields','bis_gallery_nonce');
    $ready=bis_s3_ready();
    echo '<div data-gallery-upload data-post-id="'.esc_attr($post->ID).'" data-nonce="'.esc_attr(wp_create_nonce('bis_gallery_upload')).'">';
    if (!$ready) echo '<div class="notice notice-warning inline"><p>Хранилище S3 пока не подключено. <a href="'.esc_url(admin_url('edit.php?post_type=bis_gallery&page=bis-gallery-s3')).'">Открыть настройки</a></p></div>';
    foreach (array('media'=>'Основное фото или видео','poster'=>'Обложка видео (фото)') as $slot=>$label) {
        $key=$slot==='poster'?'bis_gallery_poster':'bis_gallery_url';
        echo '<p><strong>'.esc_html($label).'</strong></p><input type="file" data-upload-slot="'.$slot.'" accept="'.($slot==='poster'?'.jpg,.jpeg,.png,.webp':'.jpg,.jpeg,.png,.webp,.mp4,.webm').'" '.disabled(!$ready,true,false).'><p><input type="url" class="widefat" readonly name="'.$key.'" data-upload-result="'.$slot.'" value="'.esc_attr(get_post_meta($post->ID,$key,true)).'"></p>';
    }
    echo '<progress data-upload-progress value="0" max="100" hidden></progress><p data-upload-status role="status" aria-live="polite"></p><p>Дождитесь завершения загрузки, затем опубликуйте или сохраните материал. Поддерживаются JPG, PNG, WebP, MP4, WebM до 2 ГБ. Файлы идут напрямую в S3.</p></div>';
}
function bis_gallery_scripts($hook) {
    if (!in_array($hook,array('post.php','post-new.php'),true) || get_current_screen()->post_type!=='bis_gallery') return;
    wp_enqueue_script('bis-gallery-admin',get_template_directory_uri().'/assets/js/admin-gallery.js',array(),bis_get_asset_version('assets/js/admin-gallery.js'),true);
}
add_action('admin_enqueue_scripts','bis_gallery_scripts');
function bis_gallery_items($limit=-1) {
    return get_posts(array('post_type'=>'bis_gallery','post_status'=>'publish','posts_per_page'=>$limit,'orderby'=>'date','order'=>'DESC','meta_query'=>array(array('key'=>'bis_gallery_url','value'=>'','compare'=>'!='))));
}
function bis_render_gallery($limit=-1) {
    $items=bis_gallery_items($limit);
    echo '<div data-gallery-section><nav class="bis-filter-tabs" aria-label="Тип материалов">';
    foreach (array('all'=>'Все','photo'=>'Фото','video'=>'Видео') as $kind=>$label) echo '<button type="button" class="btn '.($kind==='all'?'btn-primary':'btn-outline').'" data-gallery-filter="'.$kind.'" aria-pressed="'.($kind==='all'?'true':'false').'">'.$label.'</button>';
    echo '</nav><div class="bis-gallery-grid">';
    foreach ($items as $item) {
        $url=get_post_meta($item->ID,'bis_gallery_url',true); $kind=get_post_meta($item->ID,'bis_gallery_kind',true); $poster=get_post_meta($item->ID,'bis_gallery_poster',true);
        echo '<article class="bis-gallery-card" data-gallery-kind="'.esc_attr($kind).'">';
        if ($kind==='video') {
            echo '<video controls playsinline preload="none" '.($poster?'poster="'.esc_url($poster).'"':'').' aria-label="'.esc_attr($item->post_title).'"><source src="'.esc_url($url).'"></video>';
        } else echo '<a href="'.esc_url($url).'" data-gallery-photo aria-label="'.esc_attr('Открыть фото: '.$item->post_title).'"><img src="'.esc_url($url).'" alt="'.esc_attr($item->post_title).'" loading="lazy"></a>';
        echo '<h3>'.esc_html($item->post_title).'</h3><div>'.wpautop(wp_kses_post($item->post_content)).'</div></article>';
    }
    echo '</div><p data-gallery-empty '.($items?'hidden':'').'>Материалы скоро появятся.</p></div>';
}
