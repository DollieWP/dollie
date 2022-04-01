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
	 *
	 * @return \WP_Error|boolean
	 */
	public function start( string $type, string $route ): \WP_Error|bool {
		if ( ! in_array( $type, [ self::TYPE_SITE, self::TYPE_BLUEPRINT, self::TYPE_STAGING ] ) ) {
			throw new \WP_Error( 500, 'Invalid deploy type' );
		}

		$extra_vars = apply_filters( 'dollie/deploy/vars', [], $type );
		$vars       = array_merge(
			$extra_vars,
			[
				'S5_DEPLOYMENT_URL' => get_site_url(),
			]
		);

		$deploy = $this->start_deploy(
			$type,
			[
				'route' => $route,
				'vars'  => $vars,
			]
		);

		if ( false === $deploy ) {
			return false;
		}

		$post = wp_insert_post(
			[
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_author'    => get_current_user_id(),
				'post_name'      => $route,
				'post_title'     => "{$route} [Deploying]",
				'post_status'    => 'publish',
				'post_type'      => 'container',
			]
		);

		$class     = '\Dollie\Core\Factories\\' . ucfirst( $type );
		$container = new $class( $post );

		$meta = [
			'url'    => $deploy['route'],
			'status' => $deploy['status'],
			'type'   => $type,
		];

		$container->update_meta( $meta )->set_cookie();

		return true;
	}

	/**
	 * Check if container is deployed
	 *
	 * @param BaseContainer $container
	 *
	 * @return boolean
	 */
	public function check_deploy( BaseContainer $container ): \WP_Error|bool {
		if ( ! $container->is_deploying() ) {
			return new \WP_Error( 500, 'Container is not deploying' );
		}

		$deploy = $this->get_deploy( $container->get_type(), $container->get_original_url() );

		if ( false === $deploy ) {
			return new \WP_Error( 500, 'Cannot fetch deploy data' );
		}

		$post_data = [
			'post_title'  => "{$container->get_name()} [Failed]",
			'post_status' => 'draft',
		];

		$container->update_post( $post_data )->update_meta( [ 'status' => $deploy['status'] ] );

		return true;
	}
}
