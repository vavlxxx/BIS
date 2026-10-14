<?php /* Template Name: F.A.Q */ get_header(); ?>
<main class="bis-resource-page">
<section class="section-header"><span class="section-badge">F.A.Q</span><h1 class="section-title">Часто задаваемые вопросы</h1><p class="section-subtitle">Ответы на вопросы об инженерных системах и нашей работе</p></section>
<div class="mw-1400px" data-faq-section>
<nav class="bis-filter-tabs" aria-label="Рубрики вопросов"><button type="button" class="btn btn-primary" data-faq-filter="all" aria-pressed="true">Все вопросы</button>
<?php $terms=get_terms(array('taxonomy'=>'bis_faq_category','hide_empty'=>true)); foreach (is_array($terms)?$terms:array() as $term) : ?>
<button type="button" class="btn btn-outline" data-faq-filter="<?php echo esc_attr($term->term_id); ?>" aria-pressed="false"><?php echo esc_html($term->name); ?></button>
<?php endforeach; ?></nav>
<?php bis_render_faq_items(bis_faq_items()); ?>
<div class="faq-cta"><p>Остались вопросы?</p><button type="button" class="btn btn-primary callback-btn">Получить консультацию</button></div>
</div></main>
<?php get_footer(); ?>
