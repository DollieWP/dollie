<?php

namespace Dollie\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Singleton
 * @package Dollie\Core
 */
class Singleton {

	/**
	 * @var
	 */
	protected static $instances;

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

	/**
	 * Get instance
	 *
	 * @return mixed
	 */
	public static function instance() {
		$class = static::class;

		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new $class;
		}

		return self::$instances[ $class ];
	}
}
