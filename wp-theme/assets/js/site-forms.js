const bisAjaxUrl = window.bisSiteConfig?.ajaxUrl || '/wp-admin/admin-ajax.php';
const bisLocationCookieName = window.bisSiteConfig?.locationCookieName || 'bis_user_location';
const bisLocationFallback = window.bisSiteConfig?.locationFallback || 'Не определено';

const bisHCaptchaSiteKey = window.bisSiteConfig?.hcaptchaSiteKey || '';

function setBisCookie(name, value, maxAgeSeconds = 60 * 60 * 24 * 180) {
  document.cookie = `${name}=${value}; path=/; max-age=${maxAgeSeconds}; SameSite=Lax`;
}

function getBisCookie(name) {
  const cookiePrefix = `${name}=`;
  const cookies = document.cookie ? document.cookie.split('; ') : [];

  for (const cookie of cookies) {
    if (cookie.startsWith(cookiePrefix)) {
      return cookie.substring(cookiePrefix.length);
    }
  }

  return '';
}

function normalizeLocationData(rawLocation) {
  if (!rawLocation || typeof rawLocation !== 'object') {
    return null;
  }

  const city = typeof rawLocation.city === 'string' && rawLocation.city.trim()
    ? rawLocation.city.trim()
    : bisLocationFallback;
  const region = typeof rawLocation.region === 'string' ? rawLocation.region.trim() : '';
  const label = typeof rawLocation.label === 'string' && rawLocation.label.trim()
    ? rawLocation.label.trim()
    : city;

  return {
    city,
    region,
    label,
    source: typeof rawLocation.source === 'string' && rawLocation.source.trim() ? rawLocation.source.trim() : 'fallback',
    resolved: Boolean(rawLocation.resolved) && city !== bisLocationFallback,
    confirmed: Boolean(rawLocation.confirmed),
    custom: Boolean(rawLocation.custom),
    promptSeen: Boolean(rawLocation.promptSeen)
  };
}

function getSavedLocation() {
  const cookieValue = getBisCookie(bisLocationCookieName);
  if (!cookieValue) {
    return null;
  }

  try {
    return normalizeLocationData(JSON.parse(decodeURIComponent(cookieValue)));
  } catch (error) {
    console.warn('Location cookie parse failed', error);
    return null;
  }
}

function updateHeaderLocation(location = getSavedLocation()) {
  const city = location?.city || bisLocationFallback;
  const label = location?.label || bisLocationFallback;

  document.querySelectorAll('[data-location-city], [data-location-current-city]').forEach((element) => {
    element.textContent = element.hasAttribute('data-location-current-city') ? label : city;
  });
}

function saveLocation(rawLocation) {
  const location = normalizeLocationData(rawLocation) || {
    city: bisLocationFallback,
    region: '',
    label: bisLocationFallback,
    source: 'fallback',
    resolved: false,
    confirmed: false,
    custom: false,
    promptSeen: true
  };

  setBisCookie(bisLocationCookieName, encodeURIComponent(JSON.stringify(location)));
  updateHeaderLocation(location);

  return location;
}

function appendLocationToFormData(formData) {
  if (!(formData instanceof FormData)) {
    return;
  }

  const location = getSavedLocation();
  formData.append('location_region', location?.label || bisLocationFallback);
  formData.append('location_city', location?.city || bisLocationFallback);
  formData.append('location_source', location?.source || 'fallback');
}

window.bisAppendLocationToFormData = appendLocationToFormData;

function getHCaptchaContainers(scope = document) {
  return Array.from(scope.querySelectorAll('.h-captcha, h-captcha'));
}

function rerenderHCaptchaWidget(widget) {
  if (!widget) return;

  const form = widget.closest('form');
  const currentId = form?.dataset?.hCaptchaId || '';

  if (window.hCaptcha && Array.isArray(window.hCaptcha.foundForms) && currentId) {
    window.hCaptcha.foundForms = window.hCaptcha.foundForms.filter((foundForm) => foundForm.hCaptchaId !== currentId);
  }

  if (form) {
    form.removeAttribute('data-h-captcha-id');
  }

  widget.innerHTML = '';

  if (typeof window.hCaptchaBindEvents === 'function') {
    window.hCaptchaBindEvents(widget);
  }
}

function resetHCaptcha(form) {
  if (!form) return;

  form.querySelectorAll('textarea[name="h-captcha-response"], input[name="h-captcha-response"]').forEach((field) => {
    field.value = '';
  });

  const widgets = getHCaptchaContainers(form);
  if (!widgets.length) {
    return;
  }

  if (typeof window.hCaptchaBindEvents === 'function') {
    widgets.forEach((widget) => {
      try {
        rerenderHCaptchaWidget(widget);
      } catch (error) {
        console.warn('hCaptcha reset failed', error);
      }
    });
    return;
  }

  if (typeof window.hCaptchaReset !== 'function') {
    return;
  }

  widgets.forEach((widget) => {
    try {
      window.hCaptchaReset(widget);
    } catch (error) {
      console.warn('hCaptcha reset failed', error);
    }
  });
}

function clearHCaptchaError(form) {
  if (!form) return;

  form.querySelectorAll('.h-captcha').forEach((widget) => {
    widget.classList.remove('error');
    const errorElement = widget.parentElement?.querySelector('.error-message.error-message--captcha');
    if (errorElement) {
      errorElement.remove();
    }
  });
}

function validateHCaptcha(form) {
  if (!form) return true;

  const widgets = getHCaptchaContainers(form);
  if (!widgets.length) {
    return true;
  }

  const responseField = form.querySelector('textarea[name="h-captcha-response"], input[name="h-captcha-response"]');
  if (responseField && responseField.value.trim()) {
    clearHCaptchaError(form);
    return true;
  }

  const widget = widgets[0];
  if (!widget) {
    return true;
  }

  widget.classList.add('error');

  let errorElement = widget.parentElement?.querySelector('.error-message.error-message--captcha');
  if (!errorElement && widget.parentElement) {
    errorElement = document.createElement('span');
    errorElement.className = 'error-message error-message--captcha';
    errorElement.style.color = '#ef4444';
    errorElement.style.fontSize = '13px';
    errorElement.style.marginTop = '4px';
    errorElement.style.display = 'block';
    widget.parentElement.appendChild(errorElement);
  }

  if (errorElement) {
    errorElement.textContent = 'Подтвердите, что вы не робот';
  }

  return false;
}

function formatRussianPhone(value) {
  let digits = value.replace(/\D/g, '');

  if (digits.startsWith('7') || digits.startsWith('8')) {
    digits = digits.slice(1);
  }

  digits = digits.substring(0, 10);

  const parts = {
    area: digits.substring(0, 3),
    central: digits.substring(3, 6),
    line1: digits.substring(6, 8),
    line2: digits.substring(8, 10)
  };

  let formatted = '+7';

  if (parts.area) {
    formatted += ` (${parts.area}`;
    if (parts.area.length === 3) {
      formatted += ')';
    }
  }

  if (parts.central) {
    formatted += ` ${parts.central}`;
  }

  if (parts.line1) {
    formatted += `-${parts.line1}`;
  }

  if (parts.line2) {
    formatted += `-${parts.line2}`;
  }

  if (!parts.area) {
    formatted += ' ';
  }

  return formatted.trimEnd();
}

function isValidRussianPhone(value) {
  const digits = value.replace(/\D/g, '');
  return digits.length === 11 && digits.startsWith('7');
}

function attachPhoneMask(input) {
  if (!input || input.dataset.phoneMaskBound === 'true') return;

  input.dataset.phoneMaskBound = 'true';

  input.addEventListener('focus', () => {
    if (!input.value.trim()) {
      input.value = '+7 ';
    }
  });

  input.addEventListener('input', (event) => {
    event.target.value = formatRussianPhone(event.target.value);
  });

  input.addEventListener('blur', () => {
    const digits = input.value.replace(/\D/g, '');
    if (digits.length <= 1) {
      input.value = '';
    }
  });
}

function initPhoneMasks(root = document) {
  root.querySelectorAll('input[type="tel"]').forEach(attachPhoneMask);
}

function resetFormState(form, { clearErrors = false } = {}) {
  if (!form) return;

  form.reset();
  resetHCaptcha(form);

  if (!clearErrors) return;

  const inputs = form.querySelectorAll('input, textarea, select');
  inputs.forEach((input) => clearError(input));
}

function syncUniformCardHeights() {
  const containers = document.querySelectorAll('.equipment-grid, .experience-grid, .projects-grid');

  containers.forEach((container) => {
    const cards = Array.from(container.children).filter((card) => (
      card.classList.contains('equipment-card') ||
      card.classList.contains('experience-card')
    ));

    cards.forEach((card) => {
      card.style.height = '';
    });

    if (cards.length < 2) return;

    let maxHeight = 0;
    cards.forEach((card) => {
      maxHeight = Math.max(maxHeight, card.offsetHeight);
    });

    cards.forEach((card) => {
      card.style.height = `${maxHeight}px`;
    });
  });
}

function applyBisCondensedStyling(root = document.body) {
  if (!root) return;

  const disallowedParents = new Set(['SCRIPT', 'STYLE', 'NOSCRIPT', 'TEXTAREA', 'OPTION']);
  const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null);
  const textNodes = [];

  while (walker.nextNode()) {
    textNodes.push(walker.currentNode);
  }

  textNodes.forEach(node => {
    const parent = node.parentNode;
    if (!parent || disallowedParents.has(parent.nodeName) || parent.closest('.bis-condensed')) {
      return;
    }

    const text = node.nodeValue;
    if (!text || !text.includes('БИС')) return;

    const parts = text.split(/(БИС)/);
    const fragment = document.createDocumentFragment();

    parts.forEach(part => {
      if (!part) return;
      if (part === 'БИС') {
        const span = document.createElement('span');
        span.className = 'bis-condensed';
        span.textContent = part;
        fragment.appendChild(span);
      } else {
        fragment.appendChild(document.createTextNode(part));
      }
    });

    parent.replaceChild(fragment, node);
  });
}

function requestDetectedLocation() {
  const requestByIp = (ip = '') => {
    const formData = new FormData();
    formData.append('action', 'bis_detect_location');

    if (ip) {
      formData.append('ip', ip);
    }

    return fetch(bisAjaxUrl, {
      method: 'POST',
      body: formData
    })
      .then((response) => response.json())
      .then((data) => {
        if (!data.success || !data.data) {
          throw new Error(data.data?.message || 'Не удалось определить город');
        }

        return normalizeLocationData(data.data);
      });
  };

  return fetch('https://api64.ipify.org?format=json')
    .then((response) => response.json())
    .then((data) => (typeof data?.ip === 'string' ? data.ip.trim() : ''))
    .catch(() => '')
    .then((ip) => requestByIp(ip).catch(() => requestByIp()));
}

function resolveManualLocation(query) {
  const formData = new FormData();
  formData.append('action', 'bis_resolve_location');
  formData.append('query', query);

  return fetch(bisAjaxUrl, {
    method: 'POST',
    body: formData
  })
    .then((response) => response.json())
    .then((data) => {
      if (!data.success || !data.data) {
        throw new Error(data.data?.message || 'Не удалось найти город');
      }

      return normalizeLocationData(data.data);
    });
}

function initHeaderLocation() {
  const widget = document.querySelector('[data-location-widget]');
  if (!widget) {
    return;
  }

  const trigger = widget.querySelector('[data-location-trigger]');
  const popover = widget.querySelector('[data-location-popover]');
  const closeButton = widget.querySelector('[data-location-close]');
  const confirmButton = widget.querySelector('[data-location-confirm]');
  const otherButton = widget.querySelector('[data-location-other]');
  const saveButton = widget.querySelector('[data-location-save]');
  const cancelButton = widget.querySelector('[data-location-cancel]');
  const input = widget.querySelector('[data-location-input]');
  const error = widget.querySelector('[data-location-error]');
  const steps = {
    confirm: widget.querySelector('[data-location-step="confirm"]'),
    custom: widget.querySelector('[data-location-step="custom"]')
  };

  let currentStep = 'confirm';
  let returnStep = 'confirm';

  const getCurrentLocation = () => getSavedLocation() || {
    city: bisLocationFallback,
    region: '',
    label: bisLocationFallback,
    source: 'fallback',
    resolved: false,
    confirmed: false,
    custom: false,
    promptSeen: true
  };

  const clearCustomError = () => {
    if (!input || !error) {
      return;
    }

    input.classList.remove('is-error');
    error.hidden = true;
    error.textContent = 'Введите город';
  };

  const showCustomError = (message) => {
    if (!input || !error) {
      return;
    }

    input.classList.add('is-error');
    error.hidden = false;
    error.textContent = message;
  };

  const setStep = (step) => {
    currentStep = step;

    Object.entries(steps).forEach(([key, element]) => {
      if (!element) {
        return;
      }

      element.hidden = key !== step;
    });

    clearCustomError();

    if (step === 'custom' && input) {
      const location = getCurrentLocation();
      input.value = location.city && location.city !== bisLocationFallback ? location.city : '';
      window.setTimeout(() => input.focus(), 30);
    }
  };

  const openPopover = (step = currentStep) => {
    if (!popover || !trigger) {
      return;
    }

    widget.classList.add('is-open');
    popover.hidden = false;
    trigger.setAttribute('aria-expanded', 'true');
    setStep(step);
  };

  const ensureFallbackLocation = () => {
    const location = getSavedLocation();
    if (location) {
      return location;
    }

    return saveLocation({
      city: bisLocationFallback,
      region: '',
      label: bisLocationFallback,
      source: 'fallback',
      resolved: false,
      confirmed: false,
      custom: false,
      promptSeen: true
    });
  };

  const closePopover = () => {
    if (!popover || !trigger) {
      return;
    }

    ensureFallbackLocation();
    widget.classList.remove('is-open');
    popover.hidden = true;
    trigger.setAttribute('aria-expanded', 'false');
    clearCustomError();
  };

  updateHeaderLocation();

  trigger?.addEventListener('click', () => {
    if (widget.classList.contains('is-open')) {
      closePopover();
      return;
    }

    const location = getCurrentLocation();
    if (location.resolved && location.confirmed) {
      returnStep = 'custom';
      openPopover('custom');
      return;
    }

    openPopover(location.resolved ? 'confirm' : 'custom');
  });

  confirmButton?.addEventListener('click', () => {
    const location = getCurrentLocation();
    saveLocation({
      ...location,
      promptSeen: true,
      confirmed: true
    });
    closePopover();
  });

  otherButton?.addEventListener('click', () => {
    returnStep = 'confirm';
    setStep('custom');
  });

  saveButton?.addEventListener('click', () => {
    const query = input?.value.trim() || '';
    if (!query) {
      showCustomError('Введите город');
      return;
    }

    clearCustomError();

    resolveManualLocation(query)
      .then((location) => {
        saveLocation({
          ...location,
          source: 'manual',
          promptSeen: true,
          confirmed: true,
          custom: true
        });
        closePopover();
      })
      .catch((errorMessage) => {
        showCustomError(errorMessage?.message || 'Не удалось найти такой город');
      });
  });

  cancelButton?.addEventListener('click', () => {
    const location = getCurrentLocation();
    if (returnStep === 'confirm' && location.resolved) {
      setStep('confirm');
      return;
    }

    closePopover();
  });

  closeButton?.addEventListener('click', closePopover);

  input?.addEventListener('input', () => {
    if (input.value.trim()) {
      clearCustomError();
    }
  });

  document.addEventListener('click', (event) => {
    if (!widget.classList.contains('is-open')) {
      return;
    }

    if (!widget.contains(event.target)) {
      closePopover();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && widget.classList.contains('is-open')) {
      closePopover();
    }
  });

  if (getSavedLocation()) {
    updateHeaderLocation(getSavedLocation());
    return;
  }

  requestDetectedLocation()
    .then((location) => {
      const storedLocation = saveLocation({
        ...location,
        promptSeen: true,
        confirmed: false,
        custom: false
      });

      openPopover(storedLocation.resolved ? 'confirm' : 'custom');
    })
    .catch(() => {
      saveLocation({
        city: bisLocationFallback,
        region: '',
        label: bisLocationFallback,
        source: 'fallback',
        resolved: false,
        confirmed: false,
        custom: false,
        promptSeen: true
      });

      openPopover('custom');
    });
}

function initExitIntentModal() {
  const overlay = document.getElementById('exitIntentOverlay');
  const form = document.getElementById('exitIntentForm');
  const closeButtons = document.querySelectorAll('[data-exit-intent-close]');
  const storageKey = 'bisExitIntentShown';
  const canTrackPointer = window.matchMedia ? window.matchMedia('(pointer:fine)').matches : true;
  let hasShown = false;

  if (!overlay || !form || !canTrackPointer) {
    return;
  }

  if (window.localStorage.getItem(storageKey) === '1') {
    return;
  }

  const phoneInput = form.querySelector('input[type="tel"]');
  if (phoneInput) {
    attachPhoneMask(phoneInput);
  }

  if (bisHCaptchaSiteKey && !form.querySelector('.h-captcha')) {
    const submitButton = form.querySelector('button[type="submit"]');
    const widget = document.createElement('div');
    widget.className = 'h-captcha';
    widget.setAttribute('data-sitekey', bisHCaptchaSiteKey);

    if (submitButton) {
      form.insertBefore(widget, submitButton);
    } else {
      form.appendChild(widget);
    }

    if (typeof window.hcaptcha !== 'undefined' && typeof window.hcaptcha.render === 'function') {
      window.hcaptcha.render(widget, { sitekey: bisHCaptchaSiteKey });
    }
  }

  const closeOverlay = () => {
    overlay.classList.remove('active');
    overlay.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    window.localStorage.setItem(storageKey, '1');
    resetFormState(form, { clearErrors: true });
  };

  const openOverlay = () => {
    if (overlay.classList.contains('active') || hasShown) {
      return;
    }

    hasShown = true;
    overlay.classList.add('active');
    overlay.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  };

  const handleExitIntent = (event) => {
    if (window.localStorage.getItem(storageKey) === '1') {
      return;
    }

    if (event.relatedTarget || event.toElement || event.clientY > 10) {
      return;
    }

    openOverlay();
  };

  document.addEventListener('mouseout', handleExitIntent);
  document.documentElement.addEventListener('mouseleave', handleExitIntent);

  closeButtons.forEach((button) => {
    button.addEventListener('click', closeOverlay);
  });

  overlay.addEventListener('click', (event) => {
    if (event.target === overlay) {
      closeOverlay();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && overlay.classList.contains('active')) {
      closeOverlay();
    }
  });

  form.addEventListener('submit', (event) => {
    event.preventDefault();

    if (!validateFormFields(form) || !validateHCaptcha(form)) {
      return;
    }

    submitAjaxForm(form, 'bis_submit_general_request', {
      request_type: 'exit_intent'
    }, {
      successMessage: 'Спасибо! Мы скоро свяжемся с вами.',
      onSuccess: () => {
        window.localStorage.setItem(storageKey, '1');
        resetFormState(form);
        closeOverlay();
      }
    });
  });
}

function initCookieConsent() {
  const banner = document.getElementById('cookieConsentBanner');
  const acceptButton = document.getElementById('cookieConsentAccept');
  const storageKey = 'bisCookieConsentAccepted';

  if (!banner || !acceptButton) {
    return;
  }

  if (window.localStorage.getItem(storageKey) === '1') {
    banner.hidden = true;
    return;
  }

  banner.hidden = false;

  acceptButton.addEventListener('click', () => {
    window.localStorage.setItem(storageKey, '1');
    banner.hidden = true;
  });
}

// Callback Modal Functionality
function initCallbackModal() {
  const callbackButtons = document.querySelectorAll('.callback-btn');
  const callbackBtnMobile = document.querySelector('.callback-btn-mobile');
  const callbackOverlay = document.getElementById('callbackOverlay');
  const callbackClose = document.getElementById('callbackClose');
  const callbackForm = document.getElementById('callbackForm');

  if ((callbackButtons.length === 0 && !callbackBtnMobile) || !callbackOverlay) return;

  // Обработчик для всех кнопок с обратным звонком
  if (callbackButtons.length) {
    callbackButtons.forEach(btn => btn.addEventListener('click', () => {
      callbackOverlay.classList.add('active');
      closeMenuDrawer();
    }));
  }

  // Обработчик для мобильной кнопки
  if (callbackBtnMobile) {
    callbackBtnMobile.addEventListener('click', () => {
      callbackOverlay.classList.add('active');
      closeMenuDrawer();
    });
  }

  if (callbackClose) {
    callbackClose.addEventListener('click', () => {
      closeCallbackModal();
    });
  }

  callbackOverlay.addEventListener('click', (e) => {
    if (e.target === callbackOverlay) {
      closeCallbackModal();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && callbackOverlay.classList.contains('active')) {
      closeCallbackModal();
    }
  });

  if (callbackForm) {
    callbackForm.addEventListener('submit', (e) => {
      e.preventDefault();

      const formData = {
        name: callbackForm.querySelector('#callbackName').value,
        phone: callbackForm.querySelector('#callbackPhone').value,
        message: callbackForm.querySelector('#callbackMessage').value,
        type: 'callback'
      };

      if (validateFormFields(callbackForm) && validateForm(formData)) {
        submitCallbackForm(formData, callbackForm);
      }
    });

    // Добавляем валидацию полей
    const inputs = callbackForm.querySelectorAll('input, textarea');
    inputs.forEach(input => {
      input.addEventListener('blur', () => validateField(input));
      input.addEventListener('input', () => {
        if (input.classList.contains('error')) validateField(input);
      });
    });
  }

  function closeCallbackModal() {
    callbackOverlay.classList.remove('active');
    if (callbackForm) {
      resetFormState(callbackForm, { clearErrors: true });
    }
  }
}

function submitAjaxForm(form, action, extraData = {}, options = {}) {
  if (!validateHCaptcha(form)) {
    return;
  }

  const submitBtn = form.querySelector('button[type="submit"]');
  const originalText = submitBtn ? submitBtn.textContent : '';
  const formData = new FormData(form);

  formData.append('action', action);
  Object.entries(extraData).forEach(([key, value]) => {
    formData.append(key, value);
  });
  appendLocationToFormData(formData);

  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.textContent = 'Отправка...';
    submitBtn.style.opacity = '0.6';
  }

  fetch(bisAjaxUrl, {
    method: 'POST',
    body: formData
  })
    .then(response => response.json())
    .then((data) => {
      if (!data.success) {
        throw new Error(data.data?.message || 'Ошибка отправки. Попробуйте позже.');
      }

      if (submitBtn) {
        submitBtn.textContent = '✓ Отправлено!';
        submitBtn.style.background = '#10b981';
      }

      clearHCaptchaError(form);

      if (typeof options.onSuccess === 'function') {
        options.onSuccess(data);
      } else {
        resetFormState(form);
        clearHCaptchaError(form);
      }

      showNotification(options.successMessage || 'Спасибо! Ваша заявка отправлена.', 'success');
    })
    .catch((error) => {
      showNotification(error.message || 'Ошибка отправки. Попробуйте позже.', 'error');
      resetHCaptcha(form);
    })
    .finally(() => {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        submitBtn.style.background = '';
        submitBtn.style.opacity = '';
      }
    });
}

// Функция отправки формы обратного звонка
function submitCallbackForm(data, form) {
  submitAjaxForm(form, 'bis_submit_general_request', {
    request_type: data.type || 'callback'
  }, {
    successMessage: 'Спасибо! Мы перезвоним вам в течение 15 минут.',
    onSuccess: () => {
      resetFormState(form);
      const overlay = document.getElementById('callbackOverlay');
      if (overlay) {
        overlay.classList.remove('active');
      }
    }
  });
}

// Валидация формы
function initFormValidation() {
  const forms = document.querySelectorAll('#contactForm, #orderForm');

  forms.forEach(form => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      const formData = {
        name: form.querySelector('[name="name"]').value,
        phone: form.querySelector('[name="phone"]').value,
        message: form.querySelector('[name="message"]').value,
        service: form.querySelector('#orderService')?.value || '',
        isOrder: form.id === 'orderForm'
      };

      if (validateFormFields(form) && validateForm(formData)) {
        submitForm(formData, form);
      }
    });

    const inputs = form.querySelectorAll('input:not([readonly]), textarea');
    inputs.forEach(input => {
      input.addEventListener('blur', () => validateField(input));
      input.addEventListener('input', () => {
        if (input.classList.contains('error')) validateField(input);
      });
    });
  });
}

// Валидация отдельного поля
function validateField(field) {
  const value = typeof field.value === 'string' ? field.value.trim() : '';
  let isValid = true;

  if (field.type === 'checkbox' && field.hasAttribute('required') && !field.checked) {
    isValid = false;
    showError(field, 'Необходимо подтвердить согласие');
  } else if (field.hasAttribute('required') && !value) {
    isValid = false;
    showError(field, 'Это поле обязательно для заполнения');
  } else if (field.type === 'tel' && value) {
    if (!isValidRussianPhone(value)) {
      isValid = false;
      showError(field, 'Введите корректный номер телефона');
    } else {
      clearError(field);
    }
  } else {
    clearError(field);
  }

  return isValid;
}

// Показать ошибку
function validateFormFields(form) {
  if (!form) return true;

  let isValid = true;

  form.querySelectorAll('input:not([readonly]), textarea, select').forEach((field) => {
    if (!validateField(field)) {
      isValid = false;
    }
  });

  return isValid;
}

function showError(field, message) {
  field.classList.add('error');
  field.style.borderColor = '#ef4444';

  let errorElement = field.parentElement.querySelector('.error-message');
  if (!errorElement) {
    errorElement = document.createElement('span');
    errorElement.className = 'error-message';
    errorElement.style.color = '#ef4444';
    errorElement.style.fontSize = '13px';
    errorElement.style.marginTop = '4px';
    errorElement.style.display = 'block';
    field.parentElement.appendChild(errorElement);
  }
  errorElement.textContent = message;
}

// Очистить ошибку
function clearError(field) {
  field.classList.remove('error');
  field.style.borderColor = '';

  const errorElement = field.parentElement.querySelector('.error-message');
  if (errorElement) errorElement.remove();
}

// Валидация всей формы
function validateForm(data) {
  let isValid = true;

  if (!data.name) {
    isValid = false;
  }

  if (!data.phone) {
    isValid = false;
  } else if (!isValidRussianPhone(data.phone)) {
    isValid = false;
  }

  if (!data.message && !data.isOrder && data.type !== 'callback') {
    isValid = false;
  }

  return isValid;
}

// Отправка формы
function submitForm(data, form) {
  submitAjaxForm(form, 'bis_submit_general_request', {
    request_type: data.isOrder ? 'order' : 'contact'
  }, {
    successMessage: 'Спасибо! Ваша заявка отправлена. Мы свяжемся с вами в ближайшее время.',
    onSuccess: () => {
      resetFormState(form);

      if (form.id === 'orderForm') {
        closePopup();
      }
    }
  });
}

// Уведомления
function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  notification.className = 'notification';
  notification.textContent = message;

  Object.assign(notification.style, {
    position: 'fixed',
    top: '32px',
    right: '32px',
    padding: '16px 24px',
    background: type === 'success' ? '#10b981' : '#2563eb',
    color: 'white',
    borderRadius: '12px',
    boxShadow: '0 8px 24px rgba(0, 0, 0, 0.15)',
    zIndex: '10000',
    maxWidth: '400px',
    animation: 'slideIn 0.3s ease-out',
    fontWeight: '500'
  });

  document.body.appendChild(notification);

  setTimeout(() => {
    notification.style.animation = 'slideOut 0.3s ease-out';
    setTimeout(() => notification.remove(), 300);
  }, 5000);
}

// Плавная прокрутка

function initPopupForm() {
  const orderButtons = document.querySelectorAll('.order-btn');
  const popupOverlay = document.getElementById('popupOverlay');
  const popupClose = document.getElementById('popupClose');
  const orderServiceInput = document.getElementById('orderService');

  if (!popupOverlay || !orderServiceInput) return;

  orderButtons.forEach(button => {
    button.addEventListener('click', (event) => {
      event.preventDefault();
      const serviceName = button.getAttribute('data-service') || button.textContent.trim();
      orderServiceInput.value = serviceName;
      popupOverlay.classList.add('active');
      document.body.style.overflow = 'hidden';
    });
  });

  if (popupClose) popupClose.addEventListener('click', closePopup);

  popupOverlay.addEventListener('click', (e) => {
    if (e.target === popupOverlay) closePopup();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && popupOverlay.classList.contains('active')) {
      closePopup();
    }
  });
}

function closePopup() {
  const popupOverlay = document.getElementById('popupOverlay');
  if (popupOverlay) {
    popupOverlay.classList.remove('active');
    document.body.style.overflow = '';
    const orderForm = document.getElementById('orderForm');
    if (orderForm) {
      resetFormState(orderForm, { clearErrors: true });
    }
  }
}

// Добавление CSS анимаций через JavaScript
const style = document.createElement('style');
style.textContent = `
  @keyframes slideIn {
    from {
      transform: translateX(400px);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }

  @keyframes slideOut {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(400px);
      opacity: 0;
    }
  }
`;
document.head.appendChild(style);

// Estimate Modal Functionality
function initEstimateModal() {
  const estimateBtns = document.querySelectorAll('.open-estimate-modal');
  const estimateOverlay = document.getElementById('estimateOverlay');
  const estimateClose = document.getElementById('estimateClose');
  const estimateForm = document.getElementById('estimateForm');
  const estimatePhone = document.getElementById('estimatePhone');
  const ANIMATION_DURATION = 450;
  let closeTimeout;

  if (!estimateOverlay) return;

  estimateBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      openEstimateModal();
    });
  });

  if (estimateClose) {
    estimateClose.addEventListener('click', (event) => {
      event.preventDefault();
      closeEstimateModal();
    });
  }

  estimateOverlay.addEventListener('click', (e) => {
    if (e.target === estimateOverlay || e.target.closest('#estimateClose')) {
      closeEstimateModal();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && estimateOverlay.classList.contains('active')) {
      closeEstimateModal();
    }
  });

  if (estimatePhone) {
    attachPhoneMask(estimatePhone);
  }

  if (estimateForm) {
    estimateForm.addEventListener('submit', (e) => {
      e.preventDefault();

      if (!validateFormFields(estimateForm) || !validateHCaptcha(estimateForm)) {
        return;
      }

      const formData = new FormData(estimateForm);
      formData.append('action', 'bis_submit_estimate');
      appendLocationToFormData(formData);

      const submitBtn = estimateForm.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;

      submitBtn.disabled = true;
      submitBtn.textContent = 'Отправка...';

      fetch(bisAjaxUrl, {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            submitBtn.textContent = '✓ Отправлено!';
            submitBtn.style.background = '#10b981';
            clearHCaptchaError(estimateForm);

            setTimeout(() => {
              closeEstimateModal({ resetForm: true });
              submitBtn.disabled = false;
              submitBtn.textContent = originalText;
              submitBtn.style.background = '';
              showNotification('Спасибо! Мы свяжемся с вами в течение 2 дней.', 'success');
            }, 1500);
          } else {
            showNotification(data.data?.message || 'Ошибка отправки. Попробуйте позже.', 'error');
            resetHCaptcha(estimateForm);
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
          }
        })
        .catch(error => {
          console.error('Error:', error);
          resetHCaptcha(estimateForm);
          showNotification(error.message || 'Ошибка отправки. Попробуйте позже.', 'error');
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        });
    });
  }

  function openEstimateModal() {
    clearTimeout(closeTimeout);
    estimateOverlay.classList.remove('closing');
    estimateOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeEstimateModal({ resetForm = true } = {}) {
    if (!estimateOverlay.classList.contains('active')) return;
    estimateOverlay.classList.add('closing');
    clearTimeout(closeTimeout);
    closeTimeout = setTimeout(() => {
      estimateOverlay.classList.remove('active', 'closing');
      document.body.style.overflow = '';
      if (estimateForm && resetForm) {
        resetFormState(estimateForm);
      }
    }, ANIMATION_DURATION);
  }
}

