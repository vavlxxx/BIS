<?php
/*
Template Name: Медиа компании
*/
get_header();

$news_page_id = 0;
$news_pages = get_pages(array(
    'meta_key'   => '_wp_page_template',
    'meta_value' => 'page-news.php',
    'number'     => 1,
));
if (!empty($news_pages)) {
    $news_page_id = $news_pages[0]->ID;
}
if (!$news_page_id) {
    $news_page = get_page_by_path('news');
    if ($news_page) {
        $news_page_id = $news_page->ID;
    }
}

$banner_title = $news_page_id ? get_post_meta($news_page_id, 'bis_page_banner_title', true) : '';
$banner_subtitle = $news_page_id ? get_post_meta($news_page_id, 'bis_page_banner_subtitle', true) : '';
$banner_title = $banner_title ? $banner_title : 'Медиа компании';
if (!$banner_subtitle) {
    $banner_subtitle = 'Короткие обновления, новости и материалы компании.';
}
$banner_image = $news_page_id ? bis_get_page_banner_image_url($news_page_id) : '';

$paged = max(1, get_query_var('paged') ? get_query_var('paged') : get_query_var('page'));
$news_filters = bis_get_news_filter_state();
$news_query = new WP_Query(bis_build_news_query_args($paged, 9, $news_filters));
?>

<main class="news-archive-page">
    <section class="news-hero" style="padding-inline: 8vw;">
        <?php if ($banner_image) : ?>
            <div class="news-hero__media">
                <img src="<?php echo esc_url($banner_image); ?>" alt="<?php echo esc_attr($banner_title); ?>" decoding="async">
            </div>
        <?php endif; ?>
        <div class="news-hero__overlay mw-1400px">
            <h1 class="news-hero__title"><?php echo esc_html($banner_title); ?></h1>
            <?php if (!empty($banner_subtitle)) : ?>
                <p class="news-hero__text"><?php echo nl2br(esc_html($banner_subtitle)); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <section class="breadcrumbs-section">
        <nav class="project-breadcrumbs mw-1400px">
            <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
            <span class="breadcrumbs-delimiter">/</span>
            <span><?php echo esc_html($banner_title); ?></span>
        </nav>
    </section>

    <section class="news-list">
        <div class="news-list__container">
            <?php bis_render_news_filters($news_filters); ?>

            <?php if ($news_query->have_posts()) : ?>
                <div class="news-grid">
                    <?php while ($news_query->have_posts()) : $news_query->the_post(); ?>
                        <?php $image_url = bis_get_news_image_url(get_the_ID()); ?>
                        <article class="news-item">
                            <a class="news-item__image" href="<?php the_permalink(); ?>">
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                            </a>
                            <div class="news-item__body">
                                <time class="news-item__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('d.m.Y')); ?></time>
                                <h3 class="news-item__title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                <p class="news-item__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 22)); ?></p>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <?php
                $pagination = paginate_links(array(
                    'total'     => $news_query->max_num_pages,
                    'current'   => $paged,
                    'prev_text' => '&larr; Предыдущие',
                    'next_text' => 'Следующие &rarr;',
                    'type'      => 'array',
                    'add_args'  => bis_get_news_filter_query_args($news_filters),
                ));
                ?>
                <?php if (!empty($pagination)) : ?>
                    <div class="news-pagination">
                        <?php echo wp_kses_post(implode('', $pagination)); ?>
                    </div>
                <?php endif; ?>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="team-empty">
                    <span class="team-empty__label">Медиа</span>
                    <p>Материалы по выбранным фильтрам не найдены.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
get_footer();
?>
