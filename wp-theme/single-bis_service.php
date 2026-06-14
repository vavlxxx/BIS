<?php
get_header();
?>

<main class="service-single-page">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <?php
            $service_id = get_the_ID();
            $cover = bis_get_service_banner_image_url($service_id);
            $description = bis_get_service_description($service_id);
            $content = trim((string) get_post_field('post_content', $service_id));
            $parent_service = null;
            $service_post = get_post($service_id);
            if ($service_post instanceof WP_Post && (int) $service_post->post_parent > 0) {
                $parent_service = get_post((int) $service_post->post_parent);
            }
            $service_tags = get_the_terms($service_id, 'bis_service_tag');
            ?>

            <section class="news-hero news-hero--single">
                <?php if ($cover) : ?>
                    <div class="news-hero__media">
                        <img src="<?php echo esc_url($cover); ?>" alt="<?php the_title_attribute(); ?>" decoding="async">
                    </div>
                <?php endif; ?>
                <div class="news-hero__overlay">
                    <h1 class="news-hero__title"><?php the_title(); ?></h1>
                </div>
            </section>

            <section class="breadcrumbs-section">
                <nav class="project-breadcrumbs mw-1400px">
                    <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
                    <span class="breadcrumbs-delimiter">/</span>
                    <a href="<?php echo esc_url(get_post_type_archive_link('bis_service')); ?>">Услуги</a>
                    <span class="breadcrumbs-delimiter">/</span>
                    <?php if ($parent_service instanceof WP_Post) : ?>
                        <a href="<?php echo esc_url(get_permalink($parent_service)); ?>"><?php echo esc_html(get_the_title($parent_service)); ?></a>
                        <span class="breadcrumbs-delimiter">/</span>
                    <?php endif; ?>
                    <span><?php the_title(); ?></span>
                </nav>
            </section>

            <?php if (is_array($service_tags) && !empty($service_tags) && !is_wp_error($service_tags)) : ?>
                <section class="service-tags-section">
                    <div class="service-tags mw-1400px" aria-label="Теги услуги">
                        <?php foreach ($service_tags as $tag) : ?>
                            <span class="service-tag" href="<?php echo esc_url(get_term_link($tag)); ?>"><?php echo esc_html($tag->name); ?></span>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="service-article">
                <div class="service-article__container">
                    <?php if ($description !== '') : ?>
                        <div class="service-article__header">
                            <p class="service-article__lead"><?php echo esc_html($description); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="news-article__content service-article__content">
                        <?php
                        if ($content !== '') {
                            the_content();
                        } elseif ($description !== '') {
                            echo wpautop(esc_html($description));
                        }
                        ?>
                    </div>
                </div>
            </section>

            <?php
            $associated_services = bis_get_associated_services($service_id);
            ?>

            <?php if (!empty($associated_services)) : ?>
                <section class="service-links-section service-links-section--associated">
                    <div class="service-links-section__container">
                        <div class="service-related__header mw-1400px">
                            <h2 class="section-title">Связанные услуги</h2>
                        </div>
                        <ul class="service-links-list">
                            <?php foreach ($associated_services as $post) : setup_postdata($post); ?>
                                <?php $associated_description = bis_get_service_description(get_the_ID()); ?>
                                <li class="service-links-list__item">
                                    <a class="service-links-list__link" href="<?php the_permalink(); ?>">
                                        <span class="service-links-list__button" aria-hidden="true">
                                            <svg class="service-links-list__icon" width="22" height="22" viewBox="0 0 24 24" fill="none" focusable="false">
                                                <path d="M9 5L16 12L9 19" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <span class="service-links-list__content">
                                            <span class="service-links-list__title"><?php the_title(); ?></span>
                                            <?php if ($associated_description !== '') : ?>
                                                <span class="service-links-list__description"><?php echo esc_html($associated_description); ?></span>
                                            <?php endif; ?>
                                        </span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            <?php wp_reset_postdata(); ?>
                        </ul>
                    </div>
                </section>
            <?php endif; ?>

            <?php
            $related_services = bis_get_service_sibling_services($service_id, 3);
            if (empty($related_services)) {
                $related_services = array_values(array_filter(bis_get_catalog_services(array(
                    'exclude' => array($service_id),
                )), function ($post) use ($service_id) {
                    return $post instanceof WP_Post && (int) $post->ID !== (int) $service_id;
                }));
                $related_services = array_slice($related_services, 0, 3);
            }
            ?>

            <?php if (!empty($related_services)) : ?>
                <section class="services-catalog services-catalog--related">
                    <div class="services-catalog__container">
                        <div class="service-related__header mw-1400px">
                            <h2 class="section-title">Другие услуги</h2>
                        </div>
                        <div class="services-slider-shell services-slider-shell--related">
                            <div class="services-catalog__grid<?php echo bis_services_have_associated_services($related_services) ? ' services-catalog__grid--has-submenus' : ''; ?>" data-related-services-track>
                                <?php foreach ($related_services as $post) : setup_postdata($post); ?>
                                    <?php bis_render_service_card(get_the_ID()); ?>
                                <?php endforeach; ?>
                                <?php wp_reset_postdata(); ?>
                            </div>
                            <div class="services-slider-nav">
                                <button class="slider-prev" type="button" aria-label="Предыдущая услуга">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <div class="slider-dots"></div>
                                <button class="slider-next" type="button" aria-label="Следующая услуга">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        <?php endwhile; ?>
    <?php endif; ?>
</main>

<?php
get_footer();
