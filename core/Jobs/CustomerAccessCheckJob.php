<?php

namespace Dollie\Core\Jobs;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Log;
use Dollie\Core\Modules\Integrations\Integrations;
use WP_Query;

/**
 * Class CustomerAccessCheckJob
 *
 * @package Dollie\Core\Modules
 */
class CustomerAccessCheckJob extends Singleton {

	/**
	 * CustomerAccessCheckJob constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'init', array( $this, 'init_recurring_tasks' ) );

		if ( isset( $_GET['test-subs'] ) == 'yes' ) {
			add_action( 'admin_init', array( $this, 'run_access_check' ) );
		}

		//add_action( 'dollie/jobs/recurring/access_check', array( $this, 'run_access_check' ), 10 );
		//add_action( 'dollie/jobs/recurring/remove_container_posts', array( $this, 'remove_deleted_container_posts' ), 10 );
		//add_action( 'dollie/jobs/recurring/email_digest', array( $this, 'run_email_digest' ), 10 );

		add_action( 'trashed_post', array( $this, 'do_not_schedule_post_types' ) );
	}

	/**
	 * Init recurring tasks
	 */
	public function init_recurring_tasks() {

		if ( false === as_next_scheduled_action( 'dollie/jobs/recurring/access_check' ) ) {
			//as_schedule_recurring_action( strtotime( 'today' ), DAY_IN_SECONDS, 'dollie/jobs/recurring/access_check' );
		}

		if ( false === as_next_scheduled_action( 'dollie/jobs/recurring/remove_container_posts' ) ) {
			// as_schedule_recurring_action( strtotime( 'today' ), DAY_IN_SECONDS, 'dollie/jobs/recurring/schedule_for_deletion' );
		}

		if ( false === as_next_scheduled_action( 'dollie/jobs/recurring/email_digest' ) ) {
			//as_schedule_recurring_action( strtotime( 'today' ), DAY_IN_SECONDS, 'dollie/jobs/recurring/email_digest' );
		}
	}

	/**
	 * Check customer's subscriptions
	 */
	public function run_access_check() {
		// Do not run cron if Hub does not have subscription features enabled.
		if ( get_option( 'options_wpd_charge_for_deployments' ) !== '1' ) {
			return;
		}

		// The User Query.
		$query = new \WP_User_Query(
			array(
				'role__not_in' => 'Administrator',
			)
		);

		// The User Loop.
		if ( ! empty( $query->results ) ) {
			foreach ( $query->results as $customer ) {
				$user = dollie()->get_user( $customer->ID );

				// Check if user has any access settings related to allowed Sites
				$has_access = get_user_meta( $customer->ID, 'dollie_hub_resources_max_allowed_installs', true );

				// If the value exist or is set to 0, we can continue to check the user.
				if ( $has_access || $has_access === '0' ) {
					// Move on to container check if user has access settings
					$user->delete_or_restore_containers( $has_access );
				}
				if ( is_wp_error( $user ) ) {
					continue;
				}
			}
		}

		Log::add( esc_html__( 'Customer Access Check cron completed', 'dollie' ) );
	}

	/**
	 * Delete undeployed containers posts
	 */
	public function remove_deleted_container_posts() {
		$query = new WP_Query(
			array(
				'post_type'      => 'container',
				'post_status'    => array( 'draft', 'trash', 'publish' ),
				'posts_per_page' => - 1,
			)
		);

		$posts = $query->get_posts();

		foreach ( $posts as $post ) {
			$container = dollie()->get_container( $post );

			if ( is_wp_error( $container ) ) {
				continue;
			}

			$deleted_at = $container->get_details( 'deleted_at' );

			if ( is_wp_error( $deleted_at ) || empty( $deleted_at ) ) {
				continue;
			}

			$deleted_at += 5 * 86400;

			if ( $deleted_at < current_time( 'timestamp' ) ) {
				wp_delete_post( get_the_ID(), true );
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
			array(
				'{dollie_sites_stopped_count}',
				'{dollie_sites_removal_count}',
			),
			array(
				$this->count_stopped_containers(),
				$this->count_undeployed_containers(),

			),
			$subject
		);

		$message = get_field( 'wpd_email_digest_body', 'options' );
		$message = str_replace(
			array(
				'{dollie_sites_stopped_list}',
				'{dollie_sites_removal_list}',
			),
			array(
				$this->get_stopped_container_list(),
				$this->get_undeployed_container_list(),

			),
			$message
		);

		$to      = get_option( 'admin_email' );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		wp_mail( $to, $subject, $message, $headers );
	}

	/**
	 * Get stopped containers list
	 *
	 * @return false|string
	 */
	public function get_stopped_container_list() {
		$query = new WP_Query(
			array(
				'post_type'      => 'container',
				'post_status'    => array( 'draft', 'trash', 'publish' ),
				'posts_per_page' => - 1,
				'meta_key'       => 'wpd_scheduled_for_removal',
				'meta_value'     => 'yes',
			)
		);

		$posts = $query->get_posts();

		ob_start();

		foreach ( $posts as $post ) {
			$container = dollie()->get_container( $post );

			if ( is_wp_error( $container ) ) {
				continue;
			}

			$slug      = get_post_field( 'post_name', get_the_ID() );
			$undeploy  = get_post_meta( get_the_ID(), 'wpd_stop_container_at', true );
			$url       = $container->get_url();
			$domain    = $container->get_custom_domain();
			$author_id = get_the_author_meta( 'ID' );
			?>
			<a href="<?php echo $url; ?>"> <?php echo $slug; ?> - <?php echo $domain; ?></a> by customer <a
					href="<?php echo get_edit_user_link( $author_id ); ?>"><?php echo get_the_author(); ?></a> will be stopped at
			<strong><?php echo date( 'F j, Y', $undeploy ); ?></strong> <a
					href="<?php echo get_edit_post_link( get_the_ID() ); ?>">View Container</a>
			<br>
			<?php
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
			array(
				'post_type'      => 'container',
				'post_status'    => array( 'draft', 'trash', 'publish' ),
				'posts_per_page' => - 1,
				'meta_key'       => 'wpd_scheduled_for_undeployment',
				'meta_value'     => 'yes',
			)
		);

		$posts = $query->get_posts();

		ob_start();

		foreach ( $posts as $post ) {
			$container = dollie()->get_container( $post );

			if ( is_wp_error( $container ) ) {
				continue;
			}

			$slug      = get_post_field( 'post_name', get_the_ID() );
			$undeploy  = get_post_meta( get_the_ID(), 'wpd_undeploy_container_at', true );
			$url       = $container->get_url();
			$domain    = $container->get_custom_domain();
			$author_id = get_the_author_meta( 'ID' );
			?>
			<a href="<?php echo $url; ?>"> <?php echo $slug; ?> - <?php echo $domain; ?></a> by customer <a
					href="<?php echo get_edit_user_link( $author_id ); ?>"><?php echo get_the_author(); ?></a> will be undeployed on
			<strong><?php echo date( 'F j, Y', $undeploy ); ?></strong>
			<br>
			<?php
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
			array(
				'post_type'      => 'container',
				'post_status'    => 'trash',
				'posts_per_page' => - 1,
			)
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
			array(
				'post_type'      => 'container',
				'post_status'    => array( 'draft', 'trash', 'publish' ),
				'posts_per_page' => - 1,
			)
		);

		return count( $posts );
	}

	/**
	 * Don't schedule other post types except container
	 *
	 * @param $post_id
	 */
	public function do_not_schedule_post_types( $post_id ) {
		$unscheduled_post_types = array( 'container' );

		if ( in_array( get_post_type( $post_id ), $unscheduled_post_types, true ) ) {
			delete_post_meta( $post_id, '_wp_trash_meta_time' );
		}
	}
}
