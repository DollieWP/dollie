(function ($) {

    var selectors = {
        templateTypeInput: '#elementor-new-template__form__template-type',
        typeWrapper: '#elementor-new-template__form__template-dol__wrapper'
    };

    var elements = {
        $templateTypeInput: null,
        $typeWrapper: null
    };


    var setLocationFieldVisibility = function setLocationFieldVisibility() {
        elements.$typeWrapper.toggle('dollie-templates' === elements.$templateTypeInput.val() );
    };

    var setElements = function setElements() {
        jQuery.each(selectors, function (key, selector) {
            key = '$' + key;
            elements[key] = elementorNewTemplate.layout.getModal().getElements('content').find(selector);
        });
    };


   function init() {

        if (!window.elementorNewTemplate) {
            return;
        }

        // Make sure the modal has already been initialized
        elementorNewTemplate.layout.getModal();

        setElements();

        setLocationFieldVisibility();

        $('body').on('change', selectors.templateTypeInput , setLocationFieldVisibility);

    }

    jQuery(setTimeout.bind(window, init));


})(jQuery);
