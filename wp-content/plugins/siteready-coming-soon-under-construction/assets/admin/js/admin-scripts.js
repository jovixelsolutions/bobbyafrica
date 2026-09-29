// Example JS for the admin settings page
document.addEventListener('DOMContentLoaded', function () {
    console.log('Admin page loaded');
    jQuery(document).ready(function($) {
    $('.sruc-color-field').wpColorPicker();
});
});


jQuery(function ($) {

    $(document).on('click', '.sruc-logo-upload', function (e) {
        e.preventDefault();

        const $wrap = $(this).closest('.sruc-logo-field');

        const frame = wp.media({
            title: srucMedia.title,
            button: { text: srucMedia.button },
            library: { type: 'image' },
            multiple: false
        });

        frame.on('select', function () {
            const att = frame.state().get('selection').first().toJSON();
            $wrap.find('.sruc-logo-url').val(att.url);
            $wrap.find('.sruc-logo-preview').attr('src', att.url).show();
            $wrap.find('.sruc-logo-remove').show();
        });

        frame.open();
    });

    $(document).on('click', '.sruc-logo-remove', function (e) {
        e.preventDefault();

        const $wrap = $(this).closest('.sruc-logo-field');
        $wrap.find('.sruc-logo-url').val('');
        $wrap.find('.sruc-logo-preview').attr('src', '').hide();
        $(this).hide();
    });

});
