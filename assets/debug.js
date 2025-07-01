jQuery(document).ready(function($){
    $(document).on('click', 'a[id^="select-"]', function(e){
        e.preventDefault();
        var fieldId = $(this).attr('id').replace('select-','');
        var custom_uploader = wp.media({
            title: 'Select Image',
            button: { text: 'Use this image' },
            multiple: false
        }).on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#' + fieldId).val(attachment.id);
            $('#preview-' + fieldId).html(
                '<a href="#" id="select-' + fieldId + '" title="Change image">' +
                '<img src="' + attachment.url + '" style="max-width:96px;max-height:96px;">' +
                '</a>'
            );
        }).open();
    });
});
