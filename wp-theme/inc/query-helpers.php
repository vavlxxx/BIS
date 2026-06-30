<?php

function bis_build_public_query_args($post_type, array $args = array()) {
    $defaults = array(
        'post_type'           => $post_type,
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
    );

    return wp_parse_args($args, $defaults);
}

function bis_build_collection_query_args($post_type, array $args = array()) {
    return bis_build_public_query_args($post_type, wp_parse_args($args, array(
        'no_found_rows' => true,
    )));
}

function bis_get_news_archive_url() {
    $archive_url = get_post_type_archive_link('bis_news');
    return $archive_url ? $archive_url : home_url('/news/');
}

function bis_get_news_filter_state() {
    $search = isset($_GET['media_search']) ? sanitize_text_field(wp_unslash($_GET['media_search'])) : '';
    $year = isset($_GET['media_year']) ? absint(wp_unslash($_GET['media_year'])) : 0;
    $category = isset($_GET['media_category']) ? sanitize_title(wp_unslash($_GET['media_category'])) : '';

    return array(
        'search'   => trim($search),
        'year'     => $year,
        'category' => $category,
    );
}

function bis_get_news_filter_query_args($filters = null, array $overrides = array()) {
    $filters = is_array($filters) ? $filters : bis_get_news_filter_state();
    $filters = wp_parse_args($overrides, $filters);
    $query_args = array();

    if (!empty($filters['search'])) {
        $query_args['media_search'] = $filters['search'];
    }

    if (!empty($filters['year'])) {
        $query_args['media_year'] = (int) $filters['year'];
    }

    if (!empty($filters['category'])) {
        $query_args['media_category'] = $filters['category'];
    }

    return $query_args;
}

function bis_get_news_filter_url(array $overrides = array(), $filters = null) {
    $query_args = bis_get_news_filter_query_args($filters, $overrides);
    return add_query_arg($query_args, bis_get_news_archive_url());
}

function bis_get_news_category_terms($hide_empty = true) {
    $terms = get_terms(array(
        'taxonomy'   => 'bis_news_category',
        'hide_empty' => (bool) $hide_empty,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ));

    if (is_wp_error($terms) || !is_array($terms)) {
        return array();
    }

    return $terms;
}

function bis_get_news_year_options() {
    static $years = null;

    if (is_array($years)) {
        return $years;
    }

    global $wpdb;
    $raw_years = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT YEAR(post_date) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s AND post_date <> '0000-00-00 00:00:00' ORDER BY post_date DESC",
        'bis_news',
        'publish'
    ));

    $years = array_values(array_filter(array_map('absint', (array) $raw_years)));
    return $years;
}

function bis_normalize_news_search_value($search) {
    $search = trim((string) $search);
    $search = ltrim($search, "# \t\n\r\0\x0B");

    return trim($search);
}

function bis_get_news_search_match_ids($search) {
    $search = bis_normalize_news_search_value($search);

    if ($search === '') {
        return array();
    }

    static $cache = array();
    $cache_key = md5($search);

    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    global $wpdb;
    $like = '%' . $wpdb->esc_like($search) . '%';
    $title_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s AND post_title LIKE %s",
        'bis_news',
        'publish',
        $like
    ));

    $term_ids = get_terms(array(
        'taxonomy'   => 'bis_news_tag',
        'hide_empty' => false,
        'fields'     => 'ids',
        'search'     => $search,
    ));

    $tagged_ids = array();
    if (!is_wp_error($term_ids) && !empty($term_ids)) {
        $tagged_ids = get_posts(array(
            'post_type'              => 'bis_news',
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'tax_query'              => array(
                array(
                    'taxonomy' => 'bis_news_tag',
                    'field'    => 'term_id',
                    'terms'    => array_map('absint', (array) $term_ids),
                ),
            ),
        ));
    }

    $ids = array_values(array_unique(array_map('absint', array_merge((array) $title_ids, (array) $tagged_ids))));
    $cache[$cache_key] = $ids;

    return $ids;
}

function bis_build_news_query_args($paged = 1, $posts_per_page = 9, $filters = null) {
    $filters = is_array($filters) ? $filters : bis_get_news_filter_state();
    $tax_query = array();

    $query_args = array(
        'post_type'           => 'bis_news',
        'post_status'         => 'publish',
        'posts_per_page'      => (int) $posts_per_page,
        'paged'               => max(1, (int) $paged),
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
    );

    if (!empty($filters['search'])) {
        $matching_ids = bis_get_news_search_match_ids($filters['search']);
        $query_args['post__in'] = !empty($matching_ids) ? $matching_ids : array(0);
    }

    if (!empty($filters['year'])) {
        $query_args['date_query'] = array(
            array(
                'year' => (int) $filters['year'],
            ),
        );
    }

    if (!empty($filters['category'])) {
        $term = get_term_by('slug', $filters['category'], 'bis_news_category');
        if ($term instanceof WP_Term) {
            $tax_query[] = array(
                'taxonomy' => 'bis_news_category',
                'field'    => 'term_id',
                'terms'    => array((int) $term->term_id),
            );
        } else {
            $query_args['post__in'] = array(0);
        }
    }

    if (!empty($tax_query)) {
        $query_args['tax_query'] = $tax_query;
    }

    return $query_args;
}

function bis_render_news_filters($filters = null) {
    $filters = is_array($filters) ? $filters : bis_get_news_filter_state();
    $categories = bis_get_news_category_terms(true);
    $years = bis_get_news_year_options();
    $has_filters = !empty($filters['search']) || !empty($filters['year']) || !empty($filters['category']);
    ?>
    <div class="news-filter">
        <form class="news-filter__form" method="get" action="<?php echo esc_url(bis_get_news_archive_url()); ?>">
            <?php if (!empty($filters['category'])) : ?>
                <input type="hidden" name="media_category" value="<?php echo esc_attr($filters['category']); ?>">
            <?php endif; ?>
            <label class="news-filter__field news-filter__field--search">
                <span>Поиск</span>
                <input type="search" name="media_search" value="<?php echo esc_attr($filters['search']); ?>" placeholder="Название или метка">
            </label>
            <label class="news-filter__field">
                <span>Год</span>
                <select name="media_year">
                    <option value="">Любой год</option>
                    <?php foreach ($years as $year) : ?>
                        <option value="<?php echo esc_attr($year); ?>" <?php selected((int) $filters['year'], (int) $year); ?>><?php echo esc_html($year); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="btn btn-primary news-filter__submit" type="submit">Найти</button>
            <?php if ($has_filters) : ?>
                <a class="news-filter__reset" href="<?php echo esc_url(bis_get_news_archive_url()); ?>">Сбросить</a>
            <?php endif; ?>
        </form>

        <nav class="news-filter__categories" aria-label="Рубрики медиа">
            <a class="news-filter__category<?php echo empty($filters['category']) ? ' is-active' : ''; ?>" href="<?php echo esc_url(bis_get_news_filter_url(array('category' => ''), $filters)); ?>"<?php echo empty($filters['category']) ? ' aria-current="page"' : ''; ?>>Все</a>
            <?php foreach ($categories as $category) : ?>
                <?php $is_active = $filters['category'] === $category->slug; ?>
                <a class="news-filter__category<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url(bis_get_news_filter_url(array('category' => $category->slug), $filters)); ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
                    <?php echo esc_html($category->name); ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
    <?php
}
