const siteLoaderStart = performance.now();
let siteLoaderHideQueued = false;
let siteLoaderProgress = 0;
let siteLoaderProgressTimer;

function runInit(name, ...args) {
  const handler = window[name];
  if (typeof handler !== 'function') return;
  handler(...args);
}

function setSiteLoaderPercent(value) {
  const percent = document.querySelector('[data-loader-percent]');
  if (!percent) return;

  siteLoaderProgress = Math.max(0, Math.min(100, Math.round(value)));
  percent.textContent = `${siteLoaderProgress}%`;
}

function startSiteLoaderProgress() {
  setSiteLoaderPercent(0);

  siteLoaderProgressTimer = window.setInterval(() => {
    if (siteLoaderHideQueued) return;

    const nextValue = siteLoaderProgress + Math.max(1, Math.round((92 - siteLoaderProgress) * 0.08));
    setSiteLoaderPercent(Math.min(92, nextValue));
  }, 120);
}

function hideSiteLoader() {
  const loader = document.getElementById('siteLoader');
  if (!loader || siteLoaderHideQueued || loader.classList.contains('is-hidden')) return;

  siteLoaderHideQueued = true;
  window.clearInterval(siteLoaderProgressTimer);
  setSiteLoaderPercent(100);

  const minVisibleTime = 350;
  const elapsed = performance.now() - siteLoaderStart;
  const delay = Math.max(0, minVisibleTime - elapsed);

  window.setTimeout(() => {
    loader.classList.add('is-hidden');
    loader.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('site-loading');

    window.setTimeout(() => {
      loader.remove();
    }, 500);
  }, delay);
}

function initLazyYandexMap() {
  const mapRoot = document.querySelector('[data-yandex-map]');
  if (!mapRoot || mapRoot.dataset.mapInitialized === 'true') return;

  const interactiveContainer = mapRoot.querySelector('.yandex-map__interactive');
  if (!interactiveContainer) return;

  let mapRequested = false;

  const initNativeMap = () => {
    try {
      // Office coordinates: г. Москва, Таможенный проезд, 6с9
      const officeCoords = [55.751665, 37.679484];
      const mapCenter = [55.753776, 37.671575];

      const contactMap = new ymaps.Map(interactiveContainer, {
        center: mapCenter,
        zoom: 14,
        controls: ['zoomControl', 'fullscreenControl']
      }, {
        suppressMapOpenBlock: true,
        searchControlProvider: 'yandex#search'
      });

      contactMap.behaviors.enable('scrollZoom');

      const placemark = new ymaps.Placemark(officeCoords, {
        hintContent: 'БИС — Баланс Инженерных Систем (г. Москва, проезд Таможенный д.6, стр.9)',
        balloonContent: '<div style="font-family: \'Inter\', -apple-system, sans-serif; color: #111827; padding: 6px 8px; max-width: 260px;">' +
          '<strong style="font-size: 14px; font-weight: 700; color: #167b88; display: block; margin-bottom: 4px;">БИС — Баланс Инженерных Систем</strong>' +
          '<span style="font-size: 13px; color: #374151; display: block; line-height: 1.4;">г. Москва, проезд Таможенный д.6, стр.9</span>' +
          '</div>'
      }, {
        preset: 'islands#blueDotIconWithCaption'
      });

      contactMap.geoObjects.add(placemark);
      mapRoot.classList.add('is-loaded');

      // Suppress any ad banners dynamically
      const removeAds = () => {
        try {
          const ads = mapRoot.querySelectorAll(
            '[class*="-ad-"], [class*="--ad"], [class*="-ad_"], [class*="b-ad"], [class*="ad_banner"], [class*="copyrights-promo"], [class*="promo-banner"], [class*="direct-banner"], [class*="direct-advert"], [class*="ymaps-"][class*="ad"]'
          );
          ads.forEach(el => {
            el.style.setProperty('display', 'none', 'important');
            el.style.setProperty('height', '0', 'important');
          });

          mapRoot.querySelectorAll('*').forEach(el => {
            if (el.children.length === 0 && (el.textContent || '').trim().toUpperCase() === 'РЕКЛАМА') {
              const adCard = el.closest('[class*="ymaps-2-1"]') || el.parentElement?.parentElement;
              if (adCard && adCard !== interactiveContainer) {
                adCard.style.setProperty('display', 'none', 'important');
                adCard.style.setProperty('height', '0', 'important');
              }
            }
          });
        } catch (e) {}
      };

      if ('MutationObserver' in window) {
        new MutationObserver(removeAds).observe(interactiveContainer, { childList: true, subtree: true });
      }
      removeAds();
    } catch (err) {
      console.error('Error initializing contact Yandex Map:', err);
    }
  };

  const loadMap = () => {
    if (mapRequested) return;

    mapRequested = true;
    mapRoot.dataset.mapInitialized = 'true';

    if (typeof ymaps !== 'undefined') {
      ymaps.ready(initNativeMap);
    } else {
      const script = document.createElement('script');
      script.src = 'https://api-maps.yandex.ru/2.1/?lang=ru_RU';
      script.async = true;
      script.charset = 'utf-8';
      script.onload = () => {
        if (typeof ymaps !== 'undefined') {
          ymaps.ready(initNativeMap);
        }
      };
      script.onerror = () => {
        mapRequested = false;
        mapRoot.dataset.mapInitialized = 'false';
      };
      document.head.appendChild(script);
    }
  };

  if (!('IntersectionObserver' in window)) {
    loadMap();
    return;
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;

      observer.disconnect();
      loadMap();
    });
  }, {
    rootMargin: '200px 0px',
    threshold: 0.1
  });

  observer.observe(mapRoot);
}

if (document.readyState === 'complete') {
  hideSiteLoader();
} else {
  startSiteLoaderProgress();
  window.addEventListener('load', hideSiteLoader, { once: true });
  window.addEventListener('pageshow', (event) => {
    if (event.persisted || document.readyState === 'complete') {
      hideSiteLoader();
    }
  }, { once: true });
}

document.addEventListener('DOMContentLoaded', () => {
  runInit('initHeaderLocation');
  runInit('initTypingEffect');
  runInit('initMobileMenu');
  runInit('initLangSwitcher');
  runInit('initCallbackModal');
  runInit('initScrollEffects');
  runInit('initFloatingSocialPanel');
  runInit('initHeroParallax');
  runInit('initFormValidation');
  runInit('initPopupForm');
  runInit('initSmoothScroll');
  runInit('initEquipmentSlider');
  runInit('initExperienceSlider');
  runInit('initGratitudeSlider');
  runInit('initGratitudeModal');
  runInit('initExperienceModal');
  runInit('initCasesModal');
  runInit('initFAQ');
  runInit('initNewsTabs');
  runInit('initTeamSlider');
  runInit('initTeamModal');
  runInit('initServicesSlider');
  runInit('initRelatedServicesSlider');
  runInit('initEstimateModal');
  runInit('initTurnkeyCalcForm');
  runInit('initVacancyModal');
  runInit('initRevenueChart');
  runInit('initProjectConsultationForm');
  runInit('initProjectGallery');
  runInit('initCookieConsent');
  initLazyYandexMap();
  runInit('initObjectsSlider');
  runInit('initPhoneMasks');
  runInit('initExitIntentModal');
  runInit('syncUniformCardHeights');

  let resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      runInit('initServicesSlider');
      runInit('initRelatedServicesSlider');
      runInit('initExperienceSlider');
      runInit('initObjectsSlider');
      runInit('syncUniformCardHeights');
    }, 250);
  });
});

window.addEventListener('load', () => {
  runInit('syncUniformCardHeights');
});
