<?php get_header(); ?>
<main class="bis-resource-page">
<?php while (have_posts()) : the_post(); ?>
<section class="section-header"><h1 class="section-title"><?php the_title(); ?></h1></section>
<article class="news-article__content mw-1400px"><?php the_content(); ?></article>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
