// Team Slider
function initTeamSlider() {
  const slider = document.querySelector('[data-team-slider]');
  if (!slider) return;

  const track = slider.querySelector('.team-track');
  const wrap = slider.querySelector('.team-track-wrap');
  const prevBtn = slider.querySelector('.team-slider__controls .team-prev');
  const nextBtn = slider.querySelector('.team-slider__controls .team-next');

  if (!track || !wrap) return;

  if (slider.dataset.teamSliderInitialized === 'true') {
    return;
  }

  const getSlides = () => Array.from(track.querySelectorAll('.team-slide:not(.is-clone)'));
  let originalSlides = getSlides();

  if (originalSlides.length === 0) return;

  // Создаем по одному клону для бесконечного цикла
  if (originalSlides.length > 1) {
    const firstClone = originalSlides[0].cloneNode(true);
    firstClone.classList.add('is-clone');
    const lastClone = originalSlides[originalSlides.length - 1].cloneNode(true);
    lastClone.classList.add('is-clone');
    track.appendChild(firstClone);
    track.insertBefore(lastClone, track.firstChild);
  }

  slider.dataset.teamSliderInitialized = 'true';
  const allSlides = Array.from(track.querySelectorAll('.team-slide'));
  const originalCount = originalSlides.length;

  let currentIndex = originalSlides.length > 1 ? 1 : 0;
  let slideWidth = 0;
  let isAnimating = false;
  let isDragging = false;
  let dragStartX = 0;
  let dragStartY = 0;
  let dragAxis = null;
  let dragStartTranslate = 0;
  let dragRaf = null;
  let dragPendingX = 0;
  let teamMediaReady = false;

  const hydrateSlidePhoto = (slide) => {
    if (!slide) return;

    const photoWrap = slide.querySelector('[data-team-photo]');
    const photoUrl = slide.dataset.slidePhoto || '';

    if (!photoWrap || !photoUrl || photoWrap.dataset.loaded === 'true') return;

    photoWrap.style.backgroundImage = `url("${photoUrl}")`;
    photoWrap.dataset.loaded = 'true';
  };

  const hydrateSlidesAround = (index) => {
    [index - 1, index, index + 1].forEach((targetIndex) => {
      const slide = allSlides[targetIndex];
      if (!slide) return;
      hydrateSlidePhoto(slide);
    });
  };

  const ensureTeamMediaReady = () => {
    if (teamMediaReady) return;

    teamMediaReady = true;
    hydrateSlidesAround(currentIndex);
  };

  const moveTo = (index, animate = true) => {
    currentIndex = index;
    if (teamMediaReady) {
      hydrateSlidesAround(currentIndex);
    }
    track.style.transition = animate ? 'transform 0.55s cubic-bezier(0.22, 0.61, 0.36, 1)' : 'none';
    track.style.transform = `translateX(-${slideWidth * currentIndex}px)`;
    isAnimating = animate;
  };

  const setSizes = () => {
    slideWidth = wrap.getBoundingClientRect().width;
    allSlides.forEach((slide) => {
      slide.style.width = `${slideWidth}px`;
    });
    track.style.width = `${slideWidth * allSlides.length}px`;
    moveTo(currentIndex, false);
  };

  const goNext = () => {
    if (originalSlides.length <= 1 || isAnimating) return;
    moveTo(currentIndex + 1, true);
  };

  const goPrev = () => {
    if (originalSlides.length <= 1 || isAnimating) return;
    moveTo(currentIndex - 1, true);
  };

  if (prevBtn) prevBtn.addEventListener('click', goPrev);
  if (nextBtn) nextBtn.addEventListener('click', goNext);

  slider.addEventListener('click', (event) => {
    const prev = event.target.closest('.team-prev');
    const next = event.target.closest('.team-next');
    if (prev) {
      event.preventDefault();
      goPrev();
      return;
    }
    if (next) {
      event.preventDefault();
      goNext();
    }
  });

  track.addEventListener('transitionend', (event) => {
    if (event.target !== track || event.propertyName !== 'transform') return;
    if (!isAnimating || originalSlides.length <= 1) return;
    isAnimating = false;

    // Если мы закончили анимацию на клонированном слайде, мгновенно перейдем к соответствующему оригинальному
    if (currentIndex === 0) {
      // Находимся на первом клоне, переходим к последнему оригинальному
      currentIndex = originalCount;
      track.style.transition = 'none';
      track.style.transform = `translateX(-${slideWidth * currentIndex}px)`;
    } else if (currentIndex === allSlides.length - 1) {
      // Находимся на последнем клоне, переходим к первому оригинальному
      currentIndex = 1;
      track.style.transition = 'none';
      track.style.transform = `translateX(-${slideWidth * currentIndex}px)`;
    }
  });

  const handlePointerDown = (event) => {
    if (originalSlides.length <= 1 || isAnimating) return;
    if (event.target.closest('.team-more') || event.target.closest('.team-nav')) return;
    isDragging = true;
    dragStartX = event.clientX;
    dragStartY = event.clientY;
    dragAxis = null;
    dragStartTranslate = -slideWidth * currentIndex;
    track.style.transition = 'none';
    wrap.setPointerCapture(event.pointerId);
  };

  const handlePointerMove = (event) => {
    if (!isDragging) return;
    const deltaX = event.clientX - dragStartX;
    const deltaY = event.clientY - dragStartY;

    if (!dragAxis) {
      if (Math.abs(deltaX) < 6 && Math.abs(deltaY) < 6) return;
      dragAxis = Math.abs(deltaX) > Math.abs(deltaY) ? 'x' : 'y';
    }

    if (dragAxis !== 'x') {
      isDragging = false;
      dragAxis = null;
      wrap.releasePointerCapture(event.pointerId);
      return;
    }

    dragPendingX = dragStartTranslate + deltaX;
    if (dragRaf) return;

    dragRaf = requestAnimationFrame(() => {
      track.style.transform = `translateX(${dragPendingX}px)`;
      dragRaf = null;
    });
  };

  const handlePointerUp = (event) => {
    if (!isDragging) return;
    isDragging = false;
    wrap.releasePointerCapture(event.pointerId);
    const delta = event.clientX - dragStartX;
    const threshold = slideWidth * 0.15;

    if (dragRaf) {
      cancelAnimationFrame(dragRaf);
      dragRaf = null;
    }

    if (dragAxis === 'x') {
      if (Math.abs(delta) > threshold) {
        delta < 0 ? goNext() : goPrev();
      } else {
        moveTo(currentIndex, true);
      }
    }

    dragAxis = null;
  };

  wrap.addEventListener('pointerdown', handlePointerDown);
  wrap.addEventListener('pointermove', handlePointerMove);
  wrap.addEventListener('pointerup', handlePointerUp);
  wrap.addEventListener('pointercancel', handlePointerUp);
  wrap.addEventListener('dragstart', (event) => event.preventDefault());

  window.addEventListener('resize', () => {
    setSizes();
  });

  setSizes();

  if (!('IntersectionObserver' in window)) {
    ensureTeamMediaReady();
    moveTo(currentIndex, false);
    return;
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;

      observer.disconnect();
      ensureTeamMediaReady();
      moveTo(currentIndex, false);
    });
  }, {
    rootMargin: '250px 0px',
    threshold: 0.1
  });

  observer.observe(slider);
}

function initTeamModal() {
  const modal = document.getElementById('teamModal');
  if (!modal) return;

  const nameEl = modal.querySelector('[data-team-modal-name]');
  const roleEl = modal.querySelector('[data-team-modal-role]');
  const sinceEl = modal.querySelector('[data-team-modal-since]');
  const textEl = modal.querySelector('[data-team-modal-text]');
  const imageEl = modal.querySelector('[data-team-modal-image]');

  const openModal = (slide) => {
    const name = slide.dataset.name || '';
    const role = slide.dataset.role || '';
    const since = slide.dataset.since || '';
    const modalPhoto = slide.dataset.modalPhoto || slide.dataset.slidePhoto || '';
    const detail = slide.querySelector('.team-slide__long');
    const summary = slide.querySelector('.team-story');
    const detailHtml = detail && detail.innerHTML.trim() ? detail.innerHTML : (summary ? summary.innerHTML : '');

    if (nameEl) nameEl.textContent = name;
    if (roleEl) roleEl.textContent = role;
    if (sinceEl) {
      if (since) {
        sinceEl.textContent = `В команде с ${since}`;
        sinceEl.style.display = '';
      } else {
        sinceEl.textContent = '';
        sinceEl.style.display = 'none';
      }
    }
    if (textEl) textEl.innerHTML = detailHtml;
    if (imageEl) {
      const imageWrap = imageEl.closest('.team-modal__image');
      if (modalPhoto) {
        imageEl.src = modalPhoto;
        imageEl.alt = name ? name : 'Фото сотрудника';
        if (imageWrap) imageWrap.style.display = '';
      } else {
        imageEl.removeAttribute('src');
        imageEl.alt = '';
        if (imageWrap) imageWrap.style.display = 'none';
      }
    }

    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  };

  const closeModal = () => {
    modal.classList.remove('active');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  };

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-team-more]');
    if (trigger) {
      const slide = trigger.closest('.team-slide');
      if (slide) {
        openModal(slide);
      }
    }

    if (event.target.closest('[data-team-close]')) {
      closeModal();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('active')) {
      closeModal();
    }
  });
}
