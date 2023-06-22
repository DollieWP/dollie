<?php

namespace Dollie\Core\Modules\AccessGroups;

use Dollie\Core\Singleton;
use Dollie\Core\Log;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Hooks
 *
 * @package Dollie\Core\Modules\AccessGroups
 */
class Hooks extends Singleton {
	/**
	 * Hooks constructor.
	 */
	public function __construct() {
		parent::__construct();

		// add the membership level change hook
		add_action('pmpro_after_change_membership_level', [$this, 'after_change_membership_level'], 10, 2);
		add_action('init', [$this, 'create_access_group_terms']);
		add_action('add_meta_boxes', [$this, 'add_logs_meta_box']);


		if (defined('PMPRO_VERSION')) {
			add_action( 'pmpro_membership_level_after_general_information', [ $this, 'custom_level_fields' ] );
			add_action( 'pmpro_save_membership_level', [ $this, 'save_custom_level_fields' ] );
			add_action( 'pmpro_after_change_membership_level', [ $this, 'after_change_membership_level' ], 10, 2 );
		}

	}

	public function add_to_access_group($group_id, $user_ids, $source = null) {
		if(!is_array($user_ids)) {
			$user_ids = array($user_ids);
		}

		// Assuming the field name is 'wpd_group_users'
		$current_users = get_field('wpd_group_users', $group_id);

		if (!is_array($current_users)) {
			$current_users = array();
		}

		// New users are those in the input that aren't already in the group
		$new_users = array_diff($user_ids, $current_users);

		$updated_users = array_unique(array_merge($current_users, $new_users));
		update_field('wpd_group_users', $updated_users, $group_id);

		// Get group (post) title
		$group_title = get_the_title($group_id);

		// Iterate over the new users to get their details
		foreach ($new_users as $userid) {
			$user_info = get_userdata($userid);
			$username = $user_info->user_login;
			$useremail = $user_info->user_email;

			$log_message = sprintf(
				esc_html__( 'Customer %s (email: %s, ID: %s) was added to group %s (ID: %s)', 'dollie' ),
				$username,
				$useremail,
				$userid,
				$group_title,
				$group_id
			);

			// If source is provided, add it to the log message
			if ($source) {
				$log_message .= sprintf(
					esc_html__( ' Source: %s.', 'dollie' ),
					$source
				);
			}

			\WDS_Log_Post::log_message( 'dollie-logs', $log_message, '', strval($group_id) );
		}
	}

	public function remove_from_access_group($group_id, $user_ids, $source = null) {
		if(!is_array($user_ids)) {
			$user_ids = array($user_ids);
		}

		// Assuming the field name is 'wpd_group_users'
		$current_users = get_field('wpd_group_users', $group_id);

		if (!is_array($current_users)) {
			return;
		}

		// Users to remove are those in both the input and the group
		$users_to_remove = array_intersect($user_ids, $current_users);

		$updated_users = array_diff($current_users, $users_to_remove);
		update_field('wpd_group_users', $updated_users, $group_id);

		// Get group (post) title
		$group_title = get_the_title($group_id);

		// Iterate over the users to remove to get their details
		foreach ($users_to_remove as $userid) {
			$user_info = get_userdata($userid);
			$username = $user_info->user_login;
			$useremail = $user_info->user_email;

			$log_message = sprintf(
				esc_html__( 'Customer %s (email: %s, ID: %s) was removed from group %s (ID: %s)', 'dollie' ),
				$username,
				$useremail,
				$userid,
				$group_title,
				$group_id
			);

			// If source is provided, add it to the log message
			if ($source) {
				$log_message .= sprintf(
					esc_html__( ' Source: %s.', 'dollie' ),
					$source
				);
			}

			\WDS_Log_Post::log_message( 'dollie-logs', $log_message, $group_id );
		}
	}


	public function user_has_access_to_group($group_id, $user_id) {
	    // Assuming the field name is 'wpd_group_users'
	    $current_users = get_field('wpd_group_users', $group_id);

	    // If there are no current users, return false
	    if (!is_array($current_users)) {
	        return false;
	    }

	    // Check if the user ID is in the array
	    return in_array($user_id, $current_users);
	}

	public function get_users_in_group($group_id) {
	    $current_users = get_field('wpd_group_users', $group_id);
	    return is_array($current_users) ? $current_users : [];
	}

	public function is_group_empty($group_id) {
	    $current_users = get_field('wpd_group_users', $group_id);
	    return empty($current_users);
	}

	public function count_users_in_group($group_id) {
	    $current_users = get_field('wpd_group_users', $group_id);
	    return is_array($current_users) ? count($current_users) : 0;
	}

    public function get_logs_by_group_id($group_id) {
		$args = [
			'post_type' => 'dollie-logs',
			'tax_query' => [
				[
					'taxonomy' => 'wds_log_type',
					'field'    => 'slug',
					'terms'    => strval($group_id)
				]
			]
		];
		$query = new \WP_Query($args);
		return $query->posts;
	}

	public function display_logs_meta_box($post) {
			$group_id = $post->ID;
			$logs = $this->get_logs_by_group_id($group_id);

			if (empty($logs)) {
				echo "No logs for this group.";
				return;
			}

			foreach ($logs as $log) {
				echo "<p>";
				echo "<strong>" . $log->post_date . "</strong> - ";
				echo $log->post_title;
				echo "</p>";
			}
		}

	public function add_logs_meta_box() {
		add_meta_box(
			'logs_meta_box', // id
			'Logs', // title
			[$this, 'display_logs_meta_box'], // callback
			'dollie-access-groups', // screen (post type)
			'normal', // context
			'default' // priority
		);
	}



	function create_access_group_terms() {
		// Query for 'dollie-access-groups' posts
		$args = array(
			'post_type' => 'dollie-access-groups',
			'post_status' => 'publish',
			'posts_per_page' => -1, // Get all posts
		);
		$posts = get_posts( $args );

		// Get the current log types
		$log_types = apply_filters( 'wds_log_post_log_types', array() );

		foreach( $posts as $post ) {
			if ( ! isset( $log_types[ $post->post_title ] ) ) {
				$log_types[ $post->post_title ] = array(
					'slug' => (string)$post->ID, // Using post ID as the slug
					'description' => 'Access Group - ' . $post->post_title, // Using post title as the description
				);
			}
		}

		// Add a filter to set the log types
		add_filter( 'wds_log_post_log_types', function ( $terms ) use ( $log_types ) {
			return $log_types;
		} );
	}



	public function get_user_groups($user_id) {
    $group_ids = get_field('wpd_group_users', 'user_' . $user_id);

		// If the field is empty or not an array, return an empty array
		if (!is_array($group_ids)) {
			return array();
		}

    	return $group_ids;
	}


		public function custom_level_fields() {
		// Query for 'dollie-access-groups' posts
		$args = array(
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
			$this->add_to_access_group( $group_id, array( $user_id ) );

		}
	}


	/**
	 * Get groups for customer
	 *
	 * @param string $status
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

		$groups = $this->get_user_groups($customer_id);

		if ( ! is_array( $groups ) || empty( $groups ) ) {
			return false;
		}

		$data = [
			'plans'     => [],
			'resources' => [
				'max_allowed_installs' => 0,
				'max_allowed_size'     => 0,
				'staging_max_allowed'  => 0,
			],
		];

		foreach ( $groups as $group_id ) {

			// Use the $group_id to get the WP_Post object
			$group_post = get_post($group_id);
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
			$available_sections = get_field( 'available_sections', $group_id );
			$available_features_developers = get_field( 'available_features_developers', $group_id );

			$data['plans']['products'][ $group_id ] = [
				'name'                => $group_post->post_title,
				'installs'            => $installs,
				'max_size'            => $max_size,
				'included_blueprints' => get_field( '_wpd_included_blueprints', $group_id ),
				'excluded_blueprints' => get_field( '_wpd_excluded_blueprints', $group_id ),
			];

			$data['resources']['max_allowed_installs'] += $installs;
			$data['resources']['max_allowed_size']     += $max_size;
			$data['resources']['name']                 = $group_post->post_title;
			$data['resources']['staging_max_allowed']  += $staging;
			$data['resources']['available_sections']   = $available_sections;
			$data['resources']['available_features_developers'] = $available_features_developers;

			$data = apply_filters( 'dollie/woo/subscription_product_data', $data, $customer_id, $group_id );
		}

		return apply_filters( 'dollie/woo/subscription_data', $data, $customer_id );
	}


	public function get_customer_developer_features($customer_id = null) {
		// Get the customer specific details
		$customer_access_details = $this->get_customer_access_details(null, $customer_id);
		$customer_features = $customer_access_details['resources']['available_features_developers'] ?? [];

		// Get the global developer features
		$global_features = get_field( 'available_features_developers', 'option' );

		// Merge the arrays and remove duplicates
		$developer_features = array_unique(array_merge($customer_features, $global_features));

		return $developer_features;
	}


	public function get_customer_site_features($customer_id = null) {
		$customer_access_details = $this->get_customer_access_details(null, $customer_id);
		return $customer_access_details['resources']['available_sections'] ?? [];
	}


}
