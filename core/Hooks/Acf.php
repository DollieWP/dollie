<?php

namespace Dollie\Core\Hooks;

use Dollie\Core\Factories\BaseContainer;
use Dollie\Core\Log;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\ConstInterface;
use Dollie\Core\Services\NoticeService;
use Dollie\Core\Services\WorkspaceService;
use Dollie\Core\Api\PartnerApi;

/**
 * Class Acf
 *
 * @package Dollie\Core\Hooks
 */
final class Acf extends Singleton implements ConstInterface {
	use PartnerApi;

	/**
	 * Acf constructor
	 */
	public function __construct() {
		add_action( 'acf/save_post', [ $this, 'update_customer_role' ] );
		add_action( 'acf/save_post', [ $this, 'update_all_customers_roles' ] );
		add_action( 'acf/save_post', [ $this, 'update_backup_module' ], 1 );
		add_action( 'acf/save_post', [ $this, 'update_staging_status' ], 1 );
		add_action( 'acf/save_post', [ $this, 'update_create_blueprint' ] );
		add_action( 'acf/save_post', [ $this, 'update_blueprint_settings' ] );

		add_action( 'acf/input/admin_footer', [ NoticeService::instance(), 'change_user_role' ] );

		add_filter( 'acf/load_field/type=message', [ $this, 'api_token_content' ], 10, 3 );
		add_filter( 'acf/load_field/name=wpd_api_domain', [
			WorkspaceService::instance(),
			'acf_populate_active_domains'
		] );

		add_filter( 'acf/update_value/name=wpd_container_status', [ $this, 'change_container_status' ], 10, 3 );
		add_filter( 'acf/fields/relationship/result/name=_wpd_included_blueprints', [
			$this,
			'filter_blueprint_relationship_results'
		], 10, 4 );
		add_filter( 'acf/fields/relationship/result/name=_wpd_excluded_blueprints', [
			$this,
			'filter_blueprint_relationship_results'
		], 10, 4 );

		add_filter( 'wp_kses_allowed_html', [ $this, 'acf_add_allowed_iframe_tag' ], 10, 2 );

		add_action( 'dollie/site/set_details/after', [ $this, 'update_site_data' ], 10, 2 );
	}

	/**
	 * Update user role on container when profile changes
	 *
	 * @param $user_id
	 */
	public function update_customer_role( $user_id ) {
		// Make sure we are editing user.
		if ( strpos( $user_id, 'user_' ) === false ) {
			return;
		}

		$user_id = (int) str_replace( 'user_', '', $user_id );

		if ( ! $user_id || user_can( $user_id, 'administrator' ) ) {
			return;
		}

		$user   = dollie()->get_user( $user_id );
		$fields = get_fields( 'user_' . $user->get_id() );
		$role   = '';

		if ( isset( $fields['wpd_client_site_permissions'] ) ) {
			$role = $fields['wpd_client_site_permissions'];
		}

		if ( 'default' === $role ) {
			$role = get_field( 'wpd_client_site_permission', 'options' );
		}

		$last_role = get_user_meta( $user->get_id(), 'wpd_client_last_changed_role', true );

		if ( ! $role || $last_role === $role ) {
			return;
		}

		update_user_meta( $user_id, 'wpd_client_last_changed_role', $role );

		$query = new \WP_Query(
			[
				'author'         => $user->get_id(),
				'post_type'      => 'container',
				'posts_per_page' => - 1,
				'post_status'    => 'publish',
			]
		);

		$posts = $query->get_posts();

		foreach ( $posts as $post ) {
			$container = dollie()->get_container( $post );

			if ( is_wp_error( $container ) || ! $container->is_site() ) {
				continue;
			}

			as_enqueue_async_action(
				'dollie/jobs/single/change_container_customer_role',
				[
					'container' => $container,
					'role'      => $role,
				]
			);
		}

		wp_reset_postdata();

		Log::add( 'Scheduled job to update client access role for ' . $user->get_display_name() );
	}

	/**
	 * Update containers for all customers
	 *
	 * @param $post_id
	 */
	public function update_all_customers_roles( $post_id ) {
		if ( 'options' !== $post_id ) {
			return;
		}

		$role = get_field( 'wpd_client_site_permission', $post_id );

		if ( get_option( 'wpd_client_last_changed_role', '' ) === $role ) {
			return;
		}

		update_option( 'wpd_client_last_changed_role', $role );

		foreach ( get_users() as $user ) {
			if ( $user->has_cap( 'administrator' ) ) {
				continue;
			}

			$this->update_customer_role( 'user_' . $user->ID );
		}

		Log::add( 'Started to update all customers access role' );
	}

	/**
	 * Update custom backup settings
	 *
	 * @param $post_id
	 */
	public function update_backup_module( $post_id ) {
		if ( 'options' !== $post_id ) {
			return;
		}

		$new_data = [];
		$changed  = false;
		$settings = [
			'status'     => 'wpd_enable_custom_backup',
			'provider'   => 'wpd_backup_provider',
			'access_key' => 'wpd_backup_google_key',
			'secret_key' => 'wpd_backup_google_secret',
			'path'       => 'wpd_backup_google_path',
		];

		// Check if any child has changed.
		foreach ( $settings as $k => $setting ) {
			$new_data[ $k ] = $_POST['acf'][ acf_get_field( $setting )['key'] ];

			if ( isset( $new_data[ $k ] ) && get_field( $setting, 'options' ) != $new_data[ $k ] ) {
				$changed = true;
			}
		}

		if ( ! $changed ) {
			return;
		}

		$this->set_partner_option(
			[
				'backup' => $new_data,
			]
		);
	}

	/**
	 * Update staging status
	 *
	 * @param $post_id
	 *
	 * @return void
	 */
	public function update_staging_status( $post_id ) {
		if ( 'options' !== $post_id ) {
			return;
		}

		$changed   = false;
		$new_value = $_POST['acf'][ acf_get_field( 'wpd_enable_staging' )['key'] ];

		if ( get_field( 'wpd_enable_staging', 'options' ) !== $new_value ) {
			$changed = true;
		}

		if ( ! $changed ) {
			return;
		}

		$this->set_partner_option(
			[
				'staging' => (int) $new_value,
			]
		);
	}

	/**
	 * Update or create blueprint
	 *
	 * @param string $post_id
	 *
	 * @return void
	 */
	public function update_create_blueprint( $acf_id ) {
		if ( strpos( $acf_id, 'create_update_blueprint' ) === false ) {
			return;
		}

		$container = dollie()->get_container( (int) str_replace( 'create_update_blueprint_', '', $acf_id ) );

		if ( is_wp_error( $container ) || ! $container->is_blueprint() ) {
			return;
		}

		$container->update_remote_changes();
		$container->set_screenshot_data();

		$container->add_log( Log::WP_SITE_BLUEPRINT_DEPLOYED );
	}

	/**
	 * Update or create blueprint
	 *
	 * @param string $post_id
	 *
	 * @return void
	 */
	public function update_blueprint_settings( $acf_id ) {

		// is the bp update settings form.
		if ( ! isset( $_POST['acf']['field_5b05801b71f85'] ) ) {
			return;
		}

		$container = dollie()->get_container( (int) $acf_id );

		if ( is_wp_error( $container ) || ! $container->is_blueprint() ) {
			return;
		}

		$container->update_remote_settings();

	}

	/**
	 * Show Blueprint information in ACF Relationship results
	 *
	 * @param string $post_id
	 *
	 * @return string
	 */
	public function filter_blueprint_relationship_results( $text, $post, $field, $post_id ) {
		$blueprint_title  = get_field( 'wpd_installation_blueprint_title', $post->ID );
		$blueprint_status = get_field( 'wpd_blueprint_created', $post->ID );

		if ( $blueprint_status ) {
			$status = ' (Live)';
		} else {
			$status = ' (Staging)';
		}

		if ( $blueprint_title ) {
			$text = sprintf( '%s', $blueprint_title ) . $status;
		} else {
			$text = sprintf( '%s', $post->post_title ) . $status;
		}

		return $text;
	}

	/**
	 * Api token display
	 *
	 * @param string $field
	 *
	 * @return string
	 */
	public function api_token_content( $field ) {
		ob_start();
		dollie()->load_template( 'admin/api-status', [], true );
		$details = ob_get_clean();

		$field['message'] = str_replace(
			[ '%api_settings%' ],
			[ $details ],
			$field['message']
		);

		return $field;
	}

	/**
	 * Change container status
	 *
	 * @param $value
	 * @param $post_id
	 * @param $field
	 *
	 * @return mixed
	 */
	public function change_container_status( $value, $post_id, $field ) {
		$container = dollie()->get_container( $post_id );

		if ( ! is_wp_error( $container ) && $container->get_status() !== $value ) {
			$container->perform_action( $value );
		}

		return $value;
	}

	/**
	 * Add Filter to allow iframe helper video
	 *
	 * @param $tags
	 * @param $conetext
	 */
	public function acf_add_allowed_iframe_tag( $tags, $context ) {
		if ( $context === 'acf' ) {
			$tags['iframe'] = array(
				'src'             => true,
				'height'          => true,
				'width'           => true,
				'frameborder'     => true,
				'allowfullscreen' => true,
			);
		}

		return $tags;
	}

	/**
	 * @param array $data
	 * @param BaseContainer $container
	 *
	 * @return void
	 */
	public function update_site_data( $data, $container ) {
		if ( empty( $data ) ) {
			return;
		}

		foreach ( $data['site'] as $key => $value ) {
			if ( is_array( $value ) && ( $key === 'stats' || $key === 'admin' || $key === 'theme' ) ) {
				foreach ( $value as $sub_key => $sub_value ) {
					if ( empty( $sub_value ) ) {
						continue;
					}

					$field_key   = 'wpd_site_stats_' . $key . '_' . $sub_key;
					$field_label = ucfirst( $key ) . ' - ' . ucfirst( str_replace( '_', ' ', $sub_key ) );
					$field_type  = ( preg_match( "/\.(jpg|png)$/", $sub_value ) ) ? 'image' : 'text';

					if ( filter_var( $sub_value, FILTER_VALIDATE_URL ) ) {
						$field_type = 'url';
					}

					if ( $field_type === 'image' ) {
						$field['return_format'] = 'url';
					}

					$field = array(
						'key'   => 'field_' . md5( $field_key ),
						'label' => $field_label,
						'name'  => $field_key,
						'type'  => $field_type,
						'value' => $sub_value,
					);

					update_field( $field['key'], $field['value'], $container->get_id() );
				}
			} elseif ( is_array( $value ) ) {
				$repeater_key   = 'field_' . md5( $key );
				$repeater_field = array(
					'key'        => $repeater_key,
					'label'      => ucfirst( $key ),
					'name'       => 'wpd_site_stats_' . $key,
					'type'       => 'repeater',
					'sub_fields' => array(),
					'value'      => array(),
				);

				foreach ( $value as $sub_key => $sub_value ) {
					if ( is_array( $sub_value ) ) {
						foreach ( $sub_value as $sub_sub_key => $sub_sub_value ) {
							if ( empty( $sub_sub_value ) ) {
								continue;
							}
							$sub_field_type = ( preg_match( "/\.(jpg|png)$/", $sub_sub_value ) ) ? 'image' : 'text';

							if ( filter_var( $sub_sub_value, FILTER_VALIDATE_URL ) ) {
								$sub_field_type = 'url';
							}

							$sub_field                      = array(
								'key'   => 'field_' . md5( $sub_sub_key ),
								'label' => ucfirst( $sub_sub_key ),
								'name'  => $sub_sub_key,
								'type'  => $sub_field_type,
							);
							$repeater_field['sub_fields'][] = $sub_field;
						}
						$repeater_item = array();
						foreach ( $repeater_field['sub_fields'] as $sub_field ) {
							$repeater_item[ $sub_field['key'] ] = $sub_value[ $sub_field['name'] ];
						}
						$repeater_field['value'][] = $repeater_item;
					}
				}

				update_field( $repeater_key, $repeater_field['value'], $container->get_id() );
			} else {
				if ( empty( $value ) ) {
					continue;
				}
				$field_type = 'text'; // default field type

				if ( preg_match( "/\.(jpg|png)$/", $value ) ) {
					$field_type = 'image';
				} elseif ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
					$field_type = 'url';
				}

				$field = array(
					'key'   => 'field_' . md5( $key ),
					'label' => ucfirst( $key ),
					'name'  => 'wpd_stats_' . $key,
					'type'  => $field_type,
					'value' => $value,
				);

				// If the field is an image, set the return format to URL.
				if ( $field_type === 'image' ) {
					$field['return_format'] = 'url';
				}

				update_field( $field['key'], $field['value'], $container->get_id() );
			}
		}
	}
}
