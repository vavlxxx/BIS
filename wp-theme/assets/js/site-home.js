// Revenue chart rendering
function initRevenueChart() {
  const chart = document.querySelector('.revenue-chart');
  if (!chart) return;

  const svg = chart.querySelector('.revenue-svg');
  const linePath = svg?.querySelector('.revenue-line');
  const areaPath = svg?.querySelector('.revenue-area');
  const pointsGroup = svg?.querySelector('.revenue-points');
  const labelsContainer = chart.querySelector('[data-revenue-labels]');
  const axisContainer = chart.querySelector('[data-revenue-axis]');
  const gridContainer = chart.querySelector('[data-revenue-grid]');
  const xAxisContainer = chart.querySelector('[data-revenue-xaxis]');

  let points = [];
  try {
    const dataAttr = chart.dataset.revenuePoints ? JSON.parse(chart.dataset.revenuePoints) : [];
    if (Array.isArray(dataAttr)) points = dataAttr;
  } catch (e) {
    points = [];
  }

  if (!points.length && typeof bisRevenueData !== 'undefined' && Array.isArray(bisRevenueData.points)) {
    points = bisRevenueData.points;
  }

  if (!svg || !linePath || !areaPath || !pointsGroup) return;

  const cleanPoints = points
    .map(point => ({
      label: point.label || '',
      value: parseFloat(point.value) || 0,
    }))
    .filter(point => point.label !== '');

  const width = 100;
  const height = 60;
  const paddingTop = 6;
  const paddingBottom = 6;
  const rawMax = cleanPoints.length ? Math.max(...cleanPoints.map(p => p.value), 0) : 0;

  const getNiceStep = (max) => {
    if (max <= 0) {
      return { step: 10, max: 60 };
    }
    const roughStep = max / 6;
    const pow = Math.pow(10, Math.floor(Math.log10(roughStep)));
    const fraction = roughStep / pow;
    let niceFraction = 1;
    if (fraction <= 1) {
      niceFraction = 1;
    } else if (fraction <= 2) {
      niceFraction = 2;
    } else if (fraction <= 5) {
      niceFraction = 5;
    } else {
      niceFraction = 10;
    }
    const step = niceFraction * pow;
    const niceMax = Math.ceil(max / step) * step;
    return { step, max: niceMax };
  };

  const nice = getNiceStep(rawMax || 60);
  const maxValue = nice.max;
  const axisStep = nice.step;

  const DECIMAL_PRECISION = 2;
  const roundValue = (value, decimals = DECIMAL_PRECISION) => {
    const factor = Math.pow(10, decimals);
    return Math.round((value + Number.EPSILON) * factor) / factor;
  };

  const formatValue = (value, decimals = DECIMAL_PRECISION) => {
    const safeValue = Number.isFinite(value) ? value : 0;
    const rounded = roundValue(safeValue, decimals);
    let text = rounded.toFixed(decimals);
    text = text.replace(/\.?0+$/, '');
    return text.replace('.', ',');
  };

  const denom = cleanPoints.length > 1 ? (cleanPoints.length - 1) : 1;
  const coords = cleanPoints.map((point, index) => {
    const x = (index / denom) * width;
    const y = height - paddingBottom - (point.value / maxValue) * (height - paddingTop - paddingBottom);
    return { x, y, value: point.value, label: point.label };
  });

  if (axisContainer) {
    axisContainer.innerHTML = '';
    const totalSteps = Math.floor(maxValue / axisStep);
    for (let i = 0; i <= totalSteps; i++) {
      const value = axisStep * i;
      const y = height - paddingBottom - (value / maxValue) * (height - paddingTop - paddingBottom);
      const yPercent = (y / height) * 100;
      const label = document.createElement('div');
      label.className = 'revenue-axis-label';
      label.textContent = formatValue(value);
      label.style.top = `${yPercent}%`;
      axisContainer.appendChild(label);
    }
  }

  if (gridContainer) {
    gridContainer.innerHTML = '';
    const totalSteps = Math.floor(maxValue / axisStep);
    for (let i = 0; i <= totalSteps; i++) {
      const value = axisStep * i;
      const y = height - paddingBottom - (value / maxValue) * (height - paddingTop - paddingBottom);
      const yPercent = (y / height) * 100;
      const line = document.createElement('div');
      line.className = 'revenue-grid-line';
      line.style.top = `${yPercent}%`;
      gridContainer.appendChild(line);
    }
  }

  if (labelsContainer) {
    labelsContainer.innerHTML = '';
    coords.forEach((coord) => {
      const label = document.createElement('div');
      label.className = 'revenue-label';
      label.textContent = formatValue(coord.value);
      const xPercent = (coord.x / width) * 100;
      const yPercent = (coord.y / height) * 100;
      const clampedX = Math.min(96, Math.max(4, xPercent));
      const clampedY = Math.min(96, Math.max(6, yPercent));
      label.style.left = `${clampedX}%`;
      label.style.top = `${clampedY}%`;
      labelsContainer.appendChild(label);
    });
  }

  if (xAxisContainer) {
    xAxisContainer.innerHTML = '';
    coords.forEach((coord) => {
      const label = document.createElement('div');
      label.className = 'revenue-xlabel';
      label.textContent = coord.label;
      const xPercent = (coord.x / width) * 100;
      const clampedX = Math.min(96, Math.max(4, xPercent));
      label.style.left = `${clampedX}%`;
      xAxisContainer.appendChild(label);
    });
  }

  pointsGroup.innerHTML = '';

  if (!cleanPoints.length) {
    linePath.setAttribute('d', '');
    areaPath.setAttribute('d', '');
    if (labelsContainer) labelsContainer.innerHTML = '';
    if (xAxisContainer) xAxisContainer.innerHTML = '';
    return;
  }

  coords.forEach((coord) => {
    const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
    circle.setAttribute('class', 'revenue-dot');
    circle.setAttribute('cx', coord.x.toFixed(2));
    circle.setAttribute('cy', coord.y.toFixed(2));
    circle.setAttribute('r', '0.9');
    pointsGroup.appendChild(circle);
  });

  if (cleanPoints.length < 2) {
    const point = coords[0];
    const lineD = `M ${point.x.toFixed(2)} ${point.y.toFixed(2)}`;
    linePath.setAttribute('d', lineD);
    areaPath.setAttribute('d', '');
    return;
  }

  const smoothing = 0.2;

  function controlPoint(current, previous, next, reverse) {
    const p = previous || current;
    const n = next || current;
    const o = {
      length: Math.hypot(n.x - p.x, n.y - p.y) * smoothing,
      angle: Math.atan2(n.y - p.y, n.x - p.x),
    };
    const angle = o.angle + (reverse ? Math.PI : 0);
    return {
      x: current.x + Math.cos(angle) * o.length,
      y: current.y + Math.sin(angle) * o.length,
    };
  }

  const lineD = coords.reduce((path, point, i, arr) => {
    if (i === 0) {
      return `M ${point.x.toFixed(2)} ${point.y.toFixed(2)}`;
    }
    const cp1 = controlPoint(arr[i - 1], arr[i - 2], point, false);
    const cp2 = controlPoint(point, arr[i - 1], arr[i + 1], true);
    return `${path} C ${cp1.x.toFixed(2)} ${cp1.y.toFixed(2)} ${cp2.x.toFixed(2)} ${cp2.y.toFixed(2)} ${point.x.toFixed(2)} ${point.y.toFixed(2)}`;
  }, '');

  const areaStart = `M ${coords[0].x.toFixed(2)} ${height - paddingBottom}`;
  const areaCurve = coords.reduce((path, point, i, arr) => {
    if (i === 0) {
      return `${path} L ${point.x.toFixed(2)} ${point.y.toFixed(2)}`;
    }
    const cp1 = controlPoint(arr[i - 1], arr[i - 2], point, false);
    const cp2 = controlPoint(point, arr[i - 1], arr[i + 1], true);
    return `${path} C ${cp1.x.toFixed(2)} ${cp1.y.toFixed(2)} ${cp2.x.toFixed(2)} ${cp2.y.toFixed(2)} ${point.x.toFixed(2)} ${point.y.toFixed(2)}`;
  }, areaStart);
  const areaD = `${areaCurve} L ${coords[coords.length - 1].x.toFixed(2)} ${height - paddingBottom} Z`;

  linePath.setAttribute('d', lineD);
  areaPath.setAttribute('d', areaD);
  pointsGroup.innerHTML = '';
}

function initCardSlider({
  sectionSelector,
  trackSelector,
  cardSelector,
  navSelector,
  desktopEnabled = true
}) {
  const section = document.querySelector(sectionSelector);
  if (!section) return;

  const track = section.querySelector(trackSelector);
  const nav = section.querySelector(navSelector);
  const dotsContainer = nav?.querySelector('.slider-dots');

  if (!track) return;

  const cards = Array.from(track.querySelectorAll(cardSelector));
  if (cards.length === 0) {
    cleanupCardSlider(track, section, nav, dotsContainer);
    return;
  }

  const isMobile = window.innerWidth <= CARD_SLIDER_MOBILE_BREAKPOINT;
  const isWideDesktop = window.innerWidth >= CARD_SLIDER_DESKTOP_BREAKPOINT;
  const isDesktopMode = desktopEnabled && isWideDesktop;
  const isEnabled = isMobile || isDesktopMode;
  const sliderMode = isDesktopMode ? 'desktop' : 'mobile';
  const visibleSlides = isDesktopMode ? 3 : 1;
  const maxStartIndex = Math.max(0, cards.length - visibleSlides);
  const slidePositions = isDesktopMode
    ? Array.from({ length: Math.ceil(cards.length / visibleSlides) }, (_, index) => Math.min(index * visibleSlides, maxStartIndex))
    : cards.map((_, index) => index);
  const maxSlideIndex = Math.max(0, slidePositions.length - 1);

  if (!isEnabled) {
    cleanupCardSlider(track, section, nav, dotsContainer);
    return;
  }

  if (track.dataset.sliderInitialized === 'true' && track.dataset.sliderMode === sliderMode && track._sliderState?.refresh) {
    track._sliderState.refresh();
    return;
  }

  cleanupCardSlider(track, section, nav, dotsContainer);

  section.classList.add('slider-enabled');
  track.dataset.sliderInitialized = 'true';
  track.dataset.sliderMode = sliderMode;

  const prevBtn = nav?.querySelector('.slider-prev') || null;
  const nextBtn = nav?.querySelector('.slider-next') || null;
  let currentSlide = Math.min(Number(track.dataset.currentSlide) || 0, maxSlideIndex);
  let dots = [];

  const clampIndex = (index) => Math.max(0, Math.min(index, maxSlideIndex));
  const getCardIndexForSlide = (index) => slidePositions[clampIndex(index)] ?? 0;

  const getTargetLeft = (index) => {
    const targetCard = cards[getCardIndexForSlide(index)];
    if (!targetCard) return 0;

    const maxScrollLeft = Math.max(0, track.scrollWidth - track.clientWidth);

    if (sliderMode === 'desktop') {
      return Math.max(0, Math.min(targetCard.offsetLeft, maxScrollLeft));
    }

    const offset = targetCard.offsetLeft - (track.clientWidth - targetCard.offsetWidth) / 2;
    return Math.max(0, Math.min(Math.round(offset), maxScrollLeft));
  };

  const updateNavigation = () => {
    if (prevBtn) prevBtn.disabled = currentSlide === 0;
    if (nextBtn) nextBtn.disabled = currentSlide === maxSlideIndex;
    dots.forEach((dot, index) => dot.classList.toggle('active', index === currentSlide));
    nav?.classList.toggle('is-visible', maxSlideIndex > 0);
  };

  const goToSlide = (slideIndex, behavior = 'smooth') => {
    currentSlide = clampIndex(slideIndex);
    track.dataset.currentSlide = String(currentSlide);

    const targetLeft = getTargetLeft(currentSlide);
    if (track.scrollTo) {
      track.scrollTo({ left: targetLeft, behavior });
    } else {
      track.scrollLeft = targetLeft;
    }

    updateNavigation();
  };

  const buildDots = () => {
    if (!dotsContainer) return;

    dotsContainer.innerHTML = '';
    dots = [];

    for (let index = 0; index <= maxSlideIndex; index += 1) {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.className = 'slider-dot';
      dot.setAttribute('aria-label', `Перейти к слайду ${index + 1}`);
      dot.addEventListener('click', () => goToSlide(index));
      dotsContainer.appendChild(dot);
      dots.push(dot);
    }
  };

  const getClosestSlideIndex = () => {
    let closestIndex = 0;
    let minDiff = Number.POSITIVE_INFINITY;

    for (let index = 0; index <= maxSlideIndex; index += 1) {
      const diff = Math.abs(getTargetLeft(index) - track.scrollLeft);
      if (diff < minDiff) {
        minDiff = diff;
        closestIndex = index;
      }
    }

    return closestIndex;
  };

  const handleScroll = () => {
    if (track._sliderState?.scrollEndTimer) {
      clearTimeout(track._sliderState.scrollEndTimer);
    }

    track._sliderState.scrollEndTimer = setTimeout(() => {
      const newIndex = getClosestSlideIndex();
      if (newIndex !== currentSlide) {
        currentSlide = newIndex;
        track.dataset.currentSlide = String(currentSlide);
        updateNavigation();
      }
    }, 120);
  };

  const goPrev = () => goToSlide(currentSlide - 1);
  const goNext = () => goToSlide(currentSlide + 1);

  buildDots();

  if (prevBtn) prevBtn.addEventListener('click', goPrev);
  if (nextBtn) nextBtn.addEventListener('click', goNext);

  track.addEventListener('scroll', handleScroll, { passive: true });

  let resizeObserver = null;
  const refresh = () => {
    currentSlide = Math.min(Number(track.dataset.currentSlide) || currentSlide, maxSlideIndex);
    goToSlide(currentSlide, 'auto');
  };

  if (window.ResizeObserver) {
    resizeObserver = new ResizeObserver(() => {
      refresh();
    });
    resizeObserver.observe(track);
  }

  track._sliderState = {
    currentSlide,
    goPrev,
    goNext,
    handleScroll,
    prevBtn,
    nextBtn,
    refresh,
    resizeObserver,
    scrollEndTimer: null
  };

  refresh();
}

// Slider for Services
function initServicesSlider() {
  initCardSlider({
    sectionSelector: '.services',
    trackSelector: '.services-grid',
    cardSelector: '.service-card',
    navSelector: '.services-slider-nav'
  });
}

function initRelatedServicesSlider() {
  initCardSlider({
    sectionSelector: '.services-catalog--related',
    trackSelector: '[data-related-services-track]',
    cardSelector: '.service-card',
    navSelector: '.services-slider-nav'
  });
}

function initExperienceSlider() {
  initCardSlider({
    sectionSelector: '.experience',
    trackSelector: '.experience-grid',
    cardSelector: '.experience-card',
    navSelector: '.experience-slider-nav'
  });
}

// Slider for gratitude letters
function initGratitudeSlider() {
  const galleries = document.querySelectorAll('[data-gratitude-gallery]');
  if (!galleries.length) return;

  galleries.forEach((gallery) => {
    const track = gallery.querySelector('[data-gratitude-track]');
    const slides = Array.from(gallery.querySelectorAll('[data-gratitude-slide]'));
    const prevBtn = gallery.querySelector('[data-gratitude-prev]');
    const nextBtn = gallery.querySelector('[data-gratitude-next]');
    const dotsContainer = gallery.parentElement ? gallery.parentElement.querySelector('[data-gratitude-dots]') : null;

    if (!track || slides.length === 0) {
      if (prevBtn) prevBtn.disabled = true;
      if (nextBtn) nextBtn.disabled = true;
      return;
    }

    let activeIndex = 0;
    let slideStep = 0;

    const getGap = () => {
      const styles = window.getComputedStyle(track);
      const rawGap = styles.columnGap || styles.gap || '0';
      const parsedGap = parseFloat(rawGap);
      return Number.isNaN(parsedGap) ? 0 : parsedGap;
    };

    const computeSlideStep = () => {
      const first = slides[0];
      if (!first) return 0;
      const rect = first.getBoundingClientRect();
      return rect.width + getGap();
    };

    const clampIndex = (index) => {
      const total = slides.length;
      if (!total) return 0;
      return Math.max(0, Math.min(index, total - 1));
    };

    const updateNavigation = () => {
      if (prevBtn) prevBtn.disabled = activeIndex === 0;
      if (nextBtn) nextBtn.disabled = activeIndex === slides.length - 1;
    };

    const updateDots = () => {
      if (!dotsContainer) return;
      const dots = dotsContainer.querySelectorAll('.slider-dot');
      dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === activeIndex);
      });
    };

    const scrollToIndex = (index) => {
      if (!slideStep || Number.isNaN(slideStep)) {
        slideStep = computeSlideStep();
      }
      if (!slideStep || Number.isNaN(slideStep)) {
        return;
      }
      activeIndex = clampIndex(index);
      track.scrollTo({
        left: activeIndex * slideStep,
        behavior: 'smooth'
      });
      updateDots();
      updateNavigation();
    };

    if (prevBtn) {
      prevBtn.addEventListener('click', () => scrollToIndex(activeIndex - 1));
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', () => scrollToIndex(activeIndex + 1));
    }

    if (dotsContainer) {
      dotsContainer.innerHTML = '';
      slides.forEach((_, index) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'slider-dot';
        dot.addEventListener('click', () => scrollToIndex(index));
        dotsContainer.appendChild(dot);
      });
    }

    track.addEventListener('scroll', () => {
      if (!slideStep || Number.isNaN(slideStep)) {
        slideStep = computeSlideStep();
      }
      if (!slideStep || Number.isNaN(slideStep)) {
        return;
      }
      const index = Math.round(track.scrollLeft / slideStep);
      if (index !== activeIndex) {
        activeIndex = clampIndex(index);
        updateDots();
        updateNavigation();
      }
    });

    window.addEventListener('resize', () => {
      if (!track.isConnected) return;
      slideStep = computeSlideStep();
      scrollToIndex(activeIndex);
    });

    slideStep = computeSlideStep();
    updateDots();
    updateNavigation();
  });
}

// Modal for gratitude cards
function initGratitudeModal() {
  const modal = document.getElementById('gratitudeModal');
  const cards = Array.from(document.querySelectorAll('.gratitude-card.has-image'));

  if (!modal || cards.length === 0) {
    return;
  }

  const modalImage = modal.querySelector('[data-gratitude-lightbox-image]');
  const modalImageWrap = modal.querySelector('.gratitude-modal-image');
  const modalCaption = modal.querySelector('[data-gratitude-lightbox-caption]');
  const prevButton = modal.querySelector('[data-gratitude-lightbox-prev]');
  const nextButton = modal.querySelector('[data-gratitude-lightbox-next]');
  const closeButtons = modal.querySelectorAll('[data-close-gratitude]');
  const modalClose = modal.querySelector('.gratitude-modal-close');
  let previouslyFocused = null;
  let currentIndex = 0;

  const normalizeIndex = (index) => {
    const total = cards.length;
    if (!total) return 0;
    return ((index % total) + total) % total;
  };

  const updateModal = () => {
    const card = cards[currentIndex];
    const image = card ? card.dataset.image : '';
    if (modalImage) {
      modalImage.src = image || '';
      modalImage.alt = card?.dataset.title || 'Благодарственное письмо';
    }
    if (modalCaption) {
      modalCaption.textContent = `${currentIndex + 1} / ${cards.length}`;
    }
  };

  const openModal = (card) => {
    const index = cards.indexOf(card);
    if (index === -1 || !modalImage) return;

    previouslyFocused = document.activeElement;
    currentIndex = normalizeIndex(index);
    updateModal();

    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    if (modalClose) {
      modalClose.focus();
    }
  };

  const closeModal = () => {
    modal.classList.remove('active');
    modal.setAttribute('aria-hidden', 'true');
    if (modalImage) {
      modalImage.src = '';
    }
    document.body.style.overflow = '';
    if (previouslyFocused) {
      previouslyFocused.focus();
    }
  };

  cards.forEach((card) => {
    card.addEventListener('click', () => openModal(card));
    card.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        openModal(card);
      }
    });
  });

  if (prevButton) {
    prevButton.addEventListener('click', () => {
      currentIndex = normalizeIndex(currentIndex - 1);
      updateModal();
    });
  }

  if (nextButton) {
    nextButton.addEventListener('click', () => {
      currentIndex = normalizeIndex(currentIndex + 1);
      updateModal();
    });
  }

  closeButtons.forEach((btn) => {
    btn.addEventListener('click', closeModal);
  });

  modal.addEventListener('click', (event) => {
    if (event.target === modal) {
      closeModal();
    }
  });

  if (modalImageWrap) {
    modalImageWrap.addEventListener('click', (event) => {
      if (event.target === modalImageWrap) {
        closeModal();
      }
    });
  }

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('active')) {
      closeModal();
    }
  });
}

// Слайдер для оборудования
function initEquipmentSlider() {
  initCardSlider({
    sectionSelector: '.equipment-section',
    trackSelector: '.equipment-grid',
    cardSelector: '.equipment-card',
    navSelector: '.equipment-slider-nav'
  });
}

// Слайдер для объектов (мобильный)
function initObjectsSlider() {
  const objectsGrid = document.querySelector('.objects-grid');
  if (!objectsGrid) return;

  const objectCards = objectsGrid.querySelectorAll('.object-card');
  const prevBtn = document.querySelector('.objects-slider-nav .slider-prev');
  const nextBtn = document.querySelector('.objects-slider-nav .slider-next');
  const dotsContainer = document.querySelector('.objects-slider-nav .slider-dots');

  if (objectCards.length === 0) return;

  const resetButton = (selector) => {
    const btn = document.querySelector(selector);
    if (btn) {
      const clone = btn.cloneNode(true);
      btn.parentNode.replaceChild(clone, btn);
    }
  };

  const isMobile = window.innerWidth <= 768;

  if (!isMobile) {
    if (objectsGrid.dataset.sliderInitialized === 'true') {
      if (objectsGrid._scrollHandler) {
        objectsGrid.removeEventListener('scroll', objectsGrid._scrollHandler);
        objectsGrid._scrollHandler = null;
      }
      if (objectsGrid._resizeObserver) {
        objectsGrid._resizeObserver.disconnect();
        objectsGrid._resizeObserver = null;
      }
      if (objectsGrid._scrollEndTimer) {
        clearTimeout(objectsGrid._scrollEndTimer);
        objectsGrid._scrollEndTimer = null;
      }
    }

    objectsGrid.removeAttribute('data-slider-initialized');
    if (dotsContainer) dotsContainer.innerHTML = '';
    resetButton('.objects-slider-nav .slider-prev');
    resetButton('.objects-slider-nav .slider-next');
    return;
  }

  if (objectsGrid.dataset.sliderInitialized === 'true') return;

  objectsGrid.dataset.sliderInitialized = 'true';

  let currentSlide = 0;
  const totalSlides = objectCards.length;

  if (dotsContainer) {
    dotsContainer.innerHTML = '';
    objectCards.forEach((_, index) => {
      const dot = document.createElement('div');
      dot.className = 'slider-dot';
      if (index === 0) dot.classList.add('active');
      dot.addEventListener('click', () => goToSlide(index));
      dotsContainer.appendChild(dot);
    });
  }

  const dots = dotsContainer ? dotsContainer.querySelectorAll('.slider-dot') : [];

  function updateNavigation() {
    if (prevBtn) prevBtn.disabled = currentSlide === 0;
    if (nextBtn) nextBtn.disabled = currentSlide === totalSlides - 1;
    dots.forEach((dot, index) => dot.classList.toggle('active', index === currentSlide));
  }

  function getTargetLeft(index) {
    const targetCard = objectCards[index];
    const maxScrollLeft = objectsGrid.scrollWidth - objectsGrid.clientWidth;
    const offset = targetCard.offsetLeft - (objectsGrid.clientWidth - targetCard.offsetWidth) / 2;
    return Math.max(0, Math.min(Math.round(offset), maxScrollLeft));
  }

  function goToSlide(slideIndex, behavior = 'smooth') {
    currentSlide = Math.max(0, Math.min(slideIndex, totalSlides - 1));
    if (objectsGrid.scrollTo) {
      objectsGrid.scrollTo({ left: getTargetLeft(currentSlide), behavior });
    } else {
      objectsGrid.scrollLeft = getTargetLeft(currentSlide);
    }
    updateNavigation();
  }

  const goPrev = () => {
    if (currentSlide > 0) goToSlide(currentSlide - 1);
  };

  const goNext = () => {
    if (currentSlide < totalSlides - 1) goToSlide(currentSlide + 1);
  };

  if (prevBtn) prevBtn.addEventListener('click', goPrev);
  if (nextBtn) nextBtn.addEventListener('click', goNext);

  const getClosestSlideIndex = () => {
    const center = objectsGrid.scrollLeft + objectsGrid.clientWidth / 2;
    let closestIndex = 0;
    let minDiff = Number.POSITIVE_INFINITY;

    objectCards.forEach((card, index) => {
      const cardCenter = card.offsetLeft + card.offsetWidth / 2;
      const diff = Math.abs(cardCenter - center);
      if (diff < minDiff) {
        minDiff = diff;
        closestIndex = index;
      }
    });

    return closestIndex;
  };

  const handleScroll = () => {
    objectsGrid._scrollEndTimer = setTimeout(() => {
      const newIndex = getClosestSlideIndex();
      if (newIndex !== currentSlide) {
        currentSlide = newIndex;
        updateNavigation();
      }
    }, 120);
  };

  objectsGrid.addEventListener('scroll', handleScroll, { passive: true });
  objectsGrid._scrollHandler = handleScroll;

  if (window.ResizeObserver) {
    const resizeObserver = new ResizeObserver(() => {
      if (window.innerWidth <= 768) {
        goToSlide(currentSlide, 'auto');
      }
    });
    resizeObserver.observe(objectsGrid);
    objectsGrid._resizeObserver = resizeObserver;
  }

  goToSlide(0, 'auto');
}

function initExperienceModal() {
  const cardSelector = '.experience-card[data-modal="1"], .all-case-card[data-modal="1"]';
  const cards = document.querySelectorAll(cardSelector);
  const modal = document.getElementById('experienceModal');
  if (!cards.length || !modal) return;

  const modalTitle = modal.querySelector('.experience-modal-title');
  const modalImage = modal.querySelector('.experience-modal-image');
  const modalMeta = modal.querySelector('.experience-modal-meta');
  const modalClose = document.getElementById('experienceModalClose');
  const discussButton = modal.querySelector('.experience-modal-cta');
  const pageButton = modal.querySelector('.experience-modal-link');
  const casesModal = document.getElementById('casesModal');

  const openModal = (card) => {
    let title = (card.querySelector('h3') || card.querySelector('h4'))?.innerHTML.trim() || 'Проект БИС — Баланс Инженерных Систем';

    // Ensure BIZ is styled in description if it comes from data attribute as plain text
    title = title.replace(/БИС/g, 'БИС');

    // Clean up nested spans if any
    title = title.replace(/<span class="bis-condensed"><span class="bis-condensed">БИС<\/span><\/span>/g, 'БИС');

    const image = card.dataset.image || '';
    const address = card.dataset.address || '';
    const area = card.dataset.area || '';
    const year = card.dataset.year || '';
    const featured = card.dataset.featured === '1';
    const link = card.dataset.link || '';

    modalTitle.innerHTML = title;
    modalMeta.innerHTML = '';

    const metaList = document.createElement('ul');
    metaList.className = 'experience-modal-meta__list';

    const addMetaRow = (label, value) => {
      if (!value) return;
      const row = document.createElement('li');
      const labelEl = document.createElement('span');
      labelEl.textContent = label;
      const valueEl = document.createElement('strong');
      valueEl.textContent = value;
      row.appendChild(labelEl);
      row.appendChild(valueEl);
      metaList.appendChild(row);
    };

    addMetaRow('Адрес', address);
    addMetaRow('Площадь', area ? `${area} м²` : '');
    addMetaRow('Год', year);

    if (featured) {
      const badge = document.createElement('div');
      badge.className = 'experience-modal-meta__badge';
      badge.textContent = 'Ключевой проект';
      modalMeta.appendChild(badge);
    }

    if (metaList.childElementCount) {
      modalMeta.appendChild(metaList);
    } else {
      const placeholder = document.createElement('p');
      placeholder.className = 'experience-modal-meta__placeholder';
      placeholder.textContent = 'Детали проекта уточняются';
      modalMeta.appendChild(placeholder);
    }

    if (image) {
      modalImage.style.backgroundImage = `url('${image}')`;
    } else {
      modalImage.style.backgroundImage = '';
    }

    if (pageButton) {
      if (link) {
        pageButton.href = link;
        pageButton.style.display = '';
      } else {
        pageButton.style.display = 'none';
      }
    }

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  };

  const closeModal = () => {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  };

  cards.forEach(card => {
    card.addEventListener('click', () => openModal(card));
  });

  document.querySelectorAll('.experience-more, .case-more').forEach(button => {
    button.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      const parentCard = button.closest(cardSelector);
      if (parentCard) {
        openModal(parentCard);
      }
    });
  });

  if (modalClose) {
    modalClose.addEventListener('click', closeModal);
  }

  modal.addEventListener('click', (event) => {
    if (event.target === modal) {
      closeModal();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('active')) {
      closeModal();
    }
  });

  if (discussButton) {
    discussButton.addEventListener('click', (event) => {
      event.preventDefault();
      closeModal();

      if (casesModal && casesModal.classList.contains('active')) {
        casesModal.classList.remove('active');
      }

      document.body.style.overflow = '';

      const contactSection = document.getElementById('contact');
      if (contactSection) {
        contactSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        const nameInput = contactSection.querySelector('#contactForm input[name="name"]');
        if (nameInput) {
          try {
            nameInput.focus({ preventScroll: true });
          } catch (error) {
            nameInput.focus();
          }
        }
      }
    });
  }
}

// Modal for Cases
function initCasesModal() {
  const showAllBtn = document.querySelector('.show-all-cases');
  const casesModal = document.getElementById('casesModal');
  const modalClose = document.getElementById('modalClose');

  if (!showAllBtn || !casesModal) return;

  showAllBtn.addEventListener('click', () => {
    casesModal.classList.add('active');
    document.body.style.overflow = 'hidden'; // Блокируем скролл страницы
  });

  modalClose.addEventListener('click', () => {
    closeCasesModal();
  });

  casesModal.addEventListener('click', (e) => {
    if (e.target === casesModal) {
      closeCasesModal();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && casesModal.classList.contains('active')) {
      closeCasesModal();
    }
  });

  function closeCasesModal() {
    casesModal.classList.remove('active');
    document.body.style.overflow = ''; // Восстанавливаем скролл
  }
};


// FAQ Functionality
function initFAQ() {
  const faqItems = document.querySelectorAll('.faq-item');

  faqItems.forEach(item => {
    const question = item.querySelector('.faq-question');

    question.addEventListener('click', () => {
      // Закрываем все остальные элементы
      faqItems.forEach(otherItem => {
        if (otherItem !== item && otherItem.classList.contains('active')) {
          otherItem.classList.remove('active');
        }
      });

      // Переключаем текущий элемент
      item.classList.toggle('active');
    });
  });
}

function initNewsTabs() {
  const tabs = document.querySelectorAll('.news-tab');
  const contents = document.querySelectorAll('.news-tab-content');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      const target = tab.dataset.newsTarget;

      tabs.forEach(t => {
        t.classList.remove('is-active');
      });
      tab.classList.add('is-active');

      contents.forEach(content => {
        if (content.dataset.newsContent === target) {
          content.style.display = 'block';
        } else {
          content.style.display = 'none';
        }
      });
    });
  });
}
