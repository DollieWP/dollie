<?php
/*
Plugin Name: ACF Tooltip
Plugin URI: https://wordpress.org/plugins/acf-tooltip/
Description: Displays ACF field descriptions as tooltips.
Version: 1.2.2
Author: Thomas Meyer
Author URI: https://dreihochzwo.de
Text Domain: acf_tooltip
Domain Path: /languages
License: GPLv2 or later
Copyright: Thomas Meyer
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// check if class already exists
if( !class_exists('dhz_acf_plugin_tooltip') ) :

class dhz_acf_plugin_tooltip {

	function __construct() {

		if ( ! defined( 'DHZ_SHOW_DONATION_LINK' ) )
			define( 'DHZ_SHOW_DONATION_LINK', false );

		// vars
		$this->settings = array(
			'plugin'			=> 'ACF Tooltip',
			'this_acf_version'	=> 0,
			'min_acf_version'	=> '5.5.0',
			'version'			=> '1.2.2',
			'url'				=> plugin_dir_url( __FILE__ ),
			'path'				=> plugin_dir_path( __FILE__ ),
			'plugin_path'		=> 'https://wordpress.org/plugins/acf-tooltip/'
		);

		// set text domain
		load_plugin_textdomain( 'acf_tooltip', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );

		add_action( 'admin_init', array($this, 'acf_or_die'), 11);

		// include field
		add_action('acf/input/admin_enqueue_scripts', 	array($this, 'acf_tooltip_enqueue_scripts'), 11);
		// add_action('wp_head', 	array($this, 'acf_tooltip_enqueue_scripts'), 11);

		if ( DHZ_SHOW_DONATION_LINK == true ) {

			// add plugin to $plugins array for the metabox
			add_filter( '_dhz_plugins_list', array($this, '_dhz_meta_box_data') );

			// metabox callback for plugins list and donation link
			add_action( 'add_meta_boxes_acf-field-group', array($this, '_dhz_plugins_list_meta_box') );

		}

	}

	/**
	 * Let's make sure ACF Pro is installed & activated
	 * If not, we give notice and kill the activation of ACF RGBA Color Picker.
	 * Also works if ACF Pro is deactivated.
	 */
	function acf_or_die() {

		if ( !class_exists('acf') ) {
			$this->kill_plugin();
		} else {
			$this->settings['this_acf_version'] = acf()->settings['version'];
			if ( version_compare( $this->settings['this_acf_version'], $this->settings['min_acf_version'], '<' ) ) {
				$this->kill_plugin();
			}
		}
	}

	function kill_plugin() {
		deactivate_plugins( plugin_basename( __FILE__ ) );
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		add_action( 'admin_notices', array($this, 'acf_dependent_plugin_notice') );
	}

	function acf_dependent_plugin_notice() {
		echo '<div class="error"><p>' . sprintf( __('%1$s requires ACF PRO v%2$s or higher to be installed and activated.', 'acf_tooltip'), $this->settings['plugin'], $this->settings['min_acf_version']) . '</p></div>';
	}

	/*
	*  Load the javascript and CSS files on the ACF admin pages
	*/
	function acf_tooltip_enqueue_scripts() {

		// globals
		global $wp_scripts, $wp_styles;

		$url = $this->settings['url'];
		$version = $this->settings['version'];

		// // register ACF Tooltip CSS
		// wp_register_style( 'acf-tooltip-style', "{$url}assets/css/acf-tooltip.css", 'acf-tooltip-qTip2-style', $version);

		// // register qTip2 CSS
		// wp_register_style( 'acf-tooltip-qTip2-style', "{$url}assets/vendor/qtip/jquery.qtip.min.css", false, '3.0.3');

		// // register qTip2 script
		// wp_register_script( 'acf-tooltip-qTip2-script', "{$url}assets/vendor/qtip/jquery.qtip.min.js", array('jquery'), '3.0.3', true );

		// register ACF Tooltip script
		wp_register_script( 'acf-tooltip-script', "{$url}assets/js/acf-tooltip.js", array('jquery'), $version, true );

		$acf_tooltip_fieldeditor = apply_filters( "acf/tooltip/fieldeditor", FALSE );
		$acf_tooltip_css = apply_filters( "acf/tooltip/css", "" );
		$acf_tooltip_style = apply_filters( "acf/tooltip/style", 'qtip-acf' );
		$acf_tooltip_position_my = apply_filters( "acf/tooltip/position/my", 'center left' );
		$acf_tooltip_position_at = apply_filters( "acf/tooltip/position/at", 'center right' );
		$acf_tooltip_class_only = apply_filters( "acf/tooltip/class/only", '' );
		$acf_tooltip_class_exclude = apply_filters( "acf/tooltip/class/exclude", '' );

		if ( $acf_tooltip_css != "" ) {
			// register themes css file for ACF Tooltip
			wp_register_style( 'acf-tooltip-qTip2-css', $acf_tooltip_css, 'acf-tooltip-qTip2-style' );
		}

		// localize
		wp_localize_script('acf-tooltip-script', 'acfTooltip', array(
			'style'					=> $acf_tooltip_style,
			'my'					=> $acf_tooltip_position_my,
			'at'					=> $acf_tooltip_position_at,
			'class'					=> $acf_tooltip_class_only,
			'exclude_class'			=> $acf_tooltip_class_exclude,
			'fieldeditor'			=> $acf_tooltip_fieldeditor,
			'acf_version_compare'	=> version_compare(acf()->version, '5.7.0')
		));

		// enqueue styles & scripts
		wp_enqueue_style('acf-tooltip-qTip2-style');
		wp_enqueue_style('acf-tooltip-style');
		wp_enqueue_style('acf-tooltip-qTip2-css');
		wp_enqueue_script('acf-tooltip-qTip2-script');
		wp_enqueue_script('acf-tooltip-script');

	}

	/*
	*  Add plugin to $plugins array for the metabox
	*/
	function _dhz_meta_box_data($plugins=array()) {

		$plugins[] = array(
			'title' => $this->settings['plugin'],
			'screens' => array('acf-field-group'),
			'doc' => $this->settings['plugin_path']
		);
		return $plugins;

	} // end function meta_box

	/*
	*  Add metabox for plugins list and donation link
	*/
	function _dhz_plugins_list_meta_box() {

		$plugins = apply_filters('_dhz_plugins_list', array());

		$id = 'plugins-by-dreihochzwo';
		$title = '<a style="text-decoration: none; font-size: 1em;" href="https://profiles.wordpress.org/tmconnect/#content-plugins" target="_blank">'.__("Plugins by dreihochzwo", "acf_tooltip").'</a>';
		$callback = array($this, 'show_dhz_plugins_list_meta_box');
		$screens = array();
		foreach ($plugins as $plugin) {
			$screens = array_merge($screens, $plugin['screens']);
		}
		$context = 'side';
		$priority = 'default';
		add_meta_box($id, $title, $callback, $screens, $context, $priority);


	} // end function _dhz_plugins_list_meta_box

	/*
	*  Metabox callback for plugins list and donation link
	*/
	function show_dhz_plugins_list_meta_box() {

		$plugins = apply_filters('_dhz_plugins_list', array());
		?>
			<p style="margin-bottom: 10px; font-weight:500"><?php _e("Thank you for using my plugins!", "acf_tooltip") ?></p>
			<ul style="margin-top: 0; margin-left: 5px;">
				<?php
					foreach ($plugins as $plugin) {
						?>
							<li style="list-style-type: disc; list-style-position:inside; text-indent:-13px; margin-left:13px">
								<?php
									echo $plugin['title']."<br/>";
									if ($plugin['doc']) {
										?> <a style="font-size:12px" href="<?php echo $plugin['doc']; ?>" target="_blank"><?php _e("Documentation", "acf_tooltip") ?></a><?php
									}
								?>
							</li>
						<?php
					}
				?>
			</ul>
			<div style="margin-left:-12px; margin-right:-12px; margin-bottom: -12px; background: #2a9bd9; padding:14px 12px">
				<p style="margin:0; text-align:center"><a style="color: #fff;" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=XMLKD8H84HXB4&lc=US&item_name=Donation%20for%20WordPress%20Plugins&no_note=0&cn=Add%20a%20message%3a&no_shipping=1&currency_code=EUR" target="_blank"><?php _e("Please consider making a small donation!", "acf_tooltip") ?></a></p>
			</div>
		<?php
	}
}
// initialize
new dhz_acf_plugin_tooltip();

// class_exists check
endif;
