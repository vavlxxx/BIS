<?php
get_header();
?>

<main class="error-404-page">
    <section class="error-404-hero">
        <div class="error-404-hero__content mw-1400px">
            <h1 class="error-404-hero__title">Страница не найдена</h1>
            <p class="error-404-hero__text">Возможно, ссылка устарела, страница была перемещена или адрес введён с ошибкой.</p>
            <div class="error-404-hero__actions">
                <a class="btn btn-primary" href="<?php echo esc_url(home_url('/')); ?>">На главную</a>
                <a class="btn btn-outline" href="<?php echo esc_url(home_url('/projects/')); ?>">Смотреть проекты</a>
                <button class="btn btn-outline open-estimate-modal" type="button">Рассчитать смету</button>
            </div>
        </div>
    </section>

    <section class="error-404-links">
        <div class="error-404-links__grid mw-1400px">
            <a class="error-404-links__card" href="<?php echo esc_url(home_url('/about/')); ?>">
                <span class="error-404-links__label">О компании</span>
                <p>Узнать, чем занимается БИС и как мы ведём проекты.</p>
            </a>
            <a class="error-404-links__card" href="<?php echo esc_url(home_url('/projects/')); ?>">
                <span class="error-404-links__label">Проекты</span>
                <p>Посмотреть реализованные объекты по категориям.</p>
            </a>
            <a class="error-404-links__card" href="<?php echo esc_url(home_url('/media/')); ?>">
                <span class="error-404-links__label">Медиа</span>
                <p>Открыть актуальные публикации и материалы компании.</p>
            </a>
        </div>
    </section>
</main>

<?php
get_footer();
?>
