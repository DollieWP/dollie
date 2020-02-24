<?php

namespace Dollie\Core\Utils;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Tpl
 * @package Dollie\Core\Utils
 */
class Tpl {

	/**
	 * Outputs or returns a template
	 *
	 * @param $path
	 * @param array $args
	 * @param bool $echo
	 *
	 * @return false|string|void
	 */
	public static function load( $path, $args = [], $echo = false ) {
		if ( ! $path ) {
			return;
		}

		extract( $args );

		ob_start();
		include( trim( $path ) . '.php' );

		if ( $echo ) {
			echo ob_get_clean();
		} else {
			return ob_get_clean();
		}
	}
}
