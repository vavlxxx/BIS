  <!-- Footer -->
  <footer class="footer">
    <?php
    $documents = array(
      array(
        'label' => 'Сертификат соответствия ГОСТ Р ИСО 9001-2015 (ISO 9001:2015)',
        'file' => 'Сертификат ИСО 14 644-1 Чистые помещения.pdf',
      ),
      array(
        'label' => 'Сертификат ИСО 9001-2015',
        'file' => 'Сертификат ИСО 9001-2015.pdf',
      ),
      array(
        'label' => 'Сертификат соответствия СМП',
        'file' => 'Сертификат соответствия СМП.pdf',
      ),
      array(
        'label' => 'Уведомление о приеме СРО ЦСО',
        'file' => 'Уведомление о приеме СРО ЦСО.pdf',
      ),
      array(
        'label' => 'Уведомление о соответствии ЛИЦЕНЗИИ МЧС',
        'file' => 'Уведомление_о_соответствии_ЛИЦЕНЗИИ_МЧС.pdf',
      ),
      array(
        'label' => 'Сертификат качества испытательных и калибровочных лабораторий применительно к пусконаладке инженерных систем',
        'file' => 'СС_ИСО_9001_БИС_–_Баланс_Инженерных_Систем.pdf',
      ),
    );
    ?>
    <div class="footer-content">
      <div class="footer-section footer-section--contacts">
        <h3>ООО «БИС — Баланс Инженерных Систем»</h3>
        <dl class="footer-requisites">
          <div class="footer-requisites__row">
            <dt>ИНН / ОГРН</dt>
            <dd>7722323589 / 1157746324625</dd>
          </div>
          <div class="footer-requisites__row">
            <dt>КПП</dt>
            <dd>772201001</dd>
          </div>
          <div class="footer-requisites__row">
            <dt>ОКПО</dt>
            <dd>43250753</dd>
          </div>
          <div class="footer-requisites__row">
            <dt>ОКАТО</dt>
            <dd>45290564000</dd>
          </div>
          <div class="footer-requisites__row">
            <dt>ОКТМО</dt>
            <dd>45388000000</dd>
          </div>
          <div class="footer-requisites__row">
            <dt>Наименование Банка</dt>
            <dd>ПАО СБЕРБАНК г. Москва</dd>
          </div>
          <div class="footer-requisites__row">
            <dt>Расчетный счет (в рублях)</dt>
            <dd>40702810338000031340</dd>
          </div>
          <div class="footer-requisites__row">
            <dt>Корреспондентский счет</dt>
            <dd>30101810400000000225</dd>
          </div>
          <div class="footer-requisites__row">
            <dt>БИК</dt>
            <dd>044525225</dd>
          </div>
        </dl>
        <a class="footer-trusted-company" href="https://focus.kontur.ru/entity?query=1157746324625" target="_blank" rel="nofollow" aria-label="ООО «БИС — Баланс Инженерных Систем» в Контур.Фокус">
          <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/img/trusted-company.png" alt="Надёжная компания">
        </a>
      </div>
      <div class="footer-section">
        <h3>Навигация</h3>
        <p><a href="#services">Специализация</a></p>
        <p><a href="#equipment">Оборудование</a></p>
        <p><a href="#experience">Опыт</a></p>
        <p><a href="<?php echo esc_url(home_url('/services/')); ?>">Услуги</a></p>
        <p><a href="<?php echo esc_url(home_url('/calculators/')); ?>">Калькуляторы</a></p>
        <p><a href="<?php echo esc_url(home_url('/about/')); ?>">О нас</a></p>
        <p><a href="<?php echo esc_url(home_url('/projects/')); ?>">Наши проекты</a></p>
        <p><a href="<?php echo esc_url(home_url('/media/')); ?>">Медиа</a></p>
        <p><a href="#contact">Контакты</a></p>
        <p><a href="#faq">F.A.Q</a></p>
      </div>
      <div class="footer-section">
        <h3>Контакты</h3>
        <p style="display: flex; align-items: center; gap: 12px;">
          <a class="footer-phone" href="tel:+79264380770">+7 (926) 438-07-70</a>
        </p>
        <p><a class="footer-phone" href="tel:+79169861187">+7 (916) 986-11-87</a></p>
        <p><a href="mailto:office@bis-rf.ru">office@bis-rf.ru</a></p>
        <p style="display: flex; align-items: center; gap: 12px; margin-top: 20px;">
          <a href="https://t.me/+79264380770" target="_blank" rel="noopener" aria-label="Telegram" style="display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px; transition: opacity 0.3s ease;" onmouseover="this.style.opacity=0.7" onmouseout="this.style.opacity=1">
            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/img/telegram-white-32x32.png" alt="Telegram" style="width: 100%; height: 100%; object-fit: contain;">
          </a>
          <a href="https://max.ru/u/f9LHodD0cOIYdHZd-s9_nqTN9t76kGjdQxmIoxXSFGhqRnW3d4TLAMEFfVs" target="_blank" rel="noopener" aria-label="Max" style="display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px; transition: opacity 0.3s ease;" onmouseover="this.style.opacity=0.7" onmouseout="this.style.opacity=1">
            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/img/MAX-white-32x32.png" alt="Max" style="width: 100%; height: 100%; object-fit: contain;">
          </a>
        </p>
      </div>
      <div class="footer-section">
        <h3>Документы</h3>
        <ul class="document-links">
          <?php foreach ($documents as $document) : ?>
            <li>
              <a href="<?php echo esc_url(get_template_directory_uri() . '/assets/documents/' . rawurlencode($document['file'])); ?>" target="_blank" rel="noopener">
                <span class="document-icon" aria-hidden="true">
                  <svg width="22" height="26" viewBox="0 0 22 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13.5 1H4C2.89543 1 2 1.89543 2 3V23C2 24.1046 2.89543 25 4 25H18C19.1046 25 20 24.1046 20 23V7.5L13.5 1Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                    <path d="M13 1V7H20" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                    <path d="M7 14H15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M7 18H11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                  </svg>
                </span>
                <span class="document-text"><?php echo esc_html($document['label']); ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?php echo date('Y'); ?> Баланс Инженерных Систем. Все права защищены. | <a href="#privacy" class="privacy-link">Политика конфиденциальности</a></p>
    </div>
  </footer>

  <?php get_template_part('estimate-modal'); ?>
  <div class="cookie-consent" id="cookieConsentBanner" hidden>
    <p class="cookie-consent__text">Этот сайт использует cookie для хранения данных. Продолжая использовать сайт, Вы даете согласие на работу с этими файлами.</p>
    <button class="cookie-consent__button" id="cookieConsentAccept" type="button">Принять и закрыть</button>
  </div>
  <div class="popup-overlay popup-overlay--exit-intent" id="exitIntentOverlay" aria-hidden="true">
    <div class="exit-intent-modal">
      <button class="popup-close exit-intent-modal__close" type="button" data-exit-intent-close aria-label="Закрыть форму">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      <div class="exit-intent-modal__tag">Специальное предложение</div>
      <h2 class="exit-intent-modal__title">Уже уходите не получив предложение?</h2>
      <p class="exit-intent-modal__text">Просто оставьте телефон.</p>
      <form class="contact-form exit-intent-modal__form" id="exitIntentForm">
        <div class="form-group">
          <label for="exitIntentPhone">Телефон</label>
          <input type="tel" id="exitIntentPhone" name="phone" required placeholder="+7 (___) ___-__-__" autocomplete="tel">
        </div>
        <?php echo do_shortcode('[hcaptcha auto="true" force="true"]'); ?>
        <button type="submit" class="btn btn-primary">Отправить</button>
      </form>
    </div>
  </div>
  <?php wp_footer(); ?>
  <div class="floating-estimate-wrapper">
    <div class="floating-socials-panel" data-floating-social-panel>
      <div class="floating-socials-panel__head">
        <span class="floating-socials-panel__title">Свяжитесь с нами</span>
        <div class="floating-socials-panel__controls">
          <button class="floating-socials-panel__control" type="button" data-floating-social-close aria-label="Скрыть виджет">x</button>
        </div>
      </div>
      <p class="floating-socials-panel__text">Выберите удобный способ связи, и мы быстро ответим.</p>
      <div class="floating-socials-panel__contacts">
        <a class="floating-socials-panel__contact footer-phone" href="tel:+79264380770">+7 (926) 438-07-70</a>
        <a class="floating-socials-panel__contact footer-phone" href="tel:+79169861187">+7 (916) 986-11-87</a>
        <a class="floating-socials-panel__contact" href="mailto:office@bis-rf.ru">office@bis-rf.ru</a>
      </div>
      <div class="floating-socials">
        <a class="floating-socials__link floating-socials__link--telegram" href="https://t.me/+79264380770" target="_blank" rel="noopener noreferrer" aria-label="Telegram">
          <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/img/telegram-white-32x32.png" alt="Telegram" width="32" height="32" loading="lazy">
        </a>
        <a class="floating-socials__link floating-socials__link--max" href="https://max.ru/u/f9LHodD0cOIYdHZd-s9_nqTN9t76kGjdQxmIoxXSFGhqRnW3d4TLAMEFfVs" target="_blank" rel="noopener noreferrer" aria-label="Max">
          <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/img/MAX-white-32x32.png" alt="Max" width="32" height="32" loading="lazy">
        </a>
      </div>
      <button class="open-estimate-modal floating-estimate-btn">Рассчитать смету и сроки</button>
    </div>
    <button class="floating-socials-open" type="button" data-floating-social-open hidden aria-label="Показать контакты">
      <svg class="floating-socials-open__icon" width="22" height="22" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path d="M5 4.5c0-.83.67-1.5 1.5-1.5h2.1c.67 0 1.26.44 1.44 1.08l.76 2.66a1.5 1.5 0 0 1-.39 1.47L9.1 9.52a11.2 11.2 0 0 0 5.38 5.38l1.31-1.31a1.5 1.5 0 0 1 1.47-.39l2.66.76A1.5 1.5 0 0 1 21 15.4v2.1c0 .83-.67 1.5-1.5 1.5H18C10.82 19 5 13.18 5 6V4.5Z" fill="currentColor"/>
        <path d="M15.5 4.25c2.35.55 4.2 2.4 4.75 4.75M15.75 7.5c.92.32 1.43.83 1.75 1.75" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
      </svg>
    </button>
  </div>
</body>
</html>

