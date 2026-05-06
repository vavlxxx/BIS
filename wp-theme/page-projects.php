<?php
/*
Template Name: Проекты
*/
get_header();
?>

<?php
$page_id = get_the_ID();
$banner_title = get_post_meta($page_id, 'bis_page_banner_title', true);
$banner_subtitle = get_post_meta($page_id, 'bis_page_banner_subtitle', true);
$banner_title = $banner_title ? $banner_title : get_the_title();
$banner_image = bis_get_page_banner_image_url($page_id);
$selected_type = isset($_GET['project_type']) ? sanitize_title(wp_unslash($_GET['project_type'])) : '';

$project_types = get_terms(array(
    'taxonomy' => 'bis_project_type',
    'hide_empty' => true,
    'orderby' => 'name',
    'order' => 'ASC',
));

if (is_wp_error($project_types)) {
    $project_types = array();
} else {
    $project_types = bis_sort_project_type_terms($project_types);
}

$projects_args = array(
    'post_type'      => 'bis_project',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => array('menu_order' => 'ASC', 'title' => 'ASC'),
);

if ($selected_type !== '') {
    $projects_args['tax_query'] = array(
        array(
            'taxonomy' => 'bis_project_type',
            'field'    => 'slug',
            'terms'    => array($selected_type),
        ),
    );
}

$projects = get_posts($projects_args);

if ($selected_type === '') {
    $projects = bis_sort_project_posts($projects);
}
?>

<main class="projects-page">
    <section class="news-hero news-hero--page">
        <?php if ($banner_image) : ?>
            <div class="news-hero__media">
                <img src="<?php echo esc_url($banner_image); ?>" alt="<?php echo esc_attr($banner_title); ?>" decoding="async">
            </div>
        <?php endif; ?>
        <div class="news-hero__overlay">
            <div class="mw-1400px projects-page-hero__content">
                <h1 class="news-hero__title"><?php echo esc_html($banner_title); ?></h1>
                <?php if (!empty($banner_subtitle)) : ?>
                    <p class="news-hero__text"><?php echo esc_html($banner_subtitle); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="breadcrumbs-section">
        <nav class="project-breadcrumbs mw-1400px">
            <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
            <span class="breadcrumbs-delimiter">/</span>
            <span><?php echo esc_html($banner_title); ?></span>
        </nav>
    </section>

    <section class="projects-types">
        <nav class="projects-types__list mw-1400px" aria-label="Типы проектов">
            <a class="projects-types__link <?php echo $selected_type === '' ? 'is-active' : ''; ?>" href="<?php echo esc_url(get_permalink($page_id)); ?>">Все проекты</a>
            <?php foreach ($project_types as $type) : ?>
                <a class="projects-types__link <?php echo $selected_type === $type->slug ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('project_type', $type->slug, get_permalink($page_id))); ?>">
                    <?php echo esc_html($type->name); ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </section>

    <section class="projects-list">
        <div class="experience-grid projects-grid">
            <?php if (!empty($projects)) : ?>
                <?php foreach ($projects as $post) : setup_postdata($post); ?>
                    <?php
                    $project_id = get_the_ID();
                    $image_url = bis_get_project_image_url($project_id);
                    $description = bis_get_project_description($project_id);
                    $is_featured = get_post_meta($project_id, 'bis_project_is_featured', true) === '1';
                    $project_type_terms = get_the_terms($project_id, 'bis_project_type');
                    $project_type_names = array();

                    if (is_array($project_type_terms) && !is_wp_error($project_type_terms)) {
                        $project_type_terms = bis_sort_project_type_terms($project_type_terms);
                        foreach ($project_type_terms as $project_type_term) {
                            $project_type_names[] = $project_type_term->name;
                        }
                    }
                    ?>
                    <div class="experience-card">
                        <div class="experience-image">
                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async">
                        </div>
                        <div class="experience-content">
                            <?php if ($is_featured) : ?>
                                <span class="experience-badge">Ключевой проект</span>
                            <?php endif; ?>
                            <h3><?php the_title(); ?></h3>
                            <?php if (!empty($project_type_names)) : ?>
                                <p class="experience-project-type"><?php echo esc_html(implode(', ', $project_type_names)); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($description)) : ?>
                                <p class="experience-description"><?php echo esc_html($description); ?></p>
                            <?php endif; ?>
                            <a class="experience-more" href="<?php echo esc_url(get_permalink($project_id)); ?>">Подробнее<span aria-hidden="true">→</span></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php wp_reset_postdata(); ?>
        </div>
    </section>
</main>

<?php
get_footer();
?>
