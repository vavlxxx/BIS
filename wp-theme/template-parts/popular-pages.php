<?php
/**
 * Template part for rendering "Popular Services" block (Популярные услуги)
 * Structure with categories/rubrics, links, fade mask, and "Show more" toggle.
 */

if (function_exists('bis_should_hide_popular_services') && bis_should_hide_popular_services()) {
    return;
}

$groups = bis_get_popular_pages_grouped();

// If no items exist yet
if (empty($groups)) {
    if (current_user_can('edit_posts')) :
        ?>
        <section class="popular-services-section" id="popular-services">
            <div class="popular-services__container mw-1400px">
                <div class="popular-services__empty-admin" style="border: 2px dashed var(--border); padding: 36px 24px; text-align: center; background: var(--bg-alt);">
                    <div style="display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; background: rgba(112, 198, 212, 0.15); color: var(--primary-dark); margin-bottom: 12px;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    </div>
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--dark); margin: 0 0 8px 0;">Блок «Популярные услуги»</h3>
                    <p style="font-size: 14px; max-width: 520px; margin: 0 auto 18px; color: var(--text-light); line-height: 1.5;">
                        Блок настроен и готов к работе. Чтобы ссылки появились на сайте, перейдите в панель управления и добавьте рубрики и страницы.
                    </p>
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=popular_page&page=bis-popular-quick-add')); ?>" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
                        <span>⚡ Перейти к «Популярным услугам»</span>
                    </a>
                </div>
            </div>
        </section>
        <?php
    endif;
    return;
}
?>

<section class="popular-services-section" id="popular-services" aria-label="Популярные услуги">
    <div class="popular-services__container mw-1400px">
        <div class="popular-services__header">
            <h2 class="popular-services__title bis-condensed">Популярные услуги</h2>
        </div>

        <div class="popular-services__wrapper" data-popular-wrapper>
            <!-- Columns Grid -->
            <div class="popular-services__grid" data-popular-grid style="max-height: 400px;">
                <?php foreach ($groups as $group) : ?>
                    <div class="popular-services__col">
                        <div class="popular-services__col-head">
                            <h3 class="popular-services__col-title"><?php echo esc_html($group['title']); ?></h3>
                        </div>
                        <ul class="popular-services__list">
                            <?php foreach ($group['posts'] as $item) :
                                $item_url = function_exists('bis_make_url_relative') ? bis_make_url_relative($item['url']) : $item['url'];
                            ?>
                                <li class="popular-services__item">
                                    <a href="<?php echo esc_attr($item_url); ?>" class="popular-services__link"<?php if (!empty($item['target_blank'])) : ?> target="_blank" rel="noopener noreferrer"<?php endif; ?>>
                                        <?php echo esc_html($item['title']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Fade overlay when collapsed -->
            <div class="popular-services__fade" data-popular-fade></div>
        </div>

        <!-- Toggle Button ("Показать ещё" / "Скрыть") -->
        <div class="popular-services__action" data-popular-action>
            <button
                type="button"
                class="popular-services__toggle-btn"
                data-popular-toggle
                aria-expanded="false"
            >
                <span data-popular-toggle-text>Показать ещё</span>
                <svg class="popular-services__chevron" data-popular-chevron width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
        </div>
    </div>
</section>

<script>
(function() {
    function initPopularServices() {
        const grid = document.querySelector('[data-popular-grid]');
        const fade = document.querySelector('[data-popular-fade]');
        const toggleBtn = document.querySelector('[data-popular-toggle]');
        const toggleText = document.querySelector('[data-popular-toggle-text]');
        const toggleChevron = document.querySelector('[data-popular-chevron]');
        const actionWrap = document.querySelector('[data-popular-action]');

        if (!grid || !toggleBtn) return;

        const COLLAPSED_HEIGHT = 400; // default collapsed height in px

        function checkHeight() {
            // Check scrollHeight of content
            const fullHeight = grid.scrollHeight;
            if (fullHeight <= COLLAPSED_HEIGHT + 35) {
                // Everything fits comfortably, no toggle needed
                grid.style.maxHeight = 'none';
                if (fade) fade.style.display = 'none';
                if (actionWrap) actionWrap.style.display = 'none';
            } else {
                if (actionWrap) actionWrap.style.display = 'flex';
                if (!toggleBtn.classList.contains('is-open')) {
                    grid.style.maxHeight = COLLAPSED_HEIGHT + 'px';
                    if (fade) fade.style.display = 'block';
                }
            }
        }

        checkHeight();
        window.addEventListener('resize', checkHeight);

        toggleBtn.addEventListener('click', function() {
            const isCurrentlyOpen = toggleBtn.classList.contains('is-open');

            if (isCurrentlyOpen) {
                // Collapse
                grid.style.maxHeight = COLLAPSED_HEIGHT + 'px';
                toggleBtn.classList.remove('is-open');
                toggleBtn.setAttribute('aria-expanded', 'false');
                if (toggleText) toggleText.textContent = 'Показать ещё';
                if (toggleChevron) toggleChevron.style.transform = 'rotate(0deg)';
                if (fade) {
                    fade.style.opacity = '1';
                    fade.style.pointerEvents = 'none';
                }
                // Smooth scroll back to popular services section top if user was scrolled down
                const section = document.getElementById('popular-services');
                if (section && window.scrollY > section.offsetTop + 100) {
                    section.scrollIntoView({ behavior: 'smooth' });
                }
            } else {
                // Expand
                const fullHeight = grid.scrollHeight;
                grid.style.maxHeight = fullHeight + 'px';
                toggleBtn.classList.add('is-open');
                toggleBtn.setAttribute('aria-expanded', 'true');
                if (toggleText) toggleText.textContent = 'Скрыть';
                if (toggleChevron) toggleChevron.style.transform = 'rotate(180deg)';
                if (fade) {
                    fade.style.opacity = '0';
                    fade.style.pointerEvents = 'none';
                }

                // After transition ends, set to auto
                setTimeout(function() {
                    if (toggleBtn.classList.contains('is-open')) {
                        grid.style.maxHeight = 'none';
                    }
                }, 500);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPopularServices);
    } else {
        initPopularServices();
    }
})();
</script>
