document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-hero-carousel]');
    if (!root) return;
    const slides = Array.from(root.querySelectorAll('[data-hero-screen]'));
    const images = Array.from(root.querySelectorAll('.hero-slide'));
    const dots = Array.from(root.querySelectorAll('[data-hero-dot]'));
    const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const configuredInterval = Number(root.getAttribute('data-hero-interval'));
    const interval = configuredInterval > 0 ? Math.max(3000, Math.min(60000, configuredInterval)) : 7000;
    let current = 0;
    let timer;
    let hovered = false;
    const show = (index) => {
        current = (index + slides.length) % slides.length;
        slides.forEach((slide, i) => {
            const active = i === current;
            slide.classList.toggle('active', active);
            slide.setAttribute('aria-hidden', String(!active));
            slide.inert = !active;
            images[i].classList.toggle('active', active);
            dots[i].setAttribute('aria-pressed', String(active));
        });
    };
    const schedule = () => {
        clearInterval(timer);
        if (!motion.matches && !hovered && !document.hidden && !root.contains(document.activeElement)) {
            timer = setInterval(() => show(current + 1), interval);
        }
    };
    const navigate = (index) => { show(index); schedule(); };
    dots.forEach((dot, i) => dot.addEventListener('click', () => navigate(i)));
    root.addEventListener('mouseenter', () => { hovered = true; schedule(); });
    root.addEventListener('mouseleave', () => { hovered = false; schedule(); });
    root.addEventListener('focusin', schedule);
    root.addEventListener('focusout', () => setTimeout(schedule, 0));
    document.addEventListener('visibilitychange', schedule);
    motion.addEventListener('change', schedule);
    let startX = null;
    let startY = null;
    root.addEventListener('touchstart', e => { startX = e.touches[0].clientX; startY = e.touches[0].clientY; }, { passive: true });
    root.addEventListener('touchcancel', () => { startX = null; }, { passive: true });
    root.addEventListener('touchend', e => {
        if (startX === null) return;
        const dx = e.changedTouches[0].clientX - startX;
        const dy = e.changedTouches[0].clientY - startY;
        if (Math.abs(dx) > 60 && Math.abs(dx) > Math.abs(dy)) navigate(current + (dx < 0 ? 1 : -1));
        startX = null;
    }, { passive: true });
    show(0);
    schedule();
});
