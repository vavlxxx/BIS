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
        <div class="mw-1400px">
            <div class="about-mission__grid">
                <div class="about-mission__intro">
                    <span class="section-badge">Наша миссия</span>
                    <h2 class="section-title">Инженерный баланс для надежной и безопасной работы объектов</h2>
                    <p class="about-mission__lead">
                        Создавать инженерные системы, которые работают стабильно, безопасно и энергоэффективно, а заказчик чувствует уверенность в каждом этапе — от первого пуска до многолетней эксплуатации.
                    </p>
                </div>
                <div class="about-mission__card-wrap">
                    <div class="about-mission__card">
                        <div class="about-mission__card-header">
                            <span class="about-mission__card-badge">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="22" y1="12" x2="18" y2="12"></line>
                                    <line x1="6" y1="12" x2="2" y2="12"></line>
                                    <line x1="12" y1="6" x2="12" y2="2"></line>
                                    <line x1="12" y1="22" x2="12" y2="18"></line>
                                </svg>
                                Ключевой принцип
                            </span>
                        </div>
                        <blockquote class="about-mission__quote">
                            «Мы объединяем <span class="about-mission__highlight">технологическую экспертизу</span> и&nbsp;<span class="about-mission__highlight">заботу о&nbsp;комфорте людей</span> внутри объектов&nbsp;— от&nbsp;офисов и&nbsp;производств до&nbsp;жилых комплексов.»
                        </blockquote>
                        <div class="about-mission__tags">
                            <span class="about-mission__tag">Офисы и БЦ класса А</span>
                            <span class="about-mission__tag">Производства и логистика</span>
                            <span class="about-mission__tag">Жилые кварталы</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="about-stats" id="about-stats">
        <div class="mw-1400px">
            <div class="section-header">
                <span class="section-badge">БИС в цифрах</span>
                <h2 class="section-title">Опыт, подтверждённый результатами</h2>
                <p class="about-stats__hero-text">
                    За 10 лет нашей работы мы сдали <strong>более 200 объектов</strong> общей площадью свыше <strong>3 миллионов квадратных метров</strong>: бизнес-центры класса А, торгово-развлекательные комплексы, жилые кварталы, медучреждения, производства, гостиницы, аэропорты. Каждый объект прошёл приёмку в ЗОС и ввод в эксплуатацию в срок и без нареканий надзорных органов.
                </p>
                <div class="about-stats__sectors">
                    <span class="about-stats__sector-tag">Бизнес-центры класса А</span>
                    <span class="about-stats__sector-tag">ТРЦ и моллы</span>
                    <span class="about-stats__sector-tag">Жилые кварталы</span>
                    <span class="about-stats__sector-tag">Медучреждения</span>
                    <span class="about-stats__sector-tag">Производства</span>
                    <span class="about-stats__sector-tag">Гостиницы</span>
                    <span class="about-stats__sector-tag">Аэропорты</span>
                </div>
            </div>

            <div class="about-stats__summary-grid">
                <div class="about-summary-card">
                    <span class="about-summary-card__value">10</span>
                    <span class="about-summary-card__unit">лет на рынке</span>
                    <span class="about-summary-card__label">безупречной инженерной практики</span>
                </div>
                <div class="about-summary-card">
                    <span class="about-summary-card__value">&gt;200</span>
                    <span class="about-summary-card__unit">объектов</span>
                    <span class="about-summary-card__label">успешно сданных в эксплуатацию</span>
                </div>
                <div class="about-summary-card">
                    <span class="about-summary-card__value">&gt;3 млн</span>
                    <span class="about-summary-card__unit">м² площади</span>
                    <span class="about-summary-card__label">масштаб реализованных проектов</span>
                </div>
                <div class="about-summary-card">
                    <span class="about-summary-card__value">100%</span>
                    <span class="about-summary-card__unit">в срок</span>
                    <span class="about-summary-card__label">приёмка в ЗОС без нареканий</span>
                </div>
            </div>

            <div class="about-econ">
                <div class="about-econ__header">
                    <span class="about-econ__pretitle">Финансовая эффективность</span>
                    <h3 class="about-econ__title">Что мы сделали для заказчиков — в деньгах на 1 м²</h3>
                </div>

                <div class="about-econ__grid">
                    <article class="about-econ-card">
                        <div class="about-econ-card__top">
                            <div class="about-econ-card__metric">
                                <span class="about-econ-card__num">−420 ₽/м²</span>
                                <span class="about-econ-card__period">в год</span>
                            </div>
                        </div>
                        <h4 class="about-econ-card__title">Снизили затраты на энергопотребление</h4>
                        <p class="about-econ-card__desc">
                            Снизили затраты энергопотребление на 420 рублей за м² площади объекта в год. Что составило 12 млн рублей на объекте площадью 50 тыс м². Расходы на ПНР окупились в первый год эксплуатации.
                        </p>
                        <div class="about-econ-card__footer">
                            <span class="about-econ-card__highlight">12 млн ₽ экономии на 50 тыс. м²</span>
                        </div>
                    </article>

                    <article class="about-econ-card">
                        <div class="about-econ-card__top">
                            <div class="about-econ-card__metric">
                                <span class="about-econ-card__num">100%</span>
                                <span class="about-econ-card__period">в срок</span>
                            </div>
                        </div>
                        <h4 class="about-econ-card__title">Сдали все объекты в срок</h4>
                        <p class="about-econ-card__desc">
                            Каждый месяц просрочки стоил бы 8 500 рублей за м², включая пени, упущенную аренду, проценты по кредиту. Что составило бы 427 млн на объекте 50 тыс м² ежемесячно. Мы исключили эти потери на каждом объекте.
                        </p>
                        <div class="about-econ-card__footer">
                            <span class="about-econ-card__highlight">Исключили потери 427 млн ₽/мес</span>
                        </div>
                    </article>

                    <article class="about-econ-card">
                        <div class="about-econ-card__top">
                            <div class="about-econ-card__metric">
                                <span class="about-econ-card__num">100%</span>
                                <span class="about-econ-card__period">возврат удержания</span>
                            </div>
                        </div>
                        <h4 class="about-econ-card__title">Наши Заказчики вернули полностью Гарантийное удержание</h4>
                        <p class="about-econ-card__desc">
                            На всех объектах — минимальное количество обращений и нареканий в гарантийный период. Заказчики получили обратно 5–10% от стоимости контракта. Для 50 тыс м² при стоимости контракта 2,5 млрд, вернули 250 млн рублей.
                        </p>
                        <div class="about-econ-card__footer">
                            <span class="about-econ-card__highlight">Вернули 250 млн ₽ при контракте 2,5 млрд</span>
                        </div>
                    </article>

                    <article class="about-econ-card">
                        <div class="about-econ-card__top">
                            <div class="about-econ-card__metric">
                                <span class="about-econ-card__num">в 1,5–2 раза</span>
                                <span class="about-econ-card__period">дольше ресурс</span>
                            </div>
                        </div>
                        <h4 class="about-econ-card__title">Продлили срок службы оборудования</h4>
                        <p class="about-econ-card__desc">
                            Продлили срок службы оборудования в 1,5–2 раза. На объектах, сданных 5–7 лет назад, оборудование работает без замены до сих пор.
                        </p>
                        <div class="about-econ-card__footer">
                            <span class="about-econ-card__highlight">5–7 лет работы без замены</span>
                        </div>
                    </article>

                    <article class="about-econ-card">
                        <div class="about-econ-card__top">
                            <div class="about-econ-card__metric">
                                <span class="about-econ-card__num">−1 280 ₽/м²</span>
                                <span class="about-econ-card__period">ежегодно</span>
                            </div>
                        </div>
                        <h4 class="about-econ-card__title">Снизили годовые эксплуатационные затраты</h4>
                        <p class="about-econ-card__desc">
                            Снизили годовые эксплуатационные затраты на 1 280 рублей на каждый квадратный метр площади здания. На объекте 50 000 м² экономия составляет 64 миллиона рублей ежегодно.
                        </p>
                        <div class="about-econ-card__footer">
                            <span class="about-econ-card__highlight">64 млн ₽ экономии ежегодно на 50 тыс. м²</span>
                        </div>
                    </article>

                    <article class="about-econ-card">
                        <div class="about-econ-card__top">
                            <div class="about-econ-card__metric">
                                <span class="about-econ-card__num">1,2 года</span>
                                <span class="about-econ-card__period">окупаемость</span>
                            </div>
                        </div>
                        <h4 class="about-econ-card__title">Быстрый срок окупаемости ПНР</h4>
                        <p class="about-econ-card__desc">
                            Срок окупаемости ПНР: при стоимости ПНР ~1 500 руб/м² — 1,2 года. Дальше — чистая экономия каждый год всего срока службы здания.
                        </p>
                        <div class="about-econ-card__footer">
                            <span class="about-econ-card__highlight">Дальше — чистая экономия каждый год</span>
                        </div>
                    </article>
                </div>

                <div class="about-stats-cta">
                    <div class="about-stats-cta__content">
                        <span class="about-stats-cta__badge">Подтвержденная практика</span>
                        <h3 class="about-stats-cta__title">Мы не обещаем. Мы сделали это 200 раз.</h3>
                        <p class="about-stats-cta__desc">90% Заказчиков обратились к нам повторно. Давайте обсудим Ваш объект.</p>
                    </div>
                    <div class="about-stats-cta__action">
                        <button type="button" class="btn btn-primary open-estimate-modal">Обсудить объект</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="about-why" id="about-why">
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
    <div class="section-header about-team-header" id="about-team">
        <h2 class="section-title">Наша команда</h2>
        <p class="section-subtitle">Ведущие специалисты в области инженерных систем</p>
    </div>
    <section class="structure-section team-section"
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
