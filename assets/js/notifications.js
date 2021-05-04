var Dollie = Dollie || {};

(function ($) {
  "use strict";

  Dollie.notifications = {
    selectors: {
      openClose: ".dollie-notifications-nav > a, .notif-close",
      markRead: ".dollie-notifications-list .notif-mark-read",
      count: ".dollie-notifications-count",
      innerWrap: ".dollie-notifications-list .notif-inner",
      listItem: ".dollie-notifications-list .notif-item.notif-unread",
      list: ".dollie-notifications-list",
    },
    $body: $("body"),
    $nav: $(".dollie-notifications-nav"),
    refreshID: null,
    refreshTime:
      wpdNotifications.hasOwnProperty("refreshTime") &&
      wpdNotifications.refreshTime !== "0"
        ? wpdNotifications.refreshTime
        : 20000,

    init: function () {
      var _this = this;

      this.$body.on("click", _this.selectors.openClose, function () {
        $(_this.selectors.list).toggleClass("notif-visible");
        return false;
      });

      this.$body.on("click", _this.selectors.markRead, function () {
        _this.markAsRead();
        return false;
      });

      if (_this.$nav.length) {
        _this.registerAjaxInterval();
      }
    },

    markAsRead: function () {
      var _this = this;
      var values =
        "action=dollie_notifications_mark_read&check=" +
        $(_this.selectors.list).data("nonce");

      $.ajax({
        url: wpdNotifications.ajaxurl,
        type: "POST",
        dataType: "json",
        data: values,
        success: function (response) {
          if (response === null) {
            return;
          }

          if (response.success === true) {
            $(".notif-item.notif-unread")
              .removeClass("notif-unread")
              .addClass("notif-read");
            $(_this.selectors.count).text("");
          }
        },
      });
    },
    registerAjaxInterval: function () {
      var _this = this;

      if ($("body").hasClass("customize-preview")) {
        return false;
      }

      _this.refreshID = setInterval(function () {
        var values =
          "action=dollie_get_notifications&check=" +
          $(_this.selectors.list).data("nonce");
        values +=
          "&current_notifications=" + $(_this.selectors.count).first().text();

        $.ajax({
          url: wpdNotifications.ajaxurl,
          type: "GET",
          dataType: "json",
          data: values,
          success: function (response) {
            if (response === null) {
              return;
            }
            if (response.success === true && response.hasOwnProperty("data")) {
              $(_this.selectors.count).text(response.data.count);
              $(_this.selectors.listItem).remove();
              $(_this.selectors.innerWrap).prepend(response.data.content);
            }
          },
        });
      }, _this.refreshTime);
    },
  };

  $(document).ready(function () {
    Dollie.notifications.init();
  });
})(jQuery);
