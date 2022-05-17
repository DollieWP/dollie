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
		$sub_pages = '(dashboard|plugins|themes|emails|domains|backups|updates|developer-tools|blueprints|delete|migrate|staging|error)';

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
		}
		var_dump( $_REQUEST['container'] );
		die();
		$status = DeployService::instance()->check_deploy( (int) $_REQUEST['container'] );

		if ( ! $status ) {
			wp_send_json_error();
			die();
		}

		wp_send_json_success();
		die();
	}

	/**
	 * Remove pending string from front-end
	 *
	 * @param string $title
	 * @return string
	 */
	public function remove_pending_from_title( $title, $post_id ) {
		if ( ! is_admin() && is_main_query() && get_post_type( $post_id ) === 'container' ) {
			$title = str_replace( ' [deploy pending]', '', $title );
		}
		return $title;
	}

}
