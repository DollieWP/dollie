<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

/**
 * Class Hooks
 *
 * @package Dollie\Core\Modules
 */
class Hooks extends Singleton {
	/**
	 * Hooks constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'document_title_parts', [ $this, 'update_page_title' ], 10, 1 );
		add_action( 'admin_init', [ $this, 'last_admin_activity' ], 10 );
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
