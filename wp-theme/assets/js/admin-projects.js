(function ($) {
  $(document).ready(function () {
    const galleryList = $('#bis-project-gallery-list');
    const galleryTemplate = $('#bis-project-gallery-item-template');
    const hasBlockEditorStore = Boolean(window.wp && wp.data && wp.data.dispatch && wp.data.select);

    const getPreview = (targetId) => {
      if (!targetId) return $();
      return $(`[data-image-preview="${targetId}"]`);
    };

    const syncMetaField = (fieldName, value) => {
      if (!hasBlockEditorStore || !fieldName) return;

      const currentMeta = wp.data.select('core/editor').getEditedPostAttribute('meta') || {};
      wp.data.dispatch('core/editor').editPost({
        meta: {
          ...currentMeta,
          [fieldName]: value
        }
      });
    };

    const syncFeaturedMedia = (targetId, attachmentId) => {
      if (!hasBlockEditorStore || targetId !== 'bis_news_image') return;

      wp.data.dispatch('core/editor').editPost({
        featured_media: attachmentId ? Number(attachmentId) : 0
      });
    };

    const updateBadge = (checkbox) => {
      const badge = $('[data-featured-badge]');
      if (!badge.length) return;

      if (checkbox.is(':checked')) {
        badge.addClass('is-featured').text('Ключевой проект');
      } else {
        badge.removeClass('is-featured').text('Обычный проект');
      }
    };

    const updatePreview = (preview, url) => {
      if (!preview || !preview.length) return;

      if (url) {
        preview.css('background-image', `url('${url}')`);
        preview.removeClass('is-empty').find('.bis-project-media__placeholder').remove();
        return;
      }

      preview.css('background-image', 'none').addClass('is-empty');
      if (!preview.find('.bis-project-media__placeholder').length) {
        preview.append('<span class="bis-project-media__placeholder">Нет изображения</span>');
      }
    };

    const syncFieldFromElement = (element) => {
      const fieldName = element.data('meta-field');
      if (!fieldName) return;
      const value = element.is(':checkbox') ? (element.is(':checked') ? '1' : '0') : (element.val() || '');
      syncMetaField(fieldName, value);
    };

    const clearAttachmentField = (input) => {
      const attachmentTarget = input.data('attachment-target');
      if (!attachmentTarget) return null;

      const attachmentInput = $('#' + attachmentTarget);
      if (!attachmentInput.length) return null;

      attachmentInput.val('').trigger('input').trigger('change');
      syncFieldFromElement(attachmentInput);

      return attachmentInput;
    };

    const openMediaFrame = (title, multiple, callback) => {
      const frame = wp.media({
        title,
        multiple,
        library: { type: 'image' }
      });

      frame.on('select', function () {
        const selection = frame.state().get('selection');
        if (multiple) {
          selection.each(function (attachment) {
            callback(attachment.toJSON().url);
          });
          return;
        }

        callback(selection.first().toJSON());
      });

      frame.open();
    };

    $('.bis-project-image-upload').on('click', function (e) {
      e.preventDefault();
      const button = $(this);
      const targetId = button.data('target');
      const input = $('#' + targetId);
      const preview = getPreview(targetId);

      openMediaFrame('Выберите изображение', false, (attachment) => {
        const url = attachment && attachment.url ? attachment.url : '';
        const attachmentTarget = button.data('attachment-target') || input.data('attachment-target');

        input.val(url).trigger('input').trigger('change');
        syncFieldFromElement(input);

        if (attachmentTarget) {
          const attachmentInput = $('#' + attachmentTarget);
          if (attachmentInput.length) {
            attachmentInput.val(attachment && attachment.id ? attachment.id : '').trigger('input').trigger('change');
            syncFieldFromElement(attachmentInput);
          }
        }

        syncFeaturedMedia(targetId, attachment && attachment.id ? attachment.id : 0);
        updatePreview(preview, url);
      });
    });

    $('.bis-project-image-clear').on('click', function (e) {
      e.preventDefault();
      const button = $(this);
      const targetId = button.data('target');
      const input = $('#' + targetId);
      const preview = getPreview(targetId);

      input.val('').trigger('input').trigger('change');
      syncFieldFromElement(input);
      clearAttachmentField(input);
      syncFeaturedMedia(targetId, 0);
      updatePreview(preview, '');
    });

    $('[data-image-input]').on('input', function () {
      const input = $(this);
      const targetId = input.data('preview-target') || input.attr('id');

      syncFieldFromElement(input);
      clearAttachmentField(input);
      updatePreview(getPreview(targetId), input.val());
    });

    $('[data-meta-field]').not('[data-image-input]').on('input change', function () {
      syncFieldFromElement($(this));
    });

    const addGalleryItem = (url) => {
      if (!galleryList.length || !galleryTemplate.length || !url) return;

      const item = $(galleryTemplate.html());
      item.find('.bis-project-gallery-thumb').css('background-image', `url('${url}')`);
      item.find('input[type="hidden"]').attr('name', 'bis_project_gallery[]').val(url);
      galleryList.append(item);
    };

    $('#bis-project-gallery-add').on('click', function (e) {
      e.preventDefault();
      openMediaFrame('Выберите изображения галереи', true, (url) => {
        addGalleryItem(url);
      });
    });

    $('#bis-project-gallery-add-url').on('click', function (e) {
      e.preventDefault();
      const urlInput = $('#bis-project-gallery-url');
      if (!urlInput.length) return;

      const url = urlInput.val().trim();
      if (!url) return;

      addGalleryItem(url);
      urlInput.val('');
    });

    if (galleryList.length) {
      galleryList.on('click', '.bis-project-gallery-remove', function () {
        $(this).closest('.bis-project-gallery-item').remove();
      });

      if (galleryList.sortable) {
        galleryList.sortable({
          handle: '.handle'
        });
      }
    }

    $('[data-featured-toggle]').on('change', function () {
      updateBadge($(this));
    });

    updateBadge($('[data-featured-toggle]'));

    $('[data-image-input]').each(function () {
      const input = $(this);
      const targetId = input.data('preview-target') || input.attr('id');
      updatePreview(getPreview(targetId), input.val());
    });

    // FAQ Builder in Services
    const faqList = $('#bis-service-faq-list');
    const faqTemplate = $('#bis-service-faq-template');
    const faqEmpty = $('#bis-service-faq-empty');

    const updateFaqNumbers = () => {
      if (!faqList.length) return;
      faqList.children('.bis-faq-item').each(function (idx) {
        $(this).find('.bis-faq-item__num').text(idx + 1);
      });
      if (faqList.children('.bis-faq-item').length === 0) {
        faqEmpty.show();
      } else {
        faqEmpty.hide();
      }
    };

    const triggerGutenbergDirty = () => {
      if (hasBlockEditorStore) {
        const currentMeta = wp.data.select('core/editor').getEditedPostAttribute('meta') || {};
        wp.data.dispatch('core/editor').editPost({
          meta: {
            ...currentMeta,
            bis_service_faq_dirty: String(Date.now())
          }
        });
      }
    };

    $('#bis-service-faq-add').on('click', function (e) {
      e.preventDefault();
      if (!faqList.length || !faqTemplate.length) return;

      const newIndex = 'item_' + Date.now();
      const currentCount = faqList.children('.bis-faq-item').length;
      let html = faqTemplate.html()
        .replace(/__INDEX__/g, newIndex)
        .replace(/__NUM__/g, currentCount + 1);

      const newItem = $(html);
      faqList.append(newItem);
      updateFaqNumbers();
      newItem.find('input[type="text"]').first().trigger('focus');
      triggerGutenbergDirty();
    });

    if (faqList.length) {
      faqList.on('click', '.bis-faq-item__remove', function (e) {
        e.preventDefault();
        $(this).closest('.bis-faq-item').remove();
        updateFaqNumbers();
        triggerGutenbergDirty();
      });

      faqList.on('input change', 'input, textarea', function () {
        triggerGutenbergDirty();
      });

      if (faqList.sortable) {
        faqList.sortable({
          handle: '.bis-faq-item__handle',
          axis: 'y',
          opacity: 0.8,
          update: function () {
            updateFaqNumbers();
            triggerGutenbergDirty();
          }
        });
      }
    }

    // Child Services Searchable Dropdown
    const childrenContainer = $('#bis-children-search-select');
    if (childrenContainer.length) {
      const trigger = $('#bis-children-select-trigger');
      const searchInput = $('#bis-children-search-input');
      const dropdown = $('#bis-children-dropdown');
      const selectedList = $('#bis-children-selected-list');
      const countEl = $('#bis-children-count');
      const emptyEl = $('#bis-children-empty');
      const noResultsEl = dropdown.find('.bis-search-select__no-results');

      const updateChildrenCount = () => {
        const count = selectedList.children('.bis-child-tag').length;
        countEl.text(count);
        if (count === 0) {
          emptyEl.show();
        } else {
          emptyEl.hide();
        }
      };

      const openDropdown = () => {
        trigger.addClass('is-open');
        dropdown.show();
      };

      const closeDropdown = () => {
        trigger.removeClass('is-open');
        dropdown.hide();
        searchInput.val('');
        filterOptions('');
      };

      const filterOptions = (query) => {
        const q = (query || '').trim().toLowerCase();
        let totalVisible = 0;

        dropdown.find('.bis-search-select__group').each(function () {
          const group = $(this);
          const options = group.find('.bis-search-select__option');
          let groupVisible = 0;

          options.each(function () {
            const opt = $(this);
            const title = (opt.data('title') || '').toString().toLowerCase();
            const badge = (opt.data('badge') || '').toString().toLowerCase();

            if (!q || title.indexOf(q) !== -1 || badge.indexOf(q) !== -1) {
              opt.show();
              groupVisible++;
              totalVisible++;
            } else {
              opt.hide();
            }
          });

          if (groupVisible > 0) {
            group.show();
          } else {
            group.hide();
          }
        });

        if (totalVisible === 0 && q) {
          noResultsEl.show();
        } else {
          noResultsEl.hide();
        }
      };

      // Toggle dropdown on click
      trigger.on('click', function (e) {
        if (!trigger.hasClass('is-open')) {
          openDropdown();
          searchInput.trigger('focus');
        } else if (e.target !== searchInput[0]) {
          closeDropdown();
        }
      });

      searchInput.on('focus', function () {
        openDropdown();
      });

      searchInput.on('input', function () {
        openDropdown();
        filterOptions($(this).val());
      });

      // Close dropdown when clicking outside
      $(document).on('click', function (e) {
        if (!$(e.target).closest('#bis-children-search-select').length) {
          closeDropdown();
        }
      });

      // Handle item selection/deselection
      dropdown.on('click', '.bis-search-select__option', function (e) {
        e.preventDefault();
        const opt = $(this);

        // Reset all
        if (opt.data('action') === 'reset') {
          selectedList.empty();
          dropdown.find('.bis-search-select__option').removeClass('is-selected').find('.bis-search-select__check').text('');
          updateChildrenCount();
          triggerGutenbergDirty();
          closeDropdown();
          return;
        }

        const id = opt.data('id');
        const title = opt.data('title');
        const badge = opt.data('badge');

        if (opt.hasClass('is-selected')) {
          // Deselect
          opt.removeClass('is-selected');
          opt.find('.bis-search-select__check').text('');
          selectedList.find(`.bis-child-tag[data-id="${id}"]`).remove();
        } else {
          // Select
          opt.addClass('is-selected');
          opt.find('.bis-search-select__check').text('✓');
          const tagHtml = `
            <div class="bis-child-tag" data-id="${id}">
              <span class="dashicons dashicons-menu bis-child-tag__handle" title="Перетащите для изменения порядка"></span>
              <span class="bis-child-tag__title">${$('<div/>').text(title).html()}</span>
              <span class="bis-child-tag__badge">${$('<div/>').text(badge).html()}</span>
              <button type="button" class="bis-child-tag__remove" title="Удалить" aria-label="Удалить">&times;</button>
              <input type="hidden" name="bis_service_children[]" value="${id}">
            </div>`;
          selectedList.append(tagHtml);
        }

        updateChildrenCount();
        triggerGutenbergDirty();
      });

      // Handle "Выбрать раздел" in group header
      dropdown.on('click', '.bis-search-select__group-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const group = $(this).closest('.bis-search-select__group');
        const options = group.find('.bis-search-select__option');
        const unselected = options.filter(':not(.is-selected)');

        if (unselected.length > 0) {
          // Select all in group
          unselected.each(function () {
            const opt = $(this);
            const id = opt.data('id');
            const title = opt.data('title');
            const badge = opt.data('badge');

            opt.addClass('is-selected');
            opt.find('.bis-search-select__check').text('✓');
            if (!selectedList.find(`.bis-child-tag[data-id="${id}"]`).length) {
              const tagHtml = `
                <div class="bis-child-tag" data-id="${id}">
                  <span class="dashicons dashicons-menu bis-child-tag__handle" title="Перетащите для изменения порядка"></span>
                  <span class="bis-child-tag__title">${$('<div/>').text(title).html()}</span>
                  <span class="bis-child-tag__badge">${$('<div/>').text(badge).html()}</span>
                  <button type="button" class="bis-child-tag__remove" title="Удалить" aria-label="Удалить">&times;</button>
                  <input type="hidden" name="bis_service_children[]" value="${id}">
                </div>`;
              selectedList.append(tagHtml);
            }
          });
        } else {
          // Deselect all in group
          options.each(function () {
            const opt = $(this);
            const id = opt.data('id');
            opt.removeClass('is-selected');
            opt.find('.bis-search-select__check').text('');
            selectedList.find(`.bis-child-tag[data-id="${id}"]`).remove();
          });
        }

        updateChildrenCount();
        triggerGutenbergDirty();
      });

      // Handle tag remove button
      selectedList.on('click', '.bis-child-tag__remove', function (e) {
        e.preventDefault();
        const tag = $(this).closest('.bis-child-tag');
        const id = tag.data('id');

        tag.remove();
        const opt = dropdown.find(`.bis-search-select__option[data-id="${id}"]`);
        opt.removeClass('is-selected');
        opt.find('.bis-search-select__check').text('');

        updateChildrenCount();
        triggerGutenbergDirty();
      });

      // Drag and drop sorting
      if (selectedList.sortable) {
        selectedList.sortable({
          handle: '.bis-child-tag__handle',
          axis: 'y',
          opacity: 0.8,
          update: function () {
            triggerGutenbergDirty();
          }
        });
      }
    }
  });
})(jQuery);
