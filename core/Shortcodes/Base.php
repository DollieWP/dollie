<?php

namespace Dollie\Core\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Interface Base
 * @package Dollie\Core\Shortcodes
 */
interface Base {

	/**
	 * @return mixed
	 */
	public function register();

	/**
	 * @param $atts
	 *
	 * @return mixed
	 */
	public function shortcode( $atts );

}
