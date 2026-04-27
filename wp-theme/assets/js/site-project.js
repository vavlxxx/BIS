function initProjectGallery() {
  const gallery = document.querySelector('[data-project-gallery]');
  if (!gallery) return;

  const slides = Array.from(gallery.querySelectorAll('[data-gallery-slide]'));
  if (slides.length === 0) return;

  const lightbox = document.getElementById('projectLightbox');
  if (!lightbox) return;

  const lightboxImage = lightbox.querySelector('[data-lightbox-image]');
  const lightboxCaption = lightbox.querySelector('[data-lightbox-caption]');
  const lightboxPrev = lightbox.querySelector('[data-lightbox-prev]');
  const lightboxNext = lightbox.querySelector('[data-lightbox-next]');
  const closeButtons = lightbox.querySelectorAll('[data-lightbox-close]');

  let lightboxIndex = 0;

  const normalizeIndex = (index) => {
    const total = slides.length;
    if (!total) return 0;
    return ((index % total) + total) % total;
  };

  const updateLightbox = () => {
    const slide = slides[lightboxIndex];
    const src = slide ? slide.dataset.full || slide.querySelector('img')?.src : '';
    if (lightboxImage) {
      lightboxImage.src = src || '';
      lightboxImage.alt = slide ? slide.getAttribute('aria-label') || '' : '';
    }
    if (lightboxCaption) {
      lightboxCaption.textContent = `${lightboxIndex + 1} / ${slides.length}`;
    }
  };

  const openLightbox = (index) => {
    lightboxIndex = normalizeIndex(index);
    updateLightbox();
    lightbox.classList.add('active');
    lightbox.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  };

  const closeLightbox = () => {
    lightbox.classList.remove('active');
    lightbox.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if (lightboxImage) {
      lightboxImage.src = '';
    }
  };

  slides.forEach((slide, index) => {
    slide.addEventListener('click', () => openLightbox(index));
  });

  if (lightboxPrev) {
    lightboxPrev.addEventListener('click', () => {
      lightboxIndex = normalizeIndex(lightboxIndex - 1);
      updateLightbox();
    });
  }

  if (lightboxNext) {
    lightboxNext.addEventListener('click', () => {
      lightboxIndex = normalizeIndex(lightboxIndex + 1);
      updateLightbox();
    });
  }

  closeButtons.forEach((button) => {
    button.addEventListener('click', closeLightbox);
  });

  lightbox.addEventListener('click', (event) => {
    if (event.target === lightbox) {
      closeLightbox();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && lightbox.classList.contains('active')) {
      closeLightbox();
    }
  });
}

function initProjectConsultationForm() {
  const form = document.getElementById('projectConsultationForm');
  if (!form) return;

  form.addEventListener('submit', (event) => {
    event.preventDefault();

    if (!validateFormFields(form)) {
      return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.textContent : '';

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Отправка...';
    }

    const formData = new FormData(form);
    formData.append('action', 'bis_submit_project_consultation');
    if (typeof window.bisAppendLocationToFormData === 'function') {
      window.bisAppendLocationToFormData(formData);
    }

    fetch(bisAjaxUrl, {
      method: 'POST',
      body: formData
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification('Спасибо! Мы свяжемся с вами в ближайшее время.', 'success');
          resetFormState(form);
        } else {
          showNotification('Ошибка отправки. Попробуйте позже.', 'error');
        }
      })
      .catch(() => {
        showNotification('Ошибка отправки. Попробуйте позже.', 'error');
      })
      .finally(() => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        }
      });
  });
}
