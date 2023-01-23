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
use Dollie\Core\Services\AuthService;
use Dollie\Core\Services\BulkActionService;
use Dollie\Core\Services\InsightsService;
use Dollie\Core\Services\RecurringActionService;
use Dollie\Core\Services\WorkspaceService;

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

		$container      = null;
		$container_type = get_post_meta( $object->ID, 'dollie_container_type', true );

		switch ( $container_type ) {
			case self::TYPE_SITE:
				$container = new Site( $object );
				break;
			case self::TYPE_BLUEPRINT:
				$container = new Blueprint( $object );
				break;
			case self::TYPE_STAGING:
				$container = new Staging( $object );
				break;
			default:
				return new \WP_Error( 500, 'Invalid container type' );
		}

		if ( is_single( $object ) &&
		     ( $container->is_running() || $container->is_stopped() ) ) {
			$container->fetch_details();
		}

		return $container;
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
	 * WorkspaceService instance
	 *
	 * @return WorkspaceService
	 */
	public function workspace(): WorkspaceService {
		return WorkspaceService::instance();
	}

	/**
	 * AuthService instance
	 *
	 * @return AuthService
	 */
	public function auth(): AuthService {
		return AuthService::instance();
	}

	/**
	 * Get latest container URL
	 *
	 * @return string|boolean
	 */
	public function get_latest_container_url() {
		$args = [
			'post_status'    => [ 'publish', 'draft' ],
			'post_type'      => 'container',
			'posts_per_page' => 1,
		];

		if ( ! dollie()->get_user()->can_manage_all_sites() ) {
			$args['author'] = get_current_user_id();
		}

		$query = new WP_Query( $args );

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
	public function count_total_sites(): int {
		$query = new WP_Query(
			[
				'post_type'     => 'container',
				'post_per_page' => - 1,
				'meta_query'    => [
					'relation' => 'AND',
					[
						'key'   => 'dollie_container_type',
						'value' => '0',
					],
				],
			]
		);

		wp_reset_postdata();

		return $query->found_posts;
	}

	/**
	 * Get total Active blueprints counter
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
						'key'   => 'dollie_container_type',
						'value' => '1',
					],
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
	 * Get total blueprints counter, regardless of status staging/active
	 *
	 * @return integer
	 */
	public function count_total_created_blueprints(): int {
		$query = new WP_Query(
			[
				'post_type'     => 'container',
				'post_per_page' => - 1,
				'meta_query'    => [
					'relation' => 'AND',
					[
						'key'   => 'dollie_container_type',
						'value' => '1',
					],
					[
						'key'   => 'wpd_blueprint_created',
						'value' => 'yes',
					],
				],
			]
		);

		wp_reset_postdata();

		return $query->found_posts;
	}

	/**
	 * @return mixed
	 */
	public function get_partner_status() {
		if ( ! dollie()->subscription()->has_partner_subscription() ) {
			return 'trial';
		}

		if ( ! dollie()->subscription()->has_partner_verified() ) {
			return 'unverified';
		}

		if ( dollie()->workspace()->has_custom_deployment_domain() && ! get_option( 'wpd_deployment_domain_notice' ) ) {
			return 'staging';
		}

		return 'live';
	}

	/**
	 * @return bool
	 */
	public function is_live() {
		return (bool) get_option( 'options_wpd_api_domain' ) && $this->auth()->is_connected();
	}

	/**
	 * Is using a custom deploy pending template.
	 *
	 * @return bool
	 */
	public function has_deploying_template() {
		$container = dollie()->get_container( get_the_ID() );

		if ( is_wp_error( $container ) ) {
			return false;
		}

		return $container->is_deploying() && get_option( 'options_wpd_site_launching_template_id' );
	}

	/**
	 * @return mixed|void
	 */
	public function get_site_template_id() {
		// If we have a launching template then show that instead.
		if ( $this->has_deploying_template() ) {
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
		return class_exists( '\Elementor\Plugin' ) &&
		       ( \Elementor\Plugin::instance()->editor->is_edit_mode() ||
		         \Elementor\Plugin::instance()->preview->is_preview() ||
		         isset( $_GET['elementor_library'] ) );
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

		if ( empty( $template_id ) ) {
			return false;
		}

		if ( class_exists( '\Elementor\Plugin' ) && ! is_bool( \Elementor\Plugin::instance()->documents->get( $template_id ) ) && \Elementor\Plugin::instance()->documents->get( $template_id )->is_built_with_elementor() ) {
			$meta = get_post_meta( $template_id, '_elementor_data' );

			foreach ( $meta as $index => $string ) {
				if ( strpos( $string, 'dollie-layout-' ) !== false ) {
					return true;
				}
			}
		} else {
			$post = get_post( $template_id );
			if ( empty( $post ) ) {
				return false;
			}
			$content = get_post( $template_id )->post_content;

			if ( strpos( $content, 'dollie-layout-' ) !== false ) {
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
	 * Get containers by hashes
	 *
	 * @param array $hashes
	 *
	 * @return array
	 */
	public function get_containers_by_hashes( $hashes = [] ) {
		if ( empty( $hashes ) ) {
			return [];
		}

		$meta_conditions = [ 'relation' => 'OR' ];

		foreach ( $hashes as $hash ) {
			$meta_conditions[] = [
				'key'     => 'dollie_container_details',
				'value'   => $hash,
				'compare' => 'LIKE',
			];
		}

		$args = [
			'post_type'      => 'container',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
			'meta_query'     => $meta_conditions,
		];

		if ( ! current_user_can( 'manage_options' ) ) {
			$args['author'] = get_current_user_id();
		}

		$posts = new WP_Query( $args );
		$posts = $posts->get_posts();

		wp_reset_postdata();

		return $posts;
	}

	/**
	 * Get containers by ids
	 *
	 * @param array $ids
	 *
	 * @return array
	 */
	public function get_containers_by_ids( $ids = [] ) {
		if ( empty( $ids ) ) {
			return [];
		}

		$args = [
			'post_type'      => 'container',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
			'post__in'       => $ids,
		];

		if ( ! current_user_can( 'manage_options' ) ) {
			$args['author'] = get_current_user_id();
		}

		$posts = new \WP_Query( $args );
		$posts = $posts->get_posts();

		wp_reset_postdata();

		return $posts;
	}

	/**
	 * @return int
	 */
	public function count_customer_containers( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$query = new WP_Query(
			[
				'author'        => $user_id,
				'post_type'     => 'container',
				'post_per_page' => 1000,
				'post_status'   => 'publish',
			]
		);

		$total = $query->found_posts;

		wp_reset_postdata();

		return $total;
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

	/**
	 * Register new fields for existing ACF field groups.
	 *
	 * @param $fields
	 * @param $parent
	 */
	public function append_acf_fields( $fields, $parent ) {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		foreach ( $fields as &$field ) {
			$field['parent'] = $parent;
		}
		unset( $field );

		acf_add_local_fields(
			[
				$fields,
			]
		);
	}

	public function add_acf_fields_to_group( $field_group, $fields = [], $group = '', $field_name = '', $type = 'after' ) {

		if ( $field_group['key'] !== $group ) {
			return $field_group;
		}

		foreach ( $field_group['fields'] as $k => $field ) {
			if ( $field['name'] === $field_name ) {
				$offset = $k;
				break;
			}
		}

		if ( ! isset( $offset ) ) {
			return $field_group;
		}

		if ( $type === 'after' ) {
			$offset ++;
		}

		$fields = array_reverse( $fields );
		foreach ( $fields as $f ) {
			$f['parent'] = $group;
			array_splice( $field_group['fields'], $offset, 0, [ $f ] );
		}

		return $field_group;
	}


	/**
	 * Add Video Helper that can be used globally
	 *
	 * @param $fields
	 * @param $parent
	 */
	public function show_helper_video( $modal_id, $embed_id, $button_text, $title, $echo = false ) {

		$user = dollie()->get_user();

		if ( ! $user->can_manage_all_sites() ) {
			return;
		}

		if ( ! $echo ) {
			return '
				<button data-toggle="tooltip"
	data-placement="bottom"
	data-tooltip="Only Site Admins see this helper button." type="button" data-modal-id="dol-modal-' . $modal_id . '" class="dol-global-modal dol-my-4">
					<i class="fas fa-user-shield"></i>
					<span>' . $button_text . '</span>
				</button>' . dollie()->load_template(
					'parts/video-helper',
					[
						'modal_id'    => $modal_id,
						'embed_id'    => $embed_id,
						'title'       => $title,
						'button_text' => $button_text,
					],
					false
				);
		} else {
			echo '<button type="button" data-toggle="tooltip"
	data-placement="bottom"
	data-tooltip="This button is only view-able for Site Admins. Click on the button to learn more about using & building your Hub with Dollie" data-modal-id="dol-modal-' . $modal_id . '" class="dol-global-modal dol-my-4 dol-text-sm dol-p-2 dol-bg-gray-400">
					<i class="fas fa-user-shield"></i>
					<span class="dol-text-sm">' . $button_text . '</span>
				</button>';

			dollie()->load_template(
				'parts/video-helper',
				[
					'modal_id'    => $modal_id,
					'embed_id'    => $embed_id,
					'title'       => $title,
					'button_text' => $button_text,
				],
				true
			);
		}

	}

}
