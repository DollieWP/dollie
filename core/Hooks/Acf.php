<?php

namespace Dollie\Core\Hooks;

use Dollie\Core\Log;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\ConstInterface;
use Dollie\Core\Services\NoticeService;

/**
 * Class Acf
 *
 * @package Dollie\Core\Hooks
 */
final class Acf extends Singleton implements ConstInterface {
	/**
	 * Acf constructor
	 */
	public function __construct() {
		add_action( 'acf/save_post', [ $this, 'update_customer_role' ] );
		add_action( 'acf/save_post', [ $this, 'update_all_customers_roles' ] );
		add_action( 'acf/save_post', [ $this, 'update_deployment_domain' ] );
		add_action( 'acf/save_post', [ $this, 'update_backup_module' ], 1 );
		add_action( 'acf/save_post', [ $this, 'update_staging_status' ], 1 );
		add_action( 'acf/save_post', [ $this, 'update_create_blueprint' ] );

		add_action( 'acf/input/admin_footer', [ NoticeService::instance(), 'change_user_role' ] );
		add_filter(
			'acf/load_field/name=wpd_api_domain',
			static function( $field ) {
				$field['readonly'] = 1;

				return $field;
			}
		);

		add_filter( 'acf/update_value/name=wpd_container_status', [ $this, 'change_container_status' ], 10, 3 );
	}

	/**
	 * Update user role on container when profile changes
	 *
	 * @param $user_id
	 */
	public function update_customer_role( $user_id ) {
		// Make sure we are editing user.
		if ( strpos( $user_id, 'user_' ) === false ) {
			return;
		}

		$user_id = (int) str_replace( 'user_', '', $user_id );

		if ( ! $user_id || user_can( $user_id, 'administrator' ) ) {
			return;
		}

		$fields = get_fields( 'user_' . $user_id );

		$role = '';

		if ( isset( $fields['wpd_client_site_permissions'] ) ) {
			$role = $fields['wpd_client_site_permissions'];
		}

		if ( 'default' === $role ) {
			$role = get_field( 'wpd_client_site_permission', 'options' );
		}

		$last_role = get_user_meta( $user_id, 'wpd_client_last_changed_role', true );

		if ( $last_role !== $role ) {
			update_user_meta( $user_id, 'wpd_client_last_changed_role', $role );
		}

		if ( ! $role || $last_role === $role ) {
			return;
		}

		$query = new \WP_Query(
			[
				'author'         => $user_id,
				'post_type'      => 'container',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			]
		);

		$user_data = get_userdata( $user_id );

		if ( $query->have_posts() ) {
			$params = [
				'email' => $user_data->user_email,
			];

			foreach ( $query->posts as $post ) {
				$initial_username = $this->get_customer_username( $post->ID );

				$params['container_uri'] = dollie()->get_wp_site_data( 'uri', $post->ID );
				$params['username']      = $initial_username;
				$params['password']      = wp_generate_password();

				$action_id = as_enqueue_async_action(
					'dollie/jobs/single/change_container_customer_role',
					[
						'params'       => $params,
						'container_id' => $post->ID,
						'user_id'      => $user_id,
						'role'         => $role,
					]
				);

				update_post_meta( $post->ID, '_wpd_user_role_change_pending', $action_id );
			}
		}

		wp_reset_postdata();

		Log::add( 'Scheduled job to update client access role for ' . $user_data->display_name );
	}

	/**
	 * Update containers for all customers
	 *
	 * @param $post_id
	 */
	public function update_all_customers_roles( $post_id ) {
		if ( 'options' !== $post_id ) {
			return;
		}

		$role = get_field( 'wpd_client_site_permission', $post_id );

		if ( get_option( 'wpd_client_last_changed_role', '' ) === $role ) {
			return;
		}

		update_option( 'wpd_client_last_changed_role', $role );

		foreach ( get_users() as $user ) {
			if ( $user->has_cap( 'administrator' ) ) {
				continue;
			}

			$this->update_customer_role( 'user_' . $user->ID );
		}

		Log::add( 'Started to update all customers access role' );
	}

	/**
	 * Update deployment domain
	 *
	 * @param $post_id
	 */
	public function update_deployment_domain( $post_id ) {
		if ( 'options' !== $post_id ) {
			return;
		}

		$domain       = get_field( 'wpd_api_domain_custom', $post_id );
		$saved_domain = get_option( 'wpd_deployment_domain' );

		if ( ! empty( $domain ) ) {
			$domain = str_replace( [ 'https://', 'http://' ], '', $domain );
		}

		if ( ! get_field( 'wpd_show_custom_domain_options', $post_id ) && $saved_domain ) {
			$this->remove_deployment_domain();
		} else {
			if ( $saved_domain && ! $domain ) {
				$this->remove_deployment_domain();
			} elseif ( $domain && $domain !== $saved_domain ) {
				// Api::post( Api::ROUTE_DOMAIN_ADD, [ 'name' => $domain ] );

				update_option( 'wpd_deployment_domain', $domain );
				update_option( 'wpd_deployment_domain_status', false );
				update_option( 'deployment_domain_notice', false );
				delete_transient( 'wpd_deployment_domain_delay' );
				delete_option( 'wpd_deployment_delay_status' );
			}
		}
	}

	/**
	 * Update custom backup settings
	 *
	 * @param $post_id
	 */
	public function update_backup_module( $post_id ) {
		if ( 'options' !== $post_id ) {
			return;
		}

		$new_data = [];
		$changed  = false;
		$settings = [
			'status'     => 'wpd_enable_custom_backup',
			'provider'   => 'wpd_backup_provider',
			'access_key' => 'wpd_backup_google_key',
			'secret_key' => 'wpd_backup_google_secret',
			'path'       => 'wpd_backup_google_path',
		];

		// Check if any chiled has changed.
		// foreach ( $settings as $k => $setting ) {
		// $new_data[ $k ] = $_POST['acf'][ acf_get_field( $setting )['key'] ];

		// if ( isset( $new_data[ $k ] ) && get_field( $setting, 'options' ) != $new_data[ $k ] ) {
		// $changed = true;
		// }
		// }

		// if ( $changed && $new_data['status'] ) {
		// Api::post( Api::ROUTE_ADD_CUSTOM_BACKUP, $new_data );
		// } elseif ( ! $new_data['status'] ) {
		// Api::get( Api::ROUTE_DISABLE_CUSTOM_BACKUP );
		// }
	}

	/**
	 * Update staging status
	 *
	 * @param $post_id
	 *
	 * @return void
	 */
	public function update_staging_status( $post_id ) {
		if ( 'options' !== $post_id ) {
			return;
		}

		$changed   = false;
		$new_value = $_POST['acf'][ acf_get_field( 'wpd_enable_staging' )['key'] ];

		if ( get_field( 'wpd_enable_staging', 'options' ) !== $new_value ) {
			$changed = true;
		}

		if ( $changed ) {
			// Api::post(
			// Api::ROUTE_CONTAINER_STAGING_SET_STATUS,
			// [
			// 'status' => $new_value,
			// ]
			// );
		}
	}

	/**
	 * Update or create blueprint
	 *
	 * @param string $post_id
	 *
	 * @return void
	 */
	public function update_create_blueprint( $acf_id ) {
		if ( strpos( $acf_id, 'create_update_blueprint' ) === false ) {
			return;
		}

		$container_id = (int) str_replace( 'create_update_blueprint_', '', $acf_id );

		if ( $container_id <= 0 ) {
			return;
		}

		$container = dollie()->get_container( $container_id );

		if ( is_wp_error( $container ) || ! $container->is_blueprint() ) {
			return;
		}

		$container->update_changes( 'lala', 'test' );

		Log::add_front( Log::WP_SITE_BLUEPRINT_DEPLOYED, $container, $container->slug );
	}

	/**
	 * Remove existing deployment domain
	 *
	 * @return void
	 */
	private function remove_deployment_domain() {
		// $response = Api::post( Api::ROUTE_DOMAIN_REMOVE );

		// if ( false !== $response && ! $response['domain'] && ! $response['status'] ) {
		// update_option( 'wpd_deployment_domain', false );
		// update_option( 'deployment_domain_notice', false );
		// delete_transient( 'wpd_deployment_domain_delay' );
		// delete_option( 'wpd_deployment_delay_status' );
		// }
	}

	/**
	 * Change container status
	 *
	 * @param $value
	 * @param $post_id
	 * @param $field
	 *
	 * @return mixed
	 */
	public function change_container_status( $value, $post_id, $field ) {
		$container = dollie()->get_container( $post_id );

		if ( ! is_wp_error( $container ) && $container->get_status() !== $value ) {
			$container->perform_action( $value );
		}

		return $value;
	}

}
