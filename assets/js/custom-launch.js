jQuery(document).ready(function () {
  var counter = 1,
    int = setInterval(function () {
      jQuery("div.loader-wrap").attr(
        "class",
        "loader-wrap launch-class-" + counter
      );
      if (counter === 4) {
        counter = 1;
      } else {
        counter++;
      }
    }, 25000);

  var divs = jQuery('div[id^="dollie-content-"]'),
    i = 0;

  divs.hide();

  (function launchCycle() {
    divs.eq(i).fadeIn(500).delay(15000).fadeOut(500, launchCycle);

    i = ++i % divs.length;
  })();
});
