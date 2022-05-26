var DollieSiteContent = DollieSiteContent || {};

(function ($) {
    // USE STRICT
    "use strict";

    DollieSiteContent.vars = {
        ajax_url: false,
        nonce: false,
        reloaded: false,
        body: {},
    };

    DollieSiteContent.fn = {
        init: function () {
            DollieSiteContent.fn.checkDynamicFields();
            DollieSiteContent.fn.deploy();
            DollieSiteContent.fn.initStaging();
            DollieSiteContent.fn.initExecution();
            DollieSiteContent.fn.initDns();
        },

        checkDynamicFields: function () {
            var notifications = $("#dol-blueprint-notification");

            if (notifications.length) {
                var hasDynamicFields = notifications.data("dynamic-fields");

                if (hasDynamicFields) {
                    notifications.removeClass("dol-hidden");

                    $.ajax({
                        method: "POST",
                        url: notifications.data("ajax-url"),
                        data: {
                            container: notifications.data("container"),
                            action: "dollie_check_dynamic_fields",
                            nonce: notifications.data("nonce"),
                        },
                        context: $(this),
                        success: function (response) {
                            if (response.success) {
                                notifications.html(response.data.output);
                            }
                        },
                    });
                }
            }
        },

        deploy: function () {
            var deploy = $("#dol-deploying-site");

            if (deploy.length) {
                var container = deploy.data("container");

                if (container) {
                    DollieSiteContent.vars.ajax_url = deploy.data("ajax-url");

                    DollieSiteContent.vars.body = {
                        container: container,
                        staging: deploy.data("staging") == 1,
                        action: "dollie_check_deploy",
                        nonce: deploy.data("nonce"),
                    };

                    setInterval(function () {
                        if (!DollieSiteContent.vars.reloaded) {
                            DollieSiteContent.fn.sendRequest();
                        }
                    }, 6000);
                }
            }
        },

        sendRequest: function () {
            $.ajax({
                method: "POST",
                url: DollieSiteContent.vars.ajax_url,
                data: DollieSiteContent.vars.body,
                context: $(this),
                success: function (response) {
                    if (response.success) {
                        DollieSiteContent.vars.reloaded = true;

                        setTimeout(function () {
                            if (response.hasOwnProperty('data') && response.data.hasOwnProperty('redirect') && response.data.redirect !== '') {
                                console.log(response.data.redirect);
                                window.location.replace(response.data.redirect);
                            } else {
                                location.reload();
                            }
                        }, 5000);
                    }
                },
            });
        },

        initStaging: function () {
            $("#dol-delete-staging").on("submit", function () {
                var submit = confirm("Do you really want to delete staging?");

                return submit;
            });

            $("#dol-sync-staging").on("submit", function () {
                var submit = confirm(
                    "Do you really want to overwrite your live site with your staging site? This will apply the changes you made to your staging site to your live site."
                );

                return submit;
            });
        },

        initExecution: function () {
            var execution = $("#dol-execution-check");

            if (execution.length) {
                DollieSiteContent.vars.ajax_url = execution.data("ajax-url");

                DollieSiteContent.vars.body = {
                    container: execution.data("container"),
                    type: execution.data("type"),
                    action: "dollie_check_execution",
                    nonce: execution.data("nonce"),
                };

                setInterval(function () {
                    if (!DollieSiteContent.vars.reloaded) {
                        DollieSiteContent.fn.sendRequest();
                    }
                }, 5000);
            }
        },

        initDns: function () {
            $(".dol-dns-tabs .dol-dns-menu-item").on("click", function () {
                if ($(this).hasClass("dol-dns-menu-item-active")) {
                    return false;
                }

                $(".dol-dns-tabs .dol-dns-menu-item").each(function (index, item) {
                    $(item).removeClass("dol-dns-menu-item-active");
                });

                $(this).addClass("dol-dns-menu-item-active");

                $(".dol-dns-tabs-content .dol-dns-tab").each(function (index, item) {
                    $(item).removeClass("dol-dns-tab-active");
                });

                $($(this).data("tab")).addClass("dol-dns-tab-active");
            });

            $(".dol-dns-record-form").on("submit", function (e) {
                e.preventDefault();

                var loader = $(this)
                    .parent()
                    .parent()
                    .find(".dol-loader[data-for='add-dns-records']");

                $.ajax({
                    method: "POST",
                    url: $(this).attr("action"),
                    data: {
                        data: $(this).serialize(),
                        action: "dollie_create_record",
                        nonce: $(this).data("nonce"),
                    },
                    dataType: "json",
                    context: $(this),
                    beforeSend: function () {
                        if (loader.length) {
                            loader.show();
                        }
                    },
                    success: function (response) {
                        if (loader.length) {
                            loader.hide();
                        }

                        if (response.success) {
                            $("#dol-dns-manager-list").html(response.data);
                            $(this).trigger("reset");
                        }
                    },
                    error: function (request, status, error) {
                        if (loader.length) {
                            loader.hide();
                        }
                    },
                });
            });

            $(document).on("click", ".dol-dns-record-remove", function (e) {
                e.preventDefault();

                var loader = $(this)
                    .parent()
                    .parent()
                    .find(".dol-loader[data-for='remove-dns-records']");

                $.ajax({
                    method: "POST",
                    url: $(this).data("ajax-url"),
                    data: {
                        record_id: $(this).data("record-id"),
                        container_id: $(this).data("container-id"),
                        action: "dollie_remove_record",
                        nonce: $(this).data("nonce"),
                    },
                    dataType: "json",
                    context: $(this),
                    beforeSend: function () {
                        if (loader.length) {
                            loader.show();
                        }
                    },
                    success: function (response) {
                        if (loader.length) {
                            loader.hide();
                        }

                        if (response.success) {
                            $(this)
                                .closest(".dol-dns-record-item")
                                .fadeOut(300, function () {
                                    $(this).remove();
                                });
                        }
                    },
                    error: function (request, status, error) {
                        if (loader.length) {
                            loader.hide();
                        }
                    },
                });
            });
        },
    };

    $(document).ready(DollieSiteContent.fn.init);
})(jQuery);
