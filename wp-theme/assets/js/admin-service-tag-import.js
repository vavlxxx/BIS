(function (wp, document) {
    'use strict';

    if (!wp || !wp.apiFetch || !wp.data) {
        return;
    }

    var importing = false;
    var saveLock = 'bis-service-tag-import';

    function isServiceTagInput(input) {
        if (!input || input.tagName !== 'INPUT' || !input.closest) {
            return false;
        }
        var panel = input.closest('.components-panel__body');
        if (!panel || !panel.querySelector) {
            return false;
        }
        var heading = panel.querySelector('.components-panel__body-toggle, .components-panel__body-title');
        return Boolean(heading && heading.textContent && heading.textContent.indexOf('Теги услуг') !== -1);
    }

    async function onPaste(event) {
        if (!isServiceTagInput(event.target) || !event.clipboardData) {
            return;
        }
        var pasted = event.clipboardData.getData('text/plain');
        var names = pasted.split(/[,\r\n]+/).map(function (name) {
            return name.trim();
        }).filter(Boolean);
        if (names.length < 2) {
            return;
        }

        // Stop Gutenberg from creating dozens of terms through parallel REST calls.
        event.preventDefault();
        event.stopPropagation();
        if (importing) {
            return;
        }

        var editor = wp.data.select('core/editor');
        var editorActions = wp.data.dispatch('core/editor');
        var notices = wp.data.dispatch('core/notices');
        var postId = editor.getCurrentPostId();
        if (!postId) {
            notices.createErrorNotice('Сначала сохраните услугу, затем добавьте метки.', { type: 'snackbar' });
            return;
        }

        importing = true;
        editorActions.lockPostSaving(saveLock);
        try {
            var result = await wp.apiFetch({
                path: '/bis/v1/service-tags/import',
                method: 'POST',
                data: { post_id: postId, terms: pasted }
            });
            if (!result || !Array.isArray(result.ids) || result.ids.length === 0) {
                throw new Error('Сервер не вернул добавленные метки.');
            }
            var current = wp.data.select('core/editor').getEditedPostAttribute('bis_service_tag') || [];
            var uniqueIds = Array.from(new Set(current.concat(result.ids.map(Number))));
            editorActions.editPost({ bis_service_tag: uniqueIds });
            notices.createSuccessNotice('Метки добавлены в редактор. Нажмите «Сохранить».', { type: 'snackbar' });
        } catch (error) {
            notices.createErrorNotice(
                'Не удалось добавить метки: ' + (error && error.message ? error.message : 'ошибка сервера'),
                { type: 'snackbar' }
            );
        } finally {
            editorActions.unlockPostSaving(saveLock);
            importing = false;
        }
    }

    document.addEventListener('paste', onPaste, true);
})(window.wp, document);
