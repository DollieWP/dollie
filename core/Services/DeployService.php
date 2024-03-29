<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Api\DeployApi;
use Dollie\Core\Log;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\ConstInterface;
use Dollie\Core\Factories\BaseContainer;
use Dollie\Core\Jobs\ChangeContainerRoleJob;
use WP_Query;

final class DeployService extends Singleton implements ConstInterface {
	use DeployApi;

	/**
	 * Start deploy
	 *
	 * @param string $type
	 * @param string $route
	 * @param array $data
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

		// enforce custom user role.

		$deployment_domain = self::TYPE_BLUEPRINT === $type ? '.wp-site.xyz' : WorkspaceService::instance()->get_deployment_domain();

		$extra_vars = apply_filters( 'dollie/deploy/vars', [], $type );
		$vars       = array_merge(
			$extra_vars,
			[
				'S5_DEPLOYMENT_URL' => get_site_url(),
				'owner_email'       => $data['email'],
				'origin'            => get_site_url(),
				'domain'            => $deployment_domain,
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

		// custom role data just for sites.
		if ( $type === self::TYPE_SITE ) {
			$vars['custom_user_role'] = $this->get_custom_role_data( $data );
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

		$meta_input = apply_filters( 'dollie/deploy/meta_input', $meta_input, $data );

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

		if ( is_wp_error( $container ) ) {
			return new \WP_Error( 500, 'Post not created' );
		}

		$user_role = $container->user()->get_container_user_role() === 'administrator' ? 'admin' : 'editor';
		if ( 'editor' === $user_role ) {
			$site_data = [
				'editor' => [
					'username' => $data['username'],
				],
				'admin'  => [
					'username' => get_option( 'options_wpd_admin_user_name', 'sadmin' ),
				],
			];
		} else {
			$site_data = [
				'admin' => [
					'username' => $data['username'],
				],
			];
		}

		//TODO Find a way not to overwrite with empty data from API on site details update
		set_transient( "wpd_site_deploy_data_{$container->get_id()}", $site_data, 60 * 60 );

		$container->set_details(
			[
				'url'    => $deploy['route'],
				'status' => $deploy['status'],
				'type'   => $deploy['type'],
				'site'   => $site_data,
			]
		);

		// Add log.
		if ( $type === self::TYPE_BLUEPRINT ) {
			$container->add_log( Log::WP_BLUEPRINT_DEPLOY_STARTED );
		} elseif ( $type === self::TYPE_STAGING ) {
			$container->add_log( Log::WP_STAGING_DEPLOY_STARTED );
		} else {
			$container->add_log( Log::WP_SITE_DEPLOY_STARTED );
		}

		return $container;
	}

	/**
	 * Check if container is deployed
	 *
	 * @param int|bool|\WP_Post $post
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

		if ( is_wp_error( $deploy ) || empty( $deploy ) ) {
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

			// Add log.
			$container->add_log( Log::WP_SITE_DEPLOY_FAILED );

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

			update_post_meta( $container->get_id(), 'dollie_container_deployed', 1 );

			// Update user role.
			//ChangeContainerRoleJob::instance()->run( $container );

			// Add log.
			if ( $container->is_blueprint() ) {
				$container->add_log( Log::WP_BLUEPRINT_DEPLOYED );
			} elseif ( $container->is_staging() ) {
				$container->add_log( Log::WP_STAGING_DEPLOYED );
			} else {
				$container->add_log( Log::WP_SITE_DEPLOYED );
			}
		}

		return true;
	}

	/**
	 * Get custom role data to send in deploy request.
	 *
	 * @param array $data
	 * @return null|string
	 */
	private function get_custom_role_data( $data ) {

		$role = 'administrator';

		// is based on a blueprint. get bp specific setting from HQ.
		if ( ! empty( $data['blueprint_id'] ) ) {
			$source_bp = dollie()->get_container( $data['blueprint_id'] );
	
			if ( ! empty( $source_bp->get_details( 'blueprintSetting.client_role' ) ) && ! dollie()->get_user()->can_view_all_sites() ) {
				$role = $source_bp->get_details( 'blueprintSetting.client_role' );
			}
		}

		// fallback to hub setting.
		if ( empty( $role ) || $role === 'administrator' ) {
			$role = dollie()->get_user()->get_container_user_role();
		}

		if ( $role === 'administrator' ) {
			return null;
		}

		return [
			'email'          => $data['email'],
			'username'       => $data['username'],
			'password'       => $data['password'],
			'super_email'    => get_option( 'admin_email' ),
			'super_username' => get_option( 'options_wpd_admin_user_name', 'sadmin' ),
			'super_password' => wp_generate_password(),
			'switch_to'      => $role,
		];

	}

	/**
	 * Check deploy in bulk on template_redirect
	 *
	 * @return void
	 */
	public function check_deploy_bulk() {
		$query = new WP_Query(
			[
				's'              => '🚀 Launching',
				'post_status'    => [ 'publish', 'draft' ],
				'post_type'      => 'container',
				'posts_per_page' => - 1,
			]
		);

		$posts = $query->get_posts();

		foreach ( $posts as $post ) {
			$this->check_deploy( $post );
		}
	}
}
