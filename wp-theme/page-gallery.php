<?php
/*
Template Name: Фото и видео
*/
get_header();

$page_id = get_the_ID();
$banner_title = get_post_meta($page_id, 'bis_page_banner_title', true);
$banner_title = $banner_title ? $banner_title : (get_the_title() ?: 'Фото и видео');
?>

<main class="bis-resource-page">
    <section class="breadcrumbs-section">
        <nav class="project-breadcrumbs mw-1400px">
            <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
            <span class="breadcrumbs-delimiter">/</span>
            <span><?php echo esc_html($banner_title); ?></span>
        </nav>
    </section>

    <section class="section-header">
        <span class="section-badge">Галерея</span>
        <h1 class="section-title">Фото и видео</h1>
        <p class="section-subtitle">Работа нашей команды и инженерные системы на объектах</p>
    </section>

    <div class="mw-1400px">
        <?php bis_render_gallery(); ?>
    </div>
</main>

<?php get_footer(); ?>
