<?php

namespace Dollie\Core\Modules\Jobs;

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

		add_action( 'init', [ $this, 'create_daily_customer_status_cron' ] );
		add_action( 'init', [ $this, 'create_daily_customer_removal_cron' ] );
		add_action( 'init', [ $this, 'create_daily_undeployment_cron' ] );
		add_action( 'init', [ $this, 'create_daily_email_notification' ] );

		add_action( 'admin_init', [ $this, 'create_daily_container_stop_cron' ] );

		add_action( 'wpd_check_customer_status_cron', [ $this, 'daily_subscription_check' ] );
		add_action( 'wpd_check_customer_removal_cron', [ $this, 'daily_removal_check' ] );
		add_action( 'wpd_check_undeployment_cron', [ $this, 'daily_undeployment_check' ] );
		add_action( 'wpd_check_email_cron', [ $this, 'send_out_daily_email' ] );

		add_action( 'trashed_post', [ $this, 'do_not_schedule_post_types' ] );
	}

	/**
	 * Check customer's subscriptions
	 */
	public function check_customer_subscriptions() {
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

				// WooCommerce Checking.
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

		Log::add( 'Hourly customer subscription cron completed' );
	}

	/**
	 * Daily cron for customer status
	 */
	public function create_daily_customer_status_cron() {
		// Use wp_next_scheduled to check if the event is already scheduled.
		$timestamp = wp_next_scheduled( 'wpd_check_customer_status_cron' );

		// Not scheduled yet? Schedule it.
		if ( false === $timestamp ) {
			// Schedule the event for right now, then to repeat daily using the hook 'wi_create_daily_backup'.
			wp_schedule_event( time(), 'hourly', 'wpd_check_customer_status_cron' );
		}
	}

	/**
	 * Daily cron check for customer's subscriptions
	 */
	public function daily_subscription_check() {
		$this->check_customer_subscriptions();
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

		// Set "stop" date and save as user meta.
		update_user_meta( $customer_id, 'wpd_stop_container_at', $trigger_date );

		// Instantiate custom query.
		$query = new WP_Query(
			[
				'author'         => $customer_id,
				'post_type'      => 'container',
				'posts_per_page' => -1,
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
					Log::add_front( Log::WP_SITE_STARTED, dollie()->get_current_object( get_the_ID() ), get_the_title( get_the_ID() ) );
					sleep( 3 );
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
	public function stop_customer_container( $id = null ) {
		if ( null === $id ) {
			$query_args = [
				'post_type'      => 'container',
				'posts_per_page' => - 1,
				'post_status'    => 'publish',
				'meta_key'       => 'wpd_scheduled_for_removal',
				'meta_value'     => 'yes',
			];
		} else {
			$query_args = [
				'post_type' => 'container',
				'p'         => $id,
			];
		}

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
					Container::instance()->trigger( 'stop', get_the_ID() );
					Log::add_front( Log::WP_SITE_STOPPED, dollie()->get_current_object( get_the_ID() ), get_the_title( get_the_ID() ) );
					sleep( 3 );
				}
			}
		}

		wp_reset_postdata();
		wp_reset_query();

	}

	/**
	 * Undeploy customer's container if he has no active subscription
	 */
	public function undeploy_customer_container() {
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
					Container::instance()->trigger( 'undeploy', get_the_ID() );
					Log::add_front( Log::WP_SITE_UNDEPLOYED, dollie()->get_current_object( get_the_ID() ), get_the_title( get_the_ID() ) );
					sleep( 3 );
				}
			}
		}

		wp_reset_postdata();
		wp_reset_query();

	}

	/**
	 * Daily remove container cron
	 */
	public function create_daily_customer_removal_cron() {
		// Set our daily time cron.
		$timestamp = wp_next_scheduled( 'wpd_check_undeployment_cron' );

		// If $timestamp == false schedule daily backups since it hasn't been done previously.
		if ( false === $timestamp ) {
			wp_schedule_event( time(), 'hourly', 'wpd_check_undeployment_cron' );
		}
	}

	/**
	 * Daily stop container cron
	 */
	public function create_daily_container_stop_cron() {
		// Set our daily time cron.
		$timestamp = wp_next_scheduled( 'wpd_check_customer_removal_cron' );

		// If $timestamp == false schedule daily backups since it hasn't been done previously.
		if ( false === $timestamp ) {
			wp_schedule_event( time(), 'hourly', 'wpd_check_customer_removal_cron' );
		}
	}

	/**
	 * Daily removal check
	 */
	public function daily_removal_check() {
		$this->stop_customer_container();
	}

	/**
	 * Daily undeployment cron
	 */
	public function create_daily_undeployment_cron() {
		// Set our daily time cron.
		$timestamp = wp_next_scheduled( 'wpd_check_undeployment_cron' );

		// If $timestamp == false schedule daily backups since it hasn't been done previously.
		if ( false === $timestamp ) {
			wp_schedule_event( time(), 'twicedaily', 'wpd_check_undeployment_cron' );
		}
	}

	/**
	 * Daily undeployment check
	 */
	public function daily_undeployment_check() {
		$this->undeploy_customer_container();
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

	/**
	 * Get stopped containers list
	 *
	 * @return false|string
	 */
	public function get_stopped_container_list() {
		$query = new WP_Query(
			[
				'post_type'      => 'container',
				'posts_per_page' => -1,
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
				'posts_per_page' => -1,
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
	 * Send mail
	 */
	public function send_daily_update_email() {
		if ( $this->count_undeployed_containers() !== 0 || $this->count_stopped_containers() !== 0 ) {
			$email   = get_option( 'admin_email' );
			$subject = 'Dollie - ' . $this->count_stopped_containers() . ' will be stopped, ' . $this->count_undeployed_containers() . ' will be completely removed';
			$heading = 'Please review your following sites';
			$message = '<p><h4>The following sites are scheduled to be stopped in the near future:</h4><br><br>' . $this->get_stopped_container_list() . '</p><p>Please make sure that all of the above containers are indeed meant to be stopped due to cancelled subscriptions or manual removal.</p><p><h4>The following containers are scheduled to be completely undeployed in the near future:</h4><br><br>' . $this->get_undeployed_container_list() . '</p><p>Once a site has been completely undeployed, it will removed completely from our infrastructure, and can only be restored in emergency situations.</p>';
			$headers = [ 'Content-Type: text/html; charset=UTF-8' ];

			wp_mail( $email, $subject, $message, $headers );
		}
	}

	/**
	 * Daily mail cron
	 */
	public function create_daily_email_notification() {
		// Set our daily time cron.
		$timestamp = wp_next_scheduled( 'wpd_check_email_cron' );

		// If $timestamp == false schedule daily backups since it hasn't been done previously.
		if ( false === $timestamp ) {
			wp_schedule_event( time(), 'daily', 'wpd_check_email_cron' );
		}
	}

	/**
	 * Send daily mail update
	 */
	public function send_out_daily_email() {
		$this->send_daily_update_email();
	}

	/**
	 * Count undeployed containers
	 *
	 * @return int
	 */
	public function count_undeployed_containers() {
		$query = new WP_Query(
			[
				'post_type'      => 'container',
				'posts_per_page' => -1,
				'meta_key'       => 'wpd_scheduled_for_undeployment',
				'meta_value'     => 'yes',
			]
		);

		$total = $query->found_posts;

		wp_reset_postdata();

		return $total;
	}

	/**
	 * Count stopped containers
	 *
	 * @return int
	 */
	public function count_stopped_containers() {
		$query = new WP_Query(
			[
				'post_type'      => 'container',
				'posts_per_page' => -1,
				'meta_key'       => 'wpd_scheduled_for_removal',
				'meta_value'     => 'yes',
			]
		);

		$total = $query->found_posts;

		wp_reset_postdata();

		return $total;
	}

}
