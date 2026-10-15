/* Shared client-requested interactions. */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form.contact-form, #projectConsultationForm, #turnkeyCalcForm').forEach(form => {
    form.noValidate = true;
    form.querySelectorAll('input:not([type="hidden"]), textarea, select').forEach(field => {
      field.addEventListener('blur', () => validateField(field));
      field.addEventListener('input', () => { if (field.getAttribute('aria-invalid') === 'true') validateField(field); });
    });
  });
  document.querySelectorAll('.open-estimate-modal').forEach(button => {
    if (button.textContent.includes('Рассчитать смету и сроки')) button.classList.add('bis-estimate-action');
  });
  document.querySelectorAll('[data-gallery-section]').forEach(section => {
    const cards = [...section.querySelectorAll('[data-gallery-kind]')];
    const buttons = [...section.querySelectorAll('[data-gallery-filter]')];
    buttons.forEach(button => button.addEventListener('click', () => {
      const kind = button.dataset.galleryFilter;
      buttons.forEach(item => {
        const active = item === button;
        item.setAttribute('aria-pressed', String(active));
        item.classList.toggle('btn-primary', active); item.classList.toggle('btn-outline', !active);
      });
      cards.forEach(card => {
        card.hidden = kind !== 'all' && card.dataset.galleryKind !== kind;
        if (card.hidden) card.querySelector('video')?.pause();
      });
      section.querySelector('[data-gallery-empty]').hidden = cards.some(card => !card.hidden);
    }));
  });
  document.querySelectorAll('[data-faq-section]').forEach(section => {
    const buttons = [...section.querySelectorAll('[data-faq-filter]')];
    buttons.forEach(button => button.addEventListener('click', () => {
      buttons.forEach(item => {
        const active = item === button;
        item.setAttribute('aria-pressed', String(active));
        item.classList.toggle('btn-primary', active); item.classList.toggle('btn-outline', !active);
      });
      section.querySelectorAll('[data-faq-categories]').forEach(item => {
        item.hidden = button.dataset.faqFilter !== 'all' && !item.dataset.faqCategories.split(',').includes(button.dataset.faqFilter);
        if (item.hidden) {
          item.open = false;
          if (item.classList.contains('active')) {
            item.classList.remove('active');
            const ans = item.querySelector('.faq-answer');
            if (ans) {
              ans.style.maxHeight = '0px';
              ans.style.overflow = 'hidden';
            }
          }
        }
      });
    }));
  });
  document.querySelectorAll('[data-gallery-photo]').forEach(link => link.addEventListener('click', event => {
    event.preventDefault();
    const dialog = document.createElement('dialog'); dialog.className = 'bis-photo-dialog';
    const close = document.createElement('button'); close.type = 'button'; close.textContent = '×'; close.setAttribute('aria-label','Закрыть фото');
    const img = document.createElement('img'); img.src = link.href; img.alt = link.querySelector('img')?.alt || '';
    dialog.append(close, img); document.body.append(dialog);
    const cleanup = () => { dialog.remove(); link.focus(); };
    close.addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', e => { if (e.target === dialog) dialog.close(); });
    dialog.addEventListener('close', cleanup, {once:true}); dialog.showModal();
  }));
  const message = document.querySelector('[data-service-message]');
  if (message) {
    const key = `bis_service_message_${message.dataset.serviceMessage}`;
    let shown = false; try { shown = sessionStorage.getItem(key) === '1'; } catch (e) {}
    if (!shown) window.setTimeout(() => {
      message.hidden = false;
      document.body.classList.add('bis-service-message-visible');
      void message.offsetWidth;
      requestAnimationFrame(() => message.classList.add('is-visible'));
      try { sessionStorage.setItem(key, '1'); } catch (e) {}
    }, 1200);
    const dismiss = () => { message.classList.remove('is-visible'); message.hidden = true; document.body.classList.remove('bis-service-message-visible'); };
    message.querySelector('[data-service-message-close]').addEventListener('click', dismiss);
    message.querySelector('.callback-btn').addEventListener('click', dismiss);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') dismiss(); });
  }
  // Modal focus and keyboard return for the newly global callback form.
  const callback = document.getElementById('callbackOverlay');
  if (callback) {
    callback.setAttribute('role','dialog'); callback.setAttribute('aria-modal','true'); callback.setAttribute('aria-label','Заказать обратный звонок');
    let trigger;
    document.querySelectorAll('.callback-btn, .callback-btn-mobile').forEach(button => button.addEventListener('click', (e) => { if (button.tagName === 'A') e.preventDefault(); trigger = button; requestAnimationFrame(() => callback.querySelector('input')?.focus()); }));
    callback.addEventListener('keydown', event => {
      if (event.key !== 'Tab') return;
      const items = [...callback.querySelectorAll('button, input, textarea, a[href]')].filter(el => !el.disabled && el.offsetParent !== null);
      const first = items[0], last = items.at(-1);
      if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
      if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    });
    new MutationObserver(() => { if (!callback.classList.contains('active')) trigger?.focus(); }).observe(callback, {attributes:true,attributeFilter:['class']});
  }
});
