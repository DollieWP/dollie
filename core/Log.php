<?php

namespace Dollie\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Logger
 * @package Dollie\Core
 */
class Log {

	/**
	 * Add Logs using WDS_Log_Post
	 *
	 * @param $title
	 * @param string $message
	 * @param string $type
	 */
	public static function add( $title, $message = '', $type = 'error' ) {
		if ( class_exists( \WDS_Log_Post::class ) ) {
			\WDS_Log_Post::log_message( 'dollie-logs', $title, $message, $type );
		}
	}

}
