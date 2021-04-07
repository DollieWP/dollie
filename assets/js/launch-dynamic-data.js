var Dollie = Dollie || {};

(function ($) {

    "use strict";

    Dollie.dynamicData = {
        selectors: {
            wrapper: $('.af-field-site-blueprint'),
            input: $('.af-field-site-blueprint .acf-radio-list input[type="radio"]'),
            fieldsWrapper: '.wpd-blueprint-dynamic'
        },
        $body: $('body'),
        init: function () {

            var _this = this;
            this.selectors.input.on('change', function (e) {
                _this.registerChangeAction(e)
            });

        },

        registerChangeAction: function (e) {

            var _this = this;
            if ($('body').hasClass('customize-preview')) {
                return false;
            }

            if (_this.selectors.wrapper.next(_this.selectors.fieldsWrapper).length === 0) {

                _this.selectors.wrapper.after('<div class="wpd-blueprint-dynamic acf-fields"></div>')
            }

            var currentVal = $(e.target).val();

            if (currentVal == 0) {
                $(_this.selectors.fieldsWrapper).html('');
                return;
            }

            var values = 'action=dollie_launch_site_blueprint_data&blueprint=' + currentVal;

            $.ajax({
                url: wpdDynamicData.ajaxurl,
                type: "POST",
                dataType: "json",
                data: values,
                success: function (response) {

                    if (response === null) {
                        $(_this.selectors.fieldsWrapper).html('');
                        return;
                    }

                    if (response.success === true && response.hasOwnProperty('data')) {


                        $(_this.selectors.fieldsWrapper).html(response.data.fields);

                    }
                },
            });
        }
    }

    $(document).ready(function () {
        Dollie.dynamicData.init();
    });

})(jQuery);
