<?php

namespace Dollie\Core\Modules\Subscription\Plugin;

use Dollie\Core\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class PaidMembershipsPro
 *
 * @package Dollie\Core\Modules\AccessGroups
 */
class PaidMembershipsPro extends Singleton {

	protected $pmp_name = 'Paid Memberships Pro';

	/**
	 * PaidMembershipsPro constructor.
	 * Sets up the hooks needed for the access group system in Paid Memberships Pro.
	 */
	public function __construct() {
		parent::__construct();

		if ( defined( 'PMPRO_VERSION' ) ) {
			$this->pmp_name = 'Paid Memberships Pro';

			add_action( 'pmpro_membership_level_after_general_information', array( $this, 'custom_level_fields' ) );
			add_action( 'pmpro_save_membership_level', array( $this, 'save_custom_level_fields' ) );
			add_action( 'pmpro_after_change_membership_level', array( $this, 'after_change_membership_level' ), 10, 2 );
		}
	}

	/**
	 * Customizes membership level fields for Paid Memberships Pro.
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
	 * Saves custom level fields for Paid Memberships Pro.
	 *
	 * @param int $level_id ID of the membership level
	 */
	public function save_custom_level_fields( $level_id ) {
		if ( isset( $_REQUEST['extra_setting'] ) ) {
			update_option( 'my_pmpro_group_' . $level_id, sanitize_text_field( $_REQUEST['extra_setting'] ) );
		}
	}

	/**
	 * Handles actions after the membership level is changed in Paid Memberships Pro.
	 *
	 * @param int $level_id ID of the membership level
	 * @param int $user_id ID of the user
	 */
	public function after_change_membership_level( $level_id, $user_id ) {
		// Retrieve the group ID associated with the membership level
		$group_id = get_option( 'my_pmpro_group_' . $level_id );

		// If a group is associated, add the user to it
		if ( ! empty( $group_id ) ) {
			$access = new AccessGroups();
			$access->add_to_access_group(
				$group_id, // Group ID
				$user_id, // User IDs
				$this->pmp_name,
				$this->pmp_name, // Log type
				'When user is added to membership level ' . pmpro_getLevel( $level_id )->name . '.'
			);
		}
	}
}
