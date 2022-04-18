<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Api\DeployApi;
use Dollie\Core\Factories\BaseContainer;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\ConstInterface;

final class DeployService extends Singleton implements ConstInterface {
	use DeployApi;

	/**
	 * Start deploy
	 *
	 * @param string $type
	 * @param string $route
	 * @param array $data
	 *
	 * @return \WP_Error|boolean
	 */
	public function start( string $type, string $route, array $data ) {
		if ( ! in_array( $type, [ self::TYPE_SITE, self::TYPE_BLUEPRINT, self::TYPE_STAGING ] ) ) {
			return new \WP_Error( 500, 'Invalid deploy type' );
		}

		$extra_vars = apply_filters( 'dollie/deploy/vars', [], $type );
		$vars       = array_merge(
			$extra_vars,
			[
				'S5_DEPLOYMENT_URL' => get_site_url(),
				'owner_email'       => $data['email'],
				'origin'            => get_site_url(),
				'wp_setup'          => [
					'email'       => $data['email'],
					'username'    => $data['username'],
					'password'    => $data['password'],
					'name'        => $data['name'],
					'description' => $data['description'],
				],
			]
		);

		$deploy_type = 'sites';

		if ( $type === self::TYPE_BLUEPRINT ) {
			$deploy_type = 'blueprints';
		} elseif ( $type === self::TYPE_STAGING ) {
			$deploy_type = 'stagings';
		}

		$deploy = $this->start_deploy(
			$deploy_type,
			[
				'route' => $route,
				'vars'  => $vars,
			]
		);

		if ( is_wp_error( $deploy ) ) {
			return false;
		}

		$post_id = wp_insert_post(
			[
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_author'    => $data['owner_id'],
				'post_name'      => $deploy['route'],
				'post_title'     => "{$deploy['route']} [Deploying]",
				'post_status'    => 'publish',
				'post_type'      => 'container',
				'meta_input'     => [
					'dollie_container_type' => $deploy['type'],
				],
			]
		);

		$container = dollie()->get_container( $post_id );

		if ( is_wp_error( $container ) ) {
			return false;
		}

		$container->set_details(
			[
				'url'    => $deploy['route'],
				'status' => $deploy['status'],
				'type'   => $deploy['type'],
			]
		);

		return true;
	}

	/**
	 * Check if container is deployed
	 *
	 * @return boolean|\WP_Error
	 */
	public function check_deploy() {
		$container = dollie()->get_container();

		if ( is_wp_error( $container ) ) {
			return $container;
		}

		if ( ! $container->is_deploying() ) {
			return new \WP_Error( 500, 'Container is not deploying' );
		}

		$deploy_type = 'sites';

		if ( $container->get_type() === self::TYPE_BLUEPRINT ) {
			$deploy_type = 'blueprints';
		} elseif ( $container->get_type() === self::TYPE_STAGING ) {
			$deploy_type = 'stagings';
		}

		$deploy = $this->get_deploy( $deploy_type, $container->get_original_url() );

		if ( is_wp_error( $deploy ) ) {
			return new \WP_Error( 500, 'Cannot fetch deploy data' );
		}

		if ( 'Deploying' === $deploy['status'] ) {
			return false;
		}

		if ( 'Failed' === $deploy['status'] || 'Deploy Failure' === $deploy['status'] ) {
			$post_data = [
				'post_title'  => "{$container->get_original_url()} [Failed]",
				'post_status' => 'draft',
			];

			$container->update_post( $post_data )->set_details( [ 'status' => $deploy['status'] ] );

			return false;
		}

		$container->set_details( [ 'hash' => $deploy['hash'] ] )->fetch_details();

		return true;
	}
}
