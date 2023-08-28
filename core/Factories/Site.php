<?php

namespace Dollie\Core\Factories;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WP_Post;
use Dollie\Core\Api\SiteApi;
use Dollie\Core\Api\RouteApi;
use Dollie\Core\Api\ZoneApi;

final class Site extends BaseContainer {
	use SiteApi;
	use RouteApi;
	use ZoneApi;

	/**
	 * Site constructor
	 *
	 * @param WP_Post $post
	 */
	public function __construct( WP_Post $post ) {
		parent::__construct( $post );
	}

	/**
	 * Refresh site details
	 *
	 * @return self
	 */
	public function fetch_details(): self {
		if ( ! $this->needs_updated() || $this->is_deploying() ) {
			return $this;
		}

		$data = $this->get_site_by_id( $this->get_hash() );

		if ( ! is_wp_error( $data ) && isset( $data[0] ) ) {
			$this->set_details( $data[0] );
			$this->mark_updated();
		}

		return $this;
	}

	/**
	 * Get login URL
	 *
	 * @param string $location
	 *
	 * @return string
	 */
	public function get_login_url( string $location = '' ): string {

		if ( $this->get_user_role() !== 'administrator' ) {

			$username = $this->get_username_by_role( $this->get_user_role() );

		} else {
			$username = $this->get_details( 'site.admin.username' );
			if ( is_wp_error( $username ) ) {
				return '';
			}

			$user = $this->user();

			// If we need to get an editor login url.
			if ( 'administrator' !== $user->get_container_user_role() && ! $user->can_manage_all_sites() ) {
				$username = $this->get_details( 'site.editor.username' );

				if ( empty( $username ) || is_wp_error( $username ) ) {
					$temp_data = get_transient( "wpd_site_deploy_data_{$this->get_id()}" );
					if ( ! empty( $temp_data ) && isset( $temp_data['editor'], $temp_data['editor']['username'] ) ) {
						$username = $temp_data['editor']['username'];
					}
				}
			}
		}

		$location = $location ? "&location={$location}" : '';
		
		if ( empty( $username ) ) {
			return '';
		}

		$login_data = get_transient( "dollie_login_data_{$this->get_id()}" );

		if ( ! $login_data ) {
			$login_data = $this->get_site_login_url( $this->get_hash(), $username );

			if ( ! is_wp_error( $login_data ) ) {
				set_transient( "dollie_login_data_{$this->get_id()}", $login_data, 2 );
			}
		}

		if ( is_wp_error( $login_data ) || ! isset( $login_data['token'] ) || ! $login_data['token'] ) {
			return '';
		}

		return "https://{$this->get_url()}/wp-login.php?s5token={$login_data['token']}{$location}";
	}

	/**
	 * Perform action
	 *
	 * @param string $action
	 *
	 * @return \WP_Error|array
	 */
	public function perform_action( string $action ) {
		return $this->perform_site_action( $this->get_hash(), $action );
	}

	/**
	 * Change role
	 *
	 * @param array $data
	 *
	 * @return \WP_Error|array
	 */
	public function set_role( array $data ) {
		return $this->set_user_role( $this->get_hash(), $data );
	}

	/**
	 * Delete
	 *
	 * @return boolean
	 */
	public function delete(): bool {
		$status = $this->delete_site( $this->get_hash() );

		if ( is_wp_error( $status ) ) {
			return false;
		}

		$this->set_details( $status );

		parent::delete();

		return true;
	}

	/**
	 * Restore
	 *
	 * @return bool
	 */
	public function restore(): bool {
		$status = $this->restore_site( $this->get_hash() );

		if ( is_wp_error( $status ) ) {
			return false;
		}

		$this->set_details( $status );

		parent::restore();

		return true;
	}

	/**
	 * Get routes
	 *
	 * @return \WP_Error|array
	 */
	public function get_routes() {
		return $this->get_container_routes( $this->get_hash() );
	}

	/**
	 * Create route
	 *
	 * @param string $route
	 *
	 * @return \WP_Error|array
	 */
	public function create_route( string $route ) {
		return $this->create_container_route( $this->get_hash(), [ 'route' => $route ] );
	}

	/**
	 * Delete routes
	 *
	 * @return \WP_Error|array
	 */
	public function delete_routes() {
		return $this->delete_container_routes( $this->get_hash() );
	}

	/**
	 * Get zone by ID
	 *
	 * @param string $zone_id
	 *
	 * @return \WP_Error|array
	 */
	public function get_zone_by_id( string $zone_id ) {
		return $this->get_container_zone_by_id( $this->get_hash(), $zone_id );
	}

	/**
	 * Get zones
	 *
	 * @return \WP_Error|array
	 */
	public function get_zones() {
		return $this->get_container_zones( $this->get_hash() );
	}

	/**
	 * Create zone
	 *
	 * @param string $domain
	 *
	 * @return \WP_Error|array
	 */
	public function create_zone( string $domain ) {
		return $this->create_container_zone( $this->get_hash(), [ 'name' => $domain ] );
	}

	/**
	 * Delete zone
	 *
	 * @return \WP_Error|array
	 */
	public function delete_zone() {
		return $this->delete_container_zone( $this->get_hash() );
	}

	/**
	 * Get records by ID
	 *
	 * @param string $record_id
	 *
	 * @return \WP_Error|array
	 */
	public function get_record_by_id( string $record_id ) {
		return $this->get_container_record_by_id( $this->get_hash(), $record_id );
	}

	/**
	 * Get records
	 *
	 * @return \WP_Error|array
	 */
	public function get_records() {
		return $this->get_container_records( $this->get_hash() );
	}

	/**
	 * Create records
	 *
	 * @param array $data
	 *
	 * @return \WP_Error|array
	 */
	public function create_record( array $data ) {
		return $this->create_container_record( $this->get_hash(), $data );
	}

	/**
	 * Delete record
	 *
	 * @param string $record_id
	 *
	 * @return \WP_Error|array
	 */
	public function delete_record( string $record_id ) {
		return $this->delete_container_record( $this->get_hash(), $record_id );
	}
}
