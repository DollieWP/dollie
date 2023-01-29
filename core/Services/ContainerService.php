<?php

namespace Dollie\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Tpl;

final class ContainerService extends Singleton {
	/**
	 * Add subpages rules
	 */
	public function rewrite_rules_sub_pages() {
		$post_type = 'site';
		$sub_pages = '(dashboard|plugins|themes|emails|domains|backups|updates|developer-tools|blueprints|delete|migrate|staging|admin-settings|stats|error)';

		add_rewrite_rule(
			$post_type . '\/([^\/]+)(?:\/' . $sub_pages . ')\/?$',
			'index.php?' . $post_type . '=$matches[1]&post_type=' . $post_type . '&sub_page=$matches[2]',
			'top'
		);
	}

	/**
	 * Add custom query var for contaienr post type
	 *
	 * @param $vars
	 *
	 * @return array
	 */
	public function query_vars( $vars ) {
		$vars[] = 'sub_page';

		return $vars;
	}

	/**
	 * Init
	 *
	 * @return void
	 */
	public function add_acf_form_head() {
		if ( get_query_var( 'sub_page' ) === 'blueprints' ) {
			acf_form_head();
		}
	}

	/**
	 * Custom template for container
	 *
	 * @param $single
	 *
	 * @return string
	 */
	public function container_template( $single ) {
		global $post;

		if ( 'container' !== $post->post_type ) {
			return $single;
		}

		return Tpl::get_path( 'container' );
	}

	/**
	 * Fetch fresh container data
	 */
	public function fetch_container() {
		if ( ! isset( $_GET['get-details'] ) ) {
			return;
		}

		$container = dollie()->get_container();

		if ( is_wp_error( $container ) ) {
			return;
		}

		$container->mark_not_updated();
		$container->fetch_details();
	}

	/**
	 * Check container deploy status
	 *
	 * @return void
	 */
	public function check_deploy() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'check_deploy_nonce' ) ) {
			wp_send_json_error();
			die();
		}

		$container_id = (int) $_REQUEST['container'];

		$status = DeployService::instance()->check_deploy( $container_id );

		if ( ! $status ) {
			wp_send_json_error();
			die();
		}

		$data            = [];
		$launch_redirect = get_post_meta( $container_id, 'dollie_launch_redirect', true );

		if ( ! empty( $launch_redirect ) ) {
			if ( strpos( $launch_redirect, 'http' ) === 0 ) {
				$data['redirect'] = get_post_meta( $container_id, '', true );
			} else {
				$data['redirect'] = trailingslashit( get_permalink( $container_id ) ) . $launch_redirect;
			}
		}

		wp_send_json_success( $data );
		die();
	}

	/**
	 * Update container assets
	 *
	 * @return void
	 */
	public function update_assets() {
		$data = $_POST;

		if ( ! isset( $data['container'] ) || $data['action'] !== 'dollie_update_assets' || ! wp_verify_nonce( $data['nonce'], 'dollie_update_assets' ) ) {
			wp_send_json_error();
			die();
		}

		$container = dollie()->get_container( $data['container'] );

		if ( is_wp_error( $container ) ) {
			wp_send_json_error();
			die();
		}

		if ( isset( $data['themes'] ) && is_array( $data['themes'] ) ) {
			$container->update_themes( $data['themes'] );
		}

		if ( isset( $data['plugins'] ) && is_array( $data['plugins'] ) ) {
			$container->update_plugins( $data['plugins'] );
		}

		wp_send_json_success( [ 'message' => esc_html__( 'Your site\'s plugins and themes are updating.' ) ] );
		die();
	}

	/**
	 * Remove pending string from front-end
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	public function remove_pending_from_title( $title, $post_id ) {
		if ( ! is_admin() && is_main_query() && get_post_type( $post_id ) === 'container' ) {
			$title = str_replace( ' [ 🚀 Launching ]', '', $title );
		}

		return $title;
	}

	/**
	 * Restore container
	 *
	 * @return void
	 */
	public function restore() {
		if ( ! isset( $_GET['restore_container'] ) ) {
			return;
		}

		$container = dollie()->get_container();

		if ( is_wp_error( $container ) || ! $container->is_scheduled_for_deletion() ) {
			return;
		}

		$container->restore();

		wp_redirect( $container->get_permalink() );
		die();
	}

	/**
	 * Start container
	 *
	 * @return void
	 */
	public function start() {
		$data = $_POST;
		$user = dollie()->get_user();

		if ( ! $user->can_manage_all_sites() || ! isset( $data['container'] ) || $data['action'] !== 'dollie_action_start_container' || ! wp_verify_nonce( $data['nonce'], 'dollie_action_start_container' ) ) {
			return;
		}

		$container = dollie()->get_container( $data['container'] );

		if ( is_wp_error( $container ) ) {
			return;
		}

		$container->perform_action( 'start' );
		$container->set_details(
			[
				'status' => 'Running',
			]
		);

		wp_redirect( $container->get_permalink( 'admin-settings' ) );
		die();
	}

	/**
	 * Stop container
	 *
	 * @return void
	 */
	public function stop() {
		$data = $_POST;
		$user = dollie()->get_user();

		if ( ! $user->can_manage_all_sites() || ! isset( $data['container'] ) || $data['action'] !== 'dollie_action_stop_container' || ! wp_verify_nonce( $data['nonce'], 'dollie_action_stop_container' ) ) {
			return;
		}

		$container = dollie()->get_container( $data['container'] );

		if ( is_wp_error( $container ) ) {
			return;
		}

		$container->perform_action( 'stop' );
		$container->set_details(
			[
				'status' => 'Stopped',
			]
		);

		wp_redirect( $container->get_permalink( 'admin-settings' ) );
		die();
	}

	/**
	 * Set container owner
	 *
	 * @return void
	 */
	public function update_owner() {
		$data = $_POST;
		$user = dollie()->get_user();

		if ( ! $user->can_manage_all_sites() || ! isset( $data['container'] ) || $data['action'] !== 'dollie_set_container_owner' || ! wp_verify_nonce( $data['nonce'], 'dollie_set_container_owner' ) ) {
			return;
		}

		$container = dollie()->get_container( $data['container'] );

		if ( is_wp_error( $container ) ) {
			return;
		}

		$user = get_user_by( 'ID', $data['owner'] );

		if ( ! $user ) {
			wp_redirect( $container->get_permalink( 'admin-settings' ) );
			die();
		}

		wp_update_post(
			[
				'ID'          => $container->get_id(),
				'post_author' => $user->ID,
			]
		);

		wp_redirect( $container->get_permalink( 'admin-settings' ) );
		die();
	}
}
