<?php
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 */

// If this file is called directly, abort.
if(!defined('ABSPATH')) {
	exit;
}

class LivePreviewPro_Deactivator {
	public function deactivate() {
		delete_option(LIVEPREVIEWPRO_PLUGIN_NAME . '_activated');
	}
}