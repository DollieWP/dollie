var DollieSiteList = DollieSiteList || {};

(function ($) {
    // USE STRICT
    "use strict";

    DollieSiteList.vars = {
        actionInterval: false,
        selectedSites: [],
    };

    DollieSiteList.fn = {
        init: function () {
            DollieSiteList.fn.pagination();
            DollieSiteList.fn.search();
            DollieSiteList.fn.toggleView();
            DollieSiteList.fn.actionsAndFilters();
            DollieSiteList.fn.applyFilters();
            DollieSiteList.fn.sendBulkAction();
            DollieSiteList.fn.getBulkOptions();
            DollieSiteList.fn.checkBulkAction();
            DollieSiteList.fn.recurringAction();
        },

        actionsAndFilters: function () {
            $(".dol-select-all-container").on("change", function () {
                $(
                    ".dol-sites-item:not(.dol-sites-item-locked) input[type=checkbox]"
                ).prop("checked", $(this).prop("checked"));

                if ($(this).is(":checked")) {
                    $(".dol-open-modal").addClass("dol-open-modal-visible");
                } else {
                    $(".dol-open-modal").removeClass("dol-open-modal-visible");
                }

                DollieSiteList.fn.updateSelectedSites();
            });

            $(".dol-sites-item input[type=checkbox]").on("change", function () {
                var checked = DollieSiteList.fn.updateSelectedSites();

                if (checked) {
                    $(".dol-open-modal").addClass("dol-open-modal-visible");
                } else {
                    $(".dol-open-modal").removeClass("dol-open-modal-visible");
                }
            });

            $(".dol-open-modal").on("click", function (e) {
                e.preventDefault();

                var modalId = $(this).data("modal-id");
                if (!modalId) {
                    return false;
                }

                $("#" + modalId)
                    .find(".dol-modal-submit")
                    .prop("disabled", false);
                $("#" + modalId)
                    .find(".dol-modal-error")
                    .hide();
                $("#" + modalId)
                    .find(".dol-modal-success")
                    .hide();
                $("#" + modalId)
                    .find(".dol-action-list")
                    .val("");
                $("#" + modalId).addClass("dol-modal-visible");
            });

            $(".dol-modal-close").on("click", function (e) {
                e.preventDefault();

                var modal = $(this).closest(".dol-custom-modal");

                if (!modal) {
                    return false;
                }

                modal.removeClass("dol-modal-visible");

                modal.find(".dol-modal-submit").prop("disabled", true);

                $(".dol-tab-action").each(function (index, item) {
                    $(item).removeClass("dol-tab-active");
                    $(item).removeClass("dol-callback-called");
                });

                $(".dol-tab-inner").each(function (index, item) {
                    $(item).removeClass("dol-tab-active");
                });

                $(".dol-tab-action-initial").addClass("dol-tab-active");
                $("#dol-bulk-actions").addClass("dol-tab-active");
                $("#dol-schedules").html("");
            });

            $(".dol-tab-action").on("click", function (e) {
                e.preventDefault();

                if ($(this).hasClass("dol-tab-active")) {
                    return false;
                }

                var target = $(this).data("tab-name");

                if (!$(target).length) {
                    return false;
                }

                $(".dol-tab-action").each(function (index, item) {
                    $(item).removeClass("dol-tab-active");
                });

                $(".dol-tab-inner").each(function (index, item) {
                    $(item).removeClass("dol-tab-active");
                });

                $(target).addClass("dol-tab-active");
                $(this).addClass("dol-tab-active");

                if ($(this).data("tab-callback")) {
                    if ($(this).hasClass("dol-callback-called")) {
                        return;
                    }

                    DollieSiteList.fn[$(this).data("tab-callback")]();
                    $(this).addClass("dol-callback-called");
                }
            });
        },

        applyFilters: function () {
            $(".dol-apply-filters").on("click", function (e) {
                e.preventDefault();

                var per_page = $("#per-page").val();

                var searchParams = new URLSearchParams(window.location.search);

                if (per_page) {
                    searchParams.set("per_page", per_page);
                }

                $(this).closest(".dol-custom-modal").removeClass("dol-modal-visible");
                window.location.search = searchParams.toString();
            });
        },

        sendBulkAction: function () {
            $(".dol-send-bulk-action").on("click", function (e) {
                e.preventDefault();

                if (!$(".dol-action-list").val()) {
                    return;
                }

                if (!DollieSiteList.vars.selectedSites.length) {
                    $(this).closest(".dol-custom-modal").find(".dol-modal-success").hide();
                    $(this).closest(".dol-custom-modal").find(".dol-modal-error").show();
                    return;
                }

                var command_data = [];

                if ($(".dol-resources-list").length) {
                    $.each(
                        $(".dol-resources-list .dol-resource-site:checked"),
                        function () {
                            command_data.push({
                                id: $(this).val(),
                                value: $(this).attr("name"),
                            });
                        }
                    );
                }

                $.ajax({
                    method: "POST",
                    url: $(this).data("ajax-url"),
                    data: {
                        containers: DollieSiteList.vars.selectedSites,
                        command: $(".dol-action-list").val(),
                        command_data: command_data,
                        action: "dollie_do_bulk_action",
                        nonce: $(this).data("nonce"),
                    },
                    context: $(this),
                    beforeSend: function () {
                        $(this).prop("disabled", true);
                    },
                    success: function (response) {
                        if (response.success) {
                            $(this).closest(".dol-custom-modal").find(".dol-modal-error").hide();
                            $(this).closest(".dol-custom-modal").find(".dol-modal-success").show();

                            if (response.data) {
                                $.each(response.data, function (index, item) {
                                    var element = $(
                                        "[data-site-hash='" + item.container_hash + "']"
                                    );

                                    if (element.length) {
                                        element.addClass("dol-sites-item-locked");
                                        element.find(".dol-item-execution-text").html(item.text);
                                        element
                                            .find(".dol-item-execution-placeholder")
                                            .removeClass("dol-hidden");
                                        element.find(".dol-sites-controls").addClass("dol-hidden");
                                    }
                                });
                            }

                            $(
                                ".dol-sites-item.dol-sites-item-locked input[type=checkbox]"
                            ).prop("checked", false);
                            DollieSiteList.fn.updateSelectedSites();
                            DollieSiteList.fn.checkBulkAction();

                            var btn = $(this);
                            setTimeout(function () {
                                btn.closest(".dol-custom-modal").removeClass("dol-modal-visible");
                                $(".dol-open-modal").removeClass("dol-open-modal-visible");
                                $(".dol-select-all-container")
                                    .prop("checked", false)
                                    .trigger("change");
                            }, 2000);

                            $("#dol-resources-list").addClass("dol-hidden");
                            $("#dol-resources-list .dol-spinner").removeClass("dol-hidden");

                            $("#dol-resources-list").find(".dol-resources-list").remove();
                        } else {
                            $(this).closest(".dol-custom-modal").find(".dol-modal-success").hide();
                            $(this).closest(".dol-custom-modal").find(".dol-modal-error").show();

                            $(this).prop("disabled", false);
                        }
                    },
                });
            });
        },

        getBulkOptions: function () {
            $(document).on("click", ".dol-toggle-resource-details", function () {
                $(this)
                    .closest("ul")
                    .find(".dol-toggle-resource-details")
                    .each(function (index, item) {
                        $(item).removeClass("dol-toggle-resource-active");
                        $(item).find(".dol-open").removeClass("dol-hidden");
                        $(item).find(".dol-close").addClass("dol-hidden");

                        $("#" + $(item).data("item")).addClass("dol-hidden");
                    });

                $(this).addClass("dol-toggle-resource-active");
                $(this).find(".dol-open").addClass("dol-hidden");
                $(this).find(".dol-close").removeClass("dol-hidden");

                $("#" + $(this).data("item")).removeClass("dol-hidden");
            });

            $(document).on("change", ".dol-resource-item", function () {
                $(this)
                    .closest("li")
                    .find(".dol-resource-site")
                    .prop("checked", $(this).prop("checked"));
            });

            $(document).on("change", ".dol-resource-site", function () {
                var oneChecked = false;
                $(this)
                    .closest("ul")
                    .find(".dol-resource-site")
                    .each(function (index, item) {
                        if ($(item).is(":checked")) {
                            oneChecked = true;
                        }
                    });

                $(this)
                    .closest(".dol-resource-entry")
                    .find(".dol-resource-item")
                    .prop("checked", oneChecked);
            });

            $(".dol-send-bulk-action").prop("disabled", true);

            var fetchResourceRequest = null;

            $("#dol-bulk-action-type").on("change", function () {
                if (
                    $(this).val() === "update-plugins" ||
                    $(this).val() === "update-themes"
                ) {
                    $(".dol-send-bulk-action").prop("disabled", true);

                    if (fetchResourceRequest) {
                        fetchResourceRequest.abort();
                        fetchResourceRequest = null;

                        $("#dol-resources-list").addClass("dol-hidden");
                        $("#dol-resources-list .dol-spinner").removeClass("dol-hidden");

                        $("#dol-resources-list").find(".dol-resources-list").remove();
                    }

                    fetchResourceRequest = $.ajax({
                        method: "POST",
                        url: $("#dol-resources-list").data("ajax-url"),
                        data: {
                            containers: DollieSiteList.vars.selectedSites,
                            command: $(this).val(),
                            action: "dollie_get_bulk_action_data",
                            nonce: $("#dol-resources-list").data("nonce"),
                        },
                        beforeSend: function () {
                            $("#dol-resources-list").removeClass("dol-hidden");
                            $("#dol-resources-list")
                                .find(".dol-spinner")
                                .removeClass("dol-hidden");

                            $("#dol-resources-list").find(".dol-resources-list").remove();
                        },
                        success: function (response) {
                            fetchResourceRequest = null;

                            $("#dol-resources-list")
                                .find(".dol-spinner")
                                .addClass("dol-hidden");

                            var newContainer = $("#dol-resources-list");
                            newContainer.find(".dol-resources-list").remove();
                            newContainer.append(response.data);

                            var resourceContainer = newContainer.find(".dol-resources-list");

                            if (resourceContainer) {
                                resourceContainer
                                    .addClass("dol-overflow-y-scroll")
                                    .css("max-height", "580px");
                            }

                            $(".dol-send-bulk-action").prop("disabled", false);
                        },
                    });
                } else {
                    $("#dol-resources-list").addClass("dol-hidden");
                    $("#dol-resources-list .dol-spinner").removeClass("dol-hidden");
                    $("#dol-resources-list").find(".dol-resources-list").remove();

                    $(".dol-send-bulk-action").prop("disabled", false);
                }
            });
        },

        checkBulkAction: function () {
            DollieSiteList.vars.actionInterval = setInterval(function () {
                if ($(".dol-sites-item.dol-sites-item-locked").length) {
                    $.ajax({
                        method: "POST",
                        url: $("#dol-check-bulk-action").data("ajax-url"),
                        data: {
                            action: "dollie_check_bulk_action",
                            nonce: $("#dol-check-bulk-action").data("nonce"),
                        },
                        success: function (response) {
                            if (response.success) {
                                $.each($('.dol-sites-item'), function (i, element) {
                                    var hash = $(element).data('site-hash');

                                    const action = response.data.filter(item => item.container_hash === hash);

                                    if (action.length) {
                                        return true;
                                    }

                                    $(element).removeClass("dol-sites-item-locked");
                                    $(element).find(".dol-item-execution-placeholder").addClass("dol-hidden");
                                    $(element).find(".dol-sites-controls").removeClass("dol-hidden");
                                });
                            }
                        },
                    });
                }
            }, 10000);
        },

        getScheduledActions: function (success = false) {
            $.ajax({
                method: "POST",
                url: $("#dol-loading-history").data("ajax-url"),
                data: {
                    action: "dollie_get_schedule_history",
                    nonce: $("#dol-loading-history").data("nonce"),
                },
                beforeSend: function () {
                    if (!success) $("#dol-loading-history").removeClass("dol-hidden");
                },
                complete: function () {
                    if (!success) $("#dol-loading-history").addClass("dol-hidden");
                },
                success: function (response) {
                    if (response.success) {
                        var newContainer = $("#dol-schedule-history");
                        newContainer.html("");
                        newContainer.append(response.data);
                        newContainer
                            .find(".dol-schedule-list")
                            .addClass("dol-overflow-y-scroll")
                            .css("max-height", "580px");
                        newContainer
                            .find(".dol-schedule-container-list")
                            .addClass("dol-overflow-y-scroll")
                            .css("max-height", "280px");
                        newContainer
                            .find(".dol-schedule-container-logs")
                            .addClass("dol-overflow-y-scroll")
                            .css("max-height", "280px");

                        if (success) {
                            $(".dol-recurring-delete-success").show();

                            setTimeout(function () {
                                $(".dol-recurring-delete-success").hide();
                            }, 5000);
                        }
                    }
                },
            });
        },

        getScheduleTemplate: function (success = false) {
            if (!DollieSiteList.vars.selectedSites.length) {
                return;
            }

            $.ajax({
                method: "POST",
                url: $("#dol-loading-schedules").data("ajax-url"),
                data: {
                    containers: DollieSiteList.vars.selectedSites,
                    action: "dollie_get_selected_sites",
                    nonce: $("#dol-loading-schedules").data("nonce"),
                },
                beforeSend: function () {
                    $("#dol-loading-schedules").removeClass("dol-hidden");
                },
                complete: function () {
                    $("#dol-loading-schedules").addClass("dol-hidden");
                },
                success: function (response) {
                    if (response.success) {
                        var newContainer = $("#dol-schedules");
                        newContainer.html("");
                        newContainer.append(response.data);
                        newContainer
                            .find(".dol-schedule-create-list")
                            .addClass("dol-overflow-y-scroll")
                            .css("max-height", "420px");

                        if (success) {
                            $(".dol-recurring-success").show();

                            setTimeout(function () {
                                $(".dol-recurring-success").hide();
                            }, 5000);
                        }
                    }
                },
            });
        },

        recurringAction: function () {
            $(document).on("change", ".dol-select-all-schedule", function () {
                $(".dol-schedule-create-list input[type=checkbox]")
                    .prop("checked", $(this).prop("checked"))
                    .trigger("change");
            });

            $(document).on(
                "change",
                ".dol-schedule-create-list input[type=checkbox]",
                function () {
                    if (
                        $(".dol-schedule-create-list input[type=checkbox]").is(":checked")
                    ) {
                        $(".dol-schedule-create-list input[type=checkbox]").removeAttr(
                            "required"
                        );
                    } else {
                        $(".dol-schedule-create-list input[type=checkbox]").attr(
                            "required",
                            "required"
                        );
                    }
                }
            );

            $(document).on("click", ".dol-show-logs", function () {
                if ($(this).hasClass("dol-logs-visible")) {
                    $(this)
                        .closest(".dol-schedule-accordion-content")
                        .find(".dol-schedule-container-list")
                        .removeClass("dol-hidden");
                    $(this)
                        .closest(".dol-schedule-accordion-content")
                        .find(".dol-schedule-container-logs")
                        .addClass("dol-hidden");

                    $(this).removeClass("dol-logs-visible");
                    $(this).html($(this).data("show-log"));
                } else {
                    $(this)
                        .closest(".dol-schedule-accordion-content")
                        .find(".dol-schedule-container-list")
                        .addClass("dol-hidden");
                    $(this)
                        .closest(".dol-schedule-accordion-content")
                        .find(".dol-schedule-container-logs")
                        .removeClass("dol-hidden");

                    $(this).addClass("dol-logs-visible");
                    $(this).html($(this).data("hide-log"));
                }
            });

            $(document).on("submit", "#dol-schedule-form", function (e) {
                e.preventDefault();

                var loader = $(this)
                    .parent()
                    .find(".dol-loader[data-for='recurring-actions-create']");

                $.ajax({
                    method: "POST",
                    url: $(this).attr("action"),
                    data: {
                        data: $(this).serialize(),
                        action: "dollie_create_recurring_action",
                        nonce: $(this).data("nonce"),
                    },
                    dataType: "json",
                    beforeSend: function () {
                        if (loader.length) {
                            loader.show();
                        }
                    },
                    onComplete: function () {
                        if (loader.length) {
                            loader.hide();
                        }
                    },
                    success: function (response) {
                        if (response.success) {
                            $("#dol-schedules").html("");
                            DollieSiteList.fn.getScheduleTemplate(true);
                            DollieSiteList.fn.getScheduledActions();
                        }
                    },
                });
            });

            $(document).on("click", ".dol-delete-schedule", function () {
                var loader = $(this)
                    .closest(".dol-schedule-list-item")
                    .find(".dol-loader[data-for='recurring-actions-delete']");

                $.ajax({
                    method: "POST",
                    url: $(this).data("ajax-url"),
                    data: {
                        uuid: $(this).data("uuid"),
                        action: "dollie_delete_recurring_action",
                        nonce: $(this).data("nonce"),
                    },
                    dataType: "json",
                    context: $(this),
                    beforeSend: function () {
                        if (loader.length) {
                            loader.show();
                        }
                    },
                    onComplete: function () {
                        if (loader.length) {
                            loader.hide();
                        }
                    },
                    success: function (response) {
                        if (response.success && response.data) {
                            $(this)
                                .closest(".dol-schedule-list-item")
                                .fadeOut(300, function () {
                                    $(this).remove();
                                });
                        }
                    },
                });
            });

            $(document).on("click", ".dol-delete-recurring-container", function () {
                var loader = $(this)
                    .closest(".dol-schedule-container-item")
                    .find(".dol-loader[data-for='recurring-container-delete']");

                $.ajax({
                    method: "POST",
                    url: $(this).data("ajax-url"),
                    data: {
                        uuid: $(this).data("uuid"),
                        container_hash: $(this).data("container-hash"),
                        action: "dollie_delete_recurring_container",
                        nonce: $(this).data("nonce"),
                    },
                    dataType: "json",
                    context: $(this),
                    beforeSend: function () {
                        if (loader.length) {
                            loader.show();
                        }
                    },
                    onComplete: function () {
                        if (loader.length) {
                            loader.hide();
                        }
                    },
                    success: function (response) {
                        if (response.success && response.data) {
                            $(this)
                                .closest(".dol-schedule-container-item")
                                .fadeOut(300, function () {
                                    $(this).remove();
                                });
                        }
                    },
                });
            });

            $(document).on(
                "click",
                ".dol-schedule-list .dol-schedule-accordion",
                function () {
                    var alreadyOpened = $(this).hasClass("dol-schedule-accordion-opened");

                    $(".dol-schedule-list .dol-schedule-accordion").each(function (
                        index,
                        item
                    ) {
                        $(item).removeClass("dol-schedule-accordion-opened");
                        $(item).find(".dol-acc-closed").removeClass("dol-hidden");
                        $(item).find(".dol-acc-opened").addClass("dol-hidden");
                        $(item)
                            .parent()
                            .find(".dol-schedule-accordion-content")
                            .removeClass("dol-flex")
                            .addClass("dol-hidden");
                    });

                    if (!alreadyOpened) {
                        $(this).addClass("dol-schedule-accordion-opened");
                        $(this).find(".dol-acc-closed").addClass("dol-hidden");
                        $(this).find(".dol-acc-opened").removeClass("dol-hidden");

                        $(this)
                            .parent()
                            .find(".dol-schedule-accordion-content")
                            .removeClass("dol-hidden")
                            .addClass("dol-flex");
                    }
                }
            );
        },

        updateSelectedSites: function () {
            var checked;

            DollieSiteList.vars.selectedSites = [];

            $(".dol-sites-item input[type=checkbox]").each(function (index, item) {
                if ($(item).is(":checked")) {
                    checked = true;
                    DollieSiteList.vars.selectedSites.push({
                        id: $(item).val(),
                        url: $(item)
                            .closest(".dol-sites-item")
                            .find(".dol-item-url")
                            .attr("href"),
                    });
                }
            });

            return checked;
        },

        pagination: function () {
            $(".dol-sites").on(
                "click",
                ".dol-sites-pages a.page-numbers",
                function (e) {
                    e.preventDefault();

                    $(".dol-sites-item input[type=checkbox]").prop("checked", false);
                    $(".dol-select-all-container").prop("checked", false);
                    $(".dol-open-modal").removeClass("dol-open-modal-visible");
                    DollieSiteList.fn.updateSelectedSites();

                    var elementId = $(this)
                        .closest(".elementor-widget-dollie-sites-listing")
                        .data("id");
                    var load = $(this).attr("href");

                    if (!load && $(this).parent().data("current-page") === 2) {
                        load = $(this).parent().data("permalink") + "page/1";
                    }

                    let url = new URL(load);

                    var elementor_library = url.searchParams.get("elementor_library");
                    if (elementor_library) {
                        url = new URL(window.location);
                        url.searchParams.set("elementor_library", elementor_library);
                        url.searchParams.set("load-page", parseInt($(this).html()));
                    }

                    var search = $(this)
                        .closest(".elementor-widget-dollie-sites-listing")
                        .find(".dol-search-site");
                    if (search.length && search.attr("data-search-term")) {
                        url.searchParams.set("search", search.attr("data-search-term"));
                    }

                    load = url.href;

                    $.ajax({
                        method: "GET",
                        url: load,
                        context: $(this),
                        beforeSend: function () {
                            $(this)
                                .closest(".elementor-widget-dollie-sites-listing")
                                .find(".dol-loader[data-for='pagination']")
                                .show();
                        },
                        complete: function () { },
                        success: function (response) {
                            var posts = $(response).find(
                                ".elementor-element-" +
                                elementId +
                                ".elementor-widget-dollie-sites-listing .dol-sites"
                            );

                            if (posts.length) {
                                $(this).closest(".dol-sites").html(posts.html());
                            }
                        },
                    });
                }
            );
        },

        search: function () {
            $(".dol-search-site").on("keyup", function (e) {
                var key = e.which;

                if (key === 13) {
                    if ($(this).val().length === 1) {
                        return false;
                    }

                    $(".dol-sites-item input[type=checkbox]").prop("checked", false);
                    $(".dol-select-all-container").prop("checked", false);
                    $(".dol-open-modal").removeClass("dol-open-modal-visible");
                    DollieSiteList.fn.updateSelectedSites();

                    var elementId = $(this)
                        .closest(".elementor-widget-dollie-sites-listing")
                        .data("id");
                    var load = $(this).data("permalink");

                    let url = new URL(load);
                    url.searchParams.set("search", $(this).val());

                    if ($(this).data("per-page")) {
                        url.searchParams.set("per_page", $(this).data("per-page"));
                    }

                    load = url.href;

                    $.ajax({
                        method: "GET",
                        url: load,
                        context: $(this),
                        beforeSend: function () {
                            $(this)
                                .closest(".elementor-widget-dollie-sites-listing")
                                .find(".dol-loader[data-for='pagination']")
                                .show();
                        },
                        complete: function () { },
                        success: function (response) {
                            var posts = $(response).find(
                                ".elementor-element-" +
                                elementId +
                                ".elementor-widget-dollie-sites-listing .dol-sites"
                            );

                            if (posts.length) {
                                $(this)
                                    .closest(".elementor-widget-dollie-sites-listing")
                                    .find(".dol-sites")
                                    .html(posts.html());
                            }
                        },
                    });
                }

                $(this).attr("data-search-term", $(this).val());
            });
        },

        toggleView: function () {
            $(".dol-list-switch").on("click", function (e) {
                e.preventDefault();

                $(".dol-list-switch").removeClass("dol-switch-active");
                $(this).addClass("dol-switch-active");

                var sitesContainer = $(".dol-sites-container");
                var sitesContainerItem = $(".dol-sites-item");

                sitesContainer.addClass("dol-sites-list");
                sitesContainerItem.removeClass("dol-sites-grid-item");
                sitesContainerItem.addClass("dol-sites-list-item");
            });
        },
    };

    $(document).ready(DollieSiteList.fn.init);
})(jQuery);
