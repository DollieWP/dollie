=== ACF Tooltip ===
Contributors: tmconnect
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=XMLKD8H84HXB4&lc=US&item_name=Donation%20for%20WordPress%20Plugins&no_note=0&cn=Add%20a%20message%3a&no_shipping=1&currency_code=EUR
Tags: acf, acfpro, advanced custom fields, instructions, tooltip
Requires at least: 4.7
Tested up to: 5.0.1
Stable tag: 1.2.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Displays ACF field instructions as tooltips

== Description ==

If an ACF field requires a longer instruction text, the layout of the edit screen is messy and a lot of space is wasted.

The ACF Tooltip plugin hides the field instructions, adds a help symbol to the field labels and generates a tooltip based on the instruction text.

= New in Version 1.2.0 =

The tooltip will not hide if moused over, allowing create a tooltip with a link inside without hiding the tooltip.

= Custom settings =

There are 7 filters that allow adjusting the design and the behavior of the tooltips.

**This plugin works only with the [ACF PRO](https://www.advancedcustomfields.com/pro/) (version 5.5.0 or higher).**

= Localizations =
* English
* Deutsch


== Installation ==

1. Upload the `acf-tooltip` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Done!


== Custom settings with filter hooks ==

There are 7 filters that allow adjusting the design and the behavior of the tooltips can be adjusted.

= Set the design of the tooltips =
`<?php
function acf_tooltip_style() {
	$style = 'qtip-acf';

	return $style;
}
add_filter('acf/tooltip/style', 'acf_tooltip_style');
?>`

The available styles can be found on the [qTip options page](http://qtip2.com/options#style) and are shown on the [qTip demo site](http://qtip2.com/demos/).

You can mix the styles; e.g. "qtip-acf qtip-rounded qtip-shadow"

If you like, you can define your own style, with the class name of your style from your own CSS file (see next filter).

The qtip-acf style is the standard style, which is set without a filter.

= Define your own CSS file =

`<?php
function acf_tooltip_css() {
	$css_file = get_bloginfo('template_url') . '/qtip-own.css'; // if the file is saved in your themes folder

	return $css_file;
}
add_filter('acf/tooltip/css', 'acf_tooltip_css');
?>`

You will find a 'qtip-example-style.css' in the '/assets/css' folder.

= Positioning the corner of the tooltip =

`<?php
function acf_tooltip_position_my() {
	$position_my = 'center left';

	return $position_my;
}
add_filter('acf/tooltip/position/my', 'acf_tooltip_position_my');
?>`

= Position in relation to the tooltip icon =

`<?php
function acf_tooltip_position_at() {
	$position_at = 'center right';

	return $position_at;
}
add_filter('acf/tooltip/position/at', 'acf_tooltip_position_at');
?>`

Check out the [qTip demo site](http://qtip2.com/demos/) to find your perfect positioning.

= Apply tooltips only to fields with specific class =

`<?php
function acf_tooltip_class() {
	$class = 'with__tooltip'; // edit this to your prefered class name

	return $class;
}
add_filter('acf/tooltip/class/only', 'acf_tooltip_class');
?>`

Add the class to the fields where you want to show tooltips.

= Exclude tooltips on fields with specific class =

`<?php
function acf_tooltip_class_exclude() {
	$class = 'no__tooltip'; // edit this to your prefered class name

	return $class;
}
add_filter('acf/tooltip/class/exclude', 'acf_tooltip_class_exclude');
?>`

Add the class to the fields where you *don't* want to show tooltips.

= Add tooltips to the Field Editor =

With this filter, you can specify whether the instructions in the Field Editor are displayed as tooltips as well. By default, the instructions are displayed.

`<?php
add_filter('acf/tooltip/fieldeditor', '__return_true');
?>`

== Screenshots ==

1. ACF Tooltip in standard mode


== Changelog ==

= v1.2.2 =
* Fixed compatibilty error of 1.2.1 update if ACF is < 5.7.0

= v1.2.1 =
* Fixed [White-space bug](https://wordpress.org/support/topic/white-space-where-instructions-would-be-2)

= v1.2.0 =
* Tooltip is no longer hidden on hover

= v1.1.0 =
* Changed class name to prevent future conflicts with ACF

= v1.0.0 =
* Initial release of this plugin, tested and stable.
