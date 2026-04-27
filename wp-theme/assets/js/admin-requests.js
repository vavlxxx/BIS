jQuery(document).ready(function ($) {
    const listContainer = $('#bis-requests-list');
    const overviewContainer = $('#bis-requests-overview');
    const toolbarContainer = $('#bis-requests-toolbar');
    const expandedRequestIds = new Set();
    const config = window.bisRequestsData || {};
    const strings = Object.assign({}, config.strings || {}, {
        empty: 'Заявок пока нет.',
        loading: 'Загрузка заявок...',
        delete_confirm: 'Удалить заявку без возможности восстановления?',
        delete_error: 'Не удалось удалить заявку. Попробуйте еще раз.',
        search_placeholder: 'Поиск по имени, телефону, email, проекту или комментарию',
        search_button: 'Поиск',
        status_all: 'Все статусы',
        status_new: 'Новые',
        status_read: 'Просмотренные',
        type_all: 'Все типы',
        file_all: 'Все заявки',
        file_with_file: 'С файлами',
        file_problem: 'Проблемные файлы',
        expand_all: 'Развернуть все',
        collapse_all: 'Свернуть все',
        results: 'Найдено',
        total: 'Всего',
        unread: 'Новые',
        with_files: 'С файлами',
        file_problems: 'Проблемы с файлами',
        no_file: 'Файл не прикреплен',
        file_missing: 'Файл был прикреплен, но сейчас недоступен на сервере.',
        file_error: 'Ошибка загрузки файла',
        details: 'Детали',
        hide_details: 'Скрыть',
        delete: 'Удалить',
        view: 'Открыть',
        email: 'Email',
        contact: 'Предпочтительный контакт',
        file: 'Файл',
        type: 'Тип',
        project: 'Проект',
        company: 'Компания',
        position: 'Должность',
        topic: 'Тема',
        date: 'Дата',
        phone: 'Телефон',
        location: 'Город-регион',
        comment: 'Комментарий',
        type_contact: 'Связаться с нами',
        type_order: 'Заказ услуги',
        type_callback: 'Обратный звонок',
        type_estimate: 'Смета',
        type_consultation: 'Консультация по проекту',
        type_exit_intent: 'Лид-магнит при выходе',
        type_default: 'Заявка',
        status_badge_new: 'Новая',
        status_badge_read: 'Просмотрено'
    });

    let lastRequestCount = 0;
    let isMutating = false;
    let allRequests = [];
    let draftSearch = '';
    let filters = {
        search: '',
        status: 'all',
        type: 'all',
        fileState: 'all'
    };

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getAjaxData(extra) {
        return Object.assign({
            nonce: config.nonce || ''
        }, extra);
    }

    function getTypeLabel(type) {
        const map = {
            consultation: strings.type_consultation,
            estimate: strings.type_estimate,
            callback: strings.type_callback,
            order: strings.type_order,
            contact: strings.type_contact,
            exit_intent: strings.type_exit_intent
        };

        return map[type] || type || strings.type_default;
    }

    function getStatusBadge(req) {
        if (req.status === 'new') {
            return `<span class="bis-badge new">${escapeHtml(strings.status_badge_new)}</span>`;
        }

        return `<span class="bis-badge read">${escapeHtml(strings.status_badge_read)}</span>`;
    }

    function getMessengerIcon(messenger) {
        const icons = config.icons || {};
        const normalized = String(messenger || '').toLowerCase();
        let iconUrl = '';
        let alt = messenger || strings.contact;

        if (normalized === 'telegram') {
            iconUrl = icons.telegram || '';
            alt = 'Telegram';
        } else if (normalized === 'max') {
            iconUrl = icons.max || '';
            alt = 'MAX';
        } else if (normalized === 'whatsapp') {
            iconUrl = icons.whatsapp || '';
            alt = 'WhatsApp';
        }

        if (!iconUrl) {
            return '<span class="dashicons dashicons-smartphone"></span>';
        }

        return `<img class="bis-messenger-icon" src="${escapeHtml(iconUrl)}" alt="${escapeHtml(alt)}" width="18" height="18">`;
    }

    function hasFileProblem(req) {
        return Boolean(req.file_error || req.file_missing);
    }

    function getFilteredRequests() {
        const search = filters.search.trim().toLowerCase();

        return allRequests.filter((req) => {
            if (filters.status !== 'all' && req.status !== filters.status) {
                return false;
            }

            if (filters.type !== 'all' && req.type !== filters.type) {
                return false;
            }

            if (filters.fileState === 'with_file' && !req.has_file) {
                return false;
            }

            if (filters.fileState === 'problem' && !hasFileProblem(req)) {
                return false;
            }

            if (!search) {
                return true;
            }

            const haystack = [
                req.name,
                req.phone,
                req.email,
                req.comment,
                req.company,
                req.position,
                req.topic,
                req.project,
                req.location,
                req.file_name,
                getTypeLabel(req.type)
            ].join(' ').toLowerCase();

            return haystack.includes(search);
        });
    }

    function renderOverview() {
        const total = allRequests.length;
        const unread = allRequests.filter((req) => req.status === 'new').length;
        const withFiles = allRequests.filter((req) => req.has_file).length;
        const problemFiles = allRequests.filter((req) => hasFileProblem(req)).length;

        overviewContainer.html(`
            <div class="bis-overview-card">
                <span class="bis-overview-card__label">${escapeHtml(strings.total)}</span>
                <strong class="bis-overview-card__value">${total}</strong>
            </div>
            <div class="bis-overview-card">
                <span class="bis-overview-card__label">${escapeHtml(strings.unread)}</span>
                <strong class="bis-overview-card__value">${unread}</strong>
            </div>
            <div class="bis-overview-card">
                <span class="bis-overview-card__label">${escapeHtml(strings.with_files)}</span>
                <strong class="bis-overview-card__value">${withFiles}</strong>
            </div>
            <div class="bis-overview-card">
                <span class="bis-overview-card__label">${escapeHtml(strings.file_problems)}</span>
                <strong class="bis-overview-card__value">${problemFiles}</strong>
            </div>
        `);
    }

    function renderToolbar(filteredRequests) {
        const types = Array.from(new Set(allRequests.map((req) => req.type).filter(Boolean)));
        const activeElement = document.activeElement;
        const shouldRestoreSearchFocus = activeElement && activeElement.id === 'bis-requests-search';
        const selectionStart = shouldRestoreSearchFocus && typeof activeElement.selectionStart === 'number'
            ? activeElement.selectionStart
            : null;
        const selectionEnd = shouldRestoreSearchFocus && typeof activeElement.selectionEnd === 'number'
            ? activeElement.selectionEnd
            : null;

        toolbarContainer.html(`
            <div class="bis-toolbar-main">
                <form class="bis-toolbar-group bis-toolbar-group--search" id="bis-requests-search-form">
                    <label class="screen-reader-text" for="bis-requests-search">Поиск</label>
                    <input
                        type="search"
                        id="bis-requests-search"
                        class="regular-text"
                        placeholder="${escapeHtml(strings.search_placeholder)}"
                        value="${escapeHtml(draftSearch)}"
                    >
                    <button type="submit" class="button button-primary" id="bis-requests-search-button">${escapeHtml(strings.search_button)}</button>
                </form>
                <div class="bis-toolbar-group">
                    <label class="screen-reader-text" for="bis-requests-status">Статус</label>
                    <select id="bis-requests-status">
                        <option value="all"${filters.status === 'all' ? ' selected' : ''}>${escapeHtml(strings.status_all)}</option>
                        <option value="new"${filters.status === 'new' ? ' selected' : ''}>${escapeHtml(strings.status_new)}</option>
                        <option value="read"${filters.status === 'read' ? ' selected' : ''}>${escapeHtml(strings.status_read)}</option>
                    </select>
                </div>
                <div class="bis-toolbar-group">
                    <label class="screen-reader-text" for="bis-requests-type">Тип</label>
                    <select id="bis-requests-type">
                        <option value="all"${filters.type === 'all' ? ' selected' : ''}>${escapeHtml(strings.type_all)}</option>
                        ${types.map((type) => `
                            <option value="${escapeHtml(type)}"${filters.type === type ? ' selected' : ''}>${escapeHtml(getTypeLabel(type))}</option>
                        `).join('')}
                    </select>
                </div>
                <div class="bis-toolbar-group">
                    <label class="screen-reader-text" for="bis-requests-files">Файлы</label>
                    <select id="bis-requests-files">
                        <option value="all"${filters.fileState === 'all' ? ' selected' : ''}>${escapeHtml(strings.file_all)}</option>
                        <option value="with_file"${filters.fileState === 'with_file' ? ' selected' : ''}>${escapeHtml(strings.file_with_file)}</option>
                        <option value="problem"${filters.fileState === 'problem' ? ' selected' : ''}>${escapeHtml(strings.file_problem)}</option>
                    </select>
                </div>
            </div>
            <div class="bis-toolbar-secondary">
                <div class="bis-toolbar-meta">${escapeHtml(strings.results)}: <strong>${filteredRequests.length}</strong></div>
                <div class="bis-toolbar-group bis-toolbar-group--actions">
                    <button type="button" class="button" id="bis-expand-all">${escapeHtml(strings.expand_all)}</button>
                    <button type="button" class="button" id="bis-collapse-all">${escapeHtml(strings.collapse_all)}</button>
                </div>
            </div>
        `);

        attachToolbarHandlers();

        if (shouldRestoreSearchFocus) {
            const searchInput = document.getElementById('bis-requests-search');
            if (searchInput) {
                searchInput.focus();
                if (selectionStart !== null && selectionEnd !== null) {
                    searchInput.setSelectionRange(selectionStart, selectionEnd);
                }
            }
        }
    }

    function getFileMarkup(req) {
        if (req.can_download_file && req.file_url) {
            return `
                <div class="bis-file-block">
                    <a class="bis-file-link" href="${escapeHtml(req.file_url)}">
                        <span class="dashicons dashicons-media-document"></span>
                        ${escapeHtml(req.file_name || strings.view)}
                    </a>
                </div>
            `;
        }

        if (req.file_missing) {
            return `
                <div class="bis-file-block">
                    ${req.file_name ? `<div class="bis-file-name">${escapeHtml(req.file_name)}</div>` : ''}
                    <div class="bis-file-state bis-file-state--warning">${escapeHtml(strings.file_missing)}</div>
                </div>
            `;
        }

        if (req.file_error) {
            return `
                <div class="bis-file-block">
                    ${req.file_name ? `<div class="bis-file-name">${escapeHtml(req.file_name)}</div>` : ''}
                    <div class="bis-file-state bis-file-state--error">
                        <strong>${escapeHtml(strings.file_error)}:</strong> ${escapeHtml(req.file_error)}
                    </div>
                </div>
            `;
        }

        if (req.file_name) {
            return `<span class="bis-file-name">${escapeHtml(req.file_name)}</span>`;
        }

        return `<span class="bis-file-empty">${escapeHtml(strings.no_file)}</span>`;
    }

    function renderRequests() {
        const filteredRequests = getFilteredRequests();
        const visibleRequestIds = new Set(filteredRequests.map((req) => String(req.id)));

        Array.from(expandedRequestIds).forEach((id) => {
            if (!visibleRequestIds.has(id)) {
                expandedRequestIds.delete(id);
            }
        });

        renderOverview();
        renderToolbar(filteredRequests);

        if (filteredRequests.length === 0) {
            listContainer.html(`<div class="bis-empty">${escapeHtml(strings.empty)}</div>`);
            return;
        }

        const html = filteredRequests.map((req) => {
            const requestId = String(req.id);
            const isExpanded = expandedRequestIds.has(requestId);
            const messengerValue = req.messenger
                ? `${getMessengerIcon(req.messenger)}<span>${escapeHtml(req.messenger)}</span>`
                : '-';
            const detailItems = [
                { label: strings.phone, value: req.phone ? `<a href="tel:${escapeHtml(req.phone)}">${escapeHtml(req.phone)}</a>` : '-' },
                { label: strings.email, value: req.email ? `<a href="mailto:${escapeHtml(req.email)}">${escapeHtml(req.email)}</a>` : '-' },
                { label: strings.contact, value: `<span class="bis-messenger-value">${messengerValue}</span>` },
                { label: strings.file, value: getFileMarkup(req) },
                { label: strings.project, value: escapeHtml(req.project || '-') },
                { label: strings.location, value: escapeHtml(req.location || '-') },
                { label: strings.company, value: escapeHtml(req.company || '-') },
                { label: strings.position, value: escapeHtml(req.position || '-') },
                { label: strings.topic, value: escapeHtml(req.topic || '-') },
                { label: strings.date, value: escapeHtml(req.date_label || req.date || '-') }
            ];

            return `
                <div class="bis-request-card ${req.status === 'new' ? 'is-new' : 'is-read'}${isExpanded ? ' is-expanded' : ''}" data-id="${escapeHtml(req.id)}">
                    <div class="bis-request-card__summary">
                        <div class="bis-request-card__main">
                            <div class="bis-request-card__identity">
                                <div class="bis-request-card__name">${escapeHtml(req.name || strings.type_default)}</div>
                                <div class="bis-request-card__subline">
                                    <span class="bis-type-pill">${escapeHtml(getTypeLabel(req.type))}</span>
                                    ${req.phone ? `<a href="tel:${escapeHtml(req.phone)}">${escapeHtml(req.phone)}</a>` : ''}
                                    ${req.email ? `<a href="mailto:${escapeHtml(req.email)}">${escapeHtml(req.email)}</a>` : ''}
                                </div>
                            </div>
                        </div>
                        <div class="bis-request-card__meta">
                            <div class="bis-request-card__status">${getStatusBadge(req)}</div>
                            <div class="bis-request-card__date">${escapeHtml(req.date_label || req.date || '')}</div>
                            <div class="bis-request-card__flags">
                                ${req.has_file ? '<span class="bis-flag">Файл</span>' : ''}
                                ${hasFileProblem(req) ? '<span class="bis-flag bis-flag--warning">Проверить</span>' : ''}
                            </div>
                        </div>
                        <div class="bis-request-actions">
                            <button type="button" class="button toggle-row" aria-expanded="${isExpanded ? 'true' : 'false'}">
                                ${escapeHtml(isExpanded ? strings.hide_details : strings.details)}
                            </button>
                            <button type="button" class="button delete-request">
                                ${escapeHtml(strings.delete)}
                            </button>
                        </div>
                    </div>
                    <div class="bis-request-card__details${isExpanded ? '' : ' hidden'}">
                        <div class="bis-detail-grid">
                            ${detailItems.map((item) => `
                                <div class="bis-detail-item">
                                    <span class="label">${escapeHtml(item.label)}</span>
                                    <span class="value">${item.value}</span>
                                </div>
                            `).join('')}
                        </div>
                        ${req.comment ? `
                            <div class="bis-detail-comment">
                                <span class="label">${escapeHtml(strings.comment)}</span>
                                <div class="comment-box">${escapeHtml(req.comment)}</div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }).join('');

        listContainer.html(html);
        attachHandlers();
    }

    function updateMenuCount(count) {
        const menuLink = $('a.toplevel_page_bis-requests .wp-menu-name');
        const countSpan = menuLink.find('.awaiting-mod');

        if (count > 0) {
            if (countSpan.length) {
                countSpan.find('.pending-count').text(count);
                countSpan.attr('class', `awaiting-mod count-${count}`);
            } else {
                menuLink.append(` <span class="awaiting-mod count-${count}"><span class="pending-count" aria-hidden="true">${count}</span></span>`);
            }
        } else {
            countSpan.remove();
        }
    }

    function markRequestAsRead(card) {
        if (!card.hasClass('is-new')) {
            return;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: getAjaxData({
                action: 'bis_mark_read',
                id: card.data('id')
            }),
            success: function (response) {
                if (!response.success) {
                    return;
                }

                card.removeClass('is-new').addClass('is-read');
                card.find('.bis-request-card__status').html(getStatusBadge({ status: 'read' }));
                updateMenuCount(response.data.count);

                const request = allRequests.find((item) => String(item.id) === String(card.data('id')));
                if (request) {
                    request.status = 'read';
                }
            }
        });
    }

    function toggleCard(card, shouldExpand) {
        const details = card.find('.bis-request-card__details');
        const button = card.find('.toggle-row');
        const requestId = String(card.data('id'));

        details.toggleClass('hidden', !shouldExpand);
        card.toggleClass('is-expanded', shouldExpand);
        button.attr('aria-expanded', shouldExpand ? 'true' : 'false');
        button.text(shouldExpand ? strings.hide_details : strings.details);

        if (shouldExpand) {
            expandedRequestIds.add(requestId);
            markRequestAsRead(card);
        } else {
            expandedRequestIds.delete(requestId);
        }
    }

    function deleteRequest(requestId) {
        if (!window.confirm(strings.delete_confirm)) {
            return;
        }

        isMutating = true;

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: getAjaxData({
                action: 'bis_delete_request',
                id: requestId
            }),
            success: function (response) {
                if (!response.success) {
                    window.alert(strings.delete_error);
                    return;
                }

                expandedRequestIds.delete(String(requestId));
                updateMenuCount(response.data.count);
                fetchRequests();
            },
            error: function () {
                window.alert(strings.delete_error);
            },
            complete: function () {
                isMutating = false;
            }
        });
    }

    function attachHandlers() {
        $('.toggle-row').off('click').on('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            const card = $(this).closest('.bis-request-card');
            const shouldExpand = card.find('.bis-request-card__details').hasClass('hidden');
            toggleCard(card, shouldExpand);
        });

        $('.delete-request').off('click').on('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            deleteRequest($(this).closest('.bis-request-card').data('id'));
        });

        $('.bis-request-card__summary').off('click').on('click', function (event) {
            if ($(event.target).closest('button,a').length) {
                return;
            }

            const card = $(this).closest('.bis-request-card');
            const shouldExpand = card.find('.bis-request-card__details').hasClass('hidden');
            toggleCard(card, shouldExpand);
        });
    }

    function attachToolbarHandlers() {
        $('#bis-requests-search').off('input').on('input', function () {
            draftSearch = $(this).val() || '';
        });

        $('#bis-requests-search-form').off('submit').on('submit', function (event) {
            event.preventDefault();
            filters.search = draftSearch.trim();
            renderRequests();
        });

        $('#bis-requests-status').off('change').on('change', function () {
            filters.status = $(this).val() || 'all';
            renderRequests();
        });

        $('#bis-requests-type').off('change').on('change', function () {
            filters.type = $(this).val() || 'all';
            renderRequests();
        });

        $('#bis-requests-files').off('change').on('change', function () {
            filters.fileState = $(this).val() || 'all';
            renderRequests();
        });

        $('#bis-expand-all').off('click').on('click', function () {
            getFilteredRequests().forEach((req) => expandedRequestIds.add(String(req.id)));
            renderRequests();
        });

        $('#bis-collapse-all').off('click').on('click', function () {
            expandedRequestIds.clear();
            renderRequests();
        });
    }

    function fetchRequests() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: getAjaxData({
                action: 'bis_get_requests'
            }),
            beforeSend: function () {
                if (!allRequests.length) {
                    listContainer.html(`<div class="bis-loading">${escapeHtml(strings.loading)}</div>`);
                }
            },
            success: function (response) {
                if (!response.success) {
                    return;
                }

                allRequests = Array.isArray(response.data) ? response.data : [];
                updateMenuCount(allRequests.filter((req) => req.status === 'new').length);

                if (allRequests.length > lastRequestCount && lastRequestCount !== 0) {
                    window.console.info('Новая заявка получена');
                }

                lastRequestCount = allRequests.length;
                renderRequests();
            }
        });
    }

    draftSearch = filters.search;
    fetchRequests();
    setInterval(function () {
        if (!isMutating) {
            fetchRequests();
        }
    }, 5000);
});
