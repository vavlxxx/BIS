<?php
/*
Template Name: Калькуляторы
*/
get_header();

$page_id = get_the_ID();
$banner_title = get_post_meta($page_id, 'bis_page_banner_title', true);
$banner_subtitle = get_post_meta($page_id, 'bis_page_banner_subtitle', true);
$banner_title = $banner_title ? $banner_title : 'Калькуляторы';

if (!$banner_subtitle) {
    $banner_subtitle = 'Онлайн-калькуляторы для расчета систем вентиляции и кондиционирования.';
}

$banner_image = bis_get_page_banner_image_url($page_id);
?>

<main class="calculators-page">
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

    <section class="calculators-content" style="padding: 80px 8vw 120px; background: var(--bg);">
        <div class="mw-1400px" style="text-align: center; max-width: 640px; margin: 0 auto;">
            <div style="font-size: 48px; color: var(--primary); margin-bottom: 24px; display: inline-flex; justify-content: center; align-items: center; width: 80px; height: 80px; background: var(--bg-alt); border: 1px solid var(--border);">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="square" stroke-linejoin="miter"><rect x="4" y="2" width="16" height="20"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="16" y1="14" x2="16" y2="14.01"/><line x1="12" y1="14" x2="12" y2="14.01"/><line x1="8" y1="14" x2="8" y2="14.01"/><line x1="16" y1="18" x2="16" y2="18.01"/><line x1="12" y1="18" x2="12" y2="18.01"/><line x1="8" y1="18" x2="8" y2="18.01"/><line x1="16" y1="10" x2="16" y2="10.01"/><line x1="12" y1="10" x2="12" y2="10.01"/><line x1="8" y1="10" x2="8" y2="10.01"/></svg>
            </div>
            <h2 style="font-size: var(--font-size-h2); color: var(--text); margin-bottom: 16px; font-weight: var(--font-weight-bold);">Раздел находится в разработке</h2>
            <p style="color: var(--text-light); font-size: var(--font-size-body); line-height: 1.6; margin-bottom: 32px;">В скором времени здесь будут доступны калькуляторы для онлайн-расчета инженерных систем.</p>
            <a href="<?php echo esc_url(home_url('/')); ?>" style="text-align: center;margin-inline: auto;" class="btn btn-primary">Вернуться на главную</a>
        </div>
    </section>
</main>

<?php
get_footer();
?>
