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
	 * Add logs using WDS_Log_Post
	 *
	 * @param $title
	 * @param string $message
	 * @param string $type
	 * @param null $log_post_id
	 * @param bool $completed
	 */
	public static function add( $title, $message = '', $type = 'general', $log_post_id = null, $completed = false ) {
		if ( class_exists( \WDS_Log_Post::class ) ) {
			\WDS_Log_Post::log_message( 'dollie-logs', $title, $message, $type, $log_post_id, $completed );
		}
	}

}
