<?php
/*
Template Name: О компании
*/
get_header();
?>

<?php
$page_id = get_the_ID();
$banner_title = get_post_meta($page_id, 'bis_page_banner_title', true);
$banner_subtitle = get_post_meta($page_id, 'bis_page_banner_subtitle', true);
$banner_title = $banner_title ? $banner_title : get_the_title();
$banner_subtitle = $banner_subtitle ? $banner_subtitle : '«БИС — Баланс Инженерных Систем» — инжиниринговая команда полного цикла: проектируем, запускаем и сопровождаем инженерные системы.';
$banner_image = bis_get_page_banner_image_url($page_id);
?>

<main class="about-page">
    <section class="news-hero news-hero--page">
        <?php if ($banner_image) : ?>
            <div class="news-hero__media">
                <img src="<?php echo esc_url($banner_image); ?>" alt="<?php echo esc_attr($banner_title); ?>" decoding="async">
            </div>
        <?php endif; ?>
        <div class="news-hero__overlay">
            <h1 class="news-hero__title"><?php echo esc_html($banner_title); ?></h1>
            <?php if (!empty($banner_subtitle)) : ?>
                <p class="news-hero__text"><?php echo esc_html($banner_subtitle); ?></p>
            <?php endif; ?>
            <div class="news-hero__nav page-hero__nav">
                <a href="#about-who">Кто мы</a>
                <a href="#about-mission">Миссия</a>
                <a href="#about-stats">В цифрах</a>
                <a href="#about-team">Команда</a>
                <a href="#about-gratitude">Отзывы</a>
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

    <section class="about-intro" id="about-who">
        <div class="about-intro__grid mw-1400px">
            <div class="about-intro__content">
                <span class="section-badge">О компании</span>
                <h2 class="section-title">Мы берём ответственность за весь жизненный цикл инженерных систем</h2>
                <p>Команда БИС сопровождает проекты от обследования и проектирования до пусконаладки и сервисного сопровождения. Мы умеем быстро включаться в задачи заказчика, объяснять сложное простым языком и отвечать за результат.</p>
                <p>Наша практика — это строгое соблюдение стандартов, прозрачные процессы и постоянная коммуникация с клиентом на каждом этапе.</p>
            </div>
            <div class="about-intro__cards">
                <div class="about-intro__card">
                    <h3>Комплексно</h3>
                    <p>Закрываем весь цикл работ, чтобы заказчик не искал дополнительных подрядчиков.</p>
                </div>
                <div class="about-intro__card">
                    <h3>Точно</h3>
                    <p>Проверяем системы измерениями и протоколами, а не предположениями.</p>
                </div>
                <div class="about-intro__card">
                    <h3>Быстро</h3>
                    <p>Гибко реагируем на изменения и оперативно включаем дополнительные ресурсы.</p>
                </div>
                <div class="about-intro__card">
                    <h3>Надёжно</h3>
                    <p>Работаем с ответственностью и соблюдением нормативов.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="about-mission" id="about-mission">
        <div class="about-mission__content mw-1400px">
            <h2 class="section-title">Наша миссия</h2>
            <p>Создавать инженерные системы, которые работают стабильно, безопасно и энергоэффективно, а заказчик чувствует уверенность в каждом этапе — от запуска до эксплуатации.</p>
            <div class="about-mission__panel">
                <p>Мы объединяем технологическую экспертизу и заботу о комфорте людей внутри объектов — от офисов и производств до жилых комплексов.</p>
            </div>
        </div>
    </section>

    <section class="about-stats" id="about-stats">
        <div class="mw-1400px">
            <div class="section-header">
                <span class="section-badge">БИС в цифрах</span>
                <h2 class="section-title">Опыт, подтверждённый результатами</h2>
                <p class="section-subtitle">Собрали показатели, которые отражают масштаб нашей работы.</p>
            </div>
            <div class="stats about-stats__grid">
                <div class="stat-item">
                    <span class="stat-value">10</span>
                    <span class="stat-label">лет на рынке</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">200</span>
                    <span class="stat-label">реализованных проектов</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">80%</span>
                    <span class="stat-label">клиентов возвращаются повторно</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">40 тыс</span>
                    <span class="stat-label">индивидуальных испытаний</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">100 тыс</span>
                    <span class="stat-label">м.п. воздуховодов проверено</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">20 тыс</span>
                    <span class="stat-label">систем обследовано и налажено</span>
                </div>
            </div>
            </div>
    </section>

    <section class="why-us about-why" id="about-why">
    <div class="mw-1400px">  
    <div class="section-header">
            <span class="section-badge">Почему выбирают нас</span>
            <p class="section-subtitle">Мы выстраиваем устойчивые инженерные решения и берём ответственность за результат.</p>
        </div>
        <div class="why-grid">
            <div class="why-card">
                <div class="why-number">01</div>
                <h3>Экспертиза</h3>
                <p>Команда сертифицированных специалистов с опытом наладки инженерных систем.</p>
            </div>
            <div class="why-card">
                <div class="why-number">02</div>
                <h3>Надежность</h3>
                <p>Опираемся на точные измерения и проверенные технологии.</p>
            </div>
            <div class="why-card">
                <div class="why-number">03</div>
                <h3>Скорость</h3>
                <p>Оперативно включаемся в работу и подстраиваемся под графики заказчика.</p>
            </div>
            <div class="why-card">
                <div class="why-number">04</div>
                <h3>Сервис</h3>
                <p>Сопровождаем объекты после запуска и остаёмся на связи.</p>
            </div>
        </div>
        </div>  
    </section>

    <?php $team_members = bis_get_team_members(); ?>
    <div class="section-header">
        <h2 class="section-title">Наша команда</h2>
        <p class="section-subtitle">Ведущие специалисты в области инженерных систем</p>
    </div>
    <section class="structure-section team-section" id="about-team"
        <?php if (empty($team_members)) : ?>
            style="padding: 60px 0;"
        <?php endif; ?>
    >
        <?php if (!empty($team_members)) : ?>
            <div class="team-slider" data-team-slider>
                <div class="team-track-wrap">
                    <div class="team-track">
                        <?php foreach ($team_members as $member) :
                            $name = isset($member['name']) ? $member['name'] : '';
                            $role = isset($member['role']) ? $member['role'] : '';
                            $since = isset($member['since']) ? $member['since'] : '';
                            $short = isset($member['short']) ? $member['short'] : '';
                            $long = isset($member['long']) ? $member['long'] : '';
                            $photo = bis_get_team_member_photo_url($member);
                            $modal_photo = bis_get_team_member_modal_photo_url($member);
                            $qr_code = isset($member['qr_code']) ? $member['qr_code'] : '';
                            $contact_phone = isset($member['contact_phone']) ? $member['contact_phone'] : '';
                            $contact_email = isset($member['contact_email']) ? $member['contact_email'] : '';
                            $contact_phone_href = bis_get_phone_href($contact_phone);
                            $contact_email_href = sanitize_email($contact_email);
                            ?>
                            <article class="team-slide" data-team-slide data-name="<?php echo esc_attr($name); ?>" data-role="<?php echo esc_attr($role); ?>" data-since="<?php echo esc_attr($since); ?>" data-slide-photo="<?php echo esc_url($photo); ?>" data-modal-photo="<?php echo esc_url($modal_photo); ?>">
                                <div class="team-slide__content">
                                    <div class="team-story"><?php echo wp_kses_post(wpautop($short)); ?></div>
                                    <div class="team-meta">
                                        <span class="team-name"><?php echo esc_html($name); ?></span>
                                        <span class="team-role"><?php echo esc_html($role); ?></span>
                                        <?php if ($since !== '') : ?>
                                            <span class="team-since">В команде с <?php echo esc_html($since); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($qr_code || $contact_phone_href || $contact_email_href) : ?>
                                        <div class="team-contact-card<?php echo $qr_code ? '' : ' team-contact-card--no-qr'; ?>">
                                            <?php if ($qr_code) : ?>
                                                <div class="team-contact-card__qr">
                                                    <img src="<?php echo esc_url($qr_code); ?>" alt="<?php echo esc_attr($name ? 'QR-код контакта: ' . $name : 'QR-код контакта'); ?>" loading="lazy">
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($contact_phone_href || $contact_email_href) : ?>
                                                <div class="team-contact-card__details">
                                                    <?php if ($contact_phone_href) : ?>
                                                        <a class="floating-socials-panel__contact team-contact-card__link" href="<?php echo esc_url($contact_phone_href); ?>"><?php echo esc_html($contact_phone); ?></a>
                                                    <?php endif; ?>
                                                    <?php if ($contact_email_href) : ?>
                                                        <a class="floating-socials-panel__contact team-contact-card__link" href="mailto:<?php echo esc_attr($contact_email_href); ?>"><?php echo esc_html($contact_email); ?></a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <button class="btn btn-outline team-more" type="button" data-team-more>Подробнее</button>
                                </div>
                                <div class="team-slide__photo" data-team-photo aria-hidden="true"></div>
                                <div class="team-slide__long" hidden>
                                    <?php echo wp_kses_post(wpautop($long)); ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="team-controls team-slider__controls" aria-label="Навигация по команде">
                    <button class="team-nav team-prev" type="button" aria-label="Предыдущий сотрудник">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <button class="team-nav team-next" type="button" aria-label="Следующий сотрудник">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
        <?php else : ?>
            <div class="team-empty">
                <span class="team-empty__label">Команда</span>
                <p>Мы готовим презентацию ключевых специалистов.</p>
            </div>
        <?php endif; ?>
    </section>

    <div class="team-modal" id="teamModal" aria-hidden="true" role="dialog">
        <div class="team-modal__backdrop" data-team-close></div>
        <div class="team-modal__dialog" aria-modal="true" aria-labelledby="teamModalTitle">
            <button class="team-modal__close" type="button" aria-label="Закрыть" data-team-close>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <div class="team-modal__image">
                <img src="" alt="" loading="lazy" data-team-modal-image>
            </div>
            <div class="team-modal__body">
                <h3 class="team-modal__name" id="teamModalTitle" data-team-modal-name></h3>
                <p class="team-modal__role" data-team-modal-role></p>
                <p class="team-modal__since" data-team-modal-since></p>
                <div class="team-modal__text" data-team-modal-text></div>
            </div>
        </div>
    </div>

    <?php
    $gratitude_letters = new WP_Query(array(
        'post_type'      => 'bis_gratitude',
        'posts_per_page' => -1,
        'orderby'        => array('menu_order' => 'ASC', 'date' => 'DESC'),
    ));
    if ($gratitude_letters->have_posts()) :
    ?>
    <section class="gratitude-section" style="display: none;" id="about-gratitude">
        <div class="mw-1400px">
            <div class="section-header">
                <h2 class="section-title">Отзывы наших клиентов</h2>
                <p class="section-subtitle">Благодарственные письма от партнёров и заказчиков подтверждают качество и результат нашей работы</p>
            </div>

        <div class="gratitude-slider-wrapper" data-gratitude-gallery>
            <button class="gratitude-nav gratitude-prev" type="button" aria-label="Предыдущий отзыв" data-gratitude-prev>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            <div class="gratitude-slider">
                <div class="gratitude-track" data-gratitude-track>
                    <?php while ($gratitude_letters->have_posts()) : $gratitude_letters->the_post(); ?>
                        <?php
                            $image_url = bis_get_gratitude_image_url(get_the_ID());
                            $title_attr = the_title_attribute(array('echo' => false));
                        ?>
                            <button type="button" class="gratitude-card<?php echo $image_url ? ' has-image' : ''; ?>" data-gratitude-slide<?php if ($image_url) : ?> data-image="<?php echo esc_url($image_url); ?>" data-title="<?php echo esc_attr($title_attr); ?>"<?php endif; ?>>
                                <?php if ($image_url) : ?>
                                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title_attr); ?>" loading="lazy">
                                <?php else : ?>
                                    <div class="gratitude-card__placeholder">Изображение письма появится здесь</div>
                                <?php endif; ?>
                            </button>
                    <?php endwhile; ?>
                </div>
            </div>

            <button class="gratitude-nav gratitude-next" type="button" aria-label="Следующий отзыв" data-gratitude-next>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
            </div>
    </section>
    <?php
    wp_reset_postdata();
    endif;
    ?>

    <div class="gratitude-modal" id="gratitudeModal" aria-hidden="true" role="dialog">
        <div class="gratitude-modal-backdrop" data-close-gratitude></div>
        <div class="gratitude-modal-content" aria-modal="true">
            <button class="gratitude-modal-close" type="button" aria-label="Закрыть увеличенное письмо" data-close-gratitude>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <button class="gratitude-modal-nav gratitude-modal-nav--prev" type="button" aria-label="Предыдущее письмо" data-gratitude-lightbox-prev>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <div class="gratitude-modal-image">
                <img src="" alt="Благодарственное письмо" loading="lazy" data-gratitude-lightbox-image>
            </div>
            <button class="gratitude-modal-nav gratitude-modal-nav--next" type="button" aria-label="Следующее письмо" data-gratitude-lightbox-next>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <div class="gratitude-modal-caption" data-gratitude-lightbox-caption></div>
        </div>
    </div>
</main>

<?php
get_footer();
?>
