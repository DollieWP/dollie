<?php
/**
 * Package: Classic Plugins class
 *
 * Part of the CLassic package.
 *
 * @package    Commons_In_A_Box
 * @subpackage Package
 * @since      1.1.0
 */

/**
 * Plugin manifest for the CBOX Classic package.
 *
 * @since 1.1.0
 */
class CBox_Plugins_Classic {
	/**
	 * Initiator.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()} for spec.
	 */
	public static function init( $instance ) {
		self::register_required_plugins( $instance );
		self::register_recommended_plugins( $instance );
		self::register_optional_plugins( $instance );
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
			'plugin_name'       => 'WooCommerce',
			'cbox_name'         => __( 'WooCommerce', 'commons-in-a-box' ),
			'cbox_description'  => __( 'WooCommerce provides the core functionality for selling your platform services with Dollie.', 'commons-in-a-box' ),
			'version'           => '6.2.0',
			'documentation_url' => 'https://partners.getdollie.com/?s=WooCommerce&ht-kb-search=1&lang=',
			'admin_settings'    => 'admin.php?page=wc-settings',
			'network_settings'  => 'admin.php?page=wc-settings'
		) );

		// call_user_func( $instance, array(
		// 	'plugin_name'       => 'WooCommerce Subscription',
		// 	'cbox_name'         => __( 'WooCommerce Subscription', 'commons-in-a-box' ),
		// 	'cbox_description'  => __( 'WooCommerce subscriptions gives you the ability to set up recurring subscriptions for your customers directly tied to your services.', 'commons-in-a-box' ),
		// 	'version'           => '3.0.10',
		// 	'documentation_url' => 'https://partners.getdollie.com/?s=WooCommerce&ht-kb-search=1&lang=',
		// 	'admin_settings'    => 'admin.php?page=wc-settings',
		// 	'network_settings'  => 'admin.php?page=wc-settings'
		// ) );

		call_user_func(
			$instance,
			array(
				'plugin_name'       => 'Elementor',
				'cbox_name'         => __('Elementor', 'commons-in-a-box'),
				'cbox_description'  => __('The Elementor Page Builder allows you to fully customize your Customer/Client Dashboard quickly and easily.', 'commons-in-a-box'),
				'version'           => '3.5.5',
				'documentation_url' => 'https://partners.getdollie.com/?s=Elementor&ht-kb-search=1&lang=',
				'admin_settings'    => 'admin.php?page=elementor',
				'network_settings'  => 'admin.php?page=elementor'
			)
		);

		/**
		 * Register CBOX's dependency plugins internally.
		 *
		 * The reason why this is done is Plugin Dependencies (PD) does not know the download URL for dependent plugins.
		 * So if a dependent plugin is deemed incompatible by PD (either not installed or incompatible version),
		 * we can easily install or upgrade that plugin.
		 *
		 * This is designed to avoid pinging the WP.org Plugin Repo API multiple times to grab the download URL,
		 * and is much more efficient for our usage.
		 *
		 * @see CBox_Plugins::register_plugin()
		 */
		call_user_func( $instance, array(
			'plugin_name'  => 'WooCommerce',
			'type'         => 'dependency',
			'download_url' => 'https://downloads.wordpress.org/plugin/woocommerce.6.2.0.zip'
		) );

		// call_user_func(
		// 	$instance,
		// 	array(
		// 		'plugin_name'  => 'WooCommerce Subscriptions',
		// 		'type'         => 'dependency',
		// 		'download_url' => 'https://api.getdollie.com/releases/?action=download&slug=woocommerce-subscriptions'
		// 	)
		// );

		call_user_func(
			$instance,
			array(
				'plugin_name'  => 'Elementor',
				'type'         => 'dependency',
				'download_url' => 'https://downloads.wordpress.org/plugin/elementor.3.5.5.zip'
			)
		);
	}

	/**
	 * Register recommended plugins.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()}.
	 */
	protected static function register_recommended_plugins( $instance ) {
		// BuddyPress Docs
		call_user_func( $instance, array(
			'plugin_name'       => 'User Switching',
			'type'              => 'user-switching',
			'cbox_name'         => __( 'User Switching', 'commons-in-a-box' ),
			'cbox_description'  => __( 'Allows you to quickly log in as one of your clients or customers.', 'commons-in-a-box' ),
			'version'           => '1.5.8',
			'download_url'      => 'https://downloads.wordpress.org/plugin/user-switching.1.5.8.zip',
			'documentation_url' => 'https://wordpress.org/plugins/user-switching',
			'admin_settings'    => 'users.php',
			'network_settings'  => 'root-blog-only',
			'network'           => false
		) );

		// BuddyPress Docs Wiki


		// // Custom Profile Filters for BuddyPress
		// call_user_func( $instance, array(
		// 	'plugin_name'       => 'Two Factor Authentication',
		// 	'type'              => 'recommended',
		// 	'cbox_name'         => __( 'Two Factor Authentication', 'commons-in-a-box' ),
		// 	'cbox_description'  => __( 'Allows an easy way for you to secure your Administration user accounts, by setting up two factor login auth.', 'commons-in-a-box' ),
		// 	'version'           => '0.7.1',
		// 	'download_url'      => 'https://downloads.wordpress.org/plugin/two-factor.zip',
		// 	'documentation_url' => 'https://wordpress.org/plugins/two-factor/',
		// 	'network'           => false
		// ) );

	}

	/**
	 * Register optional plugins.
	 *
	 * @since 1.1.0
	 *
	 * @param callable $instance {@see CBox_Plugins::register_plugin()}.
	 */
	protected static function register_optional_plugins( $instance ) {

		// bbPress
		call_user_func($instance, array(
			'plugin_name'       => 'fluentSMTP',
			'type'              => 'optional',
			'cbox_name'         => __('FluentSMTP Email', 'commons-in-a-box'),
			'cbox_description'  => __('Our recommendation for sending reliably delivered emails from your Dollie installation to your clients/customers.', 'commons-in-a-box'),
			'version'           => '2.1.0',
			'download_url'      => 'https://downloads.wordpress.org/plugin/fluent-smtp.2.1.0.zip',
			'documentation_url' => 'https://fluentsmtp.com/docs/',
			'network_settings'  => 'root-blog-only',
			'network'           => false,
		));
		// // BuddyPress External Group Blogs
		// call_user_func( $instance, array(
		// 	'plugin_name'       => 'External Group Blogs',
		// 	'type'              => 'optional',
		// 	'cbox_name'         => __( 'External RSS Feeds for Groups', 'commons-in-a-box' ),
		// 	'cbox_description'  => __( 'Gives group creators and administrators the ability to attach external RSS feeds to groups.', 'commons-in-a-box' ),
		// 	'depends'           => 'BuddyPress (>=1.5)',
		// 	'version'           => '1.6.2',
		// 	'download_url'      => CBOX_PLUGIN_DIR . 'includes/zip/external-group-blogs-1.6.2.zip',
		// 	'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-external-group-rss',
		// 	'network'           => false
		// ) );

		// // BuddyPress Reply By Email
		// // @todo Still need to add it in the wp.org plugin repo! Using Github for now.
		// call_user_func( $instance, array(
		// 	'plugin_name'       => 'BuddyPress Reply By Email',
		// 	'type'              => 'optional',
		// 	'cbox_name'         => __( 'Reply By Email', 'commons-in-a-box' ),
		// 	'cbox_description'  => __( "Reply to content from all over the community from the comfort of your email inbox", 'commons-in-a-box' ),
		// 	'version'           => '1.0-RC10',
		// 	'depends'           => 'BuddyPress (>=1.5)',
		// 	'download_url'      => CBOX_PLUGIN_DIR . 'includes/zip/bp-reply-by-email-1.0-RC10.zip',
		// 	'documentation_url' => 'http://commonsinabox.org/documentation/plugins/buddypress-reply-by-email',
		// 	'admin_settings'    => is_multisite() ? 'options-general.php?page=bp-rbe' : 'admin.php?page=bp-rbe',
		// 	'network_settings'  => 'root-blog-only'
		// ) );
	}
}
