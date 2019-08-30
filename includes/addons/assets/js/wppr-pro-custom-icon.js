(function($) {

    $(document).ready(function(){
        onReady();
    });

    function onReady(){
        $('.cwp_bar_icon_field i').on('click', function(){
            $('.cwp_bar_icon_field i').removeClass('selected');
            $(this).addClass('selected');
            $('.cwp_bar_icon_field input[type="hidden"]').val($(this).attr('data-icon-value'));
            $('input[name="cwppos_bar_icon_type"]').val('dashicons');
        });
    }

})(jQuery);