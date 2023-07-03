<?php

namespace Dollie\Core\Modules\Integrations;

use Dollie\Core\Singleton;
use Dollie\Core\Modules\AccessGroups\AccessGroups;

// Ensure this path is correct.

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class PaidMembershipsPro
 *
 * @package Dollie\Core\Modules\AccessGroups
 */
class PaidMembershipsPro extends Singleton implements IntegrationsInterface {

	protected $name = 'Paid Memberships Pro';


	/**
	 * PaidMembershipsPro constructor.
	 * Sets up the hooks needed for the access group system in Paid Memberships Pro.
	 */
	public function __construct() {
		parent::__construct();

		if ( defined( 'PMPRO_VERSION' ) ) {
			$this->name = 'Paid Memberships Pro';

			add_action( 'pmpro_membership_level_after_general_information', array( $this, 'custom_level_fields' ) );
			add_action( 'pmpro_save_membership_level', array( $this, 'save_custom_level_fields' ) );
			add_action( 'pmpro_after_all_membership_level_changes', array( $this, 'add_users_to_groups' ), 10, 1 );
		}
	}

	// Placeholder methods for IntegrationsInterface. Replace these with actual methods defined in IntegrationsInterface.
	public function subscribe( $userId, $subscriptionId ) {
		// Your implementation here.
	}

	public function unsubscribe( $userId, $subscriptionId ) {
		// Your implementation here.
	}

	public function getAccessStatus( $userId, $subscriptionId ) {
		// Your implementation here.
	}

	/**
	 * Customizes membership level fields for Paid Memberships Pro.
	 */
	public function custom_level_fields() {
		$groups = AccessGroups::get_access_groups();

		// Get the current level.
		$level = pmpro_getLevel( $_REQUEST['edit'] );

		// Get the currently selected group ID for this level.
		$selected_group_id = get_option( 'dollie_pmpro_group_' . $level->id );
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
						foreach ( $groups as $group ) {
							$selected = ( $group->ID == $selected_group_id ) ? 'selected="selected"' : '';
							echo '<option value="' . $group->ID . '" ' . $selected . '>' . $group->post_title . '</option>';
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
	 * Saves custom level fields for Paid Memberships Pro.
	 *
	 * @param int $level_id ID of the membership level
	 */
	public function save_custom_level_fields( $level_id ) {
		if ( isset( $_REQUEST['extra_setting'] ) ) {
			update_option( 'dollie_pmpro_group_' . $level_id, sanitize_text_field( $_REQUEST['extra_setting'] ) );
		}
	}

	function add_users_to_groups( $old_user_levels ) {
		$access = new AccessGroups();

		foreach ( $old_user_levels as $user_id => $old_levels ) {
			$new_levels = pmpro_getMembershipLevelsForUser( $user_id );

			$old_group_ids = array();
			$new_group_ids = array();

			// Get the group IDs associated with old levels.
			foreach ( $old_levels as $old_level ) {
				$old_group_id = get_option( 'dollie_pmpro_group_' . $old_level->id );
				if ( ! empty( $old_group_id ) ) {
					$old_group_ids[] = $old_group_id;
				}
			}

			// Get the group IDs associated with new levels.
			foreach ( $new_levels as $new_level ) {
				$new_group_id = get_option( 'dollie_pmpro_group_' . $new_level->id );
				if ( ! empty( $new_group_id ) ) {
					$new_group_ids[] = $new_group_id;
				}
			}

			// Add the user to new groups.
			$groups_to_add = array_diff( $new_group_ids, $old_group_ids );
			foreach ( $groups_to_add as $group_id ) {
				$access->add_to_access_group(
					$group_id,
					$user_id,
					$this->name,
					$this->name,
					'User is added to membership level ' . pmpro_getLevel( $new_level )->name . '.'
				);
			}

			// Remove the user from old groups.
			$groups_to_remove = array_diff( $old_group_ids, $new_group_ids );
			foreach ( $groups_to_remove as $group_id ) {
				$access->remove_from_access_group(
					$group_id,
					$user_id,
					$this->name,
					$this->name,
					'User is removed from membership level ' . pmpro_getLevel( $new_level )->name . '.'
				);
			}
		}
	}
}
