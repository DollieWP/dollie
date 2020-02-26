jQuery(document).ready(function($) {
  var baseUrl = window.location.protocol + "//" + window.location.host + "/";
  $(".post-type-container #title").attr("disabled", true);
  $(".post-type-container #edit-slug-buttons").remove();
  $(".post-type-container .input-text-wrap input.ptitle").attr(
    "disabled",
    true
  );
  $(".post-type-container input[name='post_name']").attr("disabled", true);

  $('a[href="wpd_launch_site"]').attr("href", baseUrl + "launch-site");
  $(".post-type-container a.page-title-action").attr(
    "href",
    baseUrl + "launch-site"
  );
  $(".post-type-container li.trash a").text("Undeployed");
});
