<?php
/** S3 direct uploads: signatures stay on the server; media bypasses WordPress disk. */
function bis_s3_settings() {
    $stored=get_option('bis_gallery_s3',array()); $result=array();
    foreach (array('endpoint','region','bucket','access_key','secret_key','public_url') as $key) {
        $result[$key]=trim((string)bis_get_runtime_setting('BIS_S3_'.strtoupper($key),$stored[$key] ?? ''));
    }
    return $result;
}
function bis_s3_ready($config=null) {
    $config=$config ?? bis_s3_settings();
    foreach (array('endpoint','region','bucket','access_key','secret_key','public_url') as $key) if (empty($config[$key])) return false;
    return true;
}
function bis_gallery_file_kind($name,$mime) {
    $ext=strtolower(pathinfo($name,PATHINFO_EXTENSION));
    $map=array('jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp','mp4'=>'video/mp4','webm'=>'video/webm');
    return isset($map[$ext]) && $map[$ext]===$mime ? (strpos($mime,'video/')===0 ? 'video' : 'photo') : '';
}
function bis_s3_presign($config,$method,$key,$expires=900,$now=null,$content_type='') {
    $now=$now ?? time(); $date=gmdate('Ymd',$now); $timestamp=gmdate('Ymd\THis\Z',$now);
    $scope=$date.'/'.$config['region'].'/s3/aws4_request';
    $endpoint=rtrim($config['endpoint'],'/');
    $host=parse_url($endpoint,PHP_URL_HOST); $port=parse_url($endpoint,PHP_URL_PORT); if ($port) $host.=':'.$port;
    $path='/'.rawurlencode($config['bucket']).'/'.implode('/',array_map('rawurlencode',explode('/',$key)));
    $headers=$content_type!=='' ? 'content-type;host' : 'host';
    $canonical_headers=($content_type!=='' ? 'content-type:'.$content_type."\n" : '').'host:'.$host."\n";
    $query=array('X-Amz-Algorithm'=>'AWS4-HMAC-SHA256','X-Amz-Credential'=>$config['access_key'].'/'.$scope,'X-Amz-Date'=>$timestamp,'X-Amz-Expires'=>(string)$expires,'X-Amz-SignedHeaders'=>$headers);
    ksort($query); $qs=http_build_query($query,'','&',PHP_QUERY_RFC3986);
    $canonical=$method."\n".$path."\n".$qs."\n".$canonical_headers."\n".$headers."\nUNSIGNED-PAYLOAD";
    $string="AWS4-HMAC-SHA256\n".$timestamp."\n".$scope."\n".hash('sha256',$canonical);
    $signing=hash_hmac('sha256',$date,'AWS4'.$config['secret_key'],true);
    foreach (array($config['region'],'s3','aws4_request') as $part) $signing=hash_hmac('sha256',$part,$signing,true);
    return $endpoint.$path.'?'.$qs.'&X-Amz-Signature='.hash_hmac('sha256',$string,$signing);
}
function bis_gallery_sign_upload() {
    check_ajax_referer('bis_gallery_upload','nonce');
    $id=absint($_POST['post_id'] ?? 0);
    if (get_post_type($id)!=='bis_gallery' || !current_user_can('edit_post',$id) || !current_user_can('upload_files')) wp_send_json_error(array('message'=>'Нет доступа.'),403);
    $config=bis_s3_settings();
    if (!bis_s3_ready($config)) wp_send_json_error(array('message'=>'Сначала заполните настройки S3 в разделе Галерея → Хранилище S3.'),400);
    $name=sanitize_file_name(wp_unslash($_POST['name'] ?? '')); $mime=sanitize_text_field(wp_unslash($_POST['mime'] ?? '')); $size=(int)($_POST['size'] ?? 0);
    $kind=bis_gallery_file_kind($name,$mime);
    if (!$kind || $size<1 || $size>2*1024*1024*1024) wp_send_json_error(array('message'=>'Разрешены JPG, PNG, WebP, MP4 и WebM до 2 ГБ.'),400);
    $slot=sanitize_key($_POST['slot'] ?? 'media');
    if (!in_array($slot,array('media','poster'),true) || ($slot==='poster' && $kind!=='photo')) wp_send_json_error(array('message'=>'Обложка должна быть фотографией.'),400);
    $key='gallery/'.$id.'/'.wp_generate_uuid4().'.'.strtolower(pathinfo($name,PATHINFO_EXTENSION));
    $ticket=wp_generate_uuid4();
    set_transient('bis_s3_'.$ticket,array('user'=>get_current_user_id(),'id'=>$id,'key'=>$key,'kind'=>$kind,'mime'=>$mime,'size'=>$size,'slot'=>$slot,'public_url'=>rtrim($config['public_url'],'/').'/'.$key),HOUR_IN_SECONDS);
    wp_send_json_success(array('upload_url'=>bis_s3_presign($config,'PUT',$key,3600,null,$mime),'ticket'=>$ticket));
}
add_action('wp_ajax_bis_gallery_sign_upload','bis_gallery_sign_upload');
function bis_gallery_complete_upload() {
    check_ajax_referer('bis_gallery_upload','nonce');
    $ticket=sanitize_key($_POST['ticket'] ?? ''); $pending=get_transient('bis_s3_'.$ticket);
    if (!$pending || $pending['user']!==get_current_user_id() || !current_user_can('edit_post',$pending['id'])) wp_send_json_error(array('message'=>'Сессия загрузки истекла.'),403);
    $response=wp_safe_remote_request(bis_s3_presign(bis_s3_settings(),'HEAD',$pending['key'],60),array('method'=>'HEAD','timeout'=>20));
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response)!==200 || (int)wp_remote_retrieve_header($response,'content-length')!==$pending['size'] || strtolower(trim(explode(';',(string)wp_remote_retrieve_header($response,'content-type'))[0]))!==$pending['mime']) wp_send_json_error(array('message'=>'Файл не подтвержден в S3. Повторите загрузку.'),400);
    $field=$pending['slot']==='poster' ? 'bis_gallery_poster' : 'bis_gallery_url';
    update_post_meta($pending['id'],$field,esc_url_raw($pending['public_url']));
    if ($pending['slot']==='media') {
        update_post_meta($pending['id'],'bis_gallery_kind',$pending['kind']);
        update_post_meta($pending['id'],'bis_gallery_s3_key',$pending['key']);
    }
    delete_transient('bis_s3_'.$ticket);
    wp_send_json_success(array('url'=>$pending['public_url'],'kind'=>$pending['kind']));
}
add_action('wp_ajax_bis_gallery_complete_upload','bis_gallery_complete_upload');
function bis_s3_admin_menu() { add_submenu_page('edit.php?post_type=bis_gallery','Хранилище S3','Хранилище S3','manage_options','bis-gallery-s3','bis_s3_settings_page'); }
add_action('admin_menu','bis_s3_admin_menu');
function bis_s3_sanitize_settings($input) {
    $old=get_option('bis_gallery_s3',array()); $out=array();
    foreach (array('endpoint','region','bucket','access_key','secret_key','public_url') as $key) {
        $value=trim((string)($input[$key] ?? ''));
        if (in_array($key,array('access_key','secret_key'),true) && $value==='') $value=$old[$key] ?? '';
        if (in_array($key,array('endpoint','public_url'),true)) {
            $value=esc_url_raw($value,array('https'));
            if ($value && (!$value || parse_url($value,PHP_URL_QUERY) || parse_url($value,PHP_URL_USER) || ($key==='endpoint' && trim((string)parse_url($value,PHP_URL_PATH),'/')!==''))) {
                add_settings_error('bis_gallery_s3',$key,'Endpoint должен быть HTTPS-адресом сервиса без пути, параметров и пароля.'); $value=$old[$key] ?? '';
            }
        } else if (!in_array($key,array('access_key','secret_key'),true)) $value=sanitize_text_field($value);
        $out[$key]=$value;
    }
    return $out;
}
function bis_s3_register_settings() { register_setting('bis_gallery_s3_group','bis_gallery_s3',array('sanitize_callback'=>'bis_s3_sanitize_settings')); }
add_action('admin_init','bis_s3_register_settings');
function bis_s3_settings_page() {
    if (!current_user_can('manage_options')) return;
    $config=bis_s3_settings();
    echo '<div class="wrap"><h1>Хранилище S3 для галереи</h1><p>Фото и видео загружаются прямо из браузера в облако. Обычная медиатека WordPress не изменяется.</p>';
    settings_errors('bis_gallery_s3');
    echo bis_s3_ready($config) ? '<p>Настройки заполнены. Проверьте загрузку одного файла и его открытие после сохранения.</p>' : '<div class="notice notice-warning inline"><p>Хранилище не подключено. Создайте бакет у провайдера и заполните поля ниже.</p></div>';
    echo '<form method="post" action="options.php">'; settings_fields('bis_gallery_s3_group'); echo '<table class="form-table">';
    $labels=array('endpoint'=>'Адрес сервиса (Endpoint), HTTPS без имени бакета','region'=>'Регион','bucket'=>'Имя бакета','access_key'=>'Ключ доступа (Access Key)','secret_key'=>'Секретный ключ (Secret Key)','public_url'=>'Публичный адрес файлов (включая бакет, если нужен)');
    foreach ($labels as $key=>$label) {
        $secret=in_array($key,array('access_key','secret_key'),true);
        echo '<tr><th><label for="s3-'.$key.'">'.esc_html($label).'</label></th><td><input class="regular-text" id="s3-'.$key.'" name="bis_gallery_s3['.$key.']" type="'.($secret?'password':'text').'" autocomplete="off" value="'.esc_attr($secret?'':$config[$key]).'">';
        if ($secret) echo '<p class="description">'.($config[$key]!==''?'Ключ сохранён. Пустое поле оставит его без изменения.':'Вставьте ключ из личного кабинета провайдера.').'</p>';
        echo '</td></tr>';
    }
    echo '</table>';submit_button();echo '</form><h2>Настройка бакета</h2><ol><li>Выдайте ключу доступ только к этому бакету и префиксу gallery/.</li><li>Разрешите публичное чтение файлов gallery/ или подключите публичный CDN. Запись остаётся по подписанным ссылкам.</li><li>Добавьте приведённое ниже правило CORS у провайдера.</li></ol>';
    $origin=wp_parse_url(home_url('/')); $origin=$origin['scheme'].'://'.$origin['host'];
    $cors=array(array('AllowedOrigins'=>array($origin),'AllowedMethods'=>array('PUT','GET','HEAD'),'AllowedHeaders'=>array('Content-Type'),'ExposeHeaders'=>array('ETag'),'MaxAgeSeconds'=>3600));
    echo '<pre>'.esc_html(wp_json_encode($cors,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)).'</pre><p>Предел прямой загрузки — 2 ГБ на файл. Разрешены JPG, PNG, WebP, MP4 и WebM. Существующие файлы при замене или удалении записи автоматически из бакета не удаляются.</p></div>';
}
