jQuery(document).ready(function($){
    function setupImagePicker(rowid) {
        var button = $('#select-image-' + rowid);
        var input = $('#event_image_' + rowid);
        var preview = $('#event-image-preview-' + rowid);
        var frame;
        button.on('click', function(e){
            e.preventDefault();
            if (frame) {
                frame.open();
                return;
            }
            frame = wp.media({
                title: 'Select or Upload Image',
                button: { text: 'Use this image' },
                multiple: false
            });
            frame.on('select', function(){
                var attachment = frame.state().get('selection').first().toJSON();
                input.val(attachment.id);
                preview.html('<img src="'+attachment.url+'" style="max-width:128px;">');
            });
            frame.open();
        });
    }
    // Call setupImagePicker for each image field you output, e.g.:
    setupImagePicker('ROWID');
});

document.addEventListener("DOMContentLoaded", function() {
    // Remove this line after testing
    const btn = document.createElement("button");
    btn.innerText = "Debug";
    btn.style.position = "fixed";
    btn.style.bottom = "24px";
    btn.style.right = "24px";
    btn.style.zIndex = "99999";
    document.body.appendChild(btn);
});
