var DollieSiteList = DollieSiteList || {};

(function ($) {
  // USE STRICT
  "use strict";

  DollieSiteList.fn = {
    init: function () {
      DollieSiteList.fn.pagination();
      DollieSiteList.fn.search();
      DollieSiteList.fn.toggleView();
      DollieSiteList.fn.modalActions();
    },

    modalActions: function () {
      $(".dol-select-all-container").on("change", function () {
        $(".dol-sites-item input[type=checkbox]").prop(
          "checked",
          $(this).prop("checked")
        );

        if ($(this).is(":checked")) {
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

        $("#" + modalId).addClass("dol-modal-visible");
      });

      $(".dol-modal-close").on("click", function (e) {
        e.preventDefault();

        var modal = $(this).closest(".dol-modal");

        if (!modal) {
          return false;
        }

        modal.removeClass("dol-modal-visible");
      });

      $(".dol-apply-action").on("click", function (e) {
        $(".dol-modal-close").trigger("click");
      });
    },

    pagination: function () {
      $(".dol-sites").on(
        "click",
        ".dol-sites-pages a.page-numbers",
        function (e) {
          e.preventDefault();

          var elementId = $(this)
            .closest(".elementor-widget-dollie-sites-listing")
            .data("id");
          var load = $(this).attr("href");

          if (!load && $(this).parent().data("current-page") === 2) {
            load = $(this).parent().data("permalink") + "page/1";
          }

          let url = new URL(load);
          url.searchParams.set(
            "list_type",
            $(this).parent().attr("data-list-type")
          );

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
                .find(".dol-loader")
                .show();
            },
            complete: function () {},
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

          var elementId = $(this)
            .closest(".elementor-widget-dollie-sites-listing")
            .data("id");
          var load = $(this).data("permalink");

          let url = new URL(load);
          url.searchParams.set("list_type", $(this).attr("data-list-type"));
          url.searchParams.set("search", $(this).val());

          load = url.href;

          $.ajax({
            method: "GET",
            url: load,
            context: $(this),
            beforeSend: function () {
              $(this)
                .closest(".elementor-widget-dollie-sites-listing")
                .find(".dol-loader")
                .show();
            },
            complete: function () {},
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

        if ($(this).data("list-type") === "list") {
          sitesContainer.removeClass("dol-sites-grid");
          sitesContainer.addClass("dol-sites-list");
          sitesContainerItem.removeClass("dol-sites-grid-item");
          sitesContainerItem.addClass("dol-sites-list-item");
        } else {
          sitesContainer.removeClass("dol-sites-list");
          sitesContainer.addClass("dol-sites-grid");
          sitesContainerItem.removeClass("dol-sites-list-item");
          sitesContainerItem.addClass("dol-sites-grid-item");
        }

        $(this)
          .closest(".elementor-widget-dollie-sites-listing")
          .find(".dol-sites-pages")
          .attr("data-list-type", $(this).data("list-type"));

        $(this)
          .closest(".elementor-widget-dollie-sites-listing")
          .find(".dol-search-site")
          .attr("data-list-type", $(this).data("list-type"));
      });
    },
  };

  $(document).ready(DollieSiteList.fn.init);
})(jQuery);
