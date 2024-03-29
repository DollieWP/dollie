<?php
/**
 * Package: Agency Settings class
 *
 * Part of the CLassic package.
 *
 * @package    Dollie_Setup
 * @subpackage Package
 * @since      1.1.0
 */

/**
 * Admin settings page for the Agency package.
 *
 * @since 1.0-beta2
 *
 * @package Dollie_Setup
 * @subpackage Adminstration
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setup the DOLLIE_SETUP settings area for the Agency package.
 *
 * @since 1.0-beta2
 * @since 1.1.0 Renamed class from Dollie_Setup_Settings to Dollie_Setup_Settings_Agency.
 */
class Dollie_Setup_Settings_Agency {

	/**
	 * Static variable to hold our various settings
	 *
	 * @var array
	 */
	private static $settings = array();

	/**
	 * The settings options key used by the Agency package
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	public static $settings_key = '_dollie_setup_admin_settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// setup our hooks
		$this->setup_hooks();
	}

	/**
	 * Setup our hooks.
	 */
	private function setup_hooks() {
		add_action( 'admin_init', array( $this, 'register_settings_hook' ) );

		// UNUSED
		//add_action( 'dollie_setup_admin_menu', array( $this, 'setup_settings_page' ), 20 );
	}

	/** SETTINGS-SPECIFIC *********************************************/

	/**
	 * Public function to call our private register_settings() method.
	 *
	 * @since 1.0.5
	 */
	public function register_settings_hook() {
		$this->register_settings();
	}

	/**
	 * Register settings.
	 *
	 * Used to render the checkboxes as well as the format to load these settings
	 * on the frontend.
	 *
	 * @see Dollie_Setup_Settings::register_setting()
	 */
	private function register_settings() {
		// setup BP settings array
		$bp_settings = array();

		$bp_settings[] = array(
			'label'       => __( 'Member Profile Default Tab', 'dollie-setup' ),
			'description' => __( 'On a member page, set the default tab to "Profile" instead of "Activity".', 'dollie-setup' ),
			'class_name'  => 'Dollie_Setup_BP_Profile_Tab', // this will load up the corresponding class; class must be created
		);

		if ( function_exists( 'bp_is_active' ) && bp_is_active( 'groups' ) &&
			( function_exists( 'bbp_is_group_forums_active' ) && bbp_is_group_forums_active() ) ||
			( function_exists( 'bp_forums_is_installed_correctly' ) && bp_forums_is_installed_correctly() ) ) {
			$bp_settings[] = array(
				'label'       => __( 'Group Forum Default Tab', 'dollie-setup' ),
				'description' => __( 'On a group page, set the default tab to "Forum" instead of "Activity".', 'dollie-setup' ),
				'class_name'  => 'Dollie_Setup_BP_Group_Forum_Tab',
			);
		}

		// BuddyPress
		self::register_setting(
			array(
				'plugin_name' => 'BuddyPress',
				'key'         => 'bp',
				'settings'    => $bp_settings,
			)
		);

		// BuddyPress Group Email Subscription
		self::register_setting(
			array(
				'plugin_name' => 'BuddyPress Group Email Subscription',
				'key'         => 'ges',
				'settings'    => array(
					array(
						'label'       => __( 'Forum Full Text', 'dollie-setup' ),
						'description' => __( 'Check this box if you would like the full text of bbPress forum posts to appear in email notifications.', 'dollie-setup' ),
						'class_name'  => 'Dollie_Setup_GES_bbPress2_Full_Text',
					),
				),
			)
		);
	}

	/**
	 * Register a plugin's settings in DOLLIE_SETUP.
	 *
	 * Updates our private, static $settings variable in the process.
	 *
	 * @see Dollie_Setup_Admin_Settings::register_settings()
	 */
	private function register_setting( $args = '' ) {
		$defaults = array(
			'plugin_name' => false,   // (required) the name of the plugin as in the plugin header
			'key'         => false,   // (required) this is used to identify the plugin;
									  // also used for the filename suffix, see /includes/frontend.php
			'settings'    => array(), // (required) multidimensional array
		);

		$r = wp_parse_args( $args, $defaults );

		if ( empty( $r['plugin_name'] ) || empty( $r['key'] ) || empty( $r['settings'] ) ) {
			return false;
		}

		self::$settings[ $r['plugin_name'] ]['key']      = $r['key'];
		self::$settings[ $r['plugin_name'] ]['settings'] = $r['settings'];

	}

	/** ADMIN PAGE-SPECIFIC *******************************************/

	/**
	 * Setup DOLLIE_SETUP's settings menu item.
	 */
	public function setup_settings_page() {
		// see if DOLLIE_SETUP is fully setup
		if ( ! dollie_setup_is_setup() ) {
			return;
		}

		// add our settings page
		$page = add_submenu_page(
			'dollie_setup',
			__( 'Dollie Setup Settings', 'dollie-setup' ),
			__( 'Settings', 'dollie-setup' ),
			'install_plugins', // todo - map cap?
			'dollie_setup-settings',
			array( $this, 'admin_page' )
		);

		// validate any settings changes submitted from the DOLLIE_SETUP settings page
		add_action( "load-{$page}", array( $this, 'validate_settings' ) );

	}

	/**
	 * Validates settings submitted from the settings admin page.
	 */
	public function validate_settings() {
		if ( empty( $_REQUEST['dollie_setup-settings-save'] ) ) {
			return;
		}

		check_admin_referer( 'dollie_setup_settings_options' );

		// get submitted values
		$submitted = (array) $_REQUEST['dollie_setup_settings'];

		// update settings
		bp_update_option( self::$settings_key, $submitted );

		// add an admin notice
		$prefix = is_network_admin() ? 'network_' : '';
		add_action(
			$prefix . 'admin_notices',
			function() {
				echo '<div class="updated"><p><strong>' . __( 'Settings saved.', 'dollie-setup' ) . '</strong></p></div>';
			}
		);
	}

	/**
	 * Renders the settings admin page.
	 */
	public function admin_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'Dollie Setup Settings', 'dollie-setup' ); ?></h2>

			<p><?php _e( 'DOLLIE_SETUP can configure some important options for certain plugins.', 'dollie-setup' ); ?>

			<form method="post" action="">
				<?php $this->render_options(); ?>

				<?php wp_nonce_field( 'dollie_setup_settings_options' ); ?>

				<p><input type="submit" value="<?php _e( 'Save Changes', 'dollie-setup' ); ?>" class="button-primary" name="dollie_setup-settings-save" /></p>
			</form>
		</div>

		<?php
	}

	/**
	 * Renders all our checkboxes on the settings admin page.
	 */
	private function render_options() {
		// get all installed Dollie plugins
		$dollie_setup_plugins = dollie_setup()->plugins->get_plugins();

		// get all Dollie plugins by name
		$active = Dollie_Setup_Admin_Plugins::organize_plugins_by_state( $dollie_setup_plugins );

		// sanity check.  will probably never encounter this use-case.
		if ( empty( $active ) ) {
			return false;
		}

		// get only active plugins and flip them for faster processing
		$active = array_flip( $active['deactivate'] );

		// get saved settings
		$dollie_setup_settings = bp_get_option( self::$settings_key );

		// parse and output settings
		foreach ( self::$settings as $plugin => $settings ) {
			// if plugin doesn't exist, don't show the settings for that plugin
			if ( ! isset( $active[ $plugin ] ) ) {
				continue;
			}

			// grab the key so we can reference it later
			$key = $settings['key'];

			// drop the key for the $settings loop
			unset( $settings['key'] );
			?>
			<h3><?php echo $plugin; ?></h3>

			<table class="form-table">
			<?php foreach ( $settings['settings'] as $setting ) : ?>

				<tr valign="top">
					<th scope="row"><?php echo $setting['label']; ?></th>
					<td>
						<input id="<?php echo sanitize_title( $setting['label'] ); ?>" name="dollie_setup_settings[<?php echo $key; ?>][]" type="checkbox" value="<?php echo $setting['class_name']; ?>" <?php $this->is_checked( $setting['class_name'], $dollie_setup_settings, $key ); ?>  />
						<label for="<?php echo sanitize_title( $setting['label'] ); ?>"><?php echo $setting['description']; ?></label>
					</td>
				</tr>

			<?php endforeach; ?>
			</table>
			<?php
		}

	}

	/**
	 * Helper function to see if an option is checked.
	 */
	private function is_checked( $class_name, $settings, $key ) {
		if ( isset( $settings[ $key ] ) && in_array( $class_name, (array) $settings[ $key ] ) ) {
			echo 'checked="checked"';
		}
	}
}
