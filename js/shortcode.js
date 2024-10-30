(function ($) {
    //Tinymce
    tinymce.create('tinymce.plugins.mo_product_listing', {
        init : function(ed, url) {
            ed.addCommand('mo_command', function() {
                var shortCode = '[mo_product_listing id=]';
                ed.execCommand('mceInsertContent', 0, shortCode);

            });
            ed.addButton('mo_product_listing_button', {
                title : 'Insert product listing',
                image: url + '/../images/fav_mo64.png' ,
                cmd: 'mo_command'
            });
        },
    });

    tinymce.PluginManager.add('mo_product_listing_button', tinymce.plugins.mo_product_listing);
})(jQuery);

