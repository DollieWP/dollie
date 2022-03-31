<?php

namespace Dollie\Core\Jobs;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

/**
 * Class UpdateContainerScreenshotsJob
 *
 * @package Dollie\Core\Modules
 */
class UpdateContainerScreenshotsJob extends Singleton {

	/**
	 * Jobs constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'init', [ $this, 'init_recurring_tasks' ] );
		add_action( 'dollie/jobs/recurring/update_screenshots', [ $this, 'run' ], 10 );
	}

	/**
	 * Init recurring tasks
	 */
	public function init_recurring_tasks() {
		if ( false === as_next_scheduled_action( 'dollie/jobs/recurring/update_screenshots' ) ) {
			as_schedule_recurring_action( strtotime( 'today' ), HOUR_IN_SECONDS * 8, 'dollie/jobs/recurring/update_screenshots' );
		}
	}

	/**
	 * Update screenshots task
	 */
	public function run() {
		$args = [
			'post_type'      => 'container',
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
		];

		$posts = get_posts( $args );

		$containers = [];

		if ( $posts ) {
			foreach ( $posts as $post ) {
				$containers[] = dollie()->get_container_url( $post->ID );
			}
		}

		wp_reset_postdata();

		dollie()->regenerate_containers_screenshot( $containers );

		return true;
	}

}
