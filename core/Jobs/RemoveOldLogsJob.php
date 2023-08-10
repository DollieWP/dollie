<?php

namespace Dollie\Core\Jobs;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

/**
 * Class RemoveOldLogsJob
 *
 * @package Dollie\Core\Modules
 */
class RemoveOldLogsJob extends Singleton {

	/**
	 * Jobs constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'init', [ $this, 'init_recurring_tasks' ] );
		add_action( 'dollie/jobs/recurring/remove_old_logs', [ $this, 'run' ], 10 );
	}

	/**
	 * Init recurring tasks
	 */
	public function init_recurring_tasks() {
		if ( false === as_next_scheduled_action( 'dollie/jobs/recurring/remove_old_logs' ) ) {
			as_schedule_recurring_action( strtotime( 'tomorrow' ), DAY_IN_SECONDS, 'dollie/jobs/recurring/remove_old_logs' );
		}
	}

	/**
	 * Run task
	 */
	public function run() {
		$args = [
			'post_type'      => 'dollie-logs',
			'post_status'    => [ 'publish', 'draft', 'trash' ],
			'posts_per_page' => - 1,
			'date_query'     => [
				'before' => date( 'Y-m-d', strtotime( '-30 days' ) ),
			],
		];

		$posts = get_posts( $args );

		if ( $posts ) {
			foreach ( $posts as $post ) {
				wp_delete_post( $post->ID, true );
			}
		}

		wp_reset_postdata();

		return true;
	}

}
