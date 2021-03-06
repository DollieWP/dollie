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

    $(".dollie-is-blueprint .wp-submenu-wrap li a").each(function () {
        if (this.href.indexOf("container") != -1) {
            $(this).parent().removeClass("current");
        }
    });

    $(".dollie-is-blueprint .wp-submenu-wrap li a").each(function () {
        if (this.href.indexOf("blueprint") != -1) {
            $(this).parent().addClass("current");
        }
    });

    $(".post-type-container a.page-title-action").attr(
        "href",
        baseUrl + "launch-site"
    );

    $(".post-type-container li.trash a").text("Stopped");

    if (window.location.href.indexOf("blueprint=yes") > -1) {
        var actionLinks = $(".post-type-container .subsubsub > li a");
        actionLinks.each(function() {
            if ($(this).attr('href').indexOf("blueprint=yes") === -1) {
                $(this).attr('href', $(this).attr('href') + '&blueprint=yes' );
            }
        });

    }

    $('.edit-php.post-type-container a.submitdelete, .post-php a.submitdelete').click(function (event) {
        if (!confirm('Are you sure you want to remove this site?')) {
            event.preventDefault();
        }
    });
});
