/* global jQuery */

(function($){

    var width_option_list = [];
    var width_bar_list = [];
    var max_width_option, max_width_bar;

    $( '.option_group' ).each(function( index ) {
        width_option_list[index] = $( this ).find('.option').width();
        width_bar_list[index] = $( this ).find('.bar').width();
    });
  
    max_width_option = Math.max.apply(Math,width_option_list);
    max_width_bar = Math.max.apply(Math,width_bar_list);

    $('.option').css({width : max_width_option+'px'});
    /* not required after introduction of datatables, but keeping for future reference.
    $('.bar').css({width : max_width_bar+'px'});
    $('.option_thead').css({width : (max_width_bar+max_width_option)+'px'});
    $('.option_group').css({width : (max_width_bar+max_width_option)+'px'});
    */

    $link_col = $('table.cwppose_reviews_table th').length - 1;
    $col_defs = [];
    $col_defs.push({ 'orderable': false, 'targets': 0 });
    $col_defs.push({ 'orderable': false, 'targets': $link_col });
    if($('table th.wppr-col-options').length === 1){
        $col_defs.push({ 'orderable': false, 'targets': $('table th.wppr-col-options').attr('data-col-index') - 1 });
    }

    $table = $('table.cwppose_reviews_table').DataTable({
        columnDefs: $col_defs,
        paging: false,
        ordering: true,
        select: false,
        info: false,
        lengthChange: false,
        responsive: false,
        searching: false
    });

    $('table.cwppose_reviews_table').stacktable();
    
    // extend.
    $('body').trigger('wppr-comparison-table-render', { table: $table });

})(jQuery);