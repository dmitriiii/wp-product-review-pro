/* global sr */

(function($, sr) {

    $(document).ready( function() {
        var file_frame;
        
        // attach a click event (or whatever you want) to some element on your page
        $( '#image-button' ).on( 'click', function( event ) {
            event.preventDefault();

            // if the file_frame has already been created, just reuse it
            if ( file_frame ) {
                file_frame.open();
                return;
            } 

            file_frame = wp.media.frames.file_frame = wp.media({
                title: $( this ).data( 'uploader_title' ),
                button: {
                    text: $( this ).data( 'uploader_button_text' ),
                },
                multiple: false // set this to true for multiple file selection
            });

            file_frame.on( 'select', function() {
                attachment = file_frame.state().get('selection').first().toJSON();
                $( '#image' ).val(attachment.url);
            });

            file_frame.open();
        });

        $('.wppr-submit-review-bttn').closest('form').submit(function(){
            var form = $(this);
            form.closest('.wppr-submit-review-wrap').css( 'opacity', 0.5 );
            form.find('.wppr-spinner').addClass('is-active');

            $.ajax({
                url         : sr.url,
                beforeSend  : function(xhr){
                    xhr.setRequestHeader('X-WP-Nonce', sr.nonce);
                },
                data        : form.serialize(),
                method      : 'POST',
                success     : function(){
                    form.find('.wppr-spinner').removeClass('is-active');
                    form.closest('.wppr-submit-review-wrap').css( 'opacity', 1 );
                    form.closest('.wppr-submit-review-wrap').html(form.find('.wppr-submit-review-msg').val());
                }
            });

            return false;
        });
    });

})(jQuery, sr);