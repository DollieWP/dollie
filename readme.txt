=== Dollie ===
Contributors: GetDollie
Tags: hosting, waas, wordpress hosting, sell hosting
Requires at least: 5.0
Tested up to: 5.6.1
Requires PHP: 5.6
Stable tag: 4.1.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

An eCommerce style turn-key solution for WordPress product vendors, agencies, and developers to offer White-labeled hosting services and SaaS/WaaS.

== Description ==

Sell hosting services right from your WordPress site.
An eCommerce style turn-key solution for WordPress product vendors, agencies, and developers to offer White-labeled hosting services and SaaS/WaaS.

Dollie is a cloud automation software for WordPress. Dollie lets you convert one-time clients into recurring revenue machines, all managed through your WordPress install.

To start deploying sites you first need to connect your site to our service. This is an easy process to follow right from your admin area and it will require you to create an account at getdollie.com
Learn more about us at [GetDollie.com](https://getdollie.com/?utm_source=wp-repo&utm_medium=link&utm_campaign=readme)

https://www.youtube.com/watch?v=S5QC7jaoGCw

### Features

**Launch Your SaaS/WaaS Platform**
Stop giving away your customers revenue to hosting companies every month by offering your own branded fully managed hosting WordPress solution.
With Dollie you can build your own WordPress products and turn them into a click-to-launch solution akin to SquareSpace or WIX.

[Learn More](https://getdollie.com/enterprise-cloud-automation-for-wordpress/?utm_source=wp-repo&utm_medium=link&utm_campaign=readme)

**Take Your WordPress Agency to the next level**
Dollie Transforms the way your Agency sells WordPress services. It is the first Cloud Automation Platform to build, launch, and sell your WordPress services directly under your brand & domain.

Future-proof your agency and impress your clients by offering them a one-stop turnkey solution for all their website needs.

[Learn More](https://getdollie.com/wordpress-agencies/?utm_source=wp-repo&utm_medium=link&utm_campaign=readme)

**Blueprints**
Create re-usable blueprints for each product you offer and allow new or existing customers to launch fully configured and ready to go sites with the click of a button

**Lightning Fast Deployments**
Dollie deploys WordPress sites in just 30 seconds.
Combine that with your own custom Blueprints and your customers will love using your platform to spin up sites.

**Theme Friendly**
Dollie integrates seamlessly with any theme, so you can offer your Dollie powered services through your existing agency website

**Developer Friendly**
Dollie comes with pre-made page templates that let you get started selling quickly.
Our complex site dashboard can be edited using our pre-made Elementor Widgets, allowing you to quickly and creatively redesign your website Dashboard's however you'd like

**Woocommerce integration**
Dollie seamlessly integrates with Woocommerce so you can sell your hosting/WaaS services alongside your existing offering

**Your business, your prices**
You set your own prices and you receive the payments directly on your own Woocommerce site

### Coming soon: Easy Digital Downloads integration

### Documentation and Support
- For more information about features, FAQs and documentation, check out our website at [GetDollie.com](https://getdollie.com/?utm_source=wp-repo&utm_medium=link&utm_campaign=readme).

### Minimum Requirements

* WordPress 5.0 or greater
* PHP version 7.0 or greater
* MySQL version 5.0 or greater
* Woocommerce 4.0

### We recommend your host supports:

* PHP version 7.0 or greater
* MySQL version 5.6 or greater
* WordPress Memory limit of 64 MB or greater (128 MB or higher is preferred)

### Privacy Policy
Dollie uses Appsero SDK to collect some telemetry data upon user's confirmation. This helps us to troubleshoot problems faster & make product improvements.
Appsero SDK **does not gather any data by default.** The SDK only starts gathering basic telemetry data **when a user allows it via the admin notice**. We collect the data to ensure a great user experience for all our users.
Integrating Appsero SDK **DOES NOT IMMEDIATELY** start gathering data, **without confirmation from users in any case.**

== Installation ==

1. Install using the WordPress built-in Plugin installer, or Extract the zip file and drop the contents in the `wp-content/plugins/` directory of your WordPress installation.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to Wp admin - Dollie and click Connect with Dollie
4. Register an account and link your site with Dollie.
5. You can now start deploying sites with a click of a button

== Frequently Asked Questions ==

**Do I have to move my existing website/storefront to use Dollie?**
Absolutely not, Dollie can be installed on-premise, in any infrastructure/WebHost that supports WordPress, right alongside your existing WordPress storefront.

**What access to the features of Dollie do your customers see?**
You can control which Site Management features are available to your customers/clients. By using the Access Control options in Dollie you enable and disable specific features (for example giving clients the ability to create backups of their site, but disabling the advanced Developer Features)

**How can I (or my client/customers) connect a domain to my website**
Dollie comes with a “Domain Setup Wizard” that makes it easy for even novice users to connect their domain to any of the sites they manage. Through several steps, they are guided through the entire process with several checks along the way to make sure they don’t make any mistakes

**What domain do these sites deploy under?**
Dollie has a default domain to let you get started selling quickly. This means sites will be deployed under a subdomain of dollie.io e.g. client1.example.dollie.io

Dollie also lets you add your own deployment domain so that you can completely offer your services through your own brand and domain

**Are you able to access the deployed containers through SSH**
Yes, you can connect to every deployed container via SSH (for WP CLI etc) or SFTP (file management) to manage your containers.

**From the customer end, do the customers get to access billing information and invoices overview?**
Absolutely! All subscriptions and billing are done through your own Dollie installation, using the WooCommerce (Subscriptions) integration. It’s all under your domain, which you can extend and customize as you see fit.

**How frequent are your backups?**
Our backups run every 24hr and are kept for 28 days

**What happens with Support?**
Our team is here to provide you with technical infrastructure support only, for example, if a container has issues unrelated to the WordPress installation of your customer. This support is delivered through Slack and through our ticketing support channels. We do not provide direct support to your customers, but to you and your support team only.

== Screenshots ==


== Changelog ==
= 4.1.5 =
* Initial WP repo release

= 4.0.0 =
* Major Update! Dollie is now theme independent and it can be used with any theme
* Build all Dollie templates using Elementor
* Live notifications on all Dollie actions

= 3.2.7 =
* Some performance improvements and cron jobs optimization

= 3.2.6 =
* Site screenshot moved to own solution
* ACF update

= 3.2.5 =
* When launching a new site, show or hide the Default Blueprint option.

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
