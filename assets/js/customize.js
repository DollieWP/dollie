jQuery(window).load(function(){

    jQuery('#accordion-section-dollie_colors_section').click(function(event) {
        // Hard coded URL for testing purposes
        wp.customize.previewer.previewUrl('http://dollie.lcl/styleguide');
        wp.customize.previewer.refresh();
    });

});
