(function($) {
  "use strict";

  var tooltiptext,
    tooltip_element,
    description,
    target,
    hasTooltipClass,
    dataKey;
  var $class = acfTooltip.class;
  var $excludeClass = acfTooltip.exclude_class;

  var $appendTooltipToLabel, $appendTooltipToRepeater, $appendTooltipToField;

  var $onFieldEditor = acfTooltip.fieldeditor;
  if ($onFieldEditor == 1) {
    $appendTooltipToLabel = "body .acf-label";
    $appendTooltipToRepeater = "body .acf-repeater .acf-th";
    $appendTooltipToField = "body .acf-field .acf-input";
  } else {
    $appendTooltipToLabel = "body:not(.post-type-acf-field-group) .acf-label";
    $appendTooltipToRepeater =
      "body:not(.post-type-acf-field-group) .acf-repeater .acf-th";
    $appendTooltipToField =
      "body:not(.post-type-acf-field-group) .acf-field .acf-input";
  }

  if (typeof acf !== "undefined") {
    acf.add_action("ready", function($el) {
      $($appendTooltipToLabel).each(function() {
        tooltip_element = $(this);
        description = tooltip_element.find("p.af-field-instructions");

        if (description.length > 0) {
          target = tooltip_element.find("label");
          hasTooltipClass = tooltip_element.closest(".acf-field");
          if (!hasTooltipClass.hasClass($excludeClass)) {
            prepare_tooltip(target, description, hasTooltipClass);
          }

          if (acfTooltip.acf_version_compare > 0) {
            acf.doAction("refresh");
          }
        }
      });

      $($appendTooltipToRepeater).each(function() {
        tooltip_element = $(this);
        description = tooltip_element.find("> p.af-field-instructions");

        if (description.length > 0) {
          target = tooltip_element;
          dataKey = tooltip_element.data("key");
          hasTooltipClass = tooltip_element
            .closest(".acf-table")
            .find('td.acf-field[data-key="' + dataKey + '"]');
          if (!hasTooltipClass.hasClass($excludeClass)) {
            prepare_tooltip(target, description, hasTooltipClass);
          }

          if (acfTooltip.acf_version_compare > 0) {
            acf.doAction("refresh");
          }
        }
      });

      $($appendTooltipToField).each(function() {
        tooltip_element = $(this);
        description = tooltip_element.find("> p.af-field-instructions");

        if (description.length > 0) {
          target = tooltip_element.prev().find("label");
          hasTooltipClass = tooltip_element.closest(".acf-field");
          if (!hasTooltipClass.hasClass($excludeClass)) {
            prepare_tooltip(target, description, hasTooltipClass);
          }

          if (acfTooltip.acf_version_compare > 0) {
            acf.doAction("refresh");
          }
        }
      });

      acf_tooltip();
    });

    acf.add_action("append", function($el) {
      acf_tooltip();
    });
  }

  function prepare_tooltip($target, $description, $hasTooltipClass) {
    if ($class == "") {
      make_tooltip($target, $description);
    } else {
      if ($hasTooltipClass.hasClass($class)) {
        make_tooltip($target, $description);
      }
    }
  }

  function escapeHtml(string) {
    var entityMap = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#39;",
      "/": "&#x2F;",
      "`": "&#x60;",
      "=": "&#x3D;"
    };
    return String(string).replace(/[&<>"'`=\/]/g, function(s) {
      return entityMap[s];
    });
  }

  function make_tooltip($target, $description) {
    tooltiptext = escapeHtml($description.html());
    $description.addClass("tooltip__hidden");
    target.append(
      '<span class="dashicons dashicons-editor-help acf__tooltip" data-tooltip="' +
        tooltiptext +
        '"></span>'
    );
  }

  function acf_tooltip() {
    $(".acf__tooltip").each(function() {
      $(this).qtip({
        style: {
          classes: acfTooltip.style
        },
        position: {
          my: acfTooltip.my,
          at: acfTooltip.at
        },
        content: {
          attr: "data-tooltip"
        },
        hide: {
          fixed: true,
          delay: 0
        }
      });
    });
  }
})(jQuery);
