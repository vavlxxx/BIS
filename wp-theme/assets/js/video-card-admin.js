jQuery(document).ready(function($) {
    var mediaUploader;

    $('.upload-video-button').on('click', function(e) {
        e.preventDefault();

        var button = $(this);
        var container = button.closest('.video-upload-container');

        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Выберите видео',
            button: {
                text: 'Использовать это видео'
            },
            multiple: false,
            library: {
                type: 'video'
            }
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();

            container.find('.video-id').val(attachment.id);

            var previewVideo = container.find('.preview-video');
            previewVideo.attr('src', attachment.url);
            previewVideo.show();

            container.find('.remove-video-button').show();
        });

        mediaUploader.open();
    });

    $('.remove-video-button').on('click', function(e) {
        e.preventDefault();

        var container = $(this).closest('.video-upload-container');

        container.find('.video-id').val('');
        container.find('.preview-video').hide().attr('src', '');
        $(this).hide();
    });
});
