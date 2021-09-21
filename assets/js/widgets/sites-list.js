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
      DollieSiteList.fn.sendAction();
      DollieSiteList.fn.applyFilters();
      DollieSiteList.fn.checkAction();
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

        var modal = $(this).closest(".dol-modal");

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

        $(this).closest(".dol-modal").removeClass("dol-modal-visible");
        window.location.search = searchParams.toString();
      });
    },

    sendAction: function () {
      $(".dol-apply-action").on("click", function (e) {
        e.preventDefault();

        if (!$(".dol-action-list").val()) {
          return;
        }

        if (!DollieSiteList.vars.selectedSites.length) {
          $(this).closest(".dol-modal").find(".dol-modal-success").hide();
          $(this).closest(".dol-modal").find(".dol-modal-error").show();
          return;
        }

        $.ajax({
          method: "POST",
          url: $(this).data("ajax-url"),
          data: {
            containers: DollieSiteList.vars.selectedSites,
            command: $(".dol-action-list").val(),
            action: "dollie_do_bulk_action",
            nonce: $(this).data("nonce"),
          },
          context: $(this),
          beforeSend: function () {
            $(this).prop("disabled", true);
          },
          success: function (response) {
            if (response.success) {
              $(this).closest(".dol-modal").find(".dol-modal-error").hide();
              $(this).closest(".dol-modal").find(".dol-modal-success").show();

              if (response.data) {
                $.each(response.data, function (index, item) {
                  var element = $(
                    "[data-site-name='" + item.container_uri + "']"
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
              DollieSiteList.fn.checkAction();

              var btn = $(this);
              setTimeout(function () {
                btn.closest(".dol-modal").removeClass("dol-modal-visible");
                $(".dol-open-modal").removeClass("dol-open-modal-visible");
                $(".dol-select-all-container").prop("checked", false);
              }, 2000);
            } else {
              $(this).closest(".dol-modal").find(".dol-modal-success").hide();
              $(this).closest(".dol-modal").find(".dol-modal-error").show();

              $(this).prop("disabled", false);
            }
          },
        });
      });
    },

    checkAction: function () {
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
                if (response.data.length) {
                  $.each(response.data, function (index, item) {
                    var element = $(
                      "[data-site-name='" + item.container_uri + "']"
                    );

                    if (element.length) {
                      element.removeClass("dol-sites-item-locked");
                      element
                        .find(".dol-item-execution-placeholder")
                        .addClass("dol-hidden");
                      element
                        .find(".dol-sites-controls")
                        .removeClass("dol-hidden");
                    }
                  });
                }
              }
            },
          });
        }
      }, 10000);
    },

    getScheduledActions: function () {
      $.ajax({
        method: "POST",
        url: $("#dol-loading-history").data("ajax-url"),
        data: {
          action: "dollie_get_schedule_history",
          nonce: $("#dol-loading-history").data("nonce"),
        },
        beforeSend: function () {
          $("#dol-loading-history").removeClass("dol-hidden");
        },
        complete: function () {
          $("#dol-loading-history").addClass("dol-hidden");
        },
        success: function (response) {
          if (response.success) {
            var newContainer = $("#dol-schedule-history");
            newContainer.html("");
            newContainer.append(response.data);
            newContainer
              .find(".dol-schedule-list")
              .addClass("dol-overflow-y-scroll")
              .css("max-height", "420px");
          }
        },
      });
    },

    getScheduleTemplate: function () {
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
              .find(".dol-schedule-list")
              .addClass("dol-overflow-y-scroll")
              .css("max-height", "420px");
          }
        },
      });
    },

    recurringAction: function () {
      $(document).on("submit", "#dol-schedule-form", function (e) {
        e.preventDefault();

        $.ajax({
          method: "POST",
          url: $(this).attr("action"),
          data: {
            data: $(this).serialize(),
            action: "dollie_create_recurring_action",
            nonce: $(this).data("nonce"),
          },
          dataType: "json",
          beforeSend: function () {},
          success: function (response) {
            if (response.success) {
              $("#dol-schedules").html("");
              DollieSiteList.fn.getScheduleTemplate();
              DollieSiteList.fn.getScheduledActions();
            }
          },
        });
      });

      $(document).on(
        "change",
        ".dol-action-selector input[type='checkbox']",
        function () {
          if ($(this).is(":checked")) {
            $(this)
              .closest(".dol-action-selector")
              .find(".dol-interval-container")
              .removeClass("dol-hidden");
          } else {
            $(this)
              .closest(".dol-action-selector")
              .find(".dol-interval-container")
              .addClass("dol-hidden");
          }
        }
      );

      $(document).on("click", ".dol-delete-schedule", function () {
        $.ajax({
          method: "POST",
          url: $(this).data("ajax-url"),
          data: {
            target: $(this).data("container-id"),
            action: "dollie_delete_recurring_action",
            nonce: $(this).data("nonce"),
          },
          dataType: "json",
          beforeSend: function () {},
          success: function (response) {
            if (response.success) {
              DollieSiteList.fn.getScheduledActions();
            }
          },
        });
      });
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
          url.searchParams.set(
            "list_type",
            $(this).parent().attr("data-list-type")
          );

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

          $(".dol-sites-item input[type=checkbox]").prop("checked", false);
          $(".dol-select-all-container").prop("checked", false);
          $(".dol-open-modal").removeClass("dol-open-modal-visible");
          DollieSiteList.fn.updateSelectedSites();

          var elementId = $(this)
            .closest(".elementor-widget-dollie-sites-listing")
            .data("id");
          var load = $(this).data("permalink");

          let url = new URL(load);
          url.searchParams.set("list_type", $(this).attr("data-list-type"));
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
