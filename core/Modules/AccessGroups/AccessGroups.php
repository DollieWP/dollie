<?php

namespace Dollie\Core\Modules\AccessGroups;

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

	protected $pmp_name = 'pmpro_membership_level';
	/**
	 * Hooks constructor.
	 * Sets up the hooks needed for the access group system.
	 */
	public function __construct() {
		parent::__construct();

		$this->pmp_name = 'Paid Memberships Pro';

		// add the membership level change hook.
		add_action( 'pmpro_after_change_membership_level', array( $this, 'after_change_membership_level' ), 10, 2 );
		add_action( 'init', array( $this, 'create_access_group_terms' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_logs_meta_box' ) );
		add_action( 'admin_footer', array( $this, 'acf_readonly_fields' ) );
		add_action( 'acf/init', array( $this, 'load_acf' ) );

		if ( defined( 'PMPRO_VERSION' ) ) {
			add_action( 'pmpro_membership_level_after_general_information', array( $this, 'custom_level_fields' ) );
			add_action( 'pmpro_save_membership_level', array( $this, 'save_custom_level_fields' ) );
			add_action( 'pmpro_after_change_membership_level', array( $this, 'after_change_membership_level' ), 10, 2 );
		}
	}

	/**
	 * Load ACF
	 *
	 * @return void
	 */
	public function load_acf() {
		require DOLLIE_CORE_PATH . 'Modules/AccessGroups/acf-fields/acf-fields.php';
	}

	/**
	 * Adds a list of users to an access group.
	 *
	 * @param int    $group_id ID of the group
	 * @param array  $user_ids Array of user IDs
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
	 * Makes Advanced Custom Fields readonly.
	 */
	public function acf_readonly_fields() {
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			// replace 'field_12345678' with the field key of the field you want to disable
			$('div[data-key="field_649420e3e3fb7"] input, div[data-key="field_649420e3e3fb7"] textarea').attr(
				'readonly', 'true');
			$('div[data-key="field_649420e3e3fb7"] .acf-button, div[data-key="field_649420e3e3fb7"] .acf-row-handle')
				.addClass('hidden');
		});
		</script>
		<?php
	}

	/**
	 * Removes a list of users from an access group.
	 *
	 * @param int    $group_id ID of the group
	 * @param array  $user_ids Array of user IDs
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

		$logs = array_slice( $logs, -50 );

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
			'posts_per_page' => -1, // Get all posts
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
	 * @return array
	 */
	public function get_user_groups( $user_id ) {
		$group_ids = get_field( 'wpd_group_users', 'user_' . $user_id );

		// If the field is empty or not an array, return an empty array
		if ( ! is_array( $group_ids ) ) {
			return array();
		}

		return $group_ids;
	}

	/**
	 * Customizes membership level fields.
	 */
	public function custom_level_fields() {
		// Query for 'dollie-access-groups' posts
		$args  = array(
			'post_type'      => 'dollie-access-groups',
			'post_status'    => 'publish',
			'posts_per_page' => -1, // Get all posts
		);
		$posts = get_posts( $args );

		// Get the current level
		$level = pmpro_getLevel( $_REQUEST['edit'] );

		// Get the currently selected group ID for this level
		$selected_group_id = get_option( 'my_pmpro_group_' . $level->id );
		?>
			<hr>
			<h3>Dollie Hub - Access Group Settings</h3>
			<p>Easily add customers who subscribe to this membership plan to your Hub Access Groups.</p>
				<table>
					<tbody class="form-table">
						<tr>
							<th scope="row" valign="top"><label for="extra_setting">Add to Access Group:</label></th>
							<td>
								<select id="extra_setting" name="extra_setting">
							<?php
							foreach ( $posts as $post ) {
								$selected = ( $post->ID == $selected_group_id ) ? 'selected="selected"' : '';
								echo '<option value="' . $post->ID . '" ' . $selected . '>' . $post->post_title . '</option>';
							}
							?>
								</select>
							</td>
						</tr>
					</tbody>
				</table>

			<?php
	}

	/**
	 * Saves custom level fields.
	 *
	 * @param int $level_id ID of the membership level
	 */
	public function save_custom_level_fields( $level_id ) {
		if ( isset( $_REQUEST['extra_setting'] ) ) {
			update_option( 'my_pmpro_group_' . $level_id, sanitize_text_field( $_REQUEST['extra_setting'] ) );
		}
	}


	public function after_change_membership_level( $level_id, $user_id ) {
		// Retrieve the group ID associated with the membership level
		$group_id = get_option( 'my_pmpro_group_' . $level_id );

		// If a group is associated, add the user to it
		if ( ! empty( $group_id ) ) {
			$this->add_to_access_group(
				$group_id, // Group ID
				$user_id,      // User IDs
				$this->pmp_name,
				$this->pmp_name, // Log type
				'When user is added to membership level ' . pmpro_getLevel( $level_id )->name . '.',
			);
		}
	}


	/**
	 * Get groups for customer
	 *
	 * @param string   $status
	 * @param null|int $customer_id
	 *
	 * @return array|bool
	 */
	public function get_customer_access_details( $status = null, $customer_id = null ) {

		if ( ! $customer_id ) {
			$customer_id = get_current_user_id();
		}

		if ( ! $status ) {
			$status = 'active';
		}

		$groups = $this->get_user_groups( $customer_id );

		if ( ! is_array( $groups ) || empty( $groups ) ) {
			return false;
		}

		$data = array(
			'plans'     => array(),
			'resources' => array(
				'max_allowed_installs' => 0,
				'max_allowed_size'     => 0,
				'staging_max_allowed'  => 0,
			),
		);

		foreach ( $groups as $group_id ) {

			// Use the $group_id to get the WP_Post object
			$group_post = get_post( $group_id );
			if ( ! $group_post ) {
				continue;
			}

			$installs = (int) get_field( '_wpd_installs', $group_id );
			$max_size = get_field( '_wpd_max_size', $group_id );
			$staging  = get_field( '_wpd_staging_installs', $group_id );

			if ( ! $staging ) {
				$staging = 0;
			}

			if ( ! $max_size ) {
				$max_size = 0;
			}

			// Get the additional fields
			$available_sections            = get_field( 'available_sections', $group_id );
			$available_features_developers = get_field( 'available_features_developers', $group_id );

			$data['plans']['products'][ $group_id ] = array(
				'name'                => $group_post->post_title,
				'installs'            => $installs,
				'max_size'            => $max_size,
				'included_blueprints' => get_field( '_wpd_included_blueprints', $group_id ),
				'excluded_blueprints' => get_field( '_wpd_excluded_blueprints', $group_id ),
			);

			$data['resources']['max_allowed_installs']         += $installs;
			$data['resources']['max_allowed_size']             += $max_size;
			$data['resources']['name']                          = $group_post->post_title;
			$data['resources']['staging_max_allowed']          += $staging;
			$data['resources']['available_sections']            = $available_sections;
			$data['resources']['available_features_developers'] = $available_features_developers;

			$data = apply_filters( 'dollie/woo/subscription_product_data', $data, $customer_id, $group_id );
		}

		return apply_filters( 'dollie/woo/subscription_data', $data, $customer_id );
	}


	public function get_customer_developer_features( $customer_id = null ) {
		// Get the customer specific details
		$customer_access_details = $this->get_customer_access_details( null, $customer_id );
		$customer_features       = $customer_access_details['resources']['available_features_developers'] ?? array();

		// Get the global developer features
		$global_features = get_field( 'available_features_developers', 'option' );

		// Merge the arrays and remove duplicates
		$developer_features = array_unique( array_merge( $customer_features, $global_features ) );

		return $developer_features;
	}


	public function get_customer_site_features( $customer_id = null ) {
		$customer_access_details = $this->get_customer_access_details( null, $customer_id );
		return $customer_access_details['resources']['available_sections'] ?? array();
	}
}
