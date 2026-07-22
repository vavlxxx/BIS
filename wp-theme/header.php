<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php
$document_title = get_bloginfo('name');

if (!is_front_page()) {
    $queried_object = get_queried_object();

    if (is_home()) {
        $posts_page_id = (int) get_option('page_for_posts');
        if ($posts_page_id > 0) {
            $posts_page = get_post($posts_page_id);
            if ($posts_page instanceof WP_Post && !empty($posts_page->post_title)) {
                $document_title = $posts_page->post_title;
            }
        }
    } elseif ($queried_object instanceof WP_Post && !empty($queried_object->post_title)) {
        $document_title = $queried_object->post_title;
    } elseif (is_category() || is_tag() || is_tax()) {
        $document_title = single_term_title('', false);
    } elseif (is_post_type_archive()) {
        $document_title = post_type_archive_title('', false);
    } elseif (is_search()) {
        $document_title = 'Поиск';
    } elseif (is_404()) {
        $document_title = 'Страница не найдена';
    }
}

$meta_title = $document_title;
if (is_singular(bis_get_seo_enabled_post_types())) {
    $seo_title = bis_get_post_seo_title(get_queried_object_id());
    if ($seo_title !== '') {
        $meta_title = $seo_title;
    }
}
$document_description = bis_get_current_meta_description();
?>
  <meta name="title" content="<?php echo esc_attr($meta_title); ?>">
  <meta name="description" content="<?php echo esc_attr($document_description); ?>">
  <link rel="icon" type="image/x-icon" href="<?php echo get_template_directory_uri(); ?>/assets/img/LOGOLOGO11.ico">
  <title><?php echo esc_html($meta_title); ?></title>

  <?php wp_head(); ?>
  <script src="https://js.hcaptcha.com/1/api.js" async defer></script>

<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=108008534', 'ym');

    ym(108008534, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/108008534" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

</head>
<body <?php body_class('site-loading'); ?>>
  <?php wp_body_open(); ?>
  <div class="site-loader" id="siteLoader" role="status" aria-live="polite" aria-label="Loading page">
    <div class="site-loader__inner">
      <div class="site-loader__mark" aria-hidden="true">
        <span class="site-loader__ring"></span>
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/img/LOGOLOGO11.png" alt="" class="site-loader__logo">
      </div>
      <div class="site-loader__progress" aria-hidden="true">
        <span class="site-loader__line"></span>
        <span class="site-loader__percent" data-loader-percent>0%</span>
      </div>
    </div>
  </div>
  <noscript><style>.site-loader{display:none!important}body.site-loading{overflow:auto}</style></noscript>
  <!-- Header -->
  <header class="header" id="header">
    <div class="header-content">
      <div class="brand-block">
        <a href="<?php echo esc_url( home_url( '/#home' ) ); ?>" class="logo-link" aria-label="На главную">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/img/LOGOLOGO11.png" alt="БИС — Баланс Инженерных Систем" class="brand-mark">
        </a>
        <div class="brand-text">
          <span class="brand-title">«БИС» — Баланс</span>
          <span class="brand-subtitle">Инженерные системы</span>
        </div>
      </div>
      <div class="header-actions">
        <div class="header-location" data-location-widget>
          <button class="header-location__trigger" type="button" data-location-trigger aria-expanded="false" aria-haspopup="dialog">
            <span class="header-location__eyebrow">Ваш город</span>
            <span class="header-location__city" data-location-city>Не определено</span>
          </button>
          <div class="header-location__popover" data-location-popover hidden>
            <button class="header-location__close" type="button" data-location-close aria-label="Закрыть окно выбора города">&times;</button>
            <div class="header-location__step" data-location-step="confirm">
              <div class="header-location__caption">Ваш город</div>
              <h3 class="header-location__title">Это ваш город?</h3>
              <p class="header-location__current" data-location-current-city>Не определено</p>
              <div class="header-location__buttons">
                <button class="btn btn-primary header-location__button" type="button" data-location-confirm>Да</button>
                <button class="btn btn-outline header-location__button" type="button" data-location-other>Другой</button>
              </div>
            </div>
            <div class="header-location__step" data-location-step="custom" hidden>
              <div class="header-location__caption">Ваш город</div>
              <h3 class="header-location__title">Укажите город</h3>
              <div class="header-location__field">
                <label class="header-location__label" for="headerLocationInput">Город</label>
                <input class="header-location__input" type="text" id="headerLocationInput" placeholder="Например, Екатеринбург" data-location-input>
                <span class="header-location__error" data-location-error hidden>Введите город</span>
              </div>
              <div class="header-location__buttons">
                <button class="btn btn-primary header-location__button" type="button" data-location-save>Сохранить</button>
                <button class="btn btn-outline header-location__button" type="button" data-location-cancel>Отмена</button>
              </div>
            </div>
          </div>
        </div>
        <button class="menu-toggle" id="menuToggle" aria-label="Меню">
          <span class="line line-top"></span>
          <span class="line line-middle"></span>
          <span class="line line-bottom"></span>
        </button>
        <div class="header-lang" id="headerLang">
          <button class="lang-toggle" id="langToggle" aria-label="Смена языка" aria-haspopup="true" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12.87 15.07l-2.54-2.51.03-.03c1.74-1.94 2.98-4.17 3.71-6.53H17V4h-7V2H8v2H1v1.99h11.17C11.5 7.92 10.44 9.75 9 11.35 8.07 10.32 7.3 9.19 6.69 8h-2c.73 1.63 1.73 3.17 2.98 4.56l-5.09 5.02L4 19l5-5 3.11 3.11.76-2.04zM18.5 10h-2L12 22h2l1.12-3h4.75L21 22h2l-4.5-12zm-2.62 7l1.62-4.33L19.12 17h-3.24z"/>
            </svg>
          </button>
          <div class="lang-dropdown" id="langDropdown" hidden>
            <button class="lang-item active" type="button" data-lang="ru">RU</button>
            <button class="lang-item" type="button" data-lang="en">EN</button>
          </div>
        </div>
      </div>
    </div>
  </header>
	
  <div class="nav-drawer" id="navDrawer" aria-hidden="true">
    <div class="nav-drawer__backdrop" id="navBackdrop"></div>
    <aside class="nav-drawer__panel">
      <div class="drawer-header">
        <div class="drawer-brand">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/img/LOGOLOGO11.png" alt="БИС — Баланс Инженерных Систем" class="drawer-mark">
        </div>
        <button class="drawer-close" id="drawerClose" aria-label="Закрыть меню">
          <span></span>
          <span></span>
        </button>
      </div>
      <ul class="drawer-nav">
        <li><a href="<?php echo esc_url(home_url()); ?>">На главную</a></li>
        <li><a href="<?php echo esc_url(home_url('/services/')); ?>">Услуги</a></li>
        <li><a href="<?php echo esc_url(home_url('/media/')); ?>">Медиа</a></li>
        <li><a href="<?php echo esc_url(home_url('/projects/')); ?>">Наши проекты</a></li>
        <li><a href="<?php echo esc_url(home_url('/#services'));?>">Специализация</a></li>
        <li><a href="<?php echo esc_url(home_url('/#equipment'));?>">Оборудование</a></li>
        <li><a href="<?php echo esc_url(home_url('/#contact'));?>">Контакты</a></li>
        <li><a href="<?php echo esc_url(home_url('/about/')); ?>">О нас</a></li>
        <li><a href="<?php echo esc_url(home_url('/#faq'));?>">F.A.Q</a></li>
      </ul>
      <div class="drawer-footer">
        <p class="drawer-note">Инжиниринговая команда полного цикла — проектируем, запускаем, сопровождаем.</p>
      </div>
    </aside>
  </div>
