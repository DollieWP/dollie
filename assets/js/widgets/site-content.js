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
          var staging = deploy.data("staging");

          DollieSiteContent.vars.ajax_url = deploy.data("ajax-url");

          DollieSiteContent.vars.body = {
            container: container,
            staging: staging ? 1 : 0,
            action: "dollie_check_deploy",
            nonce: deploy.data("nonce"),
          };

          setInterval(function () {
            if (!DollieSiteContent.vars.reloaded) {
              DollieSiteContent.fn.sendRequest();
            }
          }, 15000);
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
              location.reload();
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
        var submit = confirm("Do you really want to overwrite your live site with your staging site? This will apply the changes you made to your staging site to your live site.");

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
  };

  $(document).ready(DollieSiteContent.fn.init);
})(jQuery);
