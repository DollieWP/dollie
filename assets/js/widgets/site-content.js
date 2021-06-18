var DollieSiteContent = DollieSiteContent || {};

(function ($) {
  // USE STRICT
  "use strict";

  DollieSiteContent.vars = {
    container: false,
    staging: false,
    action: "",
    ajax_url: false,
    nonce: false,
    reloaded: false,
  };

  DollieSiteContent.fn = {
    init: function () {
      DollieSiteContent.fn.checkDynamicFields();
      DollieSiteContent.fn.deploy();
      DollieSiteContent.fn.initStagingPanel();
    },

    checkDynamicFields: function () {
      var notifications = $("#dol-blueprint-notification");

      if (notifications.length) {
        DollieSiteContent.vars.container = notifications.data("container");
        DollieSiteContent.vars.ajax_url = notifications.data("ajax-url");
        DollieSiteContent.vars.nonce = notifications.data("nonce");

        var hasDynamicFields = notifications.data("dynamic-fields");

        if (hasDynamicFields) {
          notifications.removeClass("dol-hidden");

          $.ajax({
            method: "POST",
            url: DollieSiteContent.vars.ajax_url,
            data: {
              container: DollieSiteContent.vars.container,
              action: "dollie_check_dynamic_fields",
              nonce: DollieSiteContent.vars.nonce,
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

          DollieSiteContent.vars.container = container;
          DollieSiteContent.vars.staging = staging ? 1 : 0;
          DollieSiteContent.vars.ajax_url = deploy.data("ajax-url");
          DollieSiteContent.vars.nonce = deploy.data("nonce");

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
          method: "POST",
          url: DollieSiteContent.vars.ajax_url,
          data: {
            container: DollieSiteContent.vars.container,
            staging: DollieSiteContent.vars.staging,
            action: "dollie_check_deploy",
            nonce: DollieSiteContent.vars.nonce,
          },
          context: $(this),
          success: function (response) {
            if (response.success) {
              DollieSiteContent.vars.reloaded = true;

              setTimeout(function () {
                location.reload();
              }, 3000);
            }
          },
        });
      }
    },

    initStagingPanel: function () {
      var stagingPanel = $("#dol-staging-panel");

      if (stagingPanel.length) {
        $(".dol-staging-btn").on("click", function (e) {
          e.preventDefault();

          DollieSiteContent.vars.ajax_url = $(this).data("ajax-url");
          DollieSiteContent.vars.container = $(this).data("container");
          DollieSiteContent.vars.action = $(this).data("action");
          DollieSiteContent.vars.nonce = $(this).data("nonce");

          DollieSiteContent.fn.stagingRequest();
        });
      }
    },

    stagingRequest: function () {
      if (DollieSiteContent.vars.container) {
        $.ajax({
          method: "POST",
          url: DollieSiteContent.vars.ajax_url,
          data: {
            container: DollieSiteContent.vars.container,
            action: DollieSiteContent.vars.action,
            nonce: DollieSiteContent.vars.nonce,
          },
          context: $(this),
          success: function (response) {},
        });
      }
    },
  };

  $(document).ready(DollieSiteContent.fn.init);
})(jQuery);
