<?php
/**
 * Fired during plugin activation and loading.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */

// If this file is called directly, abort.
if(!defined('ABSPATH')) {
	exit;
}

class LivePreviewPro_Activator {
	public function activate() {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		update_option(LIVEPREVIEWPRO_PLUGIN_NAME . '_activated', time(), false);
	}
}