<!-- Vacancy Application Modal -->
<div class="popup-overlay" id="vacancyOverlay">
  <div class="vacancy-modal">
    <button type="button" class="vacancy-close" id="vacancyClose" aria-label="Закрыть форму">
      <span></span>
      <span></span>
    </button>
    <div class="vacancy-modal-content">
      <div class="vacancy-modal-header">
        <span class="section-badge">Отклик на вакансию</span>
        <h2 class="vacancy-modal-title" id="vacancyModalTitle">Инженер АСУ ТП систем ОВиК</h2>
        <p class="vacancy-modal-desc">Заполните контактные данные и прикрепите резюме. Наш специалист HR свяжется с вами в течение рабочего дня.</p>
      </div>
      <form class="contact-form vacancy-form" id="vacancyForm" enctype="multipart/form-data">
        <input type="hidden" name="vacancy_title" id="vacancyTitleInput" value="">
        
        <div class="form-row-2col">
          <div class="form-group">
            <label for="vacancyName">Ваше имя *</label>
            <input type="text" id="vacancyName" name="name" required placeholder="Ваше имя" autocomplete="name">
          </div>
          <div class="form-group">
            <label for="vacancyPhone">Телефон *</label>
            <input type="tel" id="vacancyPhone" name="phone" required placeholder="+7 (___) ___-__-__" autocomplete="tel">
          </div>
        </div>

        <div class="form-row-2col">
          <div class="form-group">
            <label for="vacancyEmail">Email *</label>
            <input type="email" id="vacancyEmail" name="email" required placeholder="ivanov@mail.ru" autocomplete="email">
          </div>
          <div class="form-group">
            <label for="vacancyResume">Резюме <span class="vacancy-file-hint">(.pdf, .doc, .docx)</span></label>
            <div class="vacancy-file-upload">
              <input type="file" id="vacancyResume" name="resume" accept=".pdf,.doc,.docx" class="vacancy-file-input">
              <label for="vacancyResume" class="vacancy-file-label" id="vacancyResumeLabel">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                  <polyline points="17 8 12 3 7 8"/>
                  <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                <span class="vacancy-file-text">Файл резюме...</span>
              </label>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="vacancyMessage">Сопроводительное сообщение / Опыт работы</label>
          <textarea id="vacancyMessage" name="message" rows="2" placeholder="Расскажите о профильном опыте или оставьте ссылку на портфолио / HH.ru..."></textarea>
        </div>

        <?php echo do_shortcode('[hcaptcha auto="true" force="true"]'); ?>

        <button type="submit" class="btn btn-primary btn-block vacancy-submit-btn">Отправить отклик на вакансию</button>
        <p class="form-consent">Нажимая на кнопку, вы даете согласие на обработку своих персональных данных и соглашаетесь с Политикой конфиденциальности сайта</p>
      </form>
    </div>
  </div>
</div>
