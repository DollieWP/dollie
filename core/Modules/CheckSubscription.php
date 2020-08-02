<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Log;

use Dollie\Core\Utils\Helpers;
use WC_Email;
use WP_Query;
use WP_User_Query;

/**
 * Class CheckSubscription
 * @package Dollie\Core\Modules
 */
class CheckSubscription extends Singleton {

	/**
	 * CheckSubscription constructor.
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
	 * Check if a user has bought any product
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public function has_bought_product( $user_id = 0 ) {
		global $wpdb;
		$customer_id         = ! $user_id ? get_current_user_id() : $user_id;
		$paid_order_statuses = array_map( 'esc_sql', wc_get_is_paid_statuses() );

		$results = $wpdb->get_col( "SELECT p.ID FROM {$wpdb->prefix}posts AS p
        INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
        WHERE p.post_status IN ( 'wc-" . implode( "','wc-", $paid_order_statuses ) . "' )
        AND p.post_type LIKE 'shop_order'
        AND pm.meta_key = '_customer_user'
        AND pm.meta_value = $customer_id" );

		// Count number of orders and return a boolean value depending if higher than 0
		return count( $results ) > 0;
	}

	/**
	 * Get checkout link
	 *
	 * @param $product_id
	 * @param $blueprint_id
	 *
	 * @return mixed|string|void
	 * @throws \Exception
	 */
	public function get_checkout_link( $product_id, $blueprint_id ) {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return '#';
		}

		$product_obj = wc_get_product( $product_id );

		$link_args = [
			'add-to-cart'  => $product_id,
			'blueprint_id' => $blueprint_id,
		];

		if ( method_exists( $product_obj, 'get_type' ) && $product_obj->get_type() === 'variable-subscription' ) {

			$default_atts = $product_obj->get_default_attributes();

			if ( isset( $default_atts['pa_subscription'] ) ) {

				$data_store                = \WC_Data_Store::load( 'product' );
				$default_variation         = $data_store->find_matching_product_variation( $product_obj, [ 'attribute_pa_subscription' => $default_atts['pa_subscription'] ] );
				$link_args['variation_id'] = $default_variation;
			}

		}

		$link = add_query_arg(
			$link_args,
			wc_get_checkout_url() );

		return apply_filters( 'dollie/woo/checkout_link', $link, $product_id, $blueprint_id );
	}

	/**
	 * Get subscriptions for customer
	 *
	 * @param $customer_id
	 * @param string $status
	 * @param int $resources Return max client resource allocation
	 *
	 * @return array|bool
	 */
	public function get_customer_subscriptions( $customer_id, $status = 'any', $resources = 0 ) {
		$resources_allocated = [
			'max_allowed_installs' => 0,
			'max_allowed_size'     => 0,
		];

		$active_plans = [];

		if ( ! function_exists( 'wcs_get_subscriptions' ) ) {
			return false;
		}

		$subscriptions = wcs_get_subscriptions( [
			'customer_id'         => $customer_id,
			'subscription_status' => $status
		] );

		if ( ! $subscriptions ) {
			return false;
		}

		foreach ( $subscriptions as $subscription_id => $subscription ) {

			// Getting the subscription Order ID.
			$the_subscription = wcs_get_subscription( $subscription_id );

			// Get the right number of items, count also any upgraded/downgraded orders
			$order_items = $the_subscription->get_items();

			if ( ! $order_items ) {
				continue;
			}

			// Iterating through each item in the order
			foreach ( $order_items as $item_id => $item_data ) {

				$id = $item_data['product_id'];

				if ( $id === 0 ) {
					continue;
				}

				// Filter out non Dollie subscriptions by checking custom meta field.
				if ( ! get_field( '_wpd_installs', $id ) ) {
					continue;
				}

				$installs = (int) get_field( '_wpd_installs', $id );
				$max_size = get_field( '_wpd_max_size', $id );

				$active_plans['products'][ $id ] = [
					'name'                => $item_data['name'],
					'installs'            => $installs,
					'max_size'            => $max_size,
					'included_blueprints' => get_field( '_wpd_included_blueprints', $id ),
					'excluded_blueprints' => get_field( '_wpd_excluded_blueprints', $id ),
				];

				if ( $resources ) {
					$quantity = $item_data['quantity'] ?: 1;

					// Add up individual plan's max values to obtain total max values of allowed installs and size.
					$resources_allocated['max_allowed_installs'] += $installs * $quantity;
					$resources_allocated['max_allowed_size']     += $max_size * $quantity;
					$resources_allocated['name']                 = $item_data['name'];
				}

			}

		}

		if ( $resources ) {
			return $resources_allocated;
		}

		return $active_plans;
	}

	/**
	 * Check customer's subscriptions
	 */
	public function check_customer_subscriptions() {
		if ( get_option( 'wpd_charge_for_deployments' ) !== '1' ) {
			return;
		}

		// The User Query
		$query = new WP_User_Query( [
			'role__not_in' => 'Administrator',
		] );

		// The User Loop
		if ( ! empty( $query->results ) ) {
			foreach ( $query->results as $customer ) {

				//WooCommerce Checkin
				$has_subscription = $this->get_customer_subscriptions( $customer->ID, 'active', 0 );

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
		// Use wp_next_scheduled to check if the event is already scheduled
		$timestamp = wp_next_scheduled( 'wpd_check_customer_status_cron' );

		// Not scheduled yet? Schedule it
		if ( $timestamp === false ) {
			// Schedule the event for right now, then to repeat daily using the hook 'wi_create_daily_backup'
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

		// Instantiate custom query
		$query = new WP_Query( [
			'author'         => $customer_id,
			'post_type'      => 'container',
			'posts_per_page' => 1000,
		] );

		// Output custom query loop
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$stop_time = get_post_meta( get_the_ID(), 'wpd_stop_container_at', true );

				if ( $type === 'schedule' ) {

					// Set a stop time for the container if customer subscription(s) are cancelled.
					if ( empty( $stop_time ) ) {
						update_post_meta( get_the_ID(), 'wpd_stop_container_at', $trigger_date, true );
					}
					update_post_meta( get_the_ID(), 'wpd_scheduled_for_removal', 'yes' );
					Log::add( get_the_title( get_the_ID() ) . ' has been scheduled for removal' );
				} else {
					// Start the containers that were stopped via S5 API
					ContainerManagement::instance()->container_action( 'start', get_the_ID() );
					Log::add( get_the_title( get_the_ID() ) . ' has been started because the customer has re-activated their subscription' );
					sleep( 3 );
				}
			}
		}

		wp_reset_postdata();
		wp_reset_query();

		//TODO = Also trigger a Slack/Email to notify our team. Just so we don't get surprised about unwanted downtime of containers.
	}

	/**
	 * Stop customer container if he has no active subscription
	 *
	 * @param null $id
	 */
	public function stop_customer_container( $id = null ) {
		if ( $id === null ) {
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

		// Instantiate custom query
		$query = new WP_Query( $query_args );

		// Output custom query loop
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				// Get today's timestamp.
				$today        = mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );
				$trigger_date = get_post_meta( get_the_ID(), 'wpd_stop_container_at', true );

				// If our "stop" time has passed our current time, it's time to flip the switch and stop the container.
				if ( $trigger_date < $today ) {
					ContainerManagement::instance()->container_action( 'stop', get_the_ID() );
					Log::add( get_the_title( get_the_ID() ) . ' has been stopped because the customer has no active subscription' );
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
		// Instantiate custom query
		$query = new WP_Query( [
			'post_type'      => 'container',
			'post_status'    => 'draft',
			'posts_per_page' => - 1,
			'meta_key'       => 'wpd_scheduled_for_undeployment',
			'meta_value'     => 'yes',
		] );

		if ( $query->have_posts() ) {
			// Output custom query loop
			while ( $query->have_posts() ) {
				$query->the_post();
				// Get today's timestamp.
				$today        = mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );
				$trigger_date = get_post_meta( get_the_ID(), 'wpd_undeploy_container_at', true );

				// If our "stop" time has passed our current time, it's time to flip the switch and stop the container.
				if ( $trigger_date < $today ) {
					ContainerManagement::instance()->container_action( 'undeploy', get_the_ID() );
					Log::add( get_the_title( get_the_ID() ) . ' has been undeployed' );
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

		// If $timestamp == false schedule daily backups since it hasn't been done previously
		if ( $timestamp === false ) {
			wp_schedule_event( time(), 'hourly', 'wpd_check_undeployment_cron' );
		}
	}

	/**
	 * Daily stop container cron
	 */
	public function create_daily_container_stop_cron() {
		// Set our daily time cron.
		$timestamp = wp_next_scheduled( 'wpd_check_customer_removal_cron' );

		// If $timestamp == false schedule daily backups since it hasn't been done previously
		if ( $timestamp === false ) {
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

		// If $timestamp == false schedule daily backups since it hasn't been done previously
		if ( $timestamp === false ) {
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
		// Instantiate custom query
		$query = new WP_Query( [
			'post_type'      => 'container',
			'posts_per_page' => 1000,
			'meta_key'       => 'wpd_scheduled_for_removal',
			'meta_value'     => 'yes',
		] );

		ob_start();
		// Output custom query loop
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$slug      = get_post_field( 'post_name', get_the_ID() );
				$undeploy  = get_post_meta( get_the_ID(), 'wpd_stop_container_at', true );
				$url       = get_post_meta( get_the_ID(), 'wpd_container_uri', true );
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
		// Instantiate custom query
		$query = new WP_Query( [
			'post_type'      => 'container',
			'posts_per_page' => 1000,
			'meta_key'       => 'wpd_scheduled_for_undeployment',
			'meta_value'     => 'yes',
		] );

		ob_start();
		// Output custom query loop
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$slug      = get_post_field( 'post_name', get_the_ID() );
				$undeploy  = get_post_meta( get_the_ID(), 'wpd_undeploy_container_at', true );
				$url       = get_post_meta( get_the_ID(), 'wpd_container_uri', true );
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

			// Get woocommerce mailer from instance
			$mailer = WC()->mailer();
			// Wrap message using woocommerce html email template
			$wrapped_message = $mailer->wrap_message( $heading, $message );
			// Create new WC_Email instance
			$wc_email = new WC_Email;
			// Style the wrapped message with woocommerce inline styles
			$html_message = $wc_email->style_inline( $wrapped_message );
			// Send the email using WordPress mail function
			wp_mail( $email, $subject, $html_message, $headers );
		}

		Log::add( 'Daily email update cron completed' );
	}

	/**
	 * Daily mail cron
	 */
	public function create_daily_email_notification() {
		//Set our daily time cron.
		$timestamp = wp_next_scheduled( 'wpd_check_email_cron' );

		//If $timestamp == false schedule daily backups since it hasn't been done previously
		if ( $timestamp === false ) {
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
		$query = new WP_Query( [
			'post_type'      => 'container',
			'posts_per_page' => 9999999,
			'meta_key'       => 'wpd_scheduled_for_undeployment',
			'meta_value'     => 'yes',
		] );

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
		$query = new WP_Query( [
			'post_type'      => 'container',
			'posts_per_page' => 9999999,
			'meta_key'       => 'wpd_scheduled_for_removal',
			'meta_value'     => 'yes',
		] );

		$total = $query->found_posts;

		wp_reset_postdata();

		return $total;
	}

	/**
	 * Check if customer has subscription
	 *
	 * @return bool
	 */
	public function has_subscription() {
		if ( get_option( 'options_wpd_charge_for_deployments' ) !== '1' ) {
			return true;
		}

		return (bool) $this->get_customer_subscriptions( get_current_user_id() );
	}

	/**
	 * Get how many sites are left available for customer
	 *
	 * @return int|mixed
	 */
	public function sites_available() {
		$subscription = $this->get_customer_subscriptions( get_current_user_id(), 'active', 1 );

		if ( ! $subscription ) {
			return 0;
		}

		$total_site = dollie()->count_customer_containers();

		return $subscription['max_allowed_installs'] - $total_site;
	}

	/**
	 * Get storage available for customer
	 *
	 * @return int|mixed
	 */
	public function storage_available() {
		$subscription = $this->get_customer_subscriptions( get_current_user_id(), 'active', 1 );


		if ( ! $subscription ) {
			return 0;
		}

		return $subscription['max_allowed_size'];
	}

	/**
	 * Get subscription name
	 *
	 * @return mixed|string
	 */
	public function subscription_name() {
		$subscription = $this->get_customer_subscriptions( get_current_user_id(), 'active', 1 );

		if ( ! $subscription ) {
			return esc_html__( 'None', 'dollie' );
		}

		return $subscription['name'];
	}

	/**
	 * Check if site limit has been reached
	 *
	 * @return bool
	 */
	public function site_limit_reached() {
		if ( ! class_exists( \WooCommerce::class ) || get_option( 'options_wpd_charge_for_deployments' ) !== '1' ) {
			return false;
		}

		$subscription = $this->get_customer_subscriptions( get_current_user_id(), 'active', 1 );

		if ( ! $subscription ) {
			return false;
		}

		$total_site = (int) dollie()->count_customer_containers();

		return $this->has_subscription() && ( $subscription['max_allowed_installs'] - $total_site ) <= 0 && ! current_user_can( 'manage_options' );
	}

	/**
	 * Get excluded blueprints
	 *
	 * @return bool
	 */
	public function get_excluded_blueprints() {
		$subscription = $this->get_customer_subscriptions( get_current_user_id(), 'active', 0 );

		if ( ! $subscription ) {
			return false;
		}

		$get_first = $subscription['products'];
		$product   = reset( $get_first );

		return $product['excluded_blueprints'];
	}

	/**
	 * Get included blueprints
	 *
	 * @return bool
	 */
	public function get_included_blueprints() {
		$subscription = $this->get_customer_subscriptions( get_current_user_id(), 'active', 0 );

		if ( ! $subscription ) {
			return false;
		}

		$get_first = $subscription['products'];
		$product   = reset( $get_first );

		return $product['included_blueprints'];
	}

	/**
	 * Check if the size limit has been reached
	 *
	 * @return bool
	 */
	public function size_limit_reached() {
		if ( ! class_exists( \WooCommerce::class ) || get_option( 'options_wpd_charge_for_deployments' ) !== '1' ) {
			return false;
		}

		$subscription = $this->get_customer_subscriptions( get_current_user_id(), 'active', 1 );

		if ( ! $subscription ) {
			return false;
		}

		$total_size   = dollie()->get_total_container_size();
		$allowed_size = $subscription['max_allowed_size'] * 1024 * 1024 * 1024;

		return $this->has_subscription() && $total_size >= $allowed_size && ! current_user_can( 'manage_options' );
	}

}
