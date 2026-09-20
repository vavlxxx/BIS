<?php
/*
Template Name: Расчет под ключ
*/

get_header();

$page_id = get_the_ID();
if (!$page_id || 'page' !== get_post_type($page_id)) {
    $turnkey_pages = get_pages(array(
        'meta_key'   => '_wp_page_template',
        'meta_value' => 'page-turnkey.php',
        'number'     => 1,
    ));
    if (!empty($turnkey_pages)) {
        $page_id = $turnkey_pages[0]->ID;
    } else {
        $turnkey_page = get_page_by_path('calculators/turnkey');
        if (!$turnkey_page) {
            $turnkey_page = get_page_by_path('turnkey');
        }
        if ($turnkey_page) {
            $page_id = $turnkey_page->ID;
        }
    }
}

$banner_title = $page_id ? get_post_meta($page_id, 'bis_page_banner_title', true) : '';
$banner_subtitle = $page_id ? get_post_meta($page_id, 'bis_page_banner_subtitle', true) : '';
$banner_title = $banner_title ? $banner_title : ($page_id ? get_the_title($page_id) : 'Расчет под ключ');

if (!$banner_subtitle) {
    $banner_subtitle = 'Расчет будет произведен инженерами «БИС - Баланс Инженерных Систем».';
}

$banner_image = $page_id ? bis_get_page_banner_image_url($page_id) : '';
if (!$banner_image) {
    $banner_image = get_template_directory_uri() . '/assets/img/1.webp';
}
?>

<main class="turnkey-page">
    <!-- Standard Hero Section -->
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
        </div>
    </section>

    <!-- Standard Breadcrumbs Section -->
    <section class="breadcrumbs-section">
        <nav class="project-breadcrumbs mw-1400px">
            <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
            <span class="breadcrumbs-delimiter">/</span>
            <a href="<?php echo esc_url(home_url('/calculators/')); ?>">Калькуляторы</a>
            <span class="breadcrumbs-delimiter">/</span>
            <span><?php echo esc_html($banner_title); ?></span>
        </nav>
    </section>

    <!-- Main Turnkey Section -->
    <section class="turnkey-section">
        <div class="turnkey-container mw-1400px">
            <div class="turnkey-layout">
                <!-- Left: Information & SEO Content -->
                <div class="turnkey-info">
                    <div class="turnkey-info__block">
                        <span class="turnkey-tag">Инженерный расчет БИС</span>
                        <h2 class="turnkey-info__title">Профессиональный расчет систем вентиляции и противодымной защиты</h2>
                        <p class="turnkey-info__text">
                            Инженерные расчеты вентиляционных систем требуют глубоких отраслевых знаний, владения методиками ГОСТ и нормами пожарной безопасности. Если у вас нет времени или возможности рассчитывать параметры самостоятельно, инженеры «БИС — Баланс Инженерных Систем» выполнят комплексный расчет под ключ для вашего объекта.
                        </p>
                    </div>

                    <!-- Benefits Grid -->
                    <div class="turnkey-benefits-grid">
                        <div class="turnkey-benefit-item">
                            <div class="turnkey-benefit-item__icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                            <div class="turnkey-benefit-item__content">
                                <h3>Нормативная точность</h3>
                                <p>Расчеты строго по ГОСТ Р 53300-2009, ГОСТ 34060-2017, СП 7.13130.2013 и методикам АВОК.</p>
                            </div>
                        </div>

                        <div class="turnkey-benefit-item">
                            <div class="turnkey-benefit-item__icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="0"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                            </div>
                            <div class="turnkey-benefit-item__content">
                                <h3>Точность до 95%</h3>
                                <p>Учитываем реальную аэродинамику здания, характеристики вентиляторов, клапанов и теплопотери.</p>
                            </div>
                        </div>

                        <div class="turnkey-benefit-item">
                            <div class="turnkey-benefit-item__icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/></svg>
                            </div>
                            <div class="turnkey-benefit-item__content">
                                <h3>Официальный отчет</h3>
                                <p>Вы получаете готовую пояснительную записку, таблицу параметров и смету для согласований.</p>
                            </div>
                        </div>

                        <div class="turnkey-benefit-item">
                            <div class="turnkey-benefit-item__icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                            <div class="turnkey-benefit-item__content">
                                <h3>Срок — от 2 дней</h3>
                                <p>Быстрая обработка проектной документации и оперативная обратная связь от ведущего инженера.</p>
                            </div>
                        </div>
                    </div>

                    <!-- What's included block -->
                    <div class="turnkey-included-block">
                        <h3 class="turnkey-included-title">Что входит в расчет под ключ:</h3>
                        <ul class="turnkey-included-list">
                            <li>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                <span>Определение требуемых расходов воздуха через ДПУ и клапаны дымоудаления</span>
                            </li>
                            <li>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                <span>Расчет систем подпора воздуха в шахты лифтов, лестничные клетки, тамбур-шлюзы и зоны ПБЗ</span>
                            </li>
                            <li>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                <span>Расчет фактической плотности и допустимых утечек воздуховодов по классам герметичности (A, B, C, D)</span>
                            </li>
                            <li>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                <span>Подбор вентиляционного оборудования и детализированная смета на пусконаладку</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Steps block -->
                    <div class="turnkey-steps-block">
                        <h3 class="turnkey-steps-title">Порядок работы:</h3>
                        <div class="turnkey-steps-grid">
                            <div class="turnkey-step-card">
                                <span class="turnkey-step-num">01</span>
                                <h4>Заявка и чертежи</h4>
                                <p>Вы отправляете форму и прикрепляете проектную документацию (PDF, DWG, схемы).</p>
                            </div>
                            <div class="turnkey-step-card">
                                <span class="turnkey-step-num">02</span>
                                <h4>Инженерный анализ</h4>
                                <p>Инженер БИС изучает проект, производит перерасчет и проверяет соответствие нормам.</p>
                            </div>
                            <div class="turnkey-step-card">
                                <span class="turnkey-step-num">03</span>
                                <h4>Готовый результат</h4>
                                <p>В течение 2 дней вы получаете смету, расчетные ведомости и рекомендации удобным способом.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Form Card -->
                <div class="turnkey-form-wrapper">
                    <div class="turnkey-form-card">
                        <div class="turnkey-form-card__head">
                            <h2 class="turnkey-form-card__title">Заявка на расчет под ключ</h2>
                            <p class="turnkey-form-card__subtitle">Вы получите исчерпывающую смету с точностью до 95% в течение 2 дней</p>
                        </div>

                        <form class="contact-form turnkey-form" id="turnkeyCalcForm" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="bis_submit_estimate">
                            <input type="hidden" name="request_type" value="turnkey_calc">

                            <div class="form-group">
                                <label for="turnkeyName">Имя *</label>
                                <input type="text" id="turnkeyName" name="name" required placeholder="Ваше имя" autocomplete="name">
                            </div>

                            <div class="form-group">
                                <label for="turnkeyPhone">Телефон *</label>
                                <input type="tel" id="turnkeyPhone" name="phone" required placeholder="+7 (___) ___-__-__" autocomplete="tel">
                            </div>

                            <div class="form-group">
                                <label for="turnkeyEmail">Email *</label>
                                <input type="email" id="turnkeyEmail" name="email" required placeholder="example@mail.ru" autocomplete="email">
                            </div>

                            <div class="form-group">
                                <label for="turnkeyFile">Проектная документация</label>
                                <div class="turnkey-file-wrap">
                                    <input type="file" id="turnkeyFile" name="project_doc" accept=".pdf,.dwg,.doc,.docx,.xls,.xlsx,.zip,.rar,.7z">
                                </div>
                                <span class="turnkey-file-hint">PDF, DWG, DOCX, XLSX, архивы ZIP/RAR</span>
                            </div>

                            <div class="form-group">
                                <label for="turnkeyComment">Комментарий</label>
                                <textarea id="turnkeyComment" name="comment" rows="3" placeholder="Дополнительная информация..."></textarea>
                            </div>

                            <div class="form-group">
                                <label for="turnkeyMessenger">Куда прислать расчет?</label>
                                <div class="select-wrapper">
                                    <select id="turnkeyMessenger" name="messenger">
                                        <option value="MAX">MAX</option>
                                        <option value="Telegram">Telegram</option>
                                        <option value="По телефону">По телефону</option>
                                    </select>
                                </div>
                            </div>

                            <?php echo do_shortcode('[hcaptcha auto="true" force="true"]'); ?>

                            <button type="submit" class="btn btn-primary btn-block turnkey-submit-btn">Отправить</button>
                            <p class="form-consent">Нажимая на кнопку, вы даете согласие на обработку своих персональных данных и соглашаетесь с Политикой конфиденциальности сайта</p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
