<?php
get_header();
?>

<main class="project-single">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <?php
            $project_id = get_the_ID();
            $details = bis_get_project_details($project_id);
            $banner_image = bis_get_project_banner_image($project_id);
            $banner_title = bis_get_project_banner_title($project_id);
            $banner_blocks = bis_get_project_banner_blocks($project_id);
            $project_description = bis_get_project_description($project_id);
            $gallery = bis_get_project_gallery($project_id);
            $project_content = trim((string) get_post_field('post_content', $project_id));

            $positions = array('top_left', 'top_right', 'bottom_left', 'bottom_right');
            $resolved_blocks = array();
            $has_blocks = false;

            foreach ($positions as $position) {
                $block = isset($banner_blocks[$position]) && is_array($banner_blocks[$position]) ? $banner_blocks[$position] : array();
                $label = isset($block['label']) ? trim($block['label']) : '';
                $value = isset($block['value']) ? trim($block['value']) : '';
                if ($label !== '' || $value !== '') {
                    $has_blocks = true;
                }
                $resolved_blocks[$position] = array(
                    'label' => $label,
                    'value' => $value,
                );
            }

            if (!$has_blocks) {
                $area_value = $details['area'];
                if ($area_value && !preg_match('/\b(м2|м²|m2|m²)\b/iu', $area_value)) {
                    $area_value .= ' м²';
                }

                $resolved_blocks = array(
                    'top_left' => array('label' => 'Год реализации', 'value' => $details['year']),
                    'bottom_left' => array('label' => 'Адрес', 'value' => $details['address']),
                    'top_right' => array('label' => 'Площадь', 'value' => $area_value),
                    'bottom_right' => array('label' => '', 'value' => ''),
                );
            }

            $all_projects = get_posts(array(
                'post_type' => 'bis_project',
                'posts_per_page' => -1,
                'orderby' => array('menu_order' => 'ASC', 'title' => 'ASC'),
                'fields' => 'ids',
            ));
            $next_project_id = null;
            if (!empty($all_projects)) {
                $current_index = array_search($project_id, $all_projects, true);
                if ($current_index !== false && isset($all_projects[$current_index + 1])) {
                    $next_project_id = $all_projects[$current_index + 1];
                }
            }
            ?>

            <section class="project-hero">
                <div class="project-hero__media">
                    <img src="<?php echo esc_url($banner_image); ?>" alt="<?php echo esc_attr($banner_title); ?>" decoding="async">
                </div>
                <div class="project-hero__overlay">
                    <div class="project-hero__inner">
                        <h1 class="project-hero__title"><?php echo esc_html($banner_title); ?></h1>
                        <div class="project-hero__info">
                            <?php
                            $labels = array(
                                'top_left' => 'top-left',
                                'top_right' => 'top-right',
                                'bottom_left' => 'bottom-left',
                                'bottom_right' => 'bottom-right',
                            );
                            foreach ($labels as $key => $class_suffix) :
                                $block = isset($resolved_blocks[$key]) ? $resolved_blocks[$key] : array('label' => '', 'value' => '');
                                $label = isset($block['label']) ? trim($block['label']) : '';
                                $value = isset($block['value']) ? trim($block['value']) : '';
                                if ($label === '' && $value === '') {
                                    continue;
                                }
                                ?>
                                <div class="project-hero__block project-hero__block--<?php echo esc_attr($class_suffix); ?>">
                                    <?php if ($label !== '') : ?>
                                        <span class="project-hero__label"><?php echo esc_html($label); ?></span>
                                    <?php endif; ?>
                                    <?php if ($value !== '') : ?>
                                        <span class="project-hero__value"><?php echo nl2br(esc_html($value)); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="project-hero__accent" aria-hidden="true"></div>
                </div>
            </section>

            <section class="breadcrumbs-section">
                <nav class="project-breadcrumbs mw-1400px">
                    <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
                    <span class="breadcrumbs-delimiter">/</span>
                    <a href="<?php echo esc_url(home_url('/projects/')); ?>">Проекты</a>
                    <span class="breadcrumbs-delimiter">/</span>
                    <span><?php the_title(); ?></span>
                </nav>
            </section>
            <!-- <?php if (!empty($project_description)) : ?>
                <section class="project-description">
                    <div class="project-description__body mw-1400px">
                        <?php echo wpautop(esc_html($project_description)); ?>
                    </div>
                </section>
            <?php endif; ?> -->
            <?php if ($project_content !== '') : ?>
                <section class="project-content">
                    <div class="project-content__body mw-1400px">
                        <?php the_content(); ?>
                    </div>
                </section>
            <?php endif; ?>
            <?php if (!empty($gallery)) : ?>
                <section class="project-gallery" data-project-gallery>
                    <div class="project-gallery-wrapper mw-1400px">
                        <div class="project-gallery__header">
                            <h2 class="project-gallery__title">Галерея проекта</h2>
                        </div>
                        <div class="project-gallery__masonry">
                            <?php foreach ($gallery as $index => $image) : ?>
                                <button type="button" class="project-gallery__item" data-gallery-slide data-full="<?php echo esc_url($image); ?>" aria-label="<?php echo esc_attr(get_the_title() . ' — фото ' . ($index + 1)); ?>">
                                    <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr(get_the_title() . ' — фото ' . ($index + 1)); ?>" loading="lazy">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>

                <div class="project-lightbox" id="projectLightbox" aria-hidden="true">
                    <div class="project-lightbox__content">
                        <button type="button" class="project-lightbox__close" data-lightbox-close aria-label="Закрыть">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <button type="button" class="project-lightbox__nav project-lightbox__nav--prev" data-lightbox-prev aria-label="Предыдущее фото">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <img class="project-lightbox__image" data-lightbox-image alt="">
                        <button type="button" class="project-lightbox__nav project-lightbox__nav--next" data-lightbox-next aria-label="Следующее фото">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <div class="project-lightbox__caption" data-lightbox-caption></div>
                    </div>
                </div>
            <?php endif; ?>

            <section class="project-consultation">
                <div class="project-consultation-wrapper mw-1400px">
                    <?php if ($next_project_id) : ?>
                        <a class="project-consultation__next" href="<?php echo esc_url(get_permalink($next_project_id)); ?>">Следующий проект -></a>
                    <?php endif; ?>

                    <div class="project-consultation__header">
                        <h2 class="project-consultation__title">Получить консультацию</h2>
                        <p class="project-consultation__subtitle">Заполните форму — мы обязательно вам ответим.</p>
                    </div>

                    <form class="project-consultation__form" id="projectConsultationForm">
                        <input type="hidden" name="project_id" value="<?php echo esc_attr($project_id); ?>">

                        <div class="project-consultation__field">
                            <label for="projectName">ФИО *</label>
                            <input type="text" id="projectName" name="name" required placeholder="Ваше имя" autocomplete="name">
                        </div>

                        <div class="project-consultation__field">
                            <label for="projectPhone">Телефон *</label>
                            <input type="tel" id="projectPhone" name="phone" required placeholder="+7 (___) ___-__-__" autocomplete="tel">
                        </div>

                        <div class="project-consultation__field">
                            <label for="projectEmail">Рабочий E-mail *</label>
                            <input type="email" id="projectEmail" name="email" required placeholder="example@company.ru" autocomplete="email">
                        </div>

                        <div class="project-consultation__field">
                            <label for="projectCompany">Компания *</label>
                            <input type="text" id="projectCompany" name="company" required placeholder="Название компании" autocomplete="organization">
                        </div>

                        <div class="project-consultation__field">
                            <label for="projectPosition">Должность *</label>
                            <input type="text" id="projectPosition" name="position" required placeholder="Ваша должность" autocomplete="organization-title">
                        </div>

                        <div class="project-consultation__field">
                            <label for="projectTopic">Тема вопроса *</label>
                            <select id="projectTopic" name="topic" required>
                                <option value="">Выберите тему</option>
                                <option value="Пусконаладка систем">Пусконаладка систем</option>
                                <option value="Техническое обслуживание">Техническое обслуживание</option>
                                <option value="Диагностика и аудит">Диагностика и аудит</option>
                                <option value="Другое">Другое</option>
                            </select>
                        </div>

                        <div class="project-consultation__field full">
                            <label for="projectDetails">Подробнее о задачах</label>
                            <textarea id="projectDetails" name="details" placeholder="Опишите задачу или оставьте комментарий"></textarea>
                        </div>

                        <div class="project-consultation__field full project-consultation__consent">
                            <label>
                                <input type="checkbox" name="privacy" required>
                                Я соглашаюсь с обработкой персональных данных, в соответствии с Политикой конфиденциальности.
                            </label>
                            <label>
                                <input type="checkbox" name="marketing">
                                Я соглашаюсь на получение информационных рассылок и другой коммуникации от БИС.
                            </label>
                        </div>

                        <div class="project-consultation__actions full">
                            <button type="submit" class="btn btn-primary">Отправить</button>
                        </div>
                    </form>
                </div>
            </section>
        <?php endwhile; ?>
    <?php else : ?>
        <section class="project-content">
            <div class="project-content__body">
                <p>Проект не найден.</p>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php
get_footer();
?>
