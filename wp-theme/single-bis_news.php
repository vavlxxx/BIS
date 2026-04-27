<?php
get_header();
?>

<main class="news-single-page">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <?php $cover = bis_get_news_image_url(get_the_ID()); ?>

            <section class="news-hero news-hero--single" style="padding-inline: 8vw;">
                <div class="news-hero__media">
                    <img src="<?php echo esc_url($cover); ?>" alt="<?php the_title_attribute(); ?>" decoding="async">
                </div>
                <div class="news-hero__overlay mw-1400px">
                    <h1 class="news-hero__title"><?php the_title(); ?></h1>
                </div>
            </section>

            <section class="breadcrumbs-section">
                <div class="mw-1400px" style="display: flex; justify-content: space-between;">
                    <nav class="project-breadcrumbs">
                        <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
                        <span class="breadcrumbs-delimiter">/</span>
                        <a href="<?php echo esc_url(get_post_type_archive_link('bis_news')); ?>">Новости</a>
                        <span class="breadcrumbs-delimiter">/</span>
                        <span><?php the_title(); ?></span>
                    </nav>
                    <div class="news-article__meta">
                        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('d.m.Y')); ?></time>
                    </div>
                </div>
            </section>

            <section class="news-article">
                <div class="news-article__container mw-1400px">
                    <?php if (has_excerpt()) : ?>
                        <p class="news-article__lead"><?php echo esc_html(get_the_excerpt()); ?></p>
                    <?php endif; ?>
                    <div class="news-article__content">
                        <?php the_content(); ?>
                    </div>
                </div>
            </section>

            <?php
            $related = new WP_Query(array(
                'post_type'      => 'bis_news',
                'posts_per_page' => 3,
                'post_status'    => 'publish',
                'post__not_in'   => array(get_the_ID()),
                'orderby'        => 'date',
                'order'          => 'DESC',
            ));
            ?>

            <section class="news-related">
                <div class="news-related__container mw-1400px">
                    <h2 class="section-title">Читайте также</h2>
                    <?php if ($related->have_posts()) : ?>
                        <div class="news-grid">
                            <?php while ($related->have_posts()) : $related->the_post(); ?>
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
                                        <p class="news-item__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 16)); ?></p>
                                    </div>
                                </article>
                            <?php endwhile; ?>
                        </div>
                        <?php wp_reset_postdata(); ?>
                    <?php else : ?>
                        <div class="team-empty">
                            <span class="team-empty__label">Новости</span>
                            <p>Мы готовим подборку новостей компании.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endwhile; ?>
    <?php else : ?>
        <section class="news-article">
            <div class="news-article__container">
                <div class="team-empty">
                    <span class="team-empty__label">Новости</span>
                    <p>Новость не найдена.</p>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php
get_footer();
