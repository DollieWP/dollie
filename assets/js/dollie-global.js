var DollieGlobal = DollieGlobal || {};

(function ($) {
    // USE STRICT
    "use strict";

    // DollieGlobal.vars = {
    //     ajax_url: false,
    //     nonce: false,
    //     reloaded: false,
    //     body: {},
    // };

    DollieGlobal.fn = {
        init: function () {
            DollieGlobal.fn.initModalGlobal();
        },

        initModalGlobal: function () {
                $(".dol-global-modal").on("click", function (e) {
                e.preventDefault();

                var modalId = $(this).data("modal-id");
                if (!modalId) {
                    return false;
                }

                $("#" + modalId).addClass("dol-modal-visible");
            });

            $(".dol-modal-close").on("click", function (e) {
                e.preventDefault();

                var modal = $(this).closest(".dol-custom-modal");

                if (!modal) {
                    return false;
                }

                modal.removeClass("dol-modal-visible");

            });
        }
    };

    $(document).ready(DollieGlobal.fn.init);
})(jQuery);
