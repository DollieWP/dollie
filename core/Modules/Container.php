<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\ConstInterface;
use Dollie\Core\Utils\Tpl;
use Dollie\Core\Utils\Notices;

/**
 * Class Container
 *
 * @package Dollie\Core\Modules
 */
class Container extends Singleton implements ConstInterface {
	/**
	 * Container constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp', [ $this, 'add_acf_form_head' ], 9 );
		add_action( 'template_redirect', [ $this, 'fetch_container' ] );
		add_action( 'wp_footer', [ Notices::instance(), 'site_demo_notice' ] );

		add_filter( 'init', [ $this, 'rewrite_rules_sub_pages' ], 20 );
		add_filter( 'query_vars', [ $this, 'query_vars' ] );
		add_filter( 'single_template', [ $this, 'container_template' ] );
		add_filter( 'document_title_parts', [ $this, 'update_page_title' ], 10, 1 );
	}

	/**
	 * Add subpages rules
	 */
	public function rewrite_rules_sub_pages() {
		$post_type = 'site';
		$sub_pages = '(dashboard|plugins|themes|emails|domains|backups|updates|developer-tools|blueprints|delete|migrate|staging)';

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

		$container->get_details();
	}

	/**
	 * Update page title
	 *
	 * @param $title
	 *
	 * @return mixed
	 */
	public function update_page_title( $title ) {
		if ( is_singular( 'container' ) ) {
			$title['title'] = get_post_meta( get_the_ID(), 'wpd_installation_name', true );
		}

		return $title;
	}
}
