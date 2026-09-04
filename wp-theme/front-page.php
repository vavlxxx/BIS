<?php
/*
Template Name: Главная
*/
get_header();
$hero_images = get_option('bis_hero_slider_images', array());
$has_hero_slider = !empty($hero_images);
?>
  <!-- Hero Section -->
  <section class="hero <?php echo $has_hero_slider ? 'hero--with-slider' : 'hero--parallax'; ?>" id="home">
    <?php if ($has_hero_slider) : ?>
      <div class="hero-slider">
        <?php foreach ($hero_images as $index => $image) : ?>
          <div class="hero-slide <?php echo $index === 0 ? 'active' : ''; ?>">
            <img src="<?php echo esc_url($image); ?>" alt="" decoding="async" fetchpriority="<?php echo $index === 0 ? 'high' : 'low'; ?>">
          </div>
        <?php endforeach; ?>
      </div>
    <?php else : ?>
      <div class="hero-parallax" aria-hidden="true">
        <div class="parallax-layer parallax-layer--back" data-speed="0.12">
          <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/img/layers/layer2.webp" alt="" decoding="async">
        </div>
        <div class="parallax-layer parallax-layer--front" data-speed="0.2">
          <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/img/layers/layer1.webp" alt="" decoding="async">
        </div>
      </div>
    <?php endif; ?>
    <div class="grid-pattern"></div>
    <div class="hero-content">
      <h1 class="typing-title">
        <span class="typing-text">БИС — </span><span class="cursor">|</span>
      </h1>
      <p class="hero-subtitle">
        Компания «БИС — Баланс Инженерных Систем» специализируется на комплексных пусконаладочных работах инженерных систем, техническом обслуживании и сопровождении
      </p>
      <div class="hero-cta">
        <button class="btn btn-primary open-estimate-modal">Рассчитать смету и сроки</button>
      </div>
      </div>
    </div>
    <div class="hero-nav-rail">
      <ul class="hero-nav">
        <li><a href="#services">Специализация</a></li>
        <li><a href="#equipment">Оборудование</a></li>
        <li><a href="#experience">Опыт</a></li>
        <li><a href="<?php echo esc_url(home_url('/about/')); ?>">О нас</a></li>
        <li><a href="<?php echo esc_url(home_url('/services/')); ?>">Услуги</a></li>
        <li><a href="<?php echo esc_url(home_url('/projects/')); ?>">Наши проекты</a></li>
        <li><a href="<?php echo esc_url(home_url('/media/')); ?>">Медиа</a></li>
        <li><a href="<?php echo esc_url(home_url('/vacancies/')); ?>">Вакансии</a></li>
        <?php if (is_user_logged_in()) : ?>
          <li><a href="<?php echo esc_url(home_url('/calculators/')); ?>">Калькуляторы</a></li>
        <?php endif; ?>
        <li><a href="#contact">Контакты</a></li>
        <li><a href="#faq">F.A.Q</a></li>
      </ul>
    </div>
  </section>
<!-- Tasks / Tech Customer Team Section -->
<section class="tasks-section tech-supervision-section" id="tasks">
    <div class="tasks-content mw-1400px">
        <div class="section-header">
            <span class="section-badge">Инженерный надзор и экспертиза</span>
            <h2 class="section-title bis-condensed">Инженерная команда технического заказчика</h2>
            <p class="section-subtitle">Обычный технадзор контролирует только процесс и бумаги. «БИС» контролирует конечный инженерный результат — от проверки исходных решений до комплексных испытаний и передачи в эксплуатацию.</p>
        </div>

        <div class="tech-supervision__grid">
            <div class="tech-supervision__card">
                <div class="tech-supervision__step">01</div>
                <div class="tech-supervision__icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><circle cx="12" cy="12" r="10"/></svg>
                </div>
                <h3 class="tech-supervision__card-title">Обследование объекта</h3>
                <p class="tech-supervision__card-text">Обследуем объект и существующие системы, выявляем дефекты и риски до ввода в эксплуатацию, оцениваем фактическую готовность сетей.</p>
            </div>

            <div class="tech-supervision__card">
                <div class="tech-supervision__step">02</div>
                <div class="tech-supervision__icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <h3 class="tech-supervision__card-title">Проверка проектов и расчетов</h3>
                <p class="tech-supervision__card-text">Проверяем проектные решения, аэродинамические и гидравлические расчеты, оцениваем обоснованность решений проектировщиков и подрядчиков.</p>
            </div>

            <div class="tech-supervision__card">
                <div class="tech-supervision__step">03</div>
                <div class="tech-supervision__icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                </div>
                <h3 class="tech-supervision__card-title">Контроль монтажа и замечаний</h3>
                <p class="tech-supervision__card-text">Контролируем соответствие монтажа рабочей документации, СП и ГОСТ на строительной площадке, формируем замечания и контролируем их устранение.</p>
            </div>

            <div class="tech-supervision__card">
                <div class="tech-supervision__step">04</div>
                <div class="tech-supervision__icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <h3 class="tech-supervision__card-title">Испытания и пусконаладка</h3>
                <p class="tech-supervision__card-text">Проводим индивидуальные и комплексные испытания оборудования, выполняем ПНР и выводим системы на проектные параметры силами лаборатории.</p>
            </div>

            <div class="tech-supervision__card">
                <div class="tech-supervision__step">05</div>
                <div class="tech-supervision__icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <h3 class="tech-supervision__card-title">Сдача техзаказчику и эксплуатацию</h3>
                <p class="tech-supervision__card-text">Проверяем исполнительную документацию, сопровождаем процедуру сдачи техническому заказчику и передачу систем эксплуатирующей организации.</p>
            </div>
        </div>

        <div class="tech-supervision__banner">
            <div class="tech-supervision__banner-text">
                <span class="tech-supervision__banner-tag">Ключевое отличие</span>
                <h4 class="tech-supervision__banner-title">Мы проверяем не только документы, но и фактическую работу систем</h4>
                <p class="tech-supervision__banner-desc">Наша команда сама занимается обследованием, испытаниями и наладкой. Мы видим взаимосвязь проекта, автоматики и оборудования, поэтому выявляем причину отклонений, а не просто фиксируем ошибку.</p>
            </div>
            <div class="tech-supervision__banner-action">
                <a href="#contact" class="btn btn-primary tech-supervision__btn">
                    Подробнее об услугах <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </div>
</section>


<!-- Objects Section -->
<section class="objects-section" id="objects">
  <div class="objects-container">
    <div class="section-header">
      <span class="section-badge">Типы объектов</span>
      <h2 class="section-title">Объекты, на которых мы работаем</h2>
      <p class="section-subtitle">Работаем с инженерными системами на разных типах площадок — от производственных комплексов до жилых объектов.</p>
    </div>

    <div class="objects-slider-wrapper">
      <div class="objects-grid">
        <div class="object-card">
          <h3>Промышленные</h3>
          <p>Производственные здания, склады и чистые помещения.</p>
        </div>
        <div class="object-card">
          <h3>Административные</h3>
          <p>Офисы, общественные пространства и коммерческие здания.</p>
        </div>
        <div class="object-card">
          <h3>Жилые</h3>
          <p>Многоквартирные дома, апартаменты и частные резиденции.</p>
        </div>
      </div>

      <div class="objects-slider-nav">
        <button class="slider-prev" aria-label="Предыдущий объект">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <div class="slider-dots objects-slider-dots"></div>
        <button class="slider-next" aria-label="Следующий объект">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</section>



  <section class="services" id="services">
  <div class="section-header">
    <h2 class="section-title">Комплексные решения для ваших систем</h2>
  </div>

  <div class="services-slider-shell">
    <?php
    $services = bis_get_catalog_services();
    $services_grid_classes = 'services-grid' . (bis_services_have_associated_services($services) ? ' services-grid--has-submenus' : '');
    ?>
    <div class="<?php echo esc_attr($services_grid_classes); ?>">

      <?php if (!empty($services)) : ?>
        <?php foreach ($services as $post) : setup_postdata($post); ?>
          <?php bis_render_service_card(get_the_ID()); ?>
        <?php endforeach; ?>
        <?php wp_reset_postdata(); ?>
      <?php else : ?>
        <div class="team-empty">
          <span class="team-empty__label">Услуги</span>
          <p>Мы готовим презентацию услуг.</p>
        </div>
      <?php endif; ?>
    </div>


    <div class="services-slider-nav">
      <button class="slider-prev" aria-label="Предыдущая услуга">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      <div class="slider-dots"></div>
      <button class="slider-next" aria-label="Следующая услуга">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>
  </div>

  <div class="experience-cta">
    <a class="btn btn-outline" href="<?php echo esc_url(home_url('/services/')); ?>">Смотреть все услуги</a>
  </div>
</section>

<section class="pnr-why-section">
  <div class="grid-pattern"></div>
  <div class="pnr-why-content">
    <h3>Почему «БИС — Баланс Инженерных Систем»?</h3>
    <ul class="pnr-why">
      <li>Комплексный подход</li>
      <li>Честность и ответственность</li>
      <li>Мобильность и нацеленность на качественный результат</li>
      <li>Внимание и быстрое реагирование на требования заказчика</li>
      <li>Постоянная квалифицированная команда инженеров</li>
      <li>Возможность работы с НДС и без</li>
      <li>Наличие всех необходимых лицензий и разрешений</li>
    </ul>
    <div class="pnr-stats">
      <h4 class="pnr-stats-title">Наш опыт в цифрах</h4>
      <div class="stats">
        <div class="stat-item">
          <span class="stat-value">10</span>
          <span class="stat-label">лет на рынке</span>
        </div>
        <div class="stat-item">
          <span class="stat-value">200</span>
          <span class="stat-label">реализованных проектов</span>
        </div>
        <div class="stat-item">
          <span class="stat-value">15 тыс</span>
          <span class="stat-label">общеобменных систем обследовано и налажено</span>
        </div>
        <div class="stat-item">
          <span class="stat-value">6 тыс</span>
          <span class="stat-label">противодымных систем обследовано и налажено</span>
        </div>
        <div class="stat-item">
          <span class="stat-value">200 тыс</span>
          <span class="stat-label">регулирующих устройств и балансиров</span>
        </div>
        <div class="stat-item">
          <span class="stat-value">25 тыс</span>
          <span class="stat-label">индивидуальных испытаний</span>
        </div>
        <div class="stat-item">
          <span class="stat-value">100</span>
          <span class="stat-label">ИТП и хладоцентров</span>
        </div>
        <div class="stat-item">
          <span class="stat-value">100 тыс</span>
          <span class="stat-label">м.п. воздуховодов проверено на герметичность</span>
        </div>
        <div class="stat-item">
          <span class="stat-value">80%</span>
          <span class="stat-label">клиентов возвращаются повторно</span>
        </div>
      </div>
      <p class="pnr-stats-note">Комплексный подход: мы команда высококлассных инженеров с уникальным опытом наладки инженерных систем.</p>
    </div>
    <div style="margin-top: 40px;width: 100%; text-align: center;display: flex; justify-content: center;">
      <a href="#contact" class="btn btn-primary" data-service="Общая заявка">Отправить заявку</a>
    </div>
  </div>
</section>

<?php $revenue = bis_get_revenue_settings(); ?>
<section class="revenue-section" id="revenue">
  <div class="revenue-container">
    <div class="revenue-header">
      <h2 class="revenue-title">Динамика выручки за 10 лет</h2>
      <span class="revenue-unit">млрд ₽</span>
    </div>

    <div class="revenue-chart" data-currency="₽" data-revenue-points="<?php echo esc_attr(wp_json_encode($revenue['points'])); ?>">
      <div class="revenue-axis" data-revenue-axis></div>
      <div class="revenue-plot">
        <svg class="revenue-svg" viewBox="0 0 100 60" preserveAspectRatio="none" aria-hidden="true">
          <defs>
            <linearGradient id="revenueGradient" x1="0%" y1="0%" x2="0%" y2="100%">
              <stop offset="0%" stop-color="rgba(17, 24, 39, 0.18)"/>
              <stop offset="100%" stop-color="rgba(17, 24, 39, 0)"/>
            </linearGradient>
          </defs>
          <path class="revenue-area" fill="url(#revenueGradient)" d=""></path>
          <path class="revenue-line" d=""></path>
          <g class="revenue-points"></g>
        </svg>
        <div class="revenue-grid" data-revenue-grid></div>
        <div class="revenue-labels" data-revenue-labels></div>
      </div>
      <div class="revenue-xaxis" data-revenue-xaxis></div>
    </div>

    <div class="revenue-cta">
        <a class="btn btn-outline btn-outline--bold" href="#contact">
        Узнать больше
        </a>
    </div>
  </div>
</section>

<section class="equipment-section" id="equipment">
  <div class="section-header">
    <!-- <span class="section-badge">Оборудование</span> -->
    <h2 class="section-title">Оборудование «БИС — Баланс Инженерных Систем»</h2>
    <p class="section-subtitle">Собственные решения для очистки вентиляции и полный парк измерительных приборов для ПНР, сервиса и метрологического контроля</p>
  </div>

  <div class="equipment-park">
    <div class="equipment-grid">
      <?php
      $equipment_items = new WP_Query(array(
        'post_type'      => 'bis_equipment',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => array('menu_order' => 'ASC', 'title' => 'ASC'),
      ));
      ?>

      <?php if ($equipment_items->have_posts()) : ?>
        <?php while ($equipment_items->have_posts()) : $equipment_items->the_post(); ?>
          <?php
          $equipment_id = get_the_ID();
          $image_url = bis_get_equipment_image_url($equipment_id);
          $description = get_post_meta($equipment_id, 'bis_equipment_description', true);
          ?>
          <div class="equipment-card">
            <div class="equipment-image">
              <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async">
            </div>
            <div class="equipment-content">
              <h3><?php the_title(); ?></h3>
              <?php if (!empty($description)) : ?>
                <p class="experience-description"><?php echo esc_html($description); ?></p>
              <?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>
        <?php wp_reset_postdata(); ?>
      <?php else : ?>
        <div class="team-empty">
          <span class="team-empty__label">Оборудование</span>
          <p>Мы готовим презентацию оборудования.</p>
        </div>
      <?php endif; ?>
    </div>
    <div class="equipment-slider-nav">
      <button class="slider-prev" aria-label="Предыдущий прибор">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      <div class="slider-dots"></div>
      <button class="slider-next" aria-label="Следующий прибор">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>
  </div>
</section>



<?php
$gratitude_letters = new WP_Query(array(
  'post_type'      => 'bis_gratitude',
  'posts_per_page' => -1,
  'orderby'        => array('menu_order' => 'ASC', 'date' => 'DESC'),
));
if ($gratitude_letters->have_posts()) :
?>
<section class="gratitude-section" id="gratitude" style="display: none;">
  <div class="section-header">
    <!-- <span class="section-badge">Отзывы</span> -->
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
  <div class="gratitude-dots slider-dots" data-gratitude-dots></div>
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



  <!-- Experience Section -->
<section class="experience" id="experience">
  <div class="section-header">
    <h2 class="section-title">Наши ключевые проекты</h2>
    <p class="section-subtitle">Реализованные решения для ведущих компаний</p>
  </div>

  <?php
  $featured_projects = new WP_Query(array(
    'post_type'      => 'bis_project',
    'post_status'    => 'publish',
    'posts_per_page' => 6,
    'meta_key'       => 'bis_project_is_featured',
    'meta_value'     => '1',
  ));
  ?>

  <?php if ($featured_projects->have_posts()) : ?>
  <div class="experience-slider-shell">
    <div class="experience-grid">
      <?php while ($featured_projects->have_posts()) : $featured_projects->the_post(); ?>
        <?php
        $project_id = get_the_ID();
        $image_url = bis_get_project_image_url($project_id);
        $description = bis_get_project_description($project_id);
        $project_type_terms = get_the_terms($project_id, 'bis_project_type');
        $project_type_names = array();
        if (is_array($project_type_terms) && !is_wp_error($project_type_terms)) {
            $project_type_terms = bis_sort_project_type_terms($project_type_terms);
            foreach ($project_type_terms as $project_type_term) {
                $project_type_names[] = $project_type_term->name;
            }
        }
        ?>
        <div class="experience-card"
             data-image="<?php echo esc_url($image_url); ?>"
             data-link="<?php echo esc_url(get_permalink($project_id)); ?>"
             data-featured="1">
          <div class="experience-image">
            <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async">
            <span class="experience-badge">Ключевой проект</span>
            <?php if (!empty($project_type_names)) : ?>
              <span class="experience-project-type-badge"><?php echo esc_html(implode(', ', $project_type_names)); ?></span>
            <?php endif; ?>
          </div>
          <div class="experience-content">
            <h3><?php the_title(); ?></h3>
            <?php if (!empty($description)) : ?>
              <p class="experience-description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
            <a class="experience-more" href="<?php echo esc_url(get_permalink($project_id)); ?>">Подробнее<span aria-hidden="true">→</span></a>
          </div>
        </div>
      <?php endwhile; ?>
      <?php wp_reset_postdata(); ?>
    </div>
    <div class="experience-slider-nav">
      <button class="slider-prev" aria-label="Предыдущий ключевой проект">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      <div class="slider-dots"></div>
      <button class="slider-next" aria-label="Следующий ключевой проект">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>
  </div>
  <?php endif; ?>

  <div class="experience-cta">
    <a class="btn btn-outline" href="<?php echo esc_url(home_url('/projects/')); ?>">Смотреть все проекты</a>
  </div>
</section>

<div class="experience-modal-overlay" id="experienceModal">
  <div class="experience-modal">
    <button class="modal-close" id="experienceModalClose" aria-label="Закрыть описание проекта">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
    <div class="experience-modal-image"></div>
    <div class="experience-modal-content">
      <h2 class="experience-modal-title"></h2>
      <div class="experience-modal-meta"></div>
      <div class="experience-modal-actions">
        <a href="#contact" class="btn btn-primary experience-modal-cta">Обсудить проект</a>
        <a href="#" class="btn btn-outline experience-modal-link">Страница проекта</a>
      </div>
    </div>
  </div>
</div>

<div class="cases-modal-overlay" id="casesModal">
  <div class="cases-modal">
    <button class="modal-close" id="modalClose">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>

    <div class="modal-header">
      <h2>Все наши проекты</h2>
    </div>

    <div class="all-cases-grid">
      <?php
      $all_projects = new WP_Query(array(
        'post_type'      => 'bis_project',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
      ));
      ?>

      <?php if ($all_projects->have_posts()) : ?>
        <?php while ($all_projects->have_posts()) : $all_projects->the_post(); ?>
          <?php
          $project_id = get_the_ID();
          $image_url = bis_get_project_image_url($project_id);
          $description = bis_get_project_description($project_id);
          $is_featured = get_post_meta($project_id, 'bis_project_is_featured', true) === '1';
          $project_type_terms = get_the_terms($project_id, 'bis_project_type');
          $project_type_names = array();
          if (is_array($project_type_terms) && !is_wp_error($project_type_terms)) {
              $project_type_terms = bis_sort_project_type_terms($project_type_terms);
              foreach ($project_type_terms as $project_type_term) {
                  $project_type_names[] = $project_type_term->name;
              }
          }
          ?>
          <div class="all-case-card"
               data-image="<?php echo esc_url($image_url); ?>"
               data-link="<?php echo esc_url(get_the_permalink($project_id)); ?>"
               data-featured="<?php echo $is_featured ? '1' : '0'; ?>">
            <div class="all-case-image">
              <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async">
              <?php if ($is_featured) : ?>
                <span class="experience-badge">Ключевой проект</span>
              <?php endif; ?>
              <?php if (!empty($project_type_names)) : ?>
                <span class="experience-project-type-badge"><?php echo esc_html(implode(', ', $project_type_names)); ?></span>
              <?php endif; ?>
            </div>
            <div class="experience-content">
              <h4><?php the_title(); ?></h4>
              <?php if (!empty($description)) : ?>
                <p class="experience-description"><?php echo esc_html($description); ?></p>
              <?php endif; ?>
              <a class="case-more" href="<?php echo esc_url(get_permalink($project_id)); ?>">Подробнее<span aria-hidden="true">→</span></a>
            </div>
          </div>
        <?php endwhile; ?>
        <?php wp_reset_postdata(); ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Callback Modal -->
<div class="popup-overlay" id="callbackOverlay">
  <div class="popup-form">
    <button class="popup-close" id="callbackClose">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>

    <h2>Обратный звонок</h2>
    <p>Оставьте свои контакты и мы перезвоним вам в течение 15 минут</p>

    <form class="contact-form" id="callbackForm">
      <div class="form-group">
        <label for="callbackName">Имя</label>
        <input type="text" id="callbackName" name="name" required placeholder="Ваше имя" autocomplete="name">
      </div>

      <div class="form-group">
        <label for="callbackPhone">Телефон</label>
        <input type="tel" id="callbackPhone" name="phone" required placeholder="+7 (___) ___-__-__" autocomplete="tel">
      </div>

      <div class="form-group">
        <label for="callbackMessage">Сообщение (необязательно)</label>
        <textarea id="callbackMessage" name="message" placeholder="Кратко опишите ваш вопрос"></textarea>
      </div>

      <button type="submit" class="btn btn-primary">Позвоните мне</button>
    </form>
  </div>
</div>

<!-- Objects Map Section -->
<?php
$map_projects = new WP_Query(array(
    'post_type'      => 'bis_project',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'meta_query'     => array(
        'relation' => 'AND',
        array(
            'key'     => 'bis_project_map_coords',
            'compare' => 'EXISTS',
        ),
        array(
            'key'     => 'bis_project_map_coords',
            'value'   => '',
            'compare' => '!=',
        ),
    ),
));

$projects_data = array();
if ($map_projects->have_posts()) {
    while ($map_projects->have_posts()) {
        $map_projects->the_post();
        $p_id = get_the_ID();
        $coords_raw = get_post_meta($p_id, 'bis_project_map_coords', true);
        $coords_clean = trim((string) $coords_raw);
        if (empty($coords_clean)) {
            continue;
        }
        $coords_arr = array_map('trim', explode(',', $coords_clean));
        if (count($coords_arr) === 2 && is_numeric($coords_arr[0]) && is_numeric($coords_arr[1])) {
            $img = bis_get_project_preview_image_url($p_id);
            if (empty($img)) {
                $img = get_template_directory_uri() . '/assets/img/bis-logo-white.png';
            }
            $projects_data[] = array(
                'id'          => $p_id,
                'title'       => get_the_title(),
                'link'        => get_permalink(),
                'coords'      => array(floatval($coords_arr[0]), floatval($coords_arr[1])),
                'address'     => get_post_meta($p_id, 'bis_project_address', true),
                'description' => wp_trim_words(bis_get_project_description($p_id), 12, '...'),
                'image'       => $img,
            );
        }
    }
    wp_reset_postdata();
}
?>

<div class="section-header">
    <h2 class="section-title">Карта объектов</h2>
    <!-- <p class="section-subtitle">Ведущие специалисты в области инженерных систем</p> -->
</div>
<section class="objects-map-section" id="objects-map">
    <div id="objects-yandex-map" class="objects-yandex-map" data-objects-map data-projects="<?php echo esc_attr(json_encode($projects_data, JSON_UNESCAPED_UNICODE)); ?>">
        <div class="yandex-map__static" id="objects-yandex-map-placeholder">
            <div class="yandex-map__hint">Интерактивная карта объектов загружается при просмотре...</div>
        </div>
        <div id="objects-yandex-map-live" class="objects-map__interactive"></div>
    </div>
</section>

<?php $team_members = bis_get_team_members(); ?>
<div class="section-header">
    <!-- <span class="section-badge">Оборудование</span> -->
    <h2 class="section-title">Наша команда</h2>
    <p class="section-subtitle">Ведущие специалисты в области инженерных систем</p>
  </div>
<section class="structure-section team-section" id="structure"
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
      <img src="" alt="" data-team-modal-image>
    </div>
    <div class="team-modal__body">
      <h3 class="team-modal__name" id="teamModalTitle" data-team-modal-name></h3>
      <p class="team-modal__role" data-team-modal-role></p>
      <p class="team-modal__since" data-team-modal-since></p>
      <div class="team-modal__text" data-team-modal-text></div>
    </div>
  </div>
</div>

<!-- Why Us Section -->
<section class="why-us" id="why">
  <div class="section-header">
    <span class="section-badge">Почему выбирают нас</span>
    <p class="section-subtitle">«БИС — Баланс Инженерных Систем» — это команда молодых и трудолюбивых специалистов. Для нас нет неразрешимых задач, поэтому если в Вашей деятельности возник вопрос по пусконаладке или замерам, то мы обязательно постараемся помочь.</p>
  </div>


  <div class="why-grid">
    <div class="why-card">
      <div class="why-number">01</div>
      <h3>Экспертиза</h3>
      <p>Команда сертифицированных специалистов в сфере инженерных систем.</p>
    </div>
    <div class="why-card">
      <div class="why-number">02</div>
      <h3>Надежность</h3>
      <p>Используем только проверенное оборудование и технологии. Гарантия на все виды работ.</p>
    </div>
    <div class="why-card">
      <div class="why-number">03</div>
      <h3>Индивидуальный подход</h3>
      <p>Разрабатываем решения под конкретные задачи и особенности вашего объекта.</p>
    </div>
    <div class="why-card">
      <div class="why-number">04</div>
      <h3>Поддержка 24/7</h3>
      <p>Круглосуточная техническая поддержка и оперативное реагирование на любые запросы.</p>
    </div>
  </div>
</section>

<?php
$news_query = new WP_Query(array(
  'post_type'      => 'bis_news',
  'posts_per_page' => 3,
  'post_status'    => 'publish',
));
$categories = get_terms(array(
    'taxonomy'   => 'bis_news_category',
    'hide_empty' => true,
));
?>

<section class="homepage-news" id="news">
  <div class="homepage-news__container">
    <div class="homepage-news__header">
      <h2 class="section-title">Медиа компании</h2>
      <p class="section-subtitle">Рассказываем о ключевых событиях, проектах и экспертизе нашей команды.</p>
    </div>

    <nav class="news-filter__categories" aria-label="Рубрики медиа" style="margin-bottom: 30px; justify-content: center;">
      <button type="button" class="news-filter__category news-tab is-active" data-news-target="all">Все</button>
      <?php foreach ($categories as $cat) : ?>
        <button type="button" class="news-filter__category news-tab" data-news-target="<?php echo esc_attr($cat->slug); ?>"><?php echo esc_html($cat->name); ?></button>
      <?php endforeach; ?>
    </nav>

    <div class="news-tab-content active" data-news-content="all">
      <?php if ($news_query->have_posts()) : ?>
        <div class="news-grid news-grid--home">
          <?php while ($news_query->have_posts()) : $news_query->the_post(); ?>
            <?php
            $news_id = get_the_ID();
            $image_url = bis_get_news_image_url($news_id);
            ?>
            <article class="news-item">
              <a class="news-item__image" href="<?php the_permalink(); ?>">
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
              </a>
              <div class="news-item__body">
                <time class="news-item__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('d.m.Y')); ?></time>
                <h3 class="news-item__title">
                  <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h3>
                <p class="news-item__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18)); ?></p>
              </div>
            </article>
          <?php endwhile; ?>
        </div>
        <div class="homepage-news__cta">
          <a class="btn btn-outline btn-outline--bold" href="<?php echo esc_url(get_post_type_archive_link('bis_news')); ?>">Все медиа</a>
        </div>
        <?php wp_reset_postdata(); ?>
      <?php else : ?>
        <div class="team-empty">
          <span class="team-empty__label">Медиа</span>
          <p>Мы готовим подборку материалов компании.</p>
        </div>
      <?php endif; ?>
    </div>

    <?php foreach ($categories as $cat) : ?>
      <?php
      $cat_query = new WP_Query(array(
        'post_type'      => 'bis_news',
        'posts_per_page' => 3,
        'post_status'    => 'publish',
        'tax_query'      => array(
            array(
                'taxonomy' => 'bis_news_category',
                'field'    => 'slug',
                'terms'    => $cat->slug,
            ),
        ),
      ));
      ?>
      <div class="news-tab-content" data-news-content="<?php echo esc_attr($cat->slug); ?>" style="display: none;">
        <?php if ($cat_query->have_posts()) : ?>
          <div class="news-grid news-grid--home">
            <?php while ($cat_query->have_posts()) : $cat_query->the_post(); ?>
              <?php
              $news_id = get_the_ID();
              $image_url = bis_get_news_image_url($news_id);
              ?>
              <article class="news-item">
                <a class="news-item__image" href="<?php the_permalink(); ?>">
                  <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                </a>
                <div class="news-item__body">
                  <time class="news-item__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('d.m.Y')); ?></time>
                  <h3 class="news-item__title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                  </h3>
                  <p class="news-item__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18)); ?></p>
                </div>
              </article>
            <?php endwhile; wp_reset_postdata(); ?>
          </div>
          <div class="homepage-news__cta">
            <a class="btn btn-outline btn-outline--bold" href="<?php echo esc_url(bis_get_news_filter_url(array('category' => $cat->slug))); ?>">Все статьи рубрики</a>
          </div>
        <?php else : ?>
          <div class="team-empty">
            <p>Нет записей.</p>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

  </div>
</section>

  <!-- Contact Section -->
  <section class="contact" id="contact">
    <div class="contact-wrapper">
      <div class="contact-info">
        <h2>Свяжитесь с нами</h2>
        <p>Для нас нет неразрешимых задач, мы не боимся трудностей, решение неординарных технических задач - наша работа поэтому если в Вашей деятельности возникли технические задачи мы обязательно постараемся помочь!</p>
        <div class="contact-details">
          <div class="contact-item">
            <div class="contact-icon">📞</div>
            <div class="contact-item-content">
              <h4>Телефон</h4>
              <a class="footer-phone"  href="tel:+79264380770">+7 (926) 438-07-70</a><br>
              <a class="footer-phone" href="tel:+79169861187">+7 (916) 986-11-87</a>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-icon">✉️</div>
            <div class="contact-item-content">
              <h4>Email</h4>
              <a href="mailto:office@bis-rf.ru">office@bis-rf.ru</a>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-icon">📍</div>
            <div class="contact-item-content">
              <h4>Адрес</h4>
              <p>г. Москва, проезд Таможенный д.6, стр.9</p>
            </div>
          </div>
        </div>
      </div>
      <div class="contact-form-wrapper">
        <form class="contact-form" id="contactForm">
          <div class="form-group">
            <label for="name">Имя</label>
            <input type="text" id="name" name="name" required placeholder="Ваше имя" autocomplete="name">
          </div>
          <div class="form-group">
            <label for="phone">Телефон</label>
            <input type="tel" id="phone" name="phone" required placeholder="+7 (___) ___-__-__" autocomplete="tel">
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required placeholder="example@mail.ru" autocomplete="email">
          </div>
          <div class="form-group">
            <label for="message">Сообщение</label>
            <textarea id="message" name="message" required placeholder="Кратко опишите ваш вопрос" autocomplete="off"></textarea>
          </div>
          <?php echo do_shortcode('[hcaptcha auto="true" force="true"]'); ?>
          <button type="submit" class="btn btn-primary" style="
    width: 100%;
    display: flex;
    min-width: 100%;
">Отправить заявку</button>
        </form>
      </div>
    </div>
    </section>
    <section class="map-section">
    <!-- Яндекс.Карта -->
    <div class="map-container">
      <div
        id="yandex-map"
        class="yandex-map"
        data-yandex-map
        data-map-script-src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3A7972908992b0111eaafc38990b26b0c1dbbd437ee1e3b769e14322fe175cdfff&amp;id=yandex-map-live&amp;lang=ru_RU&amp;scroll=true"
      >
        <div class="yandex-map__static">
          <img
            src="https://static-maps.yandex.ru/1.x/?l=map&lang=ru_RU&ll=37.671575165095106%2C55.75377607564223&origin=jsapi-constructor&pt=37.679484%2C55.751665%2Cpm2bll&size=650%2C400&z=13"
            alt="Карта расположения офиса"
            loading="lazy"
            decoding="async"
            width="1400"
            height="400"
          >
          <div class="yandex-map__hint">Интерактивная карта загрузится при просмотре блока</div>
        </div>
        <div
          id="yandex-map-live"
          class="yandex-map__interactive"
          aria-label="Интерактивная карта"
        ></div>
        <noscript>
          <img
            src="https://api-maps.yandex.ru/services/constructor/1.0/static/?um=constructor%3A7972908992b0111eaafc38990b26b0c1dbbd437ee1e3b769e14322fe175cdfff&amp;width=1400&amp;height=400&amp;lang=ru_RU"
            alt="Карта расположения офиса"
            width="1400"
            height="400"
          >
        </noscript>
      </div>
    </div>
  </section>

<!-- FAQ Section -->
<section class="faq-section" id="faq">
  <div class="section-header">
    <span class="section-badge">FAQ</span>
    <h2 class="section-title">Часто задаваемые вопросы</h2>
    <p class="section-subtitle">Ответы на самые популярные вопросы о пусконаладочных работах</p>
  </div>

  <div class="faq-container">
    <div class="faq-item">
      <div class="faq-question">
        <h3>Как расшифровывается аббревиатура ПНР?</h3>
        <span class="faq-toggle">+</span>
      </div>
      <div class="faq-answer">
        <p><strong>Пусконаладочные работы</strong></p>
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">
        <h3>Пусконаладочные работы - что это?</h3>
        <span class="faq-toggle">+</span>
      </div>
      <div class="faq-answer">
        <p>Это комплекс мероприятий по регулировке инженерных систем с целью фактического достижения проектных показателей</p>
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">
        <h3>Зачем нужны пусконаладочные работы?</h3>
        <span class="faq-toggle">+</span>
      </div>
      <div class="faq-answer">
        <p>Для поддержания комфортного пребывания человека в помещениях с искусственным микроклиматом</p>
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">
        <h3>Сколько стоят пусконаладочные работы?</h3>
        <span class="faq-toggle">+</span>
      </div>
      <div class="faq-answer">
        <p><strong>7 – 10% от стоимости СМР</strong></p>
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">
        <h3>Что включено в стоимость пусконаладочных работ?</h3>
        <span class="faq-toggle">+</span>
      </div>
      <div class="faq-answer">
        <ul>
          <li>Составление и согласование программы наладки</li>
          <li>Проверка фактического исполнения систем проектной документации</li>
          <li>Оформление ведомости соответствия с фотоотчетом</li>
          <li>Проведение индивидуальных испытаний оборудования</li>
          <li>Выполнение комплекса наладочных работ</li>
          <li>Разработка и оформление паспортов и протоколов</li>
        </ul>
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">
        <h3>Зачем делать испытания воздуховодов на герметичность?</h3>
        <span class="faq-toggle">+</span>
      </div>
      <div class="faq-answer">
        <p>Мы рекомендуем обязательно делать испытания воздуховодов на герметичность до возведения строительных конструкций. Это существенно сэкономит ваши нервы, деньги и время при сдаче объекта и дальнейшей эксплуатации, проверено опытом.</p>
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">
        <h3>Какую информацию необходимо предоставить для расчета стоимости?</h3>
        <span class="faq-toggle">+</span>
      </div>
      <div class="faq-answer">
        <p><strong>При наличии необходимо предоставить:</strong></p>
        <ul>
          <li>Аксонометрические схемы</li>
          <li>Поэтажные планы с разводкой систем</li>
          <li>Высоту помещений</li>
          <li>Разрешенное время проведения работ (день или ночь)</li>
          <li>Срок производства работ (начало-окончание)</li>
        </ul>
        <p>По вашему желанию данную информацию мы можем собрать самостоятельно при осмотре объекта.</p>
        <p>Тогда от вас нам будет достаточно получить только адрес объекта и контакт ответственного инженера.</p>
      </div>
    </div>
    <div class="faq-item">
      <div class="faq-question">
        <h3>В соответствии с какими нормативными документами выполняются работы?</h3>
        <span class="faq-toggle">+</span>
      </div>
      <div class="faq-answer">
        <ul class="pnr-standards">
          <li>ГОСТ 34060-2017 (испытание и наладка систем вентиляции и кондиционирования воздуха)</li>
          <li>СП 60.13330.2016 «Отопление, вентиляция и кондиционирование»</li>
          <li>СП 73.13330.2016 «Внутренние санитарно-технические системы»</li>
          <li>СП 7.13130.2016 «Требования пожарной безопасности»</li>
          <li>ГОСТ Р 53300-2009 «Противодымная защита зданий»</li>
          <li>ГОСТ 12.3.018-79 ССБТ</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="faq-cta">
    <p>Остались вопросы?</p>
    <a href="#contact" class="btn btn-primary">Получить консультацию</a>
  </div>
</section>

<!-- Popular Services Section -->
<section class="popular-services-section" id="popular-services">
  <div class="popular-services__container mw-1400px">
    <div class="section-header">
      <h2 class="popular-services__title bis-condensed">Популярные услуги</h2>
      <p class="section-subtitle"></p>
    </div>

    <div class="popular-services__grid">
      <?php
      $service_categories = get_terms(array(
          'taxonomy'   => 'bis_service_category',
          'hide_empty' => false,
      ));

      $has_custom_categories = !empty($service_categories) && !is_wp_error($service_categories);
      $rendered_columns = 0;

      if ($has_custom_categories) {
          foreach ($service_categories as $cat) {
              $cat_services = get_posts(array(
                  'post_type'      => 'bis_service',
                  'posts_per_page' => 12,
                  'post_status'    => 'publish',
                  'tax_query'      => array(
                      array(
                          'taxonomy' => 'bis_service_category',
                          'field'    => 'term_id',
                          'terms'    => $cat->term_id,
                      ),
                  ),
              ));

              if (empty($cat_services)) {
                  continue;
              }

              $rendered_columns++;
              ?>
              <div class="popular-services__col">
                <div class="popular-services__col-head">
                  <h3 class="popular-services__col-title"><?php echo esc_html($cat->name); ?></h3>
                </div>
                <ul class="popular-services__list">
                  <?php foreach ($cat_services as $srv) : ?>
                    <li>
                      <a href="<?php echo esc_url(get_permalink($srv->ID)); ?>" class="popular-services__link">
                        <?php echo esc_html(get_the_title($srv->ID)); ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <?php
          }
      }

      // Fallback: если рубрики не найдены, разбиваем все опубликованные услуги на 4 сбалансированные колонки
      if ($rendered_columns === 0) {
          $all_services = get_posts(array(
              'post_type'      => 'bis_service',
              'posts_per_page' => -1,
              'post_status'    => 'publish',
              'orderby'        => 'menu_order title',
              'order'          => 'ASC',
          ));

          if (!empty($all_services)) {
              $fallback_categories = array(
                  'Вентиляция и кондиционирование' => array(),
                  'Противодымная вентиляция'      => array(),
                  'Чистка и дезинфекция систем'   => array(),
                  'Автоматика и спецналадка'       => array(),
              );

              $col_keys = array_keys($fallback_categories);
              foreach ($all_services as $idx => $srv) {
                  $cat_key = $col_keys[$idx % count($col_keys)];
                  $fallback_categories[$cat_key][] = $srv;
              }

              foreach ($fallback_categories as $col_title => $services_list) {
                  if (empty($services_list)) continue;
                  ?>
                  <div class="popular-services__col">
                    <div class="popular-services__col-head">
                      <h3 class="popular-services__col-title"><?php echo esc_html($col_title); ?></h3>
                    </div>
                    <ul class="popular-services__list">
                      <?php foreach ($services_list as $srv) : ?>
                        <li>
                          <a href="<?php echo esc_url(get_permalink($srv->ID)); ?>" class="popular-services__link">
                            <?php echo esc_html(get_the_title($srv->ID)); ?>
                          </a>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                  <?php
              }
          }
      }
      ?>
    </div>
  </div>
</section>
<?php get_footer(); ?>

