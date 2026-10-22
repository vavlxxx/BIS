<?php
/*
Template Name: F.A.Q
*/
get_header();

$page_id = get_the_ID();
$banner_title = get_post_meta($page_id, 'bis_page_banner_title', true);
$banner_title = $banner_title ? $banner_title : (get_the_title() ?: 'F.A.Q');
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
        <span class="section-badge">F.A.Q</span>
        <h1 class="section-title">Часто задаваемые вопросы</h1>
        <p class="section-subtitle">Ответы на вопросы об инженерных системах и нашей работе</p>
    </section>

    <div class="mw-1400px" data-faq-section>
        <nav class="bis-filter-tabs" aria-label="Рубрики вопросов">
            <button type="button" class="btn btn-primary" data-faq-filter="all" aria-pressed="true">Все вопросы</button>
            <?php
            $terms = get_terms(array('taxonomy' => 'bis_faq_category', 'hide_empty' => true));
            foreach (is_array($terms) ? $terms : array() as $term) :
            ?>
                <button type="button" class="btn btn-outline" data-faq-filter="<?php echo esc_attr($term->term_id); ?>" aria-pressed="false"><?php echo esc_html($term->name); ?></button>
            <?php endforeach; ?>
        </nav>

        <?php bis_render_faq_items(bis_faq_items()); ?>

        <div class="faq-cta">
            <p>Остались вопросы?</p>
            <button type="button" class="btn btn-primary callback-btn">Получить консультацию</button>
        </div>
    </div>
</main>

<?php get_footer(); ?>
