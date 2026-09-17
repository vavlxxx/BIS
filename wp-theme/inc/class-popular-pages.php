<?php
/**
 * Class BIS_Popular_Pages_CPT
 *
 * Registers the 'popular_page' custom post type and associated taxonomies
 * ('popular_category' and 'popular_tag') to manage the popular services / pages block
 * for SEO interlinking, adapted from ECG architecture for BIS.
 */

if (!defined('ABSPATH')) {
    exit;
}

class BIS_Popular_Pages_CPT
{
    public function __construct()
    {
        add_action('init', array($this, 'register_post_type_and_taxonomies'), 10);
        add_action('init', array($this, 'register_rewrite_rules'), 25);
        add_filter('post_type_link', array($this, 'filter_post_type_link'), 10, 4);
        add_filter('request', array($this, 'resolve_popular_page_request'), 20);

        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_popular_page', array($this, 'save_meta_box_data'), 10, 2);

        // Submenu: Quick Add & Rubrics Management & Drag-and-Drop Order
        add_action('admin_menu', array($this, 'add_admin_submenus'));
        add_action('admin_init', array($this, 'handle_admin_actions'));
        add_action('admin_init', array($this, 'check_flush_rewrite_rules'));

        // Drag & Drop Order Handlers
        add_action('admin_post_bis_save_popular_category_order', array($this, 'handle_save_category_order'));
        add_action('admin_post_bis_save_popular_page_order', array($this, 'handle_save_page_order'));

        // Category order taxonomy fields and columns
        add_action('popular_category_add_form_fields', array($this, 'add_category_order_field'));
        add_action('popular_category_edit_form_fields', array($this, 'edit_category_order_field'));
        add_action('created_popular_category', array($this, 'save_category_order_meta'));
        add_action('edited_popular_category', array($this, 'save_category_order_meta'));
        add_filter('manage_edit-popular_category_columns', array($this, 'category_order_column'));
        add_filter('manage_popular_category_custom_column', array($this, 'render_category_order_column'), 10, 3);
        add_filter('manage_edit-popular_category_sortable_columns', array($this, 'category_order_sortable_column'));

        // Flush rewrites on category taxonomy changes & post save
        add_action('created_popular_category', array($this, 'flush_rewrites'));
        add_action('edited_popular_category', array($this, 'flush_rewrites'));
        add_action('delete_popular_category', array($this, 'flush_rewrites'));
        add_action('save_post_popular_page', array($this, 'flush_rewrites'), 20);

        // Custom columns in admin list table
        add_filter('manage_popular_page_posts_columns', array($this, 'customize_columns'));
        add_action('manage_popular_page_posts_custom_column', array($this, 'render_column_content'), 10, 2);
        add_filter('manage_edit-popular_page_sortable_columns', array($this, 'sortable_columns'));

        // Single post redirect if custom URL is provided
        add_action('template_redirect', array($this, 'handle_single_redirect'));

        // Auto-seed initial popular services from existing bis_service items
        add_action('init', array($this, 'maybe_seed_popular_services'), 30);
    }

    /**
     * Register Custom Post Type and Taxonomies
     */
    public function register_post_type_and_taxonomies()
    {
        // 1. Taxonomy: Rubrics (popular_category)
        $category_labels = array(
            'name'              => 'Рубрики',
            'singular_name'     => 'Рубрика',
            'search_items'      => 'Найти рубрики',
            'all_items'         => 'Все рубрики',
            'parent_item'       => 'Родительская рубрика',
            'parent_item_colon' => 'Родительская рубрика:',
            'edit_item'         => 'Редактировать рубрику',
            'update_item'       => 'Обновить рубрику',
            'add_new_item'      => 'Добавить новую рубрику',
            'new_item_name'     => 'Название новой рубрики',
            'menu_name'         => 'Рубрики',
        );

        register_taxonomy('popular_category', array('popular_page'), array(
            'hierarchical'      => true,
            'labels'            => $category_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => array(
                'slug'          => 'popular-category',
                'with_front'    => false,
            ),
        ));

        // 2. Taxonomy: Tags (popular_tag)
        $tag_labels = array(
            'name'                       => 'Метки',
            'singular_name'              => 'Метка',
            'search_items'               => 'Найти метки',
            'popular_items'              => 'Популярные метки',
            'all_items'                  => 'Все метки',
            'edit_item'                  => 'Редактировать метку',
            'update_item'                => 'Обновить метку',
            'add_new_item'               => 'Добавить новую метку',
            'new_item_name'              => 'Название новой метки',
            'separate_items_with_commas' => 'Разделяйте метки запятыми',
            'add_or_remove_items'        => 'Добавить или удалить метки',
            'choose_from_most_used'      => 'Выбрать из часто используемых',
            'not_found'                  => 'Меток не найдено',
            'menu_name'                  => 'Метки',
        );

        register_taxonomy('popular_tag', array('popular_page'), array(
            'hierarchical'      => false,
            'labels'            => $tag_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => array(
                'slug'          => 'popular-tag',
                'with_front'    => false,
            ),
        ));

        // 3. Post Type: Popular Pages / Services (popular_page)
        $post_type_labels = array(
            'name'               => 'Популярные услуги',
            'singular_name'      => 'Популярная услуга',
            'add_new'            => 'Добавить ссылку',
            'add_new_item'       => 'Добавить популярную услугу/ссылку',
            'edit_item'          => 'Редактировать ссылку',
            'new_item'           => 'Новая услуга',
            'view_item'          => 'Просмотреть',
            'search_items'       => 'Найти ссылки',
            'not_found'          => 'Ссылок не найдено',
            'not_found_in_trash' => 'В корзине ссылок не найдено',
            'all_items'          => 'Все ссылки',
            'menu_name'          => 'Популярные услуги',
        );

        register_post_type('popular_page', array(
            'labels'              => $post_type_labels,
            'public'              => true,
            'has_archive'         => false,
            'menu_icon'           => 'dashicons-admin-links',
            'menu_position'       => 22,
            'supports'            => array('title', 'editor', 'thumbnail', 'page-attributes'),
            'taxonomies'          => array('popular_category', 'popular_tag'),
            'show_in_rest'        => true,
            'query_var'           => 'popular_page',
            'rewrite'             => false,
        ));

        // Register post meta
        register_post_meta('popular_page', 'bis_popular_page_url', array(
            'single'            => true,
            'type'              => 'string',
            'show_in_rest'      => true,
            'sanitize_callback' => 'esc_url_raw',
            'auth_callback'     => function () {
                return current_user_can('edit_posts');
            },
        ));

        register_post_meta('popular_page', 'bis_popular_page_target_blank', array(
            'single'            => true,
            'type'              => 'boolean',
            'show_in_rest'      => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
            'auth_callback'     => function () {
                return current_user_can('edit_posts');
            },
        ));
    }

    /**
     * Register rewrite rules for popular pages by rubric
     */
    public function register_rewrite_rules()
    {
        $categories = get_terms(array(
            'taxonomy'   => 'popular_category',
            'hide_empty' => false,
        ));
        if (!empty($categories) && !is_wp_error($categories)) {
            foreach ($categories as $cat) {
                $cat_slug = preg_quote($cat->slug, '#');
                add_rewrite_rule(
                    '^' . $cat_slug . '/([^/]+)/?$',
                    'index.php?popular_category=' . rawurlencode($cat->slug) . '&popular_page=$matches[1]',
                    'top'
                );
            }
        }
    }

    /**
     * Format permalinks for popular_page as /{rubric}/{slug}/
     */
    public function filter_post_type_link($post_link, $post, $leavename, $sample)
    {
        if (!$post instanceof WP_Post || $post->post_type !== 'popular_page') {
            return $post_link;
        }

        $terms = get_the_terms($post->ID, 'popular_category');
        $category_slug = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->slug : 'popular';

        $post_slug = $leavename ? '%postname%' : $post->post_name;
        if ($post_slug === '') {
            $post_slug = '%postname%';
        }

        return home_url(user_trailingslashit($category_slug . '/' . $post_slug, 'single'));
    }

    /**
     * Resolve request for popular_page by rubric and slug
     */
    public function resolve_popular_page_request($query_vars)
    {
        if (empty($query_vars['popular_page']) || empty($query_vars['popular_category'])) {
            return $query_vars;
        }

        $category_slug = sanitize_title((string) $query_vars['popular_category']);
        $post_slug = sanitize_title((string) $query_vars['popular_page']);

        if ($category_slug === '' || $post_slug === '') {
            return $query_vars;
        }

        $matches = get_posts(array(
            'name'                   => $post_slug,
            'post_type'              => 'popular_page',
            'post_status'            => 'publish',
            'posts_per_page'         => 1,
            'no_found_rows'          => true,
            'suppress_filters'       => true,
            'ignore_sticky_posts'    => true,
            'tax_query'              => array(
                array(
                    'taxonomy' => 'popular_category',
                    'field'    => 'slug',
                    'terms'    => $category_slug,
                ),
            ),
        ));

        if (!empty($matches) && $matches[0] instanceof WP_Post) {
            $query_vars['post_type'] = 'popular_page';
            $query_vars['name'] = $matches[0]->post_name;
        }

        return $query_vars;
    }

    /**
     * Flush rewrites helper
     */
    public function flush_rewrites()
    {
        $this->register_rewrite_rules();
        flush_rewrite_rules(false);
    }

    /**
     * Ensure rewrite rules are flushed if version changed
     */
    public function check_flush_rewrite_rules()
    {
        $version = '1.0.0';
        if (get_option('bis_popular_page_rewrites_ver') !== $version) {
            $this->register_rewrite_rules();
            flush_rewrite_rules(false);
            update_option('bis_popular_page_rewrites_ver', $version);
        }
    }

    /**
     * Add admin submenus
     */
    public function add_admin_submenus()
    {
        add_submenu_page(
            'edit.php?post_type=popular_page',
            'Порядок популярных услуг и рубрик',
            'Порядок',
            'edit_posts',
            'bis_popular_order',
            array($this, 'render_order_page')
        );

        add_submenu_page(
            'edit.php?post_type=popular_page',
            'Управление рубриками и ссылками',
            '⚡ Быстрое добавление',
            'edit_posts',
            'bis-popular-quick-add',
            array($this, 'render_quick_add_page')
        );
    }

    /**
     * Helper to collect all linkable items across the BIS site
     */
    public static function get_linkable_site_items()
    {
        $to_rel = function ($url) {
            if (empty($url)) return '';
            return function_exists('bis_make_url_relative') ? bis_make_url_relative($url) : wp_make_link_relative($url);
        };

        $groups = array(
            'core' => array(
                'label' => '📌 Основные разделы',
                'items' => array(
                    array(
                        'id'    => 'core_home',
                        'title' => 'Главная страница',
                        'url'   => '/',
                        'type'  => 'Раздел сайта',
                    ),
                    array(
                        'id'    => 'core_calc',
                        'title' => 'Калькуляторы противодымной вентиляции',
                        'url'   => '/calculators/',
                        'type'  => 'Раздел сайта',
                    ),
                    array(
                        'id'    => 'core_vacancies',
                        'title' => 'Вакансии компании',
                        'url'   => '/vacancies/',
                        'type'  => 'Раздел сайта',
                    ),
                    array(
                        'id'    => 'core_contacts',
                        'title' => 'Контакты / Оставить заявку',
                        'url'   => '/#contact',
                        'type'  => 'Раздел сайта',
                    ),
                ),
            ),
            'services' => array(
                'label' => '🔧 Услуги БИС',
                'items' => array(),
            ),
            'projects' => array(
                'label' => '🏢 Проекты / Объекты',
                'items' => array(),
            ),
            'pages' => array(
                'label' => '📄 Страницы сайта',
                'items' => array(),
            ),
            'media' => array(
                'label' => '📰 Медиа / Новости',
                'items' => array(),
            ),
        );

        // 1. Services (bis_service)
        $services = get_posts(array(
            'post_type'      => 'bis_service',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ));
        if (!empty($services)) {
            foreach ($services as $s) {
                $groups['services']['items'][] = array(
                    'id'    => 'srv_' . $s->ID,
                    'title' => $s->post_title,
                    'url'   => $to_rel(get_permalink($s->ID)),
                    'type'  => 'Услуга БИС',
                );
            }
        }

        // 2. Projects (bis_project)
        $projects = get_posts(array(
            'post_type'      => 'bis_project',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ));
        if (!empty($projects)) {
            foreach ($projects as $pr) {
                $groups['projects']['items'][] = array(
                    'id'    => 'proj_' . $pr->ID,
                    'title' => $pr->post_title,
                    'url'   => $to_rel(get_permalink($pr->ID)),
                    'type'  => 'Проект БИС',
                );
            }
        }

        // 3. Standard Pages (page)
        $pages = get_posts(array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ));
        if (!empty($pages)) {
            foreach ($pages as $p) {
                $groups['pages']['items'][] = array(
                    'id'    => 'page_' . $p->ID,
                    'title' => $p->post_title,
                    'url'   => $to_rel(get_permalink($p->ID)),
                    'type'  => 'Страница',
                );
            }
        }

        // 4. News / Media (bis_news)
        $news_items = get_posts(array(
            'post_type'      => 'bis_news',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ));
        if (!empty($news_items)) {
            foreach ($news_items as $news) {
                $groups['media']['items'][] = array(
                    'id'    => 'news_' . $news->ID,
                    'title' => $news->post_title,
                    'url'   => $to_rel(get_permalink($news->ID)),
                    'type'  => 'Новость/Статья',
                );
            }
        }

        return $groups;
    }

    /**
     * Map existing popular pages by category and url
     * @return array<int, array<string, int>> [cat_id => [url => post_id]]
     */
    public static function get_existing_popular_pages_map()
    {
        $posts = get_posts(array(
            'post_type'      => 'popular_page',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ));

        $map = array();
        foreach ($posts as $p) {
            $custom_url = get_post_meta($p->ID, 'bis_popular_page_url', true);
            if (empty($custom_url)) {
                $custom_url = get_post_meta($p->ID, 'ecg_popular_page_url', true);
            }
            $raw_url = $custom_url ? $custom_url : get_permalink($p->ID);
            $rel_url = function_exists('bis_make_url_relative') ? bis_make_url_relative($raw_url) : $raw_url;

            $terms = get_the_terms($p->ID, 'popular_category');
            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $t) {
                    if (!isset($map[$t->term_id])) {
                        $map[$t->term_id] = array();
                    }
                    $map[$t->term_id][$rel_url] = $p->ID;
                    if ($raw_url !== $rel_url) {
                        $map[$t->term_id][$raw_url] = $p->ID;
                    }
                }
            }
        }
        return $map;
    }

    /**
     * Add Meta Box for single popular page editor
     */
    public function add_meta_boxes()
    {
        add_meta_box(
            'bis_popular_page_settings',
            'Настройки целевой ссылки',
            array($this, 'render_meta_box'),
            'popular_page',
            'normal',
            'high'
        );
    }

    /**
     * Render Meta Box
     */
    public function render_meta_box($post)
    {
        wp_nonce_field('bis_popular_page_nonce_action', 'bis_popular_page_nonce');

        $url = get_post_meta($post->ID, 'bis_popular_page_url', true);
        if (empty($url)) {
            $url = get_post_meta($post->ID, 'ecg_popular_page_url', true);
        }
        $target_blank = (bool) get_post_meta($post->ID, 'bis_popular_page_target_blank', true);
        if (!$target_blank) {
            $target_blank = (bool) get_post_meta($post->ID, 'ecg_popular_page_target_blank', true);
        }

        $linkable_groups = self::get_linkable_site_items();
        ?>
        <div style="padding: 10px 0;">
            <p style="margin-bottom: 12px; font-size: 13px; color: #475569;">
                Укажите целевой URL, на который ведет эта ссылка при клике в блоке «Популярные услуги». Если URL не указан, ссылка ведет на отдельную страницу этой записи.
            </p>

            <p style="margin-bottom: 8px;">
                <label for="bis_popular_page_url" style="font-weight: 600; display: block; margin-bottom: 4px; color: #1e293b;">
                    Целевой URL ссылки:
                </label>
                <input
                    type="text"
                    id="bis_popular_page_url"
                    name="bis_popular_page_url"
                    value="<?php echo esc_attr($url); ?>"
                    placeholder="Например: /services/pasportizaciya-ventilyacii/ или https://..."
                    style="width: 100%; max-width: 650px; padding: 7px 10px; font-size: 14px; border: 1px solid #8c8f94;"
                >
            </p>

            <p style="margin-top: 14px;">
                <label style="cursor: pointer; font-size: 14px; color: #1e293b;">
                    <input
                        type="checkbox"
                        name="bis_popular_page_target_blank"
                        value="1"
                        <?php checked($target_blank, true); ?>
                    >
                    Открывать ссылку в новой вкладке (<code>target="_blank"</code>)
                </label>
            </p>
        </div>
        <?php
    }

    /**
     * Save Meta Box Data
     */
    public function save_meta_box_data($post_id, $post)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!isset($_POST['bis_popular_page_nonce']) || !wp_verify_nonce($_POST['bis_popular_page_nonce'], 'bis_popular_page_nonce_action')) return;
        if (!current_user_can('edit_post', $post_id)) return;

        if (isset($_POST['bis_popular_page_url'])) {
            $raw_url = trim($_POST['bis_popular_page_url']);
            if ($raw_url !== '') {
                $clean_url = function_exists('bis_make_url_relative') ? bis_make_url_relative($raw_url) : $raw_url;
                update_post_meta($post_id, 'bis_popular_page_url', $clean_url);
            } else {
                delete_post_meta($post_id, 'bis_popular_page_url');
            }
        }

        $target_blank = !empty($_POST['bis_popular_page_target_blank']) ? 1 : 0;
        update_post_meta($post_id, 'bis_popular_page_target_blank', $target_blank);
    }

    /**
     * Handle actions from Quick Add page
     */
    public function handle_admin_actions()
    {
        if (!isset($_REQUEST['bis_action'])) return;
        if (!current_user_can('edit_posts')) return;

        $base_url = admin_url('edit.php?post_type=popular_page&page=bis-popular-quick-add');
        $action = sanitize_text_field($_REQUEST['bis_action']);

        // 1. Create Rubric
        if ($action === 'create_category') {
            check_admin_referer('bis_rubrics_nonce_action', 'bis_rubrics_nonce');
            $name = isset($_POST['new_category_name']) ? sanitize_text_field($_POST['new_category_name']) : '';

            if (!empty($name)) {
                $term = wp_insert_term($name, 'popular_category');
                if (!is_wp_error($term) && isset($term['term_id'])) {
                    wp_safe_redirect(add_query_arg(array('selected_cat' => $term['term_id'], 'msg' => 'cat_created'), $base_url));
                    exit;
                }
            }
            wp_safe_redirect(add_query_arg('msg', 'cat_error', $base_url));
            exit;
        }

        // 2. Rename Rubric
        if ($action === 'rename_category') {
            check_admin_referer('bis_rubrics_nonce_action', 'bis_rubrics_nonce');
            $term_id = isset($_POST['term_id']) ? (int) $_POST['term_id'] : 0;
            $new_name = isset($_POST['updated_name']) ? sanitize_text_field($_POST['updated_name']) : '';

            if ($term_id > 0 && !empty($new_name)) {
                wp_update_term($term_id, 'popular_category', array('name' => $new_name));
                wp_safe_redirect(add_query_arg(array('selected_cat' => $term_id, 'msg' => 'cat_renamed'), $base_url));
                exit;
            }
            wp_safe_redirect($base_url);
            exit;
        }

        // 3. Delete Rubric
        if ($action === 'delete_category') {
            check_admin_referer('bis_delete_category_action');
            $term_id = isset($_GET['term_id']) ? (int) $_GET['term_id'] : 0;

            if ($term_id > 0) {
                wp_delete_term($term_id, 'popular_category');
                wp_safe_redirect(add_query_arg('msg', 'cat_deleted', $base_url));
                exit;
            }
            wp_safe_redirect($base_url);
            exit;
        }

        // 4. Delete Individual Link from Rubric
        if ($action === 'delete_page') {
            check_admin_referer('bis_delete_page_action');
            $post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;
            $selected_cat = isset($_GET['selected_cat']) ? (int) $_GET['selected_cat'] : 0;

            if ($post_id > 0) {
                wp_delete_post($post_id, true);
                wp_safe_redirect(add_query_arg(array('selected_cat' => $selected_cat, 'msg' => 'page_deleted'), $base_url));
                exit;
            }
            wp_safe_redirect($base_url);
            exit;
        }

        // 5. Add Custom Manual Link to Rubric
        if ($action === 'add_custom_link') {
            check_admin_referer('bis_custom_link_action', 'bis_custom_link_nonce');
            $category_id = isset($_POST['target_category_id']) ? (int) $_POST['target_category_id'] : 0;
            $title = isset($_POST['custom_link_title']) ? sanitize_text_field($_POST['custom_link_title']) : '';
            $url = isset($_POST['custom_link_url']) ? esc_url_raw(trim($_POST['custom_link_url'])) : '';
            $target_blank = !empty($_POST['custom_link_target_blank']) ? 1 : 0;

            if ($category_id > 0 && !empty($title) && !empty($url)) {
                $clean_url = function_exists('bis_make_url_relative') ? bis_make_url_relative($url) : $url;
                $new_id = wp_insert_post(array(
                    'post_type'   => 'popular_page',
                    'post_status' => 'publish',
                    'post_title'  => $title,
                ));
                if ($new_id && !is_wp_error($new_id)) {
                    update_post_meta($new_id, 'bis_popular_page_url', $clean_url);
                    update_post_meta($new_id, 'bis_popular_page_target_blank', $target_blank);
                    wp_set_object_terms($new_id, array($category_id), 'popular_category');
                    wp_safe_redirect(add_query_arg(array('selected_cat' => $category_id, 'msg' => 'custom_link_added'), $base_url));
                    exit;
                }
            }
            wp_safe_redirect(add_query_arg(array('selected_cat' => $category_id, 'msg' => 'error'), $base_url));
            exit;
        }

        // 6. Bulk Add Pages to Category
        if ($action === 'bulk_add_pages') {
            check_admin_referer('bis_bulk_add_action', 'bis_bulk_add_nonce');
            $category_id = isset($_POST['target_category_id']) ? (int) $_POST['target_category_id'] : 0;
            $selected_items = isset($_POST['selected_pages']) && is_array($_POST['selected_pages']) ? $_POST['selected_pages'] : array();

            if ($category_id <= 0) {
                wp_safe_redirect(add_query_arg('msg', 'no_category', $base_url));
                exit;
            }

            if (empty($selected_items)) {
                wp_safe_redirect(add_query_arg(array('selected_cat' => $category_id, 'msg' => 'no_pages_selected'), $base_url));
                exit;
            }

            $count = 0;
            foreach ($selected_items as $item_json) {
                $data = json_decode(stripslashes($item_json), true);
                if (!is_array($data) || empty($data['title']) || empty($data['url'])) {
                    continue;
                }

                $title = sanitize_text_field($data['title']);
                $url = function_exists('bis_make_url_relative') ? bis_make_url_relative($data['url']) : esc_url_raw($data['url']);

                $new_post_id = wp_insert_post(array(
                    'post_type'   => 'popular_page',
                    'post_status' => 'publish',
                    'post_title'  => $title,
                ));

                if ($new_post_id && !is_wp_error($new_post_id)) {
                    update_post_meta($new_post_id, 'bis_popular_page_url', $url);
                    wp_set_object_terms($new_post_id, array($category_id), 'popular_category');
                    $count++;
                }
            }

            wp_safe_redirect(add_query_arg(array(
                'selected_cat' => $category_id,
                'msg'          => 'bulk_success',
                'count'        => $count,
            ), $base_url));
            exit;
        }
    }

    /**
     * Render Quick Add & Rubrics Management page in WP Admin
     */
    public function render_quick_add_page()
    {
        $linkable_groups = self::get_linkable_site_items();
        $categories = get_terms(array(
            'taxonomy'   => 'popular_category',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ));

        // Default or selected category
        $selected_cat_id = isset($_GET['selected_cat']) ? (int) $_GET['selected_cat'] : 0;
        if ($selected_cat_id === 0 && !empty($categories) && !is_wp_error($categories)) {
            $selected_cat_id = (int) $categories[0]->term_id;
        }

        $existing_pages_map = self::get_existing_popular_pages_map();
        $current_cat_urls = isset($existing_pages_map[$selected_cat_id]) ? $existing_pages_map[$selected_cat_id] : array();

        $selected_cat_term = ($selected_cat_id > 0) ? get_term($selected_cat_id, 'popular_category') : null;
        $selected_cat_name = ($selected_cat_term && !is_wp_error($selected_cat_term)) ? $selected_cat_term->name : '';

        // Notices
        $msg = isset($_GET['msg']) ? sanitize_text_field($_GET['msg']) : '';
        $count = isset($_GET['count']) ? (int) $_GET['count'] : 0;
        ?>
        <div class="wrap" style="max-width: 1280px;">
            <h1 class="wp-heading-inline">⚡ Быстрое управление рубриками и ссылками «Популярные услуги»</h1>
            <hr class="wp-header-end">

            <!-- Alerts -->
            <?php if ($msg === 'cat_created') : ?>
                <div class="notice notice-success is-dismissible" style="margin-top: 15px;">
                    <p><strong>✓ Новая рубрика успешно создана и выбрана!</strong> Теперь вы можете добавить в неё ссылки ниже.</p>
                </div>
            <?php elseif ($msg === 'cat_renamed') : ?>
                <div class="notice notice-success is-dismissible" style="margin-top: 15px;">
                    <p><strong>✓ Рубрика успешно переименована!</strong></p>
                </div>
            <?php elseif ($msg === 'cat_deleted') : ?>
                <div class="notice notice-success is-dismissible" style="margin-top: 15px;">
                    <p><strong>✓ Рубрика успешно удалена.</strong></p>
                </div>
            <?php elseif ($msg === 'page_deleted') : ?>
                <div class="notice notice-success is-dismissible" style="margin-top: 15px;">
                    <p><strong>✓ Ссылка удалена из рубрики.</strong></p>
                </div>
            <?php elseif ($msg === 'custom_link_added') : ?>
                <div class="notice notice-success is-dismissible" style="margin-top: 15px;">
                    <p><strong>✓ Произвольная ссылка успешно добавлена в рубрику!</strong></p>
                </div>
            <?php elseif ($msg === 'bulk_success') : ?>
                <div class="notice notice-success is-dismissible" style="margin-top: 15px;">
                    <p>
                        <strong>✓ Успешно добавлено <?php echo $count; ?> страниц в рубрику «<?php echo esc_html($selected_cat_name); ?>»!</strong>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=popular_page')); ?>" style="margin-left: 10px;">
                            Перейти ко всем ссылкам →
                        </a>
                    </p>
                </div>
            <?php elseif ($msg === 'no_category') : ?>
                <div class="notice notice-error is-dismissible" style="margin-top: 15px;">
                    <p><strong>Пожалуйста, сначала выберите или создайте рубрику.</strong></p>
                </div>
            <?php elseif ($msg === 'no_pages_selected') : ?>
                <div class="notice notice-warning is-dismissible" style="margin-top: 15px;">
                    <p><strong>Вы не отметили галочками ни одной ссылки для добавления.</strong></p>
                </div>
            <?php endif; ?>

            <!-- BLOCK 1: RUBRICS (COLUMNS) -->
            <div style="background: #ffffff; border: 1px solid #cbd5e1; padding: 22px; margin-top: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 18px;">
                    <div>
                        <h2 style="margin: 0 0 6px 0; font-size: 18px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                            📁 Рубрики (колонки блока на сайте)
                        </h2>
                        <p style="margin: 0; color: #64748b; font-size: 13px;">
                            Каждая рубрика — это колонка в блоке «Популярные услуги» на главной странице. Выберите рубрику кликом, чтобы управлять её ссылками, переименовать или удалить.
                        </p>
                    </div>

                    <!-- Create Rubric Form -->
                    <form method="post" action="" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                        <?php wp_nonce_field('bis_rubrics_nonce_action', 'bis_rubrics_nonce'); ?>
                        <input type="hidden" name="bis_action" value="create_category">
                        <input
                            type="text"
                            name="new_category_name"
                            placeholder="Название новой рубрики..."
                            required
                            style="min-width: 260px; height: 36px; padding: 0 10px; font-size: 13px; border: 1px solid #94a3b8;"
                        >
                        <button type="submit" class="button button-primary" style="height: 36px; line-height: 34px; padding: 0 14px; font-weight: 600;">
                            ➕ Создать рубрику
                        </button>
                    </form>
                </div>

                <!-- Rubrics Grid / Cards -->
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px;">
                    <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
                        <?php foreach ($categories as $cat) :
                            $is_active = ($cat->term_id == $selected_cat_id);
                            $delete_nonce = wp_create_nonce('bis_delete_category_action');
                            $delete_url = add_query_arg(array(
                                'page'        => 'bis-popular-quick-add',
                                'bis_action'  => 'delete_category',
                                'term_id'     => $cat->term_id,
                                '_wpnonce'    => $delete_nonce,
                            ), admin_url('edit.php?post_type=popular_page'));
                            $select_url = add_query_arg(array(
                                'page'         => 'bis-popular-quick-add',
                                'selected_cat' => $cat->term_id,
                            ), admin_url('edit.php?post_type=popular_page'));
                            ?>
                            <div
                                class="bis-rubric-card"
                                id="rubric_card_<?php echo $cat->term_id; ?>"
                                style="border: 2px solid <?php echo $is_active ? '#167b88' : '#e2e8f0'; ?>; background: <?php echo $is_active ? '#ecfeff' : '#f8fafc'; ?>; padding: 12px 14px; transition: all 0.2s;"
                            >
                                <!-- View Mode -->
                                <div class="bis-rubric-view" id="rubric_view_<?php echo $cat->term_id; ?>" style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <a
                                            href="<?php echo esc_url($select_url); ?>"
                                            style="text-decoration: none; font-size: 15px; font-weight: 700; color: <?php echo $is_active ? '#167b88' : '#0f172a'; ?>; flex: 1; margin-right: 8px;"
                                        >
                                            <?php if ($is_active) : ?><span style="color: #167b88; margin-right: 4px;">●</span><?php endif; ?>
                                            <?php echo esc_html($cat->name); ?>
                                        </a>
                                        <span style="font-size: 12px; font-weight: 600; padding: 2px 8px; background: <?php echo $is_active ? '#cffafe' : '#e2e8f0'; ?>; color: <?php echo $is_active ? '#155e75' : '#475569'; ?>;">
                                            <?php echo (int) $cat->count; ?> стр.
                                        </span>
                                    </div>

                                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid <?php echo $is_active ? '#a5f3fc' : '#e2e8f0'; ?>; padding-top: 8px; margin-top: 4px;">
                                        <?php if ($is_active) : ?>
                                            <span style="font-size: 12px; color: #167b88; font-weight: 700;">✓ Выбрана</span>
                                        <?php else : ?>
                                            <a href="<?php echo esc_url($select_url); ?>" class="button button-small" style="font-size: 11px;">
                                                Выбрать
                                            </a>
                                        <?php endif; ?>

                                        <div style="display: flex; gap: 4px;">
                                            <button
                                                type="button"
                                                class="button button-small"
                                                onclick="bisShowRenameForm(<?php echo $cat->term_id; ?>)"
                                                title="Переименовать"
                                                style="padding: 0 6px;"
                                            >
                                                ✏️
                                            </button>
                                            <a
                                                href="<?php echo esc_url($delete_url); ?>"
                                                class="button button-small"
                                                onclick="return confirm('Удалить рубрику «<?php echo esc_js($cat->name); ?>»? Привязанные к ней ссылки останутся, но потеряют эту категорию.');"
                                                title="Удалить рубрику"
                                                style="padding: 0 6px; color: #dc2626;"
                                            >
                                                🗑️
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Inline Rename Form -->
                                <form
                                    method="post"
                                    action=""
                                    class="bis-rubric-edit-form"
                                    id="rubric_edit_<?php echo $cat->term_id; ?>"
                                    style="display: none; flex-direction: column; gap: 6px;"
                                >
                                    <?php wp_nonce_field('bis_rubrics_nonce_action', 'bis_rubrics_nonce'); ?>
                                    <input type="hidden" name="bis_action" value="rename_category">
                                    <input type="hidden" name="term_id" value="<?php echo $cat->term_id; ?>">
                                    <label style="font-size: 12px; font-weight: 600; color: #334155;">Переименовать:</label>
                                    <input
                                        type="text"
                                        name="updated_name"
                                        value="<?php echo esc_attr($cat->name); ?>"
                                        required
                                        style="width: 100%; height: 30px; font-size: 13px;"
                                    >
                                    <div style="display: flex; gap: 6px; justify-content: flex-end; margin-top: 2px;">
                                        <button type="submit" class="button button-small button-primary">Сохранить</button>
                                        <button type="button" class="button button-small" onclick="bisHideRenameForm(<?php echo $cat->term_id; ?>)">Отмена</button>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div style="grid-column: 1 / -1; padding: 20px; text-align: center; color: #64748b; background: #f8fafc; border: 1px dashed #cbd5e1;">
                            У вас пока нет рубрик. Введите название рубрики в поле выше (например, <strong>«Вентиляция и кондиционирование»</strong>) и нажмите «Создать рубрику».
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- BLOCK 2: ADDING LINKS TO SELECTED RUBRIC -->
            <?php if ($selected_cat_id > 0 && !empty($selected_cat_name)) : ?>
                <div style="background: #ffffff; border: 1px solid #cbd5e1; padding: 22px; margin-top: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <!-- Section Header -->
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; border-bottom: 2px solid #e2e8f0; padding-bottom: 14px;">
                        <div>
                            <h2 style="margin: 0; font-size: 18px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                                ⚡ Управление ссылками рубрики: <span style="color: #167b88;">«<?php echo esc_html($selected_cat_name); ?>»</span>
                            </h2>
                            <p style="margin: 4px 0 0 0; color: #64748b; font-size: 13px;">
                                Отметьте галочками существующие страницы/услуги сайта или добавьте произвольную внешнюю/внутреннюю ссылку.
                            </p>
                        </div>
                    </div>

                    <!-- SUB-BLOCK: Add Custom Link Directly -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px 16px; margin-bottom: 20px;">
                        <h3 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 700; color: #1e293b;">
                            ✍️ Добавить произвольную ссылку в эту рубрику
                        </h3>
                        <form method="post" action="" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
                            <?php wp_nonce_field('bis_custom_link_action', 'bis_custom_link_nonce'); ?>
                            <input type="hidden" name="bis_action" value="add_custom_link">
                            <input type="hidden" name="target_category_id" value="<?php echo (int) $selected_cat_id; ?>">

                            <div style="flex: 1; min-width: 220px;">
                                <label style="display: block; font-size: 12px; color: #475569; margin-bottom: 4px;">Анкор ссылки (название):</label>
                                <input type="text" name="custom_link_title" placeholder="например, Паспортизация систем вентиляции" required style="width: 100%; height: 34px;">
                            </div>
                            <div style="flex: 1.5; min-width: 260px;">
                                <label style="display: block; font-size: 12px; color: #475569; margin-bottom: 4px;">Целевой URL:</label>
                                <input type="text" name="custom_link_url" placeholder="/services/pasportizaciya/ или https://..." required style="width: 100%; height: 34px;">
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px; padding-bottom: 6px;">
                                <label style="font-size: 12px; color: #475569; cursor: pointer;">
                                    <input type="checkbox" name="custom_link_target_blank" value="1">
                                    в новой вкладке
                                </label>
                            </div>
                            <button type="submit" class="button button-primary" style="height: 34px; line-height: 32px;">
                                ➕ Добавить
                            </button>
                        </form>
                    </div>

                    <!-- BULK SELECTOR FORM -->
                    <form method="post" action="">
                        <?php wp_nonce_field('bis_bulk_add_action', 'bis_bulk_add_nonce'); ?>
                        <input type="hidden" name="bis_action" value="bulk_add_pages">
                        <input type="hidden" name="target_category_id" value="<?php echo (int) $selected_cat_id; ?>">

                        <!-- Search and Filter Tabs -->
                        <div style="display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap; margin-bottom: 14px;">
                            <!-- Search -->
                            <div style="flex: 1; min-width: 280px;">
                                <input
                                    type="text"
                                    id="bis_bulk_search"
                                    placeholder="🔍 Быстрый поиск по названию страницы, услуги или раздела..."
                                    style="width: 100%; max-width: 500px; height: 38px; padding: 0 12px; font-size: 14px; border: 1px solid #94a3b8;"
                                >
                            </div>

                            <!-- Filter tabs -->
                            <div id="bis_type_filter" style="display: flex; gap: 6px; flex-wrap: wrap;">
                                <button type="button" class="button button-primary" data-filter="all">Все</button>
                                <button type="button" class="button" data-filter="Услуга БИС">Услуги БИС</button>
                                <button type="button" class="button" data-filter="Проект БИС">Проекты</button>
                                <button type="button" class="button" data-filter="Раздел сайта">Разделы</button>
                                <button type="button" class="button" data-filter="Страница">Страницы</button>
                                <button type="button" class="button" data-filter="Новость/Статья">Медиа</button>
                            </div>
                        </div>

                        <!-- Select all toggle bar -->
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: #f1f5f9; border: 1px solid #cbd5e1; border-bottom: none;">
                            <label style="font-weight: 600; font-size: 13px; cursor: pointer; user-select: none;">
                                <input type="checkbox" id="bis_select_all_visible" style="margin-right: 8px;">
                                Выбрать все видимые страницы
                            </label>
                            <span id="bis_selected_counter" style="font-size: 13px; font-weight: 700; color: #167b88;">
                                Выбрано для добавления: 0
                            </span>
                        </div>

                        <!-- Items Table -->
                        <div style="max-height: 500px; overflow-y: auto; border: 1px solid #cbd5e1;">
                            <table class="wp-list-table widefat fixed striped" style="margin: 0; border: none;">
                                <thead>
                                    <tr>
                                        <th style="width: 48px; text-align: center;"></th>
                                        <th style="width: 45%;">Название страницы / услуги</th>
                                        <th style="width: 18%;">Тип</th>
                                        <th style="width: 25%;">Ссылка (URL)</th>
                                        <th style="width: 12%;">Статус</th>
                                    </tr>
                                </thead>
                                <tbody id="bis_items_table_body">
                                    <?php
                                    foreach ($linkable_groups as $group) :
                                        foreach ($group['items'] as $item) :
                                            $json_value = wp_json_encode(array(
                                                'title' => $item['title'],
                                                'url'   => $item['url'],
                                            ));

                                            $already_post_id = isset($current_cat_urls[$item['url']]) ? $current_cat_urls[$item['url']] : 0;
                                            $del_page_nonce = wp_create_nonce('bis_delete_page_action');
                                            $del_page_url = add_query_arg(array(
                                                'page'         => 'bis-popular-quick-add',
                                                'bis_action'   => 'delete_page',
                                                'post_id'      => $already_post_id,
                                                'selected_cat' => $selected_cat_id,
                                                '_wpnonce'     => $del_page_nonce,
                                            ), admin_url('edit.php?post_type=popular_page'));
                                            ?>
                                            <tr class="bis-item-row" data-type="<?php echo esc_attr($item['type']); ?>" data-title="<?php echo esc_attr(mb_strtolower($item['title'])); ?>">
                                                <td style="text-align: center;">
                                                    <input
                                                        type="checkbox"
                                                        name="selected_pages[]"
                                                        value="<?php echo esc_attr($json_value); ?>"
                                                        class="bis-item-checkbox"
                                                        <?php if ($already_post_id > 0) : ?>disabled style="opacity: 0.4;"<?php endif; ?>
                                                    >
                                                </td>
                                                <td>
                                                    <strong><?php echo esc_html($item['title']); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge" style="background: #e2e8f0; padding: 3px 8px; font-size: 12px;">
                                                        <?php echo esc_html($item['type']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="<?php echo esc_url($item['url']); ?>" target="_blank" style="color: #167b88; text-decoration: underline; font-size: 12px; word-break: break-all;">
                                                        <?php echo esc_html($item['url']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php if ($already_post_id > 0) : ?>
                                                        <span style="color: #16a34a; font-weight: 600; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">
                                                            ✓ В рубрике
                                                            <a href="<?php echo esc_url($del_page_url); ?>" onclick="return confirm('Удалить эту ссылку из рубрики?');" style="color: #dc2626; text-decoration: none; font-size: 14px; font-weight: bold;" title="Удалить из рубрики">✕</a>
                                                        </span>
                                                    <?php else : ?>
                                                        <span style="color: #64748b; font-size: 12px;">Доступно</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php
                                        endforeach;
                                    endforeach;
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Submit bar -->
                        <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b; font-size: 13px;">
                                Отметьте галочками страницы и нажмите кнопку добавления.
                            </span>
                            <button type="submit" class="button button-primary button-large" style="font-weight: 600; padding: 0 20px;">
                                ➕ Добавить выбранные страницы в рубрику «<?php echo esc_html($selected_cat_name); ?>»
                            </button>
                        </div>
                    </form>
                </div>
            <?php else : ?>
                <div style="background: #ffffff; border: 1px solid #cbd5e1; padding: 30px; margin-top: 24px; text-align: center;">
                    <p style="font-size: 15px; color: #475569; margin: 0;">
                        👈 Чтобы добавить ссылки, сначала создайте или выберите рубрику в блоке выше.
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <script>
        function bisShowRenameForm(termId) {
            const view = document.getElementById('rubric_view_' + termId);
            const edit = document.getElementById('rubric_edit_' + termId);
            if (view && edit) {
                view.style.display = 'none';
                edit.style.display = 'flex';
                const input = edit.querySelector('input[name="updated_name"]');
                if (input) input.focus();
            }
        }

        function bisHideRenameForm(termId) {
            const view = document.getElementById('rubric_view_' + termId);
            const edit = document.getElementById('rubric_edit_' + termId);
            if (view && edit) {
                edit.style.display = 'none';
                view.style.display = 'flex';
            }
        }

        (function() {
            const searchInput = document.getElementById('bis_bulk_search');
            const filterBtns = document.querySelectorAll('#bis_type_filter button');
            const rows = document.querySelectorAll('#bis_items_table_body tr');
            const selectAll = document.getElementById('bis_select_all_visible');
            const counter = document.getElementById('bis_selected_counter');
            const checkboxes = document.querySelectorAll('.bis-item-checkbox:not(:disabled)');

            let currentType = 'all';
            let currentSearch = '';

            function updateFilters() {
                rows.forEach(function(row) {
                    const type = row.getAttribute('data-type');
                    const title = row.getAttribute('data-title');

                    const matchesType = (currentType === 'all' || type === currentType);
                    const matchesSearch = (!currentSearch || title.includes(currentSearch));

                    if (matchesType && matchesSearch) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
                updateCounter();
            }

            function updateCounter() {
                let count = 0;
                checkboxes.forEach(function(cb) {
                    if (cb.checked) count++;
                });
                if (counter) counter.textContent = 'Выбрано для добавления: ' + count;
            }

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    currentSearch = searchInput.value.toLowerCase().trim();
                    updateFilters();
                });
            }

            filterBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    filterBtns.forEach(b => b.classList.remove('button-primary'));
                    btn.classList.add('button-primary');
                    currentType = btn.getAttribute('data-filter');
                    updateFilters();
                });
            });

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    const isChecked = selectAll.checked;
                    rows.forEach(function(row) {
                        if (row.style.display !== 'none') {
                            const cb = row.querySelector('.bis-item-checkbox:not(:disabled)');
                            if (cb) cb.checked = isChecked;
                        }
                    });
                    updateCounter();
                });
            }

            checkboxes.forEach(function(cb) {
                cb.addEventListener('change', updateCounter);
            });
        })();
        </script>
        <?php
    }

    /**
     * Customize Admin Columns
     */
    public function customize_columns($columns)
    {
        $new_columns = array();
        foreach ($columns as $key => $title) {
            if ($key === 'date') {
                $new_columns['target_url'] = 'Целевая ссылка';
                $new_columns['menu_order'] = 'Порядок';
            }
            $new_columns[$key] = $title;
        }

        if (!isset($new_columns['target_url'])) {
            $new_columns['target_url'] = 'Целевая ссылка';
            $new_columns['menu_order'] = 'Порядок';
        }

        return $new_columns;
    }

    /**
     * Render Column Content
     */
    public function render_column_content($column, $post_id)
    {
        if ($column === 'target_url') {
            $url = get_post_meta($post_id, 'bis_popular_page_url', true);
            if (empty($url)) {
                $url = get_post_meta($post_id, 'ecg_popular_page_url', true);
            }
            if (!empty($url)) {
                $rel_url = function_exists('bis_make_url_relative') ? bis_make_url_relative($url) : $url;
                echo '<a href="' . esc_attr($rel_url) . '" target="_blank" style="color: #167b88; text-decoration: underline;">' . esc_html($rel_url) . '</a>';
            } else {
                echo '<span style="color: #94a3b8;">По умолчанию (внутр.)</span>';
            }
        } elseif ($column === 'menu_order') {
            $post = get_post($post_id);
            echo esc_html($post->menu_order);
        }
    }

    /**
     * Sortable Columns
     */
    public function sortable_columns($columns)
    {
        $columns['menu_order'] = 'menu_order';
        return $columns;
    }

    /**
     * Helper to retrieve popular categories sorted by bis_popular_category_order ASC, then name ASC.
     *
     * @param array $args Optional get_terms arguments.
     * @return WP_Term[]
     */
    public static function get_ordered_categories($args = array())
    {
        $default_args = array(
            'taxonomy'   => 'popular_category',
            'hide_empty' => false,
        );
        $query_args = wp_parse_args($args, $default_args);
        $terms = get_terms($query_args);

        if (empty($terms) || is_wp_error($terms)) {
            return array();
        }

        usort($terms, function ($a, $b) {
            $order_a = get_term_meta($a->term_id, 'bis_popular_category_order', true);
            if ($order_a === '' || $order_a === false) {
                $order_a = get_term_meta($a->term_id, 'ecg_popular_category_order', true);
            }
            $order_b = get_term_meta($b->term_id, 'bis_popular_category_order', true);
            if ($order_b === '' || $order_b === false) {
                $order_b = get_term_meta($b->term_id, 'ecg_popular_category_order', true);
            }

            $num_a = ($order_a !== '' && $order_a !== false) ? (int) $order_a : 9999;
            $num_b = ($order_b !== '' && $order_b !== false) ? (int) $order_b : 9999;

            if ($num_a !== $num_b) {
                return $num_a <=> $num_b;
            }
            return strcasecmp($a->name, $b->name);
        });

        return $terms;
    }

    /**
     * Handle saving popular categories order (Admin Post)
     */
    public function handle_save_category_order()
    {
        if (!current_user_can('edit_posts')) {
            wp_die('Недостаточно прав.');
        }
        check_admin_referer('bis_save_popular_category_order');

        $term_ids = isset($_POST['bis_category_order']) && is_array($_POST['bis_category_order'])
            ? array_values(array_unique(array_filter(array_map('absint', wp_unslash($_POST['bis_category_order'])))))
            : array();

        foreach ($term_ids as $index => $term_id) {
            $val = ($index + 1) * 10;
            update_term_meta($term_id, 'bis_popular_category_order', $val);
            update_term_meta($term_id, 'ecg_popular_category_order', $val);
        }

        $category_id = isset($_POST['category_id']) ? absint($_POST['category_id']) : 0;
        wp_safe_redirect(add_query_arg(array(
            'post_type'   => 'popular_page',
            'page'        => 'bis_popular_order',
            'category_id' => $category_id,
            'saved'       => 'categories',
        ), admin_url('edit.php')));
        exit;
    }

    /**
     * Handle saving popular pages order (Admin Post)
     */
    public function handle_save_page_order()
    {
        global $wpdb;

        if (!current_user_can('edit_posts')) {
            wp_die('Недостаточно прав.');
        }
        check_admin_referer('bis_save_popular_page_order');

        $submitted_ids = isset($_POST['bis_page_order']) && is_array($_POST['bis_page_order'])
            ? array_values(array_unique(array_filter(array_map('absint', wp_unslash($_POST['bis_page_order'])))))
            : array();

        foreach ($submitted_ids as $index => $post_id) {
            $order_val = ($index + 1) * 10;
            $wpdb->update(
                $wpdb->posts,
                array('menu_order' => $order_val),
                array('ID' => $post_id),
                array('%d'),
                array('%d')
            );
            update_post_meta($post_id, 'menu_order', $order_val);
            clean_post_cache($post_id);
        }

        $category_id = isset($_POST['category_id']) ? absint($_POST['category_id']) : 0;
        wp_safe_redirect(add_query_arg(array(
            'post_type'   => 'popular_page',
            'page'        => 'bis_popular_order',
            'category_id' => $category_id,
            'saved'       => 'pages',
        ), admin_url('edit.php')));
        exit;
    }

    /**
     * Render Two-Column Drag & Drop Order Page
     */
    public function render_order_page()
    {
        $categories = self::get_ordered_categories(array('hide_empty' => false));
        $selected_category_id = isset($_GET['category_id']) ? absint($_GET['category_id']) : 0;

        if ($selected_category_id === 0 && !empty($categories)) {
            $selected_category_id = (int) $categories[0]->term_id;
        }

        $selected_category = ($selected_category_id > 0) ? get_term($selected_category_id, 'popular_category') : null;

        $pages_query = null;
        if ($selected_category && !is_wp_error($selected_category)) {
            $pages_query = new WP_Query(array(
                'post_type'      => 'popular_page',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => array('menu_order' => 'ASC', 'title' => 'ASC'),
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'popular_category',
                        'field'    => 'term_id',
                        'terms'    => $selected_category_id,
                    ),
                ),
            ));
        }

        $saved = isset($_GET['saved']) ? sanitize_key($_GET['saved']) : '';
        ?>
        <div class="wrap bis-order-wrap">
            <div class="bis-order-header">
                <h1>
                    <span class="dashicons dashicons-sort" style="font-size: 26px; width: 26px; height: 26px;"></span>
                    Порядок популярных услуг и рубрик
                </h1>
                <p>
                    Перетаскивайте блоки мышью за ручку <code>☰</code> для настройки структуры блока «Популярные услуги» на сайте.
                    Слева задается порядок колонок-рубрик (слева направо), а справа — порядок ссылок внутри выбранной рубрики (сверху вниз).
                </p>
            </div>

            <?php if ($saved === 'categories') : ?>
                <div class="bis-order-notice bis-order-notice--success">
                    <span><strong>✓ Порядок рубрик успешно сохранен!</strong> Колонки блока «Популярные услуги» обновлены.</span>
                </div>
            <?php elseif ($saved === 'pages') : ?>
                <div class="bis-order-notice bis-order-notice--success">
                    <span><strong>✓ Порядок ссылок в рубрике успешно сохранен!</strong> Ссылки в блоке обновлены.</span>
                </div>
            <?php endif; ?>

            <div class="bis-order-layout">
                <!-- Left Column: Categories Order -->
                <div class="bis-order-card">
                    <div class="bis-order-card-header">
                        <div>
                            <h2 class="bis-order-card-title">
                                <span class="dashicons dashicons-category"></span>
                                Рубрики (колонки)
                            </h2>
                            <div class="bis-order-card-subtitle">Выводятся на сайте слева направо</div>
                        </div>
                        <span class="bis-order-badge bis-order-badge--primary"><?php echo count($categories); ?> рубр.</span>
                    </div>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('bis_save_popular_category_order'); ?>
                        <input type="hidden" name="action" value="bis_save_popular_category_order">
                        <input type="hidden" name="category_id" value="<?php echo esc_attr($selected_category_id); ?>">

                        <div class="bis-order-card-body">
                            <?php if (empty($categories)) : ?>
                                <div class="bis-order-empty">
                                    <p>Рубрики пока не созданы.</p>
                                    <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=popular_category&post_type=popular_page')); ?>" class="button button-small" style="margin-top: 10px;">Создать рубрику</a>
                                </div>
                            <?php else : ?>
                                <div class="bis-order-list" data-drag-sort data-row-selector=".bis-order-row">
                                    <?php foreach ($categories as $index => $cat) : 
                                        $is_active = ($cat->term_id === $selected_category_id);
                                        $cat_order = (int) get_term_meta($cat->term_id, 'bis_popular_category_order', true);
                                    ?>
                                        <div class="bis-order-row <?php echo $is_active ? 'is-selected' : ''; ?>" data-id="<?php echo esc_attr($cat->term_id); ?>">
                                            <span class="bis-drag-handle" title="Перетащите для изменения порядка">&#9776;</span>
                                            <span class="bis-order-num"><?php echo ($index + 1); ?></span>
                                            <input type="hidden" name="bis_category_order[]" value="<?php echo esc_attr($cat->term_id); ?>">
                                            <div class="bis-order-row-main">
                                                <div class="bis-order-row-title"><?php echo esc_html($cat->name); ?></div>
                                                <div class="bis-order-row-meta">
                                                    Ссылок: <?php echo (int) $cat->count; ?>
                                                    <?php if ($cat_order > 0) : ?>
                                                        &bull; шаг: <?php echo esc_html($cat_order); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="bis-order-row-actions">
                                                <a href="<?php echo esc_url(add_query_arg(array('post_type' => 'popular_page', 'page' => 'bis_popular_order', 'category_id' => $cat->term_id), admin_url('edit.php'))); ?>" class="bis-btn-secondary button-small" style="<?php echo $is_active ? 'background:#167b88!important;border-color:#167b88!important;color:#fff!important;' : ''; ?>">
                                                    <?php echo $is_active ? '✓ Выбрана' : 'Выбрать'; ?>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($categories)) : ?>
                            <div class="bis-order-card-footer">
                                <button type="submit" class="bis-btn-save">
                                    <span class="dashicons dashicons-saved" style="margin-top:2px;"></span>
                                    Сохранить порядок рубрик
                                </button>
                                <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=popular_category&post_type=popular_page')); ?>" class="bis-btn-secondary">
                                    Все рубрики
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Right Column: Pages Order in Selected Category -->
                <div class="bis-order-card">
                    <div class="bis-order-card-header">
                        <div>
                            <h2 class="bis-order-card-title">
                                <span class="dashicons dashicons-admin-links"></span>
                                Ссылки в рубрике: <?php echo $selected_category ? esc_html($selected_category->name) : '—'; ?>
                            </h2>
                            <div class="bis-order-card-subtitle">Выводятся внутри колонки сверху вниз</div>
                        </div>
                        <?php if ($pages_query && $pages_query->have_posts()) : ?>
                            <span class="bis-order-badge bis-order-badge--primary"><?php echo $pages_query->post_count; ?> ссылок</span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($categories)) : ?>
                        <div style="padding: 12px 20px; background: #f1f5f9; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <label for="bis_cat_switcher" style="font-weight: 600; font-size: 13px; color: #334155;">Переключить рубрику:</label>
                                <select id="bis_cat_switcher" class="bis-order-select" data-bis-category-filter data-base-url="<?php echo esc_url(admin_url('edit.php?post_type=popular_page&page=bis_popular_order')); ?>" data-param-name="category_id">
                                    <?php foreach ($categories as $c) : ?>
                                        <option value="<?php echo esc_attr($c->term_id); ?>" <?php selected($c->term_id, $selected_category_id); ?>>
                                            <?php echo esc_html($c->name); ?> (<?php echo (int) $c->count; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <a href="<?php echo esc_url(admin_url('edit.php?post_type=popular_page&page=bis-popular-quick-add&selected_cat=' . $selected_category_id)); ?>" class="bis-btn-secondary" style="font-size: 13px; padding: 5px 12px;">
                                    ⚡ Быстрое добавление
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('bis_save_popular_page_order'); ?>
                        <input type="hidden" name="action" value="bis_save_popular_page_order">
                        <input type="hidden" name="category_id" value="<?php echo esc_attr($selected_category_id); ?>">

                        <div class="bis-order-card-body">
                            <?php if (!$pages_query || !$pages_query->have_posts()) : ?>
                                <div class="bis-order-empty">
                                    <span class="dashicons dashicons-info" style="font-size: 32px; width: 32px; height: 32px; color: #cbd5e1; margin-bottom: 8px;"></span>
                                    <p>В этой рубрике пока нет ссылок.</p>
                                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=popular_page&page=bis-popular-quick-add&selected_cat=' . $selected_category_id)); ?>" class="button button-primary" style="margin-top: 12px;">
                                        ⚡ Добавить ссылки через быстрое управление
                                    </a>
                                </div>
                            <?php else : ?>
                                <div class="bis-order-list" data-drag-sort data-row-selector=".bis-order-row">
                                    <?php $p_idx = 0; while ($pages_query->have_posts()) : $pages_query->the_post(); $p_idx++;
                                        $post_id = get_the_ID();
                                        $url = get_post_meta($post_id, 'bis_popular_page_url', true);
                                        if (empty($url)) {
                                            $url = get_post_meta($post_id, 'ecg_popular_page_url', true);
                                        }
                                        $rel_url = !empty($url) ? (function_exists('bis_make_url_relative') ? bis_make_url_relative($url) : $url) : get_permalink($post_id);
                                        $target_blank = (bool) get_post_meta($post_id, 'bis_popular_page_target_blank', true);
                                    ?>
                                        <div class="bis-order-row" data-id="<?php echo esc_attr($post_id); ?>">
                                            <span class="bis-drag-handle" title="Перетащите для изменения порядка">&#9776;</span>
                                            <span class="bis-order-num"><?php echo $p_idx; ?></span>
                                            <input type="hidden" name="bis_page_order[]" value="<?php echo esc_attr($post_id); ?>">
                                            <div class="bis-order-row-main">
                                                <div class="bis-order-row-title">
                                                    <?php the_title(); ?>
                                                    <?php if ($target_blank) : ?>
                                                        <span title="Открывается в новой вкладке" style="font-size: 11px; color: #94a3b8; font-weight: normal;">↗</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="bis-order-row-meta">
                                                    <a href="<?php echo esc_url($rel_url); ?>" target="_blank"><?php echo esc_html($rel_url); ?></a>
                                                </div>
                                            </div>
                                            <div class="bis-order-row-actions">
                                                <a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>" class="bis-btn-secondary" style="font-size: 12px; padding: 4px 10px;" target="_blank">
                                                    Изменить
                                                </a>
                                            </div>
                                        </div>
                                    <?php endwhile; wp_reset_postdata(); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($pages_query && $pages_query->have_posts()) : ?>
                            <div class="bis-order-card-footer">
                                <button type="submit" class="bis-btn-save">
                                    <span class="dashicons dashicons-saved" style="margin-top:2px;"></span>
                                    Сохранить порядок страниц
                                </button>
                                <a href="<?php echo esc_url(admin_url('edit.php?post_type=popular_page&page=bis-popular-quick-add&selected_cat=' . $selected_category_id)); ?>" class="bis-btn-secondary">
                                    ⚡ Быстрое добавление
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Add Term Order Field to Popular Category Add Screen
     */
    public function add_category_order_field()
    {
        ?>
        <div class="form-field term-order-wrap">
            <label for="bis_popular_category_order">Порядок (сортировка)</label>
            <input type="number" name="bis_popular_category_order" id="bis_popular_category_order" value="0" min="0" step="10">
            <p>Позиция рубрики в блоке (10, 20, 30...). Меньшее число отображается левее.</p>
        </div>
        <?php
    }

    /**
     * Add Term Order Field to Popular Category Edit Screen
     */
    public function edit_category_order_field($term)
    {
        $order = get_term_meta($term->term_id, 'bis_popular_category_order', true);
        if ($order === '' || $order === false) {
            $order = get_term_meta($term->term_id, 'ecg_popular_category_order', true);
        }
        ?>
        <tr class="form-field term-order-wrap">
            <th scope="row"><label for="bis_popular_category_order">Порядок (сортировка)</label></th>
            <td>
                <input type="number" name="bis_popular_category_order" id="bis_popular_category_order" value="<?php echo esc_attr((int) $order); ?>" min="0" step="10">
                <p class="description">Позиция рубрики в блоке на сайте (10, 20, 30...). Меньшее число отображается левее.</p>
            </td>
        </tr>
        <?php
    }

    /**
     * Save Term Order Meta
     */
    public function save_category_order_meta($term_id)
    {
        if (isset($_POST['bis_popular_category_order'])) {
            $order = absint($_POST['bis_popular_category_order']);
            update_term_meta($term_id, 'bis_popular_category_order', $order);
            update_term_meta($term_id, 'ecg_popular_category_order', $order);
        }
    }

    /**
     * Category Order Column in Taxonomy Table
     */
    public function category_order_column($columns)
    {
        $new_cols = array();
        foreach ($columns as $k => $v) {
            if ($k === 'posts') {
                $new_cols['bis_order'] = 'Порядок';
            }
            $new_cols[$k] = $v;
        }
        if (!isset($new_cols['bis_order'])) {
            $new_cols['bis_order'] = 'Порядок';
        }
        return $new_cols;
    }

    /**
     * Render Category Order Column Value
     */
    public function render_category_order_column($content, $column_name, $term_id)
    {
        if ($column_name === 'bis_order') {
            $order = get_term_meta($term_id, 'bis_popular_category_order', true);
            if ($order === '' || $order === false) {
                $order = get_term_meta($term_id, 'ecg_popular_category_order', true);
            }
            return '<span class="bis-order-badge">' . esc_html((int) $order) . '</span>';
        }
        return $content;
    }

    /**
     * Make Category Order Column Sortable
     */
    public function category_order_sortable_column($sortable)
    {
        $sortable['bis_order'] = 'bis_order';
        return $sortable;
    }

    /**
     * Single post redirect if custom URL is provided
     */
    public function handle_single_redirect()
    {
        if (is_singular('popular_page')) {
            $post_id = get_the_ID();
            $custom_url = get_post_meta($post_id, 'bis_popular_page_url', true);
            if (empty($custom_url)) {
                $custom_url = get_post_meta($post_id, 'ecg_popular_page_url', true);
            }

            if (!empty($custom_url)) {
                $target = function_exists('bis_make_url_relative') ? bis_make_url_relative($custom_url) : $custom_url;
                wp_safe_redirect(home_url($target), 301);
                exit;
            }
        }
    }

    /**
     * Auto-seed initial popular services from bis_service
     */
    public function maybe_seed_popular_services()
    {
        // Only run if not already seeded
        if (get_option('bis_popular_pages_seeded') === 'yes') {
            return;
        }

        $existing_categories = get_terms(array(
            'taxonomy'   => 'popular_category',
            'hide_empty' => false,
        ));

        // If categories already exist, mark seeded and exit
        if (!empty($existing_categories) && !is_wp_error($existing_categories)) {
            update_option('bis_popular_pages_seeded', 'yes');
            return;
        }

        $default_rubrics = array(
            'Вентиляция и кондиционирование' => array(
                'keywords' => array('вент', 'кондиционер', 'монтаж', 'паспорт', 'аудит', 'наладк'),
                'term_id'  => 0,
            ),
            'Противодымная вентиляция' => array(
                'keywords' => array('дым', 'подпор', 'клапан', 'пд', 'ду', 'противодым'),
                'term_id'  => 0,
            ),
            'Чистка и дезинфекция систем' => array(
                'keywords' => array('чистк', 'дезинфек', 'промыв', 'обработк', 'видеоинспек'),
                'term_id'  => 0,
            ),
            'Автоматика и спецналадка' => array(
                'keywords' => array('автомат', 'асу', 'кип', 'плк', 'электр', 'шкаф', 'щит', 'частотн', 'диспетчер'),
                'term_id'  => 0,
            ),
        );

        // 1. Create default rubrics
        foreach ($default_rubrics as $rubric_name => $info) {
            $inserted = wp_insert_term($rubric_name, 'popular_category');
            if (!is_wp_error($inserted) && isset($inserted['term_id'])) {
                $default_rubrics[$rubric_name]['term_id'] = $inserted['term_id'];
            }
        }

        // 2. Query published services
        $services = get_posts(array(
            'post_type'      => 'bis_service',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
        ));

        if (!empty($services)) {
            foreach ($services as $srv) {
                $t = mb_strtolower($srv->post_title, 'UTF-8');
                $target_cat_name = 'Вентиляция и кондиционирование';

                if (preg_match('/(дым|подпор|клапан|пд|ду\b|противодым)/ui', $t)) {
                    $target_cat_name = 'Противодымная вентиляция';
                } elseif (preg_match('/(чистк|дезинфек|промыв|обработк|видеоинспек)/ui', $t)) {
                    $target_cat_name = 'Чистка и дезинфекция систем';
                } elseif (preg_match('/(автомат|асу|кип|плк|электр|шкаф|щит|частотн|диспетчер)/ui', $t)) {
                    $target_cat_name = 'Автоматика и спецналадка';
                }

                $cat_id = $default_rubrics[$target_cat_name]['term_id'];
                if ($cat_id > 0) {
                    $link_url = bis_make_url_relative(get_permalink($srv->ID));
                    $new_id = wp_insert_post(array(
                        'post_type'   => 'popular_page',
                        'post_status' => 'publish',
                        'post_title'  => $srv->post_title,
                    ));
                    if ($new_id && !is_wp_error($new_id)) {
                        update_post_meta($new_id, 'bis_popular_page_url', $link_url);
                        wp_set_object_terms($new_id, array($cat_id), 'popular_category');
                    }
                }
            }
        }

        update_option('bis_popular_pages_seeded', 'yes');
    }
}

new BIS_Popular_Pages_CPT();

/**
 * Safely convert internal URLs to relative URLs
 */
function bis_make_url_relative($url)
{
    if (empty($url) || !is_string($url)) {
        return (string) $url;
    }

    $trimmed = trim($url);
    if (str_starts_with($trimmed, '/') || str_starts_with($trimmed, '#')) {
        return $trimmed;
    }

    $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
    $url_host = wp_parse_url($trimmed, PHP_URL_HOST);

    if ($url_host && $site_host && strcasecmp($url_host, $site_host) === 0) {
        $path = wp_parse_url($trimmed, PHP_URL_PATH) ?: '/';
        $query = wp_parse_url($trimmed, PHP_URL_QUERY);
        $fragment = wp_parse_url($trimmed, PHP_URL_FRAGMENT);

        $result = $path;
        if (!empty($query)) $result .= '?' . $query;
        if (!empty($fragment)) $result .= '#' . $fragment;
        return $result;
    }

    return $trimmed;
}

/**
 * Get popular pages / services grouped by category/rubric.
 *
 * @return array<int, array{
 *     term: WP_Term|null,
 *     title: string,
 *     posts: array<int, array{
 *         id: int,
 *         title: string,
 *         url: string,
 *         target_blank: bool
 *     }>
 * }>
 */
function bis_get_popular_pages_grouped()
{
    $terms = BIS_Popular_Pages_CPT::get_ordered_categories(array('hide_empty' => true));

    $grouped = array();

    if (!empty($terms) && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            $posts_query = new WP_Query(array(
                'post_type'      => 'popular_page',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => array('menu_order' => 'ASC', 'title' => 'ASC'),
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'popular_category',
                        'field'    => 'term_id',
                        'terms'    => $term->term_id,
                    ),
                ),
            ));

            if (!$posts_query->have_posts()) {
                continue;
            }

            $items = array();
            while ($posts_query->have_posts()) {
                $posts_query->the_post();
                $post_id = get_the_ID();
                $custom_url = get_post_meta($post_id, 'bis_popular_page_url', true);
                if (empty($custom_url)) {
                    $custom_url = get_post_meta($post_id, 'ecg_popular_page_url', true);
                }
                $raw_url = !empty($custom_url) ? $custom_url : get_permalink($post_id);
                $url = bis_make_url_relative($raw_url);
                $target_blank = (bool) get_post_meta($post_id, 'bis_popular_page_target_blank', true);
                if (!$target_blank) {
                    $target_blank = (bool) get_post_meta($post_id, 'ecg_popular_page_target_blank', true);
                }

                $items[] = array(
                    'id'           => $post_id,
                    'title'        => get_the_title($post_id),
                    'url'          => $url,
                    'target_blank' => $target_blank,
                );
            }
            wp_reset_postdata();

            if (!empty($items)) {
                $grouped[] = array(
                    'term'  => $term,
                    'title' => $term->name,
                    'posts' => $items,
                );
            }
        }
    }

    return $grouped;
}

/**
 * Shortcode to render popular services block anywhere: [bis_popular_services]
 */
function bis_popular_services_shortcode($atts = array())
{
    ob_start();
    get_template_part('template-parts/popular-pages');
    return ob_get_clean();
}
add_shortcode('bis_popular_services', 'bis_popular_services_shortcode');
add_shortcode('ecg_popular_pages', 'bis_popular_services_shortcode');
