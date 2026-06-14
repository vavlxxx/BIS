<?php
/*
Template Name: Услуги
*/
get_header();

$page_id = get_the_ID();
$banner_title = get_post_meta($page_id, 'bis_page_banner_title', true);
$banner_subtitle = get_post_meta($page_id, 'bis_page_banner_subtitle', true);
$banner_title = $banner_title ? $banner_title : get_the_title();

if (!$banner_subtitle) {
    $banner_subtitle = 'Все услуги компании в одном списке: обследование, испытания, наладка и инженерное сопровождение.';
}

$banner_image = bis_get_page_banner_image_url($page_id);
$services = bis_get_catalog_services();
$services_grid_classes = 'services-catalog__grid' . (bis_services_have_associated_services($services) ? ' services-catalog__grid--has-submenus' : '');
?>

<main class="services-archive-page">
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

    <section class="services-catalog services-catalog--archive">
        <div class="services-catalog__container">
            <?php if (!empty($services)) : ?>
                <div class="<?php echo esc_attr($services_grid_classes); ?>">
                    <?php foreach ($services as $post) : setup_postdata($post); ?>
                        <?php bis_render_service_card(get_the_ID()); ?>
                    <?php endforeach; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            <?php else : ?>
                <div class="team-empty">
                    <span class="team-empty__label">Услуги</span>
                    <p>Список услуг пока пуст.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
get_footer();
