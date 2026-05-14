<?php
get_header();

$services_page_id = 0;
$services_pages = get_pages(array(
    'meta_key'   => '_wp_page_template',
    'meta_value' => 'page-services.php',
    'number'     => 1,
));

if (!empty($services_pages)) {
    $services_page_id = $services_pages[0]->ID;
}

if (!$services_page_id) {
    $services_page = get_page_by_path('services');
    if ($services_page) {
        $services_page_id = $services_page->ID;
    }
}

$banner_title = $services_page_id ? get_post_meta($services_page_id, 'bis_page_banner_title', true) : '';
$banner_subtitle = $services_page_id ? get_post_meta($services_page_id, 'bis_page_banner_subtitle', true) : '';
$banner_title = $banner_title ? $banner_title : ($services_page_id ? get_the_title($services_page_id) : 'Услуги');

if (!$banner_subtitle) {
    $banner_subtitle = 'Все услуги компании в одном списке: обследование, испытания, наладка и инженерное сопровождение.';
}

$banner_image = $services_page_id ? bis_get_page_banner_image_url($services_page_id) : '';
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
            <?php if (have_posts()) : ?>
                <div class="services-catalog__grid">
                    <?php while (have_posts()) : the_post(); ?>
                        <?php
                        $service_id = get_the_ID();
                        $image_url = bis_get_service_preview_image_url($service_id);
                        $description = bis_get_service_description($service_id);
                        ?>
                        <a class="service-card" href="<?php the_permalink(); ?>">
                            <div class="service-image">
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async">
                            </div>
                            <div class="service-content">
                                <div class="service-content-main">
                                    <h3><?php the_title(); ?></h3>
                                    <?php if (!empty($description)) : ?>
                                        <p class="experience-description"><?php echo esc_html($description); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endwhile; ?>
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
