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
class Hooks extends Singleton {
	/**
	 * Hooks constructor.
	 */
	public function __construct() {
		parent::__construct();

		// add the membership level change hook
		add_action('pmpro_after_change_membership_level', [$this, 'after_change_membership_level'], 10, 2);

		if (defined('PMPRO_VERSION')) {
			add_action( 'pmpro_membership_level_after_general_information', [ $this, 'custom_level_fields' ] );
			add_action( 'pmpro_save_membership_level', [ $this, 'save_custom_level_fields' ] );
			add_action( 'pmpro_after_change_membership_level', [ $this, 'after_change_membership_level' ], 10, 2 );
		}

	}


	public function add_to_access_group($group_id, $user_ids) {
	    if(!is_array($user_ids)) {
	        $user_ids = array($user_ids);
	    }

	    // Assuming the field name is 'wpd_group_users'
	    $current_users = get_field('wpd_group_users', $group_id);

	    if (!is_array($current_users)) {
	        $current_users = array();
	    }

	    $updated_users = array_unique(array_merge($current_users, $user_ids));
	    update_field('wpd_group_users', $updated_users, $group_id);
	}

	public function remove_from_access_group($group_id, $user_ids) {
	    if(!is_array($user_ids)) {
	        $user_ids = array($user_ids);
	    }

	    // Assuming the field name is 'wpd_group_users'
	    $current_users = get_field('wpd_group_users', $group_id);

	    if (!is_array($current_users)) {
	        return;
	    }

	    $updated_users = array_diff($current_users, $user_ids);
	    update_field('wpd_group_users', $updated_users, $group_id);
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
		<h3>My Custom Settings</h3>
		<p>These are my extra settings.</p>
		<table>
			<tbody class="form-table">
			<tr>
				<th scope="row" valign="top"><label for="extra_setting">Extra Setting</label></th>
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
			die(var_dump($group_id, $user_id));
		}
	}

}
