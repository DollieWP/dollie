var DollieSiteContent = DollieSiteContent || {};

(function ($) {

    // USE STRICT
    "use strict";

    DollieSiteContent.vars = {
        container: false,
        ajax_url: false,
        nonce: false,
        reloaded: false
    };

    DollieSiteContent.fn = {

        init: function () {
            DollieSiteContent.fn.deploy();
        },

        deploy: function () {
            var deploy = $('#dol-deploying-site');

            if (deploy.length) {
                var container = deploy.data('container');

                if (container) {
                    DollieSiteContent.vars.container = container;
                    DollieSiteContent.vars.ajax_url = deploy.data('ajax-url');
                    DollieSiteContent.vars.nonce = deploy.data('nonce');

                    setInterval(function () {
                        if (!DollieSiteContent.vars.reloaded) {
                            DollieSiteContent.fn.checkDeploy();
                        }
                    }, 10000);
                }
            }
        },

        checkDeploy: function () {
            if (DollieSiteContent.vars.container) {
                $.ajax({
                    method: 'POST',
                    url: DollieSiteContent.vars.ajax_url,
                    data: {
                        container: DollieSiteContent.vars.container,
                        action: 'dollie_check_deploy',
                        nonce: DollieSiteContent.vars.nonce
                    },
                    context: $(this),
                    success: function (response) {
                        if (response.success) {
                            location.reload();
                            DollieSiteContent.vars.reloaded = true;
                        }
                    }
                });
            }
        }
    };

    $(document).ready(DollieSiteContent.fn.init);

})(jQuery);
