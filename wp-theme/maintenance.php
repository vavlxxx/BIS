<?php
$settings = bis_get_maintenance_settings();

$badge   = isset($settings['badge']) ? $settings['badge'] : '';
$title   = isset($settings['title']) ? $settings['title'] : '';
$message = isset($settings['message']) ? $settings['message'] : '';
$phone   = isset($settings['phone']) ? $settings['phone'] : '';
$email   = isset($settings['email']) ? $settings['email'] : '';

$phone_href = preg_replace('/[^\d\+]/', '', $phone);
if ($phone_href && '+' !== substr($phone_href, 0, 1)) {
    $phone_href = '+' . $phone_href;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html($title ? $title : 'Сайт временно недоступен'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo esc_url(get_template_directory_uri() . '/assets/css/base.css'); ?>">
    <link rel="stylesheet" href="<?php echo esc_url(get_template_directory_uri() . '/assets/css/front-page.css'); ?>">
    <link rel="stylesheet" href="<?php echo esc_url(get_template_directory_uri() . '/assets/css/news.css'); ?>">
    <link rel="stylesheet" href="<?php echo esc_url(get_template_directory_uri() . '/assets/css/team.css'); ?>">
    <link rel="stylesheet" href="<?php echo esc_url(get_template_directory_uri() . '/assets/css/content.css'); ?>">
</head>
<body class="maintenance-body site-loading">
    <div class="site-loader" id="siteLoader" role="status" aria-live="polite" aria-label="Loading page">
        <div class="site-loader__inner">
            <div class="site-loader__mark" aria-hidden="true">
                <span class="site-loader__ring"></span>
                <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/img/bis-logo-white.png" alt="" class="site-loader__logo">
            </div>
            <div class="site-loader__progress" aria-hidden="true">
                <span class="site-loader__line"></span>
                <span class="site-loader__percent" data-loader-percent>0%</span>
            </div>
        </div>
    </div>
    <noscript><style>.site-loader{display:none!important}body.site-loading{overflow:auto}</style></noscript>
    <main class="maintenance">
        <div class="maintenance__bg" aria-hidden="true"></div>
        <div class="maintenance__card" role="main">
            <?php if ($badge) : ?>
                <div class="maintenance__badge">
                    <span class="maintenance__badge-dot" aria-hidden="true"></span>
                    <?php echo esc_html($badge); ?>
                </div>
            <?php endif; ?>

            <?php if ($title) : ?>
                <h1 class="maintenance__title"><?php echo esc_html($title); ?></h1>
            <?php endif; ?>

            <?php if ($message) : ?>
                <p class="maintenance__message"><?php echo esc_html($message); ?></p>
            <?php endif; ?>

            <?php if ($phone || $email) : ?>
                <div class="maintenance__contacts">
                    <?php if ($phone) : ?>
                        <a class="maintenance__chip" href="tel:<?php echo esc_attr($phone_href); ?>">
                            <span class="maintenance__chip-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.1 4.2 9.5 6.6c.4.4.5 1 .3 1.5l-.9 2.4a1.2 1.2 0 0 0 .3 1.3l3.3 3.3c.3.3.9.4 1.3.3l2.4-.9c.5-.2 1.1-.1 1.5.3l2.4 2.4c.4.4.4 1 .1 1.5-.9 1.4-2.6 2.8-5.8 2.4-3.3-.4-6.9-3.5-9.3-6-2.4-2.4-5.5-6-6-9.3-.4-3.2 1.1-4.9 2.4-5.8.4-.3 1-.3 1.4.1Z" fill="currentColor"/>
                                </svg>
                            </span>
                            <span class="maintenance__chip-text"><?php echo esc_html($phone); ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if ($email) : ?>
                        <a class="maintenance__chip" href="mailto:<?php echo antispambot($email); ?>">
                            <span class="maintenance__chip-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 6.5c0-.8.7-1.5 1.5-1.5h13c.8 0 1.5.7 1.5 1.5v11c0 .8-.7 1.5-1.5 1.5h-13A1.5 1.5 0 0 1 4 17.5v-11Z" stroke="currentColor" stroke-width="1.5" />
                                    <path d="M5 8 12 12.5 19 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            <span class="maintenance__chip-text"><?php echo esc_html($email); ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <script>
        (function () {
            const started = performance.now();
            let queued = false;
            let progress = 0;
            let progressTimer;

            function setPercent(value) {
                const percent = document.querySelector('[data-loader-percent]');
                if (!percent) return;

                progress = Math.max(0, Math.min(100, Math.round(value)));
                percent.textContent = progress + '%';
            }

            function startProgress() {
                setPercent(0);

                progressTimer = window.setInterval(function () {
                    if (queued) return;

                    const nextValue = progress + Math.max(1, Math.round((92 - progress) * 0.08));
                    setPercent(Math.min(92, nextValue));
                }, 120);
            }

            function hideLoader() {
                const loader = document.getElementById('siteLoader');
                if (!loader || queued || loader.classList.contains('is-hidden')) return;

                queued = true;
                window.clearInterval(progressTimer);
                setPercent(100);
                const delay = Math.max(0, 350 - (performance.now() - started));

                window.setTimeout(function () {
                    loader.classList.add('is-hidden');
                    loader.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('site-loading');

                    window.setTimeout(function () {
                        loader.remove();
                    }, 500);
                }, delay);
            }

            if (document.readyState === 'complete') {
                hideLoader();
            } else {
                startProgress();
                window.addEventListener('load', hideLoader, { once: true });
            }
        })();
    </script>
</body>
</html>
