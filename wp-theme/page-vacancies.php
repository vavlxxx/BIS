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
    $banner_subtitle = 'Приглашаем инженеров в команду профессионалов. Стабильная работа на знаковых объектах, высокая заработная плата, обучение и развитие в области комплексного ПНР и автоматики.';
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
                    <p>Зарплата от 170 000 ₽ на руки. Выплаты строго 2 раза в месяц без задержек.</p>
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
                <!-- Vacancy 1: Инженер АСУ ТП -->
                <article class="vacancy-card" id="asutp">
                    <div class="vacancy-card__head">
                        <div class="vacancy-card__meta-top">
                            <span class="vacancy-card__tag">Инженерия / АСУ ТП</span>
                            <span class="vacancy-card__date">Москва и объекты РФ</span>
                        </div>
                        <h2 class="vacancy-card__title">Инженер АСУ ТП систем ОВиК</h2>
                        <div class="vacancy-card__salary">от 170 000 ₽ <span class="vacancy-card__salary-note">за месяц, на руки</span></div>
                        <div class="vacancy-card__chips">
                            <span class="vacancy-chip">Опыт: 1–3 года</span>
                            <span class="vacancy-chip">График: 5/2 (+ 2 субботы)</span>
                            <span class="vacancy-chip">Формат: разъездной</span>
                            <span class="vacancy-chip">Оформление по ТК РФ</span>
                            <span class="vacancy-chip">Выплаты: 2 раза в месяц</span>
                        </div>
                    </div>

                    <div class="vacancy-card__body">
                        <div class="vacancy-section-block">
                            <h4 class="vacancy-section-block__title">Обязанности:</h4>
                            <ul class="vacancy-section-block__list">
                                <li>Знание и умение программирования для сред АРМ и ПЛК.</li>
                                <li>Наладка и испытания технологических функций АСУ ТП.</li>
                                <li>Обследование объекта автоматизации, анализ исходных данных и формирование ТЗ.</li>
                                <li>Участие в разработке и согласовании разделов проектной документации АСУ ТП.</li>
                                <li>Участие в индивидуальных и комплексных пусконаладочных работах (ПНР) систем ОВиК совместно со специалистами команды.</li>
                                <li>Проверка правильности подключения электродвигателей, приводов, датчиков и исполнительных механизмов.</li>
                                <li>Проверка силовых и управляющих цепей, аудит шкафов автоматики и щитового оборудования (сборка, маркировка, коммутация).</li>
                                <li>Диагностика и оперативный поиск неисправностей при запуске оборудования, корректировка принципиальных и функциональных схем.</li>
                                <li>Участие в подготовке исполнительной документации и фиксации результатов ПНР.</li>
                            </ul>
                        </div>

                        <div class="vacancy-section-block">
                            <h4 class="vacancy-section-block__title">Требования к кандидату:</h4>
                            <ul class="vacancy-section-block__list">
                                <li>Высшее профессиональное (техническое) образование.</li>
                                <li>Знания в области электротехники, теплоэнергетики, разработки и проектирования АСУ ТП оборудования систем АОВ.</li>
                                <li>Умение читать и составлять технологические схемы, структурные, принципиальные и монтажные схемы.</li>
                                <li>Умение проверять правильность монтажа и выполнять наладку средств КИПиА (датчики давления, температуры, расхода, электроприводы арматуры).</li>
                                <li>Уверенное владение Microsoft Office.</li>
                                <li>Готовность к командировкам (обязательно).</li>
                                <li class="vacancy-highlight"><em>Глубокий опыт ПНР вентиляции и гидравлики на старте не является обязательным — обучаем в процессе работы.</em></li>
                            </ul>
                        </div>

                        <div class="vacancy-section-block">
                            <h4 class="vacancy-section-block__title">Условия работы:</h4>
                            <ul class="vacancy-section-block__list">
                                <li>Официальное оформление по ТК РФ, стабильная зарплата 2 раза в месяц.</li>
                                <li>Оплачиваемый отпуск и больничные листы.</li>
                                <li>Оплата профильных курсов и сертификаций для решения реальных проектных задач.</li>
                                <li>Предоставление фирменной спецодежды и всех необходимых СИЗ.</li>
                                <li>График: 5/2 + две рабочие субботы в месяц (остальные две субботы — выходные).</li>
                                <li>Работа на строительных площадках Москвы, МО, РФ; оборудованные офисы в Москве и Мытищах.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="vacancy-card__foot">
                        <button type="button" class="btn btn-primary open-vacancy-modal" data-vacancy="Инженер АСУ ТП систем ОВиК">
                            Откликнуться на вакансию <span aria-hidden="true">→</span>
                        </button>
                        <a href="mailto:office@bis-rf.ru?subject=Отклик: Инженер АСУ ТП систем ОВиК" class="btn btn-outline">
                            Отправить резюме на почту
                        </a>
                    </div>
                </article>

                <!-- Vacancy 2: Инженер-электрик ПНР -->
                <article class="vacancy-card" id="electric">
                    <div class="vacancy-card__head">
                        <div class="vacancy-card__meta-top">
                            <span class="vacancy-card__tag">Электрика / ПНР</span>
                            <span class="vacancy-card__date">Москва и объекты РФ</span>
                        </div>
                        <h2 class="vacancy-card__title">Инженер-электрик ПНР ОВиК</h2>
                        <div class="vacancy-card__salary">от 170 000 ₽ <span class="vacancy-card__salary-note">за месяц, на руки</span></div>
                        <div class="vacancy-card__chips">
                            <span class="vacancy-chip">Опыт: 1–3 года</span>
                            <span class="vacancy-chip">График: 5/2 (+ 2 субботы)</span>
                            <span class="vacancy-chip">Формат: разъездной</span>
                            <span class="vacancy-chip">Оформление по ТК РФ</span>
                            <span class="vacancy-chip">Группа по ЭБ от III</span>
                        </div>
                    </div>

                    <div class="vacancy-card__body">
                        <div class="vacancy-section-block">
                            <h4 class="vacancy-section-block__title">Чем предстоит заниматься:</h4>
                            <ul class="vacancy-section-block__list">
                                <li>Выполнение пусконаладочных работ, индивидуальных испытаний и запусков инженерного оборудования.</li>
                                <li>Проверка правильности подключения оборудования перед запуском (электродвигатели, приводы, датчики, исполнительные механизмы).</li>
                                <li>Диагностика и поиск неисправностей в силовых и управляющих электрических цепях.</li>
                                <li>Аудит щитового оборудования: проверка сборки, маркировки, аппаратов защиты, коммутации и соответствия проекту.</li>
                                <li>Выявление ошибок монтажа и проектирования, предложение обоснованных технических решений.</li>
                                <li>Самостоятельное выполнение необходимых электромонтажных и наладочных работ на объекте.</li>
                                <li>Работа с проектной и рабочей документацией (ЭОМ, автоматизация), разработка и корректировка схем в AutoCAD по фактическому исполнению.</li>
                                <li>Подготовка исполнительной документации и актов по результатам ПНР.</li>
                                <li>Постепенное подключение к комплексному ПНР вентиляции, систем тепло- и холодоснабжения, гидравлики и автоматики.</li>
                            </ul>
                        </div>

                        <div class="vacancy-section-block">
                            <h4 class="vacancy-section-block__title">Что для нас важно:</h4>
                            <ul class="vacancy-section-block__list">
                                <li>Профильное техническое образование (высшее или среднее профессиональное).</li>
                                <li>Практический опыт работы с электрооборудованием и инженерными системами.</li>
                                <li>Уверенное чтение принципиальных, однолинейных и монтажных электрических схем.</li>
                                <li>Навыки работы в AutoCAD или аналогичном ПО для корректировки схем.</li>
                                <li>Знание ПУЭ и нормативных требований к электроустановкам.</li>
                                <li>Действующая группа по электробезопасности не ниже III.</li>
                                <li>Готовность работать непосредственно на объектах и отвечать за результат запуска.</li>
                                <li>Готовность к командировкам.</li>
                                <li class="vacancy-highlight"><em>Будет преимуществом: опыт ПНР вентиляционных установок, насосов, КИПиА, частотных преобразователей.</em></li>
                            </ul>
                        </div>

                        <div class="vacancy-section-block">
                            <h4 class="vacancy-section-block__title">Условия:</h4>
                            <ul class="vacancy-section-block__list">
                                <li>Работа по ТК РФ, стабильная выплата заработной платы 2 раза в месяц.</li>
                                <li>Оплачиваемый отпуск и больничные листы.</li>
                                <li>Реальное обучение пусконаладке вентиляции, гидравлических систем и холодильного оборудования.</li>
                                <li>Оплата курсов повышения квалификации за счет компании.</li>
                                <li>Выдача качественной спецодежды, СИЗ и профессионального инструмента.</li>
                                <li>График: 5/2 плюс две рабочие субботы в месяц.</li>
                                <li>Объекты в Москве, МО, РФ; комфортные офисы в Москве и Мытищах.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="vacancy-card__foot">
                        <button type="button" class="btn btn-primary open-vacancy-modal" data-vacancy="Инженер-электрик ПНР ОВиК">
                            Откликнуться на вакансию <span aria-hidden="true">→</span>
                        </button>
                        <a href="mailto:office@bis-rf.ru?subject=Отклик: Инженер-электрик ПНР ОВиК" class="btn btn-outline">
                            Отправить резюме на почту
                        </a>
                    </div>
                </article>
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
