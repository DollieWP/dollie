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
	 * @param string $path
	 * @param array  $args
	 * @param bool   $echo
	 *
	 * @return false|string|void
	 */
	public static function load( string $path = null, array $args = [], $echo = false ) {
		$template = self::get_path( $path );

		if ( empty( $template ) ) {
			return;
		}

		extract( $args );

		ob_start();

		include $template;

		if ( $echo ) {
			echo ob_get_clean();
		} else {
			return ob_get_clean();
		}
	}

	/**
	 * Get full template path
	 *
	 * @param string $path
	 *
	 * @return false|string
	 */
	public static function get_path( string $path ) {
		if ( ! isset( $path ) ) {
			return false;
		}

		$path = trim( $path );

		$template = locate_template( 'dollie/' . $path . '.php' );

		if ( ! $template ) {
			if ( file_exists( DOLLIE_MODULE_TPL_PATH . $path . '.php' ) ) {
				return DOLLIE_MODULE_TPL_PATH . $path . '.php';
			}

			return false;
		}

		return $template;
	}
}
