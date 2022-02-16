<?php
/**
 * OpenLab: Save upgrade flags into DB for fresh installations.
 *
 * @since 1.2.0
 */

/**
 * Set DB flags for fresh DOLLIE_SETUP-OL installations.
 *
 * Fresh installations do not require upgrades, only required for older
 * DOLLIE_SETUP-OL installs.
 *
 * @since 1.2.0
 */
add_action(
    'activated_plugin',
	function( $plugin ) {
        if ( 'dollie_setup-openlab-core/dollie_setup-openlab-core.php' !== $plugin ) {
            return;
        }

        // If DOLLIE_SETUP-OL is installed already, bail.
        $ver = get_site_option( 'dollie_setupol_ver' );
        if ( ! empty( $ver ) ) {
            return;
        }

        // Include autoloader.
        if ( ! interface_exists( '\DOLLIE_SETUP\OL\ItemType', false ) ) {
            include_once DOLLIE_SETUPOL_PLUGIN_DIR . 'autoload.php';
        }

        require_once DOLLIE_SETUPOL_PLUGIN_DIR . 'includes/upgrades.php';

        $items = DOLLIE_SETUP\Upgrades\Upgrade_Registry::get_instance()->get_all_registered();
        foreach ( $items as $item ) {
            if ( ! get_option( $item::FLAG, false ) ) {
                $item->finish();
            }
        }
    }
);
