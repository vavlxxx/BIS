<?php
/*
Template Name: Вакансии
*/
get_header();

$page_id = get_the_ID();
if (!$page_id || 'page' !== get_post_type($page_id)) {
    $vac_pages = get_pages(array(
        'meta_key'   => '_wp_page_template',
        'meta_value' => 'page-vacancies.php',
        'number'     => 1,
    ));
    if (!empty($vac_pages)) {
        $page_id = $vac_pages[0]->ID;
    } else {
        $vac_page = get_page_by_path('vacancies');
        if ($vac_page) {
            $page_id = $vac_page->ID;
        }
    }
}

$banner_title = $page_id ? get_post_meta($page_id, 'bis_page_banner_title', true) : '';
$banner_subtitle = $page_id ? get_post_meta($page_id, 'bis_page_banner_subtitle', true) : '';
$banner_title = $banner_title ? $banner_title : ($page_id ? get_the_title($page_id) : 'Вакансии в компании «БИС»');

if (!$banner_subtitle) {
    $banner_subtitle = 'Приглашаем инженеров в команду профессионалов.';
}

$banner_image = $page_id ? bis_get_page_banner_image_url($page_id) : '';
?>

<main class="vacancies-page">
    <section class="news-hero news-hero--page" style="padding-inline: 8vw;">
        <?php if ($banner_image) : ?>
            <div class="news-hero__media">
                <img src="<?php echo esc_url($banner_image); ?>" alt="<?php echo esc_attr($banner_title); ?>" decoding="async">
            </div>
        <?php endif; ?>
        <div class="news-hero__overlay mw-1400px">
            <h1 class="news-hero__title bis-condensed"><?php echo esc_html($banner_title); ?></h1>
            <?php if (!empty($banner_subtitle)) : ?>
                <p class="news-hero__text"><?php echo nl2br(esc_html($banner_subtitle)); ?></p>
            <?php endif; ?>
            <div class="news-hero__nav page-hero__nav">
                <a href="#vacancies-benefits">Преимущества</a>
                <a href="#vacancies-list">Открытые вакансии</a>
                <a href="#vacancies-contacts">Контакты HR</a>
            </div>
        </div>
    </section>
    
    <section class="breadcrumbs-section">
        <nav class="project-breadcrumbs mw-1400px">
            <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
            <span class="breadcrumbs-delimiter">/</span>
            <span>Вакансии</span>
        </nav>
    </section>

    <!-- Benefits Section -->
    <section class="vacancies-benefits-section" id="vacancies-benefits">
        <div class="vacancies-benefits__container mw-1400px">
            <div class="vacancies-benefits__grid">
                <div class="vacancies-benefit-card">
                    <div class="vacancies-benefit-card__icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16"/><path d="M7 15h0M2 9.5h20"/></svg>
                    </div>
                    <h3>Достойный доход</h3>
                    <p>Выплаты строго 2 раза в месяц без задержек.</p>
                </div>
                <div class="vacancies-benefit-card">
                    <div class="vacancies-benefit-card__icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/></svg>
                    </div>
                    <h3>Официально по ТК РФ</h3>
                    <p>Трудовой договор, оплачиваемые отпуска и больничные, полное соблюдение трудового права.</p>
                </div>
                <div class="vacancies-benefit-card">
                    <div class="vacancies-benefit-card__icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"/></svg>
                    </div>
                    <h3>Обучение и сертификации</h3>
                    <p>Оплачиваем курсы, сертификации и обучение ПНР вентиляции, гидравлики и автоматики.</p>
                </div>
                <div class="vacancies-benefit-card">
                    <div class="vacancies-benefit-card__icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <h3>Экипировка и инструмент</h3>
                    <p>Выдаем качественную спецодежду, необходимые СИЗ и современное измерительное оборудование.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Vacancies List Section -->
    <section class="vacancies-list-section" id="vacancies-list">
        <div class="vacancies-list__container mw-1400px">
            <div class="section-header">
                <span class="section-badge">Актуальные позиции</span>
                <h2 class="section-title bis-condensed">Открытые вакансии</h2>
                <p class="section-subtitle">Выберите интересующую позицию, ознакомьтесь с требованиями и отправьте отклик</p>
            </div>

            <div class="vacancies-items">
                <?php
                $vacancies_query = new WP_Query(array(
                    'post_type'      => 'bis_vacancy',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'orderby'        => array('menu_order' => 'ASC', 'date' => 'DESC'),
                    'meta_query'     => array(
                        'relation' => 'OR',
                        array(
                            'key'     => 'bis_vacancy_is_hidden',
                            'compare' => 'NOT EXISTS',
                        ),
                        array(
                            'key'     => 'bis_vacancy_is_hidden',
                            'value'   => '1',
                            'compare' => '!=',
                        ),
                    ),
                ));

                if ($vacancies_query->have_posts()) :
                    while ($vacancies_query->have_posts()) : $vacancies_query->the_post();
                        $v_id        = get_the_ID();
                        $v_title     = get_the_title();
                        $department  = get_post_meta($v_id, 'bis_vacancy_department', true);
                        $location    = get_post_meta($v_id, 'bis_vacancy_location', true);
                        $salary      = get_post_meta($v_id, 'bis_vacancy_salary', true);
                        $salary_note = get_post_meta($v_id, 'bis_vacancy_salary_note', true);
                        $chips_raw   = get_post_meta($v_id, 'bis_vacancy_chips', true);
                        $duties_raw  = get_post_meta($v_id, 'bis_vacancy_duties', true);
                        $reqs_raw    = get_post_meta($v_id, 'bis_vacancy_requirements', true);
                        $highlight   = get_post_meta($v_id, 'bis_vacancy_requirements_highlight', true);
                        $cond_raw    = get_post_meta($v_id, 'bis_vacancy_conditions', true);

                        $chips = array_filter(array_map('trim', explode("\n", (string)$chips_raw)));
                        $duties = array_filter(array_map('trim', explode("\n", (string)$duties_raw)));
                        $reqs = array_filter(array_map('trim', explode("\n", (string)$reqs_raw)));
                        $conditions = array_filter(array_map('trim', explode("\n", (string)$cond_raw)));
                        ?>
                        <article class="vacancy-card" id="vacancy-<?php echo esc_attr($v_id); ?>">
                            <div class="vacancy-card__head">
                                <div class="vacancy-card__meta-top">
                                    <?php if ($department) : ?>
                                        <span class="vacancy-card__tag"><?php echo esc_html($department); ?></span>
                                    <?php endif; ?>
                                    <?php if ($location) : ?>
                                        <span class="vacancy-card__date"><?php echo esc_html($location); ?></span>
                                    <?php endif; ?>
                                </div>
                                <h2 class="vacancy-card__title"><?php echo esc_html($v_title); ?></h2>
                                <?php if ($salary) : ?>
                                    <div class="vacancy-card__salary">
                                        <?php echo esc_html($salary); ?>
                                        <?php if ($salary_note) : ?>
                                            <span class="vacancy-card__salary-note"><?php echo esc_html($salary_note); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($chips)) : ?>
                                    <div class="vacancy-card__chips">
                                        <?php foreach ($chips as $chip) : ?>
                                            <span class="vacancy-chip"><?php echo esc_html($chip); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($duties) || !empty($reqs) || !empty($conditions)) : ?>
                                <div class="vacancy-card__body">
                                    <?php if (!empty($duties)) : ?>
                                        <div class="vacancy-section-block">
                                            <h4 class="vacancy-section-block__title">Обязанности:</h4>
                                            <ul class="vacancy-section-block__list">
                                                <?php foreach ($duties as $d_item) : ?>
                                                    <li><?php echo esc_html($d_item); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($reqs) || !empty($highlight)) : ?>
                                        <div class="vacancy-section-block">
                                            <h4 class="vacancy-section-block__title">Требования к кандидату:</h4>
                                            <ul class="vacancy-section-block__list">
                                                <?php foreach ($reqs as $r_item) : ?>
                                                    <li><?php echo esc_html($r_item); ?></li>
                                                <?php endforeach; ?>
                                                <?php if (!empty($highlight)) : ?>
                                                    <li class="vacancy-highlight"><em><?php echo esc_html($highlight); ?></em></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($conditions)) : ?>
                                        <div class="vacancy-section-block">
                                            <h4 class="vacancy-section-block__title">Условия работы:</h4>
                                            <ul class="vacancy-section-block__list">
                                                <?php foreach ($conditions as $c_item) : ?>
                                                    <li><?php echo esc_html($c_item); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="vacancy-card__foot">
                                <button type="button" class="btn btn-primary open-vacancy-modal" data-vacancy="<?php echo esc_attr($v_title); ?>">
                                    Откликнуться на вакансию <span aria-hidden="true">→</span>
                                </button>
                                <a href="mailto:office@bis-rf.ru?subject=<?php echo esc_attr(rawurlencode('Отклик: ' . $v_title)); ?>" class="btn btn-outline">
                                    Отправить резюме на почту
                                </a>
                            </div>
                        </article>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
                    <div class="vacancy-card" style="text-align: center; padding: 60px 24px;">
                        <h3 style="font-size: 20px; font-weight: 700; color: var(--dark); margin-bottom: 12px;">В данный момент открытых вакансий нет</h3>
                        <p style="color: var(--text-light); max-width: 600px; margin: 0 auto 24px;">
                            Мы постоянно развиваемся и расширяем инженерную команду. Отправьте нам свое резюме, и мы свяжемся с вами при появлении подходящего проекта.
                        </p>
                        <button type="button" class="btn btn-primary open-vacancy-modal" data-vacancy="Инициативный отклик (Резюме)">
                            Отправить резюме в команду БИС <span aria-hidden="true">→</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- HR Contacts Section -->
    <section class="vacancies-contacts-section" id="vacancies-contacts">
        <div class="vacancies-contacts__container mw-1400px">
            <div class="vacancies-contacts__box">
                <div class="vacancies-contacts__content">
                    <span class="section-badge">Связь с HR</span>
                    <h2 class="vacancies-contacts__title bis-condensed">Не нашли подходящую вакансию?</h2>
                    <p class="vacancies-contacts__desc">Отправьте ваше резюме и контакты нашему отделу персонала. Мы всегда рады сильным инженерам, проектировщикам и специалистам ПНР.</p>
                    <div class="vacancies-contacts__links">
                        <a href="tel:+79264380770" class="vacancies-contacts__link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            +7 (926) 438-07-70
                        </a>
                        <a href="mailto:office@bis-rf.ru?subject=Резюме в компанию БИС" class="vacancies-contacts__link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            office@bis-rf.ru
                        </a>
                        <a href="https://t.me/+79264380770" target="_blank" rel="noopener" class="vacancies-contacts__link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            Telegram HR
                        </a>
                    </div>
                </div>
                <div class="vacancies-contacts__action">
                    <button type="button" class="btn btn-primary open-vacancy-modal" data-vacancy="Инициативный отклик (Резюме)">
                        Оставить заявку <span aria-hidden="true">→</span>
                    </button>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
