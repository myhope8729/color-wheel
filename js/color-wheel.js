jQuery(document).ready( function($) {

    jQuery('input#select-cw-background').click(function(e) {
        e.preventDefault();
        var image_frame;
        if(image_frame){
            image_frame.open();
        }
        // Define image_frame as wp.media object
        image_frame = wp.media({
            title: 'Select Background',
            multiple : false,
            library : {
                type : 'image',
            }
        });

        image_frame.on('close',function() {
            // On close, get selections and save to the hidden input
            // plus other AJAX stuff to refresh the image preview
            var selection =  image_frame.state().get('selection');
            var background_ids = new Array();
            var my_index = 0;
            selection.each(function(attachment) {
                background_ids[my_index] = attachment['id'];
                my_index++;
            });
            var ids = background_ids.join(",");
            jQuery('input#color-wheel-background').val(ids);
            Refresh_Background(ids);
        });

        image_frame.on('open',function() {
            // On open, get the id from the hidden input
            // and select the appropiate images in the media manager
            var selection =  image_frame.state().get('selection');
            var ids = jQuery('input#color-wheel-background').val().split(',');
            ids.forEach(function(id) {
                var attachment = wp.media.attachment(id);
                attachment.fetch();
                selection.add( attachment ? [ attachment ] : [] );
            });
        });
        
        image_frame.open();
    });

    jQuery('input#select-cw-masks').click(function(e) {
        e.preventDefault();
        var image_frame;
        if(image_frame){
            image_frame.open();
        }
        // Define image_frame as wp.media object
        image_frame = wp.media({
            title: 'Select Masks',
            multiple : true,
            library : {
                type : 'image',
            }
        });

        image_frame.on('close',function() {
            // On close, get selections and save to the hidden input
            // plus other AJAX stuff to refresh the image preview
            var selection =  image_frame.state().get('selection');
            var background_ids = new Array();
            var my_index = 0;
            selection.each(function(attachment) {
                if (attachment['id'] != '' && attachment['id'] != 0){
                    background_ids[my_index] = attachment['id'];
                    my_index++;
                }
            });
            var ids = background_ids.join(",");
            jQuery('input#color-wheel-masks').val(ids);
            Refresh_Masks(ids);
        });

        image_frame.on('open',function() {
            // On open, get the id from the hidden input
            // and select the appropiate images in the media manager
            var selection =  image_frame.state().get('selection');
            var ids = jQuery('input#color-wheel-masks').val().split(',');
            ids.forEach(function(id) {
                var attachment = wp.media.attachment(id);
                attachment.fetch();
                selection.add( attachment ? [ attachment ] : [] );
            });
        });
        
        image_frame.open();
    });

    $('.degree_slider').slider({
        reversed : true
    }).on('slide', function(){
        $(this).closest('.color-wheel-container').find('.background img').css('transform', 'rotate(' + $(this).data('slider').getValue() + 'deg)');
    }).on('change', function(){
        $(this).closest('.color-wheel-container').find('.background img').css('transform', 'rotate(' + $(this).data('slider').getValue() + 'deg)');
    });

    $('.mask').click(function(evt){
        var x = evt.pageX - $(this).offset().left;
        var y = evt.pageY - $(this).offset().top;
        $(this).closest('.color-wheel-wrapper').find('.mask-selector-wrapper').css({'left' : x + 'px', 'top' : y + 'px', 'visibility' : 'visible'}).show();
    });

    $('.mask-selector-wrapper button').click(function(){
        $(this).closest('.mask-selector-wrapper').css('visibility', 'hidden');
    });

    $('.mask-image').click(function(){
        $(this).siblings().removeClass('active');
        $(this).addClass('active');
        $(this).closest('.color-wheel-wrapper').find('.mask img').attr('src', $(this).data('img'));
        $(this).closest('.mask-selector-wrapper').css('visibility', 'hidden');
    });

});

// Ajax request to refresh the image preview
function Refresh_Background(the_id){
    var data = {
        action: 'cw_get_background',
        id: the_id
    };

    jQuery.get(ajaxurl, data, function(response) {
        if(response.success === true) {
            jQuery('#background-preview').html( response.data.image );
        }
    });
}

// Ajax request to refresh the image preview
function Refresh_Masks(the_id){
    var data = {
        action: 'cw_get_masks',
        id: the_id
    };

    jQuery.get(ajaxurl, data, function(response) {
        if(response.success === true) {
            jQuery("#masks-preview").html("");
            jQuery.each(response.data.images, function(key, image){
                jQuery("#masks-preview").append('<div class="mask-thumbnail">' + image + '</div>');
            });
        }
    });
}