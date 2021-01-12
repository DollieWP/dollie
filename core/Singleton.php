<?php

namespace Dollie\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Singleton
 *
 * @package Dollie\Core
 */
class Singleton {

	/**
	 * @var array
	 */
	public static $instances = [];

	/**
	 * Singleton constructor.
	 */
	protected function __construct() {
	}

	/**
	 * Don't allow clone
	 */
	final private function __clone() {
	}

	final private function __wakeup() {
	}

	/**
	 * Get instance
	 *
	 * @return mixed
	 */
	public static function instance() {
		$class = static::class;

		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new $class();
		}

		return self::$instances[ $class ];
	}

}
