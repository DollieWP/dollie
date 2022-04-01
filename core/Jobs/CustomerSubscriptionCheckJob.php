<?php

namespace Dollie\Core\Jobs;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Log;
use Dollie\Core\Modules\Subscription\Subscription;
use Dollie\Core\Modules\Container;
use WP_Query;

/**
 * Class CustomerSubscriptionCheckJob
 *
 * @package Dollie\Core\Modules
 */
class CustomerSubscriptionCheckJob extends Singleton {

	/**
	 * CustomerSubscriptionCheckJob constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'init', [ $this, 'init_recurring_tasks' ] );

		add_action( 'dollie/jobs/recurring/subscription_check', [ $this, 'run_subscription_check' ], 10 );
		add_action( 'dollie/jobs/recurring/stop_sites', [ $this, 'run_stop_sites' ], 10 );
		add_action( 'dollie/jobs/recurring/undeploy_sites', [ $this, 'run_undeploy_sites' ], 10 );
		add_action( 'dollie/jobs/recurring/email_digest', [ $this, 'run_email_digest' ], 10 );

		add_action( 'trashed_post', [ $this, 'do_not_schedule_post_types' ] );
	}

	/**
	 * Init recurring tasks
	 */
	public function init_recurring_tasks() {
		if ( false === as_next_scheduled_action( 'dollie/jobs/recurring/subscription_check' ) ) {
			as_schedule_recurring_action( strtotime( 'today' ), DAY_IN_SECONDS, 'dollie/jobs/recurring/subscription_check' );
		}

		if ( false === as_next_scheduled_action( 'dollie/jobs/recurring/stop_sites' ) ) {
			as_schedule_recurring_action( strtotime( 'today' ), DAY_IN_SECONDS, 'dollie/jobs/recurring/stop_sites' );
		}

		if ( false === as_next_scheduled_action( 'dollie/jobs/recurring/undeploy_sites' ) ) {
			as_schedule_recurring_action( strtotime( 'today' ), DAY_IN_SECONDS, 'dollie/jobs/recurring/undeploy_sites' );
		}

		if ( false === as_next_scheduled_action( 'dollie/jobs/recurring/email_digest' ) ) {
			as_schedule_recurring_action( strtotime( 'today' ), DAY_IN_SECONDS, 'dollie/jobs/recurring/email_digest' );
		}
	}

	/**
	 * Check customer's subscriptions
	 */
	public function run_subscription_check() {
		if ( get_option( 'wpd_charge_for_deployments' ) !== '1' ) {
			return;
		}

		// The User Query.
		$query = new \WP_User_Query(
			[
				'role__not_in' => 'Administrator',
			]
		);

		// The User Loop.
		if ( ! empty( $query->results ) ) {
			foreach ( $query->results as $customer ) {

				// Subscription Checking.
				$has_subscription = Subscription::instance()->get_customer_subscriptions( 'active', $customer->ID );

				$status = $has_subscription ? 'yes' : 'no';
				$cron   = $has_subscription ? 'unschedule' : 'schedule';

				if ( ! $has_subscription ) {
					Log::add( $customer->ID . ' has no active Dollie subscription.' );
				}

				update_user_meta( $customer->ID, 'wpd_active_subscription', $status );
				$this->add_single_customer_action_cron( $customer->ID, $cron );
			}
		}

		Log::add( esc_html__( 'Customer subscription cron completed', 'dollie' ) );
	}

	/**
	 * Single action cron
	 *
	 * @param $customer_id
	 * @param $type
	 */
	public function add_single_customer_action_cron( $customer_id, $type ) {
		if ( get_option( 'wpd_charge_for_deployments' ) !== '1' ) {
			return;
		}
		// Number of days we want to wait with stopping of container.
		$delay_in_days = 3;

		// Calculate the "stop" date and set it 7 days into the future.
		$trigger_date = mktime( 0, 0, 0, date( 'm' ), date( 'd' ) + $delay_in_days, date( 'Y' ) );

		if ( 'schedule' === $type ) {

			// set "stop" date and save as user meta.
			update_user_meta( $customer_id, 'wpd_stop_container_at', $trigger_date );
		}

		// Instantiate custom query.
		$query = new WP_Query(
			[
				'author'         => $customer_id,
				'post_type'      => 'container',
				'posts_per_page' => - 1,
			]
		);

		// Output custom query loop.
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$stop_time = get_post_meta( get_the_ID(), 'wpd_stop_container_at', true );

				if ( 'schedule' === $type ) {

					// Set a stop time for the container if customer subscription(s) are cancelled.
					if ( empty( $stop_time ) ) {
						update_post_meta( get_the_ID(), 'wpd_stop_container_at', $trigger_date, true );
					}

					update_post_meta( get_the_ID(), 'wpd_scheduled_for_removal', 'yes' );
					Log::add_front( Log::WP_SITE_REMOVAL_SCHEDULED, dollie()->get_current_object( get_the_ID() ), get_the_title( get_the_ID() ) );
				} else {
					// Start the containers that were stopped via S5 API.
					Container::instance()->trigger( 'start', get_the_ID() );
				}
			}
		}

		wp_reset_postdata();
		wp_reset_query();

		// TODO = Also trigger a Slack/Email to notify our team. Just so we don't get surprised about unwanted downtime of containers.
	}

	/**
	 * Stop customer container if he has no active subscription
	 *
	 * @param null $id
	 */
	public function run_stop_sites() {
		$query_args = [
			'post_type'      => 'container',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
			'meta_key'       => 'wpd_scheduled_for_removal',
			'meta_value'     => 'yes',
		];

		// Instantiate custom query.
		$query = new WP_Query( $query_args );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				// Get today's timestamp.
				$today        = mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );
				$trigger_date = get_post_meta( get_the_ID(), 'wpd_stop_container_at', true );

				// If our "stop" time has passed our current time, it's time to flip the switch and stop the container.
				if ( $trigger_date < $today ) {
					wp_trash_post( get_the_ID() );
				}
			}
		}

		wp_reset_postdata();
		wp_reset_query();
	}

	/**
	 * Undeploy customer's container if he has no active subscription
	 */
	public function run_undeploy_sites() {
		$query = new WP_Query(
			[
				'post_type'      => 'container',
				'post_status'    => [ 'draft', 'trash' ],
				'posts_per_page' => - 1,
				'meta_key'       => 'wpd_scheduled_for_undeployment',
				'meta_value'     => 'yes',
			]
		);

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				// Get today's timestamp.
				$today        = mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );
				$trigger_date = get_post_meta( get_the_ID(), 'wpd_undeploy_container_at', true );

				// If our "stop" time has passed our current time, it's time to flip the switch and stop the container.
				if ( $trigger_date < $today ) {

					wp_delete_post( get_the_ID(), true );
				}
			}
		}

		wp_reset_postdata();
		wp_reset_query();
	}

	/**
	 * Send mail
	 */
	public function run_email_digest() {
		if ( ! get_field( 'wpd_email_digest_notification', 'options' ) ) {
			return;
		}

		if ( $this->count_undeployed_containers() === 0 && $this->count_stopped_containers() === 0 ) {
			return;
		}

		$subject = get_field( 'wpd_email_digest_subject', 'options' );
		$subject = str_replace(
			[
				'{dollie_sites_stopped_count}',
				'{dollie_sites_removal_count}',
			],
			[
				$this->count_stopped_containers(),
				$this->count_undeployed_containers(),

			],
			$subject
		);

		$message = get_field( 'wpd_email_digest_body', 'options' );
		$message = str_replace(
			[
				'{dollie_sites_stopped_list}',
				'{dollie_sites_removal_list}',
			],
			[
				$this->get_stopped_container_list(),
				$this->get_undeployed_container_list(),

			],
			$message
		);

		$to      = get_option( 'admin_email' );
		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];

		wp_mail( $to, $subject, $message, $headers );

	}


	/**
	 * Get stopped containers list
	 *
	 * @return false|string
	 */
	public function get_stopped_container_list() {
		$query = new WP_Query(
			[
				'post_type'      => 'container',
				'post_status'    => [ 'draft', 'trash', 'publish' ],
				'posts_per_page' => - 1,
				'meta_key'       => 'wpd_scheduled_for_removal',
				'meta_value'     => 'yes',
			]
		);

		ob_start();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$slug      = get_post_field( 'post_name', get_the_ID() );
				$undeploy  = get_post_meta( get_the_ID(), 'wpd_stop_container_at', true );
				$url       = dollie()->get_wp_site_data( 'uri', get_the_ID() );
				$domain    = get_post_meta( get_the_ID(), 'wpd_domains', true );
				$author_id = get_the_author_meta( 'ID' );
				?>
				<a href="<?php echo $url; ?>"> <?php echo $slug; ?> - <?php echo $domain; ?></a> by customer <a
						href="<?php echo get_edit_user_link( $author_id ); ?>"><?php echo get_the_author(); ?></a> will be stopped at
				<strong><?php echo date( 'F j, Y', $undeploy ); ?></strong> <a
						href="<?php echo get_edit_post_link( get_the_ID() ); ?>">View Container</a>
				<br>
				<?php
			}
		}

		wp_reset_postdata();
		wp_reset_query();

		return ob_get_clean();
	}

	/**
	 * Get undeployed containers list
	 *
	 * @return false|string
	 */
	public function get_undeployed_container_list() {
		// Instantiate custom query.
		$query = new WP_Query(
			[
				'post_type'      => 'container',
				'post_status'    => [ 'draft', 'trash', 'publish' ],
				'posts_per_page' => - 1,
				'meta_key'       => 'wpd_scheduled_for_undeployment',
				'meta_value'     => 'yes',
			]
		);

		ob_start();
		// Output custom query loop.
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$slug      = get_post_field( 'post_name', get_the_ID() );
				$undeploy  = get_post_meta( get_the_ID(), 'wpd_undeploy_container_at', true );
				$url       = dollie()->get_wp_site_data( 'uri', get_the_ID() );
				$domain    = get_post_meta( get_the_ID(), 'wpd_domains', true );
				$author_id = get_the_author_meta( 'ID' );
				?>
				<a href="<?php echo $url; ?>"> <?php echo $slug; ?> - <?php echo $domain; ?></a> by customer <a
						href="<?php echo get_edit_user_link( $author_id ); ?>"><?php echo get_the_author(); ?></a> will be undeployed on
				<strong><?php echo date( 'F j, Y', $undeploy ); ?></strong>
				<br>
				<?php
			}
		}

		wp_reset_postdata();
		wp_reset_query();

		return ob_get_clean();
	}

	/**
	 * Count undeployed containers
	 *
	 * @return int
	 */
	public function count_undeployed_containers() {
		$posts = get_posts(
			[
				'post_type'      => 'container',
				'post_status'    => 'trash',
				'posts_per_page' => - 1,
				'meta_key'       => 'wpd_scheduled_for_undeployment',
				'meta_value'     => 'yes',
			]
		);

		return count( $posts );
	}

	/**
	 * Count stopped containers
	 *
	 * @return int
	 */
	public function count_stopped_containers() {
		$posts = get_posts(
			[
				'post_type'      => 'container',
				'post_status'    => [ 'draft', 'trash', 'publish' ],
				'posts_per_page' => - 1,
				'meta_key'       => 'wpd_scheduled_for_removal',
				'meta_value'     => 'yes',
			]
		);

		return count( $posts );
	}

	/**
	 * Don't schedule other post types except container
	 *
	 * @param $post_id
	 */
	public function do_not_schedule_post_types( $post_id ) {
		$unscheduled_post_types = [ 'container' ];

		if ( in_array( get_post_type( $post_id ), $unscheduled_post_types, true ) ) {
			delete_post_meta( $post_id, '_wp_trash_meta_time' );
		}
	}

}
