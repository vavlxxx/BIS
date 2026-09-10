<?php
/**
 * Single template for popular pages / services
 */

get_header();

$post_id = get_the_ID();
$terms = get_the_terms($post_id, 'popular_category');
$category_name = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->name : 'Популярные услуги';
$category_link = (!empty($terms) && !is_wp_error($terms)) ? get_term_link($terms[0]) : home_url('/#popular-services');
?>

<main class="popular-single-page">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <section class="news-hero news-hero--single">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="news-hero__media">
                        <?php the_post_thumbnail('full'); ?>
                    </div>
                <?php endif; ?>
                <div class="news-hero__overlay">
                    <h1 class="news-hero__title bis-condensed"><?php the_title(); ?></h1>
                </div>
            </section>

            <section class="breadcrumbs-section">
                <nav class="project-breadcrumbs mw-1400px">
                    <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
                    <span class="breadcrumbs-delimiter">/</span>
                    <a href="<?php echo esc_url(home_url('/#popular-services')); ?>">Популярные услуги</a>
                    <span class="breadcrumbs-delimiter">/</span>
                    <span><?php echo esc_html($category_name); ?></span>
                    <span class="breadcrumbs-delimiter">/</span>
                    <span><?php the_title(); ?></span>
                </nav>
            </section>

            <section class="service-article">
                <div class="service-article__container mw-1400px" style="padding: 40px 8vw 80px;">
                    <div class="news-article__content service-article__content">
                        <?php the_content(); ?>
                    </div>

                    <div style="margin-top: 40px; padding-top: 24px; border-top: 1px solid var(--border);">
                        <a href="<?php echo esc_url(home_url('/#popular-services')); ?>" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 8px;">
                            ← Все популярные услуги
                        </a>
                    </div>
                </div>
            </section>
        <?php endwhile; ?>
    <?php endif; ?>
</main>

<?php
get_footer();
