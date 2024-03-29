<?php

namespace Dollie\Core\Factories;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WP_Post;
use Dollie\Core\Api\StagingApi;

final class Staging extends BaseContainer {
	use StagingApi;

	/**
	 * Staging constructor
	 *
	 * @param WP_Post $post
	 */
	public function __construct( WP_Post $post ) {
		parent::__construct( $post );
	}

	/**
	 * Refresh staging details
	 *
	 * @return self
	 */
	public function fetch_details(): self {
		if ( ! $this->needs_updated() ) {
			return $this;
		}

		$data = $this->get_staging_by_id( $this->get_hash() );

		if ( is_array( $data ) && isset( $data[0] ) ) {
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
		$location = $location ? "&location={$location}" : '';
		$username = $this->get_details( 'site.admin.username' );

		if ( is_wp_error( $username ) ) {
			return '';
		}

		$login_data = $this->get_staging_login_url( $this->get_hash(), $username );

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
	 * @return boolean|array
	 */
	public function perform_action( string $action ) {
		return $this->perform_staging_action( $this->get_hash(), $action );
	}

	/**
	 * Sync
	 *
	 * @return boolean|array
	 */
	public function sync() {
		return $this->sync_staging( $this->get_hash() );
	}

	/**
	 * Delete
	 *
	 * @return boolean
	 */
	public function delete(): bool {
		$status = $this->delete_staging( $this->get_hash() );

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
		$status = $this->restore_staging( $this->get_hash() );

		if ( is_wp_error( $status ) ) {
			return false;
		}

		$this->set_details( $status );

		parent::restore();

		return true;
	}
}
