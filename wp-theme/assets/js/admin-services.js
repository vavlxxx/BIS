/**
 * BIS Service Order Management in WP Admin Table
 */
(function($) {
    'use strict';

    $(function() {
        var config = window.bisServiceOrderConfig || {};
        if (!config.ajaxUrl || !config.nonce) {
            return;
        }

        var $tableBody = $('#the-list');
        if (!$tableBody.length) {
            return;
        }

        function setStatus($cell, state) {
            var $status = $cell.find('.bis-service-order-status');
            $status.removeClass('is-saving is-error dashicons-update dashicons-yes-alt dashicons-warning');

            if (state === 'saving') {
                $status.addClass('is-saving dashicons-update').show();
            } else if (state === 'success') {
                $status.addClass('dashicons-yes-alt').show();
                setTimeout(function() {
                    $status.fadeOut(400);
                }, 1800);
            } else if (state === 'error') {
                $status.addClass('is-error dashicons-warning').show();
            } else {
                $status.hide();
            }
        }

        function saveSingleOrder($input) {
            var $cell = $input.closest('.bis-service-order-cell');
            var postId = parseInt($cell.data('post-id'), 10);
            var orderVal = parseInt($input.val(), 10);

            if (isNaN(orderVal) || orderVal < 0) {
                orderVal = 0;
                $input.val(0);
            }

            setStatus($cell, 'saving');

            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'bis_update_service_order',
                    nonce: config.nonce,
                    post_id: postId,
                    menu_order: orderVal
                },
                success: function(response) {
                    if (response && response.success) {
                        setStatus($cell, 'success');
                    } else {
                        setStatus($cell, 'error');
                    }
                },
                error: function() {
                    setStatus($cell, 'error');
                }
            });
        }

        // Input change and Enter key
        $tableBody.on('change', '.bis-service-order-input', function() {
            saveSingleOrder($(this));
        });

        $tableBody.on('keydown', '.bis-service-order-input', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $(this).blur();
            }
        });

        // Up/Down buttons
        $tableBody.on('click', '.bis-order-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var $input = $btn.closest('.bis-service-order-input-wrap').find('.bis-service-order-input');
            var currentVal = parseInt($input.val(), 10) || 0;

            if ($btn.hasClass('bis-order-btn--up')) {
                currentVal = Math.max(0, currentVal - 1);
            } else if ($btn.hasClass('bis-order-btn--down')) {
                currentVal = currentVal + 1;
            }

            $input.val(currentVal);
            saveSingleOrder($input);
        });

        // Helper to fix widths during jQuery UI sortable dragging
        var fixHelper = function(e, ui) {
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        };

        // Initialize drag-and-drop row sorting
        if ($.fn.sortable) {
            $tableBody.sortable({
                handle: '.bis-service-drag-handle',
                items: 'tr[id^="post-"]',
                helper: fixHelper,
                placeholder: 'ui-sortable-placeholder',
                axis: 'y',
                opacity: 0.85,
                update: function() {
                    var orders = {};
                    var index = 0;

                    $tableBody.find('tr[id^="post-"]').each(function() {
                        var $row = $(this);
                        var $cell = $row.find('.bis-service-order-cell');
                        var postId = parseInt($cell.data('post-id'), 10);
                        var $input = $cell.find('.bis-service-order-input');

                        if (postId) {
                            $input.val(index);
                            orders[postId] = index;
                            setStatus($cell, 'saving');
                            index++;
                        }
                    });

                    $.ajax({
                        url: config.ajaxUrl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'bis_reorder_services',
                            nonce: config.nonce,
                            orders: orders
                        },
                        success: function(response) {
                            $tableBody.find('.bis-service-order-cell').each(function() {
                                if (response && response.success) {
                                    setStatus($(this), 'success');
                                } else {
                                    setStatus($(this), 'error');
                                }
                            });
                        },
                        error: function() {
                            $tableBody.find('.bis-service-order-cell').each(function() {
                                setStatus($(this), 'error');
                            });
                        }
                    });
                }
            });
        }
    });
})(jQuery);
