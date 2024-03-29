<?php

namespace Dollie\Core\Modules\AccessGroups;

use Dollie\Core\Modules\Integrations\Integrations;
use Dollie\Core\Singleton;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Hooks
 *
 * @package Dollie\Core\Modules\AccessGroups
 */
class AccessGroups extends Singleton {

	/**
	 * Hooks constructor.
	 * Sets up the hooks needed for the access group system.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'init', array( $this, 'register_access_groups_cpt' ) );
		add_action( 'init', array( $this, 'create_access_group_terms' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_logs_meta_box' ) );
		add_action( 'admin_footer', array( $this, 'acf_readonly_fields' ) );
		add_action( 'acf/init', array( $this, 'load_acf' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_script' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'output_user_custom_column_value' ), 10, 3 );
		add_filter( 'manage_users_columns', array( $this, 'add_user_custom_column' ) );
		add_filter( 'dollie/blueprints', array( $this, 'filter_blueprints' ) );
	}

	/**
	 * Allow only logged in users
	 *
	 * @return void
	 */
	public function register_access_groups_cpt() {
		/**
		 * Post Type: Hub Access Groups.
		 */
		$labels = array(
			'name'                     => __( 'Hub Access Groups', 'hello-dollie' ),
			'singular_name'            => __( 'Hub Access Group', 'hello-dollie' ),
			'menu_name'                => __( 'Hub Access', 'hello-dollie' ),
			'all_items'                => __( 'Access Groups', 'hello-dollie' ),
			'add_new'                  => __( 'Add Access Group', 'hello-dollie' ),
			'add_new_item'             => __( 'New Hub Access Group', 'hello-dollie' ),
			'edit_item'                => __( 'Edit Hub Access Group', 'hello-dollie' ),
			'new_item'                 => __( 'New Hub Access Group', 'hello-dollie' ),
			'view_item'                => __( 'View Hub Access Group', 'hello-dollie' ),
			'view_items'               => __( 'View Hub Access Groups', 'hello-dollie' ),
			'search_items'             => __( 'Search Hub Access Groups', 'hello-dollie' ),
			'not_found'                => __( 'No Hub Access Groups found', 'hello-dollie' ),
			'not_found_in_trash'       => __( 'No Hub Access Groups found in trash', 'hello-dollie' ),
			'parent'                   => __( 'Parent Hub Access Group:', 'hello-dollie' ),
			'featured_image'           => __( 'Featured image for this Hub Access Group', 'hello-dollie' ),
			'set_featured_image'       => __( 'Set featured image for this Hub Access Group', 'hello-dollie' ),
			'remove_featured_image'    => __( 'Remove featured image for this Hub Access Group', 'hello-dollie' ),
			'use_featured_image'       => __( 'Use as featured image for this Hub Access Group', 'hello-dollie' ),
			'archives'                 => __( 'Hub Access Group archives', 'hello-dollie' ),
			'insert_into_item'         => __( 'Insert into Hub Access Group', 'hello-dollie' ),
			'uploaded_to_this_item'    => __( 'Upload to this Hub Access Group', 'hello-dollie' ),
			'filter_items_list'        => __( 'Filter Hub Access Groups list', 'hello-dollie' ),
			'items_list_navigation'    => __( 'Hub Access Groups list navigation', 'hello-dollie' ),
			'items_list'               => __( 'Hub Access Groups list', 'hello-dollie' ),
			'attributes'               => __( 'Hub Access Groups attributes', 'hello-dollie' ),
			'name_admin_bar'           => __( 'Hub Access Group', 'hello-dollie' ),
			'item_published'           => __( 'Hub Access Group published', 'hello-dollie' ),
			'item_published_privately' => __( 'Hub Access Group published privately.', 'hello-dollie' ),
			'item_reverted_to_draft'   => __( 'Hub Access Group reverted to draft.', 'hello-dollie' ),
			'item_scheduled'           => __( 'Hub Access Group scheduled', 'hello-dollie' ),
			'item_updated'             => __( 'Hub Access Group updated.', 'hello-dollie' ),
			'parent_item_colon'        => __( 'Parent Hub Access Group:', 'hello-dollie' ),
		);

		$args = array(
			'label'                 => __( 'Hub Access Groups', 'hello-dollie' ),
			'labels'                => $labels,
			'description'           => '',
			'public'                => true,
			'publicly_queryable'    => false,
			'show_ui'               => true,
			'show_in_rest'          => true,
			'menu_position'         => 1,
			'rest_base'             => 'dollie_access_group',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'rest_namespace'        => 'dollie',
			'has_archive'           => false,
			'show_in_menu'          => true,
			'show_in_nav_menus'     => true,
			'delete_with_user'      => false,
			'exclude_from_search'   => true,
			'capability_type'       => 'post',
			'map_meta_cap'          => true,
			'hierarchical'          => false,
			'can_export'            => true,
			'rewrite'               => array(
				'slug'       => 'dollie-access-groups',
				'with_front' => true,
			),
			'query_var'             => true,
			'menu_icon'             => 'dashicons-groups',
			'supports'              => array( 'title', 'thumbnail', 'revisions' ),
			'show_in_graphql'       => false,
		);

		register_post_type( 'dollie-access-groups', $args );
	}


	/**
	 * Load ACF
	 *
	 * @return void
	 */
	public function load_acf() {
		require DOLLIE_CORE_PATH . 'Modules/AccessGroups/acf-fields/acf-fields.php';
	}

	public static function get_access_groups() {
		$args = array(
			'post_type'      => 'dollie-access-groups',
			'post_status'    => 'publish',
			'posts_per_page' => - 1, // Get all posts
		);

		return get_posts( $args );
	}

	/**
	 * Adds a list of users to an access group.
	 *
	 * @param int $group_id ID of the group
	 * @param array|int $user_ids Array of user IDs
	 * @param string $source Source from which the users are added
	 * @param string $integration Name of the integration
	 * @param string $action Name of the action
	 */
	public function add_to_access_group( $group_id, $user_ids, $source = null, $integration = '', $action = '' ) {
		if ( ! is_array( $user_ids ) ) {
			$user_ids = array( $user_ids );
		}

		// Assuming the field name is 'wpd_group_users'
		$current_users = get_field( 'wpd_group_users', $group_id );

		if ( ! is_array( $current_users ) ) {
			$current_users = array();
		}

		$updated_users = array_unique( array_merge( $current_users, $user_ids ) );
		update_field( 'wpd_group_users', $updated_users, $group_id );

		// Get group (post) title
		$group_title = get_the_title( $group_id );

		// Iterate over the added users to get their details
		foreach ( $updated_users as $userid ) {
			$user_info = get_userdata( $userid );
			$username  = $user_info->user_login;
			$useremail = $user_info->user_email;

			$log_message = sprintf(
				esc_html__( 'Customer %1$s (email: %2$s, ID: %3$s) was added to group %4$s (ID: %5$s)', 'dollie' ),
				$username,
				$useremail,
				$userid,
				$group_title,
				$group_id
			);

			// If source is provided, add it to the log message
			if ( $source ) {
				$log_message .= sprintf(
					esc_html__( ' Source: %s.', 'dollie' ),
					$source
				);
			}

			\WDS_Log_Post::log_message( 'dollie-logs', $log_message, '', (string) $group_id );

			$remove_from_groups = get_field( 'wpd_remove_from_access_groups', $group_id );

			if ( ! is_array( $remove_from_groups ) ) {
				$remove_from_groups = array();
			}

			// Remove users from other groups
			foreach ( $remove_from_groups as $remove_group ) {
				$user_info = get_userdata( $userid );

				// Remove user from the access group
				$this->remove_from_access_group(
					$remove_group->ID,                // Group ID
					$userid,        // User IDs
					'Group Interaction',            // Source
					'group-interaction', // Log type
					'Removed on subscription stopped for product ' . get_the_title( $remove_group ) . '.'
				);

			}

		}




		// Update the wpd_registered_integrations ACF repeater field with new integration and action
		if ( $integration && $action ) {
			$integration_exists = false;
			if ( have_rows( 'wpd_registered_integrations', $group_id ) ) {
				while ( have_rows( 'wpd_registered_integrations', $group_id ) ) {
					the_row();
					if ( get_sub_field( 'name' ) == $integration ) {
						$integration_exists    = true;
						$current_actions       = get_sub_field( 'actions' );
						$current_actions_array = explode( ', ', $current_actions ); // Convert string back to array
						if ( ! in_array( $action, $current_actions_array ) ) { // Check if the action doesn't exist
							$current_actions_array[] = $action;
							$current_actions         = implode( ', ', $current_actions_array ); // Convert array back to string
							update_sub_field( 'actions', $current_actions );
						}
						break;
					}
				}
			}

			if ( ! $integration_exists ) {
				$new_integration = array(
					'name'    => $integration,
					'actions' => $action, // Directly inserting the action as it's a string now
				);
				add_row( 'wpd_registered_integrations', $new_integration, $group_id );
			}
		}
	}

	/**
	 * Removes a list of users from an access group.
	 *
	 * @param int $group_id ID of the group
	 * @param array|int $user_ids Array of user IDs
	 * @param string $source Source from which the users are removed
	 */
	public function remove_from_access_group( $group_id, $user_ids, $source = null, $integration = '', $action = '' ) {
		if ( ! is_array( $user_ids ) ) {
			$user_ids = array( $user_ids );
		}

		// Assuming the field name is 'wpd_group_users'
		$current_users = get_field( 'wpd_group_users', $group_id );

		if ( ! is_array( $current_users ) ) {
			return;
		}

		// Users to remove are those in both the input and the group
		$users_to_remove = array_intersect( $user_ids, $current_users );

		$updated_users = array_diff( $current_users, $users_to_remove );
		update_field( 'wpd_group_users', $updated_users, $group_id );

		// Get group (post) title
		$group_title = get_the_title( $group_id );

		// Iterate over the users to remove to get their details
		foreach ( $users_to_remove as $userid ) {
			$user_info = get_userdata( $userid );
			$username  = $user_info->user_login;
			$useremail = $user_info->user_email;

			$log_message = sprintf(
				esc_html__( 'Customer %1$s (email: %2$s, ID: %3$s) was removed from group %4$s (ID: %5$s)', 'dollie' ),
				$username,
				$useremail,
				$userid,
				$group_title,
				$group_id
			);

			// If source is provided, add it to the log message
			if ( $source ) {
				$log_message .= sprintf(
					esc_html__( ' Source: %s.', 'dollie' ),
					$source
				);
			}

			\WDS_Log_Post::log_message( 'dollie-logs', $log_message, '', strval( $group_id ) );
		}

		// Update the wpd_registered_integrations ACF repeater field with new integration and action
		if ( $integration && $action ) {
			$integration_exists = false;
			if ( have_rows( 'wpd_registered_integrations', $group_id ) ) {
				while ( have_rows( 'wpd_registered_integrations', $group_id ) ) {
					the_row();
					if ( get_sub_field( 'name' ) == $integration ) {
						$integration_exists    = true;
						$current_actions       = get_sub_field( 'actions' );
						$current_actions_array = explode( ', ', $current_actions ); // Convert string back to array
						if ( ! in_array( $action, $current_actions_array ) ) { // Check if the action doesn't exist
							$current_actions_array[] = $action;
							$current_actions         = implode( ', ', $current_actions_array ); // Convert array back to string
							update_sub_field( 'actions', $current_actions );
						}
						break;
					}
				}
			}

			if ( ! $integration_exists ) {
				$new_integration = array(
					'name'    => $integration,
					'actions' => $action, // Directly inserting the action as it's a string now
				);
				add_row( 'wpd_registered_integrations', $new_integration, $group_id );
			}
		}
	}


	/**
	 * Checks if a user has access to a group.
	 *
	 * @param int $group_id ID of the group
	 * @param int $user_id ID of the user
	 *
	 * @return bool
	 */
	public function user_has_access_to_group( $group_id, $user_id ) {
		// Assuming the field name is 'wpd_group_users'
		$current_users = get_field( 'wpd_group_users', $group_id );

		// If there are no current users, return false
		if ( ! is_array( $current_users ) ) {
			return false;
		}

		// Check if the user ID is in the array
		return in_array( $user_id, $current_users );
	}

	/**
	 * Retrieves users in a group.
	 *
	 * @param int $group_id ID of the group
	 *
	 * @return array
	 */
	public function get_users_in_group( $group_id ) {
		$current_users = get_field( 'wpd_group_users', $group_id );

		return is_array( $current_users ) ? $current_users : array();
	}

	/**
	 * Checks if a group is empty.
	 *
	 * @param int $group_id ID of the group
	 *
	 * @return bool
	 */
	public function is_group_empty( $group_id ) {
		$current_users = get_field( 'wpd_group_users', $group_id );

		return empty( $current_users );
	}

	/**
	 * Counts users in a group.
	 *
	 * @param int $group_id ID of the group
	 *
	 * @return int
	 */
	public function count_users_in_group( $group_id ) {
		$current_users = get_field( 'wpd_group_users', $group_id );

		return is_array( $current_users ) ? count( $current_users ) : 0;
	}

	/**
	 * Retrieves logs by group ID.
	 *
	 * @param int $group_id ID of the group
	 *
	 * @return array
	 */
	public function get_logs_by_group_id( $group_id ) {
		$args  = array(
			'post_type' => 'dollie-logs',
			'tax_query' => array(
				array(
					'taxonomy' => 'wds_log_type',
					'field'    => 'slug',
					'terms'    => strval( $group_id ),
				),
			),
		);
		$query = new \WP_Query( $args );

		return $query->posts;
	}

	/**
	 * Displays logs in a meta box.
	 *
	 * @param WP_Post $post The object for the current post/page
	 */
	public function display_logs_meta_box( $post ) {
		$group_id = $post->ID;
		$logs     = $this->get_logs_by_group_id( $group_id );

		if ( empty( $logs ) ) {
			echo 'No logs for this group.';

			return;
		}

		$logs = array_slice( $logs, - 50 );

		foreach ( $logs as $log ) {
			echo '<p>';
			echo '<strong>' . $log->post_date . '</strong> - ';
			echo $log->post_title;
			echo '</p>';
		}
	}

	/**
	 * Adds a logs meta box.
	 */
	public function add_logs_meta_box() {
		add_meta_box(
			'logs_meta_box', // id
			'The last 50 log entries for this group', // title
			array( $this, 'display_logs_meta_box' ), // callback
			'dollie-access-groups', // screen (post type)
			'normal', // context
			'default' // priority
		);
	}


	/**
	 * Creates access group terms.
	 */
	function create_access_group_terms() {
		// Query for 'dollie-access-groups' posts
		$args  = array(
			'post_type'      => 'dollie-access-groups',
			'post_status'    => 'publish',
			'posts_per_page' => - 1, // Get all posts
		);
		$posts = get_posts( $args );

		// Get the current log types
		$log_types = apply_filters( 'wds_log_post_log_types', array() );

		foreach ( $posts as $post ) {
			if ( ! isset( $log_types[ $post->post_title ] ) ) {
				$log_types[ $post->post_title ] = array(
					'slug'        => (string) $post->ID, // Using post ID as the slug
					'description' => 'Access Group - ' . $post->post_title, // Using post title as the description
				);
			}
		}

		// Add a filter to set the log types
		add_filter(
			'wds_log_post_log_types',
			function ( $terms ) use ( $log_types ) {
				return $log_types;
			}
		);
	}

	/**
	 * Retrieves user groups.
	 *
	 * @param int $user_id ID of the user
	 *
	 * @return array
	 */
	public function get_user_groups( $user_id ) {
		$group_ids = get_field( 'wpd_group_users', 'user_' . $user_id );

		// If the field is empty or not an array, return an empty array
		if ( ! is_array( $group_ids ) ) {
			return [];
		}

		return $group_ids;
	}


	/**
	 * Get groups for customer
	 *
	 * @param string $status
	 * @param null|int $customer_id
	 *
	 * @return array|bool
	 */
	/**
	 * Get groups for customer
	 *
	 * @param null|int $customer_id
	 *
	 * @return array|bool
	 */
	public function get_customer_access( $customer_id = null ) {

		if ( ! $customer_id ) {
			$customer_id = get_current_user_id();
		}

		// First check for user specific settings. These will overwire any group settings.
		$user_specific_settings = get_field( 'wpd_enable_user_access_overwrite', 'user_' . $customer_id );

		if ( $user_specific_settings ) {
			$settings_ids = array( 'user_' . $customer_id ); // Use user-specific settings
		} else {
			$settings_ids = $this->get_user_groups( $customer_id );

			if ( ! is_array( $settings_ids ) || empty( $settings_ids ) ) {
				return false;
			}
		}

		$data = array(
			'plans'     => array(),
			'resources' => array(
				'max_allowed_installs' => 0,
				'max_allowed_size'     => 0,
				'staging_max_allowed'  => 0,
			),
		);

		foreach ( $settings_ids as $id ) {

			// Adjust the $group_id based on the source of settings
			$group_id = $user_specific_settings ? 'user_' . $customer_id : $id;

			// Use the $group_id to get the WP_Post object
			$group_post = get_post( $group_id );
			if ( ! $group_post && ! $user_specific_settings ) {
				continue;
			}

			$name                          = $user_specific_settings ? 'Custom Settings on User Profile' : $group_post->post_title;
			$installs                      = (int) get_field( '_wpd_installs', $group_id );
			$max_size                      = get_field( '_wpd_max_size', $group_id );
			$staging                       = get_field( '_wpd_staging_installs', $group_id );
			$available_sections            = get_field( 'available_sections', $group_id );
			$available_features_developers = get_field( 'available_features_developers', $group_id );

			if ( ! $staging ) {
				$staging = 0;
			}

			if ( ! $max_size ) {
				$max_size = 0;
			}

			$data['plans']['products'][ $id ] = array(
				'name'                => $name,
				'installs'            => $installs,
				'max_size'            => $max_size,
				'included_blueprints' => get_field( '_wpd_included_blueprints', $group_id ),
				'excluded_blueprints' => get_field( '_wpd_excluded_blueprints', $group_id ),
			);

			$data['resources']['max_allowed_installs']          += $installs;
			$data['resources']['max_allowed_size']              += $max_size;
			$data['resources']['name']                          = $name;
			$data['resources']['staging_max_allowed']           += $staging;
			$data['resources']['available_sections']            = $available_sections;
			$data['resources']['available_features_developers'] = $available_features_developers;

			$data = apply_filters( 'dollie/woo/subscription_product_data', $data, $customer_id, $group_id );
		}

		// Save each value from the $data array as an individual user meta field
		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $innerKey => $innerValue ) {
					if ( is_array( $innerValue ) ) {
						continue; // Skip nested arrays
					}

					update_user_meta( $customer_id, 'dollie_hub_' . $key . '_' . $innerKey, $innerValue );
				}
			} else {
				update_user_meta( $customer_id, 'dollie_hub_' . $key, $value );
			}
		}

		return apply_filters( 'dollie/woo/subscription_data', $data, $customer_id );
	}


	public function enqueue_admin_script( $hook ) {
		if ( 'user-edit.php' === $hook ) {
			wp_enqueue_script( 'dollie-groups-js', DOLLIE_ASSETS_URL . 'js/admin-access-groups.js', array(), DOLLIE_VERSION );
		}
	}

	/**
	 * Makes Advanced Custom Fields readonly.
	 */
	public function acf_readonly_fields() {
		?>
        <script type="text/javascript">
          jQuery(document).ready(function ($) {
            // replace 'field_12345678' with the field key of the field you want to disable
            $('div[data-key="field_649420e3e3fb7"] input, div[data-key="field_649420e3e3fb7"] textarea').attr(
              'readonly', 'true');
            $('div[data-key="field_649420e3e3fb7"] .acf-button, div[data-key="field_649420e3e3fb7"] .acf-row-handle')
              .addClass('hidden');
          });
        </script>
		<?php
	}


	public function add_user_custom_column( $columns ) {
		$columns['access_groups'] = 'Hub Access Groups';

		return $columns;
	}

	// Output custom field value in new column

	public function output_user_custom_column_value( $value, $column_name, $user_id ) {
		if ( $column_name === 'access_groups' ) {
			$access_group_ids = get_field( 'wpd_group_users', 'user_' . $user_id );

			// If there are any access group IDs, get the title of each post and return them
			if ( is_array( $access_group_ids ) && ! empty( $access_group_ids ) ) {
				$access_group_titles = array_map(
					function ( $post_id ) {
						return '<a href="' . get_edit_post_link( $post_id ) . '">' . get_the_title( $post_id ) . '</a>';
					},
					$access_group_ids
				);

				return implode( ', ', $access_group_titles );
			}

			// If there are no access groups, return a default message
			return '-';
		}

		return $value;
	}


	public function get_customer_developer_features( $customer_id = null ) {
		// Get the customer specific details
		$customer_access_details = $this->get_customer_access( $customer_id );
		$customer_features       = $customer_access_details['resources']['available_features_developers'] ?? array();

		// Get the global developer features
		$global_features = get_field( 'available_features_developers', 'option' );

		// Merge the arrays and remove duplicates
		$developer_features = array_unique( array_merge( $customer_features, $global_features ) );

		return $developer_features;
	}


	public function get_customer_site_features( $customer_id = null ) {
		$customer_access_details = $this->get_customer_access( $customer_id );

		return $customer_access_details['resources']['available_sections'] ?? array();
	}

	/**
	 * Check if customer has subscription
	 *
	 * @return bool
	 */
	public function has_subscription( $customer_id = null ) {

		if ( get_option( 'options_wpd_charge_for_deployments' ) !== '1' ) {
			return true;
		}

		$subscription = $this->get_customer_access( $customer_id );

		return $subscription ? (bool) $subscription['plans'] : $subscription;
	}

	/**
	 * Get how many sites are left available for customer
	 *
	 * @return int|mixed
	 */
	public function sites_available( $customer_id = null ) {

		if ( ! $customer_id ) {
			$customer_id = get_current_user_id();
		}


		// Overwritten by Custom User Value
		$is_custom = get_field( '_wpd_installs', 'user_' . $customer_id );

		if ( ! empty( $is_custom ) && is_numeric( $is_custom ) && $is_custom > 0 ) {
			return $is_custom - dollie()->get_user()->count_containers();

		}

		$subscription = $this->get_customer_access();

		if ( ! $subscription ) {
			return 0;
		}

		return $subscription['resources']['max_allowed_installs'] - dollie()->get_user()->count_containers();
	}

	/**
	 * Get storage available for customer
	 *
	 * @return int|mixed
	 */
	public function storage_available( $customer_id = null ) {

		if ( ! $customer_id ) {
			$customer_id = get_current_user_id();
		}

		$is_custom = get_field( '_wpd_max_size', 'user_' . $customer_id );

		if ( ! empty( $is_custom ) && is_numeric( $is_custom ) && $is_custom > 0 ) {
			return $is_custom;
		}

		$subscription = $this->get_customer_access();

		if ( ! $subscription ) {
			return 0;
		}

		return $subscription['resources']['max_allowed_size'];
	}

	/**
	 * Get has VIP subscription enabled for customer
	 *
	 * @return bool
	 */
	public function vip_status( $user_id = null ) {

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( get_field( '_wpd_woo_launch_as_vip', 'user_' . $user_id ) ) {
			return get_field( '_wpd_woo_launch_as_vip', 'user_' . $user_id );
		}

		$subscription = $this->get_customer_access();

		if ( ! $subscription ) {
			return 0;
		}

		return $subscription['resources']['launch_as_vip'];
	}

	/**
	 * Get subscription name
	 *
	 * @return mixed|string
	 */
	public function subscription_name() {
		$subscription = $this->get_customer_access();

		if ( ! $subscription || ! isset( $subscription['resources']['name'] ) ) {
			return __( 'None', 'dollie' );
		}

		return $subscription['resources']['name'];
	}

	/**
	 * Check if site limit has been reached
	 *
	 * @return bool
	 */
	public function site_limit_reached( $user_id = null ) {
		if ( get_option( 'options_wpd_charge_for_deployments' ) !== '1' ) {
			return false;
		}

		$user = dollie()->get_user( $user_id );

		if ( $user->can_manage_all_sites() ) {
			return false;
		}

		$is_custom = get_user_meta( $user->get_id(), 'dollie_hub_resources_max_allowed_installs', true );

		if ( ! empty( $is_custom ) && is_numeric( $is_custom ) && $is_custom > 0 ) {
			return $user->count_containers() >= $is_custom;
		}

		$subscription = $this->get_customer_access();

		if ( ! $subscription ) {
			return true;
		}

		return $user->count_containers() >= $subscription['resources']['max_allowed_installs'];
	}

	/**
	 * Check if the size limit has been reached
	 *
	 * @return bool
	 */
	public function size_limit_reached( $customer_id = null ) {
		if ( get_option( 'options_wpd_charge_for_deployments' ) !== '1' ) {
			return false;
		}

		if ( ! $customer_id ) {
			$customer_id = get_current_user_id();
		}

		$user = dollie()->get_user();

		$is_custom = get_field( '_wpd_max_size', 'user_' . $customer_id );

		if ( ! empty( $is_custom ) && is_numeric( $is_custom ) && $is_custom > 0 ) {
			$allowed_size = $is_custom;
			$total_size   = dollie()->insights()->get_total_container_size();
			$allowed_size *= 1024 * 1024 * 1024;

			return $total_size >= $allowed_size && ! $user->can_manage_all_sites();

		}

		$subscription = $this->get_customer_access();

		if ( ! $subscription ) {
			return false;
		}

		$total_size   = dollie()->insights()->get_total_container_size();
		$allowed_size = $subscription['resources']['max_allowed_size'] * 1024 * 1024 * 1024;

		return $this->has_subscription() && $total_size >= $allowed_size && ! $user->can_manage_all_sites();
	}

	/**
	 * Get excluded blueprints
	 *
	 * @return array|boolean
	 */
	public function get_blueprints_exception( $type = 'excluded' ) {
		$data   = array();
		$type   .= '_blueprints';
		$access = $this->get_customer_access();

		if ( empty( $access ) ) {
			return false;
		}

		foreach ( $access['plans']['products'] as $product ) {
			if ( isset( $product[ $type ] ) && ! empty( $product[ $type ] ) ) {
				foreach ( $product[ $type ] as $bp ) {
					$data[ $bp ] = $bp;
				}
			}
		}

		if ( empty( $data ) ) {
			return false;
		}

		return $data;
	}

	/**
	 * Check if user has staging
	 *
	 * @param null|int $user_id
	 *
	 * @return boolean
	 */
	public function has_staging( $user_id = null ) {
		if ( get_option( 'options_wpd_charge_for_deployments' ) !== '1' ) {
			return true;
		}

		if ( ! get_field( 'wpd_enable_staging', 'options' ) ) {
			return false;
		}

		if ( is_super_admin() ) {
			return true;
		}

		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}

		$access = $this->get_customer_access( $user_id );

		// If no subscription is active.
		if ( empty( $access ) ) {
			return false;
		}

		// Apply overrides at product level.
		if ( isset( $access['resources']['staging_max_allowed'] ) ) {
			return $access['resources']['staging_max_allowed'] > 0;
		}

		return false;
	}

	/**
	 * Filter blueprints
	 *
	 * @param array $blueprints
	 *
	 * @return array
	 */
	public function filter_blueprints( $blueprints ) {

		$user = dollie()->get_user();

		if ( $user->can_manage_all_sites() ) {
			return $blueprints;
		}

		if ( empty( $blueprints ) ) {
			return $blueprints;
		}

		$customer_id  = get_current_user_id();
		$sub_included = $this->get_blueprints_exception( 'included' );

		// Has Blueprint includes in User meta?
		if ( get_field( '_wpd_included_blueprints', 'user_' . $customer_id ) ) {
			$user_included_blueprints = get_field( '_wpd_included_blueprints', 'user_' . $customer_id );

			// Check if arrays should be merged.
			if ( ! empty( $sub_included ) ) {
				$included = array_merge( $sub_included, $user_included_blueprints );
			} else {
				$excluded = $user_included_blueprints;
			}
		} else {
			$included = $sub_included;
		}

		if ( ! empty( $included ) ) {
			return array_intersect_key( $blueprints, $included );
		}

		// Has Blueprint exclusions in sub?
		$sub_excluded = $this->get_blueprints_exception();

		// Has Blueprint excludes in User meta?
		if ( get_field( '_wpd_excluded_blueprints', 'user_' . $customer_id ) ) {
			$user_excluded_blueprints = get_field( '_wpd_excluded_blueprints', 'user_' . $customer_id );

			// Check if arrays should be merged.
			if ( ! empty( $sub_excluded ) ) {
				$excluded = array_merge( $sub_excluded, $user_excluded_blueprints );
			} else {
				$excluded = $user_excluded_blueprints;
			}
		} else {
			$excluded = $sub_excluded;
		}

		// Filter blueprints.
		if ( ! empty( $excluded ) ) {
			foreach ( $excluded as $bp_id ) {
				if ( isset( $blueprints[ $bp_id ] ) ) {
					unset( $blueprints[ $bp_id ] );
				}
			}
		}

		return $blueprints;
	}

	public function get_checkout_link( $args ) {
		return Integrations::instance()->get_checkout_link( $args );
	}

	/**
	 * Check if site limit has been reached
	 *
	 * @return bool
	 */
	public function staging_sites_limit_reached( $user_id = null ) {

		$user = dollie()->get_user();

		if ( $user->can_manage_all_sites() ) {
			return false;
		}

		if ( get_option( 'options_wpd_charge_for_deployments' ) !== '1' ) {
			return false;
		}

		$user = dollie()->get_user( $user_id );

		if ( $user->can_manage_options() ) {
			return false;
		}

		$access = $this->get_customer_access( $user->get_id() );

		if ( ! is_array( $access ) || empty( $access ) ) {
			return false;
		}

		return ( $access['resources']['staging_max_allowed'] - (int) $user->count_stagings() ) <= 0;
	}

	/**
	 * Check if user has vip status
	 *
	 * @param null|int $user_id
	 *
	 * @return boolean
	 */
	public function has_vip( $user_id = null ) {

		if ( ! get_field( 'wpd_enable_vip_sites', 'options' ) ) {
			return false;
		}

		if ( is_super_admin() ) {
			return true;
		}

		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}

		// Has VIP via User meta overwrite?
		$usermeta_vip = get_field( '_wpd_woo_launch_as_vip', 'user_' . $user_id );

		if ( $usermeta_vip ) {
			return true;
		}

		// Has subscription?
		$access = $this->get_customer_access( null, $user_id );

		// If no subscription is active or no subscription is found.
		if ( empty( $access ) ) {
			return false;
		}

		// Has subscription but is VIP enabled for this subscription?
		if ( isset( $access['resources']['launch_as_vip'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get available sections
	 *
	 * @return mixed
	 */
	public function get_available_sections() {

		$user = dollie()->get_user();

		// Get default access settings
		$default_access_settings = get_field( 'available_sections', 'option' );

		// Get customer developer features
		$hooks                       = self::instance();
		$customer_developer_features = $hooks->get_customer_site_features();

		// Merge default and customer settings
		$available_sections_array = array_merge( $default_access_settings, $customer_developer_features );

		// Remove duplicates
		$available_sections_array = array_unique( $available_sections_array );

		if ( get_field( 'wpd_enable_blueprints_for', 'option' ) === 'all' && ! $user->can_manage_all_sites() ) {
			$available_sections_array = array_filter(
				$available_sections_array,
				function ( $v, $k ) {
					return $v !== 'blueprints';
				}
			);
		}

		if ( ! in_array( 'staging', $available_sections_array ) ) {
			$available_sections_array[] = 'staging';
		}

		return $available_sections_array;
	}

	public function has_bought_product( $user_id = null ) {

		return $this->has_subscription( $user_id );
	}

	public function migrate_woo_to_acces_groups() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ! function_exists( 'wcs_get_subscriptions' ) ) {
			return true;
		}

		$subscriptions = wcs_get_subscriptions(
			[
				'subscription_status' => 'active',
			]
		);

		if ( ! is_array( $subscriptions ) || empty( $subscriptions ) ) {
			return false;
		}

		foreach ( $subscriptions as $subscription_id => $subscription ) {

			if ( empty( $subscription_id ) ) {
				continue;
			}

			// Getting the subscription Order ID.
			$the_subscription = wcs_get_subscription( $subscription_id );
			$user_id          = $the_subscription->get_user_id();

			// Get the right number of items, count also any upgraded/downgraded orders.
			$order_items = $the_subscription->get_items();

			if ( ! is_array( $order_items ) || empty( $order_items ) ) {
				continue;
			}

			// Iterating through each item in the order.
			foreach ( $order_items as $item_data ) {

				// Check if it's a variation
				if ( $item_data->get_variation_id() ) {
					$product_id   = $item_data->get_variation_id();
					$product_type = 'variation';
				} else {
					$product_id   = $item_data->get_product_id();
					$product_type = 'product';
				}

				if ( 0 === $product_id ) {
					continue;
				}

				// Filter out non Dollie subscriptions by checking custom meta field.
				if ( ! get_field( '_wpd_installs', $product_id ) ) {
					continue;
				}

				// Add user to the access group.
				$group_id = $this->get_or_create_by_name_from_product( get_the_title( $product_id ), $product_id );

				$this->add_to_access_group(
					$group_id,       // Group ID
					$user_id,        // User IDs
					'WooCommerce',  // Source
					'WooCommerce', // Log type
					'Migrated to access groups the purchase of ' . $product_type . ' ' . get_the_title( $product_id ) . '.'
				);

			}
		}

		return true;
	}

	private function get_or_create_by_name_from_product( $name, $product_id ) {

		$existing_groups = get_posts( [
			'title'     => $name,
			'post_type' => 'dollie-access-groups',
		] );

		if ( ! empty( $existing_groups ) ) {
			return $existing_groups[0]->ID;
		}

		// Create the access group
		$group_id = wp_insert_post(
			[
				'post_title'  => $name,
				'post_type'   => 'dollie-access-groups',
				'post_status' => 'publish'
			]
		);

		$installs     = (int) get_field( '_wpd_installs', $product_id );
		$max_size     = get_field( '_wpd_max_size', $product_id );
		$staging      = get_field( '_wpd_staging_installs', $product_id );
		$included_bps = get_field( '_wpd_included_blueprints', $product_id );
		$excluded_bps = get_field( '_wpd_excluded_blueprints', $product_id );

		if ( ! $staging ) {
			$staging = 0;
		}
		if ( ! $max_size ) {
			$max_size = 0;
		}

		update_field( '_wpd_installs', $installs, $group_id );
		update_field( '_wpd_max_size', $max_size, $group_id );
		update_field( '_wpd_staging_installs', $staging, $group_id );
		update_field( '_wpd_included_blueprints', $included_bps, $group_id );
		update_field( '_wpd_excluded_blueprints', $excluded_bps, $group_id );

		return $group_id;
	}
}
