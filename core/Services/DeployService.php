<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Api\DeployApi;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\ConstInterface;
use Dollie\Core\Factories\BaseContainer;
use WP_Query;

final class DeployService extends Singleton implements ConstInterface {
	use DeployApi;

	/**
	 * Start deploy
	 *
	 * @param string $type
	 * @param string $route
	 * @param array  $data
	 *
	 * @return \WP_Error|BaseContainer
	 */
	public function start( string $type, string $route, array $data ) {
		if ( ! in_array( $type, [ self::TYPE_SITE, self::TYPE_BLUEPRINT, self::TYPE_STAGING ] ) ) {
			return new \WP_Error( 500, 'Invalid deploy type' );
		}

		if ( ! isset( $data['username'] ) || empty( $data['username'] ) ) {
			$data['username'] = get_user_by( 'ID', $data['owner_id'] )->user_login;
		}

		if ( ! isset( $data['password'] ) || empty( $data['password'] ) ) {
			$data['password'] = wp_generate_password( 8, false );
		}

		if ( ! isset( $data['name'] ) || empty( $data['name'] ) ) {
			$data['name'] = esc_html__( 'My New Site', 'dollie' );
		}

		if ( ! isset( $data['description'] ) || empty( $data['description'] ) ) {
			$data['description'] = esc_html__( 'The best website in the world', 'dollie' );
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

		if ( $type === self::TYPE_BLUEPRINT ) {
			$deploy_type = 'blueprints';
		} elseif ( $type === self::TYPE_STAGING ) {
			$deploy_type = 'stagings';
		} else {
			$deploy_type = 'sites';

			if ( isset( $data['bp_customizer'] ) ) {
				$vars['dynamic'] = $data['bp_customizer'];
			}
		}

		$deploy = $this->start_deploy(
			$deploy_type,
			[
				'route'  => $route,
				'vars'   => $vars,
				'source' => $data['blueprint'],
			]
		);

		if ( is_wp_error( $deploy ) ) {
			return new \WP_Error( 500, 'Deploy failed' );
		}

		$meta_input = [
			'dollie_container_type' => $deploy['type'],
		];

		if ( ! empty( $data['redirect'] ) ) {
			$meta_input['dollie_launch_redirect'] = $data['redirect'];
		}

		$post_id = wp_insert_post(
			[
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_author'    => $data['owner_id'],
				'post_name'      => $route,
				'post_title'     => '🚀 Launching',
				'post_status'    => 'publish',
				'post_type'      => 'container',
				'meta_input'     => $meta_input,
			]
		);

		$container = dollie()->get_container( $post_id );

		// Log::add_front( Log::WP_SITE_DEPLOY_STARTED, ['id' =>$post_id ] );

		if ( is_wp_error( $container ) ) {
			return new \WP_Error( 500, 'Post not created' );
		}

		$container->set_details(
			[
				'url'    => $deploy['route'],
				'status' => $deploy['status'],
				'type'   => $deploy['type'],
			]
		);

		return $container;
	}

	/**
	 * Check if container is deployed
	 *
	 * @param int|bool|\WP_Post $post_id
	 *
	 * @return bool|\WP_Error
	 */
	public function check_deploy( $post = false ) {
		if ( ! $post ) {
			$post = get_the_ID();
		}

		if ( ! $post ) {
			return false;
		}

		$container = dollie()->get_container( $post );

		if ( is_wp_error( $container ) ) {
			return false;
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

		$post_title = explode( '.', $container->get_original_url() );
		$post_title = $post_title[0];

		if ( 'Failed' === $deploy['status'] || 'Deploy Failure' === $deploy['status'] ) {
			$container
				->update_post(
					[
						'post_title' => "{$post_title} [Failed]",
					]
				)
				->set_details(
					[
						'status' => $deploy['status'],
					]
				);
		} elseif ( 'Running' === $deploy['status'] ) {
			$container
				->update_post(
					[
						'post_title' => $post_title,
					]
				)
				->set_details(
					[
						'hash'   => $deploy['hash'],
						'status' => $deploy['status'],
					]
				);

			$container->mark_not_updated();
			$container->fetch_details();
		}

		update_post_meta( $container->get_id(), 'dollie_container_deployed', 1 );

		return true;
	}

	public function check_deploy_bulk() {
		$query = new WP_Query(
			[
				's'              => '🚀 Launching',
				'post_status'    => [ 'publish', 'draft' ],
				'post_type'      => 'container',
				'posts_per_page' => -1,
			]
		);

		$posts = $query->get_posts();

		foreach ( $posts as $post ) {
			$this->check_deploy( $post );
		}
	}
}
