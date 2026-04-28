function initTypingEffect() {
  const typingText = document.querySelector('.typing-text');
  const cursor = document.querySelector('.cursor');

  if (!typingText) return;

  const textParts = [
    { text: 'Баланс ', isGradient: false },
    { text: 'Инженерных ', isGradient: true },
    { text: 'Систем', isGradient: false }
  ];

  // Установим ширину контейнера до начала анимации, чтобы избежать смещения
  const fullText = 'БИС — ' + textParts.map(part => part.text).join('');
  const tempSpan = document.createElement('span');
  tempSpan.style.visibility = 'hidden';
  tempSpan.style.position = 'absolute';
  tempSpan.style.whiteSpace = 'pre-wrap';
  tempSpan.style.font = getComputedStyle(typingText).font;
  tempSpan.textContent = fullText;
  document.body.appendChild(tempSpan);

  // Учитываем размер экрана при установке ширины
  const textWidth = tempSpan.offsetWidth;
  const screenWidth = window.innerWidth;

  // Для мобильных устройств используем меньшую ширину
  if (screenWidth <= 768) {
    // На мобильных устройствах ограничиваем ширину для лучшего отображения
    typingText.style.minWidth = '100%';
    typingText.style.display = 'inline-block';
    typingText.style.width = '100%';
  } else {
    // На десктопе устанавливаем рассчитанную ширину
    typingText.style.minWidth = (textWidth + 20) + 'px'; // Добавляем немного места для курсора
  }

  // Также устанавливаем максимальную ширину для предотвращения переполнения
  typingText.style.maxWidth = '100%';

  // Убедимся, что элемент занимает всю доступную ширину для корректного позиционирования курсора
  typingText.style.flex = '1 1 auto';

  document.body.removeChild(tempSpan);

  let partIndex = 0;
  let charIndex = 0;
  const typingSpeed = 70; // Скорость печати в мс
  const pauseAfterWord = 150; // Пауза после слова
  cursor.style.display = 'inline-block';
  function type() {
    if (partIndex < textParts.length) {
      const currentPart = textParts[partIndex];

      if (charIndex < currentPart.text.length) {
        const char = currentPart.text[charIndex];
        let currentSpan = typingText.querySelector(`[data-part="${partIndex}"]`);
        if (!currentSpan) {
          currentSpan = document.createElement('span');
          currentSpan.setAttribute('data-part', partIndex);
          if (currentPart.isGradient) currentSpan.className = 'gradient-text';
          typingText.appendChild(currentSpan);
        }
        currentSpan.textContent += char;

        // Перемещаем курсор после добавленного текста
        if (cursor && currentSpan.parentNode) {
          // Перемещаем курсор после текущего span
          currentSpan.parentNode.insertBefore(cursor, currentSpan.nextSibling);
        }

        charIndex++;
        setTimeout(type, typingSpeed);
      } else {
        partIndex++;
        charIndex = 0;
        setTimeout(type, pauseAfterWord);
      }
    } else {
      setTimeout(() => {
        if (cursor) {
          // Останавливаем анимацию курсора и делаем его менее заметным
          cursor.style.animation = 'none';
          cursor.style.opacity = '0';
        }
      }, 1000);
    }
  }
  setTimeout(type, 500);
}

function openMenuDrawer() {
  const navDrawer = document.getElementById('navDrawer');
  const menuToggle = document.getElementById('menuToggle');

  if (navDrawer) {
    navDrawer.classList.add('active');
    navDrawer.setAttribute('aria-hidden', 'false');
  }

  if (menuToggle) {
    menuToggle.classList.add('active');
  }

  document.body.classList.add('nav-open');
}

function closeMenuDrawer() {
  const navDrawer = document.getElementById('navDrawer');
  const menuToggle = document.getElementById('menuToggle');

  if (navDrawer) {
    navDrawer.classList.remove('active');
    navDrawer.setAttribute('aria-hidden', 'true');
  }

  if (menuToggle) {
    menuToggle.classList.remove('active');
  }

  document.body.classList.remove('nav-open');
}


// Мобильное меню
function initMobileMenu() {
  const menuToggle = document.getElementById('menuToggle');
  const navDrawer = document.getElementById('navDrawer');
  const navBackdrop = document.getElementById('navBackdrop');
  const drawerClose = document.getElementById('drawerClose');
  const drawerLinks = document.querySelectorAll('.drawer-nav a');
  const primaryLinks = document.querySelectorAll('.nav a');

  if (!menuToggle || !navDrawer) return;

  menuToggle.addEventListener('click', () => {
    const isOpen = navDrawer.classList.contains('active');
    if (isOpen) {
      closeMenuDrawer();
    } else {
      openMenuDrawer();
    }
  });

  [navBackdrop, drawerClose].forEach(el => {
    if (el) el.addEventListener('click', closeMenuDrawer);
  });

  [...drawerLinks, ...primaryLinks].forEach(link => {
    link.addEventListener('click', () => {
      closeMenuDrawer();
    });
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth > 1024) {
      closeMenuDrawer();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMenuDrawer();
  });
}

// Эффекты при скролле
function initScrollEffects() {
  const header = document.getElementById('header');
  const floatingEstimateWrapper = document.querySelector('.floating-estimate-wrapper');
  const floatingEstimateBtn = document.querySelector('.floating-estimate-btn');
  let lastScroll = 0;

  window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    if (currentScroll > 50) header.classList.add('scrolled');
    else header.classList.remove('scrolled');

    if (floatingEstimateWrapper) {
      if (currentScroll > 400) {
        floatingEstimateWrapper.classList.add('visible');
      } else {
        floatingEstimateWrapper.classList.remove('visible');
      }
    } else if (floatingEstimateBtn) {
      if (currentScroll > 400) {
        floatingEstimateBtn.classList.add('visible');
      } else {
        floatingEstimateBtn.classList.remove('visible');
      }
    }

    lastScroll = currentScroll;
  });

  const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('fade-in');
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);

  // Все элементы для анимации при скролле
  document.querySelectorAll(
    '.service-card, .case-card, .why-card, .task-item, .pnr-content, .pnr-why-content, .equipment-card, .brand-card'
  ).forEach(el => observer.observe(el));
}

function initFloatingSocialPanel() {
  const panel = document.querySelector('[data-floating-social-panel]');
  if (!panel) return;

  const closeBtn = panel.querySelector('[data-floating-social-close]');
  const openBtn = document.querySelector('[data-floating-social-open]');
  const storageKey = 'bisFloatingSocialPanelClosed';

  const setHidden = (hidden, { persist = true } = {}) => {
    panel.classList.toggle('is-hidden', hidden);
    if (openBtn) {
      openBtn.hidden = !hidden;
    }

    if (!persist) {
      return;
    }

    if (hidden) {
      window.localStorage.setItem(storageKey, '1');
    } else {
      window.localStorage.removeItem(storageKey);
    }
  };

  if (closeBtn) {
    closeBtn.addEventListener('click', () => {
      setHidden(true);
    });
  }

  if (openBtn) {
    openBtn.addEventListener('click', () => {
      setHidden(false);
    });
  }

  setHidden(window.localStorage.getItem(storageKey) === '1' || panel.classList.contains('is-hidden'), { persist: false });
}

// Параллакс в hero по умолчанию
function initHeroParallax() {
  const parallax = document.querySelector('.hero-parallax');
  if (!parallax) return;

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (prefersReducedMotion) return;

  const hero = document.querySelector('.hero');
  const layers = Array.from(parallax.querySelectorAll('[data-speed]'));
  if (!layers.length) return;

  let ticking = false;
  let heroOffset = 0;

  const recalc = () => {
    heroOffset = hero ? hero.offsetTop : 0;
    update();
  };

  const update = () => {
    const relativeScroll = Math.max(window.scrollY - heroOffset, 0);
    layers.forEach(layer => {
      const speed = parseFloat(layer.dataset.speed) || 0;
      layer.style.transform = `translate3d(0, ${relativeScroll * speed}px, 0)`;
    });
    ticking = false;
  };

  const onScroll = () => {
    if (!ticking) {
      window.requestAnimationFrame(update);
      ticking = true;
    }
  };

  recalc();
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', recalc);
}

// Анимация счетчиков статистики

function initSmoothScroll() {
  // Прокрутка для навигационных ссылок
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const targetId = this.getAttribute('href');

      if (targetId === '#') return;

      const targetElement = document.querySelector(targetId);

      if (targetElement) {
        const headerHeight = document.getElementById('header').offsetHeight;
        const targetPosition = targetElement.offsetTop - headerHeight;

        window.scrollTo({
          top: targetPosition,
          behavior: 'smooth'
        });

        // Закрываем меню если оно открыто
        closeMenuDrawer();
      }
    });
  });

  // Особый обработчик для ссылки на главную
  const homeLink = document.querySelector('a[href="#home"]');
  if (homeLink) {
    homeLink.addEventListener('click', function (e) {
      e.preventDefault();
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });

      // Закрываем меню если оно открыто
      closeMenuDrawer();
    });
  }
}

const CARD_SLIDER_MOBILE_BREAKPOINT = 768;
const CARD_SLIDER_DESKTOP_BREAKPOINT = 769;

function cleanupCardSlider(track, section, nav, dotsContainer) {
  if (!track) return;

  const state = track._sliderState;
  if (state) {
    if (state.handleScroll) {
      track.removeEventListener('scroll', state.handleScroll);
    }
    if (state.resizeObserver) {
      state.resizeObserver.disconnect();
    }
    if (state.scrollEndTimer) {
      clearTimeout(state.scrollEndTimer);
    }
    if (state.prevBtn && state.goPrev) {
      state.prevBtn.removeEventListener('click', state.goPrev);
      state.prevBtn.disabled = false;
    }
    if (state.nextBtn && state.goNext) {
      state.nextBtn.removeEventListener('click', state.goNext);
      state.nextBtn.disabled = false;
    }
  }

  track.removeAttribute('data-slider-initialized');
  track.removeAttribute('data-slider-mode');
  track.removeAttribute('data-current-slide');
  track._sliderState = null;

  section?.classList.remove('slider-enabled');
  nav?.classList.remove('is-visible');

  if (dotsContainer) {
    dotsContainer.innerHTML = '';
  }
}
