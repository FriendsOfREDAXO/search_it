(function (jQuery) {
    jQuery(document).ready(function () {


        // ajax request for sample-text
        jQuery('#search_it_highlight').change(function () {
            jQuery.get('index.php?page=search_it&ajax=sample&type=' + jQuery('#search_it_highlight').val(), {}, function (data) {
                jQuery('#search_it_sample').html(data);
            });
        });


        // open datebase tables
        var active_tables = jQuery();
        jQuery('.include_checkboxes').each(function (i, elem) {
            if (jQuery('.checkbox input:checked', elem).length > 0) {
                active_tables = active_tables.add(jQuery('header', elem));
            }
        });
        jQuery.each(active_tables, function (i, elem) {
                var pe = jQuery(elem).parent();
                pe.removeClass('panel-info');
                pe.addClass('panel-edit');
            }
        );
        active_tables.click();


    });
})(jQuery);
