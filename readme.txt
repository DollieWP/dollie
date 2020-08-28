=== Dollie ===
Contributors: dollie
Requires at least: 4.9
Tested up to: 5.5
Stable tag: 3.2.3
License: MIT
License URI: https://opensource.org/licenses/MIT

A turn-key solution for WordPress product vendors, agencies and developers to start offering white-labeled cloud services and SaaS/WaaS to their customers.
== Description ==

A turn-key solution for WordPress product vendors, agencies and developers to start offering white-labeled cloud services and SaaS/WaaS to their customers.

== Changelog ==

= 3.2.4 =
* Added extra checks for Client Restricted Access functionality.

= 3.2.3 =
* WP 5.5 compatibility. Changed dashboard query parameter from "page" to "section"

= 3.2.2 =
* Extra check on undeploy cron so a site will be removed locally if it was already removed from dollie.io

= 3.2.1 =
* Added daily scheduled task to synchronize sites with Dollie dashboard. Removed sites from dollie.io panel will automatically get removed from your partner site.

= 3.2.0 =
* Client-Deployed Site Permissions. Read more: https://getdollie.com/feature-release-client-deployed-site-permissions/
* One time login token for client sites. Starting with this version all the login tokens for client site authentication are unique for better security.

= 3.1.0 =
* Dollie now allows you to translate all Form labels, messages and instructions.
You can use Loco Translate for an easy translation experience right from your admin dashbaord.
* Improved blueprints shortcode. You can use wordPress post order field to arrange the displayed blueprints. Added orderby and order parameters.

= 3.0.4 =
* Fix Woocommerce subscription limits when using renewals/upgrades/downgrades
* Show tooltip for non admins too - dashicons aren't loaded if no admin bar
* Fix for [dollie-blueprints] shortcode number of items showing

= 3.0.3 =
* Use the correct blueprint id on launch new site

= 3.0.2 =
* Make sure user has access to deployed sites

= 3.0.0 =
* Updates to structure
* Developer friendly code
