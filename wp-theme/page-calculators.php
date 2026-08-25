<?php
/*
Template Name: Калькуляторы
*/
get_header();

$page_id = get_the_ID();
$banner_title = get_post_meta($page_id, 'bis_page_banner_title', true);
$banner_subtitle = get_post_meta($page_id, 'bis_page_banner_subtitle', true);
$banner_title = $banner_title ? $banner_title : 'Инженерные калькуляторы';

if (!$banner_subtitle) {
    $banner_subtitle = 'Профессиональный комплекс онлайн-расчетов противодымной вентиляции, подпора воздуха и проверки герметичности воздуховодов по нормативам ГОСТ и АВОК.';
}

$banner_image = bis_get_page_banner_image_url($page_id);
?>

<main class="calculators-page">
    <!-- Standard Hero Section -->
    <section class="news-hero" style="padding-inline: 8vw;">
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
            <span><?php echo esc_html($banner_title); ?></span>
        </nav>
    </section>

    <!-- Main Calculators Section -->
    <section class="calculators-section">
        <div class="calculators-section__container mw-1400px">
            <!-- 3 Main Block Selectors -->
            <div class="calc-nav-grid">
                <div class="calc-nav-card active" data-block="block1">
                    <span class="calc-nav-card__tag">Блок 1 • ГОСТ Р 53300-2009</span>
                    <span class="calc-nav-card__title">Аэродинамика и утечки шахты</span>
                    <span class="calc-nav-card__desc">Поэтажный расчет потерь давления и утечек через закрытые дымовые клапаны (Приложение Б)</span>
                </div>

                <div class="calc-nav-card" data-block="block2">
                    <span class="calc-nav-card__tag">Блок 2 • Рекомендации АВОК</span>
                    <span class="calc-nav-card__title">Противодымная вентиляция (6 видов)</span>
                    <span class="calc-nav-card__desc">Расчет дымоудаления из коридоров, подпора в ЛК, шахты лифтов, зоны ПБЗ и тамбур-шлюзы</span>
                </div>

                <div class="calc-nav-card" data-block="block3">
                    <span class="calc-nav-card__tag">Блок 3 • ГОСТ 34060</span>
                    <span class="calc-nav-card__title">Конструктор и герметичность сети</span>
                    <span class="calc-nav-card__desc">Развернутая площадь фасонных элементов и проверка классов герметичности (A, B, C)</span>
                </div>
            </div>

        <!-- ====================================================================
             BLOCK 1: GOST R 53300-2009 (Appendix B)
             ==================================================================== -->
        <div id="panel-block1" class="calc-block-content" style="display: block;">
            <div class="calc-layout-grid">
                <!-- Left Side: Forms & Floors Table -->
                <div class="calc-main-column">
                    <!-- General Inputs Card -->
                    <div class="calc-card">
                        <div class="calc-card__head">
                            <h2 class="calc-card__title">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                Исходные параметры здания и вентилятора
                            </h2>
                            <span class="calc-norm-pill">ГОСТ Р 53300-2009</span>
                        </div>

                        <div class="calc-grid-fields calc-grid-fields--3cols">
                            <div class="calc-form-group">
                                <label for="b1_Lpr">Проектный расход Lпр <small>Расход вентилятора</small></label>
                                <div class="calc-field-wrap">
                                    <input type="number" id="b1_Lpr" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="34760" step="50">
                                    <span class="calc-field-unit">м³/ч</span>
                                </div>
                            </div>

                            <div class="calc-form-group">
                                <label for="b1_Psv">Давление вентилятора Psv <small>По номограмме при 20°C</small></label>
                                <div class="calc-field-wrap">
                                    <input type="number" id="b1_Psv" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="1550" step="10">
                                    <span class="calc-field-unit">Па</span>
                                </div>
                            </div>

                            <div class="calc-form-group">
                                <label for="b1_Tpg">Температура горения Тпг <small>В очаге пожара</small></label>
                                <div class="calc-field-wrap">
                                    <input type="number" id="b1_Tpg" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="400">
                                    <span class="calc-field-unit">К</span>
                                </div>
                            </div>

                            <div class="calc-form-group">
                                <label for="b1_Tpom">Температура в помещении <small>Внутренний воздух</small></label>
                                <div class="calc-field-wrap">
                                    <input type="number" id="b1_Tpom" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="18">
                                    <span class="calc-field-unit">°C</span>
                                </div>
                            </div>

                            <div class="calc-form-group">
                                <label for="b1_h_top">Отметка выброса <small>Верх шахты / вентилятор</small></label>
                                <div class="calc-field-wrap">
                                    <input type="number" id="b1_h_top" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="146.95" step="0.1">
                                    <span class="calc-field-unit">м</span>
                                </div>
                            </div>

                            <div class="calc-form-group">
                                <label for="b1_h_bot">Отметка открытого клапана <small>Нижний обслуживаемый этаж</small></label>
                                <div class="calc-field-wrap">
                                    <input type="number" id="b1_h_bot" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="9.82" step="0.1">
                                    <span class="calc-field-unit">м</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Base Shaft Dimensions Quick Configuration Bar -->
                    <div class="shaft-quick-bar">
                        <div class="shaft-quick-bar__title">
                            <span>Типовые параметры для шахты (быстрая настройка всех этажей):</span>
                            <button type="button" id="b1BtnApplyDefaults" class="btn btn-outline btn--small">
                                Применить ко всем этажам
                            </button>
                        </div>
                        <div class="calc-grid-fields calc-grid-fields--3cols">
                            <div class="calc-form-group">
                                <label><small>Типовое сечение шахты A × B (м)</small></label>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <input type="number" id="b1_def_shaftA" class="calc-field-input" value="1.1" step="0.1" placeholder="A (м)">
                                    <span>×</span>
                                    <input type="number" id="b1_def_shaftB" class="calc-field-input" value="0.8" step="0.1" placeholder="B (м)">
                                </div>
                            </div>
                            <div class="calc-form-group">
                                <label><small>Типовой клапан a × b (м)</small></label>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <input type="number" id="b1_def_valveA" class="calc-field-input" value="1.0" step="0.1" placeholder="a (м)">
                                    <span>×</span>
                                    <input type="number" id="b1_def_valveB" class="calc-field-input" value="0.5" step="0.1" placeholder="b (м)">
                                </div>
                            </div>
                            <div class="calc-form-group">
                                <label><small>Типовая высота этажа li (м)</small></label>
                                <input type="number" id="b1_def_floorH" class="calc-field-input" value="4.3" step="0.1" placeholder="li (м)">
                            </div>
                        </div>
                    </div>

                    <!-- Clean Floor Network Table -->
                    <div class="calc-card">
                        <div class="calc-card__head">
                            <h2 class="calc-card__title">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                                Распределение давления и расходов по этажам
                            </h2>
                            <span class="calc-norm-pill">Поэтажный расчет</span>
                        </div>

                        <div class="calc-table-container">
                            <table class="calc-clean-table">
                                <thead>
                                    <tr>
                                        <th style="width: 70px;">Этаж</th>
                                        <th style="width: 90px;">Длина li</th>
                                        <th style="width: 140px;">Сечение шахты A×B</th>
                                        <th style="width: 160px;">КМС (сопротивление)</th>
                                        <th style="width: 140px;">Клапан a×b</th>
                                        <th>Давление Psi</th>
                                        <th>Утечка Gdpn</th>
                                        <th>Расход Li</th>
                                        <th style="width: 40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="b1FloorsTableBody">
                                    <!-- Populated via JavaScript -->
                                </tbody>
                            </table>
                        </div>

                        <div class="calc-table-btns" style="align-items:center;">
                            <button type="button" id="b1BtnAddFloor" class="btn btn-outline btn--small">
                                + Добавить этаж
                            </button>
                            <div style="display:inline-flex; align-items:center; gap:6px; margin-left:auto;">
                                <label for="b1_floor_count_input" style="font-size:12px; color:var(--text-light); white-space:nowrap;">Кол-во этажей:</label>
                                <input type="number" id="b1_floor_count_input" value="20" min="1" max="100" class="calc-field-input" style="width:65px; height:34px; text-align:center; padding:4px;">
                                <button type="button" id="b1BtnGenFloors" class="btn btn-ghost btn--small">
                                    Сгенерировать
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Sticky Summary Results Card -->
                <div class="calc-results-sidebar">
                    <div class="calc-summary-panel">
                        <div class="calc-summary-panel__header">
                            Параметры вентилятора
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        </div>

                        <div class="calc-metric-row">
                            <span class="calc-metric-row__label">Итоговый расход в точке забора L₀:</span>
                            <span id="b1_res_L0" class="calc-metric-row__value">18 383 <span class="unit">м³/ч</span></span>
                        </div>

                        <div class="calc-metric-row">
                            <span class="calc-metric-row__label">Давление перед вентилятором Psa:</span>
                            <span id="b1_res_Psa" class="calc-metric-row__value">1 694 <span class="unit">Па</span></span>
                        </div>

                        <div class="calc-metric-row">
                            <span class="calc-metric-row__label">Массовый расход G₀:</span>
                            <span id="b1_res_G0" class="calc-metric-row__value">6.19 <span class="unit">кг/с</span></span>
                        </div>

                        <div class="calc-metric-row">
                            <span class="calc-metric-row__label">Суммарные утечки через закрытые клапаны:</span>
                            <span id="b1_res_Leak" class="calc-metric-row__value">9 117 <span class="unit">м³/ч</span></span>
                        </div>

                        <button type="button" class="btn-calc-cta" onclick="window.calcEngineOpenProtocol()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            Сформировать протокол
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ====================================================================
             BLOCK 2: AVOK RECOMMENDATIONS (6 CALCULATORS)
             ==================================================================== -->
        <div id="panel-block2" class="calc-block-content" style="display: none;">
            <div class="avok-tabs-panel">
                <div class="avok-tabs-header">Выберите подкалькулятор по методике АВОК:</div>
                <div class="avok-tabs-list">
                    <div class="avok-tab-item active" data-avok="du4_1">
                        <span class="code">ДУ4-1</span>
                        <span>Дымоудаление из коридора</span>
                    </div>
                    <div class="avok-tab-item" data-avok="pd4_1">
                        <span class="code">ПД4-1</span>
                        <span>Подпор в лестничную клетку (ЛК)</span>
                    </div>
                    <div class="avok-tab-item" data-avok="pd4_2">
                        <span class="code">ПД4-2</span>
                        <span>Подпор в шахту лифта</span>
                    </div>
                    <div class="avok-tab-item" data-avok="pd4_7">
                        <span class="code">ПД4-7</span>
                        <span>Зона ПБЗ (открытая дверь)</span>
                    </div>
                    <div class="avok-tab-item" data-avok="pd4_8">
                        <span class="code">ПД4-8</span>
                        <span>Тамбур-шлюз перед ЛК</span>
                    </div>
                    <div class="avok-tab-item" data-avok="pd7_a">
                        <span class="code">ПД7-а</span>
                        <span>Зона ПБЗ (закрытая дверь)</span>
                    </div>
                </div>
            </div>

            <div class="calc-layout-grid">
                <div class="calc-main-column">
                    <!-- AVOK DU4-1 -->
                    <div id="avok-du4_1" class="avok-calc-content" style="display: block;">
                        <div class="calc-card">
                            <div class="calc-card__head">
                                <h2 class="calc-card__title">Расчет ДУ4-1: Дымоудаление из коридора</h2>
                                <span class="calc-norm-pill">АВОК 5.5.1-2018</span>
                            </div>
                            <div class="calc-grid-fields">
                                <div class="calc-form-group">
                                    <label for="du4_1_Lk">Длина коридора Lк</label>
                                    <div class="calc-field-wrap">
                                        <input type="number" id="du4_1_Lk" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="24" step="0.5">
                                        <span class="calc-field-unit">м</span>
                                    </div>
                                </div>
                                <div class="calc-form-group">
                                    <label for="du4_1_Bk">Ширина коридора Bк</label>
                                    <div class="calc-field-wrap">
                                        <input type="number" id="du4_1_Bk" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="2.4" step="0.1">
                                        <span class="calc-field-unit">м</span>
                                    </div>
                                </div>
                                <div class="calc-form-group">
                                    <label for="du4_1_Hk">Высота коридора Hк</label>
                                    <div class="calc-field-wrap">
                                        <input type="number" id="du4_1_Hk" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="2.8" step="0.1">
                                        <span class="calc-field-unit">м</span>
                                    </div>
                                </div>
                                <div class="calc-form-group">
                                    <label for="du4_1_Q">Мощность очага пожара Q</label>
                                    <div class="calc-field-wrap">
                                        <input type="number" id="du4_1_Q" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="1200" step="50">
                                        <span class="calc-field-unit">кВт</span>
                                    </div>
                                </div>
                                <div class="calc-form-group">
                                    <label for="du4_1_w_door">Ширина двери выхода</label>
                                    <div class="calc-field-wrap">
                                        <input type="number" id="du4_1_w_door" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="0.9" step="0.05">
                                        <span class="calc-field-unit">м</span>
                                    </div>
                                </div>
                                <div class="calc-form-group">
                                    <label for="du4_1_h_door">Высота двери выхода</label>
                                    <div class="calc-field-wrap">
                                        <input type="number" id="du4_1_h_door" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="2.1" step="0.05">
                                        <span class="calc-field-unit">м</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AVOK PD4-1 -->
                    <div id="avok-pd4_1" class="avok-calc-content" style="display: none;">
                        <div class="calc-card">
                            <div class="calc-card__head">
                                <h2 class="calc-card__title">Расчет ПД4-1: Подпор воздуха в лестничную клетку (ЛК)</h2>
                                <span class="calc-norm-pill">АВОК / СП 7.13130</span>
                            </div>
                            <div class="calc-grid-fields">
                                <div class="calc-form-group">
                                    <label for="pd4_1_floors">Количество этажей здания</label>
                                    <input type="number" id="pd4_1_floors" class="calc-field-input calc-auto-recalc" value="16">
                                </div>
                                <div class="calc-form-group">
                                    <label for="pd4_1_building_type">Назначение здания</label>
                                    <select id="pd4_1_building_type" class="calc-field-select calc-auto-recalc">
                                        <option value="living" selected>Жилое здание (V в двери ≥ 1.3 м/с)</option>
                                        <option value="public">Общественное здание (V в двери ≥ 1.5 м/с)</option>
                                    </select>
                                </div>
                                <div class="calc-form-group">
                                    <label for="pd4_1_b_door">Ширина двери выхода в ЛК</label>
                                    <div class="calc-field-wrap">
                                        <input type="number" id="pd4_1_b_door" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="0.9" step="0.05">
                                        <span class="calc-field-unit">м</span>
                                    </div>
                                </div>
                                <div class="calc-form-group">
                                    <label for="pd4_1_h_door">Высота двери выхода в ЛК</label>
                                    <div class="calc-field-wrap">
                                        <input type="number" id="pd4_1_h_door" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="2.1" step="0.05">
                                        <span class="calc-field-unit">м</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AVOK PD4-2 -->
                    <div id="avok-pd4_2" class="avok-calc-content" style="display: none;">
                        <div class="calc-card">
                            <div class="calc-card__head">
                                <h2 class="calc-card__title">Расчет ПД4-2: Подпор в шахту лифта</h2>
                                <span class="calc-norm-pill">АВОК / СП 7.13130</span>
                            </div>
                            <div class="calc-grid-fields">
                                <div class="calc-form-group">
                                    <label for="pd4_2_floors">Количество этажей лифтовой шахты</label>
                                    <input type="number" id="pd4_2_floors" class="calc-field-input calc-auto-recalc" value="16">
                                </div>
                                <div class="calc-form-group">
                                    <label for="pd4_2_elevators">Количество шахт лифтов в группе</label>
                                    <input type="number" id="pd4_2_elevators" class="calc-field-input calc-auto-recalc" value="1">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AVOK PD4-7 -->
                    <div id="avok-pd4_7" class="avok-calc-content" style="display: none;">
                        <div class="calc-card">
                            <div class="calc-card__head">
                                <h2 class="calc-card__title">Расчет ПД4-7: Зона ПБЗ (открытая дверь)</h2>
                                <span class="calc-norm-pill">АВОК / СП 7.13130</span>
                            </div>
                            <div class="calc-grid-fields">
                                <div class="calc-form-group">
                                    <label for="pd4_7_w">Ширина дверного проема ПБЗ</label>
                                    <div class="calc-field-wrap">
                                        <input type="number" id="pd4_7_w" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="1.0" step="0.05">
                                        <span class="calc-field-unit">м</span>
                                    </div>
                                </div>
                                <div class="calc-form-group">
                                    <label for="pd4_7_h">Высота дверного проема ПБЗ</label>
                                    <div class="calc-field-wrap">
                                        <input type="number" id="pd4_7_h" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="2.1" step="0.05">
                                        <span class="calc-field-unit">м</span>
                                    </div>
                                </div>
                                <div class="calc-form-group">
                                    <label for="pd4_7_v">Нормируемая скорость в проеме</label>
                                    <div class="calc-field-wrap">
                                        <input type="number" id="pd4_7_v" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="1.3" step="0.1">
                                        <span class="calc-field-unit">м/с</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AVOK PD4-8 -->
                    <div id="avok-pd4_8" class="avok-calc-content" style="display: none;">
                        <div class="calc-card">
                            <div class="calc-card__head">
                                <h2 class="calc-card__title">Расчет ПД4-8: Тамбур-шлюз перед ЛК</h2>
                                <span class="calc-norm-pill">АВОК / СП 7.13130</span>
                            </div>
                            <div class="calc-grid-fields">
                                <div class="calc-form-group">
                                    <label for="pd4_8_w">Ширина проема тамбур-шлюза</label>
                                    <div class="calc-field-wrap">
                                        <input type="number" id="pd4_8_w" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="0.9" step="0.05">
                                        <span class="calc-field-unit">м</span>
                                    </div>
                                </div>
                                <div class="calc-form-group">
                                    <label for="pd4_8_h">Высота проема тамбур-шлюза</label>
                                    <div class="calc-field-wrap">
                                        <input type="number" id="pd4_8_h" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="2.1" step="0.05">
                                        <span class="calc-field-unit">м</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AVOK PD7-a -->
                    <div id="avok-pd7_a" class="avok-calc-content" style="display: none;">
                        <div class="calc-card">
                            <div class="calc-card__head">
                                <h2 class="calc-card__title">Расчет ПД7-а: Зона ПБЗ (закрытая дверь)</h2>
                                <span class="calc-norm-pill">АВОК / СП 7.13130</span>
                            </div>
                            <div class="calc-grid-fields">
                                <div class="calc-form-group">
                                    <label for="pd7_a_doors">Количество дверей в зоне ПБЗ</label>
                                    <input type="number" id="pd7_a_doors" class="calc-field-input calc-auto-recalc" value="2">
                                </div>
                                <div class="calc-form-group">
                                    <label for="pd7_a_reqP">Требуемое избыточное давление</label>
                                    <div class="calc-field-wrap">
                                        <input type="number" id="pd7_a_reqP" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="20" step="5">
                                        <span class="calc-field-unit">Па</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Sticky Summary Results Card -->
                <div class="calc-results-sidebar">
                    <div class="calc-summary-panel">
                        <div class="calc-summary-panel__header">
                            Параметры вентилятора
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        </div>

                        <div class="calc-metric-row">
                            <span class="calc-metric-row__label">Требуемый расход воздуха L:</span>
                            <span id="avok_res_main_val" class="calc-metric-row__value">26 400 <span class="unit">м³/ч</span></span>
                        </div>

                        <div class="calc-metric-row">
                            <span class="calc-metric-row__label">Давление / перепад P:</span>
                            <span id="avok_res_sub1_val" class="calc-metric-row__value">380 <span class="unit">Па</span></span>
                        </div>

                        <div class="calc-metric-row">
                            <span class="calc-metric-row__label">Дополнительный показатель:</span>
                            <span id="avok_res_sub2_val" class="calc-metric-row__value">180 <span class="unit">°C</span></span>
                        </div>

                        <button type="button" class="btn-calc-cta" onclick="window.calcEngineOpenProtocol()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            Сформировать протокол
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ====================================================================
             BLOCK 3: GOST 34060 DUCT NETWORK CONSTRUCTOR & LEAKAGE (APK)
             ==================================================================== -->
        <div id="panel-block3" class="calc-block-content" style="display: none;">
            <div class="calc-layout-grid">
                <div class="calc-main-column">
                    <!-- Aerodynamic Test Parameters Card -->
                    <div class="calc-card">
                        <div class="calc-card__head">
                            <h2 class="calc-card__title">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                Параметры аэродинамического испытания
                            </h2>
                            <span class="calc-norm-pill">ГОСТ 34060 / СП 60.13330</span>
                        </div>

                        <div class="calc-grid-fields calc-grid-fields--3cols">
                            <div class="calc-form-group">
                                <label for="b3_factP">Фактическое давление Pф <small>При испытании воздуховода</small></label>
                                <div class="calc-field-wrap">
                                    <input type="number" id="b3_factP" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="400" step="10">
                                    <span class="calc-field-unit">Па</span>
                                </div>
                            </div>

                            <div class="calc-form-group">
                                <label for="b3_factL">Фактический расход утечки Lф <small>Показания расходомера</small></label>
                                <div class="calc-field-wrap">
                                    <input type="number" id="b3_factL" class="calc-field-input calc-field-input--with-unit calc-auto-recalc" value="25.4" step="0.5">
                                    <span class="calc-field-unit">м³/ч</span>
                                </div>
                            </div>

                            <div class="calc-form-group">
                                <label for="b3_reqClass">Требуемый класс герметичности <small>По проектной документации</small></label>
                                <select id="b3_reqClass" class="calc-field-select calc-auto-recalc">
                                    <option value="A">Класс A (Низкая плотность, C=0.027)</option>
                                    <option value="B" selected>Класс B (Плотный, C=0.009)</option>
                                    <option value="C">Класс C (Высокая плотность, C=0.003)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Duct Network Builder (from APK) -->
                    <div class="calc-card">
                        <div class="calc-card__head">
                            <h2 class="calc-card__title">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                                Конструктор испытываемого воздуховода
                            </h2>
                            <span class="calc-norm-pill">Нажмите элемент для добавления</span>
                        </div>

                        <div class="elements-grid-selector">
                            <div class="element-select-card" data-type="D1">
                                <span class="icon">⭕</span>
                                <span class="name">Прямой круглый (D1)</span>
                            </div>
                            <div class="element-select-card" data-type="D2">
                                <span class="icon">▭</span>
                                <span class="name">Прямой прям. (D2)</span>
                            </div>
                            <div class="element-select-card" data-type="O1">
                                <span class="icon">↪️</span>
                                <span class="name">Отвод круглый (O1)</span>
                            </div>
                            <div class="element-select-card" data-type="O2">
                                <span class="icon">🔄</span>
                                <span class="name">Отвод прям. (O2)</span>
                            </div>
                            <div class="element-select-card" data-type="A3">
                                <span class="icon">🔻</span>
                                <span class="name">Переход прям. (A3)</span>
                            </div>
                            <div class="element-select-card" data-type="E1">
                                <span class="icon">🔘</span>
                                <span class="name">Заглушка круг (E1)</span>
                            </div>
                            <div class="element-select-card" data-type="E2">
                                <span class="icon">⬛</span>
                                <span class="name">Заглушка прям (E2)</span>
                            </div>
                        </div>

                        <div class="calc-table-container">
                            <table class="calc-clean-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">№</th>
                                        <th style="text-align:left;">Наименование элемента</th>
                                        <th style="width: 80px;">Тип</th>
                                        <th style="text-align:left;">Геометрические размеры</th>
                                        <th style="width: 120px;">Площадь S</th>
                                        <th style="width: 40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="b3ElementsTableBody">
                                    <!-- Populated via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Sticky Summary Results Card -->
                <div class="calc-results-sidebar">
                    <div class="calc-summary-panel">
                        <div class="calc-summary-panel__header">
                            Проверка герметичности
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>

                        <div class="calc-metric-row">
                            <span class="calc-metric-row__label">Развернутая площадь сети S:</span>
                            <span id="b3_res_totalS" class="calc-metric-row__value">27.75 <span class="unit">м²</span></span>
                        </div>

                        <div class="calc-metric-row">
                            <span class="calc-metric-row__label">Фактическая утечка Lф.ут:</span>
                            <span id="b3_res_factLeak" class="calc-metric-row__value">0.91 <span class="unit">м³/(ч·м²)</span></span>
                        </div>

                        <div class="calc-metric-row">
                            <span class="calc-metric-row__label">Допустимая утечка Lдоп (ГОСТ 34060):</span>
                            <span id="b3_res_allowLeak" class="calc-metric-row__value">1.59 <span class="unit">м³/(ч·м²)</span></span>
                        </div>

                        <div id="b3_verdict_badge" class="calc-status-badge calc-status-badge--success">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            СООТВЕТСТВУЕТ КЛАССУ B
                        </div>

                        <button type="button" class="btn-calc-cta" onclick="window.calcEngineOpenProtocol()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            Сформировать протокол
                        </button>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ====================================================================
         IN-PAGE MODAL: ADD / EDIT DUCT ELEMENT (BLOCK 3)
         ==================================================================== -->
    <div id="calcElementModal" class="calc-modal-overlay">
        <div class="calc-modal-box calc-modal-box--medium">
            <div class="calc-modal-box__head">
                <h3 id="calcElementModalTitle" class="calc-modal-box__title">Добавление элемента воздуховода</h3>
                <button type="button" class="calc-modal-box__close" onclick="window.calcEngineCloseElementModal()">&times;</button>
            </div>

            <div class="calc-modal-box__body">
                <div class="element-modal-type-badge">
                    <span id="elModalIcon" class="icon">⭕</span>
                    <div>
                        <div id="elModalTypeName" class="name">Прямой круглый участок</div>
                        <span id="elModalTypeTag" class="tag">D1</span>
                    </div>
                </div>

                <form id="calcElementForm" onsubmit="event.preventDefault(); window.calcEngineSaveElement();">
                    <div id="elModalFieldsContainer" class="calc-grid-fields">
                        <!-- Populated dynamically based on element type -->
                    </div>

                    <div class="element-modal-preview">
                        <span class="label">Развернутая площадь элемента S:</span>
                        <span id="elModalCalculatedArea" class="value">0.00 <span class="unit">м²</span></span>
                    </div>
                </form>
            </div>

            <div class="calc-modal-box__foot">
                <button type="button" class="btn btn-outline" onclick="window.calcEngineCloseElementModal()">
                    Отмена
                </button>
                <button type="button" class="btn btn-primary" onclick="window.calcEngineSaveElement()">
                    Добавить в спецификацию
                </button>
            </div>
        </div>
    </div>

    <!-- ====================================================================
         OFFICIAL PROTOCOL MODAL & PRINT CONTAINER
         ==================================================================== -->
    <div id="calcProtocolModal" class="calc-modal-overlay">
        <div class="calc-modal-box">
            <div class="calc-modal-box__head">
                <h3 class="calc-modal-box__title">Официальный протокол расчета и испытаний</h3>
                <button type="button" class="calc-modal-box__close" onclick="window.calcEngineCloseProtocol()">&times;</button>
            </div>

            <div class="calc-modal-box__body">
                <!-- Editable Meta Fields Bar -->
                <div class="calc-card" style="margin-bottom: 20px; padding: 20px; background: var(--bg-alt);">
                    <div style="font-size: 13px; font-weight: bold; margin-bottom: 12px; color: var(--dark); text-transform: uppercase;">
                        Реквизиты протокола для печати:
                    </div>
                    <div class="calc-grid-fields calc-grid-fields--3cols">
                        <div class="calc-form-group">
                            <label><small>№ Протокола</small></label>
                            <input type="text" class="calc-field-input" value="1" oninput="window.calcEngineUpdateMeta('number', this.value)">
                        </div>
                        <div class="calc-form-group">
                            <label><small>Дата составления</small></label>
                            <input type="date" class="calc-field-input" value="<?php echo date('Y-m-d'); ?>" oninput="window.calcEngineUpdateMeta('date', this.value)">
                        </div>
                        <div class="calc-form-group">
                            <label><small>Объект / Адрес</small></label>
                            <input type="text" class="calc-field-input" value="ЖК «Симфония», Корпус 2" oninput="window.calcEngineUpdateMeta('objectName', this.value)">
                        </div>
                        <div class="calc-form-group">
                            <label><small>Наименование системы</small></label>
                            <input type="text" class="calc-field-input" value="Система дымоудаления ДУ-1" oninput="window.calcEngineUpdateMeta('systemName', this.value)">
                        </div>
                        <div class="calc-form-group">
                            <label><small>Испытываемый участок</small></label>
                            <input type="text" class="calc-field-input" value="Шахта ШД-1 (этажи 2-42)" oninput="window.calcEngineUpdateMeta('section', this.value)">
                        </div>
                        <div class="calc-form-group">
                            <label><small>Инженер-составитель</small></label>
                            <input type="text" class="calc-field-input" value="Иванов И.И." oninput="window.calcEngineUpdateMeta('engineer', this.value)">
                        </div>
                    </div>
                </div>

                <!-- On-Screen Live Preview Container -->
                <div class="protocol-preview-scroll-wrapper">
                    <div id="protocolPrintArea"></div>
                </div>
            </div>

            <div class="calc-modal-box__foot">
                <button type="button" class="btn btn-outline" onclick="window.calcEngineCloseProtocol()">
                    Закрыть
                </button>
                <button type="button" class="btn btn-primary" onclick="window.calcEngineDirectPrint()" style="display: inline-flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Распечатать / Экспорт в PDF
                </button>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
?>
