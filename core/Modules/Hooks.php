<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

/**
 * Class Hooks
 * @package Dollie\Core\Modules
 */
class Hooks extends Singleton {

	/**
	 * Hooks constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'dollie_before_container', [ $this, 'load_container_headers' ] );
		add_filter( 'document_title_parts', [ $this, 'update_page_title' ], 10, 1 );

		add_action( 'admin_init', [ $this, 'last_admin_activity' ], 10 );

	}

	/**
	 * Load container headers
	 */
	public function load_container_headers() {
		if ( is_singular( 'container' ) ) {
			$post_id        = dollie()->get_current_object()->id;
			$setup_complete = get_post_meta( $post_id, 'wpd_setup_complete', true );
			$status         = get_post_meta( $post_id, 'wpd_container_status', true );

			if ( $status !== 'stop' && $status !== 'failed' ) {

				//Include our API requests so they are available across our templates.
				include get_template_directory() . '/lib/get-container-details.php';

				//Load the correct templates
				if ( isset( $_GET['page'] ) && $_GET['page'] === 'backups' ) {
					if ( ! empty( $request ) ) {
						include_once( locate_template( '/templates/site-manager/backups-header.php' ) );
					}
				} elseif ( isset( $_GET['page'] ) && $_GET['page'] === 'updates' ) {
					if ( ! empty( $request ) ) {
						include_once( locate_template( '/templates/site-manager/updates-header.php' ) );
					}
				} elseif ( isset( $_GET['page'] ) && $_GET['page'] === 'developers' ) {
					if ( ! empty( $request ) ) {
						include_once( locate_template( '/templates/site-manager/developer-header.php' ) );
						if ( $setup_complete === 'yes' ) {
							include_once( locate_template( '/templates/site-manager/developer-navigation.php' ) );
						}
					}
				} elseif ( isset( $_GET['page'] ) && $_GET['page'] === 'blueprint' ) {
					if ( ! empty( $request ) ) {
						include_once( locate_template( '/templates/site-manager/blueprint-header.php' ) );
					}
				} else {
					include_once( locate_template( '/templates/site-manager/site-header.php' ) );
					if ( ! isset( $_GET['page'] ) ) {
						if ( $setup_complete === 'yes' ) {
							include_once( locate_template( '/templates/site-manager/site-navigation.php' ) );
						}
					}
				}
			}
		}
	}

	/**
	 * Update page title
	 *
	 * @param $title
	 *
	 * @return mixed
	 */
	public function update_page_title( $title ) {
		if ( is_singular( 'container' ) ) {
			// change title parts here
			$title['title'] = get_post_meta( get_the_ID(), 'wpd_installation_name', true );
		}

		return $title;
	}

	/**
	 * Save last admin activity when on staging
	 */
	public function last_admin_activity() {
		if ( is_user_logged_in() && ! dollie()->is_live() ) {
			update_option( 'wpd_staging_last_seen', time() );
		}
	}

}
