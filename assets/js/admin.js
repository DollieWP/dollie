jQuery(document).ready(function ($) {
    var baseUrl = window.location.protocol + "//" + window.location.host + "/";
    $(".post-type-container #title").attr("disabled", true);
    $(".post-type-container #edit-slug-buttons").remove();
    $(".post-type-container .input-text-wrap input.ptitle").attr(
        "disabled",
        true
    );
    $(".post-type-container input[name='post_name']").attr("disabled", true);
    $("#acf-group_5af8272e96d48 input").attr("disabled", true);

    $(".post-type-container a.page-title-action").attr(
        "href",
        baseUrl + "launch-site"
    );

    $(".post-type-container li.trash a").text("Stopped");

    if (window.location.href.indexOf("blueprint=yes") > -1) {
        $(".toplevel_page_dollie_blueprints").addClass("wp-menu-open");
        $(".toplevel_page_dollie_blueprints").addClass("wp-has-current-submenu");
        $(".toplevel_page_dollie_blueprints").removeClass("wp-not-current-submenu");
        $(".menu-icon-container").removeClass("wp-menu-open");
        $(".menu-icon-container").removeClass("wp-has-current-submenu");
        $(".menu-icon-container").addClass("wp-not-current-submenu");
        var actionLinks = $(".post-type-container .subsubsub > li a");
        actionLinks.each(function () {
            if ($(this).attr('href').indexOf("blueprint=yes") === -1) {
                $(this).attr('href', $(this).attr('href') + '&blueprint=yes');
            }
        });

    }
});
