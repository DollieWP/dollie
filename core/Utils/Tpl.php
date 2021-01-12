<?php

namespace Dollie\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Tpl
 *
 * @package Dollie\Core\Utils
 */
class Tpl {

	/**
	 * Outputs or returns a template
	 *
	 * @param $path
	 * @param array $args
	 * @param bool  $echo
	 *
	 * @return false|string|void
	 */
	public static function load( $path = null, $args = [], $echo = false ) {
		if ( ! isset( $path ) ) {
			return;
		}

		$path = trim( $path );

		extract( $args );

		$template = locate_template( 'dollie/' . $path . '.php' );

		if ( ! $template ) {
			if ( file_exists( $path . '.php' ) ) {
				$template = $path . '.php';
			} else {
				return '';
			}
		}

		ob_start();
		include $template;

		if ( $echo ) {
			echo ob_get_clean();
		} else {
			return ob_get_clean();
		}
	}

}
