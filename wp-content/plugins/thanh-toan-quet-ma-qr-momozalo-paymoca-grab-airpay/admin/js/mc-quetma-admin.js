jQuery(document).ready(function(){
	jQuery(".uploadinput").each(function(i, e){
        jQuery(e).hide();
		jQuery(e).after('<div class="mc-preview-qrcode"><img id="placeholder_'+jQuery(e).attr('name')+'" src="'+jQuery(e).val()+'"></div>');
        jQuery(e).after('<input type="button" class="btnUploadForInput button button-primary button-large" data-forinput="'+ jQuery(e).attr('name') +'" value="Chọn Hình">');
	});

    if(jQuery( ".mc_guide_accordion" ).length > 0 ){
        jQuery( ".mc_guide_accordion" ).accordion({
            active: false,
            collapsible: true            
        });
    }
    

	jQuery(document).on('click', '.btnUploadForInput', function(e) {
        e.preventDefault();
        //var button = jQuery(this);
        var inputName = jQuery(this).data('forinput');
        

        var custom_uploader = wp.media({
            title: 'Chọn Hình QR Code',
            button: {
                text: 'Chọn'
            },
            multiple: false  // Set this to true to allow multiple files to be selected
        })
        .on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            //$('.custom_media_image').attr('src', attachment.url);
            //$('.custom_media_url').val(attachment.url);
            //$('.custom_media_id').val(attachment.id);
            jQuery('input[name="'+inputName+'"]').val(attachment.url);           
            jQuery('#placeholder_'+inputName).attr('src',attachment.url); 
        })
        .open();

        return false;
    });
    
});