<?php
/**
 * Package: OpenLab Plugins class
 *
 * Part of the OpenLab package.
 *
 * @package    Commons_In_A_Box
 * @subpackage Package
 * @since      1.1.0
 */

/**
 * Plugin manifest for the DOLLIE_SETUP OpenLab package.
 *
 * @since 1.1.0
 */
class CBox_Plugins_OpenLab {
	/**
	 * Initiator.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()} for spec.
	 */
	public static function init( $instance ) {
		self::register_required_plugins( $instance );
		self::register_dependency_plugins( $instance );
		self::register_recommended_plugins( $instance );
		self::register_optional_plugins( $instance );
		self::register_installonly_plugins( $instance );
	}

	/**
	 * Register required plugins.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()}.
	 */
	protected static function register_required_plugins( $instance ) {
		// BuddyPress
		call_user_func( $instance, array(
			'plugin_name'       => 'BuddyPress',
			'dollie_setup_name'         => __( 'BuddyPress', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'BuddyPress provides the core functionality of Commons In A Box, including groups and user profiles.', 'commons-in-a-box' ),
			'version'           => '9.2.0',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-plugin',
			'admin_settings'    => 'options-general.php?page=bp-components',
			'network_settings'  => 'settings.php?page=bp-components'
		) );

		// DOLLIE_SETUP-OpenLab Core
		call_user_func( $instance, array(
			'plugin_name'       => 'DOLLIE_SETUP-OpenLab Core',
			'dollie_setup_name'         => __( 'OpenLab Core', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'Core functionality for DOLLIE_SETUP-OpenLab.', 'commons-in-a-box' ),
			'version'           => '1.3.2',
			'download_url'      => DOLLIE_SETUP_PLUGIN_DIR . 'includes/zip/cbox-openlab-core-1.3.2.zip',
			//'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-plugin',
		) );

		// bbPress
		call_user_func( $instance, array(
			'plugin_name'       => 'bbPress',
			'dollie_setup_name'         => __( 'bbPress Forums', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'Sitewide and group-specific discussion forums.', 'commons-in-a-box' ),
			'version'           => '2.6.9',
			'download_url'      => 'http://downloads.wordpress.org/plugin/bbpress.2.6.9.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/bbpress',
			'admin_settings'    => 'options-general.php?page=bbpress',
			'network_settings'  => 'root-blog-only',
			'network'           => false,
			'hide'              => dollie_setup_is_main_site()
		) );

		// BuddyPress Docs
		call_user_func( $instance, array(
			'plugin_name'       => 'BuddyPress Docs',
			'dollie_setup_name'         => __( 'Docs', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'Allows your members to collaborate on wiki-style Docs.', 'commons-in-a-box' ),
			'version'           => '2.1.6',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => 'http://downloads.wordpress.org/plugin/buddypress-docs.2.1.6.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-docs',
			'admin_settings'    => 'edit.php?post_type=bp_doc',
			'network_settings'  => 'root-blog-only',
			'network'           => false
		) );

		// BuddyPress Docs In Group
		call_user_func( $instance, array(
			'plugin_name'       => 'BuddyPress Docs In Group',
			'dollie_setup_name'         => __( 'Docs in Group', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'Put BuddyPress Docs into the Group context.', 'commons-in-a-box' ),
			'version'           => '1.0.2',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => DOLLIE_SETUP_PLUGIN_DIR . 'includes/zip/buddypress-docs-in-group-1.0.2.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-docs',
			'network_settings'  => 'root-blog-only',
			'network'           => false,
		) );

		// BP Group Documents
		call_user_func( $instance, array(
			'plugin_name'       => 'BP Group Documents',
			'dollie_setup_name'         => __( 'Group Documents', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'Allow your members to attach documents to groups.', 'commons-in-a-box' ),
			'version'           => '1.12.3',
			'depends'           => 'BuddyPress (>=2.7)',
			'download_url'      => 'http://downloads.wordpress.org/plugin/bp-group-documents.1.12.3.zip',
			'documentation_url' => '', // @todo
			'network_settings'  => 'settings.php?page=bp-group-documents-settings',
			'network'          => false
		) );

		// BuddyPress Group Email Subscription
		call_user_func( $instance, array(
			'plugin_name'       => 'BuddyPress Group Email Subscription',
			'dollie_setup_name'         => __( 'Group Email Subscription', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'Allows your community members to receive email notifications of activity within their groups.', 'commons-in-a-box' ),
			'depends'           => 'BuddyPress (>=1.5)',
			'version'           => '4.0.1',
			'download_url'      => 'http://downloads.wordpress.org/plugin/buddypress-group-email-subscription.4.0.1.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-group-email-subscription',
			'admin_settings'    => 'admin.php?page=ass_admin_options', // this doesn't work for BP_ENABLE_MULTIBLOG
			'network_settings'  => 'root-blog-only'
		) );

		/*
		// This is custom-developed and is only included in the main openlab repo
		// We'll break it out into its own.
		// BP Customizable Group Categories
		call_user_func( $instance, array(
			'plugin_name'       => 'BP Customizable Group Categories',
			'dollie_setup_name'         => __( 'BP Customizable Group Categories', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'Categories for BuddyPress Groups', 'commons-in-a-box' ),
			'version'           => '1.0.0',
			'download_url'      => '', // @todo
			'documentation_url' => '', // @todo
			'network'           => false
		) );
		*/

		// Invite Anyone
		call_user_func( $instance, array(
			'plugin_name'       => 'Invite Anyone',
			'dollie_setup_name'         => __( 'Invite Anyone', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'An enhanced interface for inviting existing community members to groups, as well as a powerful tool for sending invitations, via email, to potential members.', 'commons-in-a-box' ),
			'version'           => '1.4.2',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => 'http://downloads.wordpress.org/plugin/invite-anyone.1.4.2.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/invite-anyone',
			'admin_settings'    => 'admin.php?page=invite-anyone',
			'network_settings'  => 'admin.php?page=invite-anyone',
			'network'           => false
		) );

		// CAC Featured Content
		call_user_func( $instance, array(
			'plugin_name'       => 'CAC Featured Content',
			'dollie_setup_name'         => __( 'Featured Content Widget', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'Provides a widget that allows you to select among five different content types to feature in a widget area.', 'commons-in-a-box' ),
			'version'           => '1.0.9',
			'download_url'      => 'http://downloads.wordpress.org/plugin/cac-featured-content.1.0.9.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/cac-featured-content',
		) );

		// More Privacy Options
		call_user_func( $instance, array(
			'plugin_name'       => 'More Privacy Options',
			'dollie_setup_name'         => __( 'More Privacy Options', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'Adds more blog privacy options for your users.', 'commons-in-a-box' ),
			'version'           => '4.6',
			'download_url'      => 'http://downloads.wordpress.org/plugin/more-privacy-options.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/more-privacy-options',
			'network_settings'  => 'settings.php#menu'
		) );

		// OpenLab Portfolio
		call_user_func( $instance, array(
			'plugin_name'       => 'OpenLab Portfolio',
			'dollie_setup_name'         => __( 'Portfolio', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'WordPress portfolio tools.', 'commons-in-a-box' ),
			'version'           => '1.1.0',
			'download_url'      => DOLLIE_SETUP_PLUGIN_DIR . 'includes/zip/openlab-portfolio-1.1.0.zip',
		) );

		// OpenLab Badges
		call_user_func( $instance, array(
			'plugin_name'       => 'OpenLab Badges',
			'dollie_setup_name'         => __( 'Badges', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'Customizable badges for your OpenLab groups.', 'commons-in-a-box' ),
			'version'           => '1.0.0',
			'download_url'      => DOLLIE_SETUP_PLUGIN_DIR . 'includes/zip/openlab-badges-1.0.0.zip',
		) );
	}

	/**
	 * Register dependency plugins.
	 *
	 * The reason why this is done is Plugin Dependencies (PD) does not know the
	 * download URL for dependent plugins. So if a dependent plugin is deemed
	 * incompatible by PD (either not installed or incompatible version), we can
	 * easily install or upgrade that plugin.
	 *
	 * This is designed to avoid pinging the WP.org Plugin Repo API multiple times
	 * to grab the download URL, and is much more efficient for our usage.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()}.
	 */
	protected static function register_dependency_plugins( $instance ) {
		// BuddyPress
		call_user_func( $instance, array(
			'plugin_name'  => 'BuddyPress',
			'type'         => 'dependency',
			'download_url' => 'http://downloads.wordpress.org/plugin/buddypress.9.2.0.zip'
		) );

		// Event Organiser
		call_user_func( $instance, array(
			'plugin_name'  => 'Event Organiser',
			'type'         => 'dependency',
			'version'      => '3.10.8',
			'download_url' => 'http://downloads.wordpress.org/plugin/event-organiser.3.10.8.zip',
			'network'      => false,
			'hide'         => dollie_setup_is_main_site()
		) );

		// Braille
		call_user_func( $instance, array(
			'plugin_name'  => 'Braille',
			'type'         => 'dependency',
			'version'      => '0.0.6',
			'download_url' => 'http://downloads.wordpress.org/plugin/braille.0.0.6.zip',
			'network'      => false,
			'hide'         => dollie_setup_is_main_site()
		) );
	}

	/**
	 * Register recommended plugins.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()}.
	 */
	protected static function register_recommended_plugins( $instance ) {
		// BP Event Organiser
		$instance( array(
			'plugin_name'       => 'BuddyPress Event Organiser',
			'type'              => 'recommended',
			'dollie_setup_name'         => __( 'Events', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'Allows your members to create a calendar for themselves and to attach specific events to groups.', 'commons-in-a-box' ),
			'version'           => '1.2.0',
			'depends'           => 'BuddyPress (>=1.5), Event Organiser (>=3.1)',
			'download_url'      => DOLLIE_SETUP_PLUGIN_DIR . 'includes/zip/bp-event-organiser-1.2.0.zip',
			'network'           => false
		) );
	}

	/**
	 * Register optional plugins.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()}.
	 */
	protected static function register_optional_plugins( $instance ) {
		// BuddyPress Reply By Email
		// @todo Still need to add it in the wp.org plugin repo! Using Github for now.
		call_user_func( $instance, array(
			'plugin_name'       => 'BuddyPress Reply By Email',
			'type'              => 'optional',
			'dollie_setup_name'         => __( 'Reply By Email', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( "Reply to content from all over the community from the comfort of your email inbox", 'commons-in-a-box' ),
			'version'           => '1.0-RC10',
			'depends'           => 'BuddyPress (>=1.5)',
			'download_url'      => DOLLIE_SETUP_PLUGIN_DIR . 'includes/zip/bp-reply-by-email-1.0-RC10.zip',
			'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-reply-by-email',
			'admin_settings'    => is_multisite() ? 'options-general.php?page=bp-rbe' : 'admin.php?page=bp-rbe',
			'network_settings'  => 'root-blog-only'
		) );

		// BP Braille
		call_user_func( $instance, array(
			'plugin_name'       => 'BP Braille',
			'type'              => 'optional',
			'dollie_setup_name'         => __( 'Braille Support', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'An addon for the Braille plugin providing support for BuddyPress Group Forums and Private Messaging', 'commons-in-a-box' ),
			'version'           => '0.2.0',
			'depends'           => 'Braille (>=0.0.3)',
			'download_url'      => DOLLIE_SETUP_PLUGIN_DIR . 'includes/zip/bp-braille-master.zip',
			'documentation_url' => 'https://wordpress.org/plugins/braille',
			'network'           => false
		) );
	}

	/**
	 * Register install-only plugins.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()}.
	 */
	protected static function register_installonly_plugins( $instance ) {
		call_user_func( $instance, array(
			'plugin_name'       => 'Anthologize',
			'type'              => 'install-only',
			'dollie_setup_name'         => __( 'Anthologize', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'Create ebooks from your blog posts or other external content.', 'commons-in-a-box' ),
			'version'           => '0.8.0',
			'download_url'      => 'http://downloads.wordpress.org/plugin/anthologize.0.8.0.zip',
			'documentation_url' => 'https://wordpress.org/plugins/anthologize',
		) );

		call_user_func( $instance, array(
			'plugin_name'       => 'Braille',
			'type'              => 'install-only',
			'dollie_setup_name'         => __( 'Braille', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'Provides a number of Braille-related services to WordPress.', 'commons-in-a-box' ),
			'documentation_url' => 'https://wordpress.org/plugins/braille',
		) );

		call_user_func( $instance, array(
			'plugin_name'       => 'PressForward',
			'type'              => 'install-only',
			'dollie_setup_name'         => __( 'PressForward', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'A plugin providing an editorial workflow for content aggregation and curation within the WordPress dashboard. Designed for bloggers and editorial teams wishing to collect, discuss, and share content from a variety of sources on the open web.', 'commons-in-a-box' ),
			'version'           => '5.2.8',
			'download_url'      => 'http://downloads.wordpress.org/plugin/pressforward.5.2.8.zip',
			'documentation_url' => 'https://wordpress.org/plugins/pressforward',
		) );

		// OpenLab Attributions
		call_user_func( $instance, array(
			'plugin_name'       => 'OpenLab Attributions',
			'type'              => 'install-only',
			'dollie_setup_name'         => __( 'Attributions', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'A plugin for creating inline attributions for site content.', 'commons-in-a-box' ),
			'version'           => '2.0.0',
			'download_url'      => DOLLIE_SETUP_PLUGIN_DIR . 'includes/zip/openlab-attributions-2.0.0.zip',
		) );

		// OpenLab Private Comments
		call_user_func( $instance, array(
			'plugin_name'       => 'OpenLab Private Comments',
			'type'              => 'install-only',
			'dollie_setup_name'         => __( 'Private Comments', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'Private comments for sites in your network.', 'commons-in-a-box' ),
			'version'           => '1.1.1',
			'download_url'      => DOLLIE_SETUP_PLUGIN_DIR . 'includes/zip/openlab-private-comments-1.1.1.zip',
		) );

		call_user_func( $instance, array(
			'plugin_name'       => 'WP Grade Comments',
			'type'              => 'install-only',
			'dollie_setup_name'         => __( 'WP Grade Comments', 'commons-in-a-box' ),
			'dollie_setup_description'  => __( 'A plugin for instructors using their WordPress site in a course setting. Provides ability to give private feedback and/or grades to post authors, all without leaving the familiar commenting interface.', 'commons-in-a-box' ),
			'version'           => '1.4.4',
			'download_url'      => 'http://downloads.wordpress.org/plugin/wp-grade-comments.1.4.4.zip',
			'documentation_url' => 'https://wordpress.org/plugins/wp-grade-comments',
		) );
	}
}
