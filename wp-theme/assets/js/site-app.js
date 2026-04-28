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

  const scriptSrc = mapRoot.dataset.mapScriptSrc;
  const interactiveContainer = mapRoot.querySelector('.yandex-map__interactive');

  if (!scriptSrc || !interactiveContainer) return;

  let mapRequested = false;

  const loadMap = () => {
    if (mapRequested) return;

    mapRequested = true;
    mapRoot.dataset.mapInitialized = 'true';

    const script = document.createElement('script');
    script.src = scriptSrc;
    script.async = true;
    script.charset = 'utf-8';
    script.onload = () => {
      mapRoot.classList.add('is-loaded');
    };
    script.onerror = () => {
      mapRequested = false;
      mapRoot.dataset.mapInitialized = 'false';
    };

    interactiveContainer.appendChild(script);
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
  runInit('initTeamSlider');
  runInit('initTeamModal');
  runInit('initServicesSlider');
  runInit('initRelatedServicesSlider');
  runInit('initEstimateModal');
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
