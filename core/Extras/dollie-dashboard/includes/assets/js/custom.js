/**
 * Dollie Dashboard Admin scripts
 *
 * @package WeFoster
 * @subpackage Administration
 */
jQuery(document).ready(function($) {
  // When the iframe dashboard is loaded...
  $("#wefoster-dashboard").load(function() {
    // place this within dom ready function
    setTimeout(function() {
      // Hide loading UI
      $(".pre-loader").removeClass("show-loader");
      $(".pre-loader-message").addClass("done-loading");
    }, 500);
  });
});
