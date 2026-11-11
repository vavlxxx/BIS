jQuery(function ($) {
    const list = $('#hero-slider-list');
    const renumber = () => list.children('li').each(function (index) {
        $(this).find('[data-hero-field]').each(function () {
            this.name = `hero_slides[${index}][${this.dataset.heroField}]`;
        });
    });
    list.sortable({ handle: '.handle', placeholder: 'ui-state-highlight', update: renumber });
    $('#hero-slider-form').on('submit', renumber);
    $('#add-hero-image').on('click', function () {
        if (list.children().length >= 12) return;
        list.append(document.querySelector('#hero-slide-template').content.cloneNode(true));
        renumber();
    });
    list.on('click', '.remove-image', function () { $(this).closest('li').remove(); renumber(); });
    list.on('click', '.choose-hero-image', function () {
        const card = $(this).closest('li');
        const frame = wp.media({ title: 'Фон слайда', multiple: false, library: { type: 'image' } });
        frame.on('select', function () {
            const image = frame.state().get('selection').first().toJSON();
            card.find('[data-hero-field="image"]').val(image.url);
            card.find('.image-preview').css('background-image', `url("${image.url.replace(/["\\]/g, '\\$&')}")`);
        });
        frame.open();
    });
});
