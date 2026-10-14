<?php
function bis_register_faq() {
    register_post_type('bis_faq',array('labels'=>array('name'=>'F.A.Q','singular_name'=>'Вопрос','add_new_item'=>'Добавить вопрос','edit_item'=>'Редактировать вопрос'),'public'=>false,'show_ui'=>true,'show_in_rest'=>true,'menu_icon'=>'dashicons-editor-help','supports'=>array('title','editor','page-attributes')));
    register_taxonomy('bis_faq_category','bis_faq',array('labels'=>array('name'=>'Рубрики','singular_name'=>'Рубрика'),'public'=>false,'show_ui'=>true,'show_in_rest'=>true,'hierarchical'=>true));
}
add_action('init','bis_register_faq');
function bis_client_create_pages() {
    if (!current_user_can('manage_options') || get_option('bis_october_pages_v1')) return;
    foreach (array('faq'=>array('F.A.Q','page-faq.php'),'gallery'=>array('Фото и видео','page-gallery.php')) as $slug=>$page) {
        if (!get_page_by_path($slug)) {
            $id=wp_insert_post(array('post_type'=>'page','post_status'=>'publish','post_title'=>$page[0],'post_name'=>$slug),true);
            if (is_wp_error($id)) return;
            update_post_meta($id,'_wp_page_template',$page[1]);
        }
    }
    $seeds=json_decode(file_get_contents(get_template_directory().'/inc/faq-seeds.json'),true);
    foreach ($seeds ?: array() as $index=>$item) {
        $existing=get_posts(array('post_type'=>'bis_faq','post_status'=>'any','meta_key'=>'bis_faq_seed','meta_value'=>(string)$index,'fields'=>'ids','posts_per_page'=>1));
        if ($existing) continue;
        $id=wp_insert_post(array('post_type'=>'bis_faq','post_status'=>'publish','post_title'=>$item['question'],'post_content'=>$item['answer'],'menu_order'=>$index,'meta_input'=>array('bis_faq_seed'=>(string)$index)),true);
        if (is_wp_error($id)) return;
    }
    update_option('bis_october_pages_v1','1',false);
}
add_action('admin_init','bis_client_create_pages');
function bis_faq_items($limit=-1) {
    return get_posts(array('post_type'=>'bis_faq','post_status'=>'publish','posts_per_page'=>$limit,'orderby'=>array('menu_order'=>'ASC','date'=>'ASC')));
}
function bis_render_faq_items($items,$filters=false) {
    if (!$items) { echo '<p>Вопросы скоро появятся.</p>'; return; }
    echo '<div class="faq-container" data-faq-list>';
    foreach ($items as $item) {
        $terms=wp_get_post_terms($item->ID,'bis_faq_category',array('fields'=>'ids'));
        echo '<details class="bis-faq-detail" data-faq-categories="'.esc_attr(implode(',',is_array($terms)?$terms:array())).'"><summary>'.esc_html($item->post_title).'</summary><div>'.wpautop(wp_kses_post($item->post_content)).'</div></details>';
    }
    echo '</div>';
}
