<?php

namespace Dollie\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use WP_Query;

use Dollie\Core\Modules\Subscription\Subscription;

use Dollie\Core\Factories\Site;
use Dollie\Core\Factories\Blueprint;
use Dollie\Core\Factories\Staging;
use Dollie\Core\Factories\User;

use Dollie\Core\Services\AccessService;
use Dollie\Core\Services\BulkActionService;
use Dollie\Core\Services\InsightsService;
use Dollie\Core\Services\RecurringActionService;

/**
 * Class Helpers
 *
 * @package Dollie\Core
 */
class Helpers extends Singleton implements ConstInterface {
	/**
	 * Get container
	 *
	 * @param \WP_Post|int $id
	 *
	 * @return Site|Blueprint|Staging|\WP_Error
	 */
	public function get_container( $id = null ) {
		if ( $id instanceof \WP_Post ) {
			$object = $id;
		} elseif ( is_numeric( $id ) ) {
			$object = get_post( $id );
		} else {
			$object = get_queried_object();
		}

		if ( ! $object instanceof \WP_Post ) {
			return new \WP_Error( 500, 'Object is not a post' );
		}

		if ( get_post_type( $object->ID ) !== 'container' ) {
			return new \WP_Error( 500, 'Object is not a container' );
		}

		$container_type = get_post_meta( $object->ID, 'dollie_container_type', true );

		if ( $container_type === self::TYPE_BLUEPRINT ) {
			return new Blueprint( $object );
		}

		if ( $container_type === self::TYPE_STAGING ) {
			return new Staging( $object );
		}

		return new Site( $object );
		//return new \WP_Error( 500, 'Invalid container type' );
	}

	/**
	 * Get user
	 *
	 * @param \WP_User|integer|null $id
	 *
	 * @return User
	 */
	public function get_user( $id = null ): User {
		return new User( $id );
	}

	/**
	 * Icon instance
	 *
	 * @return Icon
	 */
	public function icon(): Icon {
		return Icon::instance();
	}

	/**
	 * PageManager instance
	 *
	 * @return PageManager
	 */
	public function page(): PageManager {
		return PageManager::instance();
	}

	/**
	 * StringVariants instance
	 *
	 * @return StringVariants
	 */
	public function string_variants(): StringVariants {
		return StringVariants::instance();
	}

	/**
	 * Subscription instance
	 *
	 * @return Subscription
	 */
	public function subscription(): Subscription {
		return Subscription::instance();
	}

	/**
	 * BulkActionService instance
	 *
	 * @return BulkActionService
	 */
	public function bulk_actions(): BulkActionService {
		return BulkActionService::instance();
	}

	/**
	 * RecurringActionService instance
	 *
	 * @return RecurringActionService
	 */
	public function recurring_actions(): RecurringActionService {
		return RecurringActionService::instance();
	}

	/**
	 * InsightsService instance
	 *
	 * @return InsightsService
	 */
	public function insights(): InsightsService {
		return InsightsService::instance();
	}

	/**
	 * AccessService instance
	 *
	 * @return AccessService
	 */
	public function access(): AccessService {
		return AccessService::instance();
	}

	/**
	 * Get lastest container URL
	 *
	 * @return string|boolean
	 */
	public function get_latest_container_url() {
		$query = new WP_Query(
			[
				'post_status'    => [ 'publish', 'draft' ],
				'author'         => get_current_user_id(),
				'post_type'      => 'container',
				'posts_per_page' => 1,
			]
		);

		$output = '';

		while ( $query->have_posts() ) {
			$query->the_post();
			$output = get_the_permalink();
		}

		wp_reset_postdata();

		return $output ?: false;
	}

	/**
	 * Check if there are products
	 *
	 * @return boolean
	 */
	public function has_products(): bool {
		$args = [
			'post_type'  => 'product',
			'status'     => 'publish',
			'meta_query' => [
				[
					'key'         => 'wpd_',
					'compare_key' => 'LIKE',
				],
			],
		];

		$args = apply_filters( 'dollie_product_query', $args );

		return ( new \WP_Query( $args ) )->have_posts();
	}

	/**
	 * Get total containers counter
	 *
	 * @return integer
	 */
	public function count_total_containers(): int {
		$query = new WP_Query(
			[
				'post_type'     => 'container',
				'post_per_page' => - 1,
			]
		);

		wp_reset_postdata();

		return $query->found_posts;
	}

	/**
	 * Get total blueprints counter
	 *
	 * @return integer
	 */
	public function count_total_blueprints(): int {
		$query = new WP_Query(
			[
				'post_type'     => 'container',
				'post_per_page' => - 1,
				'meta_query'    => [
					'relation' => 'AND',
					[
						'key'   => 'wpd_blueprint_created',
						'value' => 'yes',
					],
					[
						'key'     => 'wpd_installation_blueprint_title',
						'compare' => 'EXISTS',
					],
				],
			]
		);

		wp_reset_postdata();

		return $query->found_posts;
	}

	/**
	 * @return bool
	 */
	public function is_api_connected() {
		if ( get_option( 'dollie_auth_token' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function is_live() {
		return (bool) get_option( 'options_wpd_api_domain' ) && $this->is_api_connected();
	}

	/**
	 * @return mixed|void
	 */
	public function get_site_template_id() {

		$container = dollie()->get_container( get_the_ID() );


		// If we have a launching template then show that instead.
		if ( $container->is_deploying() && get_option( 'options_wpd_site_launching_template_id' ) ) {
			return (int) get_option( 'options_wpd_site_launching_template_id' );
		}

		return (int) get_option( 'options_wpd_site_template_id' );
	}

	/**
	 * @return mixed
	 */
	public function get_support_link() {
		return get_field( 'wpd_support_link', 'options' );
	}

	/**
	 * Get site preview link.
	 *
	 * @param string $type url|path
	 *
	 * @return mixed|string
	 */
	public function get_preview_url( $type = 'url' ) {
		$preview_path = get_option( 'options_wpd_site_preview_path', 'preview' );

		if ( 'url' === $type ) {
			return home_url( $preview_path );
		}

		return $preview_path;
	}

	/**
	 * Check if is in array recursively
	 *
	 * @param $needle
	 * @param $haystack
	 * @param bool $strict
	 *
	 * @return bool
	 */
	public function in_array_r( $needle, $haystack, $strict = false ) {
		if ( ! is_array( $haystack ) ) {
			return false;
		}

		foreach ( $haystack as $item ) {
			if ( ( $strict ? $item === $needle : $item == $needle ) || ( is_array( $item ) && $this->in_array_r( $needle, $item, $strict ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get Elementor template types
	 *
	 * @return array
	 */
	public function get_elementor_template_types(): array {
		return [
			'container' => __( 'Site View', 'dollie' ),
		];
	}

	/**
	 * Check if it is elementor editor panel
	 *
	 * @return boolean
	 */
	public function is_elementor_editor(): bool {
		return class_exists( '\Elementor\Plugin' ) && ( \Elementor\Plugin::instance()->editor->is_edit_mode()
		                                                || \Elementor\Plugin::instance()->preview->is_preview()
		                                                || isset( $_GET['elementor_library'] ) );
	}

	/**
	 * @param $plugin_path
	 *
	 * @return bool
	 */
	public function is_plugin_installed( $plugin_path ) {
		$plugins = get_plugins();

		return isset( $plugins[ $plugin_path ] );
	}


	/**
	 * @param $plugin_path
	 *
	 * @return bool
	 */
	public function has_layout_widget() {
		$template_id = dollie()->get_site_template_id();
		$meta        = get_post_meta( $template_id, '_elementor_data' );

		foreach ( $meta as $index => $string ) {
			if ( strpos( $string, 'dollie-layout-' ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get current post ID
	 *
	 * @return int
	 */
	public function get_current_post_id() {
		$current_id = get_the_ID();

		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return $current_id;
		}

		if ( $this->is_elementor_editor() ) {
			$args = [
				'post_type' => 'container',

				'posts_per_page' => 1,
			];

			if ( dollie()->get_user()->can_manage_all_sites() ) {
				$args['author'] = get_current_user_id();
			}

			$my_sites = get_posts( $args );

			if ( ! empty( $my_sites ) ) {
				$current_id = $my_sites[0]->ID;
			}
		}

		return $current_id;
	}

	/**
	 * Get containers data
	 *
	 * @param array $data
	 * @param string $with
	 *
	 * @return array
	 */
	public function containers_query( $data, $with = 'post_id' ) {
		$args = [
			'post_type'      => 'container',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		];

		if ( 'post_id' === $with ) {
			$ids = [];

			foreach ( $data as $container ) {
				$ids[] = (int) $container['id'];
			}

			$args['post__in'] = $ids;
		} elseif ( 'container_id' === $with ) {
			$containers_ids = [];

			foreach ( $data as $container ) {
				$containers_ids[] = $container['container_id'];
			}

			$args['meta_query'] = [
				[
					'key'     => 'wpd_container_id',
					'value'   => $containers_ids,
					'compare' => 'IN',
				],
			];
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			$args['author'] = get_current_user_id();
		}

		$posts = new WP_Query( $args );
		$posts = $posts->get_posts();

		wp_reset_postdata();

		return $posts;
	}

	/**
	 * Load template
	 *
	 * @param string $template
	 * @param array $args
	 * @param boolean $echo
	 *
	 * @return void|string
	 */
	public function load_template( string $template, array $args = [], $echo = false ) {
		if ( $echo ) {
			Tpl::load( $template, $args, $echo );
		} else {
			return Tpl::load( $template, $args, $echo );
		}
	}

	/**
	 * @param $size
	 *
	 * @return string
	 */
	public function convert_to_readable_size( $size ) {
		if ( ! $size ) {
			return $size;
		}

		$base   = log( $size ) / log( 1024 );
		$suffix = [ '', 'KB', 'MB', 'GB', 'TB' ];
		$f_base = floor( $base );

		return round( 1024 ** ( $base - floor( $base ) ), 1 ) . $suffix[ $f_base ];
	}
}
