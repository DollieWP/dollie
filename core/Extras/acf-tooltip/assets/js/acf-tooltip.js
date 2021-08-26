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
      '<span class="svg-tooltip acf__tooltip" data-tooltip="' +
        tooltiptext +
        '">' +
        '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"\n' +
        '\t width="15px" height="15px" viewBox="0 0 93.936 93.936" style="enable-background:new 0 0 93.936 93.936;"\n' +
        '\t xml:space="preserve">\n' +
        '<g>\n' +
        '\t<path d="M80.179,13.758c-18.342-18.342-48.08-18.342-66.422,0c-18.342,18.341-18.342,48.08,0,66.421\n' +
        '\t\tc18.342,18.342,48.08,18.342,66.422,0C98.521,61.837,98.521,32.099,80.179,13.758z M44.144,83.117\n' +
        '\t\tc-4.057,0-7.001-3.071-7.001-7.305c0-4.291,2.987-7.404,7.102-7.404c4.123,0,7.001,3.044,7.001,7.404\n' +
        '\t\tC51.246,80.113,48.326,83.117,44.144,83.117z M54.73,44.921c-4.15,4.905-5.796,9.117-5.503,14.088l0.097,2.495\n' +
        '\t\tc0.011,0.062,0.017,0.125,0.017,0.188c0,0.58-0.47,1.051-1.05,1.051c-0.004-0.001-0.008-0.001-0.012,0h-7.867\n' +
        '\t\tc-0.549,0-1.005-0.423-1.047-0.97l-0.202-2.623c-0.676-6.082,1.508-12.218,6.494-18.202c4.319-5.087,6.816-8.865,6.816-13.145\n' +
        '\t\tc0-4.829-3.036-7.536-8.548-7.624c-3.403,0-7.242,1.171-9.534,2.913c-0.264,0.201-0.607,0.264-0.925,0.173\n' +
        '\t\ts-0.575-0.327-0.693-0.636l-2.42-6.354c-0.169-0.442-0.02-0.943,0.364-1.224c3.538-2.573,9.441-4.235,15.041-4.235\n' +
        '\t\tc12.36,0,17.894,7.975,17.894,15.877C63.652,33.765,59.785,38.919,54.73,44.921z"/>\n' +
        '</g>\n' +
        '</svg>' +
        '</span>'
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
          attr: "data-tooltip-old"
        },
        hide: {
          fixed: true,
          delay: 0
        }
      });
    });
  }
})(jQuery);
