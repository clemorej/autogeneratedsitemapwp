jQuery('#custom_page_add_row').click(function (){
    jQuery('#custom_page_body').append('<tr> ' +
        '<td><input name=\"sitemap_custom_page_url[]\" type=\"text\" style=\"width: 100%;\"></td> ' +
        '<td><input name=\"sitemap_custom_page_date[]\" type=\"datetime-local\" style=\"width: 100%;\"></td>' +
        '<td><a href=\"javascript:void(0)\" onclick=\"jQuery(this).parent().parent().remove()\" style=\"color: red\">X</a></td>' +
        ' </tr>');
});

jQuery(document).ready(function(){

    jQuery('#custom_page_body').sortable();

    jQuery('#excluded_categories').select2();

    jQuery('#excluded_posts').select2();

});

