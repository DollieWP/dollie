var DollieCustomersList = DollieCustomersList || {};

(function ($) {
    // USE STRICT
    "use strict";

    DollieCustomersList.fn = {
        init: function () {
            // DollieCustomersList.fn.pagination();
            DollieCustomersList.fn.search();
            DollieCustomersList.fn.toggleView();
        },

        pagination: function () {
            $(".dol-customers").on(
                "click",
                ".dol-customers-pages a.page-numbers",
                function (e) {
                    e.preventDefault();

                    var elementId = $(this)
                        .closest(".elementor-widget-dollie-customers-listing")
                        .data("id");
                    var load = $(this).attr("href");

                    if (!load && $(this).parent().data("current-page") === 2) {
                        load = $(this).parent().data("permalink") + "page/1";
                    }

                    let url = new URL(load);

                    var search = $(this)
                        .closest(".elementor-widget-dollie-customers-listing")
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
                                .closest(".elementor-widget-dollie-customers-listing")
                                .find(".dol-loader[data-for='pagination']")
                                .show();
                        },
                        complete: function () { },
                        success: function (response) {
                            var posts = $(response).find(
                                ".elementor-element-" +
                                elementId +
                                ".elementor-widget-dollie-customers-listing .dol-customers"
                            );

                            if (posts.length) {
                                $(this).closest(".dol-customers").html(posts.html());
                            }
                        },
                    });
                }
            );
        },

        search: function () {
            $(".dol-search-customer").on("keyup", function (e) {
                var parent = $(this).closest(
                    ".elementor-widget-dollie-customers-listing"
                );
                var key = e.which;

                if (key === 13) {
                    if ($(this).val().length === 1) {
                        return false;
                    }

                    var elementId = parent.data("id");
                    var load = $(this).data("permalink");

                    let url = new URL(load);
                    url.searchParams.set("search", $(this).val());

                    load = url.href;

                    $.ajax({
                        method: "GET",
                        url: load,
                        context: $(this),
                        beforeSend: function () {
                            parent.find(".dol-loader[data-for='pagination']").show();
                        },
                        complete: function () { },
                        success: function (response) {
                            var posts = $(response).find(
                                ".elementor-element-" +
                                elementId +
                                ".elementor-widget-dollie-customers-listing .dol-customers"
                            );

                            if (posts.length) {
                                parent.find(".dol-customers").html(posts.html());
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

                var customersContainer = $(".dol-customers-container");
                var customersContainerItem = $(".dol-customers-item");

                customersContainer.addClass("dol-customers-list");
                customersContainerItem.removeClass("dol-customers-grid-item");
                customersContainerItem.addClass("dol-customers-list-item");
            });
        },
    };

    $(document).ready(DollieCustomersList.fn.init);
})(jQuery);
